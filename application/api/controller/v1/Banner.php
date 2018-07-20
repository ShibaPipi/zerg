<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/6/29
 * Time: 22:21
 */

namespace app\api\controller\v1;


use app\api\validate\IDMustBePositiveInt;
use \app\api\model\Banner as BannerModel;
use app\lib\exception\BannerMissException;

class Banner
{
    /**
     * @param $id banner 的 id 号
     */
    public function getBanner($id)
    {
        // AOP面向切面编程，验证id是否为正整数
        (new IDMustBePositiveInt())->goCheck();

        // 获取banner的id
        $banner = BannerModel::getBannerByID($id);

        // 验证banner是否存在
        if (!$banner) {
            throw new BannerMissException();
        }

        return $banner;
    }

}