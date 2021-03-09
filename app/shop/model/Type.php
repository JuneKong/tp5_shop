<?php
/**
 * 类型模型类
 */

namespace app\shop\model;

class Type extends Base{
    // 数据表名称
    protected $table = 'jx_type';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'type_name', 'create_time', 'update_time'];
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'type_name' => 'require|unique:type',
        ],
        'msg' => [
            'type_name.require' => '类型名称不可为空',
            'type_name.unique' => '类型名称重复',
        ]
    ];

    // 删除类型
    public function remove($id)
    {
        return $this -> where("id='$id'") -> delete();
    }
}

?>