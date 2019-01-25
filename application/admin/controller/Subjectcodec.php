<?php
/**
 * Created by PhpStorm.
 * User: 000
 * Date: 2019/1/12
 * Time: 8:53
 */

namespace app\admin\controller;


use app\admin\service\DownloadService;
use Think\Db;
use think\Loader;

class Subjectcodec
{
    /**
     * 根据关键字查询
     * @param $code
     * @return mixed
     */
    public function getSubjectList($token='',$keyword='',$page=1,$pagesize=1000000){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
        header('Access-Control-Allow-Credentials: true');
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}

        $logicSubjectcode = Loader::model('Subjectcode','logic');
        $result = $logicSubjectcode->getSubjectList($keyword,$page,$pagesize);
        if(!empty($result)){
            return retmsg(0,$result);
        }
        return retmsg(-1);
    }

    /**
     * 添加单个科目代码
     * @return mixed
     */
    public function addSubject ($token=''){
        header("Access-Control-Allow-Origin: *");
        $jsondata = json_decode(file_get_contents("php://input"),true);

        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}
        $logicSubjectcode = Loader::model('Subjectcode','logic');
        $result = $logicSubjectcode->addSubject($jsondata['data']);
        return $result;
    }

    /**
     * 批量删除操作或者单个删除
     * @param $suc_code
     * @return mixed
     */
    public function deleteSubList($token=''){
        header("Access-Control-Allow-Origin: *");
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}
        $jsondata = json_decode(file_get_contents("php://input"), true);
        $logicSubject = Loader::model('Subjectcode','logic');
        $res = $logicSubject->deleteSubList($jsondata['data']['id']);
        return $res;
    }

    /**
     * 修改科目代码
     * @param $sub_code
     * @param $sub_name
     * @return mixed
     */
    public function updateSub($token=''){
        header("Access-Control-Allow-Origin: *");
        $jsondata = json_decode(file_get_contents("php://input"),true);
        $info = checktoken($token);

        if(!$info){ return retmsg(-2);}
        $logicSubject = Loader::model('Subjectcode','logic');
        $res = $logicSubject->updateSub($jsondata['data']);
        return $res;
    }

    /**
     * 处理Execl表格导入
     * @param $token
     * @return array
     */
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
        $logicSubject = Loader::model('Subjectcode','logic');
        $data = $logicSubject->importExcel($execl_file);
        return $data;
    }

    /**
     * 处理导出Execel
     * @param string $token
     * @param string $keyword
     * @param bool $excel2007
     * @return array
     */
    public function exportExcel($token='',$keyword=''){
        header("Access-Control-Allow-Origin: *");
        $info = checktoken($token);
        if(!$info){ return retmsg(-2);}
        $logicExportExcel = Loader::model('Subjectcode','logic');
        $logicExportExcel->exportExcel($keyword);
    }

}