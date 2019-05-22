<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/13
 * Time: 16:36
 */

namespace app\impressiom\model;

use app\Common\Cloud_webapi_client;
use think\Db;
use think\Exception;
class StepByStepIn
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

        // 过滤已存在数据
        $FBillNos = [];
        foreach ($data as $v){
            $FBillNos[] = $v['FBillNo'];
        }
        $isICStockBill  = WiseDB::getConnect()->table('ICStockBill')->where('FBillNo','IN',$FBillNos)->select();
        if (!empty($isICStockBill)){
            $isFBillNos =[];// 已同步的单据
            foreach ($isICStockBill as $value){
                $isFBillNos[] = $value['FBillNo'];
            }
            throw new Exception('单据：'.join(',',$isFBillNos).' 在目标系统已存在...');
        }

        WiseDB::getConnect()->table('ICMaxNum')->where(['FTableName'=>'ICStockBill'])->setInc('FMaxNum',count($data));//更新内码
        $this->FInertID = WiseDB::getConnect()->table('ICStockBill')->max('FInterID');// 最新内码

        // 保存单据
        foreach ($data as $line){
            // 获取当前 遍历单据信息
            $dataArray = DocumentsModel::getCloudInfo('STK_TRANSFERIN', $line['FBillNo'] );

//            return json($this->getInfo($dataArray['BillNo']));
            //return json($dataArray);
            $this->saveMasterSingle( $dataArray );
        }
        return returnData('成功同步了 '.count($data).' (张) 分步式调入单...');
    }


    /**
     * @name 保存单据体
     * @param $dataArray
     * @return int|string
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
                'FBrNo'=>"0",// FBrNo	STRING	公司机构内码
                'FInterID'=>$this->FInertID,// FInterID	INTEGER	单据内码
                'FTranType'=>1,// FTranType	INTEGER	单据类型 1 入库，21 出库
                'FDate'=>strtr($dataArray['Date'], 'T', ' '),// FDate	DATETIME	单据日期// 日期
                'FBillNo'=>$dataArray['BillNo'],// FBillNo	STRING	入库单号// 单据编号"WIN000005",// FBillNo	STRING	入库单号
                'FDeptID'=>getValue(ItemClassTable::getWiseItemIDByNmae(2,'物流部'),''),//FDeptID	INTEGER	部门// 部门"0",//FDeptID	INTEGER	部门
                'FEmpID'=>"0",// FEmpID	INTEGER	业务员
                'FSupplyID'=>ItemClassTable::getWiseItemID(8,$dataArray['SUPPLIERID_Id']),// FSupplyID	INTEGER	供应商内码// 供应商"604",// FSupplyID	INTEGER	供应商内码
                'FFManagerID'=>ItemClassTable::getWiseItemIDByNmae(3,'李兰斌'),// FFManagerID	INTEGER	验收人 Item 250// 验收员"602",// FFManagerID	INTEGER	验收人 Item 250
                'FSManagerID'=>ItemClassTable::getWiseItemIDByNmae(3,'李兰斌'),// FSManagerID	INTEGER	保管人// 保管员"602",// FSManagerID	INTEGER	保管人
                'FBillerID'=>"16394",// FBillerID	INTEGER	制单人
                'FDCStockID'=>ItemClassTable::getWiseItemIDByNmae(5,'综合仓'),// FDCStockID	INTEGER	收入库房// 收料仓库
                'FHookInterID'=>"0",// FHookInterID	INTEGER	钩稽单据
                'FPosted'=>"0",// FPosted	INTEGER	过账
                'FCheckSelect'=>"0",
                'FROB'=>"1",// FROB	INTEGER	红蓝字
                'FStatus'=>"0",// FStatus	INTEGER	状态
                'FUpStockWhenSave'=>"0",// FUpStockWhenSave	INTEGER	更新库存
                'FCancellation'=>"0",// FCancellation	INTEGER	作废
                'FOrgBillInterID'=>"0",// FOrgBillInterID	INTEGER	源单内码
                'FBillTypeID'=>"0",// FBillTypeID	INTEGER	单据类别
                'FPOStyle'=>"252",// FPOStyle	INTEGER	采购方式
                'FBackFlushed'=>"0",// FBackFlushed	INTEGER	倒冲标志
                'FWBInterID'=>"0",// FWBInterID	INTEGER	工序计划单内码
                'FTranStatus'=>"0",// FTranStatus	INTEGER	传输状态
                'FRelateBrID'=>"0",
                'FPurposeID'=>"0",
                'FRelateInvoiceID'=>"0",
//            'FOperDate'=>"0000000000086553",// FOperDate	UnKnown	时间戳
            'FImport'=>"0",
            'FSystemType'=>"0",
            'FMarketingStyle'=>"12530",
            'FPayBillID'=>"0",
            'FManagerID'=>"0",
            'FRefType'=>"0",
            'FSelTranType'=>"0",
            'FChildren'=>"0",
            'FHookStatus'=>"0",
            'FActPriceVchTplID'=>"0",
            'FPlanPriceVchTplID'=>"0",
            'FProcID'=>"0",
            'FActualVchTplID'=>"0",
            'FPlanVchTplID'=>"0",
            'FBrID'=>"0",
            'FVIPCardID'=>"0",
            'FVIPScore'=>".0000000000",
            'FHolisticDiscountRate'=>".0000000000",
            'FWorkShiftId'=>"0",
            'FCussentAcctID'=>"0",
            'FZanGuCount'=>"0",
            'FPOOrdBillNo'=>"",
            'FLSSrcInterID'=>"0",
            'FSettleDate'=>strtr($dataArray['Date'], 'T', ' '),// FSettleDate	DATE	收付款日期
            'FManageType'=>"0",
            'FAutoCreType'=>"0",
            'FDrpRelateTranType'=>"0",
            'FPrintCount'=>"0",
            'FPOMode'=>"36680",
            'FInventoryType'=>"0",
            'FObjectItem'=>"0",
            'FConfirmStatus'=>"0",
            'FConfirmer'=>"0",
            'FAutoCreatePeriod'=>"0",
            'FPayCondition'=>"0",
            'FsourceType'=>"37521",
            'FSendStatus'=>"0",
            'FEnterpriseID'=>"0",
            'FISUpLoad'=>"1059",
        ];
        try{
            $exec = $this->dbConnect->table('ICStockBill')->insert( $ICStockBillMaster );// 执行 保存单据头
        }catch (Exception $exception){
            goto start; // 调回执行开始点
        }

        if ($exec == 1){
            return $this->saveEntrySingle( $dataArray ,$ICStockBillMaster);// 调用 保存分录
        }
    }

    /**
     * @name 保存分录
     * @param $dataArray
     * @param $ICStockBillMaster
     * @return int|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveEntrySingle( $dataArray  ,$ICStockBillMaster){
//        return $ICStockBillMaster['FInterID'];
//        $exec = $this->dbConnect->table('ICStockBill')->where(['FBillNo'=>$dataArray['BillNo']])->find()['FInterID'];
//        $arr = $this->dbConnect->table('ICStockBillEntry')->where(['FInterID'=>1789])->find();
//        return fieldArrayToCodeString($arr);
        $InStockEntry = $dataArray['STK_STKTRANSFERINENTRY'];// 分录列表

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
        $FDetailID = $this->dbConnect->table('ICStockBillEntry')->max('FDetailID');
        foreach ($InStockEntry as $k=>$v){
            $FDetailID += 1;
            $ICStockBillEntry = [
                'FBrNo'=>"0",
                'FInterID'=>$ICStockBillMaster['FInterID'],// FInterID	INTEGER	单据内码
                'FEntryID'=>$k+1,// 分录号
                'FItemID'=>ItemClassTable::getWiseItemID(4,$v['MaterialID_Id']),// 产品内码
                'FQtyMust'=>".0000000000",
                'FQty'=>$v['FQty'],// FQty	FLOAT	实际数量
                'FPrice'=>$priceInfoArr[$v['MaterialID_Id']]['FTaxPrice'],// FPrice	FLOAT	单价
                'FAmount'=>$priceInfoArr[$v['MaterialID_Id']]['FAllAmount'],// FAmount	FLOAT	金额
                'FUnitID'=>getValue(ItemClassTable::getWiseItemIDByNmae(7,$v['UnitID']['Name'][0]['Value']),ItemClassTable::getWiseItemIDByNmae(7,'个')) ,//"253"
                'FAuxPrice'=>$priceInfoArr[$v['MaterialID_Id']]['FTaxPrice'],// 辅助单价
                'FAuxQty'=>$priceInfoArr[$v['MaterialID_Id']]['FAllAmount'],//辅助实际数量
                'FAuxQtyMust'=>".0000000000",
                'FQtyActual'=>".0000000000",
                'FAuxQtyActual'=>".0000000000",
                'FPlanPrice'=>".0000000000",
                'FAuxPlanPrice'=>".0000000000",
                'FSourceEntryID'=>"0",
                'FCommitQty'=>".0000000000",
                'FAuxCommitQty'=>".0000000000",
                'FKFPeriod'=>"0",
                'FDCSPID'=>"0",
                'FConsignPrice'=>".0000000000",
                'FConsignAmount'=>".00",
                'FProcessCost'=>".00",
                'FMaterialCost'=>".00",
                'FTaxAmount'=>".00",
                'FMapNumber'=>"",
                'FMapName'=>"",
                'FOrgBillEntryID'=>"0",
                'FOperID'=>"0",
                'FPlanAmount'=>".00",
                'FProcessPrice'=>".0000000000",
                'FTaxRate'=>".0000000000",
                'FSnListID'=>"0",
                'FAmtRef'=>".00",
                'FAuxPropID'=>"0",
                'FCost'=>".0000",
                'FPriceRef'=>".0000000000",
                'FAuxPriceRef'=>".0000000000",
                'FQtyInvoice'=>".0000000000",
                'FQtyInvoiceBase'=>".0000000000",
                'FUnitCost'=>".0000000000",
                'FSecCoefficient'=>".0000000000",
                'FSecQty'=>".0000000000",
                'FSecCommitQty'=>".0000000000",
                'FSourceTranType'=>"0",
                'FSourceInterId'=>"0",
                'FContractInterID'=>"0",
                'FContractEntryID'=>"0",
                'FContractBillNo'=>"",
                'FICMOInterID'=>"0",
                'FPPBomEntryID'=>"0",
                'FOrderInterID'=>"0",
                'FOrderEntryID'=>"0",
                'FAllHookQTY'=>".0000000000",
                'FAllHookAmount'=>".0000000000",
                'FCurrentHookQTY'=>".0000000000",
                'FCurrentHookAmount'=>".0000000000",
                'FStdAllHookAmount'=>".0000000000",
                'FStdCurrentHookAmount'=>".0000000000",
                'FSCStockID'=>"0",
                'FDCStockID'=>ItemClassTable::getWiseItemIDByNmae(5,'综合仓'),//调入仓库
                'FCostObjGroupID'=>"0",
                'FCostOBJID'=>"0",
                'FDetailID'=>$FDetailID,// 分录详情id
                'FMaterialCostPrice'=>".0000000000",
                'FReProduceType'=>"0",
                'FBomInterID'=>"0",
                'FDiscountRate'=>".0000000000",
                'FDiscountAmount'=>".00",
                'FSepcialSaleId'=>"0",
                'FOutCommitQty'=>".0000000000000",
                'FOutSecCommitQty'=>".0000000000000",
                'FDBCommitQty'=>".0000000000000",
                'FDBSecCommitQty'=>".0000000000000",
                'FAuxQtyInvoice'=>".0000000000",
                'FOperSN'=>"0",
                'FCheckStatus'=>"0",
                'FInStockID'=>"0",
                'FSaleCommitQty'=>".0000000000",
                'FSaleSecCommitQty'=>".0000000000",
                'FSaleAuxCommitQty'=>".0000000000",
                'FSelectedProcID'=>"0",
                'FVWInStockQty'=>".0000000000",
                'FAuxVWInStockQty'=>".0000000000",
                'FSecVWInStockQty'=>".0000000000",
                'FSecInvoiceQty'=>".0000000000",
                'FCostCenterID'=>"0",
                'FPlanMode'=>"14036",
                'FSecQtyActual'=>".0000000000",
                'FSecQtyMust'=>".0000000000",
                'FClientOrderNo'=>"",
                'FClientEntryID'=>"0",
                'FRowClosed'=>"0",
                'FCostPercentage'=>".00",
                'FAcctCheck'=>"0",
                'FClosing'=>"0",
                'FDeliveryNoticeEntryID'=>"0",
                'FDeliveryNoticeFID'=>"0",
                'FIsVMI'=>"0",
                'FEntrySupply'=>"0",
                'FChkPassItem'=>"1058",
                'FSEOutInterID'=>"0",
                'FSEOutEntryID'=>"0",
                'FWebReturnQty'=>".0000000000000",
                'FWebReturnAuxQty'=>".0000000000000",
                'FItemStatementBillNO'=>"",
                'FItemStatementEntryID'=>"0",
                'FItemStatementInterID'=>"0",
                'FCommitAmt'=>".0000000000",
                'FFatherProductID'=>"0",
                'FRealAmount'=>".0000000000",
                'FRealPrice'=>".0000000000",
                'FDefaultBaseQty'=>".0000000000",
                'FDefaultQty'=>".0000000000",
                'FRealStockBaseQty'=>".0000000000",
                'FRealStockQty'=>".0000000000",
                'FDiscardID'=>"0",
                'FLockFlag'=>"0",
                'FReturnNoticeEntryID'=>"0",
                'FReturnNoticeInterID'=>"0",
                'FProductFileQty'=>".0000000000",
                'FServiceRequestNo'=>"",
                'FQtySplit'=>".0000000000",
                'FAuxQtySplit'=>".0000000000",
                'FAddQty'=>".0000000000000",
                'FAuxAddQty'=>".0000000000000",
                'FPurchasePrice'=>$v['Price'],
                'FPurchaseAmount'=>$v['Amount'],
                'FCheckAmount'=>".0000000000",
                'FOutSourceInterID'=>"0",
                'FOutSourceEntryID'=>"0",
                'FOutSourceTranType'=>"0",
                'FProcessTaxPrice'=>".0000000000",
                'FProcessTaxCost'=>".0000000000",
                'FReviewBillsQty'=>".0000000000",
                'FPTLQty'=>".0000000000",
            ];
            $ICStockBillEntryAll[] = $ICStockBillEntry;
        }
//        return json($ICStockBillEntryAll);
        $Db = WiseDB::getConnect();// 获取新的连接
        $Db->startTrans();// 在连接上开启事务
        try{
            $exec = $Db->table('ICStockBillEntry')->fetchSql()->insertAll( $ICStockBillEntryAll );// 分录写入
            $this->dbConnect->execute('SET IDENTITY_INSERT ICStockBillEntry ON; '.$exec);
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
            "FormId" => "IOS_APSettlement",
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