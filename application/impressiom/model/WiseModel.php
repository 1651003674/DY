<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/29
 * Time: 11:47
 */

namespace app\impressiom\model;

use think\Db;
use think\Exception;

class WiseModel
{
    /**
     * @name  操作在那个数据库链接上执行
     * @var null|Db
     */
    private $dbConnect = null;

    /**
     * WiseModel constructor.
     * @param Db $dbConnect 要执行操作的数据库链接
     */
    public function __construct( $dbConnect )
    {
        $this->dbConnect = $dbConnect;
    }

    /**
     * @name 保存 会计科目
     * @param $data
     * @return int|string
     */
    public function saveAccountingSubject( $data ){

        if(empty($data)) return returnData('未选择同步的数据!',400);

        // 获取 写入表的字段
//                return fieldArrayToCodeString($this->dbConnect->table('t_Account')->find());
        // 当前 wise 最大 科目内码
//        $FAccountID =  $this->dbConnect->table('t_Account')->max('FAccountID');

        // 科目类别 映射关系
        $FGROUPIDs = [
            '3001'=>'101',	//流动资产
            '3002'=>'102',	//非流动资产
            '3003'=>'201',	//流动负债
            '3004'=>'202',	//非流动负债
            '3005'=>'700',	//共同
            '3006'=>'300',	//所有者权益
            '3007'=>'400',	//成本
            '3008'=>'501',	//营业收入
            '3009'=>'502',	//营业成本及税金
            '3010'=>'503',	//期间费用
            '3011'=>'504',	//其他收益
            '3012'=>'505',	//其他损失
            '3013'=>'506',	//以前年度损益调整
            '3014'=>'507',	//所得税
            '3015'=>'601',	//表外科目
        ];

        $insertDataArray = [];
        $IsNumber = [];// 查询已存在 科目条件
        foreach ($data as $dataArray){
//            $dataArray["FNumber"] =9000;

            $IsNumber[] = $dataArray["FNumber"];// 所有要插入的科目 代码
//            $FAccountID += 1;// 生成新的科目内码

            // 转换数据字段为 wise 数据
            $fieldsArray =
                [
//                    'FAccountID'=>$FAccountID ,//INTEGER	科目内码 = 最大内码加 1
                    'FNumber'=>$dataArray["FNumber"],// STRING	科目代码
                    'FName'=>$dataArray['FName'],// STRING	科目名称
                    'FLevel'=>$dataArray['FLEVEL'],// INTEGER	科目级次
                    'FDetail'=>$dataArray['FISDETAIL'],// INTEGER	明细科目
                    'FParentID'=>$dataArray['FPARENTID'],// INTEGER	上级科目内码
                    'FRootID'=>$dataArray['FPARENTID'],// INTEGER	上级科目内码
                    'FGroupID'=>$FGROUPIDs[$dataArray['FGROUPID']],// INTEGER	科目类别内码
                    'FDC'=>$dataArray['FDC'],// INTEGER	借贷方向
//                    'FHelperCode'=>$dataArray['FHelperCode'],// FHelperCode	STRING	助记码
                    'FCurrencyID'=>"1",// FCurrencyID	 INTEGER	币别内码 默认 1 人民币
                    'FAdjustRate'=>"0",// FAdjustRate	INTEGER	期末调汇
//                    'FEarnAccountID'=>"",// FEarnAccountID	INTEGER	利润科目内码
                    'FQuantities'=>"0",// FQuantities 	INTEGER	数量辅助核算
                    'FUnitGroupID'=>"0",// FUnitGroupID	INTEGER	单位组内码
                    'FMeasureUnitID'=>"0",// FMeasureUnitID	INTEGER	单位内码
                    'FIsCash'=>$dataArray['FISCASH'] == 'false'?0:1,// FIsCash	INTEGER	现金类科目
                    'FIsBank'=>$dataArray['FISBANK'] == 'false'?0:1,// FIsBank	INTEGER	银行类科目
                    'FJournal'=>"0",// FJournal	INTEGER	输出明细账
                    'FContact'=>"0",// FContact	INTEGER	往来核算
                    'FIsCashFlow'=>"0",// FIsCashFlow 	INTEGER	现金等价物
                    'FDetailID'=>"0",// FDetailID	INTEGER	核算项目内码
                    'FAcnt'=>"0",// FAcnt	INTEGER	核算项目
                    'FLoan'=>"0",// FLoan	INTEGER	贷款科目
                    'FDpst'=>"0",// FDpst	INTEGER	定期存款
                    'FStatedDpst'=>"0",// FStatedDpst	INTEGER	活期存款
                    'FInterest'=>"0",// FInterest	INTEGER	是否利息科目
                    'FIsAcnt'=>"0",// FIsAcnt	INTEGER	是否账号
                    'FIsBudget'=>"0",// FIsBudget	INTEGER	是否预算科目
                    'FAcntID'=>"0",// FAcntID	INTEGER	账号
                    'FBrNo'=>"0",// FBrNo	STRING	公司代码
                    'FAcctint'=>"0",// FAcctint	INTEGER	是否计息
                    'FintRate'=>"0.0",// FintRate	FLOAT	利息率
//                    'FLastintDate'=>"",// FLastintDate	DATETIME	计息时间
                    'FAcntType'=>"0",// FAcntType	INTEGER	账户类型
//                    'FTradeNum'=>"",// FTradeNum	STRING	集团控制科目代码
                    'FControl'=>"0",// FControl	INTEGER
                    'FViewMsg'=>"0",// FViewMsg	INTEGER
//                    'FMessage'=>"",// FMessage	STRING
                    'FDelete'=>"0",// FDelete	INTEGER	是否禁用
                    'FIsBusi'=>"0",// FIsBusi	INTEGER
                    'FFullName'=>$dataArray['FFULLNAME'],// FFullName	STRING	全名
//                    'FModifyTime'=>"00000000000775BC",// FModifyTime	UnKnown
                    'FSystemType'=>"1",// FSystemType	INTEGER	系统标示
                    'FControlSystem'=>"0",
                    'FCFItemID'=>"0",
                    'FSubCFItemID'=>"0",
                ];
//            var_dump($fieldsArray);die();
            $insertDataArray[] = $fieldsArray;
        }

        try{
            // 检查 科目是否存在
            $IsNumbers = $this->dbConnect->table('t_Account')->where( 'FNumber','IN',$IsNumber )->select();

            if(!empty($IsNumbers)){
                $FNumbers = [];
                foreach ($IsNumbers as  $item){
                    $FNumbers[] = $item['FNumber'];
                }
                return returnData("科目：".join(',',$FNumbers).' 已存在...',400);
            }
            $execNum =  $this->dbConnect->table('t_Account')->insertAll( $insertDataArray );// 执行插入操作

            return returnData("成功同步了：".$execNum.' 条数据...');
        }catch (Exception $e){
            return  returnData($e->getMessage(),400);
        }
//        return $this->dbConnect->table('test')->insertAll( $data );// 执行插入操作
    }

