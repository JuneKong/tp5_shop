<?php
/**
 * 管理员控制器
 */

namespace app\shop\controller;

class Admin extends Base{
    // 添加管理员
    public function add()
    {
        if(request()->isPost()){
            $post = input('post.');
            $model = Model('Admin');
            $res = $model -> save($post);
            if($res){
                $this -> success('添加用户成功', url('showlist'));
            }else{
                $this -> error($model -> getError());
            }
        }else{
            $role = Model('Role') -> select();
            $this -> assign('role', $role);
            return $this -> fetch();
        }
    }

    // 管理员列表
    public function showList()
    {
        $model = Model('Admin');
        $list = $model -> getListData();
        $page = $list -> render();
        $count = $model -> count();
        $this -> assign('list', $list);
        $this -> assign('page', $page);
        $this -> assign('count', $count);
        return $this -> fetch();
    }

    // 删除管理员
    public function del()
    {
        $id = input('id');
        if($id <= 1){
            $this -> error('参数错误');
        }
        $model = Model('Admin');
        $res = $model -> remove($id);
        if($res){
            $this -> success('删除角色成功');
        }else{
            $this -> error($model->getError());
        }
    }

    // 修改管理员
    public function edit()
    {
        $id = input('id');
        if($id <= 1){
            $this -> error('参数错误');
        }
        $model = Model('Admin');
        if(request()->isPost()){
            $post = input('post.');
            $res = $model -> updateData($post);
            if($res){
                $this -> success('修改成功', url('showlist'));
            }else{
                $this -> error($model->getError());
            }
        }else{
            $info = $model -> findOne($id);
            $role = Model('Role') -> select();
            $this -> assign('info', $info);
            $this -> assign('role', $role);
            return $this -> fetch();
        }
    }
}
?>