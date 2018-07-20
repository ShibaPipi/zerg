<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/6
 * Time: 10:39
 */

namespace app\api\model;


class Banner extends BaseModel
{
    protected $hidden = [
        'update_time',
        'delete_time',
    ];

    public function items()
    {
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }

    public static function getBannerByID($id)
    {
        // 模型的嵌套关联关系
        $banner = self::with(['items', 'items.img'])->find($id);

//        $result = Db::query('SELECT * FROM banner_item WHERE banner_id = ?', [$id]);

        // 表达式法
//        $result = Db::table('banner_item')
//            ->where('banner_id', '=', $id)
//            ->select();
//
        // 闭包法
//        $result = Db::table('banner_item')
//            ->where(function ($query) use ($id) {
//                $query->where('banner_id', '=', $id);
//            })
//            ->select();
//
//        return $result;

        return $banner;
    }

}