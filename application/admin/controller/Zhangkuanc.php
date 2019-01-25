<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 17:37
 */

namespace app\admin\controller;


use think\Loader;

class Zhangkuanc
{
//    预收账款/预收账款明细
    public function getysmx($token=null,$page=1,$pagesize=1000000,$stime=null,$etime=null,$type="暂存款明细"){
        header("Access-Control-Allow-Origin: *");
        $logic_zk = Loader::model('Zhangkuan','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_zk->getysmx($page,$pagesize,$stime,$etime,$type,$info["dept_id"]);
        return $ret;
    }
//    预收账款/预收账款总账
    public function getyshz($token=null,$page=1,$pagesize=10000000,$stime=null,$etime=null,$type="暂存款总账"){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Zhangkuan','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->getyshz($page,$pagesize,$stime,$etime,$type,$info["dept_id"]);
        return $ret;
    }

}