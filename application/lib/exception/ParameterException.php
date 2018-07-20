<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/7
 * Time: 11:07
 */

namespace app\lib\exception;


class ParameterException extends BaseException
{
    public $code = 400;
    public $msg = 'Param err';
    public $errorCode = 10000;

}