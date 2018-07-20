<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/19
 * Time: 18:59
 */

namespace app\api\validate;


class AppTokenGet extends BaseValidate
{
    protected $rule = [
        'ac' => 'require|isNotEmpty',
        'se' => 'require|isNotEmpty'
    ];

}