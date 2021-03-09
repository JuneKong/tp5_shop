<?php
/**
 * 商品库存模型类
 */

namespace app\shop\model;

class GoodsNumber extends Base{
    // 数据表名称
    protected $table = 'jx_goods_number';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'goods_id', 'goods_attr_ids', 'goods_number', 'create_time', 'update_time'];

    // 设置商品库存
    public function setNumber($goods_id, $goods_number, $attr)
    {
        if(!$goods_number){
            $this -> error = '请填写库存数量';
            return false;
        }
        // 删除先前的库存信息
        $this -> where('goods_id='.$goods_id) -> delete();
        $has = [];
        foreach ($goods_number as $key => $value) {
            $temp = [];
            foreach ($attr as $k => $v) {
                $temp[] = $v[$key];
            }
            $goods_attr_ids = implode(',', $temp);
            // 去重，防止相同属性信息
            if(in_array($goods_attr_ids, $has)){
                unset($goods_number[$key]);
                continue;
            }
            $has[] = $goods_attr_ids;
            $list[] = [
                'goods_id' => $goods_id,
                'goods_number' => $value,
                'goods_attr_ids' => $goods_attr_ids
            ];
        };
        // 商品总数入库
        $goods_count = array_sum($goods_number);
        model('Goods') -> save([
            'goods_number' => $goods_count,
            ],['id' => $goods_id]);
        return $this -> saveAll($list);
    }
}

?>