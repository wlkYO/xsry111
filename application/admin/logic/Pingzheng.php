<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16
 * Time: 19:37
 */

namespace app\admin\logic;


use think\Db;
use think\Loader;

class Pingzheng
{
    private $pingzhengModel;

    public function __construct()
    {
        $modelPingzheng = Loader::model('Pingzheng');
        $this->pingzhengModel = $modelPingzheng;
    }

    public function getPingzheng($sdate, $edate, $depts)
    {
        $cash_record = $this->pingzhengModel->getPingzheng($sdate, $edate, $depts);//获取现金日记账记录
        if (empty($cash_record)) {
            return -1;//没有查询到现金日记账明细数据
        }
        # 根据现金日记账记录及科目代码对应关系生成所选部门的凭证数据
        # 根据数据中的项目类别，一级明细名称及二级明细名称获取项目对应的id
        $errorData = array();//没有找到对应关系的数据
        $pingzhengData = array();//有对应关系，系统自动生成凭证数据
        foreach ($cash_record as $key => $val) {
            $xmlb = $val['xmlb'];
            $xm_yjmx = $val['yijimx_name'];
            $xm_ejmx = $val['erjimx_name'];
            $xm_relation = $this->pingzhengModel->getCashXmid($xmlb, $xm_yjmx, $xm_ejmx);
            if (!empty($xm_relation)) {
                $cash_record[$key]['xm_id'] = $xm_relation['xm_id'];
                $cash_record[$key]['subject_code'] = $xm_relation['subject_code'];
                $cash_record[$key]['subject_name'] = $xm_relation['subject_name'];
                array_push($pingzhengData, $cash_record[$key]);
            } else {
                array_push($errorData, $cash_record[$key]);
            }
        }
        # 将获取到对应关系的数据，系统自动生成对应的凭证
        if (!empty($pingzhengData)) {
            $insertData = $this->createPingzhengBySystem($pingzhengData);
        }
        return array('error_data' => $errorData, 'pingzheng_data' => $pingzhengData, 'pingzhengku' => $insertData);
    }

    public function createPingzhengBySystem($pingzhengData)
    {
        $insertData = array();
        foreach ($pingzhengData as $key => $val) {
            $combineData = $this->combinePingzhengData($val);
            foreach ($combineData as $k => $v) {
                array_push($insertData, $v);
            }
        }
        # 数据组装好之后进行入库操作
        $ret = $this->pingzhengModel->insertPingzheng($insertData);
        return $insertData;
    }

