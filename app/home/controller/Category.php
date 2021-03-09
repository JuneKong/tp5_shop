<?php
/**
 * 分类控制器
 */
namespace app\home\controller;

class Category extends Mypublic
{
    public function index()
    {
        $id = input('id');
        // dump(input(''));die;
        if($id <= 0){
            $this -> error('参数出错！');
        }
        $info = model('\app\shop\model\Goods') -> getList();
        $page = $info['data'] -> render();
        $this -> assign('info', $info);
        $this -> assign('page', $page);
        return $this -> fetch();
    }
}
