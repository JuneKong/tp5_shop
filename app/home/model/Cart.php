<?php
/**
 * 购物车模型类
 */

namespace app\home\model;

use app\shop\model\Base;

class Cart extends Base{
    // 数据表名称
    protected $table = 'jx_cart';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'user_id', 'goods_id', 'goods_attr_ids', 'goods_count','create_time', 'update_time'];
    

    // 加入购物车
    public function addCart($goods_id, $goods_count, $attr)
    {
        if($attr){
            // 对属性进行小到大的排序，为了之后对于库存查询更方便
            sort($attr);
            // 转换成字符串的格式
            $goods_attr_ids = implode(',',$attr);
        }else{
            $goods_attr_ids = '';
        }
        // 检查库存量是否足够
        $count = $this -> checkGoodsNumber($goods_id, $goods_count, $goods_attr_ids);
        if(!$count){
            $this -> error = '库存量不足';
            return false;
        }
        // 判断用户是否登入
        $user_id = session('user_id');
        if($user_id){
            // 用户登录，直接操作数据库购物车表
            $where = array(
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'goods_attr_ids' => $goods_attr_ids
            );
            $info = $this -> where($where) -> find();
            // 数据表中有当前属性的商品，直接修改数量值
            if($info){
                $tmp_count = $info['goods_count'] + $goods_count;
                return $this -> save(['goods_count' => $tmp_count], $where);
            }else{
                // 数据表中不存在当前属性的商品，直接插入
                $where['goods_count'] = $goods_count;
                return $this -> save($where); 
            }
        }else{
            // 用户没有登录，操作cookie
            // 对于php中数组转换成字符串是通过序列化操作，因此获取cart数组要先反序列化
            $cart = unserialize(cookie('cart'));
            $key = $goods_id.'-'.$goods_attr_ids;
            if($cart && array_key_exists($key, $cart)){
                // 如果缓存中存在，则直接更新数量即可
                $cart[$key] += $goods_count;
            }else{
                $cart[$key] = $goods_count;
            }
            // 更新完数量之后，要把新的数据再次写入进缓存中
            cookie('cart', serialize($cart));
            return true;
        }
    }

    // 检查库存量
    public function checkGoodsNumber($goods_id, $goods_count, $goods_attr_ids)
    {
        // 先检查总的库存量是否充足
        $goods = model('app\shop\model\Goods') -> where("id=$goods_id") -> find();
        if($goods['goods_number'] < $goods_count){
            return false;
        }
        // 检查当前属性商品的库存量
        if($goods_attr_ids){
            $where = "goods_id=$goods_id and goods_attr_ids='$goods_attr_ids'";
            $info = model('app\shop\model\GoodsNumber') -> where($where) -> find();
            if(!$info || $info['goods_number'] < $goods_count){
                return false;
            }
        }
        return true;
    }

    // cookie缓存中转入数据库
    public function cookie2Db()
    {
        $user_id = session('user_id');
        if(!$user_id){//用户未登录
            return false;
        }
        $cart = unserialize(cookie('cart'));//$cart = [{'id-attr' => 'count'}]
        if($cart){
            foreach ($cart as $key => $value) {
                $tmp = explode('-', $key);
                $where = [
                    'user_id' => $user_id,
                    'goods_id' => $tmp[0],
                ];
                if($tmp[1]){
                    $where['goods_attr_ids'] = $tmp[1];
                }
                $info = $this -> where($where) -> find();
                if($info){
                    $this -> save(['goods_count' => $info['goods_count'] + $value], $where);
                }else{
                    $where['goods_count'] = $value;
                    $this -> save($where);
                }
            }
            cookie('cart', null);
        }
        return true;
    }

    // 获取购物车信息
    public function getCartList()
    {
        // 1.获取购物车信息
        $user_id = session('user_id');
        $data = [];
        if($user_id){
            // 用户已登录，从数据库中获取购物车信息
            $data = $this -> where("user_id=$user_id") -> select();
            //将返回的结果集转换成数组
            $data = collection($data) ->  toArray();
        }else{
            // 用户未登录，从cookie中获取信息
            $cart = unserialize(cookie('cart'));
            foreach ($cart as $key => $value) {
                $temp = explode('-', $key);
                $data[] = [
                    'goods_id' => $temp[0],
                    'goods_attr_ids' => $temp[1],
                    'goods_count' => $value
                ];
            }
        }
        // 2.根据购物车信息中的商品id获得商品信息
        $goodsModel = model('app\shop\model\Goods');
        $goodsAttrModel = model('app\shop\model\GoodsAttr');
        foreach ($data as $key => $value) {
            $goods = $goodsModel -> where('id='.$value['goods_id']) -> find();
            // 商品处于促销阶段
            if($goods['cx_price'] > 0 && $goods['start'] <= time() && $goods['end'] > time()){
                $goods['shop_price'] = $goods['cx_price'];
            }
            $data[$key]['goods'] = $goods;
            // 3.根据商品的属性id组合,获取商品的属性信息
            if($value['goods_attr_ids']){
                $data[$key]['attr'] = $goodsAttrModel -> alias('a') 
                                -> join('Attribute b', 'a.attr_id=b.id') 
                                -> field('a.*, b.attr_name')
                                -> where("a.id in (".$value['goods_attr_ids'].")")
                                -> select();
            }else{
                $data[$key]['attr'] = '';
            }
        }
        return $data;
    }

    // 获得总数
    public function getTotal($data)
    {
        $count = $price = 0;
        foreach ($data as $key => $value) {
            $count += $value['goods_count'];
            $price += $value['goods_count'] * $value['goods']['shop_price'];
        }   
        return array('count'=>$count,'price'=>$price);
    }

    // 删除
    public function del($goods_id, $goods_attr_ids)
    {
        $user_id = session('user_id');
        if($user_id){
            // 用户已登录
            $where = "user_id=$user_id and goods_id=$goods_id";
            if($goods_attr_ids){
                $where .= " and goods_attr_ids='$goods_attr_ids'";
            }
            return $this -> where($where) -> delete();
        }else{
            // 用户未登录
            $cart = unserialize(cookie('cart'));
            $key = $goods_id.'-'.$goods_attr_ids;
            if($cart && array_key_exists($key, $cart)){
                unset($cart[$key]);
            }
            // 删除后要把新数据重新写入cookie
            cookie('cart', serialize($cart));
        }
        return true;
    }

    // 更新商品数量
    public function setCount($goods_id, $goods_attr_ids, $goods_count)
    {
        // 数量小于等于0是不予修改
        if($goods_count <= 0){
            return false;
        }
        $user_id = session('user_id');
        if($user_id){
            // 用户已登录
            $where = "user_id=$user_id and goods_id=$goods_id";
            if($goods_attr_ids){
                $where .= " and goods_attr_ids='$goods_attr_ids'";
            }
            return $this -> save(['goods_count'=>$goods_count], $where);
        }else{
            // 用户未登录
            $cart = unserialize(cookie('cart'));
            $key = $goods_id.'-'.$goods_attr_ids;
            if($cart && array_key_exists($key, $cart)){
                $cart[$key] = $goods_count;
            }
            // 删除后要把新数据重新写入cookie
            cookie('cart', serialize($cart));
        }
        return true;
    }
}
