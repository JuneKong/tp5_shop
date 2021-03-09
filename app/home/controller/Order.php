<?php
/**
 * 定单控制器
 */
namespace app\home\controller;

class Order extends Mypublic
{
    public function __construct()
    {
        parent::__construct();
        $this -> checkLogin();
    }

    // 确认定单
    public function check()
    {
        $model = model('Cart');
        $data = $model -> getCartList();
        $total = $model -> getTotal($data);
        $this -> assign('data', $data);
        $this -> assign('total', $total);
        return $this -> fetch();
    }

    // 提交定单
    public function order()
    {
        $post = input('post.');
        $model = model('Order');
        $res = $model -> order($post);
        if($res){
            // 如果使用支付宝支付，此时则跳转到支付宝的支付页面
            $this -> zifubaoPay($res);
            $this -> success('ok', url('index/index'));
        }else{
            $this -> error($model->getError());
        }
    }

    // 继续支付
    public function pay()
    {
        $order_id = input('order_id');
        dump($order_id);
        $info = model('Order') -> where('id='.$order_id) -> find();
        if(!$info){
            $this -> error('参数错误');
        }
        if($info['pay_status'] == 1){
            $this -> error('该订单已支付！');
        }

        // 请求支付
        $this -> zifubaoPay($info);
    }

    // 支付宝支付
    private function zifubaoPay($info)
    {
        # code...
    }
}
