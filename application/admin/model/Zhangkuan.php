<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 17:37
 */

namespace app\admin\model;


use think\Db;

class Zhangkuan
{
//    预收账款/预收账款明细
    public function getysmx($page,$pagesize,$stime,$etime,$type,$dept_id){
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
        $where = null;
        $header = null;
        if($type=="预收账款明细"){
            $where = array("12","17");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"收预收款","field"=>"income"),
                array("headerName"=>"支预收款","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }elseif ($type=="暂存款明细"){
            $where = array("13","18");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"收暂存","field"=>"income"),
                array("headerName"=>"支暂存","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="应收账款明细"){
            $where = array("71","63");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"新增欠款","field"=>"income"),
                array("headerName"=>"收回欠款","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="应付账款明细"){
            $where = array("69");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"新增应付","field"=>"income"),
                array("headerName"=>"支外购款","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="其他应收账款明细"){
            $where = array("70");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"借款支出","field"=>"spending"),
                array("headerName"=>"借款收回","field"=>"income"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="其他应付账款明细"){
            $where = array("72","73");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"款项收入","field"=>"income"),
                array("headerName"=>"款项支出","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="其他收入明细"){
            $where = array("10","8","62","54","77","78","79","80","81");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"其他收入金额","field"=>"income"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="其他支出明细"){
            $where = array("19","55","57","58","59","60","61","65");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"其他支出金额","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="资金调入明细"){
            $where = array("11");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"其他收入金额","field"=>"income"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        elseif ($type=="资金调拨明细"){
            $where = array("24","25");
            $header = array(
                array("headerName"=>"日期","field"=>"date"),
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"其他支出金额","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"摘要","field"=>"remark"),
            );
        }
        $starttime = date("Y-m-01", time());
        $endtime = date('Y-m-d', time());
        if(!empty($stime)&&!empty($etime)){
            $starttime = $stime;
            $endtime = $etime;
        }
        $ret = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
            ->where('a.yijimx','in',$where)
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->field('a.date,a.erjimx as name,b.yw_type_name,a.income,a.spending,a.handlers,a.remark')
            ->order('a.date desc')
            ->page($page,$pagesize)
            ->select();
        $heji = array();
        $heji["spending"]=0;
        $heji["income"]=0;
        $heji["date"]="合计";
        foreach ($ret as $k=>$v){
            $heji["spending"] += $v["spending"];
            $heji["income"] += $v["income"];
        }
        $heji["spending"] = sprintf("%.2f",$heji["spending"]);
        $heji["income"] = sprintf("%.2f",$heji["income"]);
        $heji1 = array();
        $heji1["date"]="合计";
        $heji1["income"] =  Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
            ->where('a.yijimx','in',$where)
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->sum('a.income');
        $heji1["spending"] =  Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
            ->where('a.yijimx','in',$where)
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->sum('a.spending');
        $heji1["spending"] = sprintf("%.2f",$heji1["spending"]);
        $heji1["income"] = sprintf("%.2f",$heji1["income"]);
        $cnt = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
            ->where('a.yijimx','in',$where)
            ->where('a.yw_type=b.id')
            ->where('a.dept_id','in',$de)
            ->where('a.date','>=',$starttime)
            ->where('a.date','<=',$endtime)
            ->count();
        return array("header"=>$header,"total"=>$cnt,"list"=>$ret,"heji"=>$heji1,"pageheji"=>$heji);
    }
//    预收账款/预收账款总账
    public function getyshz($page,$pagesize,$stime,$etime,$type,$dept_id){
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
        $where = null;
        $qc_type =null;
        $header = null;
        if($type=="预收账款总账"){
            $where = array("12","17");
            $qc_type = 3;
            $header = array(
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初余额","field"=>"qichu"),
                array("headerName"=>"收预收款","field"=>"income"),
                array("headerName"=>"支预收款","field"=>"spending"),
                array("headerName"=>"期末余额","field"=>"qimo"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="暂存款总账"){
            $where = array("13","18");
            $qc_type = 4;
            $header = array(
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初余额","field"=>"qichu"),
                array("headerName"=>"收暂存","field"=>"income"),
                array("headerName"=>"支暂存","field"=>"spending"),
                array("headerName"=>"期末余额","field"=>"qimo"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="应收账款总账"){
            $where = array("71","63");
            $qc_type = 2;
            $header = array(
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初余额","field"=>"qichu"),
                array("headerName"=>"新增欠款","field"=>"income"),
                array("headerName"=>"收回欠款","field"=>"spending"),
                array("headerName"=>"期末余额","field"=>"qimo"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"最远欠款日期","field"=>"qichu"),
                array("headerName"=>"账龄","field"=>"income"),
                array("headerName"=>"最近清账日","field"=>"spending"),
                array("headerName"=>"风险评估","field"=>"qimo"),
                array("headerName"=>"管理办法","field"=>"handlers"),
            );
        }
        elseif ($type=="应付账款总账"){
            $where = array("69");
            $qc_type = 5;
            $header = array(
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初余额","field"=>"qichu"),
                array("headerName"=>"新增应付","field"=>"income"),
                array("headerName"=>"支外购款","field"=>"spending"),
                array("headerName"=>"期末余额","field"=>"qimo"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="其他应收账款总账"){
            $where = array("70");
            $qc_type = 6;
            $header = array(
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初余额","field"=>"qichu"),
                array("headerName"=>"借款支出","field"=>"spending"),
                array("headerName"=>"借款收回","field"=>"income"),
                array("headerName"=>"期末余额","field"=>"qimo"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="其他应付账款总账"){
            $where = array("72","73");
            $qc_type = 7;
            $header = array(
                array("headerName"=>"客户姓名","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"期初余额","field"=>"qichu"),
                array("headerName"=>"款项收入","field"=>"income"),
                array("headerName"=>"款项支出","field"=>"spending"),
                array("headerName"=>"期末余额","field"=>"qimo"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="其他收入总账"){
            $where = array("8","10","62","54","77","78","79","80","81");
            $header = array(
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"资金调入金额","field"=>"income"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="其他支出总账"){
            $where = array("19","55","57","58","59","60","61","65");
            $header = array(
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"金额","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="资金调入总账"){
            $where = array("11");
            $header = array(
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"资金调入金额","field"=>"income"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        elseif ($type=="资金调拨总账"){
            $where = array("24","25");
            $header = array(
                array("headerName"=>"收入或往来对象","field"=>"name"),
                array("headerName"=>"业务类别","field"=>"yw_type_name"),
                array("headerName"=>"金额","field"=>"spending"),
                array("headerName"=>"经手人","field"=>"handlers"),
            );
        }
        $starttime = date("Y-m-01", time());
        $endtime = date('Y-m-d', time());
        if(!empty($stime)&&!empty($etime)){
            $starttime = $stime;
            $endtime = $etime;
        }
        if($type == "其他收入总账"||$type == "其他支出总账"||$type == "资金调入总账"||$type == "资金调拨总账"){
            $mxlist = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
                ->where('a.yijimx','in',$where)
                ->where('a.yw_type=b.id')
                ->where('a.dept_id','in',$de)
                ->where('a.date','>=',$starttime)
                ->where('a.date','<=',$endtime)
                ->field('a.erjimx as name,b.yw_type_name,a.spending,a.income,a.handlers')
                ->order('a.date desc')
                ->select();
            $heji = array();
            $heji["name"] = "合计";$heji["spending"]=0;
            $heji["income"]=0;
            foreach ($mxlist as $k=>$v){
                $heji["spending"] +=$v["spending"];
                $heji["income"] +=$v["income"];
            }
            $heji["spending"] = sprintf("%.2f",$heji["spending"]);
            $heji["income"] = sprintf("%.2f",$heji["income"]);
            $cnt = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
                ->where('a.yijimx','in',$where)
                ->where('a.yw_type=b.id')
                ->where('a.dept_id','in',$de)
                ->where('a.date','>=',$starttime)
                ->where('a.date','<=',$endtime)
                ->group('a.erjimx')
                ->count();
            return array("header"=>$header,"list"=>$mxlist,"total"=>$cnt,"remark"=>2,"heji"=>$heji);
        }
            $mxlist = Db::table('xjrjz_rijizhang a,xjrjz_yw_type b')
                ->where('a.yijimx','in',$where)
                ->where('a.yw_type=b.id')
                ->where('a.dept_id','in',$de)
                ->where('a.date','>=',$starttime)
                ->where('a.date','<=',$endtime)
                ->field('a.date,a.erjimx as name,b.yw_type_name,a.income,a.spending,a.handlers,a.remark')
                ->order('a.date desc')
                ->select();
        $de = implode(',',$de);
            $userlist = Db::table('xjrjz_rijizhang')
                ->alias('a')->join('xjrjz_qichu b',"a.erjimx=b.cname and b.dept_id in ($de) and b.qc_type_id=$qc_type",'LEFT')
                ->where('a.yijimx','in',$where)
                ->where('a.dept_id','in',$de)
                ->where('a.date','>=',$starttime)
                ->where('a.date','<=',$endtime)
                ->group('a.erjimx')
                ->page($page,$pagesize)
                ->field('a.erjimx as name,b.qichu')
                ->order('a.date desc')
                ->select();
            $cnt = Db::table('xjrjz_rijizhang')
                ->alias('a')->join('xjrjz_qichu b',"a.erjimx=b.cname and b.dept_id in ($de) and b.qc_type_id=$qc_type",'LEFT')
                ->where('a.yijimx','in',$where)
                ->where('a.dept_id','in',$de)
                ->where('a.date','>=',$starttime)
                ->where('a.date','<=',$endtime)
                ->group('a.erjimx')
                ->count();
            return array("header"=>$header,"mxlist"=>$mxlist,"userlist"=>$userlist,"total"=>$cnt,"remark"=>1);
    }
}