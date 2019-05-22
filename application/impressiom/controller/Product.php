<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/6
 * Time: 15:59
 */

namespace app\impressiom\controller;


use app\impressiom\model\ItemClassTable;
use think\Controller;
use app\Common\Cloud_webapi_client;
use app\impressiom\model\WiseDB;
use app\impressiom\model\WiseModel;

class Product extends Controller
{
    /**
     * @name 物料 基础资料 同步界面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * @name 获取基础资料 物料列表
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
            'FNumber',// 编码 代码
            'FName',// 名称
            'FMATERIALID',
            'FUSEORGID',// 使用组织
            'FCREATEORGID',// 创建组织
        ];

        $fieldStr = join(',',$fieldArr);// 转换 获取的字段 为字符串

        $save_arr =[
            "FormId"=>"BD_MATERIAL",
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

        return json_encode($kvArr);
    }

    /**
     * @name 同步物料资料
     * @return int|string
     * @throws \think\Exception
     */
    public function Asyc(){
        // 返回执行数据写入结果
        $callbackObj = ( new WiseModel( WiseDB::getConnect(request()->param('orgId') ) ) );
        return ( new ItemClassTable( WiseDB::getConnect(request()->param('orgId') ) ) )->save(  4 ,[$callbackObj,'saveProduct']);
    }
}