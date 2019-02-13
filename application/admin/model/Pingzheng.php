<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16
 * Time: 19:35
 */
namespace app\admin\model;

use think\Db;
use think\Loader;

class Pingzheng
{

    public function getPingzheng($sdate, $edate, $depts)
    {
        $sql = "SELECT
	temp2.dept_id,
	e.dname,
	temp2.date,
	temp2.xmlb,
	temp2.yijimx_name,
	if((temp2.erjimx REGEXP '[^0-9.]')=1,temp2.erjimx,d.name) as erjimx_name,
	temp2.income,
	temp2.spending,
	temp2.balance,
	temp2.yw_type_name,
	temp2.remark
FROM
	(
		SELECT
			temp.*, c. NAME AS yijimx_name
		FROM
			(
				SELECT
					a.dept_id,
					a.date,
					a.xm_type,
					b.`name` AS xmlb,
					a.yijimx,
					a.erjimx,
					a.yw_type,
					a.income,
					a.spending,
					a.balance,
					a.remark,
					c.yw_type_name
				FROM
					xjrjz_rijizhang a,
					xjrjz_xm_type b,
					xjrjz_yw_type c
				WHERE
					a.xm_type = b.id
				AND a.yw_type = c.id
				AND a.date BETWEEN '2019-01-01'
				AND '2019-01-16'
				AND a.xm_type IS NOT NULL
				AND a.yijimx IS NOT NULL
				AND a.yw_type IS NOT NULL
				AND a.xm_type != 3
and dept_id in (71,1037)
			) temp,
			xjrjz_xm_type c
		WHERE
			temp.yijimx = c.id
	) temp2
LEFT JOIN xjrjz_xm_type d ON temp2.erjimx = d.id,
 xsrb_department e
WHERE
	temp2.dept_id = e.id";
        $ret = Db::query($sql);
        # 数据重组
        if (!empty($ret)) {
            foreach ($ret as $key => $val) {
                $xmlb = $val['xmlb'];//项目类别
                $xm_yjmx = $val['yijimx_name'];//一级明细
                $xm_ejmx = $val['erjimx_name'];//二级明细
                # 销售日报现金账的费用类现金支出===月报的损益类现金支出
                if ($xmlb == '费用类现金支出') {
                    $xmlb = '损益类现金支出';
                }
                # 根据项目类别和一级明细及二级明细为空查询到的数据，给二级明细重置为空数据
                $xmData = $this->getCashXmid($xmlb, $xm_yjmx,'');
                if (!empty($xmData)) {
                    # 重置二级明细为空
                    $ret[$key]['erjimx_name'] = '';
                }
                $ret[$key]['xmlb'] = $xmlb;
            }
        }
        return empty($ret)?[]:$ret;
    }

    /**
     * 获取现金账项目类别一二级明细及其对应的总账科目关系
     */
    public function getCashXmid($xmlb,$xm_yjmx,$xm_ejmx)
    {
        $sql = "select id as xm_id from certificate_cash_xm where xmlb='$xmlb' and xm_yjmx='$xm_yjmx' and xm_ejmx='$xm_ejmx'";
        $sql = "select a.id as xm_id,a.xmlb,a.xm_yjmx,a.xm_ejmx,b.id as relation_id,c.subject_code,c.subject_name
 from certificate_cash_xm a,certificate_relation b,certificate_subject c 
where a.id=b.xm_id and c.subject_code=b.subject_code and a.xmlb='$xmlb' and a.xm_yjmx='$xm_yjmx' and a.xm_ejmx='$xm_ejmx'";
        $ret = Db::query($sql);
        return empty($ret)?[]:$ret[0];
    }

    public function getSubjectCode($xm_id, $type)
    {
        $sql = "select * from certificate_relation where xm_id='$xm_id' and type=1";
    }

    public function getPianqu($dname)
    {
        $year = date('Y');
        $month = date('m');
//        $sql = "select id,dname from xsrb_department where id=(select pid as id from xsrb_department where id=$dept_id)";
        $sql = "select dept_second as dname from certificate_dept where `year`='$year' and `month`='$month' and dept_third='$dname'";
        $ret = Db::query($sql);
        return empty($ret)?'':$ret[0]['dname'];
    }

