<?php
/**
 * 角色权限模型类
 */

namespace app\shop\model;

class RoleRule extends Base{
    // 数据表名称
    protected $table = 'jx_role_rule';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'role_id', 'rule_id', 'create_time', 'update_time'];

    // 根据id获得权限id
    public function getRulesById($role_id)
    {
        return $this -> where("role_id='$role_id'") ->select();
    }

    // 设置角色权限
    public function dispatch($data)
    {
        $role_id = $data['id'];
        $this -> where("role_id='$role_id'") -> delete();
        if(!empty($data['rules'])){
            $rules = $data['rules'];
            foreach ($rules as $value) {
                $list[] = [
                    'role_id' => $role_id,
                    'rule_id' => $value
                ];
            }
            $this -> saveAll($list);
        }
        return true;
    }
}

?>