<?php
/**
 * 基础模型类
 */

namespace app\shop\model;

use \think\Model;

class Base extends Model{
    // 数据库配置
    protected $connection = [
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'hostname'        => '127.0.0.1',
        // 数据库名
        'database'        => 'db_shop',
        // 用户名
        'username'        => 'root',
        // 密码
        'password'        => 'root',
        // 端口
        'hostport'        => '3306',
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => 'jx_',
    ];

    /**格式化数据分层显示 */
    protected function getFormatData($list, $id = 0, $level=1, $iscache=true)
    {
        static $array = array();
        // 是否要重置数据
        if(!$iscache){
            $array = array();
        }
        foreach ($list as $value) {
            if($value['parent_id'] == $id){
                $value['level'] = $level;
                $array[] = $value;
                $this -> getFormatData($list, $value['id'], $level+1);
            }
        }
        return $array;
    }

    public function findOneById($id)
    {
        $info = $this -> where('id='.$id) -> find($id);
        return $info;
    }
}

?>