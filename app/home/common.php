<?php

// 生成分类主页的筛选链接
function cateUrl($name, $value)
{
    $sort = $value;
    if($name == 'attr'){
        if(input('attr') && input('attr') != $value){
            $sort .= ','.input('attr');
            $sort = explode(',', $sort);
            $sort = array_unique($sort);
            $sort = implode(',', $sort);
        }
    }else if(input('attr')){
        $sort .= '&attr='.input('attr');
    }
    

    if($name !== 'sort' && input('sort')){
        $sort .= '&sort='.input('sort');
    }
    if($name !== 'price' && input('price')){
        $sort .= '&price='.input('price');
    }
    return url('Category/index', 'id='.input('id').'&'.$name.'='.$sort).'#filter';
}
