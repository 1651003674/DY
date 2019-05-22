<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19
 * Time: 15:47
 */

namespace app\impressiom\controller;


use app\Common\Cloud_webapi_client;
use app\impressiom\model\test;
use think\Controller;
use think\Db;
use PDO;

class Cloudapi extends Controller
{
    public function index(){
        $inputData = [
            'a',
            'b'
        ];

        $inputData = getParam($inputData);

        return response(json_encode($inputData),200,[],'application/json');

        $save_arr =[
            "FormId"=>"STK_InStock",
            "FieldKeys"=>"FID",
            "FilterString"=>"[FID>100002 AND FID<100026]",
            "OrderString"=>"",
            "TopRowCount"=>"0",
            "StartRow"=>"0",
            "Limit"=>"10"
        ];

        $data_model = json_encode(["data"=>$save_arr]);//  编码成 json 格式
        return (new Cloud_webapi_client())->getList($data_model);
    }

    public function sql(){

//        使用 nginx + 5.6.37 版本 phpStudy
//        开启Php扩展
//          php_pdo_sqlsrv
//          php_sqlsrv

//        php.ini 中添加
//        extension=php_pdo_sqlsrv_56_nts.dll
//        extension=php_sqlsrv_56_nts.dll

//        Db::table()->execute(); 执行写操作
//        Db::table()->query(); 执行查询操作


//        $db = Db::connect('db_wise')->query('select * from t_ItemClass ');

        $db = Db::connect('db_wise');

        $sql = $db->table('t_ItemClass')->fetchSql()->find();
        $msql = (new test())->getData();
//        print_r($db->query($sql));
//        return $msql;

        $a =[
            'FItemClassID'=>"0",
            'FNumber'=>"*",
            'FName'=>"*",
            'FName_cht'=>"*",
            'FName_en'=>"*",
            'FSQLTableName'=>"",
            'FVersion'=>"0",
            'FImport'=>"0",
            'FBrNo'=>"0",
            'FUserDefilast'=>"100",
            'FType'=>"1",
            'FGRType'=>"0",
            'FRemark'=>"",
            'FModifyTime'=>"0000000000033743",
            'FGrControl'=>"0",
            'UUID'=>"9714A906-6459-49FC-9E3F-23236FCEEC7D",
            'ROW_NUMBER'=>"1",
        ];
        return fieldArrayToCodeString($db->query($sql)[0]);// 输出
//        var_dump($sql);
    }

    public function getSupplierList()
    {

        $db = Db::connect('db_wise')->query('select * from t_ItemClass ');
        var_dump($db);


    }

    /**
     * @name 获取基础资料 科目列表
     * @return \app\Common\mixed\
     */
    public function getAccountingsubjectList(){
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
}