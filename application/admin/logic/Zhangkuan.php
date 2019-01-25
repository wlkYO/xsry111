<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 17:37
 */

namespace app\admin\logic;


use think\Loader;

class Zhangkuan
{
//    预收账款/预收账款明细
    public function getysmx($page,$pagesize,$stime,$etime,$type,$dept_id){
        $model_rjz = Loader::model('Zhangkuan');
        $ret = $model_rjz->getysmx($page,$pagesize,$stime,$etime,$type,$dept_id);
        if($ret){
            return retmsg(0,$ret);
        }else{
            return retmsg(0);
        }
    }
//    预收账款/预收账款总账
    public function getyshz($page,$pagesize,$stime,$etime,$type,$dept_id){
        $model_rjz = Loader::model('Zhangkuan');
        $ret = $model_rjz->getyshz($page,$pagesize,$stime,$etime,$type,$dept_id);
//        return $ret;
        if($ret["remark"]==2){
            return retmsg(0,array("header"=>$ret["header"],"total"=>$ret["total"],"list"=>$ret["list"],"heji"=>$ret["heji"]));
        }
        $list1 = array();
        $list3 =array();
        $list3["qichu"]=0;
        $list3["income"]=0;
        $list3["spending"]=0;
        $list3["qimo"]=0;
        foreach ($ret["userlist"] as $k=>$v){
            $list["name"]=$v["name"];
            $list["yw_type_name"]=null;
            $list["qichu"]=$v["qichu"];
            $list["income"]=0;$list["spending"]=0;$list["qimo"]=null;
            $list["handlers"]=null;
            foreach ($ret["mxlist"] as $k1=>$v1){
                if($v["name"] == $v1["name"]){
                    $list["spending"] += $v1["spending"];
                    $list["income"] += $v1["income"];
                    $list["yw_type_name"] = $v1["yw_type_name"];
                    $list["handlers"] = $v1["handlers"];
                }
            }
            if ($type=="其他应收账款总账"){
                $list["qimo"] = $list["qichu"]+$list["spending"]-$list["income"];
            }
            else{
                $list["qimo"] = $list["qichu"]+$list["income"]-$list["spending"];
            }
            $list["qichu"] = sprintf("%.2f",$list["qichu"]);
            $list["income"] = sprintf("%.2f",$list["income"]);
            $list["spending"] = sprintf("%.2f",$list["spending"]);
            $list["qimo"] = sprintf("%.2f",$list["qimo"]);
            array_push($list1,$list);
        }
        foreach ($list1 as $k1=>$v1){
            $list3["qichu"] +=$v1["qichu"];
            $list3["qimo"] += $v1["qimo"];
            $list3["income"] +=$v1["income"];
            $list3["spending"] +=$v1["spending"];
        }
        $list3["name"] = "合计";
        $list3["qichu"] = sprintf("%.2f",$list3["qichu"]);
        $list3["qimo"] = sprintf("%.2f",$list3["qimo"]);
        $list3["income"] = sprintf("%.2f",$list3["income"]);
        $list3["spending"] = sprintf("%.2f",$list3["spending"]);
//        var_dump($list3);
//        die();
        if($ret){
            return retmsg(0,array("header"=>$ret["header"],"total"=>$ret["total"],"list"=>$list1,"heji"=>$list3));
        }else{
            return retmsg(0);
        }
    }
}