<?php
/**
 * 角色控制器
 */

namespace app\shop\controller;

class Role extends Base{
    // 添加角色
    public function add()
    {
        if(request()->isPost()){
            $post = input('post.');
            $model = Model('Role');
            $res = $model -> save($post);
            if($res){
                $this -> success('添加角色成功');
            }else{
                $this -> error($model -> getError());
            }
        }else{
            return $this -> fetch();
        }
    }

    // 角色列表
    public function showList()
    {
        $model = Model('Role');
        $list = $model -> paginate(10);
        $page = $list -> render();
        $count = $model -> count();
        $this -> assign('list', $list);
        $this -> assign('page', $page);
        $this -> assign('count', $count);
        return $this -> fetch();
    }

    // 删除角色
    public function del()
    {
        $id = input('id');
        if($id <= 1){
            $this -> error('参数错误');
        }
        $model = Model('Role');
        $res = $model -> remove($id);
        if($res){
            $this -> success('删除角色成功');
        }else{
            $this -> error($model->getError());
        }
    }

    // 修改角色
    public function edit()
    {
        $id = input('id');
        if($id <= 1){
            $this -> error('参数错误');
        }
        $model = Model('Role');
        if(request()->isPost()){
            $post = input('post.');
            $res = $model -> save($post, $id);
            if($res){
                $this -> success('修改成功', url('showlist'));
            }else{
                $this -> error($model->getError());
            }
        }else{
            $info = $model -> findOneById($id);
            $this -> assign('info', $info);
            return $this -> fetch();
        }
    }

    // 权限设置
    public function dispatch()
    {
        $id = input('id');
        if($id <= 1){
            $this -> error('参数错误');
        }
        $rrModel = Model('RoleRule');
        if(request()->isPost()){
            $post = input('post.');
            $res = $rrModel -> dispatch($post);
            if($res){
                // 修改角色权限，要更新对应用户角色的信息，删除对应的缓存信息
                $user_info = model('AdminRole') -> where("role_id=$id") -> select();
                foreach ($user_info as $key => $value) {
                    cache('user_'.$value['admin_id'], null);
                }
                $this -> success('设置成功', url('showlist'));
            }else{
                $this -> error('设置失败');
            }
        }else{
            $rule_id = $rrModel -> getRulesById($id);
            if(!$rule_id){
                $currRule = [];
            }else{
                foreach ($rule_id as $value) {
                    $currRule[] = $value['rule_id'];
                }
            }
            $rule = Model('Rule') -> getRuleData();
            $this -> assign('currRule', $currRule);
            $this -> assign('rule', $rule);
            $this -> assign('id', $id);
            return $this -> fetch();
        }
    }

    // 更新超级管理员的缓存信息
    public function flushAdmin()
    {
        $user = model('AdminRole') -> where('role_id=1') -> select();
        foreach ($user as $key => $value) {
            cache('user_'.$value['admin_id'], null);
        }
    }
}
?>