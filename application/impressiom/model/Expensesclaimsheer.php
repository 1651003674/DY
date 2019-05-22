<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/15
 * Time: 15:07
 */

namespace app\impressiom\model;

use think\Db;
use think\Exception;
class Expensesclaimsheer
{
    /**
     * @var null | Db
     */
    private $dbConnect = null;
    private $FInertID = null;// 内码

    /**
     * @param Db $DB
     * @return \think\response\Json
     */
    public function save( $DB ){

        $this->dbConnect = $DB;
        $data = request()->param('data');
//        $fields = WiseDB::getConnect()->table('t_VoucherEntry')->where(['FVoucherID'=>3])->find();
//
//        return fieldArrayToCodeString($fields);

        WiseDB::getConnect()->table('ICMaxNum')->where(['FTableName'=>'t_Voucher'])->setInc('FMaxNum',count($data));//更新内码
        $this->FInertID = WiseDB::getConnect()->table('t_Voucher')->max('FVoucherID');// 最新内码

        foreach ($data as $line){
            // 获取当前 遍历单据信息
            $dataArray = DocumentsModel::getCloudInfo('ER_ExpReimbursement', $line['FBillNo'] );
return json($dataArray);
            $this->saveMasterSingle( $dataArray );
        }

        return returnData('成功生成了 '.count($data).' (张) 费用报销凭证...');
    }

