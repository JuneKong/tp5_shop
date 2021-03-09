<?php
/**
 * 角色模型类
 */

namespace app\shop\model;

class Role extends Base{
    // 数据表名称
    protected $table = 'jx_role';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'role_name', 'create_time', 'update_time'];
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'role_name' => 'require|unique:role',
        ],
        'msg' => [
            'role_name.require' => '角色名称不可为空',
            'role_name.unique' => '角色名称重复',
        ]
    ];

    // 删除角色
    public function remove($id)
    {
        return $this -> where("id='$id'") -> delete();
    }
}

?>