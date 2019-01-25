<?php
/**
 * Created by PhpStorm.
 * User: 000
 * Date: 2019/1/12
 * Time: 8:58
 */

namespace app\admin\model;



use think\Db;
use think\Exception;
use think\Loader;

class Subjectcode
{
    public function getSubjectList($Keyword,$page,$pagesize){
        try{
            $subject =  Db::table('certificate_subject')
                ->field('id,subject_code,subject_name')
                ->where('subject_code','like','%'.$Keyword.'%')
                ->whereOr('subject_name','like','%'.$Keyword.'%')
                ->page($page,$pagesize)
                ->order('create_time desc,update_time desc')
                ->select();
            $count =  Db::table('certificate_subject')
            ->field('id,subject_code,subject_name')
                ->where('subject_code','like','%'.$Keyword.'%')
                ->whereOr('subject_name','like','%'.$Keyword.'%')
                ->count();
            $subject=[$subject,$count];

        }catch (\Exception $e){
            $e->getMessage('查询异常');
        }
        return $subject;
    }

    public function addSubject($jsondata){
        if(!is_numeric($jsondata['subject_code']) || empty($jsondata['subject_code'])){
            return '科目代码只能为数字且不为空';
        }
        $has_sub_code = Db::table('certificate_subject')
            ->where('subject_code',$jsondata['subject_code'])
            ->find();
        if(empty($has_sub_code)){
            $data = [
                'subject_code'  =>  $jsondata['subject_code'],
                'subject_name'  =>  $jsondata['subject_name'],
                'create_user'   =>  $jsondata['create_user'],
                'create_time'   => date('Y-m-d H:i:s',time())
            ];
            $add_res = Db::table('certificate_subject')->insert($data);
        }else{
            return '该科目代码已经存在';
        }
        return $add_res;
    }

    public function deleteSubList($array)
    {
        if(!empty($array)){
                $res = Db::table('certificate_subject')
                    ->where('id','in',$array)
                    ->delete();
        }else{
            return '';
        }
        return $res;
    }

    public  function updateSub($data){
        $res = Db::table('certificate_subject')
            ->where('id',$data['id'])
            ->update([
                    'subject_code'=>$data['subject_code'],
                    'subject_name'=>$data['subject_name'],
                    'update_user'=>$data['update_user'],
                    'update_time'=>date('Y-m-d H:i:s',time()),
                ]);
        return $res;
    }

    public function importExcel($execl_file){
        if(empty($execl_file)){
            return '';
        }
        $importService = Loader::model('ImportExcelService','service');
        $data = $importService->importExcel($execl_file);
        $err_data =[];  //存放重复信息
        for ($j=0; $j<count($data,0); $j++){
            foreach ($data[$j] as $key=>$value){
                foreach ($data[$j][$key] as $k=>$v){
                    if(!is_numeric($data[$j][$key]['A']) && !empty($data[$j][$key]['A'])){
                        $err_data[]=['msg'=>'科目代码:'.$data[$j][$key]['A'].'数据格式不对'];
                        break;
                    }
                    $save_data = [
                        'subject_code' =>$data[$j][$key]['A'],
                        'subject_name'  =>$data[$j][$key]['B'],
                    ];
                }
                $res = Db::table('certificate_subject')
                    ->where('subject_code',$save_data['subject_code'])
                    ->find();
               if(empty($res)){
                    $logic = Loader::model('Subjectcode','logic');
                    $logic->addSubject($save_data);
               }else{
                   $err_data[] = ['msg'=>'科目代码:'.$save_data['subject_code'].'已经存在'];
               }
            }
        }
        return $err_data;
    }
}