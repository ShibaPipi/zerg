<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/10
 * Time: 18:01
 */

namespace app\api\model;


class Category extends BaseModel
{
    protected $hidden = [
        'create_time',
        'delete_time',
        'update_time',
    ];

    public function img()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }
}