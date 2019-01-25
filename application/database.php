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


//return array(
//    //'配置项'=>'配置值'
//    'DB_TYPE'   => 'mysqli', // 数据库类型
//    'DB_HOST'               => 'rm-bp1y19j04xs816r5s532.mysql.rds.aliyuncs.com', // 服务器地址
//    'DB_NAME'               => 'xsrb',          // 数据库名
//    'DB_USER'               => 'jinxiaocun',      // 用户名
//    'DB_PWD'                => 'hfjdhs543@6542jfkd',          // 密码
//    'DB_PORT'               => '3306',        // 端口
//    'DB_PREFIX'             => '',    // 数据库表前缀
//    'SHOW_PAGE_TRACE' =>false,  //
//    'SHOW_ERROR_MSG' =>    false,
//    'CACHE_NAME'			=>	'xsrb',		//ACE缓存名称
//    'Cache_TimeOut_Token'	=>	60*60*24*15,		//token缓存时间，单位：秒
//    'ERROR_MESSAGE'  =>    '发生错误！',
//    'REDIS_URL'=>'r-bp1e313c4f1b2f24.redis.rds.aliyuncs.com',//redis的url
//    'REDIS_PWD'=>'jwRzwEoB2eTCMvkddhXffekg',
//    'MEM_URL'=>'m-bp1be9dbba6220c4.memcache.rds.aliyuncs.com',
//    'UPEXCEL_URL'			=> XSRB_IP.'/upload/uploadfile.php?xlspath=',
//    'Controller_url'		=> XSRB_IP.'/index.php/Home',		//手动下载excel属于的url
//    'JXC_DATABASE_CONNECTION'=>'mysql://jinxiaocun:hfjdhs543@6542jfkd@rm-bp1y19j04xs816r5s532.mysql.rds.aliyuncs.com/jinxiaocun',
//    'jxc_domain'=>    'http://jxc.wsy.me'
//);
return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '192.111.111.221',
    // 数据库名
    'database'        => 'xsrb',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => '123456',
    // 端口
    'hostport'        => '3306',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => 'certificate',
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
    'params' =>[
        \PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8",
    ],
];
