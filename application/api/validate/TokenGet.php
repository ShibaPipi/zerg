<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/11
 * Time: 10:14
 */

namespace app\api\validate;


class TokenGet extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty',
    ];

    protected $message=[
        'code' => '没有code还想拿token？做梦哦'
    ];

}