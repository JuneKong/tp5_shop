<?php
/**
 * 管理员模型类
 */

namespace app\shop\model;

use \think\Db;

class Admin extends Base{
    // 数据表名称
    protected $table = 'jx_admin';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'username', 'password','create_time', 'update_time'];
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'username' => 'require|unique:admin',
            'password' => 'require',
        ],
        'msg' => [
            'username.require' => '用户名不可为空',
            'username.unique' => '用户名重复',
            'password' => '密码不可为空'
        ]
    ];

    //修改器：这是的 set 和 Attr 是固定的, 中间的 Password 是字段名, 第一个字母大写
    public function setPasswordAttr($val)
    {
        return md5($val);
    }

    protected static function init()
    {
        // ***模型事件只可以在调用模型的方法才能生效，使用查询构造器通过Db类操作是无效的
        self::afterInsert(function ($admin) {
            //处理角色问题
            $data = ['admin_id' => $admin['id'], 'role_id' => $admin['role_id']];
            model('AdminRole')->save($data);
        });
    }

    // 获取用户列表信息
    public function getListData()
    {
        $list = $this -> alias('a') 
                -> field('a.*, c.role_name') 
                -> join('admin_role b', 'a.id=b.admin_id') 
                -> join('role c', 'b.role_id=c.id') 
                -> paginate(10);
        return $list; 
    }

    // 删除用户
    public function remove($id)
    {
        // ***注意：在事务操作的时候，确保你的数据库连接是相同的。
        $db = $this -> getQuery();
        $db->startTrans();
        try {
            // 1.删除用户信息
            $this -> where("id='$id'") -> delete();
            // 2.删除用户的角色信息
            model('AdminRole') -> where("admin_id='$id'") ->delete();
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    // 查找用户信息
    public function findOne($id)
    {
        return $this -> alias('a') -> field('a.*, b.role_id') -> join('admin_role b', 'a.id=b.admin_id') -> find($id);
    }

    // 更新用户信息
    public function updateData($data)
    {
        // 更新用户信息
        $this -> save($data, $data['id']);
        // 更新角色id
        $role_info = model('AdminRole') -> where("admin_id=".$data['id']) -> find();
        $role_info -> role_id = $data['role_id'];
        $role_info -> save();
        return true;
    }

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
        cookie('username', $user['username']);
        return true;
    }
}

?>