    public function insertPingzheng($data)
    {
        foreach ($data as $key => $val) {
            $type = $val['type'];
            $dept_id = $val['dept_id'];
            $date = $val['date'];
            $zongzhang_kemu = $val['zongzhang_kemu'];
            $yiji_kemu = $val['yiji_kemu'];
            $erji_kemu = $val['erji_kemu'];
            $yewu_type = $val['yewu_type'];
            /*$exist = "select * from certificate_list where type='$type' and dept_id='$dept_id' and date='$date' and zongzhang_kemu='$zongzhang_kemu'
and yiji_kemu='$yiji_kemu' and erji_kemu='$erji_kemu' and yewu_type='$yewu_type'";
            $exist_data = Db::query($exist);
            if (!empty($exist_data)) {//存在即更新
                $qichu_yue = empty($val['qichu_yue'])?0:$val['qichu_yue'];
                $qimo_yue = empty($val['qimo_yue'])?0:$val['qimo_yue'];
                $jiefang = empty($val['jiefang'])?0:$val['jiefang'];
                $daifang = empty($val['daifang'])?0:$val['daifang'];
                Db::execute("update certificate_list set qichu_yue='$qichu_yue',qimo_yue='$qimo_yue',jiefang='$jiefang',daifang='$daifang' where id=".$exist_data[0]['id']);
            } else {//不存在则新增
                Db::table('certificate_list')->insertGetId($val);
            }*/
            Db::table('certificate_list')->insertGetId($val);
        }
        return true;
    }

