<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/6/29
 * Time: 23:59
 */

namespace app\api\validate;


class IDMustBePositiveInt extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'id' => 'id 必须是正整数',
    ];

}