    public function combinePingzhengData($arr)
    {
        $result = array();
        # 每一条数据记录先生成一条总账科目为"库存现金"的数据，金额数据存储在借方处
        $temp1 = array();
        $temp1['type'] = 1;
        $temp1['dept_id'] = $arr['dept_id'];
        $temp1['dname'] = $arr['dname'];
        $temp1['date'] = $arr['date'];
        $temp1['zongzhang_kemu'] = '库存现金';//总账科目
        $temp1['yiji_kemu'] = '';//一级科目
        $temp1['erji_kemu'] = '';//二级科目
        $temp1['qichu_yue'] = '';//期初余额
        $temp1['qimo_yue'] = '';//期末余额
        $temp1['jiefang'] = (strpos($arr['xmlb'], '收入') !== false) ? $arr['income'] : '';//借方---收入类项目金额放借方
        $temp1['daifang'] = (strpos($arr['xmlb'], '支出') !== false) ? $arr['spending'] : '';//贷方---支出类项目金额放贷方
        $temp1['zhaiyao'] = $arr['yijimx_name'];//摘要=一级明细+二级明细+业务类别  .$arr['erjimx_name'].$arr['yw_type_name']
        $temp1['yewu_type'] = $arr['yw_type_name'];//业务类型
        $temp1['create_by'] = '系统';//制单人
        $temp1['create_time'] = date('Y-m-d H:i:s');//制单时间
        array_push($result, $temp1);
        if ($arr['xmlb'] == '损益类现金收入') {//$val['subject_name'] == '主营业务收入'
            if ($arr['subject_name'] == '内部往来') {//内部往来分拨两部分数据 40% 给片区，60%给当前部门
                $temp = array();
                $temp = $temp1;
                $temp['zongzhang_kemu'] = empty($this->getPianqu($arr['dname'])) ? $arr['dname'] : $this->getPianqu($arr['dname']);//总账科目---当前部门所属片区
                $temp['yiji_kemu'] = empty($this->getPianqu($arr['dname'])) ? $arr['dname'] : $this->getPianqu($arr['dname']);//当前部门所属片区
                $temp['jiefang'] = $arr['income'] * 0.4;//40%的金额转给片区
                $temp['daifang'] = '';
                $temp['zhaiyao'] = $temp1['zhaiyao'];//摘要=一级明细+二级明细+业务类别
                array_push($result, $temp);
                $temp_dept = array();
                $temp_dept = $temp1;
                $temp_dept['zongzhang_kemu'] = empty($this->getPianqu($arr['dname'])) ? $arr['dname'] : $this->getPianqu($arr['dname']);//总账科目---当前所属部门
                $temp_dept['yiji_kemu'] = $arr['dname'];//根据部门所得的所属片区，根据二三级附表数据得来
                $temp_dept['jiefang'] = $arr['income'] * 0.6;//40%的金额转给片区
                $temp_dept['daifang'] = '';
                $temp_dept['zhaiyao'] = $arr['yijimx_name'] . $arr['erjimx_name'] . $arr['yw_type_name'] . '元';//内部往来的摘要
                array_push($result, $temp_dept);
            } elseif ($arr['subject_name'] == '主营业务收入') {//主营业务收入
                $temp = array();
                $temp = $temp1;
                $temp['zongzhang_kemu'] = $arr['subject_name'];//总账科目
                $temp['yiji_kemu'] = $arr['yw_type_name'];//一级科目
                $temp['jiefang'] = '';
                $temp['daifang'] = $arr['income'];
                array_push($result, $temp);
            } elseif ($arr['yijimx_name'] == '报废收入') {//报废收入
                $temp = array();
                $temp = $temp1;
                $temp['zongzhang_kemu'] = '管理费用';//总账科目
                $temp['yiji_kemu'] = $arr['subject_name'];//一级科目
                $temp['jiefang'] = -$arr['income'];//报废收入的借方=收入的相反数
                $temp['daifang'] = '';
                $temp['zhaiyao'] = '收' . $arr['remark'] . $arr['yijimx_name'] . $arr['income'] . '元';
                array_push($result, $temp);
            } else {//其他收入
                $temp = array();
                $temp = $temp1;
                $temp['zongzhang_kemu'] = $arr['yijimx_name'];//总账科目
                $temp['yiji_kemu'] = $arr['subject_name'];//一级科目
                $temp['jiefang'] = '';
                $temp['daifang'] = $arr['income'];
                if ($arr['erjimx_name'] == '现金盘盈') {
                    $temp['zhaiyao'] = $arr['erjimx_name'] . $arr['income'] . '元';
                } elseif ($arr['erjimx_name'] == '预收账核销收入') {
                    $temp['zhaiyao'] = $arr['remark'] . $arr['erjimx_name'] . $arr['income'] . '元';
                } else {
                    $temp['zhaiyao'] = '收' . $arr['remark'] . $arr['yw_type_name'] . $arr['erjimx_name'] . $arr['income'] . '元';
                }
                array_push($result, $temp);
            }
        } elseif ($arr['xmlb'] == '损益类现金支出') {//$val['yijimx_name'] == '经营费用' or '车辆费用',注意车辆费用显示数据不同于经营费用
            $temp = array();
            $temp = $temp1;
            $temp['zongzhang_kemu'] = ($arr['yijimx_name'] == '经营费用') ? '营业费用' : '其他业务支出';//总账科目
            $temp['yiji_kemu'] = ($arr['yijimx_name'] == '经营费用') ? $arr['erjimx_name'] : $arr['yijimx_name'];//一级科目
            $temp['jiefang'] = $arr['spending'];
            $temp['daifang'] = '';
            $temp['zhaiyao'] = $this->dateConvert($arr['date']) . '发生' . $arr['erjimx_name'] . $arr['spending'] . '元';//摘要=一级明细+二级明细+业务类别
            array_push($result, $temp);
        } elseif ($arr['xmlb'] == '资产类现金收入') {
            if ($arr['subject_name'] == '内部往来') {
                $temp = array();
                $temp = $temp1;
                $temp['zongzhang_kemu'] = $this->getPianqu($arr['dname']);//总账科目
                $temp['yiji_kemu'] = $arr['dname'];//一级科目
                $temp['jiefang'] = '';
                $temp['daifang'] = $arr['income'];
                $temp['zhaiyao'] = $arr['erjimx_name'] . $arr['remark'] . $arr['income'] . '元';//摘要=一级明细+二级明细+业务类别
                array_push($result, $temp);
            } else {
                $temp = array();
                $temp = $temp1;
                $temp['zongzhang_kemu'] = $arr['subject_name'];//总账科目
                $temp['yiji_kemu'] = $arr['erjimx_name'];//一级科目
                $temp['jiefang'] = '';
                $temp['daifang'] = $arr['income'];
                $temp['zhaiyao'] = $arr['erjimx_name'] . $arr['remark'] . $arr['income'] . '元';//摘要=一级明细+二级明细+业务类别
                array_push($result, $temp);
            }
        } elseif ($arr['xmlb'] == '资产类现金支出') {

            $temp = array();
            $temp = $temp1;
            $temp['zongzhang_kemu'] = ($arr['yijimx_name'] == '经营费用') ? '营业费用' : '其他业务支出';//总账科目
            $temp['yiji_kemu'] = ($arr['yijimx_name'] == '经营费用') ? $arr['erjimx_name'] : $arr['yijimx_name'];//一级科目
            $temp['jiefang'] = $arr['spending'];
            $temp['daifang'] = '';
            $temp['zhaiyao'] = $this->dateConvert($arr['date']) . '发生' . $arr['erjimx_name'] . $arr['spending'] . '元';//摘要=一级明细+二级明细+业务类别
            array_push($result, $temp);
        }
        return $result;
    }