    /**
     * @name 保存部门数据 到 wise
     * @param $data
     * @return false|string
     */
    public function saveDepartment($data)
    {
//        throw new  Exception('回调到了：'.json_encode( $data[0] ));
        $insertDataArray = [];
        foreach ($data as $dataArray) {
            // 转换数据字段为 wise 数据
            $fieldsArray =
                [
                    'FItemID'=>$dataArray['WFItemID'],//INTEGER	部门内码
                    'FBrNO'=>"0",// STRING	公司代码
                    'FManager'=>"0",// INTEGER	负责人
                    'FPhone'=>"",// STRING	电话
                    'FFax'=>"",// STRING	传真
                    'FNote'=>"",// STRING	备注
                    'FNumber'=>$dataArray["WFNumber"],// STRING	部门代码
                    'FName'=>$dataArray["WFName"],// STRING	部门名称
                    'FParentID'=>$dataArray['CPID'],// INTEGER	上级部门内码
//                    'FDProperty'=>$dataArray['FDEPTPROPERTY'],// INTEGER	属性
                    'FDStock'=>"",// INTEGER	部门库房
                    'FDeleted'=>"0",// INTEGER	是否禁用
                    'FShortNumber'=>$dataArray["WFNumber"],// STRING	部门简码
                    'FAcctID'=>"0",// INTEGER	科目内码
                    'FCostAccountType'=>"363",// INTEGER	成本核算类型
//                    'FModifyTime'=>"00000000000787C0",
                    'FCalID'=>"999",// INTEGER	工厂日历
                    'FPlanArea'=>"",// INTEGER	计划区域
                    'FOtherARAcctID'=>"0",//INTEGER	其他应收账款科目代码
                    'FOtherAPAcctID'=>"0",// INTEGER	其他应收账款科目代码
                    'FPreARAcctID'=>"0",//INTEGER	预付账款科目代码
                    'FPreAPAcctID'=>"0",//INTEGER	预付账款科目代码
                    'FIsCreditMgr'=>"0",// BOOLEAN	是否进行信用管理
//                    'FIsVDept'=>"0",
                ];
            $insertDataArray[] = $fieldsArray;
        }
        $this->dbConnect->table('t_Department')->insertAll($insertDataArray);// 执行插入操作
    }

    /**
     * @name 保存员工数据 到 wise
     * @param $data
     * @return false|string
     */
    public function saveEmployees($data)
    {
        //        throw new  Exception('回调到了：'.json_encode( $data[0] ));
        $insertDataArray = [];
        foreach ($data as $dataArray) {
            // 转换数据字段为 wise 数据
            $fieldsArray =
                [
                    'FAccountName'=>"",
                    'FAddress'=>"",// STRING	地址
                    'FAllotPercent'=>".0000000000",// FLOAT	分配率
                    'FAllotWeight'=>".0000000000000",
                    'FBankAccount'=>"",// FBankAccount	STRING	银行账号
                    'FBankID'=>"0",// FBankID	INTEGER	银行
                    'FBirthday'=>"",// FBirthday	DATETIME	生日
                    'FBrNO'=>"0",// FBrNO	STRING	公司代码
                    'FCityID'=>"0",//
                    'FCreditAmount'=>"",
                    'FCreditDays'=>"",
                    'FCreditLevel'=>"0",// FCreditLevel	INTEGER	信用级次
                    'FCreditPeriod'=>"0",// FCreditPeriod	INTEGER	信用期
                    'FDegree'=>"0",//FDegree	INTEGER	文化程度
                    'FDeleted'=>"0",//FDeleted	INTEGER	是否禁用
                    'FDepartmentID'=>"0",//FDepartmentID	INTEGER	部门内码
                    'FDuty'=>"0",//FDuty	INTEGER	职务
                    'FEmail'=>"",//FEmail	STRING	邮件地址
                    'FEmpGroup'=>"0",//FEmpGroup	INTEGER	职员组名称
                    'FEmpGroupID'=>"0",//FEmpGroupID	INTEGER	职员组代码
                    'FFingerprintCardNo'=>"",
                    'FGender'=>"1068",//FGender	INTEGER	性别
                    'FHireDate'=>"",//FHireDate	DATETIME	入职日期
                    'FID'=>"",//FID	STRING	身份证号码
                    'FIsCreditMgr'=>"0",//FIsCreditMgr	INTEGER	是否进行信用管理
                    'FItemDepID'=>"0",//FItemDepID	INTEGER	部门号
                    'FItemID'=>$dataArray['WFItemID'],//FItemID	INTEGER	职员内码
                    'FJobTypeID'=>"0",//FJobTypeID	INTEGER	工种
                    'FLeaveDate'=>"",//FLeaveDate	DATETIME	离职日期
                    'FMFGCardNo'=>"",
                    'FMobilePhone'=>"",
                    'FName'=>$dataArray['WFName'],//FName	STRING	姓名
                    'FNote'=>"",//FNote	STRING	备注
                    'FNumber'=>$dataArray['WFNumber'],//FNumber	STRING	职员代码
                    'FOperationGroup'=>"0",
                    'FOtherAPAcctID'=>"0",
                    'FOtherARAcctID'=>"0",
                    'FParentID'=>"0",//FParentID	INTEGER	上级代码
                    'FPersonalBank'=>"",
                    'FPhone'=>"",//FPhone	STRING	电话
                    'FPreAPAcctID'=>"0",
                    'FPreARAcctID'=>"0",
                    'FProfessionalGroup'=>"0",//FProfessionalGroup	INTEGER	业务组
                    'FProvinceID'=>"0",
                    'FShortNumber'=>$dataArray['WFNumber'],//FShortNumber	STRING	职员简码
                ];
            $insertDataArray[] = $fieldsArray;
        }
        $this->dbConnect->table('t_Emp')->insertAll($insertDataArray);// 执行插入操作
    }

