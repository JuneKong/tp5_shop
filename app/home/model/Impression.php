<?php
/**
 * 评论模型类
 */

namespace app\home\model;

use app\shop\model\Base;

class Impression extends Base{
    // 数据表名称
    protected $table = 'jx_impression';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'goods_id', 'name', 'count', 'create_time', 'update_time'];
    

    // 根据商品id获得列表信息
    public function getListByGoodId($goods_id, $count=8)
    {
        $data = $this -> where('goods_id='.$goods_id) -> order('count desc') -> limit($count) -> select();
        return $data;
    }
}
