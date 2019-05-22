<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/5
 * Time: 14:00
 */

namespace app\impressiom\controller;


use app\impressiom\model\ItemClassTable;
use think\Controller;
use app\Common\Cloud_webapi_client;
use app\impressiom\model\WiseDB;
use app\impressiom\model\WiseModel;

/**
 * Class Employees 员工基础资料 同步
 * @package app\impressiom\controller
 */
class Employees extends Controller
{
    /**
     * @name 员工 基础资料 同步界面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * @name 获取基础资料 员工列表
     * @return \app\Common\mixed\
     */
    public function getCloudList(){
        $inputData = [
            'orderType',
            'number',
            'orgId'
        ];

        $inputData = getParam($inputData);

        $fieldArr = [
            'FNumber',// 编码 员工代码
            'FName',// 名称
            'FSTAFFID',// Cloud 员工内码
            'FMASTERID',// Cloud 员工内码
            'FUSEORGID',// 使用组织
        ];

        $fieldStr = join(',',$fieldArr);// 转换 获取的字段 为字符串

        $save_arr =[
            "FormId"=>"BD_Empinfo",// BD_Empinfo
            "FieldKeys"=>$fieldStr,
            "FilterString"=>"[FUSEORGID IN (".$inputData['orgId'].")]",
            "OrderString"=>"FNumber ".$inputData['orderType'],
            "TopRowCount"=>"0",
            "StartRow"=>"0",
            "Limit"=>$inputData['number']
        ];

        $data_model = json_encode(["data"=>$save_arr]);//  编码成 json 格式

        $valueArr = json_decode((new Cloud_webapi_client())->getList($data_model),1);

        $kvArr = get_key_value_array($fieldArr,$valueArr);

//var_dump($kvArr);die();
        return json_encode($kvArr);
    }

    /**
     * @name 同步员工资料
     * @return int|string
     * @throws \think\Exception
     */
    public function Asyc(){
        // 返回执行数据写入结果
        $callbackObj = ( new WiseModel( WiseDB::getConnect(request()->param('orgId') ) ) );
        return ( new ItemClassTable( WiseDB::getConnect(request()->param('orgId') ) ) )->save( 3,[$callbackObj,'saveEmployees'] );
    }
}