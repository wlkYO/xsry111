<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5
 * Time: 15:42
 */

namespace app\admin\controller;

use think\Loader;
use think\Request;

class Qichuc
{
//    现金结存期初
    public function getjiecunqc($token=null,$page=1,$pagesize=1000000){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
        header('Access-Control-Allow-Credentials: true');
        $logic_qc = Loader::model('Qichu','logic');
        $info = checktoken($token);
        if(!$info){
                return retmsg(-2);
        }
        $ret = $logic_qc->getjiecunqc($page,$pagesize,$info["dept_id"]);
        return $ret;
    }
//  添加修改现金结存初期
    public function addjiecunqc($token=null){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Qichu','logic');
        if (Request::instance()->isPost()) {
            $postdata = json_decode(file_get_contents("php://input"), true);
//        $json_data = '{
//	"data": {
//		"qichu": "2826850"
//	}
//}';
//        $postdata = json_decode($json_data,true);
        $info = checktoken($token);
        if(!$info){
                return retmsg(-2);
        }
        $ret = $logic_qc->addjiecunqc($postdata,$info["dept_id"],$info["user_name"]);
        return $ret;
        }
    }
//    删除现金结存初期
    public function deljiecunqc($token=null,$id){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Qichu','logic');
        if (Request::instance()->isPost()){
        $info = checktoken($token);
        if(!$info){
                return retmsg(-2);
        }
        $ret = $logic_qc->deljiecunqc($id);
        return $ret;
        }
    }
//    应收账款期初
    public function getYSqc($token=null,$page=1,$pagesize=10000000,$qctype="暂存款期初"){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Qichu','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_qc->getYSqc($page,$pagesize,$qctype,$info["dept_id"]);
        return $ret;
    }
//  添加修改应收账款期初
    public function addYSqc($token=null,$qctype="应付账款期初"){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Qichu','logic');
        if (Request::instance()->isPost()) {
            $postdata = json_decode(file_get_contents("php://input"), true);
//        $json_data = '{
//	"data": [{
//	    "area":"直发部",
//	    "dept":"和乐川北存量",
//	    "cname":"代理金-钟时敏",
//	    "date":"2018/11/5",
//	    "yw_type":"1",
//		"qichu": "500001",
//		"handlers":"杜飞（土门）"
//	},{
//	    "area":"直发部",
//	    "dept":"和乐川北存量",
//	    "cname":"代理金-钟时敏",
//	    "date":"2018/11/5",
//	    "yw_type":"1",
//		"qichu": "500002",
//		"handlers":"杜飞（土门）"
//	}]
//}';
//        $postdata = json_decode($json_data,true);
        $info = checktoken($token);
        if (!$info) {
            return retmsg(-2);
        }
            $ret = $logic_qc->addYSqc($postdata,$qctype,$info["dept_id"],$info["user_name"]);
            return $ret;
        }
    }
//    删除应收账款期初
    public function delYSqc($token=null,$id){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Qichu','logic');
        if (Request::instance()->isPost()){
        $info = checktoken($token);
        if(!$info){
                return retmsg(-2);
        }
        $ret = $logic_qc->delYSqc($id,$info["dept_id"]);
        return $ret;
        }
    }
//    月初生成下月期初
    public function nextqc($token=null){
        if (date('Y-m-d',time())!==date('Y-m-01',time())){
            return array("resultcode"=>-1,"resultmsg"=>"今天不是月初，不能更新期初数据！","data"=>null);
        }
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Qichu','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_qc->nextqc();
        return $ret;
    }
}