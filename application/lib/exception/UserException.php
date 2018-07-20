<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/12
 * Time: 15:12
 */

namespace app\lib\exception;


class UserException extends BaseException
{
    public $code = 404;
    public $msg = '用户不存在';
    public $errorCode = 60000;

}