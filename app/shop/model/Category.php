<?php
/**
 * 基础模型类
 */

namespace app\shop\model;

class Category extends Base{
    // 数据表名称
    protected $table = 'jx_category';
    // 数据表字段信息 留空则自动获取
    // **注：更改数据表字段一定要同步修改，不然无法看到任何修改变化
    protected $field = ['id', 'cname', 'parent_id', 'isrec', 'create_time', 'update_time'];
    // 字段验证规则
    protected $validate = [
        'rule' => [
            'cname' => 'require',
        ],
        'msg' => [
            'cname' => '分类名称不可为空',
        ]
    ];

    /**获得分类格式化数据 */
    public function getCateData($id=0)
    {
        $list = $this -> select();
        $res = $this -> getFormatData($list,$id);
        return $res;
    }

    /**获得当前分类的子分类 */
    public function getChildrenById($cate_id)
    {
        $list = $this -> select();
        $res = $this -> getFormatData($list, $cate_id, 1, false);
        $tree = [];
        foreach ($res as $key => $value) {
            $tree[] = $value['id'];
        }
        return $tree;
    }

    //删除
    public function dels($cate_id)
    {
        // 获得分类id的的子分类列表
        $list = $this -> where('parent_id='.$cate_id) -> find();
        if($list){
            return false;
        }
        return $this -> where('id='.$cate_id) -> delete();
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
        $info = $this -> getCateData($data['id']);
        foreach ($info as $value) {
            if($pid == $value['id']){
                $this -> error = '上级权限不可为自己和其子权限';
                return false;
            }
        }
        return $this -> save($data, $data['id']);
    }

    // 获取楼层信息
    public function getFloor()
    {
        // 1.获取顶级分类
        $data = $this -> where('parent_id=0') -> select();
        //将返回的结果集转换成数组
        $data = collection($data) ->  toArray();
        foreach ($data as $key => $value) {
            // 2.获取二级分类
            $son = $this -> where('parent_id='.$value['id']) -> select();
            $data[$key]['son'] = collection($son) -> toArray();
            // 3.获取二级分类推荐信息
            foreach ($data[$key]['son'] as $k => $v) {
                if($v['isrec'] == 1){
                    $data[$key]['son_rec'][] = $v;
                    $data[$key]['son_rec'][$k]['goods'] = $this -> getGoodsByCateId($v['id']);
                }
            }
        }
        return $data;
    }

    // 根据分类id获得商品信息
    public function getGoodsByCateId($cate_id)
    {
        // 获取当前分类及子分类
        $child = $this -> getChildrenById($cate_id);
        $child[] = $cate_id;
        $child = implode(',', $child);
        // 获取分类下的部分商品 条件：未删除，上架中，在分类中
        $data = model('app\shop\model\Goods') -> where("isdel=1 and is_sale=1 and cate_id in ($child)") -> limit(8) -> select();
        return $data;
    }
}

?>