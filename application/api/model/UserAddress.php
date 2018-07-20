<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/12
 * Time: 16:10
 */

namespace app\api\model;


class UserAddress extends BaseModel
{
    protected $hidden = [
        'id',
        'user_id',
        'delete_time',
    ];
}