<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/15
 * Time: 01:24
 */

namespace app\api\service;


use app\api\model\Product;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Exception;
use think\Loader;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class WxNotify extends \WxPayNotify
{
//    <xml>
//    <return_code><![CDATA[SUCCESS]]></return_code>
//    <return_msg><![CDATA[OK]]></return_msg>
//    <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
//    <mch_id><![CDATA[10000100]]></mch_id>
//    <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
//    <openid><![CDATA[oUpF8uMuAJO_M2pxb1Q9zNjWeS6o]]></openid>
//    <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
//    <result_code><![CDATA[SUCCESS]]></result_code>
//    <prepay_id><![CDATA[wx201411101639507cbf6ffd8b0779950874]]></prepay_id>
//    <trade_type><![CDATA[JSAPI]]></trade_type>
//    </xml>
    public function NotifyProcess($data, &$msg)
    {
//        $data = $this->data;
        if ($data['result_code'] == 'SUCCESS') {
            $orderNo = $data['out_trade_no'];

            Db::startTrans();

            try {
                $order = OrderModel::where('order_no', '=', $orderNo)->lock(true)->find();
                if ($order->status == 1) {
                    $service = new OrderService();
                    $stockStatus = $service->checkOrderStock($order->id);
                    if ($stockStatus['pass']) {
                        $this->updateOrderStatus($order->id, true);
                        $this->reduceStock($stockStatus);
                    } else {
                        $this->updateOrderStatus($order->id, false);
                    }
                }

                Db::commit();
            } catch (Exception $exception) {
                Db::rollback();
                Log::error($exception);
                // 如果出现异常，向微信返回false，请求重新发送通知
                return false;
            }
        }

        // 最后，除非 result_code 为 false，否则都要给微信返回 true，意义是通知微信你已知晓结果，而非代表支付的成败
        return true;
    }

    // 减掉库存量
    private function reduceStock($stockStatus)
    {
//        $pIDs = array_keys($stockStatus['pStatus']);
        foreach ($stockStatus['pStatusArray'] as $singlePStatus) {
            Product::where('id', '=', $singlePStatus['id'])->setDec('stock', $singlePStatus['count']);
        }
    }

    private function updateOrderStatus($orderID, $success)
    {
        $status = $success ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id', '=', $orderID)->update(['status' => $status]);
    }

}