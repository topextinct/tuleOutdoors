<?php
use app\lib\Excel;

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
 * @return mixed
 */
function myFloor($num)
{
    return sprintf("%.2f", ($num * 100) / 100);
}

/**
 * 返回信息
 * @param string $code  200：成功  300：失败
 * @param string $message
 * @param null $data
 * @return mixed
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
/**
 * 根据数组生成excel
 * @param array $data   //第一行
 * @param array $arr    //处理过的需要导出的数据
 * @param string $title     //文件名
 */
function createExcel($data = [], $arr = [], $title = '') {
    $excel_obj = new Excel();
    $excel_data = [];
    //设置样式
    $excel_obj->setStyle(['id' => 's_title', 'Font' => ['FontName' => '宋体', 'Size' => '12', 'Bold' => '1']]);
    //header
    foreach ($data as $v){
        $excel_data[0][] = ['styleid' => 's_title', 'data' => $v];
    }
    foreach ($arr as $k => $v) {
        $tmp = [];
        foreach ($v as $value){
            $tmp[] = ['data' => $value];
        }
        $excel_data[] = $tmp;
    }
    $excel_data = $excel_obj->charset($excel_data, 'utf-8');
    $excel_obj->addArray($excel_data);
    $excel_obj->addWorksheet($excel_obj->charset($title, 'utf-8'));
    $excel_obj->generateXML($excel_obj->charset($title, 'utf-8') . '-' . date('Y-m-d-H', time()));
}
/**
 * 同统一转换字符串
 * @param $arr
 * @return mixed
 */
function arr_foreach(&$arr) {
    if (!is_array($arr)) {
        return $arr;
    }
    foreach ($arr as $key => &$val) {
        if (is_array($val)) {
            arr_foreach($val);
        } else {
            if (!is_string($val)) {
                if(!is_object($val)){  // 不是字符串  不是对象 就转换为字符串
                    $val = strval($val);
                }else{
                    if(is_subclass_of($val,'\app\other\model\TuleModel')){
                        $val = $val->toArray();
                        arr_foreach($val);
                    }
                }
            }
        }
    }
    return $arr;
}