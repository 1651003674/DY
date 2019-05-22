<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * @name 获取参数
 * @param $data
 * @return array
 */
function getParam($data){
    $datas = [];
    foreach ($data as $v){
        $datas[$v]=request()->param($v);
    }
    return $datas;
}

/**
 * @name 获取 键值对 数据 cloud 获取数据用
 * @param $fields
 * @param $values
 * @return array
 */
function get_key_value_array($fields,$values)
{
    $kvArr = [];
    foreach ($values as $key=> $value){
        for($i=0;$i<count($fields);$i++){

            $kvArr[ $key][trim($fields[$i])] = $value[$i];
        }
    }
    return $kvArr;
}

/**
 * @name 数据表字段数组 转换成 代码字符串
 * @param array $fieldArray
 * @return string
 */
function fieldArrayToCodeString($fieldArray=[]){
    $str = '['.PHP_EOL;
    foreach ($fieldArray as $k=>$value){
        $str .= "'". $k ."'=>\"".trim($value).'",'.PHP_EOL;
    }
    $str .= ']';

    return $str;
}

/**
 * @name 请求响应数据包
 * @param $msg 消息
 * @param int $code 状态码 200 成功，400 异常
 * @param array $data 数据
 * @return false|string
 */
function returnData($msg,$code=200,$data=[]){
    return json_encode(['code'=>$code,'msg'=>$msg,'data'=>$data]);
}

/**
 * @name 获取转换后的值
 * @param $value
 * @param $defaultValue
 * @return mixed
 */
function getValue($value,$defaultValue){
  return  empty($value)?$defaultValue:$value;
}