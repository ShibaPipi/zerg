<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/19
 * Time: 19:02
 */

namespace app\api\model;


class ThirdApp extends BaseModel
{
    public static function check($ac, $se)
    {
        $app = self::where('app_id','=',$ac)->where('app_secret', '=',$se)->find();

        return $app;
    }

}