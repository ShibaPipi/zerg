<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/11
 * Time: 15:17
 */

namespace app\lib\exception;


class WeChatException extends BaseException
{
    public $code = 400;
    public $msg = 'WeChat Unknown Error';
    public $errorCode = 999;

}