    // --------------------------------商品类凭证 start-----------------------------------------
    public function createPingZheng($token,$sdate,$edate){
        $c_data = Loader::model('Pingzheng','controller');
        $arr_data = $c_data->searchProduct($token,$sdate, $edate);
        $data = $this->reCombineData($arr_data,1);
        if(!empty($data)){
            foreach ($data as   $key => $value ){
                foreach ($data[$key] as $k => $v){
                    $sunyi_value =['调价升值' => '升值',
                        '盘点升溢' => '盘盈',
                        '调价降值' => '降值',
                        '盘点短缺' => '盘亏',
                        '其他业务支出' =>'换货支出'
                        ];

                    $xm_id = Db::table('certificate_product_xm')
                        ->where('xmlb',$data[$key][$k]['xmlb'])
                        ->where('xm_fenlei',$data[$key][$k]['yijimx_name'])
                        ->value('id');

                    $sub_code = Db::table('certificate_relation')
                        ->where('xm_id',$xm_id)
                        ->where('type',2)
                        ->where('product_type',1)
                        ->value('subject_code');
                    //处理错误信息
                    if(empty($sub_code)){
                        $this->handleErrPz($data[$key][$k],1);
                    } else {   //  处理产生凭证
                        $sub_name = Db::table('certificate_subject')
                            ->where('subject_code',$sub_code)
                            ->value('subject_name');

                        $yijikemu = Db::table('certificate_dept')
                            ->where('dept_third',$data[$key][$k]['banshichu'])
                            ->value('dept_second');
                        $dept_id = Db::table('xsrb_department')
                            ->field('id')
                            ->where('dname',$data[$key][$k]['dept_name'])
                            ->value('id');

                        //组合数据
                        if($key == 0){
                            $zhaiyao = [
                                '内部往来'=>'其他部门调入',
                                '待处理财产损益'=>'有效商品'.$sunyi_value[$data[$key][$k]['yijimx_name']],
                                '发出商品' =>  '本月送货支出',
                                '管理费用' =>  '本月商品报废支出',
                                '库存商品(有效)' =>  '',
                                '库存商品(暂借)' =>'给客户铺货商品',
                                '其他业务支出' =>'商品换货收支',
                                '应付账款' =>'外购商品入库',
                                '暂借商品' =>'收客户暂存商品',
                                '主营业务成本' =>'销售成本',
                                '其他业务支出' =>'商品换货收支',
                                '管理费用' => '本月报废商品支出',
                            ];

                            $save_data_1 =[
                                "dept_id" =>$dept_id,
                                "dname" =>$data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate ,
                                "zongzhang_kemu" => '库存商品有效',
                                "yiji_kemu" =>'',
                                "erji_kemu" =>'',
                                "qichu_yue"=>0,
                                "qimo_yue" =>0,
                                "jiefang" =>$data[$key][$k]['income'],
                                "daifang" =>0,
                                "zhaiyao"  =>$zhaiyao[$sub_name],
                                "yewu_type" =>'数码',
                                "create_by" =>'系统',
                                "create_time" =>date('Y-m-d H:i:s')
                            ];

                            $save_data_2 =[
                                "dept_id" =>$dept_id,
                                "dname" =>$data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate ,
                                "zongzhang_kemu" => $sub_name,
                                "yiji_kemu" =>$yijikemu,
                                "erji_kemu" =>$data[$key][$k]['banshichu'],
                                "jiefang" => 0,
                                "daifang" =>$data[$key][$k]['income'],
                                "qichu_yue"=>0,
                                "qimo_yue" =>0,
                                "zhaiyao"  =>$zhaiyao[$sub_name] ,
                                "yewu_type" =>'数码',
                                "create_by" =>'系统',
                                "create_time" =>date('Y-m-d H:i:s')
                            ];
                            //处理科目代码大于4位
                            if(strlen($sub_code)>4){
                                $sub_code_has = substr($sub_code,0,4);
                                $sub_name_has = Db::table('certificate_subject')
                                    ->where('subject_code',$sub_code_has)
                                    ->value('subject_name');
                                $save_data_2["zongzhang_kemu"] = $sub_name_has;
                                $save_data_2["yiji_kemu"] =$sub_name;
                                $save_data_1['zhaiyao'] =$zhaiyao[$sub_name_has];
                                $save_data_2['zhaiyao'] =$zhaiyao[$sub_name_has];
                            }
                        } else {  //处理商品支出
                            $zhaiyao = [
                                '内部往来'=>'商品调出',
                                '待处理财产损益'=>'有效商品'.$sunyi_value[$data[$key][$k]['yijimx_name']],
                                '发出商品' =>  '本月送货支出',
                                '管理费用' =>  '本月商品报废支出',
                                '库存商品(有效)' =>  '',
                                '库存商品(暂借)' =>'给客户铺货商品',
                                '其他业务支出' =>'商品换货收支',
                                '应付账款' =>'外购商品出库',
                                '暂借商品' =>'支客户暂存商品',
                                '主营业务成本' =>'销售成本',
                            ];
                            $save_data_1 =[
                                "dept_id" =>$dept_id,
                                "dname" =>$data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate ,
                                "zongzhang_kemu" => '库存商品有效',
                                "yiji_kemu" =>'',
                                "erji_kemu" =>'',
                                "qichu_yue"=>0,
                                "qimo_yue" =>0,
                                "jiefang" =>$data[$key][$k]['outcome'],
                                "daifang" =>0,
                                "zhaiyao"  =>$zhaiyao[$sub_name],
                                "yewu_type" =>'数码',
                                "create_by" =>'系统',
                                "create_time" =>date('Y-m-d H:i:s')
                            ];
                            $save_data_2 =[
                                "dept_id" =>$dept_id,
                                "dname" =>$data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate ,
                                "zongzhang_kemu" => $sub_name,
                                "yiji_kemu" =>$yijikemu,
                                "erji_kemu" =>$data[$key][$k]['banshichu'],
                                "jiefang" => 0,
                                "daifang" =>$data[$key][$k]['outcome'],
                                "qichu_yue"=>0,
                                "qimo_yue" =>0,
                                "zhaiyao"  =>$zhaiyao[$sub_name],
                                "yewu_type" =>'数码',
                                "create_by" =>'系统',
                                "create_time" =>date('Y-m-d H:i:s')
                            ];

                            //处理科目代码大于4位
                            if(strlen($sub_code)>4){
                                $sub_code_has = substr($sub_code,0,4);
                                $sub_name_has = Db::table('certificate_subject')
                                    ->where('subject_code',$sub_code_has)
                                    ->value('subject_name');
                                $save_data_2["zongzhang_kemu"] = $sub_name_has;
                                $save_data_2["yiji_kemu"] =$sub_name;
                                $save_data_1['zhaiyao'] =$zhaiyao[$sub_name_has];
                                $save_data_2['zhaiyao'] =$zhaiyao[$sub_name_has];

                            }
                        }
                        Db::table('certificate_list')
                            ->insert($save_data_1);
                        Db::table('certificate_list')
                            ->insert($save_data_2);
                    }
                    }
            }
            return 1;
        }
       return '';
    }

