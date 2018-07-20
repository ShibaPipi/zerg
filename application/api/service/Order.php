<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/13
 * Time: 16:27
 */

namespace app\api\service;


use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\api\model\Order as OrderModel;
use think\Db;
use think\Exception;

class Order
{
    // 订单的商品列表，也就是客户端传递过来的 products 参数
    protected $orderedProducts;

    // 真实的商品信息（包括库存）
    protected $products;

    protected $uid;

    public function place($uid, $orderedProducts)
    {
        // $oProducts 和 $products 作对比
        // $products 是从数据库查询出来的
        $this->orderedProducts = $orderedProducts;
        $this->products = $this->getProductsByOrder($orderedProducts);
        $this->uid = $uid;
        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        // 开始创建订单
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;

        return $order;
    }

    // 生成订单
    private function createOrder($snap)
    {
        // 使用事务，防止一个方法中多条数据库写入操作不能同时写入或者同时放弃写入从而导致不同表中数据不一致的问题

        // 开始事务
        Db::startTrans();

        try {
            $orderNo = $this->makeOrderNo();

            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);

            $order->save();

            $orderID = $order->id;
            $create_time = $order->create_time;

            foreach ($this->orderedProducts as &$p) {
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->orderedProducts);

            // 提交事务
            Db::commit();

            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time,
            ];
        } catch (Exception $exception) {
            // 异常时，回滚事务
            Db::rollback();
            throw $exception;
        }

    }

    public static function makeOrderNo()
    {
        $yCode = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $orderSn = $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m')))
            . date('d') . substr(time(), -5) . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));

        return $orderSn;
    }

    // 生成订单快照
    private function snapOrder($status)
    {
        // 订单的快照信息
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => null,
            'snapName' => '',
            'snapImg' => '',
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];

        if (count($this->products) > 1) {
            $snap['snapName'] .= '等...';
        }

        return $snap;
    }

    private function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001,
            ]);
        }

        return $userAddress->toArray();
    }

    public function checkOrderStock($orderID)
    {
        $orderedProducts = OrderProduct::where('order_id', '=', $orderID)->select();
        // 将查询结果写在属性里，而不是把变量在方法之间传来传去
        $this->orderedProducts = $orderedProducts;

        $this->products = $this->getProductsByOrder($orderedProducts);
        $status = $this->getOrderStatus();

        return $status;
    }

    // 获取订单下所有商品的库存情况
    private function getOrderStatus()
    {
        // 一组商品中，只要有任何一个缺货，则不能通过
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            // 购买商品数量的总合，不是购买商品的种类数
            'totalCount' => 0,
            // 被用来保存订单中所有商品的详细信息
            'pStatusArray' => [],
        ];

        foreach ($this->orderedProducts as $orderedProduct) {
            $pStatus = $this->getProductStatus($orderedProduct['product_id'], $orderedProduct['count'], $this->products);

            // 只要有任何一个缺货，则不能通过
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }

            // 求订单总数
            $status['totalCount'] += $pStatus['counts'];

            // 求订单总价
            $status['orderPrice'] += $pStatus['totalPrice'];

            // 保存订单中所有商品的详细信息
            array_push($status['pStatusArray'], $pStatus);
        }

        return $status;
    }

    // 获取某个商品的库存情况
    private function getProductStatus($oPID, $orderCount, $products)
    {
        $pIndex = -1;

        // 订单中某一个商品的详细信息
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'counts' => 0,
            'name' => '',
            'price' => 0,
            // 此商品的总价，不是订单价格
            'totalPrice' => 0,
            'main_img_url' => null,
        ];

        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            // 客户端传递的 productid 有可能根本不存在
            throw new OrderException([
                'msg' => 'id 为' . $oPID . '的商品不存在，创建订单失败',
            ]);
        } else {
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['price'] = $product['price'];
            $pStatus['main_img_url'] = $product['main_img_url'];
            $pStatus['counts'] = $orderCount;
            $pStatus['totalPrice'] = $product['price'] * $orderCount;

            if ($product['stock'] - $orderCount >= 0) {
                $pStatus['haveStock'] =true;
            }
        }

        return $pStatus;
    }

    // 根据订单查找真实商品信息
    private function getProductsByOrder($orderedProducts)
    {
        // 为了避免循环查询数据库，将用户需要购买的所有商品的 id 存在一个数组里，一次查询出来
        $oPIDs = [];
        foreach ($orderedProducts as $item) {
            array_push($oPIDs, $item['product_id']);
        }

        $products = Product::all($oPIDs)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();

        return $products;
    }

    public function delivery($orderID, $jumpPage = '')
    {
        $order = OrderModel::where('id', '=', $orderID)
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != OrderStatusEnum::PAID) {
            throw new OrderException([
                'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
//            ->update(['status' => OrderStatusEnum::DELIVERED]);
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order, $jumpPage);
    }
}