    /**
     * @name 保存仓库数据 到 wise
     * @param $data
     * @return false|string
     */
    public function saveWarehouse($data)
    {
        //        throw new  Exception('回调到了：'.json_encode( $data[0] ));
        $insertDataArray = [];
        foreach ($data as $dataArray) {
            // 转换数据字段为 wise 数据
            $fieldsArray =
                [
                    'FItemID'=>$dataArray['WFItemID'],// FItemID	INTEGER	库房内码
                    'FBrNO'=>"0",// FBrNO	STRING	公司代码
                    'FHelperCode'=>"",// FHelperCode	STRING	助记码
                    'FEmpID'=>"0",// FEmpID	INTEGER	管理员
//                    'FAddress'=>$dataArray['FADDRESS'],//  FAddress	STRING	库房地址
//                    'FPhone'=>$dataArray['FTEL'],// FPhone	STRING	库房电话
                    'FProperty'=>"10",// FProperty	INTEGER	库房属性
                    'FBWorkShop'=>"",// FBWorkShop	INTEGER	是否为车间仓库
                    'FName'=>$dataArray["WFName"],// FName	STRING	库房名称
                    'FNumber'=>$dataArray["WFNumber"],// FNumber	STRING	库房代码
                    'FParentID'=>"0",// FParentID	INTEGER	上级内码
                    'FDeleted'=>"0",// FDeleted	INTEGER	是否禁用
                    'FShortNumber'=>$dataArray["WFNumber"],// FShortNumber	STRING	库房简码
                    'FTypeID'=>"500",// FTypeID	INTEGER	库房类型
                    'FIsStockMgr'=>"0",// FIsStockMgr	INTEGER	是否进行仓位管理
                    'FSPGroupID'=>"0",// FSPGroupID	INTEGER	仓位组ID
                    'FMRPAvail'=>"1",// FMRPAvail	INTEGER	是否MRP可用量
                    'FGroupID'=>"0",// FGroupID	INTEGER	组ID
                    'FStockGroupID'=>"0",// FStockGroupID	INTEGER	仓位组ID
                    'FCalcCostOrder'=>"",// FCalcCostOrder	INTEGER	成本计算顺序
                    'FPlanArea'=>"",// FPlanArea	INTEGER	计划区域
                    'FUnderStock'=>"0",
                    'FIncludeAccounting'=>"1",
                    'FPTLEnable'=>"0",
                ];
            $insertDataArray[] = $fieldsArray;
        }
        $this->dbConnect->table('t_Stock')->insertAll($insertDataArray);// 执行插入操作
    }

