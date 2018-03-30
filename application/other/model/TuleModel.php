<?php
namespace app\other\model;

use think\Model;

class TuleModel extends Model
{
    /**
     * @var string 表前缀
     */
    private $prefix = 'mall_';
    // 关闭自动写入update_time字段
    protected $updateTime = false;

    /**
     * 得到信息   单条
     * @param string $where 条件
     * @param array $join 连表
     * @param string $field 字段
     * @param string $order
     * @return array|false|\PDOStatement|string|Model
     */
    public function getInfo($where = '', $join = [], $field = '*', $order = '')
    {
        $res = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->find();
        return $res;
    }

}