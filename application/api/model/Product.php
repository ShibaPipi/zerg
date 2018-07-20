<?php

namespace app\api\model;


class Product extends BaseModel
{
    protected $hidden = [
        'main_img_id',
        'pivot',
        'from',
        'category_id',
        'create_time',
        'update_time',
        'delete_time'
    ];

    // 读取器
    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }

    public function imgs()
    {
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }

    public function properties()
    {
        return $this->hasMany('ProductProperty', 'product_id', 'id');
    }

    public static function getMostRecent($count)
    {
        $products = self::limit($count)->order('create_time desc')->select();

        return $products;
    }

    public static function getProductsByCategoryID($categoryID)
    {
        $products = self::where('category_id', '=', $categoryID)->select();

        return $products;
    }

    public static function getProductDetail($id)
    {
        // Query 查询构造器 闭包法
        $product = self::with([
            'imgs' => function($query) {
                $query->with(['imgUrl'])
                    ->order('order', 'asc');
            }
        ])
            ->with(['properties'])
            ->find($id);

        return $product;
    }
}
