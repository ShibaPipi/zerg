<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/11
 * Time: 21:25
 */

namespace app\lib\exception;


class TokenException extends BaseException
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10001;

}