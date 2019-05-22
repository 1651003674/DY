<?php
namespace app\impressiom\controller;

use app\Common\Cloud_webapi_client;
use think\Controller;

class Index extends Controller
{
    /**
     * @name 操作界面主页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * @name  获取组织列表
     * @return false|string
     */
    public function getOrgList(){
        $inputData = [
//            'orderType',
            'number'
        ];
//
        $inputData = getParam($inputData);

        $fieldArr = [
            'FORGID',//FDEPTID 100230 组织内码 StockDeptId_Id
            'FName',// 组织名称
            'FNumber',// 编码 组织代码
//            'FPARENTID',// 上级组织
//            'FUSEORGID',// 使用组织
        ];

        $fieldStr = join(',',$fieldArr);// 转换 获取的字段 为字符串

        $save_arr =[
            "FormId"=>"ORG_Organizations",
            "FieldKeys"=>$fieldStr,
//            "FilterString"=>"[FUSEORGID IN (101043)]",
            "OrderString"=>"FORGID DESC",
            "TopRowCount"=>"0",
            "StartRow"=>"0",
            "Limit"=>$inputData['number']
        ];

        $data_model = json_encode(["data"=>$save_arr]);//  编码成 json 格式

        $valueArr = json_decode((new Cloud_webapi_client())->getList($data_model),1);

        $kvArr = get_key_value_array($fieldArr,$valueArr);
//print_r($kvArr);die();
//var_dump($kvArr);die();
        return json_encode($kvArr);
    }
}
