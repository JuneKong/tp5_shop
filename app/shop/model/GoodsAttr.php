<?php
/**
 * 商品属性模型类
 */

namespace app\shop\model;

use \think\Db;

class GoodsAttr extends Base{
    // 数据表名称
    protected $table = 'jx_goods_attr';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'goods_id', 'attr_id', 'attr_values','create_time', 'update_time'];


    // 获得商品属性的单选属性及信息
    public function getSingleAttr($goods_id)
    {
        $data = $this -> alias('a') 
                -> join('Attribute b', 'a.attr_id=b.id') 
                -> field('a.*, b.attr_name, b.type_id, b.attr_type, b.attr_input_type, b.attr_value') 
                -> where("a.goods_id=$goods_id and attr_type=2") 
                -> select();
        $list = [];
        foreach ($data as $key => $value) {
            $list[$value['attr_id']][] = $value;
        }
        return $list;
    }

    // 获取商品属性唯一属性
    public function getUnion($goods_id)
    {
        $data = $this -> alias('a') 
                -> join('Attribute b', 'a.attr_id=b.id') 
                -> field('a.*, b.attr_name, b.type_id, b.attr_type, b.attr_input_type, b.attr_value') 
                -> where("a.goods_id=$goods_id and attr_type=1") 
                -> select();
        return $data;
    }
}

?>