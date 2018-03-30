<?php
/**
 * 参数检查 用于新增数据
 * @param $arr  需要接收的字段的数组集合
 * @param $type 0：字段是否存在；1：需要判断是否为空
 * @return mixed
 */
function parameter_check($arr, $type = 0){
    $arr = array_flip($arr);    //键值反转
    $arr_data = array_intersect_key($_POST, $arr);  //获取数组中所需元素组成新的数组，用来安全接受数据
    if($type == 1){ //去除空值，用于判断数据是否为空
        $arr_data = array_filter($arr_data);    //去除false，null，''，0
    }
    //array_diff_key() 返回一个数组，该数组包括了所有出现在 array1 中但是未出现在任何其它参数数组中的键名的值。
    $arr_data_check = array_diff_key($arr, $arr_data);   //数组比较返回差值
    //检查返回所缺参数
    if(count($arr_data_check) > 0){
        $error_message = implode(',',array_keys($arr_data_check));
        return return_info(300, $error_message.'参数异常,请检查表单');
    }
    return return_info(200, '验证通过', $arr_data);
}
/**
 * 金额取小数点后面两位
 * @param $num
 * @return string
 */
function myFloor($num)
{
    return sprintf("%.2f", ($num * 100) / 100);
}
/**
 * 返回信息
 * @param $code 200：成功  300：失败
 * @param $message
 * @param $data
 */
function return_info($code = '300', $message = '信息错误', $data = null)
{
    $arr['code'] = $code;
    $arr['message'] = $message;

    if ($data !== null) {
        $arr['data'] = $data;
    }
    return $arr;
}