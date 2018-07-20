<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/12
 * Time: 13:03
 */

namespace app\api\model;


class ProductProperty extends BaseModel
{
    protected $hidden = [
        'id',
        'product_id',
        'delete_time',
        'update_time',
    ];
}