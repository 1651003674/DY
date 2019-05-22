<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
$idStr = '186';
return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '127.0.0.1',
    // 数据库名
    'database'        => 'cloud_to_wise_mapping',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => 'huangchao',
    // 端口
    'hostport'        => '',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 自动读取主库数据
    'read_master'     => false,
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    // Builder类
    'builder'         => '',
    // Query类
    'query'           => '\\think\\db\\Query',
    // 是否需要断线重连
    'break_reconnect' => false,
    // 断线标识字符串
    'break_match_str' => [],

    /**
     * @name 印象集团 数据库链接
     */
    'db_wise' => [
        // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '192.168.86.'.$idStr,
        // 数据库名
        'database'    => 'AIS20190330095051',
        // 数据库用户名
        'username'    => 'sa',
        // 数据库密码
        'password'    => 'sasa',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    /**
     * 东源 wise 库链接
     */
    'db_dongYuan' => [
        // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '192.168.86.'.$idStr,
        // 数据库名
        'database'    => 'AIS20190518172329',
        // 数据库用户名
        'username'    => 'sa',
        // 数据库密码
        'password'    => 'sasa',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    /**
     * 英普 wise 库链接
     */
    'db_impressiom' => [
        // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '192.168.86.'.$idStr,
        // 数据库名
        'database'    => 'AIS20190518172403',
        // 数据库用户名
        'username'    => 'sa',
        // 数据库密码
        'password'    => 'sasa',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
];
