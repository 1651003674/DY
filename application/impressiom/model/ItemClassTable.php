<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/8
 * Time: 9:46
 */

namespace app\impressiom\model;


use think\Db;
use think\Exception;

/**
 * @name 基础资料 同步器
 * Class ItemClassTable
 * @package app\impressiom\model
 */
class ItemClassTable
{
    /**
     * @var null|Db
     */
    protected $dbConnect = null;

    /**
     * ItemClassTable constructor.
     * @param Db $dbConnect
     */
    public function __construct( $dbConnect)
    {
        $this->dbConnect = $dbConnect;

    }

    /**
     * @name 基础资料 类型列表
     * @var array
     */
    private $ItemClass = [
        '1'=>[
            'FNumber'=>'001',
            'FName'=>'客户',
            'FTable'=>'t_Organization'
        ],
        '2'=>[
            'FNumber'=>'002',
            'FName'=>'部门',
            'FTable'=>'t_Department'
        ],
        '3'=>[
            'FNumber'=>'003',
            'FName'=>'职员',
            'FTable'=> 't_Emp'
        ],
        '4'=>[
            'FNumber'=>'004',
            'FName'=>'物料',
            'FTable'=> 't_ICItem'
        ],
        '5'=>[
            'FNumber'=>'005',
            'FName'=>'仓库',
            'FTable'=> 't_Stock'
        ],
        '8'=>[
            'FNumber'=>'008',
            'FName'=>'供应商',
            'FTable'=> 't_Supplier'
        ]
    ];

    //        $arr = $this->dbConnect->table('t_Item')->where(['FItemID'=>249])->find();
    // 基础资料总表字段
//    private $t_Item = [
////            'FItemID'=>"249",//FItemID	INTEGER	ID号
//        'FItemClassID'=>"1",//FItemClassID	INTEGER	类型ID号
//        'FExternID'=>"-1",// FExternID	INTEGER	外键ID
//        'FNumber'=>"001",// FNumber	STRING	编码
//        'FParentID'=>"0",// FParentID	INTEGER	父ID
//        'FLevel'=>"1",//FLevel	INTEGER	级别
//        'FDetail'=>"1",// FDetail	INTEGER	是否明细
//        'FName'=>"成都印象电子有限公司",// FName	STRING	名称
//        'FUnUsed'=>"0",// FUnUsed	INTEGER	是否使用
//        'FBrNo'=>"0",// FBrNo	STRING	公司代码
//        'FFullNumber'=>"001",// FFullNumber	STRING	全编码
//        'FDiff'=>"0",//
//        'FDeleted'=>"0",// FDeleted	INTEGER	是否删除
//        'FShortNumber'=>"001",// FShortNumber	STRING	短代码
//        'FFullName'=>"成都印象电子有限公司",// FFullName	STRING	全名
////            'UUID'=>"5D956B91-66DA-4308-A30E-79AE7DF003DF",
//        'FGRCommonID'=>"-1",
//        'FSystemType'=>"1",//FSystemType	INTEGER	系统类型
//        'FUseSign'=>"0",//FUseSign	INTEGER	使用标记
//        'FChkUserID'=>"",//FChkUserID	INTEGER	审核人ID
//        'FAccessory'=>"0",
//        'FGrControl'=>"-1",//FGrControl	INTEGER	下发受控类型
////            'FModifyTime'=>"0000000000078689",//FModifyTime	UnKnown	修改时间
//        'FHavePicture'=>"0",
//    ];


    // 流程：
    // 获取 cloud 数据
    // 写入到 wise 基础资料总表,获得基础资料 ItemID 【检查 基础资料是否存在】
    // 组合基础资料映射信息，并写入到映射信息数据库中【检查数据映射是否存在，存在就更新映射】

    /**
     * @name 保存基础资料
     * @param $ItemClass wise 基础资料内码
     * @param $callback 执行成功后的 回调函数
     * @return false|string|\think\response\Json
     */
    public function save( $ItemClass ,$callback){
        $this->dbConnect->startTrans();// 开启wise 事务
        Db::startTrans();// 开启 映射事务
        try{

            $exec =  $this->saveItem( $ItemClass , $callback );

            $this->dbConnect->commit();// 提交wise 事务
            Db::commit();//  提交 映射事务

            return $exec;// 返回请求执行响应
        }catch (Exception $e)
        {
            $this->dbConnect->rollback();// 回滚 wise 事务
            Db::rollback();// 回滚 映射事务

            return returnData( $e->getMessage(),400 );
        }
//        $this->dbConnect->transaction(function (){
//            $this->dbConnect->table();
//            $this->dbConnect->table();
//        });
    }

