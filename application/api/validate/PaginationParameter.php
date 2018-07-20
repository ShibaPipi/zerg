<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/15
 * Time: 13:30
 */

namespace app\api\validate;


class PaginationParameter extends BaseValidate
{
    protected $rule = [
        'page' => 'isPositiveInteger',
        'size' => 'isPositiveInteger'
    ];

    protected $message = [
        'page' => '分页参数必须是正整数',
        'size' => '分页参数必须是正整数'
    ];
}