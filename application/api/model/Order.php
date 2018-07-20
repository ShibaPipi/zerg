<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/13
 * Time: 23:24
 */

namespace app\api\model;


class Order extends BaseModel
{
    protected $hidden = ['user_id', 'delete_time', 'update_time'];

    protected $autoWriteTimestamp = true;

    // 获取器
    public function getSnapItemsAttr($value)
    {
        if(empty($value)) {
            return null;
        }

        return json_decode($value);
    }

    // 获取器
    public function getSnapAddressAttr($value){
        if(empty($value)) {
            return null;
        }

        return json_decode(($value));
    }

    public static function getSummaryByUser($uid, $page = 1, $size = 15)
    {
        $pagination = self::where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, true, ['page' => $page]);

        return $pagination ;
    }


    public static function getSummaryByPage($page = 1, $size = 20){
        $pagination = self::order('create_time desc')
            ->paginate($size, true, ['page' => $page]);

        return $pagination ;
    }

}