<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    // 自定义创建时间的字段名
    // protected $createTime = 'created_at';

    // 读取图片url
    protected function prefixImgUrl($value, $data)
    {
        $finalUrl = $value;
        if ($data['from'] == 1) {
            $imgPrefix = config('setting.img_prefix');
            $finalUrl = $imgPrefix . $value;

            return $finalUrl;
        } else {
            return $finalUrl;
        }
    }

}
