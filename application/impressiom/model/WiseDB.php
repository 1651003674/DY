<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/24
 * Time: 13:47
 */

namespace app\impressiom\model;

use think\Db;

/**
 * Class WiseDB wise
 * @package app\impressiom\model
 */
class WiseDB
{
    // 英普wise
    const DB_IMP = 'db_impressiom';

    // 东源wise
    const DB_DY = 'db_dongYuan';

    //组织id
    const YX_ORGID= 1;// 印象集团 1
    const YP_ORGID = 100001;// 英普 100001
    const DY_ORGID = 100002;// 东源 100002

    /**
     * @name  获取 impressiom 链接实例
     * @return mixed
     * @throws \think\Exception
     */
    public static function impressiom_DB(){
        $dbconnect = Db::connect(self::DB_IMP);
        return $dbconnect;// database.php 中配置
    }

    /**
     * @name 获取 东源 数据库链接实例
     * @return mixed
     * @throws \think\Exception
     */
    public static function dongYuan_DB(){
        return Db::connect(self::DB_DY);// database.php 中配置
    }

    /**
     * @name 获取对应组织的数据库链接
     * @param $orgID Cloud 组织ID
     * @return Db mixed|null
     * @throws \think\Exception
     */
    public static function getConnect( $orgID= null ){
        $dbConnectObj = null;
        ($orgID == null) ?$orgID = request()->param('orgId'):null;
        switch ( $orgID ){

            case self::YX_ORGID:
                $dbConnectObj = Db::connect(self::DB_IMP);
                break;

            case self::YP_ORGID:
                $dbConnectObj = Db::connect(self::DB_IMP);
                break;

            case self::DY_ORGID:
                $dbConnectObj = Db::connect(self::DB_DY);
                break;
        }
        return  $dbConnectObj;
    }

}