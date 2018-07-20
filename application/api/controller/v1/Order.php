<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/13
 * Time: 00:06
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\OrderPlace;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\api\validate\PaginationParameter;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;

class Order extends BaseController
{
    // 用户在选择商品后，向 api 提交包含其所选商品的相关信息
    // 服务器 api 在接收到信息后，需要检查订单相关商品的库存量
    // 有库存，把订单数据存入数据库 = 下单成功，返回客户端消息，告诉客户端可以支付
    // 调用我们的支付接口，进行支付
    // 还需要再次进行库存量检测
    // 服务器就可以调用支付微信接口进行支付
    // 小程序根据服务器返回的结果拉起微信支付
    // 微信会返回一个支付结果消息（异步）
    // 成功：再进行一次库存量检测
    // 成功：进行库存量的扣除，失败：返回一个支付失败的消息

    // 做一次库存量检测
    // 创建订单
    // 减库存——预扣除库存
    // 支付成功后，真正扣除库存
    // 在一定时间内未支付，还原库存
    //
    // （模糊查询，无用功）在php里写一个定时器/linux crontab/，每隔1min去遍历数据库找到那些超时的订单，还原库存
    //
    // （redis）任务队列，把订单任务加入到任务队列里，在里面写方法，还原库存

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getSummaryByUser,getDetail'],
    ];

    /**
     * 根据用户id分页获取订单列表（简要信息）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummaryByUser($page = 1, $size = 6)
    {
        (new PaginationParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $paginateOrders = OrderModel::getSummaryByUser($uid, $page, $size);
        if ($paginateOrders->isEmpty()) {
            // 考虑到客户端的体验，不建议返回空值或者抛出异常，而是返回一个关联数组
            return [
                'current_page' => $paginateOrders->currentPage(),
                'data' => [],
            ];
        }
//        $collection = collection($paginateOrders->items());
//        $data = $collection->hidden(['snap_items', 'snap_address'])
//            ->toArray();
        $data = $paginateOrders->hidden(['snap_items', 'snap_address', 'prepay_id'])->toArray();

        return [
            'current_page' => $paginateOrders->currentPage(),
            'data' => $data,
        ];
    }

    /**
     * 获取订单详情
     * @param $id
     * @return static
     * @throws OrderException
     * @throws \app\lib\exception\ParameterException
     */
    public function getDetail($id)
    {
        (new IDMustBePositiveInt())->goCheck();
        $orderDetail = OrderModel::get($id);
        if (!$orderDetail) {
            throw new OrderException();
        }

        return $orderDetail->hidden(['prepay_id']);
    }

    public function placeOrder()
    {
        (new OrderPlace())->goCheck();

        $products = input('post.');
        $products = $products['products'];
//        $products = [
//            [
//                'product_id' => 1,
//                'count' => 1,
//            ],
//            [
//                'product_id' => 2,
//                'count' => 1,
//            ],
//        ];
//        dump($products);exit;
        $uid = TokenService::getCurrentUid();

        $order = new OrderService();
        $status = $order->place($uid, $products);

        return $status;
    }

    /**
     * 获取全部订单简要信息（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummary($page = 1, $size = 20)
    {
        (new PaginationParameter())->goCheck();
//        $uid = Token::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByPage($page, $size);
        if ($pagingOrders->isEmpty())
        {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }

    public function delivery($id){
        (new IDMustBePositiveInt())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        if($success){
            return new SuccessMessage();
        }
    }
}