<?php
/**
 * 商品分类控制器
 */

namespace app\shop\controller;

class Category extends Base{
    //添加
    public function add()
    {
        if(request() -> isPost()){
            $post = input('post.');
            $model = Model('Category');
            $res = $model -> save($post);
            if($res == false){
                $this -> error($model -> getError());
            }else{
                $this -> success('添加成功', url('showList'));
            }          
        }else{
            $cate = Model('Category') -> getCateData();
            $this -> assign('cate', $cate);
            return $this -> fetch();
        }
    }

    //列表显示
    public function showList()
    {
        $data = Model('Category') -> getCateData();
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
        $model = Model('Category');
        $res = $model -> dels($id);
        if($res){
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
        $model = Model('Category');
        if(request()->isPost()){
            $post = input('post.');
            $res = $model -> updateData($post);
            if($res){
                $this -> success('编辑成功', 'category/showList');
            }else{
                $this -> error($model->getError());
            }
        }else{
            $data = $model -> findOneById($id);
            $cate = $model -> getCateData();
            $this -> assign('data', $data);
            $this -> assign('cate', $cate);
            return $this -> fetch();
        }
    }
}
?>