<?php
/**
 * 登录模型类
 */
namespace app\shop\model;

class Login extends Base{

    // 登录
    public function login($username, $password)
    {
        $user = $this ->where("username ='$username'") -> find();
        if(!$user){
            $this -> error = '用户不存在！';
            return false;
        }
        if($user['password'] != md5($password)){
            $this -> error = '密码错误！';
            return false;
        }
        // 存储用户信息
        cookie('id', $user['id']);
        cookie('username', $username);
        return true;
    }
}

?>