<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5
 * Time: 15:43
 */

namespace app\admin\model;


use think\Db;

class Qichu
{
//    现金结存初期
    public function getjiecunqc($page,$pagesize,$dept_id){
        $header = array(
            array("headerName"=>"现金结存期初","field"=>"qichu"),
            array("headerName"=>"操作","field"=>"cz"),
        );
        $de = array();
        if ($dept_id==1){
            $dept = Db::query('select id from xsrb_department where pid !=0 and qt1 !=0');
           foreach ($dept as $k=>$v){
               foreach ($v as $k1=>$v1){
                   array_push($de,$v1);
               }
           }
        }else{
            $dept = Db::query("select * from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($dept as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
        $list = Db::table('xjrjz_qichu')
            ->where('qc_type_id',1)
            ->where('dept_id','in',$de)
            ->where('month',date("Y-m",time()))
            ->field('id,qichu')
            ->page($page,$pagesize)
            ->select();
        $cnt = Db::table('xjrjz_qichu')
            ->where('month',date("Y-m",time()))
            ->where('dept_id','in',$de)
            ->where('qc_type_id',1)
            ->count();
        return array("header"=>$header,"total"=>$cnt,"list"=>$list);
    }
//    添加现金结存初期
    public function addjiecunqc($postdata,$dept_id,$username){
        $jiecun["qichu"] = $postdata["qichu"];
        $jiecun["dept_id"] = $dept_id;
        $jiecun["create_name"] = $username;
        $jiecun["qc_type_id"] = 1;
        $jiecun["month"] = date("Y-m",time());
        $jiecun["create_time"] = date("Y-m-d H:i:s",time());
        $ret = Db::table('xjrjz_qichu')->insert($jiecun);
        return $ret;
    }
//    修改现金结存期初
    public function editjiecunqc($postdata,$username){
        $jiecun["qichu"] = $postdata["qichu"];
        $jiecun["update_name"] = $username;
        $jiecun["update_time"] = date("Y-m-d H:i:s",time());
        $ret = Db::table('xjrjz_qichu')->where('id',$postdata["id"])->update($jiecun);
        return $ret;
    }
//    删除现金结存期初
    public function deljiecunqc($id){
        $ret = Db::table('xjrjz_qichu')->where('id',$id)->delete();
        return $ret;
    }
//    应收账款期初
    public function getYSqc($page,$pagesize,$qctype,$dept_id){
        $de = array();
        if ($dept_id==1){
            $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }else{
            $depts = Db::query("select * from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
        $qc_type=null;
        if($qctype == "应收账款期初"){
            $qc_type = 2;
            $header = array(
                array("headerName"=>"片区","field"=>"area"),
                array("headerName"=>"经营部","field"=>"dept"),
                array("headerName"=>"客户姓名","field"=>"cname"),
                array("headerName"=>"期初欠款日期","field"=>"date"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初","field"=>"qichu"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"操作","field"=>"cz"),
            );
        }
        elseif ($qctype == "预收账款期初"||$qctype == "暂存款期初"||$qctype == "应付账款期初"){
            $header = array(
                array("headerName"=>"片区","field"=>"area"),
                array("headerName"=>"经营部","field"=>"dept"),
                array("headerName"=>"客户姓名","field"=>"cname"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"初期","field"=>"qichu"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"操作","field"=>"cz"),
            );
            if($qctype == "预收账款期初"){
                $qc_type = 3;
            }elseif ($qctype == "暂存款期初"){
                $qc_type = 4;
            }elseif ($qctype == "应付账款期初"){
                $qc_type = 5;
            }
        }else{
            $header = array(
                array("headerName"=>"片区","field"=>"area"),
                array("headerName"=>"经营部","field"=>"dept"),
                array("headerName"=>"明细科目","field"=>"cname"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"初期","field"=>"qichu"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"操作","field"=>"cz"),
            );
            if ($qctype == "其他应收账款期初"){
                $qc_type = 6;
            }elseif ($qctype="其他应付账款期初"){
                $qc_type = 7;
            }
        }
        $list = Db::table('xjrjz_qichu a,xjrjz_yw_type b')
            ->where('a.yw_type=b.id')
            ->where('a.qc_type_id',$qc_type)
            ->where('dept_id','in',$de)
            ->where('a.month',date("Y-m",time()))
            ->field('a.id,a.area,a.dept,a.cname,a.date,b.yw_type_name,a.qichu,a.handlers')
            ->page($page,$pagesize)
            ->select();
        $heji = Db::table('xjrjz_qichu a,xjrjz_yw_type b')
            ->where('a.yw_type=b.id')
            ->where('a.qc_type_id',$qc_type)
            ->where('dept_id','in',$de)
            ->where('a.month',date("Y-m",time()))
            ->sum('a.qichu');
        $heji2=array();
        $heji3=array();
        $heji2["qichu"] = 0;
        $heji3["qichu"] = $heji;
        $heji2["area"] = "合计";
        $heji3["area"] = "合计";
        foreach ($list as $k=>$v){
            $heji2["qichu"] +=$v["qichu"];
        }
        $cnt = Db::table('xjrjz_qichu a,xjrjz_yw_type b')
            ->where('a.yw_type=b.id')
            ->where('dept_id','in',$de)
            ->where('a.qc_type_id',$qc_type)
            ->where('a.month',date("Y-m",time()))
            ->count();
        $yw_type = Db::table('xjrjz_yw_type')->field('id,yw_type_name')->select();
        $customer = Db::table('xjrjz_qichu')->field('cname')->group('cname')->select();
        $dept= Db::table('xsrb_department')->field('pid,dname')->where('id',$dept_id)->find();
        $pianqu = Db::table('xsrb_department')->field('dname')->where('id',$dept["pid"])->find();
        return array("header"=>$header,"total"=>$cnt,"list"=>$list,"heji"=>$heji3,"pageheji"=>$heji2,"yw_type"=>$yw_type,"erjimx"=>$customer,"dept"=>array("area"=>$pianqu["dname"],"dept"=>$dept["dname"]));
    }
//    添加应收账款期初
    public function addYSqc($postdata,$qctype,$dept_id,$username){
        $jiecun["dept_id"] = $dept_id;
        $jiecun["create_name"] = $username;
        $jiecun["area"] = $postdata["area"];
        $jiecun["dept"] = $postdata["dept"];
        $jiecun["cname"] = $postdata["cname"];
        $qc_type=null;
        if($qctype=="应收账款期初"){
            $qc_type = 2;
            $jiecun["date"] = $postdata["date"];
        }elseif ($qctype == "预收账款期初"){
            $qc_type = 3;
        }elseif ($qctype == "暂存款期初"){
            $qc_type = 4;
        }elseif ($qctype == "应付账款期初"){
            $qc_type = 5;
        }elseif ($qctype == "其他应收账款期初"){
            $qc_type = 6;
        }elseif ($qctype == "其他应付账款期初"){
            $qc_type = 7;
        }
        $jiecun["yw_type"] = $postdata["yw_type_name"];
        $jiecun["qichu"] = $postdata["qichu"];
        $jiecun["handlers"] = $postdata["handlers"];
        $jiecun["qc_type_id"] = $qc_type;
        $jiecun["month"] = date("Y-m",time());
        $jiecun["create_time"] = date("Y-m-d H:i:s",time());
        $ret = Db::table('xjrjz_qichu')->insert($jiecun);
        return $ret;
    }
//    修改应收账款期初
    public function editYSqc($postdata,$qctype,$username){
        $jiecun["update_name"] = $username;
        $jiecun["area"] = $postdata["area"];
        $jiecun["dept"] = $postdata["dept"];
        $jiecun["cname"] = $postdata["cname"];
        if($qctype=="应收账款期初"){
            $jiecun["date"] = $postdata["date"];
        }
        $jiecun["yw_type"] = $postdata["yw_type_name"];
        $jiecun["qichu"] = $postdata["qichu"];
        $jiecun["handlers"] = $postdata["handlers"];
        $jiecun["update_time"] = date("Y-m-d H:i:s",time());
        $ret = Db::table('xjrjz_qichu')->where('id',$postdata["id"])->update($jiecun);
        return $ret;
    }
//    删除应收账款期初
    public function delYSqc($id){
        $ret = Db::table('xjrjz_qichu')->where('id',$id)->delete();
        return $ret;
    }
//    月初生成下月期初
    public function nextqc(){
        $nowyaer = date("Y-",time());
        $nowmonth = date("m",time())-1;
//        将字符串补全到两位
        $nowmonth = str_pad($nowmonth,2,"0",STR_PAD_LEFT);
        $month =$nowyaer.$nowmonth;
        $ret = Db::table('xjrjz_qichu')
            ->where('month',$month)
            ->group('cname')
            ->select();
        return $ret;
    }
//    根据客户名称获取其日记账收支明细
    public function getrjz($erjimx){
        $income = Db::table('xjrjz_rijizhang')->where('erjimx',$erjimx)->where("PERIOD_DIFF(date_format(now(),'%Y%m'),date_format(date,'%Y%m'))=1")->sum('income');
        $spending = Db::table('xjrjz_rijizhang')->where('erjimx',$erjimx)->where("PERIOD_DIFF(date_format(now(),'%Y%m'),date_format(date,'%Y%m'))=1")->sum('spending');
        return array("income"=>$income,"spending"=>$spending);
    }
//    添加下月期初
    public function addnextqc($data){
        $ret = Db::table('xjrjz_qichu')->insertAll($data);
        return $ret;
    }
//    删除和修改期初时判断该客户是否有日记账
    public function ishaverjz($id,$dept){
        $ret = Db::table('xjrjz_qichu')->field('cname')->where('id',$id)->find();
        $ret1 = Db::table('xjrjz_rijizhang')->where('erjimx',$ret["cname"])->where('dept_id',$dept)->find();
        return array("cname"=>$ret["cname"],"rjz"=>$ret1);
    }
}