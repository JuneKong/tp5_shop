<?php
/**
 * 首页控制器
 */
namespace app\home\controller;

class Index extends Mypublic
{
    public function index()
    {
        // 首页标示
        $this -> assign('is_show', 1);
        $goodsModel = model('app\shop\model\Goods');
        // 获得热卖商品
        $hot = $goodsModel -> getGoodsByName('is_hot');
        $this -> assign('hot', $hot);
        // 获得推荐商品
        $rec = $goodsModel -> getGoodsByName('is_rec');
        $this -> assign('rec', $rec);
        // 获得新品商品
        $now = $goodsModel -> getGoodsByName('is_now');
        $this -> assign('now', $now);
        // 获得促销商品
        $crazy = $goodsModel -> getSalesGoods();
        $this -> assign('crazy', $crazy);
        // 获取楼层信息
        $floor = model('app\shop\model\Category') -> getFloor();
        $this -> assign('floor', $floor);
        return $this -> fetch();
    }
}
