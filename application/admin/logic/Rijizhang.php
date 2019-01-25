<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/6
 * Time: 15:17
 */

namespace app\admin\logic;


use think\Loader;

class Rijizhang
{
//  现金日记账
    public function getrijizhang($page,$pagesize,$starttime,$endtime,$deptarr,$dept_id){
        $model_rjz = Loader::model('Rijizhang');
        $ret = $model_rjz->getrijizhang($page,$pagesize,$starttime,$endtime,$deptarr,$dept_id);
//        return $ret;
        $res = array();
        foreach ($ret["list"] as $k=>$v){
            $yijimx = $model_rjz->getyijimx($v["yijimx"]);
            $erjimx = $model_rjz->getyijimx($v["erjimx"]);
            if(!empty($erjimx)){
                $v["erjimx"] = $erjimx["name"];
            }
            $v["yijimx"] = $yijimx["name"];
            $v["yId"] = $yijimx["id"];
            array_push($res,$v);
        }
        $ret["list"]=$res;
        if($ret){
            return retmsg(0,$ret);
        }
        else{
            return retmsg(0);
        }
    }
//    添加现金日记账
    public function addrijizhang($postdata,$dept_id)
    {
        $model_qc = Loader::model('Rijizhang');
        $endres = true;
//        $arr = array("6","42","11","24","25","7","62","54","17","55","56","57","58","59","60","61","8");
        foreach ($postdata["data"] as $k => $v) {
            if ($v["date"]==date('Y-m-d',time())){
//                if (in_array($v["yijimx"],$arr)){
                    if (array_key_exists("id",$v)){
                        $ret = $model_qc->editrijizhang($v);
                        $endres = $endres&$ret;
                    }else{
                        $ret = $model_qc->addrijizhang($v,$dept_id);
                        $endres = $endres&$ret;
                    }
//                }else{
//                    $ishavecname = $model_qc->ishavecname($v["erjimx"]);
//                    if($ishavecname){
//                        if (array_key_exists("id",$v)){
//                            $ret = $model_qc->editrijizhang($v);
//                            $endres = $endres&$ret;
//                        }else{
//                            $ret = $model_qc->addrijizhang($v,$dept_id);
//                            $endres = $endres&$ret;
//                        }
//                    }else{
//                        $k1=$k+1;
//                        return array("resultcode" => -1, "resultmsg" => "第$k1"."条数据的客户还没有期初值", "data" => null);
//                    }
//                }
            }
        }
        if ($endres) {
             return retmsg(0);
        } else {
             return retmsg(-1);
       }
    }
//    修改现金日记账
    public function editrijizhang($postdata){
        $model_qc = Loader::model('Rijizhang');
        $ret = $model_qc->editrijizhang($postdata);
        if($ret){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }
//    删除现金日记账
    public function delrijizhang($id){
        $model_qc = Loader::model('Rijizhang');
        $ret = $model_qc->delrijizhang($id);
        if ($ret){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }
//   获取项目类别，一级明细，二级明细和摘要
    public function getyijimxBytype($dept_id){
        $model_rjz = Loader::model('Rijizhang');
        $xmtype = $model_rjz->getxmtype();
       foreach ($xmtype as $xm_k=>&$xm_v){
           $ret = $model_rjz->getyijimxBytype($xm_v["id"]);
           foreach ($ret as $k=>&$v){
               $yijimx = $model_rjz->getyijimx($v["id"]);
               if($yijimx["name"]=="经营费用"||$yijimx["name"]=="车辆费用"||$yijimx["name"]=="其他收入"){
                   $ret1 = $model_rjz->getyijimxBytype($v["id"]);
                   $v["children"]=$ret1;
               }
               elseif($yijimx["name"]=="经营部资金调入"||$yijimx["name"]=="资金调成总"||$yijimx["name"]=="资金调经营部"||$yijimx["name"]=="代收款"||$yijimx["name"]=="代支款"){
                   $ret2 = $model_rjz->getdept();
                   foreach ($ret2 as $k2=>&$v2){
                       $ret3 = $model_rjz->getremarkBydept($v2["id"]);
                       foreach ($ret3 as $k3=>&$v3){
                           $v3["name"] = $v3["skhm"].$v3["rbmc"];
                           unset($v3["skhm"]);
                           unset($v3["rbmc"]);
                       }
                       $v2["children"]=$ret3;
                   }
                   $v["children"]=$ret2;
               }
               else{
                   $ysarr = array("12","17");
                   $yingsarr = array("71","63");
                   $zcarr = array("13","18");
                   $yfarr = array("69");
                   $qtysarr = array("70");
                   $qtyfarr = array("72","73");
                   if (in_array($v["id"],$ysarr)){
                       $customer = $model_rjz->getcustomer(3,$dept_id);
                       $v["children"]=$customer;
                   }
                   elseif(in_array($v["id"],$yingsarr)){
                        $customer = $model_rjz->getcustomer(2,$dept_id);
                        $v["children"]=$customer;
                   }
                   elseif(in_array($v["id"],$zcarr)){
                       $customer = $model_rjz->getcustomer(4,$dept_id);
                       $v["children"]=$customer;
                   }
                   elseif(in_array($v["id"],$yfarr)){
                       $customer = $model_rjz->getcustomer(5,$dept_id);
                       $v["children"]=$customer;
                   }
                   elseif(in_array($v["id"],$qtysarr)){
                       $customer = $model_rjz->getcustomer(6,$dept_id);
                       $v["children"]=$customer;
                   }
                   elseif(in_array($v["id"],$qtyfarr)){
                       $customer = $model_rjz->getcustomer(7,$dept_id);
                       $v["children"]=$customer;
                   }
               }
           }
           $xm_v["children"]=$ret;
       }
        return retmsg(0,$xmtype);
    }
//    根据一级明细id获取其二级明细
    public function geterjimxByyiji($id,$dept){
        $model_rjz = Loader::model('Rijizhang');
        $yijimx = $model_rjz->getyijimx($id);
        if($yijimx["name"]=="经营费用"||$yijimx["name"]=="车辆费用"){
            $ret = $model_rjz->getyijimxBytype($id);
            if ($ret){
                return retmsg(0,$ret);
            }else{
                return retmsg(0);
            }
        }
        elseif($yijimx["name"]=="经营部资金调入"||$yijimx["name"]=="资金调成总"||$yijimx["name"]=="资金调经营部"){
            $ret = $model_rjz->getdept($dept);
            if ($ret){
                return retmsg(0,$ret);
            }else{
                return retmsg(0);
            }
        }
        else{
            return retmsg(0,null);
        }
    }
//    根据部门名称获取其摘要
    public function getremarkBydept($deptid,$remark){
        $model_rjz = Loader::model('Rijizhang');
        $ret = $model_rjz->getremarkBydept($deptid,$remark);
        $res = array();
        foreach ($ret as $k=>$v){
            $v["remark"] = $v["skhm"]."+".$v["rbmc"];
            unset($v["skhm"]);
            unset($v["rbmc"]);
            array_push($res,$v);
        }
        if($ret){
            return retmsg(0,$res);
        }else{
            return retmsg(0);
        }
    }
//    费用明细/费用明细
    public function getfymx($page,$pagesize,$stime,$etime,$dept_id){
        $model_rjz = Loader::model('Rijizhang');
        $ret = $model_rjz->getfymx($page,$pagesize,$stime,$etime,$dept_id);
        if($ret){
            return retmsg(0,$ret);
        }else{
            return retmsg(0);
        }
    }
//    费用明细/费用汇总表
    public function getfyhz($stime,$etime,$dept_id){
        $model_rjz = Loader::model('Rijizhang');
        $ret = $model_rjz->getfyhz($stime,$etime,$dept_id);
        $list1 = array();
        $list3 = array();
        $clheji = 0;
        $jyheji = 0;
        foreach ($ret["erjijylist"] as $k=>$v){
            $list["spending"]=0;
            $list["name"]=$v["name"];
            foreach ($ret["jylist"] as $k1=>$v1){
                if($v["name"] == $v1["name"]){
                    $list["spending"] += $v1["spending"];
                    $list["yw_type_name"] = $v1["yw_type_name"];
                    $list["handlers"] = $v1["handlers"];
                }else{
                    $list["yw_type_name"] = "";
                    $list["handlers"] = "";
                }
            }
            $jyheji += $list["spending"];
            array_push($list1,$list);
        }
        foreach ($ret["erjicllist"] as $k2=>$v2){
            $list2["clspending"]=0;
            $list2["clname"]=$v2["name"];
            foreach ($ret["cllist"] as $k3=>$v3){
                if($v2["name"] == $v3["name"]){
                    $list2["clspending"] += $v3["spending"];
                    $list2["clyw_type_name"] = $v3["yw_type_name"];
                    $list2["clhandlers"] = $v3["handlers"];
                }else{
                    $list2["clyw_type_name"] = "";
                    $list2["clhandlers"] = "";
                }
            }
            $clheji += $list2["clspending"];
            array_push($list3,$list2);
        }
        $a["spending"] = $jyheji+$clheji;
        $a["spending"] = sprintf("%.2f",$a["spending"]);
        $a["name"] = "经营费用合计";
        $a["yw_type_name"] = "";
        $a["handlers"] = "";
        $lastlist = array();
        array_unshift($list1,$a);
       foreach ($list1 as $k4=>$v4){
           $list4["name"] = $v4["name"];
           $list4["yw_type_name"] = $v4["yw_type_name"];
           $list4["spending"] = $v4["spending"];
           $list4["handlers"] = $v4["handlers"];
           foreach ($list3 as $k5=>$v5){
                if ($k4 == $k5) {
                    $list4["clname"] = $v5["clname"];
                    $list4["clyw_type_name"] = $v5["clyw_type_name"];
                    $list4["clspending"] = $v5["clspending"];
                    $list4["clhandlers"] = $v5["clhandlers"];
                }
           }
           if($k4 > count($list3)-1){
                    $list4["clname"] = "";
                    $list4["clyw_type_name"] = "";
                    $list4["clspending"] = "";
                    $list4["clhandlers"] = "";
           }
           array_push($lastlist, $list4);
       }
        if($ret){
            return retmsg(0,array("header"=>$ret["header"],"list"=>$lastlist));
        }else{
            return retmsg(0);
        }
    }
//    片区经营部列表
    public function getdeptlist(){
        $model_rjz = Loader::model('Rijizhang');
        $ret = $model_rjz->getdeptlist();
        $res = array();
        foreach ($ret as $k=>$v){
            $v["children"] = $model_rjz->getseconddept($v["id"]);
            array_push($res,$v);
        }
        if($ret){
            return retmsg(0,$res);
        }else{
            return retmsg(0);
        }
    }
//    现金对账单
    public function getDZD($stime,$etime,$dept_id){
        $model_rjz = Loader::model('Rijizhang');
        $model_rjz2 = Loader::model('Rijizhang','logic');
        $model_rjz1 = Loader::model('Zhangkuan','logic');
        $ret = $model_rjz->getDZD($stime,$etime,$dept_id);
        $list =array();
        $res = array();
        foreach ($ret["list"] as $k=>$v){
            $yijimx = $model_rjz->getyijimx($v["yijimx"]);
            $erjimx = $model_rjz->getyijimx($v["erjimx"]);
            if(!empty($erjimx)){
                $v["erjimx"] = $erjimx["name"];
            }
            $v["yijimx"] = $yijimx["name"];
            array_push($res,$v);
        }
        $ret["list"]=$res;
        $zijindiaobo = 0;$xsshouru = 0;
        $zijindiaoru = 0;$yingshouzhichu = 0;
        $fyzhichu = 0; $yingshoushouru = 0;
        $yushouzhichu = 0;$yushoushouru = 0;
        $zancunzhichu = 0;$zancunshouru = 0;
        $yingfuzhichu = 0;$yingfushouru = 0;
        $otheryszhichu = 0;$otherysshouru = 0;$otheryfzhichu = 0;$otheryfshouru = 0;
        $othershouru = 0;$gudingzichan = 0;
        $daizhicaigou = 0;$daizhiotherdept = 0;
        $diyiping = 0; $zhifugongzi = 0;
        $fudongxinchou = 0;$zhifuyuti = 0;
        $daichuli = 0;
        $yingshouqclist = $model_rjz1->getyshz(1,999999,$stime,$etime,$type="应收账款总账",$dept_id);
        $yingshouqc = $yingshouqclist["data"]["heji"]["qichu"];
        $yingshouqm = $yingshouqclist["data"]["heji"]["qimo"];
        $yingfuqclist = $model_rjz1->getyshz(1,999999,$stime,$etime,$type="应付账款总账",$dept_id);
        $yingfuqc = $yingfuqclist["data"]["heji"]["qichu"];
        $yingfuqm = $yingfuqclist["data"]["heji"]["qimo"];
        $zancunqclist = $model_rjz1->getyshz(1,999999,$stime,$etime,$type="暂存款总账",$dept_id);
        $zancunqc = $zancunqclist["data"]["heji"]["qichu"];
        $zancunqm = $zancunqclist["data"]["heji"]["qimo"];
        $yushouqclist = $model_rjz1->getyshz(1,999999,$stime,$etime,$type="预收账款总账",$dept_id);
        $yushouqc = $yushouqclist["data"]["heji"]["qichu"];
        $yushouqm = $yushouqclist["data"]["heji"]["qimo"];
        $otheryslist = $model_rjz1->getyshz(1,999999,$stime,$etime,$type="其他应收账款总账",$dept_id);
        $otherysqc = $otheryslist["data"]["heji"]["qichu"];
        $otherysqm = $otheryslist["data"]["heji"]["qimo"];
        $otheryflist = $model_rjz1->getyshz(1,999999,$stime,$etime,$type="其他应付账款总账",$dept_id);
        $otheryfqc = $otheryflist["data"]["heji"]["qichu"];
        $otheryfqm = $otheryflist["data"]["heji"]["qimo"];
        $huangjie = 0;$luobo=0;$dufei = 0;$xiongshanhong = 0;$yangshangyi = 0;
        $yw1=null;$yw2=null;$yw3=null;$yw4=null;$yw5=null;
        foreach ($ret["list"] as $k1=>$v1){
            if ($v1["xm_type"]=="资产类现金支出"){
                if($v1["yijimx"]=="资金调成总"||$v1["yijimx"]=="资金调经营部"){
                    $zijindiaobo +=$v1["spending"];
                }
                if($v1["yijimx"]=="支外购款"||$v1["yijimx"]=="应付账款"){
                    $yingfuzhichu += $v1["spending"];
                    $yingfushouru += $v1["income"];
                }
                if ($v1["yijimx"]=="代支采购贷款"){
                    $daizhicaigou +=$v1["spending"];
                }
                if ($v1["yijimx"]=="代支其他部门"){
                    $daizhiotherdept +=$v1["spending"];
                }
                if ($v1["yijimx"]=="增加固定资产"){
                    $gudingzichan +=$v1["spending"];
                }
                if ($v1["yijimx"]=="增加低易品与待摊费用"){
                    $diyiping +=$v1["spending"];
                }
                if ($v1["yijimx"]=="支付工资"){
                    $zhifugongzi +=$v1["spending"];
                }
                if ($v1["yijimx"]=="支付职工浮动薪酬"){
                    $fudongxinchou +=$v1["spending"];
                }
                if ($v1["yijimx"]=="支付预提"){
                    $zhifuyuti +=$v1["spending"];
                }
                if ($v1["yijimx"]=="待处理"){
                    $daichuli +=$v1["spending"];
                }
            }
            if ($v1["xm_type"]=="资产类现金收入"){
                if($v1["yijimx"]=="经营部资金调入"){
                    $zijindiaoru +=$v1["income"];
                }
            }
            if ($v1["xm_type"]=="费用类现金支出"){
                if($v1["yijimx"]=="经营费用"||$v1["yijimx"]=="车辆费用"){
                    $fyzhichu += $v1["spending"];
                }
            }
            if ($v1["xm_type"]=="损益类现金收入"){
                if($v1["yijimx"]=="销售收入"){
                    $xsshouru += $v1["income"];
                    if($v1["handlers"]=="黄杰（东兴）"){
                        $huangjie +=$v1["income"];
                        $yw1 = $v1["yw_type_name"];
                    }elseif ($v1["handlers"]=="罗波"){
                        $luobo +=$v1["income"];
                        $yw2 = $v1["yw_type_name"];
                    }
                    elseif ($v1["handlers"]=="杜飞（土门）"){
                        $dufei +=$v1["income"];
                        $yw3 = $v1["yw_type_name"];
                    }
                    elseif ($v1["handlers"]=="熊善红"){
                        $xiongshanhong +=$v1["income"];
                        $yw4 = $v1["yw_type_name"];
                    }
                    elseif ($v1["handlers"]=="杨尚翼"){
                        $yangshangyi +=$v1["income"];
                        $yw5 = $v1["yw_type_name"];
                    }
                }else{
                    $othershouru +=$v1["income"];
                }
            }
            if ($v1["xm_type"]=="资产类现金收入"||$v1["xm_type"]=="应收款"){
                if($v1["yijimx"]=="收回欠款"||$v1["yijimx"]=="新增"||$v1["yijimx"]=="应收账款"){
                    $yingshouzhichu += $v1["spending"];
                    $yingshoushouru += $v1["income"];
                }
            }
            if ($v1["xm_type"]=="资产类现金收入"||$v1["xm_type"]=="资产类现金支出"){
                if($v1["yijimx"]=="增加预收款"||$v1["yijimx"]=="减少预收款"||$v1["yijimx"]=="预收账款"){
                    $yushouzhichu += $v1["spending"];
                    $yushoushouru += $v1["income"];
                }
                if ($v1["yijimx"]=="增加暂存款"||$v1["yijimx"]=="减少暂存款"||$v1["yijimx"]=="暂存款"){
                    $zancunzhichu += $v1["spending"];
                    $zancunshouru += $v1["income"];
                }
                if($v1["yijimx"]=="职工还借"||$v1["yijimx"]=="职工借款"||$v1["yijimx"]=="其他应收款"){
                    $otheryszhichu += $v1["spending"];
                    $otherysshouru += $v1["income"];
                }
                if($v1["yijimx"]=="支押金"||$v1["yijimx"]=="收押金"||$v1["yijimx"]=="其他应付款"){
                    $otheryfzhichu += $v1["spending"];
                    $otheryfshouru += $v1["income"];
                }
            }
        }
        $zijindiaobo = sprintf("%.2f",$zijindiaobo);
        $zijindiaoru = sprintf("%.2f",$zijindiaoru);
        $fyzhichu = sprintf("%.2f",$fyzhichu);$yingshouzhichu = sprintf("%.2f",$yingshouzhichu);
        $yushouzhichu = sprintf("%.2f",$yushouzhichu);$huangjie = sprintf("%.2f",$huangjie);
        $luobo = sprintf("%.2f",$luobo);$zancunzhichu = sprintf("%.2f",$zancunzhichu);
        $xsshouru = sprintf("%.2f",$xsshouru);$dufei = sprintf("%.2f",$dufei);
        $yingfuzhichu = sprintf("%.2f",$yingfuzhichu);$xiongshanhong = sprintf("%.2f",$xiongshanhong);
        $otheryszhichu = sprintf("%.2f",$otheryszhichu);$yangshangyi = sprintf("%.2f",$yangshangyi);
        $otheryfzhichu = sprintf("%.2f",$otheryfzhichu);$daizhicaigou = sprintf("%.2f",$daizhicaigou);
        $yingshoushouru = sprintf("%.2f",$yingshoushouru);$daizhiotherdept = sprintf("%.2f",$daizhiotherdept);
        $yushoushouru = sprintf("%.2f",$yushoushouru);$gudingzichan = sprintf("%.2f",$gudingzichan);
        $line1 = array("val1"=>"现金上期结存","val2"=>"","val3"=>$ret["lastjc"]["qichu"],
            "val4"=>"资金调出","val5"=>"","val6"=>$zijindiaobo);array_push($list,$line1);
        $line2 = array("val1"=>"资金调入","val2"=>"","val3"=>$zijindiaoru,
            "val4"=>"费用支出","val5"=>"","val6"=>$fyzhichu);array_push($list,$line2);
        $line3 = array("val1"=>"本期销售收入","val2"=>"","val3"=>$xsshouru,
            "val4"=>"应收账款新增","val5"=>"","val6"=>$yingshouzhichu);array_push($list,$line3);
        $line4 = array("val1"=>"黄杰（东兴）","val2"=>$yw1,"val3"=>$huangjie,
            "val4"=>"预收账款支出","val5"=>"","val6"=>$yushouzhichu);array_push($list,$line4);
        $line5 = array("val1"=>"罗波","val2"=>$yw2,"val3"=>$luobo,
            "val4"=>"暂存款支出","val5"=>"","val6"=>$zancunzhichu);array_push($list,$line5);
        $line6 = array("val1"=>"杜飞（土门）","val2"=>$yw3,"val3"=>$dufei,
            "val4"=>"应付账款支出","val5"=>"","val6"=>$yingfuzhichu);array_push($list,$line6);
        $line7 = array("val1"=>"熊善红","val2"=>$yw4,"val3"=>$xiongshanhong,
            "val4"=>"其他应收款新增","val5"=>"","val6"=>$otheryszhichu);array_push($list,$line7);
        $line8 = array("val1"=>"杨尚翼","val2"=>$yw5,"val3"=>$yangshangyi,
            "val4"=>"其他应付款新增","val5"=>"","val6"=>$otheryfzhichu);array_push($list,$line8);
        $line9 = array("val1"=>"","val2"=>"","val3"=>"",
            "val4"=>"代支采购贷款","val5"=>"","val6"=>$daizhicaigou);array_push($list,$line9);
        $line10 = array("val1"=>"应收账款收回","val2"=>"","val3"=>$yingshoushouru,
            "val4"=>"代支其他部门","val5"=>"","val6"=>$daizhiotherdept);array_push($list,$line10);
        $line11 = array("val1"=>"预收账款收入","val2"=>"","val3"=>$yushoushouru,
            "val4"=>"增加固定资产","val5"=>"","val6"=>$gudingzichan);array_push($list,$line11);
        $zancunshouru = sprintf("%.2f",$zancunshouru);$diyiping = sprintf("%.2f",$diyiping);
        $line12 = array("val1"=>"暂存款收入","val2"=>"","val3"=>$zancunshouru,
            "val4"=>"增加低易品与待摊费用","val5"=>"","val6"=>$diyiping);array_push($list,$line12);
        $yingfushouru = sprintf("%.2f",$yingfushouru);$zhifugongzi = sprintf("%.2f",$zhifugongzi);
        $line13 = array("val1"=>"应付账款新增","val2"=>"","val3"=>$yingfushouru,
            "val4"=>"支付工资","val5"=>"","val6"=>$zhifugongzi);array_push($list,$line13);
        $otherysshouru = sprintf("%.2f",$otherysshouru);$fudongxinchou = sprintf("%.2f",$fudongxinchou);
        $line14 = array("val1"=>"其他应收款收回","val2"=>"","val3"=>$otherysshouru,
            "val4"=>"支付职工浮动薪酬","val5"=>"","val6"=>$fudongxinchou);array_push($list,$line14);
        $otheryfshouru = sprintf("%.2f",$otheryfshouru);$zhifuyuti = sprintf("%.2f",$zhifuyuti);
        $line15 = array("val1"=>"其他应付款收入","val2"=>"","val3"=>$otheryfshouru,
            "val4"=>"支付预提","val5"=>"","val6"=>$zhifuyuti);array_push($list,$line15);
        $othershouru = sprintf("%.2f",$othershouru);$daichuli = sprintf("%.2f",$daichuli);
        $line16 = array("val1"=>"其他收入","val2"=>"","val3"=>$othershouru,
            "val4"=>"待处理","val5"=>"","val6"=>$daichuli);array_push($list,$line16);
        $yingshouqc = sprintf("%.2f",$yingshouqc);$yingshouqm = sprintf("%.2f",$yingshouqm);
        $line17 = array("val1"=>"应收账款上期余额","val2"=>"","val3"=>$yingshouqc,
            "val4"=>"应收账款本期余额","val5"=>"","val6"=>$yingshouqm);array_push($list,$line17);
        $yushouqc = sprintf("%.2f",$yushouqc);$yushouqm = sprintf("%.2f",$yushouqm);
        $line18 = array("val1"=>"预收账款上期余额","val2"=>"","val3"=>$yushouqc,
            "val4"=>"预收账款本期余额","val5"=>"","val6"=>$yushouqm);array_push($list,$line18);
        $zancunqc = sprintf("%.2f",$zancunqc);$zancunqm = sprintf("%.2f",$zancunqm);
        $line19 = array("val1"=>"暂存款上期余额","val2"=>"","val3"=>$zancunqc,
            "val4"=>"暂存款本期余额","val5"=>"","val6"=>$zancunqm);array_push($list,$line19);
        $yingfuqc = sprintf("%.2f",$yingfuqc);$yingfuqm = sprintf("%.2f",$yingfuqm);
        $line20 = array("val1"=>"应付账款上期余额","val2"=>"","val3"=>$yingfuqc,
            "val4"=>"应付账款本期余额","val5"=>"","val6"=>$yingfuqm);array_push($list,$line20);
        $otherysqc = sprintf("%.2f",$otherysqc);$otherysqm = sprintf("%.2f",$otherysqm);
        $line21 = array("val1"=>"其他应收账款上期余额","val2"=>"","val3"=>$otherysqc,
            "val4"=>"其他应收款本期余额","val5"=>"","val6"=>$otherysqm);array_push($list,$line21);
        $otheryfqc = sprintf("%.2f",$otheryfqc);$otheryfqm = sprintf("%.2f",$otheryfqm);
        $line22 = array("val1"=>"其他应付账款上期余额","val2"=>"","val3"=>$otheryfqc,
            "val4"=>"其他应付款本期余额","val5"=>"","val6"=>$otheryfqm);array_push($list,$line22);
        $xianjinjc = $model_rjz2->getrijizhang(1,999999,$stime,$etime,null,$dept_id);
        $jc = $xianjinjc["data"]["heji"]["balance"];
        $jc = sprintf("%.2f",$jc);
        $line23 = array("val1"=>"","val2"=>"","val3"=>"",
            "val4"=>"现金结存","val5"=>"","val6"=>$jc);array_push($list,$line23);
        $shouruheji = $ret["lastjc"]["qichu"]+$zijindiaoru+$xsshouru+$yingshoushouru+$yushoushouru+$zancunshouru+$yingfushouru+$otherysshouru
            +$otheryfshouru+$othershouru;
        $zhichuheji = $zijindiaobo+$fyzhichu+$yingshouzhichu+$yushouzhichu+$zancunzhichu+$yingfuzhichu+$otheryszhichu+$otheryfzhichu
            +$daizhicaigou+$daizhiotherdept+$gudingzichan+$diyiping+$zhifugongzi+$fudongxinchou+$zhifuyuti+$daichuli;
        $shouruheji = sprintf("%.2f",$shouruheji);$zhichuheji = sprintf("%.2f",$zhichuheji);
        $line24 = array("val1"=>"合计","val2"=>"","val3"=>$shouruheji,
            "val4"=>"合计","val5"=>"","val6"=>$zhichuheji);array_push($list,$line24);
        $line25 = array("val1"=>"","val2"=>"核对","val3"=>"",
            "val4"=>"","val5"=>"","val6"=>"");array_push($list,$line25);
        $line26 = array("val1"=>"负责人","val2"=>"","val3"=>"",
            "val4"=>"制表人","val5"=>"","val6"=>"");array_push($list,$line26);
        if($ret){
            return retmsg(0,array("header"=>$ret["header"],"list"=>$list));
        }else{
            return retmsg(0);
        }
    }
}