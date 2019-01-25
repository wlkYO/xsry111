<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/6
 * Time: 15:17
 */

namespace app\admin\controller;


use think\Loader;
use think\Request;

class Rijizhangc
{
//    现金日记账
    public function getrijizhang($token=null,$page=1,$pagesize=10000000,$stime=null,$etime=null,$deptarr=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
//        $info = checktoken($token);
//        if(!$info){
////            return retmsg(-2);
//        }
        $ret = $logic_rjz->getrijizhang($page,$pagesize,$stime,$etime,$deptarr,$info["dept_id"]);
        return $ret;
    }
//  添加修改现金日记账
    public function addrijizhang($token=null,$id=null){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Rijizhang','logic');
        if (Request::instance()->isPost()) {
            $postdata = json_decode(file_get_contents("php://input"), true);
//        $json_data = '{
//	"data": [{
//		"date": "2018-11-10",
//		"xm_type": "4",
//		"yijimx": "12",
//		"erjimx": "二级明细",
//		"remark": "摘要测试修改",
//		"yw_type": "1",
//		"income": "5200",
//		"spending": "5200",
//		"balance":"439690.00",
//		"handlers":"ywr"
//	}]
//}';
//        $postdata = json_decode($json_data,true);
        $info = checktoken($token);
        if (!$info) {
            return retmsg(-2);
        }
            $ret = $logic_qc->addrijizhang($postdata,$info["dept_id"]);
            return $ret;
        }
    }
//    删除现金日记账
    public function delrijizhang($token=null,$id){
        header("Access-Control-Allow-Origin: *");
        $logic_qc = Loader::model('Rijizhang','logic');
        if (Request::instance()->isPost()){
        $info = checktoken($token);
        if(!$info){
                return retmsg(-2);
        }
        $ret = $logic_qc->delrijizhang($id);
        return $ret;
        }
    }
//   获取项目类别，一级明细，二级明细和摘要
    public function getyijimxBytype($token=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
//        $info = checktoken($token);
//        if(!$info){
//            return retmsg(-2);
//        }
//        $ret = $logic_rjz->getyijimxBytype($info["dept_id"]);
        $ret = $logic_rjz->getyijimxBytype(10009);
        return $ret;
    }
//    根据一级明细id获取其二级明细
    public function geterjimxByyiji($token=null,$id,$dept=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->geterjimxByyiji($id,$dept);
        return $ret;
    }
//    根据部门名称获取其摘要
    public function getremarkBydept($token=null,$deptid=null,$remark=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->getremarkBydept($deptid,$remark);
        return $ret;
    }

//    费用明细/费用明细
    public function getfymx($token=null,$page=1,$pagesize=1000000,$stime=null,$etime=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->getfymx($page,$pagesize,$stime,$etime,$info["dept_id"]);
        return $ret;
    }
//    费用明细/费用汇总表
    public function getfyhz($token=null,$stime=null,$etime=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->getfyhz($stime,$etime,$info["dept_id"]);
        return $ret;
    }

//    片区和经营部列表
    public function getdeptlist($token=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->getdeptlist();
        return $ret;
    }
//    现金对账单
    public function getDZD($token=null,$stime=null,$etime=null){
        header("Access-Control-Allow-Origin: *");
        $logic_rjz = Loader::model('Rijizhang','logic');
        $info = checktoken($token);
        if(!$info){
            return retmsg(-2);
        }
        $ret = $logic_rjz->getDZD($stime,$etime,$info["dept_id"]);
        return $ret;
    }
}