    /**
     * @name 保存供应商基础资料 到 wise
     * @param $data
     * @return false|string
     */
    public function saveSupplier($data)
    {
        $insertDataArray = [];
        foreach ($data as $dataArray) {
            // 转换数据字段为 wise 数据
            $fieldsArray =[
                'FItemID'=>$dataArray['WFItemID'],//FItemID	INTEGER	供应商内码
                'FAddress'=>"",// FAddress	STRING	地址
                'FCity'=>"",//FCity	STRING	城市
                'FProvince'=>"",//FProvince	STRING	省份
                'FCountry'=>"",//FCountry	STRING	国家
                'FPostalCode'=>"",//FPostalCode	STRING	邮编
                'FPhone'=>"",// FPhone	STRING	电话
                'FFax'=>"",// FFax	STRING	传真
                'FEmail'=>"",// FEmail	STRING	邮件地址
                'FHomePage'=>"",// FHomePage	STRING	公司主页
                'FCreditLimit'=>"",// FCreditLimit	STRING	信用额度
                'FTaxID'=>"",// FTaxID	STRING	税务登记号
                'FBank'=>"",// FBank	STRING	银行
                'FAccount'=>"",//  FAccount	STRING	银行账号
                'FBrNo'=>"",// FBrNo	STRING	公司及其分公司代码
                'FBoundAttr'=>"",// FBoundAttr	INTEGER
                'FErpClsID'=>"",// FErpClsID	INTEGER
                'FShortName'=>$dataArray['WFName'],// FShortName	STRING	供应商简称
                'FPriorityID'=>"",// FPriorityID	INTEGER
                'FPOGroupID'=>"",// FPOGroupID	INTEGER
                'FStatus'=>"1072",// FStatus	INTEGER	状态
                'FLanguageID'=>"",// FLanguageID	INTEGER
                'FRegionID'=>"0",// FRegionID	INTEGER	区域代码
                'FTrade'=>"0",// FTrade	INTEGER	行业代码
                'FMinPOValue'=>"",// FMinPOValue	FLOAT
                'FMaxDebitDate'=>"",// FMaxDebitDate	FLOAT
                'FLegalPerson'=>"",// FLegalPerson	STRING
                'FContact'=>"",// FContact	STRING	联系人
                'FContactAcct'=>"",// FContactAcct	STRING
                'FPhoneAcct'=>"",// FPhoneAcct	STRING	电话号码
                'FFaxAcct'=>"",// FFaxAcct	STRING
                'FZipAcct'=>"",// FZipAcct	STRING
                'FEmailAcct'=>"",// FEmailAcct	STRING
                'FAddrAcct'=>"",// FAddrAcct	STRING
                'FTax'=>"",// FTax	FLOAT
                'FCyID'=>"0",// FCyID	INTEGER	结算币种
                'FSetID'=>"0",// FSetID	INTEGER	结算方式
                'FSetDLineID'=>"",// FSetDLineID	INTEGER
                'FTaxNum'=>"",// FTaxNum	STRING	税务登记号
                'FPriceClsID'=>"",// FPriceClsID	INTEGER
                'FOperID'=>"",// FOperID	INTEGER
                'FCIQNumber'=>"",// FCIQNumber	STRING
                'FDeleted'=>"0",// FDeleted	INTEGER	是否禁用
                'FSaleMode'=>"1057",// FSaleMode	INTEGER
                'FName'=>$dataArray['WFName'],//FName	STRING	供应商名称
                'FNumber'=>$dataArray['WFNumber'],// FNumber	STRING	供应商代码
                'FParentID'=>"0",// FParentID	INTEGER	上级內码
                'FShortNumber'=>$dataArray['WFNumber'],// FShortNumber	STRING	供应商简码
                'FARAccountID'=>"0",// FARAccountID	INTEGER
                'FAPAccountID'=>"0",//FAPAccountID	INTEGER	应付账款科目代码
                'FpreAcctID'=>"0",// FpreAcctID	INTEGER	预付账款科目代码
                'FlastTradeAmount'=>".0000",// FlastTradeAmount	FLOAT	最后交易金额
                'FLastRPAmount'=>".0000",// FLastRPAmount	FLOAT	最后付款金额
                'FFavorPolicy'=>"",// FFavorPolicy	STRING	优惠政策
                'Fdepartment'=>"0",// Fdepartment	INTEGER	分管部门
                'Femployee'=>"0",// Femployee	INTEGER	专营业务员
                'Fcorperate'=>"",// Fcorperate	STRING	法人代表
                'FBeginTradeDate'=>"",// FBeginTradeDate	DATETIME	开始交易日期
                'FEndTradeDate'=>"",// FEndTradeDate	DATETIME	结束交易日期
                'FLastTradeDate'=>"",// FLastTradeDate	DATETIME	最后交易日期
                'FLastReceiveDate'=>"",// FLastReceiveDate	DATETIME	最后付款日期
                'FcashDiscount'=>"",// FcashDiscount	STRING
                'FcurrencyID'=>"0",// FcurrencyID	INTEGER	币别ID
                'FMaxDealAmount'=>"0.0",// FMaxDealAmount	FLOAT	最大交易金额
                'FMinForeReceiveRate'=>"1.0",// FMinForeReceiveRate	FLOAT	最小预收比率(%)
                'FMinReserveRate'=>"0.0",// FMinReserveRate	FLOAT	最小订金比率(%)
                'FMaxForePayAmount'=>"0.0",// FMaxForePayAmount	FLOAT	最大预付比率(%)
                'FMaxForePayRate'=>"0.0",// FMaxForePayRate	FLOAT
                'FdebtLevel'=>"0",// FdebtLevel	INTEGER	偿还能力
                'FCreditDays'=>"0",// FCreditDays	INTEGER	信用期限
                'FValueAddRate'=>"17.00",// FValueAddRate	FLOAT	增值税率
                'FPayTaxAcctID'=>"0",// FPayTaxAcctID	INTEGER	应交税金科目代码
                'FDiscount'=>".0000000000",// FDiscount	FLOAT
                'FTypeID'=>"0",// FTypeID	INTEGER
                'FCreditAmount'=>"",// FCreditAmount	FLOAT
                'FCreditLevel'=>"",// FCreditLevel	STRING
                'FStockIDAssignee'=>"0",// FStockIDAssignee	INTEGER
                'FBr'=>"0",// FBr	INTEGER
                'FRegmark'=>"",// FRegmark	STRING
                'FLicAndPermit'=>"1",// FLicAndPermit	INTEGER
                'FLicence'=>"",// FLicence	STRING
                'FPaperPeriod'=>"",// FPaperPeriod	DATETIME
                'FAlarmPeriod'=>"",// FAlarmPeriod	INTEGER
                'FOtherARAcctID'=>"0",// FOtherARAcctID	INTEGER
                'FOtherAPAcctID'=>"0",// FOtherAPAcctID	INTEGER
                'FPreARAcctID'=>"0",// FPreARAcctID	INTEGER
                'FHelpCode'=>"",// FHelpCode	STRING
                'FNameEN'=>"",// FNameEN	STRING	英文名称
                'FAddrEn'=>"",// FAddrEn	STRING	英文地址
                'FCIQCode'=>"",//FCIQCode	STRING	海关注册码
                'FRegion'=>"0",// FRegion	INTEGER	国别地区
                'FMobilePhone'=>"",// FMobilePhone	STRING	手机号码
                'FManageType'=>"0",// FManageType	INTEGER	保税监管类型
                'FRegsterDate'=>"",//
            ];
            $insertDataArray[] = $fieldsArray;
        }
        $this->dbConnect->table('t_Supplier')->insertAll($insertDataArray);// 执行插入操作
    }

