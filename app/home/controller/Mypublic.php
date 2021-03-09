<?php
/**
 * 公共控制器
 */
namespace app\home\controller;

use \think\Controller;

class Mypublic extends Controller
{
    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        // 获取分类信息
        $cate = model('app\shop\model\Category') -> getCateData();
        $this -> assign('cate', $cate);
    }

    // 用户是否登录
    public function checkLogin()
    {
        $user_id = session('user_id');
        if(!$user_id){
            $this -> error('请先登录！', url('user/login'));
            exit;
        }
    }
}

