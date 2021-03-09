<?php
/**
 * 购物车控制器
 */
namespace app\home\controller;

class Cart extends Mypublic
{
    // 加入购物车
    public function add()
    {
        $goods_id = input('post.goods_id');
        $goods_count = input('post.goods_count');
        $attr = input('post.attr/a');//使用助手函数input接受数组时要使用'/a'转换成数组
        $model = model('Cart');
        $res = $model -> addCart($goods_id, $goods_count, $attr);
        if($res){
            $this -> success('加入购物车成功');
        }else{
            $this -> error($model->getError());
        }
    }

    public function test()
    {
        dump(cookie('cart'));
        dump(unserialize(cookie('cart')));
    }

    // 主页
    public function index()
    {
        $model = model('Cart');
        $data = $model -> getCartList();
        $total = $model -> getTotal($data);
        $this -> assign('data', $data);
        $this -> assign('total', $total);
        return $this -> fetch();
    }

    // 删除
    public function del()
    {
        $goods_id = input('goods_id');
        $goods_attr_ids = input('goods_attr_ids');
        $res = model('Cart') -> del($goods_id, $goods_attr_ids);
        if($res){
            $this -> success('删除成功！', url('index'));
        }else{
            $this -> error('删除失败！');
        }
    }

    // 修改数量
    public function setCount()
    {
        $goods_id = input('post.goods_id');
        $goods_count = input('post.goods_count');
        $goods_attr_ids = input('post.goods_attr_ids');
        model('Cart') -> setCount($goods_id, $goods_attr_ids, $goods_count);
    }
}