    /**
     * @name 保存客户资料 到 wise
     * @param $data
     * @return false|string
     */
    public function saveCustomer($data)
    {
        $insertDataArray = [];
        foreach ($data as $dataArray) {
            // 转换数据字段为 wise 数据
            $fieldsArray =[
                'FItemID'=>$dataArray['WFItemID'],// FItemID	INTEGER	客户内码
                'FAddress'=>"",// FAddress	STRING	地址
                'FCity'=>"",// FCity	STRING	城市
                'FProvince'=>"",// FProvince	STRING	省份
                'FCountry'=>"",// FCountry	STRING	国家
                'FPostalCode'=>"",// FPostalCode	STRING	邮编
                'FPhone'=>"",// FPhone	STRING	电话号码
                'FFax'=>"",// FFax	STRING	传真号
                'FEmail'=>"",// FEmail	STRING	邮件地址
                'FHomePage'=>"",// FHomePage	STRING	公司主页
                'FCreditLimit'=>"",// FCreditLimit	STRING	信用额度
                'FTaxID'=>"",// FTaxID	STRING
                'FBank'=>"",// FBank	STRING	开户银行
                'FAccount'=>"",// FAccount	STRING	银行账号
                'FBankNumber'=>"",// FBankNumber	STRING
                'FBrNo'=>"",// FBrNo	STRING	公司代码
                'FBoundAttr'=>"",// FBoundAttr	INTEGER
                'FErpClsID'=>"",// FErpClsID	INTEGER	产品类别
                'FShortName'=>$dataArray["WFName"],// FShortName	STRING	客户简称
                'FPriorityID'=>"",// FPriorityID	INTEGER
                'FPOGroupID'=>"",// FPOGroupID	INTEGER
                'FStatus'=>"1072",// FStatus	INTEGER	状态
                'FLanguageID'=>"",// FLanguageID	INTEGER	语言
                'FRegionID'=>"0",// FRegionID	INTEGER	区域代码
                'FTrade'=>"0",// FTrade	INTEGER	行业代码
                'FMinPOValue'=>"",// FMinPOValue	FLOAT
                'FMaxDebitDate'=>"",// FMaxDebitDate	FLOAT
                'FLegalPerson'=>"",// FLegalPerson	STRING
                'FContact'=>"",// FContact	STRING	联系人
                'FContactAcct'=>"",// FContactAcct	STRING	对应会计科目
                'FPhoneAcct'=>"",// FPhoneAcct	STRING
                'FFaxAcct'=>"",// FFaxAcct	STRING
                'FZipAcct'=>"",// FZipAcct	STRING
                'FEmailAcct'=>"",// FEmailAcct	STRING
                'FAddrAcct'=>"",// FAddrAcct	STRING
                'FTax'=>"",// FTax	FLOAT
                'FCyID'=>"0",// FCyID	INTEGER	结算币种
                'FSetID'=>"0",// FSetID	INTEGER	结算方式
                'FSetDLineID'=>"",// FSetDLineID	INTEGER
                'FTaxNum'=>"",// FTaxNum	STRING	税务登记号
                'FPriceClsID'=>"",// FPriceClsID	INTEGER	单价类别
                'FOperID'=>"",// FOperID	INTEGER	操作人ID
                'FCIQNumber'=>"",// FCIQNumber	STRING	海关代码
                'FDeleted'=>"0",// FDeleted	INTEGER	是否禁用
                'FSaleMode'=>"1057",// FSaleMode	INTEGER	销售模式
                'FName'=>$dataArray["WFName"],//FName	STRING	客户名称
                'FNumber'=>$dataArray["WFNumber"],// FNumber	STRING	客户代码
                'FParentID'=>"0",// FParentID	INTEGER	上级内码
                'FShortNumber'=>$dataArray["WFNumber"],// FShortNumber	STRING	客户简码
                'FARAccountID'=>"0",// FARAccountID	INTEGER	应收账款科目代码
                'FAPAccountID'=>"0",// FAPAccountID	INTEGER
                'FpreAcctID'=>"0",// FpreAcctID	INTEGER	预收账款科目代码
                'FlastTradeAmount'=>".0000",// FlastTradeAmount	FLOAT
                'FlastRPAmount'=>".0000",// FlastRPAmount	FLOAT	最后收款金额
                'FfavorPolicy'=>"",// FfavorPolicy	STRING	优惠政策
                'Fdepartment'=>"0",// Fdepartment	INTEGER	分管部门
                'Femployee'=>"0",// Femployee	INTEGER	专营业务员
                'Fcorperate'=>"",// Fcorperate	STRING	法人代表
                'FbeginTradeDate'=>"",// FbeginTradeDate	DATETIME	开始交易日期
                'FendTradeDate'=>"",// FendTradeDate	DATETIME	最后交易日期
                'FlastTradeDate'=>"",// FlastTradeDate	DATETIME	最后交易日期
                'FlastReceiveDate'=>"",// FlastReceiveDate	DATETIME	最后收款日期
                'FcashDiscount'=>"",// FcashDiscount	STRING
                'FcurrencyID'=>"0",// FcurrencyID	INTEGER	结算币种
                'FmaxDealAmount'=>"0.0",// FmaxDealAmount	FLOAT	最大交易金额
                'FminForeReceiveRate'=>"1.0",// FminForeReceiveRate	FLOAT	最小预收比率(%)
                'FminReserverate'=>"1.0",// FminReserverate	FLOAT	最小订金比率(%)
                'FdebtLevel'=>"0",// FdebtLevel	INTEGER	偿债等级
                'FCarryingAOS'=>"0",// FCarryingAOS	INTEGER	默认运输提前期(天)
                'FIsCreditMgr'=>"0",// FIsCreditMgr	INTEGER	是否进行信用管理
                'FCreditPeriod'=>"0",// FCreditPeriod	INTEGER
                'FCreditLevel'=>"0",// FCreditLevel	INTEGER
                'FPayTaxAcctID'=>"0",// FPayTaxAcctID	INTEGER	应交税金科目代码
                'FValueAddRate'=>"17.00",// FValueAddRate	FLOAT	增值税率
                'FTypeID'=>"0",// FTypeID	INTEGER
                'FCreditDays'=>"",// FCreditDays	INTEGER
                'FCreditAmount'=>"",// FCreditAmount	FLOAT
                'FStockIDAssign'=>"0",// FStockIDAssign	INTEGER
                'FStockIDInst'=>"0",// FStockIDInst	INTEGER
                'FStockIDKeep'=>"0",//  FStockIDKeep	INTEGER
                'FPaperPeriod'=>"",// FPaperPeriod	DATETIME
                'FAlarmPeriod'=>"",// FAlarmPeriod	INTEGER
                'FLicAndPermit'=>"1",// FLicAndPermit	INTEGER
                'FOtherARAcctID'=>"0",// FOtherARAcctID	INTEGER
                'FOtherAPAcctID'=>"0",//FOtherAPAcctID	INTEGER
                'FPreAPAcctID'=>"0",// FPreAPAcctID	INTEGER
                'FSaleID'=>"0",// FSaleID	INTEGER
                'FHelpCode'=>"",// FHelpCode	STRING
                'FNameEN'=>"",// FNameEN	STRING	英文名称
                'FAddrEn'=>"",// FAddrEn	STRING	英文地址
                'FCIQCode'=>"",//FCIQCode	STRING	海关注册码
                'FRegion'=>"0",// FRegion	INTEGER	国别地区
                'FMobilePhone'=>"",// FMobilePhone	STRING	手机号码
                'FPayCondition'=>"0",// FPayCondition	INTEGER	收款条件
                'FManageType'=>"0",// FManageType	INTEGER	保税监管类型
                'FClass'=>"0",// FClass	INTEGER	客户级别
                'FValue'=>"",// FValue	其它	客户价值
//                'FRegUserID'=>"16394",//
                'FLastModifyDate'=>"",// FLastModifyDate	DATETIME	最近修改日期
                'FRecentContactDate'=>"",// FRecentContactDate	DATETIME	最后接触日期
                'FRegDate'=>"2019-03-26 10:21:34.000",// FRegDate	DATETIME	登记日期
                'FFlat'=>"1",//FFlat	INTEGER	交易客户
//                'FClassTypeID'=>"1012000",// FClassTypeID	INTEGER	单据编码
            ];
            $insertDataArray[] = $fieldsArray;
        }
        $this->dbConnect->table('t_Organization')->insertAll($insertDataArray);// 执行插入操作
    }

