<?php
/**
 * 商品模型类
 */

namespace app\shop\model;

class Goods extends Base{
    // 数据表名称
    protected $table = 'jx_goods';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'goods_name', 'goods_sn', 'cate_id', 'market_price', 'shop_price', 'goods_body', 'goods_img', 'goods_thumb', 'is_hot', 'is_rec', 'is_now', 'isdel', 'is_sale', 'type_id', 'goods_number', 'cx_price', 'start', 'end', 'plcount', 'sale_count', 'create_time', 'update_time'];

    protected static function init()
    {
        // ***模型事件只可以在调用模型的方法才能生效，使用查询构造器通过Db类操作是无效的
        // 插入前
        self::beforeInsert(function ($goods) {
            //处理货号问题
            if(!$goods['goods_sn']){
                $goods['goods_sn'] = 'jx_'.uniqid();
            }else{
                $res = $goods -> where('goods_sn', $goods['goods_sn']) -> find();
                if($res){
                    $goods -> error = '货号重复。';
                    return false;
                }
            }
            // 实现图片上传
            $imgInfo = $goods -> uploadImage();
            if($imgInfo){
                $goods['goods_img'] = $imgInfo['img'];
                $goods['goods_thumb'] = $imgInfo['thumb'];
            }
            // 处理促销时间
            if(!empty($goods['cx_price']) && $goods['cx_price'] > 0){
                // 设置促销价格
                $goods['start'] = strtotime($goods['start']);
                $goods['end'] = strtotime($goods['end']);
            }else{
                unset($goods['start']);
                unset($goods['end']);
            }
        });

        // 插入后
        self::afterInsert(function ($goods) {
            $goods_id = $goods['id'];
            $goods_cate = $goods['goods_cate'];
            // 去重
            $goods -> addExtCate($goods_id, $goods_cate);

            // 商品属性入库
            if(!empty($goods['attr'])){
                $attr = $goods['attr'];
                $this -> addGoodsAttr($attr, $goods_id);
            }
            
            // 相册图片入库
            $this -> addGoodsImg($goods_id);
        });
    }

    /**获取商品列表 */
    public function getListData($isdel = 1)
    {
        $post = input('post.');
        $where = 'isdel='.$isdel;
        // 拼接查询条件
        if($post){
            // 1.处理分类
            if($post['cate_id'] != 0){
                $where .= ' and cate_id='.$post['cate_id'];
            }
            // 2.处理推荐，热卖，新品
            if($post['intro_type'] == 'is_hot' || $post['intro_type'] == 'is_rec' || $post['intro_type'] == 'is_now'){
                $where .= ' and '.$post['intro_type'].'=1';
            }
            // 3.处理销售状态
            if($post['is_sale'] != null){
                $where .= ' and is_sale='.$post['is_sale'];
            }
            // 4.处理关键字
            if($post['keywords']){
                $where .= " and goods_name like '%". $post['keywords']."%'";
            }
        }
        $list = $this -> where($where) -> paginate(10);
        return $list;
    }

    /**商品的伪删除 */
    public function dels($id, $isdel=0)
    {
        return $this -> where('id='.$id) -> setField('isdel', $isdel);
    }

    /**商品信息更新 */ 
    public function updateData($data)
    {
        $id = $data['id'];
        // 1.货号唯一问题
        $goods_sn = $data['goods_sn'];
        if(!$goods_sn){
            $goods_sn = 'jx_'.uniqid();
        }else{
            $res = $this -> where("goods_sn='$goods_sn' and id!='$id'") -> find();
            if($res){
                $this -> error = '货号重复。';
                return false;
            }
        }
        // 2.扩展分类
        // 删除之前保存的扩展分类
        Model('GoodsCate') -> where('goods_id='.$id) -> delete();
        // 新加当前选择的扩展分类
        $this -> addExtCate($id, $data['goods_cate']);
        
        // 3.处理图片
        $imgInfo = $this -> uploadImage();
        if($imgInfo){
            $data['goods_img'] = $imgInfo['img'];
            $data['goods_thumb'] = $imgInfo['thumb'];
        }

        // 4.处理属性
        // 删除之前保存的属性
        model('GoodsAttr') -> where('goods_id='.$id) -> delete();
        // 新加当前选择的属性
        if(!empty($data['attr'])){
            $attr = $data['attr'];
            $this -> addGoodsAttr($attr, $id);
        }

        // 5.处理图片相册
        $this -> addGoodsImg($id);

        // 6.处理促销时间
        if(!empty($data['cx_price']) && $data['cx_price'] > 0){
            // 设置促销价格
            $data['start'] = strtotime($data['start']);
            $data['end'] = strtotime($data['end']);
        }else{
            unset($data['start']);
            unset($data['end']);
        }

        return $this -> save($data, $data['id']);
    }

    /**添加商品扩展 */ 
    public function addExtCate($goods_id, $ext)
    {
        $goods_cate = array_unique($ext);// 去重
        foreach ($goods_cate as $value) {
            if($value != 0){
                $list[] = ['goods_id' => $goods_id, 'cate_id' => $value];
            }
        }
        if(!empty($list)){
            Model('GoodsCate') -> saveAll($list);
        }
    }

    /**图片上传 */ 
    public function uploadImage()
    {
        $image = request() -> file('image');
        if($image){
            $info = $image -> move(ROOT_PATH . FILE_UPLOADS_PATH);
            if($info){
                $savename = $info -> getSaveName();
                $goods_img = 'uploads' . DS . $savename;
                $path = explode(DS, $savename);
                // 缩略图
                $thumb = \think\Image::open($image);
                $thumb -> thumb(200,200) -> save(ROOT_PATH . FILE_UPLOADS_PATH . $path[0] . DS ."thumb_" . $path[1]);
                $goods_thumb = 'uploads' . DS . $path[0] . DS .'thumb_' . $path[1];
                return ['img' => $goods_img, 'thumb' => $goods_thumb];
            }else{
                $this -> error = $image -> getError();
                return false;
            }
        }
        return false;
    }

    /**销毁（彻底删除） */
    public function remove($id)
    {
        // ***注意：在事务操作的时候，确保你的数据库连接是相同的。
        $db = $this -> getQuery();
        $db->startTrans();
        try {
            // 1.删除上传照片
            $info = $this -> findOneById($id);
            if(!$info){
                return false;
            }
            // 删除文件
            if($info['goods_img'] && file_exists($info['goods_img'])){
                unlink($info['goods_img']);
            }
            if($info['goods_thumb'] && file_exists($info['goods_thumb'])){
                unlink($info['goods_thumb']);
            }
            // 2.删除商品的扩展分类
            Model('GoodsCate') -> where('goods_id='.$id) -> delete();
            // 3.删除goods表上的商品
            $this -> where('id='.$id) -> delete();
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**添加属性 */
    public function addGoodsAttr($attr, $goods_id)
    {
        if($attr){
            foreach ($attr as $key => $value) {
                // 属性值去重
                $value = array_unique($value);
                foreach ($value as $val) {
                    $attrList[] = [
                        'goods_id' => $goods_id,
                        'attr_id' => $key,
                        'attr_values' => $val
                    ];
                }
            }
            model('GoodsAttr') -> saveAll($attrList);
        }
    }

    /**添加图片相册 */
    public function addGoodsImg($goods_id)
    {
        $image = request() -> file('pic');
        if($image){
            foreach ($image as $key => $value) {
                $info = $value -> move(ROOT_PATH . FILE_UPLOADS_PATH);
                if($info){
                    $savename = $info -> getSaveName();
                    $goods_img = 'uploads' . DS . $savename;
                    $path = explode(DS, $savename);
                    // 缩略图
                    $thumb = \think\Image::open($value);
                    $thumb -> thumb(100,100) -> save(ROOT_PATH . FILE_UPLOADS_PATH . $path[0] . DS ."thumb_" . $path[1]);
                    $goods_thumb = 'uploads' . DS . $path[0] . DS .'thumb_' . $path[1];
                    $imgList[] = [
                        'goods_id' => $goods_id,
                        'goods_img' => $goods_img, 
                        'goods_thumb' => $goods_thumb
                    ];
                }else{
                    $this -> error = $value -> getError();
                }
            }
            if($imgList){
                model('GoodsImg') -> saveAll($imgList);
            }
        }
    }


    // 根据字段名获取商品信息
    public function getGoodsByName($name)
    {
        $where = 'is_sale=1 and '.$name.'=1';
        $data = $this -> where($where) -> limit(5) -> select();
        return $data;
    }

    // 获得促销商品
    public function getSalesGoods($limit=5)
    {
        $where = 'is_sale=1 and cx_price>0 and start<='.time().' and end>'.time();
        $data = $this -> where($where) -> limit($limit) -> select();
        return $data;
    }

    // 根据分类id获得商品列表
    public function getList()
    {
        $cate_id = input('id');
        // 获得分类id的子分类
        $children = model('\app\shop\model\Category') -> getChildrenById($cate_id);
        $children[] = $cate_id;
        $children = implode(',', $children);

        $where = "is_sale=1 and isdel=1 and cate_id in ($children)";
        // 获得价格区间
        // 获得所有商品的id组合
        $info = $this -> field('max(shop_price) max_price, min(shop_price) min_price, count(id) count, group_concat(id) goods_ids') -> where($where) -> find();
        $price = [];
        if($info['count'] > 1){
            $gap = 0;
            if($info['max_price'] < 500){
                $gap = 1;
            }else if($info['max_price'] < 1000){
                $gap = 2;
            }else if($info['max_price'] < 2000){
                $gap = 3;
            }else if($info['max_price'] < 5000){
                $gap = 4;
            }else if($info['max_price'] < 10000){
                $gap = 5;
            }else{
                $gap = 6;
            }
            $zl = ceil($info['max_price']/$gap);
            $frist = 0;
            for ($i=0; $i < $gap; $i++) { 
                $price[] = $frist.'-'.($frist + $zl);
                $frist += $zl;
            }
        }

        // 获得属性
        $goodsAttrModel =  model('\app\shop\model\GoodsAttr');
        $attribute = $goodsAttrModel -> alias('a') -> field('b.id, b.attr_name, a.attr_values') -> join('Attribute b', 'a.attr_id=b.id') -> where('goods_id in ('.$info['goods_ids'].')') -> select();
        $attrList = [];
        foreach ($attribute as $key => $value) {
            $attrList[$value['id']][] = $value;
        }
        // 查询 价格区间
        if(input('price')){
            $p = input('price');
            if($p !== '0'){//0代表不限
                $p = str_replace('-', ' and ', $p);
                $where .= ' and shop_price between '.$p;
            }
        }

        // 查询 属性
        if(input('attr')){
            $attr = input('attr');
            $attr = explode(',', $attr);
            $goods = $goodsAttrModel -> field('group_concat(goods_id) goods_ids') -> where(['goods_id' => ['in', $attr]]) -> find();
            if($goods['goods_ids']){
                $where .= ' and id in ('.$goods['goods_ids'].')';
            }
        }

        //排序 销量/价格/评论数/上架时间
        $sort = input('sort') ? : 'sale_number';
        $data = $this -> where($where) -> order($sort.' desc') -> paginate(1, false, ['fragment' => 'filter']);
        return ['data' => $data, 'price' => $price, 'attr' => $attrList];
    }


}

?>