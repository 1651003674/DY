<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/28
 * Time: 17:14
 */

namespace app\impressiom\controller;


use app\impressiom\model\WiseDB;
use app\impressiom\model\WiseModel;
use think\Controller;

class Dongyuanwise extends Controller
{
    /**
     * @name 同步科目资料
     * @return \think\response\Json
     */
    public function AsycAccountingsubject(){

        // 返回执行数据写入结果
        return ( new WiseModel( WiseDB::getConnect(request()->param('orgId') ) ) )->saveAccountingSubject(  request()->param('data') );
//        return json_encode(input("data"));
    }
}