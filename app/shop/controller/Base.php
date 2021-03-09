<?php
/**
 * 基础控制器
 */

namespace app\shop\controller;

use \think\Controller;

class Base extends Controller{
    
    /**是否需要验证 */
    public $is_check_rule = true;
    /**用户信息 */
    public $user = array();

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        $id = cookie('id');
        if(!$id){
            $this -> error('请登录！', url('Login/login'));
        }
        $this->user = cache('user_'.$id);
        if(!$this->user){
            // echo 'mysql';
            // 保存用户信息
            $this -> user['id'] = $id;
            $this -> user['username'] = cookie('username');
            // 根据用户id获得角色id
            $role_info = model('AdminRole') -> where("admin_id='$id'") -> find();
            $this -> user['role_id'] = $role_info['role_id'];
            // 获得角色id的权限信息列表
            $ruleModel = model('Rule');
            if($role_info['role_id'] == 1){
                // 超级管理员
                $this -> is_check_rule = false;
                $rule_list = $ruleModel -> select();
            }else{
                // 普通管理员
                // 根据角色id获得权限id
                $role_rules = model('RoleRule') -> where('role_id='.$role_info['role_id']) -> select();
                $rule_ids = [];
                foreach ($role_rules as $key => $value) {
                    $rule_ids[] = $value['rule_id'];
                }
                $rule_ids = implode(',', $rule_ids);
                $rule_list = model('Rule') -> where("id in ($rule_ids)") -> select();
            }
            foreach ($rule_list as $key => $value) {
                // 用于限制访问权限
                $this->user['rules'][] = strtolower( $value['module_name'] .'/'. $value['controller_name'] .'/'.$value['action_name']);
                // 显示菜单
                if($value['is_show'] == 1){
                    $this->user['menu'][] = $value;
                }
            }
            cache('user_'.$id, $this->user);
        }
        if($this->user['role_id'] == 1){
            $this->is_check_rule = false;
        }
        if($this->is_check_rule){
            // 默认主页可访问
            $this->user['rules'][] = 'shop/index/index';
            $this->user['rules'][] = 'shop/index/home';
            $url = strtolower(request() -> module() .'/'. request() -> controller() .'/'. request() -> action());
            if(!in_array($url, $this->user['rules'])){
                if(request()->isAjax()){
                    return json(['state' => 0, 'msg' => '您暂没有访问权限！']);
                }else{
                    $this -> error('您暂没有访问权限！', url('index/home'));
                }
                exit;
            }
        }
    }
}
?>