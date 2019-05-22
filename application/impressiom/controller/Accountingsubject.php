<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/23
 * Time: 15:16
 */

namespace app\impressiom\controller;


use think\Controller;
use app\Common\Cloud_webapi_client;
use app\impressiom\model\WiseDB;
use app\impressiom\model\WiseModel;
use think\Exception;

class Accountingsubject extends Controller
{
    /**
     * @name 会计科目基础资料 同步界面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * @name 获取基础资料 科目列表
     * @return \app\Common\mixed\
     */
    public function getCloudList(){
        $inputData = [
            'orderType',
            'number'
        ];

        $inputData = getParam($inputData);

        $fieldArr = [
            'FNumber',// 编码 科目代码
            'FName',// 科目名称
            'FLEVEL',// 科目级次
            'FISDETAIL',// 明细科目
            'FPARENTID',// 上级科目内码
            'FGROUPID',// 科目类别 id
            'FDC',// 余额方向
            'FHelperCode',// 助记码
            'FCURRENCYID',// 币别内码
            'FISCASH',// 现金科目
            'FISBANK',// 银行科目
            'FFLEXITEMPROPERTYID',// 核算项目维度内码
            'FAMOUNTDC',// 发生方向
            'FITEMDETAILID',// 核算维度
            'FUSEORGID',// 使用组织 印象集团 1，英普 100001，东源 100002
            'FCREATEORGID',// 创建组织 印象集团 1，英普 100001，东源 100002
            'FFULLNAME',// 全名
        ];

        $fieldStr = join(',',$fieldArr);// 转换 获取的字段 为字符串

        $save_arr =[
            "FormId"=>"BD_Account",
            "FieldKeys"=>$fieldStr,
            "FilterString"=>"[]",
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
     * @name 同步科目资料
     * @return int|string
     * @throws \think\Exception
     */
    public function Asyc(){
        // 返回执行数据写入结果
        return ( new WiseModel( WiseDB::getConnect(request()->param('orgId') ) ) )->saveAccountingSubject(  request()->param('data') );
//        return json_encode(input("data"));
    }
}