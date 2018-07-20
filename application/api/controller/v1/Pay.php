<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/14
 * Time: 16:44
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\WxNotify;
use app\api\validate\IDMustBePositiveInt;
use app\api\service\Pay as PayService;
use think\Log;

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    public function getPreOrder($id = '')
    {
        (new IDMustBePositiveInt())->goCheck();
        $pay= new PayService($id);

        return $pay->pay();
    }

    public function redirectNotify()
    {
        $notify = new WxNotify();
        $notify->Handle();
    }

    public function notifyConcurrency()
    {
        $notify = new WxNotify();
        $notify->Handle();
    }

    public function receiveNotify()
    {
        // 通知频率为 15/15/30/180/1800/1800/1800/1800/3600 单位：秒


        // 1.检测库存量，超卖
        // 2.更新订单状态
        // 3.减库存
        // 如果成功处理，返回微信成功处理的信息，否则，返回没有成功处理

        // 特点：post：xml格式，不可以携带参数

//        $xmlData = file_get_contents('php://input');
//        Log::error($xmlData);
//        $notify = new WxNotify();
//        $notify->Handle();
        $xmlData = file_get_contents('php://input');
        $result = curl_post_raw('http://localhost:8888/zerg/public/api/v1/pay/re_notify?XDEBUG_SESSION_START=12954',
            $xmlData);
        return $result;
        Log::error($xmlData);
    }
}