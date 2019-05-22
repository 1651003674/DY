<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/13
 * Time: 16:27
 */

namespace app\impressiom\controller;


use think\Controller;
use app\impressiom\model\DocumentsModel;
use think\Exception;
use app\Common\Cloud_webapi_client;
use app\impressiom\model\WiseDB;
use app\impressiom\model\WiseModel;
class Stepbystepin extends Controller
{
    /**
     * @name 销售出库单 同步界面
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * @name 获取  销售出库单 列表
     * @return \app\Common\mixed\
     */
    public function getCloudList(){
        try {
            $inputData = [
//                'orderType',
//                'number',
                'orgId',
                'startDate',
                'stopDate',
            ];
            $inputData = getParam($inputData);

            $fieldArr = [
                'FBillNo',// 编码 代码
                'FDate',// 日期
                'FDocumentStatus',// 状态
                'FStockOrgId',// 收料组织
//                'FIsIOSForFin',// 是否组织间业务
//                'FBillAmount',// 金额
//                'FBillTaxAmount',// 税额
//                'FBillAllAmount',// 价税合计
//                'FCreatorId',// 创建人
            ];

            $fieldStr = join(',', $fieldArr);// 转换 获取的字段 为字符串

            //过滤条件
            $whereField = [
                "FDocumentStatus = 'C'",// 已审核的单据
                "FIsIOSForFin=0",// 是否组织间业务 0 = false
                "FStockOrgId IN (".$inputData['orgId'].")",//指定单据 调入组织
                "( FDate >= '".$inputData['startDate']."' AND FDate <= '".$inputData['stopDate']."' )",// 设置时间筛选范围
            ];
            $save_arr = [
                "FormId" => "STK_TRANSFERIN",
                "FieldKeys" => $fieldStr,
                "FilterString" => join(' AND ',$whereField),// 过滤条件组合
//                "FilterString" => "[FDocumentStatus = 'C' AND FIsIOSForFin=0 AND FStockOrgId IN (".$inputData['orgId'].") AND( FDate >= '".$inputData['startDate']."' AND FDate <= '".$inputData['stopDate']."' )]",
                "OrderString" => "FDate DESC" ,
                "TopRowCount" => "0",
                "StartRow" => "0",
                "Limit" =>'0'
            ];

            $data_model = json_encode(["data" => $save_arr]);//  编码成 json 格式

            $valueArr = json_decode((new Cloud_webapi_client())->getList($data_model), 1);

            $kvArr = empty($valueArr) ? [] : get_key_value_array($fieldArr, $valueArr);

//var_dump($kvArr);die();
            return json_encode($kvArr);
        }catch (Exception $e){
            var_dump($e->getMessage());
        }
    }

    /**
     * @name 同步 销售出库单 资料
     * @return int|string
     * @throws \think\Exception
     */
    public function Asyc(){
        // 返回执行数据写入结果
        try{

            return ( new DocumentsModel( WiseDB::getConnect( request()->param('orgId') ) ) )->save( [new \app\impressiom\model\StepByStepIn(),'save'] );
        }catch (Exception $e){

            return returnData($e->getMessage(),400);
        }
    }
}