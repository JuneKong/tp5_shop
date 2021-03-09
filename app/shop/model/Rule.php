<?php
/**
 * 权限模型类
 */

namespace app\shop\model;

class Rule extends Base{
    // 数据表名称
    protected $table = 'jx_rule';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'rule_name', 'module_name', 'controller_name', 'action_name', 'parent_id', 'is_show', 'create_time', 'update_time'];
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'rule_name' => 'require|unique:rule',
            'module_name' => 'require',
            'controller_name' => 'require',
            'action_name' => 'require',
        ],
        'msg' => [
            'rule_name.require' => '权限名称不可为空',
            'rule_name.unique' => '权限名称重复',
            'module_name.require' => '模块名称不可为空',
            'controller_name.require' => '控制器名称不可为空',
            'action_name.require' => '方法名称不可为空',
        ]
    ];

    /**获得分类格式化数据 */
    public function getRuleData($id=0)
    {
        $list = $this -> select();
        $res = $this -> getFormatData($list, $id);
        return $res;
    }

    //删除
    public function dels($rule_id)
    {
        // 获得分类id的的子分类列表
        $list = $this -> where('parent_id='.$rule_id) -> find();
        if($list){
            return false;
        }
        return $this -> where('id='.$rule_id) -> delete();
    }

    // 编辑更新数据
    public function updateData($data)
    {
        // 注意：修改的时候不可以将上级权限设置为自己和其子权限
        $pid = $data['parent_id'];
        // 1.上级权限不可为自己
        if($data['id'] == $pid){
            $this -> error = '上级权限不可为自己和其子权限';
            return false;
        }
        // 2.获得子权限
        $info = $this -> getRuleData($data['id']);
        foreach ($info as $key => $value) {
            if($pid == $value['id']){
                $this -> error = '上级权限不可为自己和其子权限';
                return false;
            }
        }
        return $this -> save($data, $data['id']);
    }

}

?>