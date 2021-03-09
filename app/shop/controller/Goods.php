<?php
/**
 * 商品控制器
 */

namespace app\shop\controller;

class Goods extends Base{
    
    private $models = [];

    /**获得模型实例 */
    private function getModel($name)
    {
        if(empty($this -> models[$name])){
            $this -> models[$name] = Model($name);
        }
        return $this -> models[$name];
    }

    // 添加
    public function add()
    {
        if(request()->isPost()){
            $post = input('post.');
            $res = $this -> getModel('Goods') -> validate(true) -> save($post);
            if($res){
                $this -> success('添加成功!');
            }else{
                $this -> error($this -> getModel('Goods') -> getError());
            }
        }else{
            $data = $this -> getModel('Category') -> getCateData();
            $type = model('Type') -> select();
            $this -> assign('data', $data);
            $this -> assign('type', $type);
            return $this -> fetch();
        }
    }

    // 商品列表显示
    public function showList()
    {
        $cate = $this -> getModel('Category') -> getCateData();
        $list = $this -> getModel('Goods') -> getListData();
        $page = $list -> render();
        $count = $this -> getModel('Goods') -> count();
        $this -> assign('cate', $cate);
        $this -> assign('list', $list);
        $this -> assign('page', $page);
        $this -> assign('count', $count);
        return $this -> fetch();
    }

    // 查看商品介绍
    public function showContent()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $info = $this -> getModel('Goods') -> find($id);
        if($info){
            return $info['goods_body'];
        }
    }

    // 商品伪删除
    public function dels()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $info = $this -> getModel('Goods') -> dels($id);
        if($info){
            $this -> success('删除成功！');
        }else{
            $this -> error('删除失败');
        }
    }

    // 修改商品信息
    public function edit()
    {
        $id = input('id');
        if(request() -> isPost()){
            $post = input('post.');
            $res = $this -> getModel('Goods') -> updateData($post);
            if($res){
                $this -> success('修改成功!', url('showList'));
            }else{
                $this -> error($this -> getModel('Goods') -> getError());
            }
        }else{
            if($id <= 0){
                $this -> error('参数错误');
            }
            $info = $this -> getModel('Goods') -> findOneById($id);
            $cate = $this -> getModel('Category') -> getCateData();
            $ext_cate = $this -> getModel('GoodsCate') -> where('goods_id='.$id) -> select();
            if(!$ext_cate){//当无扩展分类时，防止默认情况没有扩展选择，手动加个选择
                $ext_cate[] = 0;
            }
            // 获得类型
            $type = $this -> getModel('Type') -> select();
            // 获得属性
            $attr = $this -> getModel('GoodsAttr') 
                          -> alias('a') 
                          -> join('Attribute b', 'a.attr_id = b.id') 
                          -> field('a.*, b.attr_name, b.type_id, attr_type, b.attr_input_type, b.attr_value') 
                          -> where('goods_id='.$id) 
                          -> select();
            $attrList = [];
            foreach ($attr as $key => $value) {
                // 格式化默认值
                $attr[$key]['attr_value'] = explode(',', $value['attr_value']);
                // 格式化属性数据
                $attrList[$value['attr_id']][] = $value;
            }
            // 获得图片
            $img = $this -> getModel('GoodsImg') -> where('goods_id='.$id) -> select();
            $this -> assign('info', $info);
            $this -> assign('cate', $cate);
            $this -> assign('ext_cate', $ext_cate);
            $this -> assign('type', $type);
            $this -> assign('attr', $attrList);
            $this -> assign('img', $img);
            return $this -> fetch();
        }
    }

    // 商品回收站列表
    public function trash()
    {
        $cate = $this -> getModel('Category') -> getCateData();
        $list = $this -> getModel('Goods') -> getListData(0);
        $page = $list -> render();
        $count = $this -> getModel('Goods') -> count();
        $this -> assign('cate', $cate);
        $this -> assign('list', $list);
        $this -> assign('page', $page);
        $this -> assign('count', $count);
        return $this -> fetch();
    }

    // 商品状态还原
    public function restore()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $info = $this -> getModel('Goods') -> dels($id, 1);
        if($info){
            $this -> success('还原成功！');
        }else{
            $this -> error('还原失败');
        }
    }

    // 商品信息销毁（彻底删除）
    public function remove()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $res = $this -> getModel('Goods') -> remove($id);
        if($res){
            $this -> success('删除成功', url('trash'));
        }else{
            $this -> error('删除失败');
        }
    }

    // 显示商品类型属性
    public function showAttr()
    {
        $type_id = input('post.type_id');
        if($type_id <= 0){
            echo '暂无数据';exit;
        }
        $attr = model('Attribute') -> where('type_id='.$type_id) -> select();
        foreach ($attr as $key => $value) {
            if($value['attr_input_type'] == 2){
                $attr[$key]['attr_value'] = explode(',', $value['attr_value']);
            }
        }
        $this -> assign('attr', $attr);
        return $this -> fetch();
    }

    // 删除商品图片
    public function delImg()
    {
        $img_id = input('post.img_id');
        if($img_id <= 0){
            return json(['state' => 0, 'msg' => '参数错误']);
        }
        $model = $this -> getModel('GoodsImg');
        // 删除缓存图片
        $imgInfo = $model -> where('id='.$img_id) -> find();
        if(!$imgInfo){
            return json(['state' => 0, 'msg' => '参数错误']);
        }
        unlink($imgInfo['goods_img']);
        unlink($imgInfo['goods_thumb']);
        // 删除数据库图片信息
        $model -> where('id='.$img_id) -> delete();
        return json(['state' => 1, 'msg' => 'ok']);
    }

    // 设置商品库存
    public function setNumber()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数错误');
        }
        $model = $this -> getModel('GoodsNumber');
        if(request()->isPost()){
            $post = input('post.');
            if(empty($post['attr'])){
                // 不存在单选选项的属性库存设置
                $this -> getModel('Goods') -> save([
                    'goods_number' => $post['goods_number'],
                    ],['id' => $id]);
                $this -> success('库存设置成功', url('showList'));
                exit;
            }
            $res = $model -> setNumber($id, $post['goods_number'], $post['attr']);
            if($res === false){
                $this -> error($model -> getError());
            }else{
                $this -> success('库存设置成功', url('showList'));
            }
        }else{            
            // 获得商品属性的单选属性
            $attr = $this -> getModel('GoodsAttr') -> getSingleAttr($id);
            if(!$attr){
                // 不存在单选选项的属性库存设置
                $info = $this -> getModel('Goods') -> where('id='.$id) -> find();
                $this -> assign('number', $info['goods_number']);
                return $this -> fetch('noSingleAttr');
            }
            $kucun = $model -> where('goods_id='.$id) -> select();
            if(!$kucun){
                $kucun = ['goods_number' => 0];
            }
            $this -> assign('attr', $attr);
            $this -> assign('kucun', $kucun);
            return $this -> fetch();
        }
    }
}
?>