<?php
/**
 * 定单模型类
 */

namespace app\home\model;

use app\shop\model\Base;
use think\Db;

class Order extends Base{
    // 数据表名称
    protected $table = 'jx_order';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'user_id', 'total_price', 'pay_status', 'name', 'address', 'tel', 'create_time', 'update_time'];
    
    // 定单入库
    public function order($info)
    {
        // 1.获得购物车信息
        $cartModel = model('Cart');
        $cart = $cartModel -> getCartList();
        if(!$cart){
            $this -> error = '购物车中无商品';
            return false;
        }
        // 2.对商品进行库存检查
        foreach ($cart as $key => $value) {
            $goods_number = $cartModel -> checkGoodsNumber($value['goods_id'], $value['goods_count'], $value['goods_attr_ids']);
            if(!$goods_number){
                $this -> error = '商品库存不足：'.$value['goods']['goods_name'];
                return false;
            }
        }
        // 3.向定单表存入数据
        $user_id = session('user_id');
        $total = $cartModel -> getTotal($cart);
        $data = [
            'user_id' => $user_id,
            'total_price' => $total['price'],
            'name' => $info['name'],
            'address' => $info['address'],
            'tel' => $info['tel']
        ];
        $this -> save($data);
        // 保存后获得自增长的定单标示id
        $order_id = $this -> id;
        // 4.向定单商品表存入商品数据
        foreach ($cart as $key => $value) {
            $order_goods[] = [
                'order_id' => $order_id,
                'goods_id' => $value['goods_id'],
                'goods_attr_ids' => $value['goods_attr_ids'],
                'price' => $value['goods']['shop_price'],
                'goods_count' => $value['goods_count']
            ];
        }
        model('OrderGoods') -> saveAll($order_goods);
        $goodsModel = model('app\shop\model\Goods');
        // 5.减少商品的库存量和增加销售数量
        foreach ($cart as $key => $value) {
            // 商品库存总数减少
            $goodsModel -> where('id='.$value['goods_id']) -> setDec('goods_number', $value['goods_count']);
            // 商品单选属性库存减少
            if($value['goods_attr_ids']){
                $where = 'goods_id='.$value['goods_id'].' and goods_attr_ids='."'".$value['goods_attr_ids']."'";
                model('app\shop\model\GoodsNumber') -> where($where) -> setDec('goods_number', $value['goods_count']);
            }
            // 商品销售数量
            $goodsModel -> where('id='.$value['goods_id']) -> setInc('sale_number', $value['goods_count']);
        }
        // 6.清空购物车数据
        $cartModel -> where('user_id='.$user_id) -> delete(); 
        // 返回定单信息
        $data['id'] = $order_id;
        return $data;
    }
}
