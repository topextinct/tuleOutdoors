<?php
namespace app\other\model;

use think\Model;

class TuleModel extends Model
{
    /**
     * @var string 表前缀
     */
    private $prefix = 'way_';
    // 关闭自动写入update_time字段
    protected $updateTime = false;

    /**
     * 得到信息   单条
     * @param array $where 条件
     * @param array $join 连表
     * @param string $field 字段
     * @param string $order
     * @return array|false|\PDOStatement|string|Model
     */
    public function getInfo($where = [], $join = [], $field = '*', $order = '')
    {
        $res = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->find();
        if($res)$res->toArray();
        return $res;
    }
    /**
     * 得到信息   多条
     * @param array $where 条件
     * @param array $join 连表
     * @param string $field 字段
     * @param string $order
     * @param string $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getListInfo($where = [], $join = [], $field = '*', $order = '', $limit = '')
    {
        $res_list = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->limit($limit)->select();
//        //统一转成数组形式返回
//        foreach ($res_list as &$v){
//            $v = $v->toArray();
//        }
        return $res_list;
    }
    /**
     * 分页显示列表
     * @param array $where
     * @param array $join 连表
     * @param string $field
     * @param string $order 排序
     * @param int $pagesize 每页数
     * @return mixed
     */
    public function getListPageInfo($where = [], $join = [], $field = '*', $order = '', $pagesize = WX_PAGE_SIZE)
    {
        $page_size = input('page_size') ? input('page_size') : $pagesize ;    //每页多少条
        $res_list = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->paginate($page_size);
        return $res_list->all();
    }
    /**
     * 分页显示列表
     * @param array $where
     * @param array $join 连表
     * @param string $field
     * @param string $order 排序
     * @param int $pagesize 每页数
     * @return mixed
     */
    public function getListPageTotalInfo($where = [], $join = [], $field = '*', $order = '', $pagesize = WX_PAGE_SIZE)
    {
        $page_size = input('page_size') ? input('page_size') : $pagesize ;    //每页多少条
        $res = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->paginate($page_size);
        return $res;
    }
}