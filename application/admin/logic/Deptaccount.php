<?php
/**
 * Created by PhpStorm.
 * User: 000
 * Date: 2019/1/17
 * Time: 8:55
 */

namespace app\admin\logic;


use Think\Db;
use think\Loader;

class Deptaccount
{
    public function getDeptList($year,$month,$keyword,$page,$pagesize){

        $modelDeptAccount = Loader::model('Deptaccount','model');
        $result = $modelDeptAccount->getDeptList($year,$month,$keyword,$page,$pagesize);
        if(!empty($result)){
            $header = [
                ['headerName'=>'年度','field'=>'year'],
                ['headerName'=>'月份','field'=>'month'],
                ['headerName'=>'二级部门','field'=>'dept_second'],
                ['headerName'=>'三级部门','field'=>'dept_third'],
            ];
            return array('head'=>$header,'total'=>$result[1],'list'=>$result[0]);
        }else{
            return '';
        }
    }

    public function addDeptAccount ($data){
        $modelDept = Loader::model('Deptaccount','model');
        $result = $modelDept->addDeptAccount($data);
        if(is_numeric($result)){
            return retmsg(0);
        }else{
            return retmsg(-1,'',$result);
        }
    }

    public function deleteDeptAccount($data){
        $modelDept = Loader::model('Deptaccount','model');
        $res = $modelDept->deleteDeptAccount($data);
        if(!empty($res)){
            return retmsg(0);
        }
        return retmsg(-1);
    }

    public function updateDeptAccount($data){
        $logicDept= Loader::model('Deptaccount','model');
        $res = $logicDept->updateDeptAccount($data);
        if(!empty($res)){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }

    //-------------------导入------------------------------
    public function importExcel($execl_file){
        $modelImportExcel = Loader::model('Deptaccount','model');
        $data = $modelImportExcel->importExcel($execl_file);

        if(empty($data)){
            return retmsg(0,'','导入成功');
        }else{
            return  retmsg(-1,$data,'导入失败');
        }
    }
    // ----------------------导入end-------------------------


    //----------------------------导出的函数   begin------------------------------
    public function exportExcel($year,$month,$keyword){
        $getData =  $this->getExportData($year,$month,$keyword);
//        dump($getData); die();
        //定义一个数组来存查询到的值
        $all_data_list = $getData;
        //导入插件
        vendor('PHPExcel.Classes.PHPExcel');
        $objExecl = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExecl, 'Excel5');
        ob_end_clean();    //擦除缓冲区
        $file_name = '二三级对应关系部门表'.'.xls';
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
    public function getExportData($year,$month,$keyword){

        $deptList = Db::table('certificate_dept')
            ->field('year,month,dept_second,dept_third');
        if(!empty($year)){
            $deptList->where('year',$year);
        }
        if(!empty($month)){
            $deptList->where('month',$month);
        }
        if(!empty($keyword)){
            $deptList->where('dept_second|dept_third','like',"%$keyword%");
        }
        $res = $deptList
            ->select();
        return $res;
    }

    /**
     * 构建表中所有数据
     * @param $data
     */
    public function createExcelData($data){
        //导入插件
        vendor('PHPExcel.Classes.PHPExcel');
        $objExecl = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExecl, 'Excel5');
        ob_end_clean();    //擦除缓冲区
        $file_name = time().'.xls';

        $heard =array('subject_code'=>'科目代码','subject_name'=>'科目名称');
        $indexKey = array('subject_code','subject_name');    //$indexKey $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
        array_unshift($data,$heard);
        return $data;
    }

    /**
     * 返回 对应表中 的字段名
     * @param $type
     * @return array
     */
    public function indexKey(){
        $indexKey = array('year','month','dept_second','dept_third');    //$indexKey $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
        return $indexKey;
    }

    /**\
     * 添加表头信息
     * @param $data
     * @param $type
     * @return int
     */
    public function addHeadToData($data){
        $heard =array('year'=>'年度','month'=>'月份','dept_second'=>'二级部门','dept_third'=>"三级部门");
        array_unshift($data,$heard);
        return $data;
    }

    /**
     * 将组合的数据 添加到表格中
     * @param $data   组合数据
     * @param $objActSheet 对象
     * @param $indexKey   下标
     * @param $heard_arr  头
     */
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
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(30);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(20);
    }

    //----------------------------导出的函数   ending------------------------------

}