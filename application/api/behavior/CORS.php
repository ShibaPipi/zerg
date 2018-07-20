<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/19
 * Time: 19:58
 */

namespace app\api\behavior;


class CORS
{
    public function appInit(&$params)
    {
        // 解决浏览器跨域问题，谷歌认为有两种，简单请求和复杂请求

        // 给所有请求的返回结果附加header
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: POST,GET,PUT');

        // 如果请求是options，停止，此时已经在请求的返回结果附加header，从而再次请求可以正常访问api
        if(request()->isOptions()) {
            exit();
        }
    }
}