    public function dateConvert($date)
    {
        $nian = date('Y', strtotime($date));
        $yue = date('m', strtotime($date));
        $day = date('d', strtotime($date));
        return $nian . '年' . $yue . '月' . $day . '日';
    }

    public function getPianqu($dname)
    {
        $data = $this->pingzhengModel->getPianqu($dname);
        return $data;
    }


    public function searchProduct($token, $sdate, $edate)
    {
        $modelPz = Loader::model('Pingzheng', 'model');
        $res = $modelPz->searchProduct($token, $sdate, $edate, $url = '');
        return $res;
    }

    public function createPingZheng($token, $sdate, $edate)
    {
        $modelPz = Loader::model('Pingzheng', 'model');
        $res = $modelPz->createPingZheng($token, $sdate, $edate);
        if (!empty($res)) {
            return retmsg(0);
        }
        return retmsg(-1);
    }

    //
    public function doorPingZheng($token, $sdate, $edate)
    {
        $modelPz = Loader::model('Pingzheng', 'model');
        $res = $modelPz->doorPingZheng($token, $sdate, $edate);

        if (!empty($res)) {
            $insert_data = $this->handleDoorData($res, 1);

            $insert_res = Loader::model('Pingzheng', 'model')->createDoorPingZheng($insert_data, $edate, 1);
            return retmsg(0);
        }
        return retmsg(-1);
    }

    public function doorInvalidPz($token, $sdate, $edate)
    {
        $modelPz = Loader::model('Pingzheng', 'model');
        $url = "http://xsrb.wsy.me:801/saleStockSystem/index.php/admin/SPZDZD/search/type/0/sdate/$sdate/edate/$edate/token/$token";
        $res = $modelPz->doorPingZheng($token, $sdate, $edate, $url);

        if (!empty($res)) {
            $insert_data = $this->handleDoorData($res, 0);
            $insert_res = Loader::model('Pingzheng', 'model')->createDoorPingZheng($insert_data, $edate, 0);
            return retmsg(0);
        }
        return retmsg(-1);
    }

    /**处理门和门配接口返回的数据,过滤为为0的数据，返回需要保存的格式
     * @param $data
     * @param $type
     * @return array
     */
    public function handleDoorData($data, $type)
    {
        $xmlb = ($type == 1) ? '有效商品收入' : '无效商品收入';
        $xmlb_zc = ($type == 1) ? '有效商品支出' : '无效商品支出';
        $yewu_type = '防盗门';
        //用于存放商品收入或支出
        $result1 = array();    //收入
        $result2 = array();    //支出

        $banshichu_1 = [];    //存放收入的办事处
        $banshichu_2 = [];      //存放支出的办事处

        $index1 = 0;
        $index2 = 0;
        foreach ($data[0] as $key => $value) {
            if ($data[0][$key]['initem'] == '送货收回') {
                $index1 = $key;
            }
        }
        foreach ($data[1] as $key => $value) {
            if ($data[1][$key]['outitem'] == '送货支出') {
                $index2 = $key;
            }
        }
        if ($index1 - 1 > 1) {
            for ($i = 2; $i < $index1; $i++) {
                array_push($banshichu_1, array('banshichu' => $data[0][$i]['initem'], 'money' => $data[0][$i]['inmoney']));
            }
        } else {
            $banshichu_1 = '';
        }

        if ($index2 - 1 > 1) {
            for ($i = 2; $i < $index1; $i++) {
                array_push($banshichu_2, array('banshichu' => $data[1][$i]['outitem'], 'money' => $data[1][$i]['outmoney']));
            }
        } else {
            $banshichu_2 = '';
        }

        $model = Loader::model('Pingzheng', 'model');
        $xm_fenlei = $model->getProjectFenlei($xmlb, $type);//项目的具体分类
        foreach ($data[0] as $key => $val) {
            if (in_array($val['initem'], $xm_fenlei) && $val['inmoney'] != 0) {
                $temp = array();
                $temp['dept_name'] = $val['dept_name'];
                $temp['xmlb'] = $xmlb;
                $temp['yijimx_name'] = $val['initem'];
                $temp['income'] = $val['inmoney'];
                $temp['yw_type_name'] = $yewu_type;
                $temp['banshichu'] = ($val['initem'] == '本月调入') ? $banshichu_1 : '';
                array_push($result1, $temp);
            }
        }
        $xm_fenlei_2 = $model->getProjectFenlei($xmlb_zc, $type);


        foreach ($data[1] as $key => $val) {
            if (in_array($val['outitem'], $xm_fenlei_2) && $val['outmoney'] != 0) {
                $temp1 = array();
                $temp1['dept_name'] = $val['dept_name'];
                $temp1['xmlb'] = $xmlb_zc;
                $temp1['yijimx_name'] = $val['outitem'];
                $temp1['outcome'] = $val['outmoney'];
                $temp1['yw_type_name'] = $yewu_type;
                $temp1['banshichu'] = ($val['outitem'] == '本月调出') ? $banshichu_2 : '';
                array_push($result2, $temp1);
            }
        }
        $result = [$result1, $result2];
        return $result;
    }
}