    /**
     * @name 保存基础资料
     * @param $ItemClass 基础资料类型
     * @param $callback
     * @return false|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private  function saveItem( $ItemClass ,$callback )
    {
        $dataArray = request()->param();// 请求数据

        if (empty($dataArray['data'] )) throw new Exception('未选择要传入的数据！');
        $FNumberArray = [];// 要写入的编码
        foreach ( $dataArray['data'] as $line){
            $FNumberArray[] = $line['FNumber'];
        }

        // 获取已存在的基础资料列表
        $isItems = $this->dbConnect->table('t_Item')
            ->where('FNumber','IN',$FNumberArray)
            ->where(
                [
                    'FItemClassID'=>$ItemClass,
                ]
            )
            ->select();
        if(!empty($isItems)){// 有在wise 已存在的 基础资料时
            $isItemsInfo = [];
            foreach ($isItems as  $ItemLine){
                $isItemsInfo[] = $ItemLine['FName'];
            }
            $msgArr = [
                $this->ItemClass[$ItemClass]['FName'],
                '基础资料 ：',
                '( ',
                join(',',$isItemsInfo),
                ' )',
                ' 在Wise中已存在 !'
            ];
            throw  new Exception(join('',$msgArr));
        }

        // 循环处理 要传入到wise的基础资料
        foreach ( $dataArray['data'] as $line ){
            // 要写入基础资料 总表的数据
            $wiseItemArray = [
//            'FItemID'=>"249",//FItemID	INTEGER	ID号
                'FItemClassID'=>$ItemClass,//FItemClassID	INTEGER	类型ID号
                'FNumber'=>$line['FNumber'],// FNumber	STRING	编码
                'FParentID'=>empty($line['FPARENTID'])?0:$line['FPARENTID'],// FParentID	INTEGER	父ID
                'FLevel'=>"1",//FLevel	INTEGER	级别
                'FDetail'=>"1",// FDetail	INTEGER	是否明细
                'FName'=>$line['FName'],// FName	STRING	名称
                'FFullNumber'=>$line['FNumber'],// FFullNumber	STRING	全编码
                'FShortNumber'=>$line['FNumber'],// FShortNumber	STRING	短代码
                'FFullName'=>$line['FName'],// FFullName	STRING	全名
            ];

            // 写入基础资料到 wise 基础资料总表
            $this->dbConnect->table('t_Item')->insert( $wiseItemArray );
        }

        $insertsInfo =  $this->dbConnect->table('t_Item')
            ->where(
                [
                    'FItemClassID'=>$ItemClass,//FItemClassID	INTEGER	类型ID号
                ]
            )
            ->where('FNumber','IN',$FNumberArray)
            ->select();
        // 组建 映射数据信息
        $mappingDataArray =  $this->manger_mapping_array($dataArray['data'],$insertsInfo);
        $this->saveMapping($mappingDataArray);// 调用保存 基础资料映射
        $callback( $mappingDataArray );// 调用 回调 将数据写入到 对应基础资料数据表中
        $reMsgArr = [
            '成功同步了',
            count($mappingDataArray),
            '条 ',
            $this->ItemClass[$ItemClass]['FName'],
            '基础资料 ...',
        ];
        return returnData(join($reMsgArr),200);
    }

    /**
     * @name 合并 映射数据
     * @param array $inputDataArray 请求传入的数据
     * @param array $insertdDataArray 插入到 wise 基础资料总表的数据
     * @return array
     */
    private function manger_mapping_array( $inputDataArray=[] , $insertdDataArray=[] )
    {
        $mangerArray = [];
        foreach ($insertdDataArray as $value){

            foreach ($inputDataArray as $Ivalue){
                if($value['FNumber'] != $Ivalue['FNumber']) continue;// 不匹配 结束本次循环

                $mappingArray = [
//            'id'=>"1",// 自增ID
                    'WFItemID'=>$value['FItemID'],// Wise 基础资料ID
                    'FItemClassID'=>$value['FItemClassID'],// Wise 基础资料类别ID
                    'WFNumber'=>$value['FNumber'],// Wise 基础资料编码
                    'WFParentID'=>'',// Wise 基础资料 父id
                    'WFLevel'=>"",// Wise 基础资料 级别
                    'WFDetail'=>"",// Wise 是否明细
                    'WFName'=>$value['FName'],// Wise 基础资料 名称
                    'WFFullNumber'=>$value['FNumber'],// 全编码
                    'WFShortNumber'=>$value['FNumber'],// 短代码
                    'WFFullName'=>$value['FName'],// 全名
                    'CFID'=>$Ivalue['FID'],// cloud 内码
                    'CFNumber'=>$Ivalue['FNumber'],// 'cloud 代码
                    'CFUSEORGID'=>request()->param('orgId'),// cloud 使用组织内码
                    'CPID'=>empty($Ivalue['FPARENTID'])?0:$Ivalue['FPARENTID'],// Cloud 父ID
                ];
                $mangerArray[] = $mappingArray;// 将当前匹配到的数据
                break;// 匹配到当前数据后 结束循环
            }
        }
        return $mangerArray;
    }

