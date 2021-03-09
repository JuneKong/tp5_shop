<?php
/**
 * 属性控制器
 */

namespace app\shop\controller;

class Attribute extends Base{

    private $_model;
    // 获得属性模型
    private function model()
    {
        if(!$this -> _model){
            $this -> _model = model('Attribute');
        }
        return $this -> _model;
    }
    // 添加类型
    public function add()
    {
        if(request()->isPost()){
            $post = input('post.');
            $res = $this -> model() -> save($post);
            if($res){
                $this -> success('添加属性成功');
            }else{
                $this -> error($this -> model() -> getError());
            }
        }else{
            $type = model('Type') -> select();
            $this -> assign('type', $type);
            return $this -> fetch();
        }
    }

    // 类型列表
    public function showList()
    {
        $list = $this -> model() -> getListData();
        $page = $list -> render();
        $count = $this -> model() -> count();
        $this -> assign('list', $list);
        $this -> assign('page', $page);
        $this -> assign('count', $count);
        return $this -> fetch();
    }

    // 删除类型
    public function dels()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $res = $this -> model() -> remove($id);
        if($res){
            $this -> success('删除属性成功');
        }else{
            $this -> error($this -> model() -> getError());
        }
    }

    // 修改类型
    public function edit()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        if(request()->isPost()){
            $post = input('post.');
            $res = $this -> model() -> save($post, $id);
            if($res){
                $this -> success('修改成功', url('showlist'));
            }else{
                $this -> error($this -> model() -> getError());
            }
        }else{
            $info = $this -> model() -> findOneById($id);
            $type = model('Type') -> select();
            $this -> assign('info', $info);
            $this -> assign('type', $type);
            return $this -> fetch();
        }
    }
}
?>