    /**
     * 处理商品对账单
     * @param $sdate
     * @param $edate
     * @param string $depts
     */
    public function searchProduct($token,$sdate,$edate,$url='')
    {
        $url = empty($url)?"http://xsrb.wsy.me:801/saleStockSystem/index.php/qt/SPZDZD/search/type/1/token/$token/sdate/$sdate/edate/$edate":$url;

        $data = json_decode(curl_get($url,10),true);
        $list = $data['data'];
        $head_name = $data['header'][0]['children'][0]['headerName'];
        $head_name = substr($head_name,strpos($head_name,":")+1);
        $in_items = [];
        $out_items = [];
        for($i=0;   $i<count($list);    $i++){
            foreach ($list[$i] as $key=>$value){
                if($key == "initem" || $key == "inmoney"){
                   $in_items[$i] = [
                       "dept_name" => $head_name,
                        "initem"=>$list[$i]["initem"],
                        "inmoney"=>$list[$i]["inmoney"]
                       ];
                }elseif ($key == "outitem" || $key == "outmoney"){
                    $out_items[$i] =[
                        "dept_name" => $head_name,
                        "outitem" =>$list[$i]["outitem"],
                        "outmoney" =>$list[$i]["outmoney"]
                    ];
                }
            }
        }
        $handleData = [$in_items,$out_items];
        return $handleData;

    }

    public function reCombineData($data,$type)
    {
        $xmlb = ($type==1)?'有效商品收入':'无效商品收入';
        $xmlb_zc = ($type==1)?'有效商品支出':'无效商品支出';
        $yewu_type = '数码';
        //用于存放商品收入或支出
        $result1 = array();
        $shangping_data = $data[0];

        $banshichu_1 = '';
        $banshichu_2 = [];

        foreach ($data[0] as $key=>$value){
          $has = Db::table('certificate_dept')->where('dept_third',$value['initem'])->find();
             if(!empty($has)){
                 $banshichu_1 = $value['initem'];
             }
      }
        foreach ($data[1] as $key=>$value){
            $has = Db::table('certificate_dept')->where('dept_third',$value['initem'])->find();
            if(!empty($has)){
                $banshichu_2 = $value['initem'];
            }
        }
        $xm_fenlei = $this->getProjectFenlei($xmlb,$type);//项目的具体分类
        foreach ($shangping_data as $key => $val) {
                if (in_array($val['initem'], $xm_fenlei) && $val['inmoney'] != 0) {
                    $temp = array();
                    $temp['dept_name'] =  $val['dept_name'];
                    $temp['xmlb'] = $xmlb;
                    $temp['yijimx_name'] = $val['initem'];
                    $temp['income'] = $val['inmoney'];
                    $temp['yw_type_name'] = $yewu_type;
                    $temp['banshichu'] =($val['initem'] == '本月调入')?$banshichu_1:'';
                    array_push($result1, $temp);
                }
            }
         $result2 = [];
        $xm_fenlei_2 = $this->getProjectFenlei($xmlb_zc,$type);

        foreach ($data[1] as $key => $val) {
            if (in_array($val['outitem'], $xm_fenlei_2) && $val['outmoney'] != 0) {
                $temp1 = array();
                $temp1['dept_name'] =  $val['dept_name'];
                $temp1['xmlb'] = $xmlb_zc;
                $temp1['yijimx_name'] = $val['outitem'];
                $temp1['outcome'] = $val['outmoney'];
                $temp1['yw_type_name'] = $yewu_type;
                $temp1['banshichu'] =$temp['banshichu'] =($val['initem'] == '本月调出')?$banshichu_2:'';
                array_push($result2, $temp1);
            }
        }
        $result = [$result1,$result2];
        return $result;
    }

