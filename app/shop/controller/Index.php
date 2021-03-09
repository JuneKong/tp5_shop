<?php
/**
 * 首页控制器
 */

namespace app\shop\controller;

class Index extends Base{

    public function index()
    {
        $menu = $this -> user['menu'];
        $this -> assign('menu', $menu);
        return $this -> fetch();
    }

    public function home()
    {
        return $this -> fetch();
    }
}
?>