<?php
/**
 * Created by PhpStorm.
 * User: sunyaopeng
 * Date: 2018/7/12
 * Time: 14:35
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\UserAddress;
use app\api\validate\AddressNew;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdate,getUserAddress']
    ];
    // 只有在 second 方法执行之前执行且必执行 first 方法
//    protected $beforeActionList = [
//        'first' => ['only' => 'second']
//    ];
//
//    private function first()
//    {
//        return 'first';
//    }
//
//    public function second()
//    {
//        return 'second';
//    }

    public function createOrUpdate()
    {
        $validate = new AddressNew();
        $validate->goCheck();

        // 根据 token 获取 uid
        $uid = TokenService::getCurrentUid();

        // 根据 uid 查找用户数据，判断用户是否存在，如果不存在，抛出异常
        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }

        // 获取用户从客户端传递来的地址信息
        $dataArray = $validate->getDataByRule(input('post.'));

        // 根据用户地址信息是否存在，从而判断新增或更新地址
        $userAddress = $user->address;
        if (!$userAddress) {
            $user->address()->save($dataArray);
        } else {
            $user->address->save($dataArray);
        }

//        return $user;
        return json(new SuccessMessage(), 201);
    }

    /**
     * 获取用户地址信息
     * @return UserAddress
     * @throws UserException
     */
    public function getUserAddress(){
        $uid = TokenService::getCurrentUid();
        $userAddress = UserAddress::where('user_id', $uid)
            ->find();
        if(!$userAddress){
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }
        return $userAddress;
    }
}