    //获取项目分类
    public function getProjectFenlei($xmlb,$product_type)
    {
        $sql = "select xm_fenlei from certificate_product_xm where xmlb='$xmlb' and type=$product_type";
        $ret = Db::query($sql);
        $arr = array();
        foreach ($ret as $k => $v) {
            array_push($arr, $v['xm_fenlei']);
        }
        return $arr;
    }

    //处理商品类无效凭证
    public function createInvalidPz($token,$sdate, $edate,$type)
    {
        $url = "http://xsrb.wsy.me:801/saleStockSystem/index.php/qt/SPZDZD/search/type/0/token/$token/sdate/$sdate/edate/$edate";
        $arr_data = $this->searchProduct($sdate, $edate, $url);
        $data = $this->reCombineData($arr_data, $type);

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                foreach ($data[$key] as $k => $v) {
                    $sunyi_value =['调价升值' => '升值',
                        '盘点升溢' => '盘盈',
                        '调价降值' => '降值',
                        '盘点短缺' => '盘亏',
                        '其他业务收入' =>'换货收入'
                    ];

                    $xm_id = Db::table('certificate_product_xm')
                        ->where('xmlb', $data[$key][$k]['xmlb'])
                        ->where('xm_fenlei', $data[$key][$k]['yijimx_name'])
                        ->value('id');
                    $sub_code = Db::table('certificate_relation')
                        ->where('xm_id', $xm_id)
                        ->where('type', 2)
                        ->where('product_type', 0)
                        ->value('subject_code');
                    //处理错误信息
                    if(empty($sub_code)){
                        $this->handleErrPz($data[$key][$k],0);
                    }else{
                        $sub_name = Db::table('certificate_subject')
                            ->where('subject_code', $sub_code)
                            ->value('subject_name');
                        $yijikemu = Db::table('certificate_dept')
                            ->where('dept_third', $data[$key][$k]['banshichu'])
                            ->value('dept_second');

                        $dept_id = Db::table('xsrb_department')
                            ->field('id')
                            ->where('dname', $data[$key][$k]['dept_name'])
                            ->value('id');

                        //组合数据
                        if ($key == 0) {
                            $zhaiyao = [
                                '内部往来'=>'本月无效商品调入',
                                '待处理财产损益'=>'无效商品'.$sunyi_value[$data[$key][$k]['yijimx_name']],
                                '发出商品' =>  '无效商品送货收回',
                                '管理费用' =>  '无效商品报废支出',
                                '库存商品(无效)' =>  '无效商品送货收回',
                                '库存商品(暂借)' =>'给客户铺货商品',
                                '其他业务收入' =>'无效商品换货收支',
                                '应付账款' =>'外购商品入库',
                                '暂借商品' =>'收客户无效暂存商品',
                                '主营业务成本' =>'销售成本',
                            ];
                            $save_data_1 = [
                                "dept_id" => $dept_id,
                                "dname" => $data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate,
                                "zongzhang_kemu" => '库存商品无效',
                                "yiji_kemu" => '',
                                "erji_kemu" => '',
                                "qichu_yue" => 0,
                                "qimo_yue" => 0,
                                "jiefang" => $data[$key][$k]['income'],
                                "daifang" => 0,
                                "zhaiyao" =>$zhaiyao[$sub_name],
                                "yewu_type" => '数码',
                                "create_by" => '系统',
                                "create_time" => date('Y-m-d H:i:s')
                            ];
                            $save_data_2 = [
                                "dept_id" => $dept_id,
                                "dname" => $data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate,
                                "zongzhang_kemu" => $sub_name,
                                "yiji_kemu" => $yijikemu,
                                "erji_kemu" => $data[$key][$k]['banshichu'],
                                "jiefang" => 0,
                                "daifang" => $data[$key][$k]['income'],
                                "qichu_yue" => 0,
                                "qimo_yue" => 0,
                                "zhaiyao" => $zhaiyao[$sub_name],
                                "yewu_type" => '数码',
                                "create_by" => '系统',
                                "create_time" => date('Y-m-d H:i:s')
                            ];

                            //处理科目代码大于4位
                            if(strlen($sub_code)>4){
                                $sub_code_has = substr($sub_code,0,4);
                                $sub_name_has = Db::table('certificate_subject')
                                    ->where('subject_code',$sub_code_has)
                                    ->value('subject_name');
                                $save_data_2["zongzhang_kemu"] = $sub_name_has;
                                $save_data_2["yiji_kemu"] =$sub_name;
                                $save_data_1['zhaiyao'] =$zhaiyao[$sub_name_has];
                                $save_data_2['zhaiyao'] =$zhaiyao[$sub_name_has];
                            }

                        } else {
                            $zhaiyao = [
                                '内部往来'=>'无效商品调出',
                                '待处理财产损益'=>'无效商品'.$sunyi_value[$data[$key][$k]['yijimx_name']],
                                '发出商品' =>  '无效商品送货支出',
                                '管理费用' =>  '无效商品报废支出',
                                '库存商品(无效)' => '无效商品送货收回',
                                '库存商品(暂借)' =>'给客户铺货商品',
                                '其他业务支出' =>'商品换货收支',
                                '应付账款' =>'外购商品入库',
                                '暂借商品' =>'支客户无效暂存商品',
                                '主营业务成本' =>'销售成本',
                            ];
                            $save_data_1 = [
                                "dept_id" => $dept_id,
                                "dname" => $data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate,
                                "zongzhang_kemu" => '库存商品无效',
                                "yiji_kemu" => '',
                                "erji_kemu" => '',
                                "qichu_yue" => 0,
                                "qimo_yue" => 0,
                                "jiefang" => $data[$key][$k]['outcome'],
                                "daifang" => 0,
                                "zhaiyao" =>$zhaiyao[$sub_name],
                                "yewu_type" => '数码',
                                "create_by" => '系统',
                                "create_time" => date('Y-m-d H:i:s')
                            ];
                            $save_data_2 = [
                                "dept_id" => $dept_id,
                                "dname" => $data[$key][$k]['dept_name'],
                                "type" => 2,
                                "date" => $edate,
                                "zongzhang_kemu" => $sub_name,
                                "yiji_kemu" => $yijikemu,
                                "erji_kemu" => $data[$key][$k]['banshichu'],
                                "jiefang" => 0,
                                "daifang" => $data[$key][$k]['outcome'],
                                "qichu_yue" => 0,
                                "qimo_yue" => 0,
                                "zhaiyao" =>$zhaiyao[$sub_name],
                                "yewu_type" => '数码',
                                "create_by" => '系统',
                                "create_time" => date('Y-m-d H:i:s')
                            ];
                            //处理科目代码大于4位
                            if(strlen($sub_code)>4){
                                $sub_code_has = substr($sub_code,0,4);
                                $sub_name_has = Db::table('certificate_subject')
                                    ->where('subject_code',$sub_code_has)
                                    ->value('subject_name');
                                $save_data_2["zongzhang_kemu"] = $sub_name_has;
                                $save_data_2["yiji_kemu"] =$sub_name;
                                $save_data_1['zhaiyao'] =$zhaiyao[$sub_name_has];
                                $save_data_2['zhaiyao'] =$zhaiyao[$sub_name_has];
                            }
                        }
                        Db::table('certificate_list')
                            ->insert($save_data_1);
                        Db::table('certificate_list')
                            ->insert($save_data_2);
                    }
                    }
            }
            return 1;
        }
        return '';
    }
    // --------------------------------商品类凭证 end-------------------------------------------

    //--------------处理商品类凭证错误的信息start--------------
    public function handleErrPz($data,$type){

       $dept_id = Db::table('xsrb_department')
           ->where('dname',$data['dept_name'])
           ->value('id');
       $add_data = [
           'type' => 2,
           'dept_id' => $dept_id,
           'dname' => $data['dept_name'],
           'xmlb' =>   $data['xmlb'],
           'yijimx_name' => $data['yijimx_name'],
           'erjimx_name' => '',
           'yw_type_name' => $data['yw_type_name'],
           'income' => $data['income'],
           'spending' => $data['outcome'],
           'remark' => $data['xmlb'].'和'. $data['yijimx_name'].'未匹配到相应科目信息',
           'date' => date('Y-m-d',time()),
           'balance' => ''
       ];
       Db::table('certificate_error_log')
            ->insert($add_data);
    }
    //--------------处理商品类凭证错误的信息end----------------
}