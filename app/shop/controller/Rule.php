<?php
/**
 * 权限控制器
 */

namespace app\shop\controller;

use app\shop\controller\Role;

class Rule extends Base{
    //添加
    public function add()
    {
        if(request() -> isPost()){
            $post = input('post.');
            $model = Model('Rule');
            $res = $model -> save($post);
            if($res == false){
                $this -> error($model -> getError());
            }else{
                $this->refresh();
                $this -> success('添加成功', url('showlist'));
            }          
        }else{
            $cate = Model('Rule') -> getRuleData();
            $this -> assign('cate', $cate);
            return $this -> fetch();
        }
    }

    //列表显示
    public function showList()
    {
        $data = Model('Rule') -> getRuleData();
        $this -> assign('data', $data);
        return $this -> fetch();
    }

    //删除
    public function del()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $model = Model('Rule');
        $res = $model -> dels($id);
        if($res){
            $this->refresh();
            $this -> success("删除成功");
        }else{
            $this -> error('删除失败！');
        }
    }

    // 编辑
    public function edit()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $model = Model('Rule');
        if(request()->isPost()){
            $post = input('post.');
            $res = $model -> updateData($post);
            if($res){
                $this->refresh();
                $this -> success('编辑成功', url('showlist'));
            }else{
                $this -> error($model -> getError());
            }
        }else{
            $data = $model -> findOneById($id);
            $cate = $model -> getRuleData();
            $this -> assign('data', $data);
            $this -> assign('cate', $cate);
            return $this -> fetch();
        }
    }

    // 更新超级管理员信息
    public function refresh()
    {
        $role = new Role();
        $role -> flushAdmin();
    }
}
?>