<?php
/**
 * 类型控制器
 */

namespace app\shop\controller;

class Type extends Base{
    // 添加类型
    public function add()
    {
        if(request()->isPost()){
            $post = input('post.');
            $model = Model('Type');
            $res = $model -> save($post);
            if($res){
                $this -> success('添加类型成功');
            }else{
                $this -> error($model -> getError());
            }
        }else{
            return $this -> fetch();
        }
    }

    // 类型列表
    public function showList()
    {
        $model = Model('Type');
        $list = $model -> paginate(10);
        $page = $list -> render();
        $count = $model -> count();
        $this -> assign('list', $list);
        $this -> assign('page', $page);
        $this -> assign('count', $count);
        return $this -> fetch();
    }

    // 删除类型
    public function del()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $model = Model('Type');
        $res = $model -> remove($id);
        if($res){
            $this -> success('删除类型成功');
        }else{
            $this -> error($model->getError());
        }
    }

    // 修改类型
    public function edit()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $model = Model('Type');
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
}
?>