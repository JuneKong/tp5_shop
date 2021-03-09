<?php
/**
 * 商品图片模型类
 */

namespace app\shop\model;

class GoodsImg extends Base{
    // 数据表名称
    protected $table = 'jx_goods_img';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'goods_id', 'goods_img', 'goods_thumb', 'create_time', 'update_time'];

    
}

?>