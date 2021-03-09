<?php
/**
 * 用户模型类
 */

namespace app\home\model;

use app\shop\model\Base;

class User extends Base{
    // 数据表名称
    protected $table = 'jx_user';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'username', 'password', 'salt','create_time', 'update_time'];
    
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'username' => 'require|unique:user',
            'password' => 'require',
        ],
        'msg' => [
            'username.require' => '用户名不可为空',
            'username.unique' => '用户名重复',
            'password' => '密码不可为空'
        ]
    ];

    // 注册
    public function regist($username, $password)
    {
        if(!$username || !$password){
            $this -> error = '用户名或密码不可为空';
            return false;
        }
        // 用户是否存在
        $info = $this -> where("username='$username'") -> find();
        if($info){
            $this -> error = '用户名已存在';
            return false;
        }
        $salt = rand(100000, 999999);
        $db_pass = md5(md5($password).$salt);
        $data = array(
            'username' => $username,
            'password' => $db_pass,
            'salt' => $salt
        );
        return $this -> save($data);
    }

    // 登录
    public function login($username, $password)
    {
        if(!$username || !$password){
            $this -> error = '用户名或密码不可为空';
            return false;
        }
        $info = $this -> where("username='$username'") -> find();
        // 用户是否存在
        if(!$info){
            $this -> error = '当前用户名不存在';
            return false;
        }
        // 密码是否正确
        $db_pass = md5(md5($password).$info['salt']);
        if($db_pass != $info['password']){
            $this -> error = '密码错误';
            return false;
        }
        // 存入缓存
        session('user_id', $info['id']);
        session('user_name', $info['username']);
        // 缓存中的购物车信息存入数据库
        model('cart') -> cookie2Db();
        return true;
    }
}
