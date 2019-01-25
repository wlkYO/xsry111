<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16
 * Time: 19:38
 */

namespace app\admin\controller;
use app\admin\service\DownloadService;
use app\admin\service\ExportService;
use app\admin\service\ImportExcelService;
use think\Loader;

class Pingzheng
{
    private $pingzhengLogic;

    public function __construct()
    {
        $logicPingzheng = Loader::model('Pingzheng','logic');
        $this->pingzhengLogic = $logicPingzheng;
    }

    public function searchCash($sdate='', $edate='', $depts='71,1037')
    {
        $sdate = empty($sdate)?date('Y-m-01'):$sdate;
        $edate = empty($edate)?date('Y-m-d'):$edate;
        $result = $this->pingzhengLogic->getPingzheng($sdate, $edate, $depts);
        # 获取选择时间段内的销售日报系统现金日记账记录
//        var_dump($cash_riji);
        return $result;
    }


    public function downloadTable($type =1,$sdate='', $edate='', $depts='71,1037')
    {
        if ($type == 1) {
            $printData = $this->search($sdate, $edate, $depts);
        }
        $title = array('部门','日期','总账科目','二级科目','三级科目','期初余额','借方','贷方','期末余额','摘要','制单人','业务类别');
        $listData = array();
        foreach ($printData['pingzhengku'] as $key => $val) {
            $temp = array();
            $temp['dname'] = $val['dname'];
            $temp['date'] = $this->dateConvert($val['date']);
            $temp['zongzhang_kemu'] = $val['zongzhang_kemu'];
            $temp['yiji_kemu'] = $val['yiji_kemu'];
            $temp['erji_kemu'] = $val['erji_kemu'];
            $temp['qichu_yue'] = $val['qichu_yue'];
            $temp['jiefang'] = $val['jiefang'];
            $temp['daifang'] = $val['daifang'];
            $temp['qimo_yue'] = $val['qimo_yue'];
            $temp['zhaiyao'] = $val['zhaiyao'];
            $temp['create_by'] = $val['create_by'];
            $temp['yewu_type'] = $val['yewu_type'];
            array_push($listData, $temp);
        }
        $data = array(
            'head'=>$title,
            'data'=>$listData,
        );
        $tableName = ($type == 1)?"现金类会计凭证库":"商品类会计凭证库";
        return DownloadService::downLoadExcel($tableName, DownloadService::getObjPHPExcel($tableName,$data));
    }

    public function dateConvert($date)
    {
        $nian = date('Y', strtotime($date));
        $yue = date('m', strtotime($date));
        $day = date('d', strtotime($date));
        return $nian.'年'.$yue.'月'.$day.'日';
    }


    //--------------------------商品类商品凭证start------------------------------

    /**
     * @param string $sdate
     * @param string $edate
     * @param int $type    0: 无效    1:有效
     * @return mixed
     */
    public function createPingZheng($sdate='', $edate='',$type =1){
        $sdate = empty($sdate)?date('Y-m-01',time()):$sdate;
        $edate = empty($edate)?date('Y-m-d',time()):$edate;
        if($type == 0){
            $invalidModel = Loader::model('Pingzheng','model');
            $res = $invalidModel->createInvalidPz($sdate, $edate,$type);
            if(!empty($res)){
                return retmsg(0);
            }
            return retmsg(-1);
        }
        $logicPz  = Loader::model('Pingzheng','logic');
        $res = $logicPz->createPingZheng($sdate,$edate);
        return $res;
    }

    /**
     * 查询商品类的凭证,得到收入和收出的数组。
     * @param string $sdate
     * @param string $edate
     * @param string $depts
     * @return mixed
     */
    public function searchProduct($sdate='', $edate='')
    {
        $sdate = empty($sdate)?date('Y-m-01',time()):$sdate;
        $edate = empty($edate)?date('Y-m-d',time()):$edate;
        $logicPz = Loader::model('Pingzheng','logic');
        $res = $logicPz->searchProduct($sdate,$edate);
        return $res;
    }

    //--------------------------商品类商品凭证end------------------------------

}