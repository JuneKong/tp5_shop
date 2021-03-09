<?php
/**
 * 管理员角色模型类
 */

namespace app\shop\model;

use \think\Db;

class AdminRole extends Base{
    // 数据表名称
    protected $table = 'jx_admin_role';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'admin_id', 'role_id','create_time', 'update_time'];

    
}

?>