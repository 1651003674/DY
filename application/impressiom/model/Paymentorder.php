<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/15
 * Time: 15:38
 */

namespace app\impressiom\model;

use think\Db;
use think\Exception;
class Paymentorder
{
    /**
     * @var null | Db
     */
    private $dbConnect = null;

    /**
     * @param Db $DB
     * @return \think\response\Json
     */
    public function save( $DB ){

        $this->dbConnect = $DB;
        $data = request()->param('data');
        foreach ($data as $line){
            // 获取当前 遍历单据信息
            $dataArray = DocumentsModel::getCloudInfo('STK_InStock', $line['FBillNo'] );

            $this->saveMasterSingle( $dataArray );
//            return json( [$dataArray['SupplierId_Id'],$dataArray,] );
        }
    }

    private function saveMasterSingle( $dataArray ){
        //        $arr = $this->dbConnect->table('ICStockBill')->where(['FInterID'=>1783])->find();
        // 出入库单据主体
        $ICStockBill =
            [
                'FBrNo'=>"0",// FBrNo	STRING	公司机构内码
                'FInterID'=>"1783",// FInterID	INTEGER	单据内码
                'FTranType'=>"21",// FTranType	INTEGER	单据类型
                'FDate'=>"2019-04-01 00:00:00.000",// FDate	DATETIME	单据日期
                'FBillNo'=>"XOUT000003",// FBillNo	STRING	入库单号
                'FUse'=>"",// FUse	STRING	用途
                'FNote'=>"",// FNote	STRING	备注
                'FDCStockID'=>"",// FDCStockID	INTEGER	收入库房
                'FSCStockID'=>"",// FSCStockID	INTEGER	发出库房
                'FDeptID'=>"292",//FDeptID	INTEGER	部门
                'FEmpID'=>"250",// FEmpID	INTEGER	业务员
                'FSupplyID'=>"300",// FSupplyID	INTEGER	供应商内码
                'FPosterID'=>"",// FPosterID	INTEGER	记账人
                'FCheckerID'=>"16394",// FCheckerID	INTEGER	审核人
                'FFManagerID'=>"250",// FFManagerID	INTEGER	验收人 Item 250
                'FSManagerID'=>"250",// FSManagerID	INTEGER	保管人
                'FBillerID'=>"16394",// FBillerID	INTEGER	制单人
                'FReturnBillInterID'=>"",// FReturnBillInterID	INTEGER	退货单号
                'FSCBillNo'=>"",// FSCBillNo	STRING
                'FHookInterID'=>"0",// FHookInterID	INTEGER	钩稽单据
                'FVchInterID'=>"",// FVchInterID	INTEGER	凭证内码
                'FPosted'=>"0",// FPosted	INTEGER	过账
                'FCheckSelect'=>"0",// FCheckSelect	INTEGER
                'FCurrencyID'=>"",// FCurrencyID	INTEGER	币别
                'FSaleStyle'=>"101",// FSaleStyle	INTEGER	销售方式
                'FAcctID'=>"",// FAcctID	INTEGER	科目内码
                'FROB'=>"1",// FROB	INTEGER	红蓝字
                'FRSCBillNo'=>"",// FRSCBillNo	STRING
                'FStatus'=>"1",// FStatus	INTEGER	状态
                'FUpStockWhenSave'=>"0",// FUpStockWhenSave	INTEGER	更新库存
                'FCancellation'=>"0",// FCancellation	INTEGER	作废
                'FOrgBillInterID'=>"0",// FOrgBillInterID	INTEGER	源单内码
                'FBillTypeID'=>"0",// FBillTypeID	INTEGER	单据类别
                'FPOStyle'=>"",// FPOStyle	INTEGER	采购方式
                'FMultiCheckLevel1'=>"",// FMultiCheckLevel1	INTEGER	一审
                'FMultiCheckLevel2'=>"",// FMultiCheckLevel2	INTEGER	二审
                'FMultiCheckLevel3'=>"",// FMultiCheckLevel3	INTEGER	三审
                'FMultiCheckLevel4'=>"",// FMultiCheckLevel4	INTEGER	四审
                'FMultiCheckLevel5'=>"",// FMultiCheckLevel5	INTEGER	五审
                'FMultiCheckLevel6'=>"",// FMultiCheckLevel6	INTEGER	六审
                'FMultiCheckDate1'=>"",// FMultiCheckDate1	DATETIME	一级审核日期
                'FMultiCheckDate2'=>"",// FMultiCheckDate2	DATETIME	二级审核日期
                'FMultiCheckDate3'=>"",// FMultiCheckDate3	DATETIME	三级审核日期
                'FMultiCheckDate4'=>"",// FMultiCheckDate4	DATETIME	四级审核日期
                'FMultiCheckDate5'=>"",// FMultiCheckDate5	DATETIME	五级审核日期
                'FMultiCheckDate6'=>"",// FMultiCheckDate6	DATETIME	六级审核日期
                'FCurCheckLevel'=>"",//FCurCheckLevel	INTEGER	当前审核级别
                'FTaskID'=>"",// FTaskID	INTEGER	应付单号码
                'FResourceID'=>"",// FResourceID	INTEGER
                'FBackFlushed'=>"0",// FBackFlushed	INTEGER	倒冲标志
                'FWBInterID'=>"0",// FWBInterID	INTEGER	工序计划单内码
                'FTranStatus'=>"0",// FTranStatus	INTEGER	传输状态
                'FZPBillInterID'=>"",// FZPBillInterID	INTEGER	赠品单据内码
                'FRelateBrID'=>"0",// FRelateBrID	INTEGER	分支机构内码
                'FPurposeID'=>"0",// FPurposeID	INTEGER	领料类型
//            'FUUID'=>"EF4FA380-5ED9-4960-96B6-FEC0F889A753",//FUUID	UnKnown
            'FRelateInvoiceID'=>"1072",// FRelateInvoiceID	INTEGER	关联发票号
//            'FOperDate'=>"0000000000081EC5",// FOperDate	UnKnown	时间戳
            'FImport'=>"0",//FImport	INTEGER	引入标志
            'FSystemType'=>"0",// FSystemType	INTEGER	系统类型
            'FMarketingStyle'=>"12530",// FMarketingStyle	INTEGER	销售业务类型
            'FPayBillID'=>"0",//FPayBillID	INTEGER	应付单内码
            'FCheckDate'=>"2019-03-30 00:00:00.000",// FCheckDate	DATETIME	审核日期
            'FExplanation'=>"",// FExplanation	STRING	摘要
            'FFetchAdd'=>"",// FFetchAdd	STRING	交货地点
            'FFetchDate'=>"",// FFetchDate	DATETIME	交货日期
            'FManagerID'=>"0",// FManagerID	INTEGER	主管
            'FRefType'=>"0",// FRefType	INTEGER	调拨类型
            'FSelTranType'=>"0",// FSelTranType	INTEGER	源单类型
            'FChildren'=>"3",// FChildren	INTEGER	关联标识
            'FHookStatus'=>"0",// FHookStatus	INTEGER	钩稽标志
            'FActPriceVchTplID'=>"0",// FActPriceVchTplID	INTEGER
            'FPlanPriceVchTplID'=>"0",// FPlanPriceVchTplID	INTEGER
            'FProcID'=>"0",// FProcID	INTEGER
            'FActualVchTplID'=>"0",// FActualVchTplID	INTEGER
            'FPlanVchTplID'=>"0",// FPlanVchTplID	INTEGER
            'FBrID'=>"0",// FBrID	INTEGER	制单机构
            'FVIPCardID'=>"0",// FVIPCardID	INTEGER	VIP卡
            'FVIPScore'=>".0000000000",// FVIPScore	FLOAT	VIP积分
            'FHolisticDiscountRate'=>".0000000000",// FHolisticDiscountRate	FLOAT	整单折扣率
            'FPOSName'=>"",// FPOSName	STRING	POS机名称
            'FWorkShiftId'=>"0",// FWorkShiftId	INTEGER	班次ID
            'FCussentAcctID'=>"0",// FCussentAcctID	INTEGER
            'FZanGuCount'=>"0",//FZanGuCount	INTEGER	暂估数量
            'FPOOrdBillNo'=>"",//
            'FLSSrcInterID'=>"0",
            'FSettleDate'=>"2019-04-01 00:00:00.000",// FSettleDate	DATE	收付款日期
            'FManageType'=>"0",// FManageType	INTEGER	保税监管类型
            'FOrderAffirm'=>"0",
            'FAutoCreType'=>"0",
            'FConsignee'=>"0",
            'FDrpRelateTranType'=>"0",
            'FPrintCount'=>"0",
            'FPOMode'=>"0",
            'FInventoryType'=>"0",
            'FObjectItem'=>"0",
            'FConfirmStatus'=>"0",
            'FConfirmMem'=>"",
            'FConfirmDate'=>"",
            'FConfirmer'=>"0",
            'FAutoCreatePeriod'=>"0",
            'FYearPeriod'=>"",
            'FPayCondition'=>"0",
            'FsourceType'=>"37521",
            'FReceiver'=>"",
            'FInvoiceStatus'=>"",
            'FSendStatus'=>"0",
            'FEnterpriseID'=>"0",
            'FBillReviewer'=>"",
            'FBillReviewDate'=>"",
            'FCod'=>"",
            'FReceiveMan'=>"",
            'FConsigneeAdd'=>"",
            'FISUpLoad'=>"1059",
            'FReceiverMobile'=>"",
        ];
        start:
        $ICStockBillMaster =[
            'FInterID'=> $this->dbConnect->table('ICStockBill')->max('FInterID') + 1,// FInterID	INTEGER	单据内码
            'FBrNo'=>"0",// FBrNo	STRING	公司机构内码
            'FTranType'=>"1",// FTranType	INTEGER	单据类型 1 外购入库单，21 出库单
//            'FSettleDate'=>"2019-04-01 00:00:00.000",// FSettleDate	DATE	收付款日期//付款日期
            //是否上传管易
            'FPOStyle'=>"252",// FPOStyle	INTEGER	采购方式// 采购方式 ： 252=》 赊购
            'FDate'=>strtr($dataArray['CreateDate'], 'T', ' '),// FDate	DATETIME	单据日期// 日期
            'FBillNo'=>$dataArray['BillNo'],// FBillNo	STRING	入库单号// 单据编号
            'FSupplyID'=>$dataArray['SupplierId_Id'],// FSupplyID	INTEGER	供应商内码// 供应商
            'FAcctID'=>"",// FAcctID	INTEGER	科目内码// 往来科目
            'FDCStockID'=>$dataArray['F_sa_Base_Id'],// FDCStockID	INTEGER	收入库房// 收料仓库

            'FSManagerID'=>$dataArray['StockerId_Id'],// FSManagerID	INTEGER	保管人// 保管员
            // 负责人
            'FDeptID'=>$dataArray['PurchaseDeptId_Id'],//FDeptID	INTEGER	部门// 部门
            'FFManagerID'=>$dataArray['ConfirmerId_Id'],// FFManagerID	INTEGER	验收人 Item 250// 验收员
//            'FEmpID'=>"250",// FEmpID	INTEGER	业务员// 业务员
            'FBillerID'=>"16394",// FBillerID	INTEGER	制单人// 制单人
        ];
        try{
            $exec = $this->dbConnect->table('ICStockBill')->insert( $ICStockBillMaster );
        }catch (Exception $exception){
            goto start;
        }

        if ( !empty($exec )){
            $this->saveEntrySingle( $dataArray );
        }
    }

