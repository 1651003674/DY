<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/7
 * Time: 9:54
 */

namespace app\impressiom\model;


use app\Common\Cloud_webapi_client;
use think\Db;

class DocumentsModel
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
     * @param $callback
     * @return false|string
     */
    public function save( $callback  ){

        $this->dbConnect->startTrans();// 开启wise 事务
//        Db::startTrans();// 开启 映射事务
        try{
//            $exec =  $this->saveMasterSingle( $ItemClass , $callback );
            $exec =  $callback( $this->dbConnect );// 回调 传入当前链接

            $this->dbConnect->commit();// 提交wise 事务
//            Db::commit();//  提交 映射事务

            return $exec;// 返回请求执行响应
        }catch (Exception $e)
        {
            $this->dbConnect->rollback();// 回滚 wise 事务
//            Db::rollback();// 回滚 映射事务

            return returnData( $e->getMessage().$e->getLine(),400 );
        }

    }

    /**
     * @name 调取 Cloud 接口 获取单据详情
     * @param $formid
     * @param $Number
     * @return mixed
     */
    public static function getCloudInfo( $formid , $Number){
        $PostData = [
            'formid'=>$formid,
            'data'=>[
                "CreateOrgId"=> 0,
                "Number"=> $Number,
                "Id"=>""
            ]
        ];
        return json_decode( (new Cloud_webapi_client())->View( json_encode( $PostData ) ), 1)['Result']['Result'];// 拿取单据实体数据
    }

}