<?php
/**
 * 商品验证类
 */

namespace app\shop\validate;

use think\Validate;

class Goods extends Validate{

    protected $rule = [
        'goods_name' => 'require',
        'cate_id' => 'checkCategory:',//对于自定义规则即便没有参数，也要带"："
        'market_price' => ['regex'=> '/^\d+(\.\d+)?$/'], //正则里如果有|，必须使用数组形式
        'shop_price' => ['regex'=> '/^\d+(\.\d+)?$/'],
        'goods_number' => 'number'
    ];

    protected $message = [
        'goods_name' => '商品名称不可为空',
        'cate_id' => '商品分类必须填写',
        'market_price' => '市场售价格式错误',
        'shop_price' => '本店售价格式错误',
        'goods_number' => '请输入数字'
    ];

    // 验证商品分类
    public function checkCategory($value)
    {
        $value = intval($value);
        if($value > 0){
            return true;
        }
        return false;
    }
}

?>