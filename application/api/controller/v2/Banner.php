<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/6/29
 * Time: 22:21
 */

namespace app\api\controller\v2;


class Banner
{
    /**
     * @param $id banner 的 id 号
     */
    public function getBanner($id)
    {
        return 'this is version 2.';
    }

}