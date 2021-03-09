<?php
/**
 * 类型模型类
 */

namespace app\shop\model;

class Attribute extends Base{
    // 数据表名称
    protected $table = 'jx_attribute';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'attr_name', 'type_id', 'attr_type', 'attr_input_type', 'attr_value', 'create_time', 'update_time'];
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'attr_name' => 'require',
            'type_id' => 'require',
            'attr_type' => 'in:1,2',
            'attr_input_type' => 'in:1,2',
        ],
        'msg' => [
            'attr_name' => '属性名称不可为空',
            'type_id' => '类型名称不可为空',
            'attr_type' => '属性类型只能为唯一或单选',
            'attr_input_type' => '属性录入方式只能为手动输入或列表选择',
        ]
    ];

    // 获得显示列表
    public function getListData()
    {
        $list = $this -> paginate(10);
        // 类型显示问题 1：链表查询 2：替换操作
        // 2=> 
        if($list){
            // 1:获得所有类型
            $type = model('Type') -> select();
            // 2:形成以id为键的列表 
            foreach ($type as $key => $value) {
                $typeinfo[$value['id']] = $value;
            }
            // 3:替换
            foreach ($list as $key => $value) {
                $list[$key]['type_id'] = $typeinfo[$value['type_id']]['type_name'];
            }
        }
        return $list;
    }

    // 删除类型
    public function remove($id)
    {
        return $this -> where("id='$id'") -> delete();
    }
}

?>