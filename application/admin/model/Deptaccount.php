<?php
/**
 * Created by PhpStorm.
 * User: 000
 * Date: 2019/1/17
 * Time: 8:55
 */

namespace app\admin\model;


use Think\Db;
use think\Loader;

class Deptaccount
{
    public function getDeptList($year,$month,$keyword,$page,$pagesize){
        $deptList = Db::table('certificate_dept')
            ->field('id,year,month,dept_second,dept_third');
        if(!empty($year)){
            $deptList->where('year',$year);
        }
        if(!empty($month)){
            $deptList->where('month',$month);
        }
        if(!empty($keyword)){
            $deptList->where('dept_second','like','%'.$keyword.'%')
            ->whereOr('dept_third','like','%'.$keyword.'%');
        }

        $res = $deptList
            ->page($page,$pagesize)
            ->order('create_time desc,update_time desc')
            ->select();

        $count = Db::table('certificate_dept')
            ->field('id,year,month,dept_second,dept_third');

        if(!empty($year)){
            $count->where('year',$year);
        }
        if(!empty($month)){
            $count->where('month',$month);
        }
        if(!empty($keyword)){
            $count->where('dept_second','like','%'.$keyword.'%')
                ->whereOr('dept_third','like','%'.$keyword.'%');
        }
        $num = $count->count();
        return [$res,$num];
    }

    public function addDeptAccount ($data){

        if(!empty($data)){
            $has = Db::table('certificate_dept')
                ->where('dept_second',$data['dept_second'])
                ->where('dept_third',$data['dept_third'])
                ->where('year',$data['year'])
                ->where('month',$data['month'])
                ->find();
            if($has){
                return '添加的部门信息已经存在';
            }
            if( empty($data['dept_second']) || empty($data['dept_third']) ||empty($data['year'])
            || empty($data['month'])){
                return '输入信息不能为空';
            }else{
                $addData  = [
                    'year'  =>  $data['year'],
                    'month' =>  $data['month'],
                    'dept_second'   =>  $data['dept_second'],
                    'dept_third' => $data['dept_third'],
                    'create_time' => date('Y-m-d H:i:s',time()),
                    'create_by' =>  $data['create_by']
                ];
                $res = Db::table('certificate_dept')
                    ->insert($addData);
                return $res;
            }
        }
        return '没有获取到新增信息';
    }

    public function deleteDeptAccount($data){
        if(!empty($data)){
            $res = Db::table('certificate_dept')
                ->delete($data);
            return $res;
        }else{
            return '';
        }
    }
    public function updateDeptAccount($data){
        if(!empty($data)){
            $updateDate = [
                'id'=>$data['id'],
                'year'  =>  $data['year'],
                'month' =>  $data['month'],
                'dept_second'   =>  $data['dept_second'],
                'dept_third' => $data['dept_third'],
                'update_time' => date('Y-m-d H:i:s',time()),
                'update_by' =>  $data['update_by']
            ];
            $res = Db::table('certificate_dept')
                ->update($updateDate);
            return $res;
        }else{
            return '';
        }
    }

    public function importExcel($execl_file){
        if(empty($execl_file)){
            return '';
        }
        $importService = Loader::model('ImportExcelService','service');
        $data = $importService->importExcel($execl_file);

        $err_data =[];
        for ($j=0; $j<count($data,0); $j++){
            foreach ($data[$j] as $key=>$value){
                foreach ($data[$j][$key] as $k=>$v){
                    $save_data = [
                        'year'  =>  $data[$j][$key]['A'],
                        'month' =>  $data[$j][$key]['B'],
                        'dept_second'   => $data[$j][$key]['C'],
                        'dept_third' => $data[$j][$key]['D'],
                    ];
                }

                $has =Db::table('certificate_dept')
                        ->where($save_data)
                        ->find();
                if(empty($has)){
                    $logicDept =Loader::model('Deptaccount','logic');
                   $msg = $logicDept->addDeptAccount($save_data);

                }else{
                   $err_data[] = ['msg'=>$save_data['year'].','.$save_data['month'].','.$save_data['dept_second'].','.$save_data['dept_third'].'已经存在'];
                }
            }
        }
        return $err_data;
    }
}