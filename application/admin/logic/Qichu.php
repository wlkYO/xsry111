<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5
 * Time: 15:43
 */

namespace app\admin\logic;


use think\Loader;

class Qichu
{
//  现金结存初期
    public function getjiecunqc($page,$pagesize,$dept_id){
        $model_qc = Loader::model('Qichu');
        $ret = $model_qc->getjiecunqc($page,$pagesize,$dept_id);
        if($ret){
            return retmsg(0,$ret);
        }
        else{
            return retmsg(0);
        }
    }
//    添加现金结存初期
    public function addjiecunqc($postdata,$dept_id,$username){
        $model_qc = Loader::model('Qichu');
        $endres = true;
        foreach ($postdata["data"] as $k=>$v) {
            if (array_key_exists("id", $v)) {
                $ret = $model_qc->editjiecunqc($v, $username);
                $endres = $endres & $ret;
            } else {
                $ret = $model_qc->addjiecunqc($v, $dept_id, $username);
                $endres = $endres & $ret;
            }
        }
        if($endres){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }
//    修改现金结存初期
    public function editjiecunqc($postdata){
        $model_qc = Loader::model('Qichu');
        $endres = true;
        foreach ($postdata["data"] as $k=>$v){
            $ret = $model_qc->editjiecunqc($v);
            $endres = $endres&$ret;
        }
        if($endres){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }
//    删除现金结存初期
    public function deljiecunqc($id){
        $model_qc = Loader::model('Qichu');
        $ret = $model_qc->deljiecunqc($id);
        if ($ret){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }
//    应收账款初期
    public function getYSqc($page,$pagesize,$qctype,$dept_id){
        $model_qc = Loader::model('Qichu');
        $ret = $model_qc->getYSqc($page,$pagesize,$qctype,$dept_id);
        if($ret){
            return retmsg(0,$ret);
        }
        else{
            return retmsg(0);
        }
    }
//    添加应收账款初期
    public function addYSqc($postdata,$qctype,$dept_id,$username){
        $model_qc = Loader::model('Qichu');
        $endres = true;
        $error = array();
        if($qctype == "应收账款期初"){
            $errorheader = array(
                array("headerName"=>"行数","field"=>"line"),
                array("headerName"=>"片区","field"=>"area"),
                array("headerName"=>"经营部","field"=>"dept"),
                array("headerName"=>"客户姓名","field"=>"cname"),
                array("headerName"=>"期初欠款日期","field"=>"date"),
                array("headerName"=>"业务类别","field"=>"yw_type_nameT"),
                array("headerName"=>"期初","field"=>"qichu"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"备注","field"=>"remark"),
            );
        }
        elseif ($qctype == "预收账款期初"||$qctype == "暂存款期初"||$qctype == "应付账款期初"){
            $errorheader = array(
                array("headerName"=>"行数","field"=>"line"),
                array("headerName"=>"片区","field"=>"area"),
                array("headerName"=>"经营部","field"=>"dept"),
                array("headerName"=>"客户姓名","field"=>"cname"),
                array("headerName"=>"业务类别","field"=>"yw_type_nameT"),
                array("headerName"=>"初期","field"=>"qichu"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"备注","field"=>"remark"),
            );

        }else{
            $errorheader = array(
                array("headerName"=>"行数","field"=>"line"),
                array("headerName"=>"片区","field"=>"area"),
                array("headerName"=>"经营部","field"=>"dept"),
                array("headerName"=>"明细科目","field"=>"cname"),
                array("headerName"=>"业务类别","field"=>"yw_type_nameT"),
                array("headerName"=>"初期","field"=>"qichu"),
                array("headerName"=>"经手人","field"=>"handlers"),
                array("headerName"=>"备注","field"=>"remark"),
            );
        }
        foreach ($postdata["data"] as $k=>&$v){
            if (array_key_exists("id",$v)){
                $ishaverjz = $model_qc->ishaverjz($v["id"]);
                if (!empty($ishaverjz["rjz"])){
                    if ($ishaverjz["cname"]!==$v["cname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的客户存在日记账，暂时不能修改客户名称";
                        array_push($error,$v);
                    }else{
                        $ret = $model_qc->editYSqc($v,$qctype,$username);
                        $endres = $endres&$ret;
                    }
                }else{
                    $ret = $model_qc->editYSqc($v,$qctype,$username);
                    $endres = $endres&$ret;
                }
            }else{
                $ret = $model_qc->addYSqc($v,$qctype,$dept_id,$username);
                $endres = $endres&$ret;
            }
        }
        if($endres){
            if (!empty($error)){
                return retmsg(-1,array("error"=>$error,"errorheader"=>$errorheader));
            }else{
                return retmsg(0);
            }
        }else{
            return retmsg(-1);
        }
    }
//    修改应收账款初期
    public function editYSqc($postdata,$qctype){
        $model_qc = Loader::model('Qichu');
        $endres = true;
        foreach ($postdata["data"] as $k=>$v){
            $ret = $model_qc->editYSqc($v,$qctype);
            $endres = $endres&$ret;
        }
        if($endres){
            return retmsg(0);
        }else{
            return retmsg(-1);
        }
    }
//    删除应收账款初期
    public function delYSqc($id,$dept){
        $model_qc = Loader::model('Qichu');
        $ishaverjz = $model_qc->ishaverjz($id,$dept);
        if (!empty($ishaverjz["rjz"])){
                return array("resultcode"=>-1,"resultmsg"=>"该条数据的客户存在日记账，暂时不能删除","data"=>null);
        }else{
            $ret = $model_qc->delYSqc($id);
            if ($ret){
                return retmsg(0);
            }else{
                return retmsg(-1);
            }
        }
    }
//    月初生成下月期初
    public function nextqc(){
        $model_qc = Loader::model('Qichu');
        $model_rjz = Loader::model('Rijizhang','logic');
        $ret = $model_qc->nextqc();
//        现金结存期初等于现金日记账合计余额
        $xjjc = $model_rjz->getrijizhang(1,99999,null,null,null);
        foreach ($ret as $k=>&$v){
            $rjz = $model_qc->getrjz($v["cname"]);
            $v["create_time"] = date('Y-m-d H:i:s',time());
            $v["month"] = date('Y-m',time());
            if ($v["qc_type_id"]==6){
                $v["qichu"]=$v["qichu"]+$rjz["spending"]-$rjz["income"];
            }
            elseif ($v["qc_type_id"]==1){
                $v["qichu"]=$xjjc["data"]["heji"]["balance"];
            }
            else{
                $v["qichu"]=$v["qichu"]+$rjz["income"]-$rjz["spending"];
            }
            unset($v["id"]);
            unset($v["update_time"]);
        }
        $addnextqc = $model_qc->addnextqc($ret);
        if($addnextqc){
            return retmsg(0);
        }
        else{
            return retmsg(-1);
        }
    }
}