    /**
     *  @name 保存映射 关系信息
     * @param array $mappingDataArray
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private function saveMapping($mappingDataArray = [])
    {
        // 将创建好的 映射关系写入到 映射表
        foreach ($mappingDataArray as $mappingData){
            $mappingID = Db::table('t_ItemMapping')
                ->field(['id'])
                ->where([
                    'FItemClassID'=>$mappingData['FItemClassID'],
                    'CFID'=>$mappingData['CFID'],
                ])
                ->find();
            if(empty($mappingID)){// 如果是未同步过得数据
                Db::table('t_ItemMapping')->insert( $mappingData );// 就新插入
            }else{// 如果是同步过得数据
                Db::table('t_ItemMapping')
                    ->where([
                        'id'=>$mappingID
                    ])
                    ->update( $mappingData );// 就更新映射数据
            }
        }
    }

    /**
     * @name 按名称获取内码
     * @param $FItemClassID
     * @param $FName
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getWiseItemIDByNmae($FItemClassID,$FName){
        $Db = WiseDB::getConnect();// 获取新的连接

        $data = [
            'FItemClassID'=>$FItemClassID,// 基础资料类别ID
            'FName'=>$FName,//  Cloud 对应内码
        ];
        $WFItemID = $Db->table('t_Item')->where($data)->find();
        return $WFItemID['FItemID'];
    }
    /**
     * @name  获取对应的 wise 基础资料内码
     * @param $FItemClassID
     * @param $CFID
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getWiseItemID($FItemClassID,$CFID){
        $data = [
            'FItemClassID'=>$FItemClassID,// 基础资料类别ID
            'CFID'=>$CFID,//  Cloud 对应内码
            'CFUSEORGID'=>request()->param('orgId'),// 组织机构 对应 wise 账套
        ];
        $WFItemID = Db::table('t_itemmapping')->field(['WFItemID'])->where($data)->find();
        return $WFItemID['WFItemID'];
//        '1'=>[
//            'FNumber'=>'001',
//            'FName'=>'客户',
//            'FTable'=>'t_Organization'
//        ],
//        '2'=>[
//            'FNumber'=>'002',
//            'FName'=>'部门',
//            'FTable'=>'t_Department'
//        ],
//        '3'=>[
//            'FNumber'=>'003',
//            'FName'=>'职员',
//            'FTable'=> 't_Emp'
//        ],
//        '4'=>[
//            'FNumber'=>'004',
//            'FName'=>'物料',
//            'FTable'=> 't_ICItem'
//        ],
//        '5'=>[
//            'FNumber'=>'005',
//            'FName'=>'仓库',
//            'FTable'=> 't_Stock'
//        ],
//        '8'=>[
//            'FNumber'=>'008',
//            'FName'=>'供应商',
//            'FTable'=> 't_Supplier'
//        ]
    }
}