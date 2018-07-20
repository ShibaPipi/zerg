<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/6
 * Time: 12:59
 */

namespace app\lib\exception;


class BannerMissException extends BaseException
{
    public $code = 404;
    public $msg = '请求的 Banner 不存在';
    public $errorCode = 40000;

}