    /**
     * @name 保存物料基础资料 到 wise
     * @param $data
     * @return false|string
     */
    public function saveProduct($data)
    {
        $insertDataArray = [];
        foreach ($data as $dataArray) {
            // 转换数据字段为 wise 数据
            $fieldsArray =[
                'FItemID'=>$dataArray['WFItemID'],// FItemID	INTEGER	产品内码
                'FModel'=>"",// FModel	STRING	规格型号
                'FName'=>$dataArray["WFName"],// FName	STRING	产品名称
                'FHelpCode'=>"",//FHelpCode	STRING	助记码
                'FDeleted'=>"0",// FDeleted	INTEGER	是否禁用
                'FShortNumber'=>$dataArray["WFNumber"],// FShortNumber	STRING	产品简码
                'FNumber'=>$dataArray["WFNumber"],// FNumber	STRING	产品代码
//                'FParentID'=>"410",// FParentID	INTEGER	产品类代码
                'FBrNo'=>"0",// FBrNo	STRING	公司代码
                'FTopID'=>"0",// FTopID	INTEGER	最高级
                'FRP'=>"",// FRP	INTEGER	收付标志
                'FOmortize'=>"",// FOmortize	INTEGER
                'FOmortizeScale'=>"",// FOmortizeScale	INTEGER
                'FForSale'=>"0",// FForSale	INTEGER	是否销售
                'FStaCost'=>"",// FStaCost	FLOAT
             'FOrderPrice'=>"0.0",// FOrderPrice	FLOAT	订货单价
                'FOrderMethod'=>"",// FOrderMethod	INTEGER	订货方法
                'FPriceFixingType'=>"",// FPriceFixingType	INTEGER
                'FSalePriceFixingType'=>"",// FSalePriceFixingType	INTEGER
                'FPerWastage'=>"0.0",// FPerWastage	FLOAT
                'FARAcctID'=>"",// FARAcctID	INTEGER	应收科目
                'FPlanPriceMethod'=>"",// FPlanPriceMethod	INTEGER	计划方法
                'FPlanClass'=>"",// FPlanClass	INTEGER	计划类型
                'FPY'=>"",//
                'FPinYin'=>"",//
                'FErpClsID'=>"1",// FErpClsID	INTEGER	产品属性
                'FUnitID'=>"253",// FUnitID	INTEGER	单位内码
                'FUnitGroupID'=>"252",// FUnitGroupID	INTEGER	单位组内码
                'FDefaultLoc'=>"0",// FDefaultLoc	INTEGER	缺省库房
                'FSPID'=>"0",// FSPID	INTEGER	缺省仓位
                'FSource'=>"0",// FSource	INTEGER	来源
                'FQtyDecimal'=>"0",// FQtyDecimal	INTEGER	数量精度
                'FLowLimit'=>".0000000000",// FLowLimit	FLOAT	最低存量
                'FHighLimit'=>"1000.0000000000",// FHighLimit	FLOAT	最高存量
                'FSecInv'=>".0000000000",// FSecInv	FLOAT	安全库存
                'FUseState'=>"341",// FUseState	INTEGER	使用状态
                'FIsEquipment'=>"0",// FIsEquipment	INTEGER	是否设备
                'FEquipmentNum'=>"",// FEquipmentNum	STRING	设备编码
                'FIsSparePart'=>"0",// FIsSparePart	INTEGER	是否备件
             'FFullName'=>$dataArray["WFName"],// FFullName	STRING	全名
                'FSecUnitID'=>"0",// FSecUnitID	INTEGER	辅助计量单位
                'FSecCoefficient'=>".0000000000",// FSecCoefficient	FLOAT	辅助计量单位换算率
                'FSecUnitDecimal'=>"0",//
                'FAlias'=>"",//FAlias	STRING	别名
                'FOrderUnitID'=>"253",// FOrderUnitID	INTEGER	采购计量单位
                'FSaleUnitID'=>"253",// FSaleUnitID	INTEGER	销售计量单位
                'FStoreUnitID'=>"253",// FStoreUnitID	INTEGER	库存计量单位
                'FProductUnitID'=>"253",// FProductUnitID	INTEGER	生产计量单位
                'FApproveNo'=>"",// FApproveNo	STRING	批准文号
                'FAuxClassID'=>"0",// FAuxClassID	INTEGER	辅助属性类别
                'FTypeID'=>"0",// FTypeID	INTEGER	产品分类
                'FPreDeadLine'=>"",//
                'FSerialClassID'=>"0",//
                'FDefaultReadyLoc'=>"0",//
                'FSPIDReady'=>"0",//
                'FDSManagerID'=>"0",//
                'FForbbitBarcodeEdit'=>"0",//
                'FOrderRector'=>"0",//FOrderRector	INTEGER	采购负责人内码
                'FPOHghPrcMnyType'=>"1",//   FPOHghPrcMnyType	INTEGER	采购最高价币别
                'FPOHighPrice'=>".0000000000",//FPOHighPrice	FLOAT	采购最高价
                'FWWHghPrc'=>".0000000000",//FWWHghPrc	FLOAT	委外加工最高价
                'FWWHghPrcMnyType'=>"1",// FWWHghPrcMnyType	INTEGER	委外加工最高价币别
                'FSOLowPrc'=>".0000000000",//FSOLowPrc	FLOAT	销售最低价
                'FSOLowPrcMnyType'=>"1",//FSOLowPrcMnyType	INTEGER	销售最低价币别
                'FIsSale'=>"0",//FIsSale	INTEGER	是否销售
                'FProfitRate'=>".0000000000",// FProfitRate	FLOAT	毛利率
                'FSalePrice'=>".0000000000",// FSalePrice	FLOAT	销售单价
                'FBatchManager'=>"0",// FBatchManager	INTEGER	是否采用业务批次管理
                'FISKFPeriod'=>"0",// FISKFPeriod	INTEGER	是否进行保质期管理
                'FKFPeriod'=>".0000000000",// FKFPeriod	FLOAT	保质期(天)
                'FTrack'=>"76",// FTrack	INTEGER	计价方法
                'FPlanPrice'=>".0000000000",// FPlanPrice	FLOAT	计划单价
                'FPriceDecimal'=>"2",// FPriceDecimal	INTEGER	单价精度
                'FAcctID'=>"1019",// FAcctID	INTEGER	存货科目
                'FSaleAcctID'=>"1084",// FSaleAcctID	INTEGER	销售科目
                'FCostAcctID'=>"1089",// FCostAcctID	INTEGER	成本科目
                'FAPAcctID'=>"0",// FAPAcctID	INTEGER	应付科目
                'FGoodSpec'=>"0",// FGoodSpec	INTEGER	税目代码内码
                'FCostProject'=>"0",//FCostProject	INTEGER	成本项目
                'FIsSnManage'=>"0",// FIsSnManage	INTEGER
                'FStockTime'=>"0",// FStockTime	INTEGER	是否需要库龄管理
                'FBookPlan'=>"0",// FBookPlan	INTEGER	是否需要进行订补货计划的运算
                'FBeforeExpire'=>"0",// FBeforeExpire	INTEGER	失效提前期(天)
             'FTaxRate'=>"13.0000000000",// FTaxRate	INTEGER	税率(%)
                'FAdminAcctID'=>"0",// FAdminAcctID	INTEGER	代管物资科目
                'FNote'=>"",// FNote	STRING	备注
                'FIsSpecialTax'=>"0",// FIsSpecialTax	INTEGER	是否农林计税
                'FSOHighLimit'=>".0000000000",// FSOHighLimit	FLOAT	销售超交比例(%)
                'FSOLowLimit'=>".0000000000",//FSOLowLimit	FLOAT	销售欠交比例(%)
                'FOIHighLimit'=>".0000000000",//FOIHighLimit	FLOAT	外购超收比例(%)
                'FOILowLimit'=>".0000000000",// FOILowLimit	FLOAT	外购欠收比例(%)
                'FDaysPer'=>"",//FDaysPer	INTEGER	每周/月第()天
                'FLastCheckDate'=>"",// FLastCheckDate	FLOAT	上次盘点日期
                'FCheckCycle'=>"",// FCheckCycle	INTEGER	盘点周期
                'FCheckCycUnit'=>"0",//FCheckCycUnit	INTEGER	盘点周期单位
                'FStockPrice'=>".0000000000",//
                'FABCCls'=>"",//  FABCCls	STRING	ABC类别
                'FBatchQty'=>"",// FBatchQty	FLOAT	订货批量
                'FClass'=>"0",// FClass	INTEGER	产品类别
                'FCostDiffRate'=>"",// FCostDiffRate	FLOAT	成本差异科目
                'FDepartment'=>"0",// FDepartment	INTEGER	部门
                'FSaleTaxAcctID'=>"",// FSaleTaxAcctID	INTEGER	税金科目
                'FCBBmStandardID'=>"0",// FCBBmStandardID	INTEGER	分配标准内码
                'FCBRestore'=>"0",//
                'FPickHighLimit'=>".0000000000",//
                'FPickLowLimit'=>".0000000000",//
                'FOnlineShopPName'=>"",//
                'FOnlineShopPNo'=>"",//
                'FUnitPackageNumber'=>".0000000000",//
                'FOrderDept'=>"0",//
                'FPlanTrategy'=>"321",//FPlanTrategy	INTEGER	计划策略
                'FOrderTrategy'=>"331",// FOrderTrategy	INTEGER	订货策略
                'FLeadTime'=>"0.0",// FLeadTime	FLOAT	提前期
                'FFixLeadTime'=>"0.0",// FFixLeadTime	FLOAT	固定提前期
                'FTotalTQQ'=>"0",// FTotalTQQ	INTEGER	累计提前期
                'FQtyMin'=>"1.0000000000",// FQtyMax	FLOAT	最大订货量
                'FQtyMax'=>"10000.0000000000",//FQtyMin	FLOAT	最小订货量
                'FCUUnitID'=>"0",// FCUUnitID	INTEGER	常用计量单位
                'FOrderInterVal'=>"0",// FOrderInterVal	INTEGER	订货间隔期(天)
                'FBatchAppendQty'=>"1.0000000000",// FBatchAppendQty	FLOAT	批量增量
                'FOrderPoint'=>".0000000000",// FOrderPoint	FLOAT	再订货点
                'FBatFixEconomy'=>".0000000000",// FBatFixEconomy	FLOAT	固定/经济批量
                'FBatChangeEconomy'=>"1.0000000000",// FBatChangeEconomy	FLOAT	变动提前期批量
                'FRequirePoint'=>"1",// FRequirePoint	INTEGER	需求时界(天)
                'FPlanPoint'=>"1",// FPlanPoint	INTEGER	计划时界(天)
                'FDefaultRoutingID'=>"0",// FDefaultRoutingID	INTEGER	默认工艺路线
                'FDefaultWorkTypeID'=>"0",// FDefaultWorkTypeID	INTEGER	默认生产类型
                'FProductPrincipal'=>"0",// FProductPrincipal	INTEGER	生产负责人
                'FDailyConsume'=>".0000000000",// FDailyConsume	FLOAT	日消耗量
                'FMRPCon'=>"1",// FMRPCon	INTEGER	MRP计算是否合并需求
                'FPlanner'=>"0",// FPlanner	INTEGER	计划员
                'FPutInteger'=>"0",// FPutInteger	INTEGER	投料自动取整
                'FInHighLimit'=>".0000000000",// FInHighLimit	FLOAT	完工超收比例(%)
                'FInLowLimit'=>".0000000000",// FInLowLimit	FLOAT	完工欠收比例(%)
                'FLowestBomCode'=>"",// FLowestBomCode	INTEGER	低层码
                'FMRPOrder'=>"0",// FMRPOrder	INTEGER	MRP计算是否产生采购申请
                'FIsCharSourceItem'=>"0",// FIsCharSourceItem	INTEGER	产品对应特性
                'FCharSourceItemID'=>"",// FCharSourceItemID	INTEGER	特性配置来源
                'FPlanMode'=>"14036",// FPlanMode	INTEGER	计划模式
                'FCtrlType'=>"14039",// FCtrlType	INTEGER	控制类型
                'FCtrlStraregy'=>"0",// FCtrlStraregy	INTEGER	控制策略
                'FContainerName'=>"",// FContainerName	STRING	容器名称
                'FKanBanCapability'=>"1",// FKanBanCapability	INTEGER	看板容量
                'FIsBackFlush'=>"0",//
                'FBackFlushStockID'=>"0",//
                'FBackFlushSPID'=>"0",//
                'FBatchSplitDays'=>"0",//
                'FBatchSplit'=>".0000000000",//
                'FIsFixedReOrder'=>"1",//
                'FAuxInMrpCal'=>"0",//
                'FProductDesigner'=>"0",//
                'FChartNumber'=>"",// FChartNumber	STRING	图号
                'FIsKeyItem'=>"0",// FIsKeyItem	INTEGER	是否关键件
                'FMaund'=>"0",// FMaund	INTEGER	重量单位
                'FGrossWeight'=>".0000000000",// FGrossWeight	FLOAT	毛重
                'FNetWeight'=>".0000000000",// FNetWeight	FLOAT	净重
                'FCubicMeasure'=>"0",// FCubicMeasure	INTEGER	长度单位
                'FLength'=>".0000000000",// FLength	FLOAT	长度
                'FWidth'=>".0000000000",// FWidth	FLOAT	宽度
                'FHeight'=>".0000000000",// FHeight	FLOAT	高度
                'FSize'=>".0000000000",// FSize	FLOAT	体积
                'FVersion'=>"",// FVersion	STRING	版本号
                'FStartService'=>"0",//
                'FMakeFile'=>"0",//
                'FIsFix'=>"0",//
                'FTtermOfService'=>"0",//
                'FTtermOfUsefulTime'=>"0",//
                'FStandardCost'=>".0000000000",//FStandardCost	FLOAT	单位标准成本
                'FStandardManHour'=>".0000000000",// FStandardManHour	FLOAT	单位标准工时(小时)
                'FStdPayRate'=>".0000000000",// FStdPayRate	FLOAT	标准工资率
                'FChgFeeRate'=>".0000000000",// FChgFeeRate	FLOAT	变动制造费用分配率
                'FStdFixFeeRate'=>".0000000000",// FStdFixFeeRate	FLOAT	单位标准固定制造费用金额
                'FOutMachFee'=>".0000000000",// FOutMachFee	FLOAT	单位委外加工费
                'FPieceRate'=>".0000000000",// FPieceRate	FLOAT	单位计件工资
                'FStdBatchQty'=>"1.0000000000",//
                'FPOVAcctID'=>"0",
                'FPIVAcctID'=>"0",
                'FMCVAcctID'=>"0",
                'FPCVAcctID'=>"0",
                'FSLAcctID'=>"0",
                'FCAVAcctID'=>"0",
                'FCBAppendRate'=>".0000000000",//
                'FCBAppendProject'=>"0",
                'FCostBomID'=>"0",
                'FCBRouting'=>"0",
                'FOutMachFeeProject'=>"0",
                'FInspectionLevel'=>"352",// FInspectionLevel	INTEGER	检验方式
                'FInspectionProject'=>"0",// FInspectionProject	INTEGER	检验方案
                'FIsListControl'=>"",// FIsListControl	INTEGER
                'FProChkMde'=>"352",// FProChkMde	INTEGER	产品检验方式
                'FWWChkMde'=>"352",// FWWChkMde	INTEGER	委外加工检验方式
                'FSOChkMde'=>"352",// FSOChkMde	INTEGER	发货检验方式
                'FWthDrwChkMde'=>"352",// FWthDrwChkMde	INTEGER	退货检验方式
                'FStkChkMde'=>"352", // FStkChkMde	INTEGER	库存检验方式
                'FOtherChkMde'=>"352",// FOtherChkMde	INTEGER	其他检验方式
                'FStkChkPrd'=>"9999",// FStkChkPrd	INTEGER	库存检验周期（天）
                'FStkChkAlrm'=>"0",// FStkChkAlrm 	INTEGER	库存周期检验预警提前期（天）
                'FIdentifier'=>"0",//FIdentifier	INTEGER	检验员
                'FSampStdCritical'=>"0",//
                'FSampStdStrict'=>"0",
                'FSampStdSlight'=>"0",
                'FNameEn'=>"",//FNameEn	STRING	英文名称
                'FModelEn'=>"",// FModelEn	STRING	英文规格
                'FHSNumber'=>"0",// FHSNumber	INTEGER	HS编码
                'FFirstUnit'=>"",// FFirstUnit	STRING	HS第一法定单位
                'FSecondUnit'=>"",// FSecondUnit	STRING	HS第二法定单位
                'FFirstUnitRate'=>".0000000000",// FFirstUnitRate	FLOAT	HS第一法定单位换算率
                'FSecondUnitRate'=>".0000000000",// FSecondUnitRate	FLOAT	HS第二法定单位换算率
                'FIsManage'=>"0",// FIsManage	INTEGER	是否保税监管
                'FPackType'=>"",//
                'FLenDecimal'=>"2",// FLenDecimal	INTEGER	长度精度
                'FCubageDecimal'=>"4",// FCubageDecimal	INTEGER	体积精度
                'FWeightDecimal'=>"2",// FWeightDecimal	INTEGER	重量精度
                'FImpostTaxRate'=>".0000000000",// FImpostTaxRate	FLOAT	进口关税率%
                'FConsumeTaxRate'=>".0000000000",// FConsumeTaxRate	FLOAT	进口消费税率%
                'FManageType'=>"0",// FManageType	INTEGER	保税监管类型
                'FExportRate'=>".0000000000",//
                'FBarcode'=>"",
            ];
            $insertDataArray[] = $fieldsArray;
        }
         $this->dbConnect->table('t_ICItem')->insertAll($insertDataArray);// 执行插入操作
    }
}