<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/12
 * Time: 23:56
 */

namespace app\lib\exception;


class ForbiddenException extends BaseException
{
    public $code = 403;
    public $msg = '权限不够';
    public $errorCode = 10001;
}