    /**
     * @name 保存单据体
     * @param $dataArray
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveMasterSingle( $dataArray ){
        $this->FInertID += 1;

        // 出入库单据主体
        start:
        $ICStockBillMaster =
            [
                'FBrNo'=>"0",// FBrNo	STRING	公司代码
                'FVoucherID'=>$this->FInertID,// FVoucherID	INTEGER	凭证内码
                'FDate'=>strtr($dataArray['Date'], 'T', ' '),// FDate	DATETIME	凭证日期
                'FYear'=>substr($dataArray['Date'],0,4),// FYear	INTEGER	会计年度
                'FPeriod'=>intval(substr($dataArray['Date'],5,2)),//FPeriod	INTEGER	会计期间
                'FGroupID'=>"1",//FGroupID	INTEGER	凭证字内码
                'FNumber'=>"1",//FNumber	INTEGER	凭证号
                'FReference'=>"",//FReference	STRING	参考信息
                'FExplanation'=>"q",//FExplanation	STRING	备注
                'FAttachments'=>"0",// FAttachments	INTEGER	附件张数
                'FEntryCount'=>"2",// FEntryCount	INTEGER	分录数
                'FDebitTotal'=>"500.0000",// FDebitTotal	 FLOAT	借方金额合计
                'FCreditTotal'=>"500.0000",// FCreditTotal	FLOAT	贷方金额合计
                'FInternalInd'=>"",// FInternalInd	STRING	机制凭证
                'FChecked'=>"0",// FChecked	INTEGER	是否审核
                'FPosted'=>"0",// FPosterID	INTEGER	记账人
                'FPreparerID'=>"16394",// FPreparerID	INTEGER	制单人
                'FCheckerID'=>"-1",// FCheckerID	INTEGER	审核人
                'FPosterID'=>"-1",// FPosterd	INTEGER	是否过账
                'FCashierID'=>"-1",// FCashierID	INTEGER	出纳员
                'FHandler'=>"",// FHandler	STRING	会计主管
                'FOwnerGroupID'=>"1",// FOwnerGroupID	INTEGER	制单人所属工作组
                'FObjectName'=>"",// FObjectName	STRING	对象接口
                'FParameter'=>"",// FParameter	STRING	接口参数
                'FSerialNum'=>"1",// FSerialNum	INTEGER	凭证序号
                'FTranType'=>"0",// FTranType	INTEGER	单据类型
                'FTransDate'=>strtr($dataArray['Date'], 'T', ' '),// FTransDate	DATETIME	业务日期
                'FFrameWorkID'=>"-1",// FFrameWorkID	INTEGER	集团组织机构内码
                'FApproveID'=>"-1",// FApproveID	INTEGER	审批
                'FFootNote'=>"",// FFootNote	STRING	批注
            ];
        try{
            $exec = $this->dbConnect->table('t_Voucher')->insert( $ICStockBillMaster );// 执行 保存单据头
        }catch (Exception $exception){
            goto start; // 调回执行开始点
        }

        if ($exec == 1){
            return $this->saveEntrySingle( $dataArray ,$ICStockBillMaster);// 调用 保存分录
        }
    }

    private function saveEntrySingle( $dataArray  ,$ICStockBillMaster){
//        return $ICStockBillMaster['FInterID'];
//        $exec = $this->dbConnect->table('ICStockBill')->where(['FBillNo'=>$dataArray['BillNo']])->find()['FInterID'];
//        $arr = $this->dbConnect->table('ICStockBillEntry')->where(['FInterID'=>1789])->find();
//        return fieldArrayToCodeString($arr);
        $InStockEntry = $dataArray['STK_STKTRANSFEROUTENTRY'];// 分录列表

        $priceArr = $this->getInfo($dataArray['BillNo']);
        if(empty($priceArr)) throw  new Exception('单据：'.$dataArray['BillNo'].' 没有结算信息，同步失败...');
        $priceInfoArr = [];// 物料财务信息
        foreach ($priceArr as $priceInfo){
            $priceInfoArr[$priceInfo['FMaterialId']] = $priceInfo;
        }
//        return json($priceInfoArr);
//        return json($InStockEntry);
//         出入库单据 分录
        $ICStockBillEntryAll = [];
        $FDetailID = $this->dbConnect->table('t_VoucherEntry')->max('FDetailID');
        foreach ($InStockEntry as $k=>$v){
            $FDetailID += 1;
            $ICStockBillEntry =
                [
                    'FBrNo'=>"0",// FBrNo	STRING	公司代码
                    'FVoucherID'=>"3",// FVoucherID	INTEGER	凭证内码
                    'FEntryID'=>"0",// FEntryID	INTEGER	分录号
                    'FExplanation'=>"q",// FExplanation	STRING	摘要
                    'FAccountID'=>"1000",//  FAccountID	INTEGER	科目内码
                    'FDetailID'=>"0",// FDetailID	INTEGER	核算项目
                    'FCurrencyID'=>"1",// FCurrencyID	INTEGER	币别
                    'FExchangeRate'=>"1.0",// FExchangeRate	FLOAT	汇率
                    'FDC'=>"1",// FDC	INTEGER	余额方向
                    'FAmountFor'=>"500.0000",// FAmountFor	FLOAT	原币金额
                    'FAmount'=>"500.0000",// FAmount	FLOAT	本位币金额
                    'FQuantity'=>"0.0",// FQuantity	FLOAT	数量
                    'FMeasureUnitID'=>"0",// FMeasureUnitID	INTEGER	单位内码
                    'FUnitPrice'=>"0.0",// FUnitPrice	FLOAT	单价
                    'FInternalInd'=>"",// FInternalInd	STRING	机制凭证
                    'FAccountID2'=>"1113",// FAccountID2	INTEGER	对方科目 内码
                    'FSettleTypeID'=>"0",// FSettleTypeID	INTEGER	结算方式
                    'FSettleNo'=>"",// FSettleNo	STRING	结算号
                    'FTransNo'=>"",// FTransNo	STRING	业务号
                    'FCashFlowItem'=>"0",// FCashFlowItem	INTEGER	现金流量
                    'FTaskID'=>"0",// FTaskID	INTEGER	项目任务内码
                    'FResourceID'=>"0",// FResourceID	INTEGER	项目资源内码
                    'FExchangeRateType'=>"1",
                    'FSideEntryID'=>"1",
                ];
            $ICStockBillEntryAll[] = $ICStockBillEntry;
        }
//        return json($ICStockBillEntryAll);
        $Db = WiseDB::getConnect();// 获取新的连接
        $Db->startTrans();// 在连接上开启事务
        try{
            $exec = $Db->table('t_VoucherEntry')->fetchSql()->insertAll( $ICStockBillEntryAll );// 分录写入
            $this->dbConnect->execute('SET IDENTITY_INSERT t_VoucherEntry ON; '.$exec);
            $Db->commit();// 事务提交
        }catch (Exception $e){
            $Db->rollback();// 回滚 wise 事务
            throw  new Exception('写入单据分录发生错误：'.$e->getMessage());
        }
        return $exec;
    }

    /**
     * @name 获取调入物料财务信息
     * @param $SrcBillNo
     * @return array
     */
    private function getInfo($SrcBillNo){
        $fieldArr = [
            'FBizBillNo ',// 编码 代码
            'FMaterialId  ',// 物料内码
            'FTaxPrice ',// 含税单价
            'FAllAmount ',// 价税合计
        ];

        $fieldStr = join(',', $fieldArr);// 转换 获取的字段 为字符串

        //过滤条件
        $whereField = [
//            "FDocumentStatus = 'C'",// 已审核的单据
//            "FStockOrgId IN (".request()->param('orgId').")",//指定单据 调入组织
            'FBIZBILLNO = '.$SrcBillNo,

        ];
        $save_arr = [
            "FormId" => "IOS_ARSettlement",
            "FieldKeys" => $fieldStr,
            "FilterString" => "FBIZBILLNO ='".$SrcBillNo."'",// 过滤条件组合
            "OrderString" => "" ,
            "TopRowCount" => "0",
            "StartRow" => "0",
            "Limit" =>'0'
        ];
//return $save_arr;
        $data_model = json_encode(["data" => $save_arr]);//  编码成 json 格式

        $valueArr = json_decode((new Cloud_webapi_client())->getList($data_model), 1);

        $reArr =  empty($valueArr) ? [] : get_key_value_array($fieldArr, $valueArr);
        return $reArr;
    }
}