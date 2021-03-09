<?php
/**
 * 用户中心控制器
 */
namespace app\home\controller;

class Member extends Mypublic
{
    public function __construct()
    {
        parent::__construct();
        $this -> checkLogin();
    }

    // 我的定单
    public function order()
    {
        $user_id = session('user_id');
        $info = model('Order') -> where('user_id='.$user_id) -> select();
        $this -> assign('info', $info);
        return $this -> fetch();
    }
}
