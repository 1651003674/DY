<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/4
 * Time: 21:29
 */

namespace app\impressiom\controller;


use app\impressiom\model\ItemClassTable;
use think\Controller;
use app\Common\Cloud_webapi_client;
use app\impressiom\model\WiseDB;
use app\impressiom\model\WiseModel;

/**
 * Class Department 部门基础资料 同步
 * @package app\impressiom\controller
 */
class Department extends  Controller
{
    /**
     * @name 部门 基础资料 同步界面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * @name 获取基础资料 部门列表
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
            'FDEPTID',//FDEPTID 100230 部门内码 StockDeptId_Id
            'FName',// 部门名称
            'FNumber',// 编码 部门代码
            'FPARENTID',// 上级部门
            'FUSEORGID',// 使用组织
        ];

        $fieldStr = join(',',$fieldArr);// 转换 获取的字段 为字符串

        $save_arr =[
            "FormId"=>"BD_Department",
//            "FormId"=>"ORG_Organizations",
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
     * @name 同步部门资料
     * @return int|string
     * @throws \think\Exception
     */
    public function Asyc(){
        // 返回执行数据写入结果
        /**
         * 调用公共基础资料 同步器
         */
        $callbackObj = ( new WiseModel( WiseDB::getConnect(request()->param('orgId') ) ) );
        return ( new ItemClassTable( WiseDB::getConnect(request()->param('orgId') ) ) )->save( 2,[ $callbackObj , 'saveDepartment'] );// 2  为部门基础资料类型
    }
}