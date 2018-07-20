<?php

namespace app\api\model;


class Image extends BaseModel
{
    protected $hidden = [
        'id',
        'from',
        'update_time',
        'delete_time'
    ];

    // 此处获取器调用基类的方法，避免所有有关url的方法只能调用图片的url
    public function getUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}
