<?php
/**
 * 评论模型类
 */

namespace app\home\model;

use app\shop\model\Base;

class Comment extends Base{
    // 数据表名称
    protected $table = 'jx_comment';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'user_id', 'goods_id', 'content', 'star', 'good_number','create_time', 'update_time'];
    
    protected static function init()
    {
        // ***模型事件只可以在调用模型的方法才能生效，使用查询构造器通过Db类操作是无效的
        // 插入数据前
        self::beforeInsert(function ($comment) {
            $user_id = session('user_id');
            $comment['user_id'] = $user_id;
        });

        // 插入数据后
        self::afterInsert(function ($impression)
        {
            // 印象入库
            $model = model('Impression');
            if(!empty($impression['old'])){
                $old = $impression['old'];
                $old = implode(',', $old);
                $model -> where("id in ($old)") -> setInc('count');
            }            
            $name = $impression['name'];
            // 转换成数组
            $name = explode(',', $name);
            // 数组去重
            $name = array_unique($name);
            $list = [];
            foreach ($name as $key => $value) {
                if(!$value){
                    continue;
                }
                $where = [
                    'goods_id' => $impression['goods_id'],
                    'name' => $value
                ];
                $res = $model -> where($where) -> find();
                if($res){
                    $model -> where($where) -> setInc('count');
                }else{
                    $where['count'] = 1;
                    $list[] = $where;
                }
            }
            if($list){
                $model -> saveAll($list);
            }
            // 商品评论数更新
            model('\app\shop\model\Goods') -> where('id='.$impression['goods_id']) -> setInc('plcount');
        });
    }

    // 根据商品id获得评论列表
    public function getListByGoodsId($goods_id, $page=2)
    {
        $data = $this -> alias('a') 
                -> field('a.*,b.username') 
                -> join('user b', 'a.user_id=b.id') 
                -> where('goods_id='.$goods_id) 
                -> paginate($page, false, ['fragment' => 'detail']);
        return $data;
    }
}
