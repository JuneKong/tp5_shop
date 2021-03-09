<?php
/**
 * 定单商品模型类
 */

namespace app\home\model;

use app\shop\model\Base;

class OrderGoods extends Base{
    // 数据表名称
    protected $table = 'jx_order_goods';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'order_id', 'goods_id', 'goods_attr_ids', 'price', 'goods_count', 'create_time', 'update_time'];
    
}
