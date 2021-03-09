<?php
/**
 * 商品控制器
 */
namespace app\home\controller;

class Goods extends Mypublic
{
    public function index()
    {
        $id = input('id');
        if($id <= 0){
            $this -> error('参数出错！');
        }
        // 商品详情
        $info = model('app\shop\model\Goods') -> findOneById($id);
        // 商品相册
        $pic = model('app\shop\model\GoodsImg') -> where('goods_id='.$id) -> select();
        // 属性
        $attrModel = model('app\shop\model\GoodsAttr');
        $attrList = $attrModel -> alias('a') 
                    -> join('Attribute b', 'a.attr_id=b.id') 
                    -> field('a.*, b.attr_name, b.type_id, b.attr_type, b.attr_input_type, b.attr_value') 
                    -> where("a.goods_id=$id") 
                    -> select();
        if($attrList){
            foreach ($attrList as $key => $value) {
                if($value['attr_type'] == 1){//唯一属性
                    $union[] = $value;
                }else{//单选属性
                    $attr[$value['attr_id']][] = $value;
                }
            }
        }else{
            $attr = [];
            $union = [];
        }
        // 评论
        $comment = model('Comment') -> getListByGoodsId($id);
        $cPage = $comment -> render();
        // 买家印象
        $impression = model('Impression') -> getListByGoodId($id);
        $this -> assign('info', $info);
        $this -> assign('pic', $pic);
        $this -> assign('attr', $attr);
        $this -> assign('union', $union);
        $this -> assign('comment', $comment);
        $this -> assign('cPage', $cPage);
        $this -> assign('impression', $impression);
        return $this -> fetch();
    }

    // 添加评论
    public function comment()
    {
        $this -> checkLogin();
        $post = input('post.');
        $res = model('Comment') -> save($post);
        if($res){
            $this -> success('评论成功');
        }else{
            $this -> error('评论失败');
        }
    }

    // 设置有用
    public function good()
    {
        $comment_id = input('post.comment_id');
        $model = model('Comment');
        $info = $model -> where('id='.$comment_id) -> find();
        if(!$info){
            return json(['state' => 0, 'msg' => '参数错误']);
        }
        $res = $model -> save(['good_number' => $info['good_number']+1], ['id' => $comment_id]);
        if(!$res){
            return json(['state' => 0, 'msg' => $model->getError()]);
        }else{
            return json(['state' => 1, 'msg' => 'ok', 'data' => $info['good_number']+1]);
        }
    }
}
