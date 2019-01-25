<?php
/**
 * Created by PhpStorm.
 * User: 000
 * Date: 2019/1/17
 * Time: 8:55
 */

namespace app\admin\controller;


use Think\Db;
use think\Loader;

class Deptaccountc
{
    /**
     * 根据关键字查询相关部门
     * @param $code
     * @return mixed
     */
    public function getDeptList($token='',$year='',$month='',$keyword='',$page=1,$pagesize=1000000){
        header("Access-Control-Allow-Origin: *");
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}

        $logicDeptAccount = Loader::model('Deptaccount','logic');
        $result = $logicDeptAccount->getDeptList($year,$month,$keyword,$page,$pagesize);
        if(!empty($result)){
            return retmsg(0,$result);
        }else{
            return retmsg(-1);
        }
    }

    /**
     * 添加单个核算部门
     * @return mixed
     */
    public function addDeptAccount ($token=''){
        header("Access-Control-Allow-Origin: *");
        $info = checktoken($token);
        $jsondata = json_decode(file_get_contents("php://input"),true);
        if(!$info){ return retmsg(-2);}
        $logicDept = Loader::model('Deptaccount','logic');
        $result = $logicDept->addDeptAccount($jsondata['data']);
        return $result;
    }

    /**
     * 批量删除操作或者单个删除
     * @param $suc_code
     * @return mixed
     */
    public function deleteDeptAccount($token=''){
        header("Access-Control-Allow-Origin: *");
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}

        $jsondata = json_decode(file_get_contents("php://input"), true);
        $logicDept = Loader::model('Deptaccount','logic');
        $res = $logicDept->deleteDeptAccount($jsondata['data']['id']);
        return $res;
    }

    /**
     * 修改部门信息
     * @param $sub_code
     * @param $sub_name
     * @return mixed
     */
    public function updateDeptAccount($token=''){
        header("Access-Control-Allow-Origin: *");
        $jsondata = json_decode(file_get_contents("php://input"),true);
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}

        $logicDept= Loader::model('Deptaccount','logic');
        $res = $logicDept->updateDeptAccount($jsondata['data']);
        return $res;
    }

    public function importExcel($token=''){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
        header('Access-Control-Allow-Credentials: true');
        header("content-type:text/html; charset=utf-8");
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}

        //获取文件上传信息
        $execl_file = request()->file('file');
        $logicDeptaccount = Loader::model('Deptaccount','logic');
        $data = $logicDeptaccount->importExcel($execl_file);
        return $data;
    }


    /**
     * 处理导出Execel
     * @param string $token
     * @param string $keyword
     * @param bool $excel2007
     * @return array
     */
    public function exportExcel($token='',$year='',$month='',$keyword=''){
        header("Access-Control-Allow-Origin: *");
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}

        $logicDeptaccount = Loader::model('Deptaccount','logic');
        $logicDeptaccount->exportExcel($year,$month,$keyword);
    }

}