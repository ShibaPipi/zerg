<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/13
 * Time: 13:47
 */

namespace app\api\controller;


use think\Controller;
use app\api\service\Token as TokenService;

class BaseController extends Controller
{
    // PSR-4 PSR-0 自动加载规范，不需显式地调用 require_once() 方法就可以调用其他类的方法
    protected function checkPrimaryScope()
    {
        TokenService::needPrimaryScope();
    }

    protected function checkExclusiveScope()
    {
        TokenService::needExclusiveScope();
    }
}