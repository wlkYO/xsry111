<?php
/**
 * Created by PhpStorm.
 * User: 000
 * Date: 2019/1/12
 * Time: 8:58
 */

namespace app\admin\logic;


use Think\Db;
use think\Loader;

class Subjectcode
{
    public function getSubjectList($keyword,$page,$pagesize){
        $modleSubject = Loader::model('Subjectcode');
        $result = $modleSubject->getSubjectList($keyword,$page,$pagesize);
        $header = array(
            array('headerName'=>'科目代码','field'=>'subject_code'),
            array('headerName'=>'科目名称','field'=>'subject_name'));
       if(!empty($result)){
            return array('head'=>$header,"total"=>$result[1],'list'=>$result[0]);
       }else{
           return '';
       }
    }

    //新增的操作
    public function addSubject($jsondata){
        $modelSuject = Loader::model('Subjectcode');
        $result = $modelSuject->addSubject($jsondata);
        if(is_numeric($result)){
            return retmsg(0);
        }else{
            return retmsg(-1,'',$result);
        }
    }

    public function deleteSubList($array){
        $modelSubject = Loader::model('Subjectcode');
        $res = $modelSubject->deleteSubList($array);
        if(!empty($res)){
            return retmsg(0);
        }else{
            return retmsg(-1,'','删除失败');
        }
    }

    public function updateSub($data){
        $modelSubject = Loader::model('Subjectcode');
        $res = $modelSubject->updateSub($data);
        if(!empty($res)){
            return retmsg(0);
        }else{
            return retmsg(-1,'','更新失败');
        }
    }

    public function importExcel($execl_file){
       $modelImportExcel = Loader::model('Subjectcode','model');
       $err_data = $modelImportExcel->importExcel($execl_file);
     //  $err_data = json_encode($err_data);
       // return array("resultcode"=>-1,"resultmsg"=>"导入失败","data"=>null,"error"=>$err_data);
       if(empty($err_data)){
           return retmsg(0,null,'导入成功');
       }else{
           return retmsg(-1,$err_data,"导入失败");
       }
    }

    public function exportExcel($keyword=''){
        $getData =  $this->getExportData($keyword);
        //定义一个数组来存查询到的值
        $all_data_list = $getData;

        //导入插件
        vendor('PHPExcel.Classes.PHPExcel');
        $objExecl = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExecl, 'Excel5');
        ob_end_clean();    //擦除缓冲区
        $file_name = '科目代码基础表'.'.xls';
        $indexKey = $this->indexKey();
        //构造表头字段,添加在list数组之前,组成完整数组
        $all_data_list = $this->addHeadToData($all_data_list);
        $heard_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        //写入数据到表格中
        $objActSheet = $objExecl->getActiveSheet();
        $this->addDataToExcel($all_data_list,$objActSheet,$indexKey,$heard_arr);
        // 下载这个表格，在浏览器输出
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename='.$file_name.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    /**
     * 得到需要导出的数据  返回所有数据
     * @param string $year
     * @param string $month
     * @param string $keyword
     * @param $type
     * @return mixed
     */
    public function getExportData($keyword=''){

       $resMsg = Db::table('certificate_subject');
       if(!empty($keyword)){
           $resMsg->where('subject_code','like','%'.$keyword.'%')
               ->whereor('subject_name','like','%'.$keyword.'%');
       }
       $res = $resMsg->select();
        return $res;
    }

//    /**
//     * 构建表中所有数据
//     * @param $data
//     */
//    public function createExcelData($data){
//        //导入插件
//        vendor('PHPExcel.Classes.PHPExcel');
//        $objExecl = new \PHPExcel();
//        $objWriter = \PHPExcel_IOFactory::createWriter($objExecl, 'Excel5');
//        ob_end_clean();    //擦除缓冲区
//
//        $heard =array('subject_code'=>'科目代码','subject_name'=>'科目名称');
//        $indexKey = array('subject_code','subject_name');    //$indexKey $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
//        array_unshift($data,$heard);
//        return $data;
//    }

    /**
     * 返回 对应表中 的字段名
     * @param $type
     * @return array
     */
    public function indexKey(){
        $indexKey = array('subject_code','subject_name');    //$indexKey $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
        return $indexKey;
    }

    /**\
     * 添加表头信息
     * @param $data
     * @param $type
     * @return int
     */
    public function addHeadToData($data){
        $heard =array('subject_code'=>'科目代码','subject_name'=>'科目名称');
        array_unshift($data,$heard);
        return $data;
    }

    public function addDataToExcel($data,$objActSheet,$indexKey,$heard_arr){
        $startRow = 1;
        $objActSheet->getStyle('A1:F1')->getFont()->setBold(true);
        foreach ($data as $key=>$value){
            foreach ($indexKey as $k=>$v){
                $objActSheet->setCellValue($heard_arr[$k].$startRow,$data[$key][$v]);
                $objActSheet->getStyle($heard_arr[$k].$startRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            $startRow++;
        }
        $objActSheet->getColumnDimension('A')->setWidth(10);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(30);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(30);
    }
}