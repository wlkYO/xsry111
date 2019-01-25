<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/6
 * Time: 15:17
 */

namespace app\admin\model;


use think\Db;

class Rijizhang
{
//    现金日记账
    public function getrijizhang($page,$pagesize,$stime,$etime,$deptarr,$dept_id){
        $de = array();
        if (!empty($dept_id)){
            if ($dept_id==1){
                $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
                foreach ($depts as $k=>$v){
                    foreach ($v as $k1=>$v1){
                        array_push($de,$v1);
                    }
                }
            }else{
                $depts = Db::query("select id from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
                foreach ($depts as $k=>$v){
                    foreach ($v as $k1=>$v1){
                        array_push($de,$v1);
                    }
                }
            }
        }
        $header = array(
            array("headerName"=>"日期","field"=>"date"),
            array("headerName"=>"项目类别","field"=>"xm_type"),
            array("headerName"=>"一级明细","field"=>"yijimx"),
            array("headerName"=>"二级明细","field"=>"erjimx"),
            array("headerName"=>"摘要","field"=>"remark"),
            array("headerName"=>"业务类别","field"=>"yw_type_name"),
            array("headerName"=>"收入","field"=>"income"),
            array("headerName"=>"支出","field"=>"spending"),
            array("headerName"=>"余额","field"=>"balance"),
            array("headerName"=>"经手人","field"=>"handlers"),
            array("headerName"=>"操作","field"=>"cz"),
        );
//        本期现金结存
        $xjjiecun = Db::table('xjrjz_qichu') ->where('qc_type_id',1)
                  ->where('month',date("Y-m",time()))
                  ->where('dept_id','in',$de)
                  ->sum('qichu');
        if(empty($stime)&&empty($etime)){
            $stime = date("Y-m-01", time());
            $etime = date('Y-m-d', time());
        }
//        日记账本期当前总收入、支出
        $sr = Db::table('xjrjz_rijizhang')
            ->where('date','>=',$stime)
            ->where('date','<=',$etime)
            ->where('dept_id','in',$de)
            ->sum('income');
        $zc = Db::table('xjrjz_rijizhang')
            ->where('date','>=',$stime)
            ->where('date','<=',$etime)
            ->where('dept_id','in',$de)
            ->sum('spending');
//        当前余额
        $yue = $xjjiecun+$sr-$zc;
        $where = null;
        if (!empty($deptarr)){
            $de = explode(',',$deptarr);
        }
        $list = Db::table('xjrjz_rijizhang a,xjrjz_xm_type b,xjrjz_yw_type c')
            ->where('a.xm_type=b.id')
            ->where('a.yw_type=c.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$stime)
            ->where('a.date','<=',$etime)
            ->field('a.id,a.date,b.name as xm_type,a.yijimx,a.erjimx,a.remark,c.yw_type_name,a.income,a.spending,a.balance,a.handlers')
            ->page($page,$pagesize)
            ->order('a.create_time asc')
            ->select();
        $cnt = Db::table('xjrjz_rijizhang a,xjrjz_xm_type b,xjrjz_yw_type c')
            ->where('a.xm_type=b.id')
            ->where('a.yw_type=c.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$stime)
            ->where('a.date','<=',$etime)
            ->count();
        $balanceheji = $sr-$zc+$xjjiecun;
        $incomeheji1=0;
        $spendheji1=0;
        foreach ($list as $k=>$v){
            $incomeheji1 +=$v["income"];
            $spendheji1 +=$v["spending"];
        }
        $incomeheji1 = sprintf("%.2f",$incomeheji1);
        $incomeheji = sprintf("%.2f",$sr);
        $spendheji = sprintf("%.2f",$zc);
        $spendheji1 = sprintf("%.2f",$spendheji1);
        $balanceheji = sprintf("%.2f",$balanceheji);
        $balanceheji1=$incomeheji1-$spendheji1;
        $balanceheji1 = sprintf("%.2f",$balanceheji1);
        $xmtype = Db::table('xjrjz_xm_type')->where('mxid',0)->field('id,name')->select();
        $yw_type = Db::table('xjrjz_yw_type')->field('id,yw_type_name')->select();
        return array("header"=>$header,"total"=>$cnt,"list"=>$list,
            "heji"=>array("date"=>"本月发生额合计","income"=>$incomeheji,"spending"=>$spendheji,"balance"=>$balanceheji),
            "pageheji"=>array("date"=>"单页发生额合计","income"=>$incomeheji1,"spending"=>$spendheji1,"balance"=>$balanceheji1),
            "xmtypelist"=>$xmtype,"yw_type"=>$yw_type,"yue"=>$yue);
    }
//    添加现金日记账
    public function addrijizhang($postdata,$dept_id){
        $de = array();
        if ($dept_id==1){
            $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }else{
            $depts = Db::query("select id from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
//        本期现金结存
        $xjjiecun = Db::table('xjrjz_qichu') ->where('qc_type_id',1)
            ->where('month',date("Y-m",time()))
            ->where('dept_id','in',$de)
            ->sum('qichu');
        $stime = date("Y-m-01", time());
        $etime = date('Y-m-d', time());
//        日记账本期当前总收入、支出
        $sr = Db::table('xjrjz_rijizhang')
            ->where('date','>=',$stime)
            ->where('date','<=',$etime)
            ->where('dept_id','in',$de)
            ->sum('income');
        $zc = Db::table('xjrjz_rijizhang')
            ->where('date','>=',$stime)
            ->where('date','<=',$etime)
            ->where('dept_id','in',$de)
            ->sum('spending');
//        当前余额
        $yue = $xjjiecun+$sr-$zc;
        $banlence = $yue+$postdata["income"]-$postdata["spending"];
        if ($banlence==$postdata["balance"]){
            $rijiz["balance"] = $postdata["balance"];
        }else{
            $rijiz["balance"] = $banlence;
        }
        $rijiz["date"] = $postdata["date"];
        $rijiz["dept_id"] = $dept_id;
        $rijiz["xm_type"] = $postdata["xm_type"];
        $rijiz["yijimx"] = $postdata["yijimx"];
        $rijiz["erjimx"] = $postdata["erjimx"];
        $rijiz["remark"] = $postdata["remark"];
        $rijiz["yw_type"] = $postdata["yw_type_name"];
        $rijiz["income"] = $postdata["income"];
        $rijiz["spending"] = $postdata["spending"];
        $rijiz["handlers"] = $postdata["handlers"];
        $rijiz["create_time"] = date("Y-m-d H:i:s",time());
        $ret = Db::table('xjrjz_rijizhang')->insert($rijiz);
        return $ret;
    }
//    修改现金日记账
    public function editrijizhang($postdata){
        $rijiz["date"] = $postdata["date"];
        $rijiz["xm_type"] = $postdata["xm_type"];
        $rijiz["yijimx"] = $postdata["yijimx"];
        $rijiz["erjimx"] = $postdata["erjimx"];
        $rijiz["remark"] = $postdata["remark"];
        $rijiz["yw_type"] = $postdata["yw_type_name"];
        $rijiz["income"] = $postdata["income"];
        $rijiz["spending"] = $postdata["spending"];
        $rijiz["balance"] = $postdata["balance"];
        $rijiz["handlers"] = $postdata["handlers"];
        $rijiz["update_time"] = date("Y-m-d H:i:s",time());
        $ret = Db::table('xjrjz_rijizhang')->where('id',$postdata["id"])->update($rijiz);
        return $ret;
    }
//    删除现金日记账
    public function delrijizhang($id){
        $ret = Db::table('xjrjz_rijizhang')->where('id',$id)->delete();
        return $ret;
    }
//    根据一级明细id获取一级明细名称
    public function getyijimx($yijimx){
        $ret = Db::table('xjrjz_xm_type')->where('id',$yijimx)->find();
        return $ret;
    }
    public function getxmtype(){
        $xmtype = Db::table('xjrjz_xm_type')->where('mxid',0)->field('id,name')->select();
        return $xmtype;
    }
//    根据项目类别id获取其一级明细
    public function getyijimxBytype($id){
        $ret = Db::table('xjrjz_xm_type')->where('mxid',$id)->field('id,name')->select();
        return $ret;
    }
//    部门列表
    public function getdept($dept=null){
        $where=null;
        if(!empty($dept)){
            $where["dname"] = array('like',"%{$dept}%");
        }
        $ret = Db::table('zjdb_hmgl')->field('id,dname as name')->where($where)->group('dname')->select();
        return $ret;
    }
//    检查期初表里有无该客户名称
    public function ishavecname($name){
        $ret = Db::table('xjrjz_qichu')->where('cname',$name)->find();
        if ($ret){
            return true;
        }else{
            return false;
        }
    }
//    摘要列表
    public function getremarkBydept($deptid,$remark=null){
        $deptname = Db::table('zjdb_hmgl')->field('dname')->where('id',$deptid)->find();
        $remark1=null;
        if(!empty($remark)){
            $remark1 = $remark;
        }
        $ret = Db::table('zjdb_hmgl')->field('skhm,rbmc')
            ->where('dname',$deptname["dname"])
            ->where('skhm|rbmc','like',"%{$remark1}%")
            ->select();
        return $ret;
    }
//    费用明细/费用明细
    public function getfymx($page,$pagesize,$stime,$etime,$dept_id){
        $de = array();
        if ($dept_id==1){
            $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }else{
            $depts = Db::query("select id from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
        $header = array(
            array("headerName"=>"日期","field"=>"date"),
            array("headerName"=>"费用项目","field"=>"name"),
            array("headerName"=>"业务类别","field"=>"yw_type_name"),
            array("headerName"=>"费用金额","field"=>"spending"),
            array("headerName"=>"经手人","field"=>"handlers"),
            array("headerName"=>"摘要","field"=>"remark"),
        );
        $starttime = date("Y-m-01", time());
        $endtime = date('Y-m-d', time());
        if(!empty($stime)&&!empty($etime)){
            $starttime = $stime;
            $endtime = $etime;
        }
        $ret = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b,xjrjz_xm_type c')
            ->where('a.yijimx=6 or a.yijimx=42')
            ->where('a.erjimx=c.name')
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->field('a.date,c.name,b.yw_type_name,a.spending,a.handlers,a.remark')
            ->order('a.date desc')
            ->page($page,$pagesize)
            ->select();
        $heji = array();$heji["date"] = "合计";
        $heji["spending"]=0;
        foreach ($ret as $k=>$v){
            $heji["spending"] += $v["spending"];
        }
        $heji1 = array();$heji1["date"] = "合计";
        $heji1["spending"] = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b,xjrjz_xm_type c')
            ->where('a.yijimx=6 or a.yijimx=42')
            ->where('a.erjimx=c.id')
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->sum('spending');
        $heji["spending"] = sprintf("%.2f",$heji["spending"]);
        $heji1["spending"] = sprintf("%.2f",$heji1["spending"]);
        $cnt = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b,xjrjz_xm_type c')
            ->where('a.yijimx=6 or a.yijimx=42')
            ->where('a.erjimx=c.id')
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->count();
        return array("header"=>$header,"total"=>$cnt,"list"=>$ret,"heji"=>$heji1,"pageheji"=>$heji);
    }
//    费用明细/费用汇总表
    public function getfyhz($stime,$etime,$dept_id){
        $de = array();
        if ($dept_id==1){
            $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }else{
            $depts = Db::query("select id from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
        $header = array(
            array("headerName"=>"费用项目","field"=>"name"),
            array("headerName"=>"业务类别","field"=>"yw_type_name"),
            array("headerName"=>"费用金额","field"=>"spending"),
            array("headerName"=>"经手人","field"=>"handlers"),
            array("headerName"=>"费用项目","field"=>"clname"),
            array("headerName"=>"业务类别","field"=>"clyw_type_name"),
            array("headerName"=>"费用金额","field"=>"clspending"),
            array("headerName"=>"经手人","field"=>"clhandlers"),
        );
        $starttime = date("Y-m-01", time());
        $endtime = date('Y-m-d', time());
        if(!empty($stime)&&!empty($etime)){
            $starttime = $stime;
            $endtime = $etime;
        }
        $jingyinglist = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b,xjrjz_xm_type c')
            ->where('a.yijimx',6)
            ->where('a.erjimx=c.name')
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->field('a.date,c.name,b.yw_type_name,a.spending,a.handlers,a.remark')
            ->order('a.date desc')
            ->select();
        $erjijylist = Db::table('xjrjz_xm_type')->where('mxid',6)->select();
        $chelianglist = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b,xjrjz_xm_type c')
            ->where('a.yijimx',42)
            ->where('a.erjimx=c.name')
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->field('a.date,c.name,b.yw_type_name,a.spending,a.handlers,a.remark')
            ->order('a.date desc')
            ->select();
        $erjicllist = Db::table('xjrjz_xm_type')->where('mxid',42)->select();
        return array("header"=>$header,"jylist"=>$jingyinglist,"erjijylist"=>$erjijylist,"cllist"=>$chelianglist,"erjicllist"=>$erjicllist);
    }

//    片区经营部列表
    public function getdeptlist(){
        $ret = Db::table('xsrb_department')->where('pid',1)->field('id,dname')->select();
        return $ret;
    }
//    获取经营部
    public function getseconddept($id){
        $ret = Db::table('xsrb_department')->where('pid',$id)->field('id,dname')->select();
        return $ret;
    }

//    现金对账单
    public function getDZD($stime,$etime,$dept_id){
        $de = array();
        if ($dept_id==1){
            $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }else{
            $depts = Db::query("select id from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
        $header = array(
            array("headerName"=>"收入项目","colspan"=>3,
            "children"=>array(
                array("headerName"=>"项目","field"=>"val1"),
                array("headerName"=>"业务类别","field"=>"val2"),
                array("headerName"=>"收入金额","field"=>"val3"),
            )),
            array("headerName"=>"支出项目","colspan"=>3,
                "children"=>array(
                    array("headerName"=>"项目","field"=>"val4"),
                    array("headerName"=>"业务类别","field"=>"val5"),
                    array("headerName"=>"支出金额","field"=>"val6"),
                ))
        );
        $starttime = date("Y-m-01", time());
        $endtime = date('Y-m-d', time());
        if(!empty($stime)&&!empty($etime)){
            $starttime = $stime;
            $endtime = $etime;
        }
        $list = Db::table('xjrjz_rijizhang a,xjrjz_xm_type b,xjrjz_yw_type c')
            ->where('a.xm_type=b.id')
            ->where('a.yw_type=c.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->field('a.id,a.date,a.yijimx,a.erjimx,a.remark,a.income,a.spending,a.balance,a.handlers,b.name as xm_type,c.yw_type_name')
            ->select();
        $lastjc = Db::table('xjrjz_qichu')
            ->where('month',date("Y-m",time()))
            ->where('qc_type_id',1)
            ->where('dept_id','in',$de)
            ->field('qichu')->find();
       return array("header"=>$header,"list"=>$list,"lastjc"=>$lastjc);
    }
//     获取客户列表
    public function getcustomer($id,$dept_id){
        $de = array();
        if ($dept_id==1){
            $depts = Db::query("select id from xsrb_department where pid !=0 and qt1 !=0");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }else{
            $depts = Db::query("select id from xsrb_department where (pid =$dept_id or id =$dept_id) and pid !=1");
            foreach ($depts as $k=>$v){
                foreach ($v as $k1=>$v1){
                    array_push($de,$v1);
                }
            }
        }
        $ret = Db::table('xjrjz_qichu')->where('qc_type_id',$id)->where('dept_id','in',$de)->where('month',date('Y-m',time()))->field('cname as name')->group('cname')->select();
        return $ret;
    }
}