    /**
     * @name 保存分录
     * @param $dataArray
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveEntrySingle( $dataArray  ){

        $exec = $this->dbConnect->table('ICStockBill')->where(['FBillNo'=>$dataArray['BillNo']])->find()['FInterID'];
        //        $arr = $this->dbConnect->table('ICStockBillEntry')->where(['FInterID'=>1783])->find();
        // 出入库单据 分录
        $ICStockBillEntry =
            [
                'FBrNo'=>"0",//FBrNo	STRING	公司机构内码
                'FInterID'=>"1783",// FInterID	INTEGER	单据内码
                'FEntryID'=>"1",// FEntryID	INTEGER	分录号
                'FItemID'=>"442",// FItemID	INTEGER	产品内码
                'FQtyMust'=>".0000000000",// FQtyMust	FLOAT	申请数量
                'FQty'=>"20.0000000000",// FQty	FLOAT	实际数量
                'FPrice'=>"1600.0000000000",// FPrice	FLOAT	单价
                'FBatchNo'=>"",// FBatchNo	STRING	批次
                'FAmount'=>"32000.00",// FAmount	FLOAT	金额
                'FNote'=>"",// FNote	STRING	备注
                'FSCBillInterID'=>"",// FSCBillInterID	INTEGER	原单内码
                'FSCBillNo'=>"",// FSCBillNo	STRING	原单单号
                'FUnitID'=>"253",// FUnitID	INTEGER	单位内码
                'FAuxPrice'=>"1600.0000000000",// FAuxPrice	FLOAT	辅助单价
                'FAuxQty'=>"20.0000000000",// FAuxQty	FLOAT	辅助实际数量
                'FAuxQtyMust'=>".0000000000",// FAuxQtyMust	FLOAT	辅助账存数量
                'FQtyActual'=>".0000000000",// FQtyActual	FLOAT	实存数量
                'FAuxQtyActual'=>".0000000000",// FAuxQtyActual	FLOAT	辅助实存数量
                'FPlanPrice'=>".0000000000",// FPlanPrice	FLOAT	计划价
                'FAuxPlanPrice'=>".0000000000",// FAuxPlanPrice	FLOAT	辅助计划价
                'FSourceEntryID'=>"0",// FSourceEntryID	INTEGER	原分录号
                'FCommitQty'=>".0000000000",// FCommitQty	FLOAT	提交数量
                'FAuxCommitQty'=>".0000000000",// FAuxCommitQty	FLOAT	辅助提交数量
                'FKFDate'=>"",// FKFDate	DATETIME	生产/采购日期
                'FKFPeriod'=>"0",// FKFPeriod	INTEGER	保质期
                'FDCSPID'=>"0",// FDCSPID	INTEGER	目标仓位
                'FSCSPID'=>"",// FSCSPID	INTEGER	源仓位
                'FConsignPrice'=>".0000000000",// FConsignPrice	FLOAT	代销单价
                'FConsignAmount'=>".00",// FConsignAmount	FLOAT	代销金额
                'FProcessCost'=>".00",// FProcessCost	FLOAT	加工费
                'FMaterialCost'=>".00",// FMaterialCost	FLOAT	材料费
                'FTaxAmount'=>".00",// FTaxAmount	FLOAT	税额
                'FMapNumber'=>"",// FMapNumber	STRING	对应代码
                'FMapName'=>"",// FMapName	STRING	对应名称
                'FOrgBillEntryID'=>"0",// FOrgBillEntryID	INTEGER	拆单源单行号
                'FOperID'=>"0",// FOperID	INTEGER	工序
                'FPlanAmount'=>".00",// FPlanAmount	FLOAT	计划价金额
                'FProcessPrice'=>".0000000000",// FProcessPrice	FLOAT	委外加工入库单增加加工单价
                'FTaxRate'=>".0000000000",// FTaxRate	FLOAT	税率
                'FSnListID'=>"0",// FSnListID	INTEGER	序列号
                'FAmtRef'=>".00",// FAmtRef	FLOAT	调拨金额
                'FAuxPropID'=>"0",// FAuxPropID	INTEGER	辅助属性
                'FCost'=>".0000",// FCost	FLOAT
                'FPriceRef'=>".0000000000",// FPriceRef	FLOAT
                'FAuxPriceRef'=>".0000000000",// FAuxPriceRef	FLOAT	调拨单价
                'FFetchDate'=>"",// FFetchDate	DATETIME	交货日期
                'FQtyInvoice'=>"20.0000000000",// FQtyInvoice	FLOAT	基本单位开票数量
                'FQtyInvoiceBase'=>".0000000000",// FQtyInvoiceBase	FLOAT
                'FUnitCost'=>".0000000000",// FUnitCost	FLOAT
                'FSecCoefficient'=>".0000000000",// FSecCoefficient	FLOAT	换算率
                'FSecQty'=>".0000000000",// FSecQty	FLOAT	辅助数量
                'FSecCommitQty'=>".0000000000",// FSecCommitQty	FLOAT	辅助执行数量
                'FSourceTranType'=>"0",// FSourceTranType	INTEGER	源单类型
                'FSourceInterId'=>"0",// FSourceInterId	INTEGER	源单内码
                'FSourceBillNo'=>"",// FSourceBillNo	STRING	源单单号
                'FContractInterID'=>"0",// FContractInterID	INTEGER	合同内码
                'FContractEntryID'=>"0",// FContractEntryID	INTEGER	合同分录
                'FContractBillNo'=>"",// FContractBillNo	STRING	合同单号
                'FICMOBillNo'=>"",// FICMOBillNo	STRING	生产任务单号
                'FICMOInterID'=>"0",// FICMOInterID	INTEGER	任务单内码
                'FPPBomEntryID'=>"0",// FPPBomEntryID	INTEGER	投料单分录号
                'FOrderInterID'=>"0",// FOrderInterID	INTEGER	订单内码
                'FOrderEntryID'=>"0",//FOrderEntryID	INTEGER	订单分录
                'FOrderBillNo'=>"",// FOrderBillNo	STRING	订单单号
                'FAllHookQTY'=>".0000000000",// FAllHookQTY	FLOAT	已钩稽数量
                'FAllHookAmount'=>".0000000000",// FAllHookAmount	FLOAT	已钩稽金额
                'FCurrentHookQTY'=>".0000000000",// FCurrentHookQTY	FLOAT	本期钩稽数量
                'FCurrentHookAmount'=>".0000000000",// FCurrentHookAmount	FLOAT	本期钩稽金额
                'FStdAllHookAmount'=>".0000000000",// FStdAllHookAmount	FLOAT	已钩稽金额(本位币)
                'FStdCurrentHookAmount'=>".0000000000",// FStdCurrentHookAmount	FLOAT	本期钩稽金额(本位币)
                'FSCStockID'=>"0",// FSCStockID	INTEGER	调出仓库
                'FDCStockID'=>"391",// FDCStockID	INTEGER	调入仓库
                'FPeriodDate'=>"",// FPeriodDate	DATETIME	有效期至
                'FCostObjGroupID'=>"0",// FCostObjGroupID	INTEGER	成本对象组
                'FCostOBJID'=>"0",// FCostOBJID	INTEGER	成本对象
                'FDetailID'=>"7",// FDetailID	INTEGER
                'FMaterialCostPrice'=>".0000000000",// FMaterialCostPrice	FLOAT
                'FReProduceType'=>"0",// FReProduceType	INTEGER	是否返工
                'FBomInterID'=>"0",// FBomInterID	INTEGER	客户BOM
                'FDiscountRate'=>".0000000000",// FDiscountRate	FLOAT	折扣率
                'FDiscountAmount'=>".00",// FDiscountAmount	FLOAT	折扣额
                'FSepcialSaleId'=>"0",// FSepcialSaleId	INTEGER	特价ID
                'FOutCommitQty'=>".0000000000000",// FOutCommitQty	FLOAT
                'FOutSecCommitQty'=>".0000000000000",// FOutSecCommitQty	FLOAT
                'FDBCommitQty'=>".0000000000000",// FDBCommitQty	FLOAT
                'FDBSecCommitQty'=>".0000000000000",// FDBSecCommitQty	FLOAT
                'FAuxQtyInvoice'=>"20.0000000000",// FAuxQtyInvoice	FLOAT	开票数量
                'FOperSN'=>"0",// FOperSN	INTEGER	工序号
                'FCheckStatus'=>"0",// FCheckStatus	INTEGER	审核标志
                'FSplitSecQty'=>"",// FSplitSecQty	FLOAT	拆分辅助数量
                'FInStockID'=>"0",//
                'FSaleCommitQty'=>".0000000000",
                'FSaleSecCommitQty'=>".0000000000",
                'FSaleAuxCommitQty'=>".0000000000",
                'FSelectedProcID'=>"0",
                'FVWInStockQty'=>".0000000000",
                'FAuxVWInStockQty'=>".0000000000",
                'FSecVWInStockQty'=>".0000000000",
                'FSecInvoiceQty'=>".0000000000",
                'FCostCenterID'=>"0",
                'FPlanMode'=>"14036",// FPlanMode	STRING	计划模式
                'FMTONo'=>"",// FMtoNo	STRING	计划跟踪号
                'FSecQtyActual'=>".0000000000",
                'FSecQtyMust'=>".0000000000",
                'FClientOrderNo'=>"",
                'FClientEntryID'=>"0",
                'FRowClosed'=>"0",
                'FCostPercentage'=>".00",
                'FItemSize'=>"",
                'FItemSuite'=>"",
                'FPositionNo'=>"",
                'FAcctCheck'=>"0",
                'FClosing'=>"0",
                'FDeliveryNoticeEntryID'=>"0",
                'FDeliveryNoticeFID'=>"0",
                'FIsVMI'=>"0",
                'FEntrySupply'=>"0",
                'FChkPassItem'=>"1058",
                'FSEOutInterID'=>"0",
                'FSEOutEntryID'=>"0",
                'FSEOutBillNo'=>"",
                'FConfirmMemEntry'=>"",
                'FWebReturnQty'=>".0000000000000",
                'FWebReturnAuxQty'=>".0000000000000",
                'FItemStatementBillNO'=>"",
                'FItemStatementEntryID'=>"0",
                'FItemStatementInterID'=>"0",
                'FCommitAmt'=>"49557.5200000000",
                'FFatherProductID'=>"0",
                'FRealAmount'=>".0000000000",
                'FRealPrice'=>".0000000000",
                'FDefaultBaseQty'=>".0000000000",
                'FDefaultQty'=>".0000000000",
                'FRealStockBaseQty'=>".0000000000",
                'FRealStockQty'=>".0000000000",
                'FDiscardID'=>"0",
                'FOLOrderBillNo'=>"",
                'FLockFlag'=>"0",
                'FReturnNoticeBillNO'=>"",
                'FReturnNoticeEntryID'=>"0",
                'FReturnNoticeInterID'=>"0",
                'FProductFileQty'=>".0000000000",
                'FServiceRequestNo'=>"",
                'FSplitState'=>"",
                'FQtySplit'=>".0000000000",
                'FAuxQtySplit'=>".0000000000",
                'FAddQty'=>".0000000000000",
                'FAuxAddQty'=>".0000000000000",
                'FPurchasePrice'=>".0000000000",
                'FPurchaseAmount'=>".0000000000",
                'FCheckAmount'=>".0000000000",
                'FOutSourceInterID'=>"0",
                'FOutSourceEntryID'=>"0",
                'FOutSourceTranType'=>"0",
                'FProcessTaxPrice'=>".0000000000",
                'FProcessTaxCost'=>".0000000000",
                'FShopName'=>"",
                'FPostFee'=>".00",
                'FReviewBillsQty'=>".0000000000",
                'FPTLQty'=>".0000000000"
            ];
        throw  new  Exception(json_encode($exec));
    }
}