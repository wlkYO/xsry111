<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/2
 * Time: 16:08
 */

namespace app\api\service;
use think\Db;
use think\Controller;

class ExcelCsvImportService
{
    /**
     * 文件上传路径
     * @var
     */
    private static $uploadPath;

    private static $PHPExcel;
    /**
     * 二维数组添加新键值
     * @param $column
     * @param $value
     * @param $data
     * @return mixed
     */
    public function addNewColumn($column,$value,$data){
        foreach ($data as $key=>$val){
            $data[$key][$column] = $value;
        }
        return $data;
    }

    /**
     * 检查订单日报数据
     * @param $data
     * @return array|mixed
     */
    public function checkDingdanrbConfirm($data,$option){
        foreach ($option['columnMap'] as $key=>$val){
            $titles[] = $key;
        }
        $tableName = 'PLAN_DINGDANRB';
        foreach($data as $key=>$val){
            $i= 0;
            $info_mark = 0;
            foreach ($val as $keys=>$value){
                $res = empty($value)?0:$value;
                //工单号可为空 ,订单号,项次可为空
                if(in_array($titles[$i],array('工单号','订单号','项次'))){
                    $res = empty($res)?'':$res;
                }
                //日期,订单号,项次不能为空
                $hang = $key+$option['firstLine'];
                if (empty($val['dingdanh']) && empty($val['gongdanh'])){
                    return json_encode(array('resultcode' => -1, 'resultmsg' => "工单号与订单号不能同时为空"));
                }
                if (in_array($titles[$i],array('预计开工日期','总装日期')) && empty($res)){
                    $str = "第 $hang 数据行中,".$titles[$i]."数据格式为空,不能导入!";
                    return json_encode(array('resultcode' => -1, 'resultmsg' => $str));
                }
                if (!is_numeric($res) && in_array($titles[$i],array('前期未交量','订单变更','总装计划','项次','发货计划','未交量调整','上期结存'))){
                    $str = "第 $hang 数据行中,".$titles[$i]."数据为非数值型,不能导入!";
                    return json_encode(array('resultcode' => -1, 'resultmsg' => $str));
                }
                $data[$key][$keys] = $res;
                $i++;
            }
            //线下工单号所在行归纳
            if(empty($val['dingdanh']) && !empty($val['gongdanh'])){
                $hangs[] = $hang;
                $info_mark = 1;
            }
            $dingdanh = $val['dingdanh'];
            $xiangci = $val['xiangci'];
            $gongdanh = $val['gongdanh'];
            $sql = "select * from $tableName where dingdanh='$dingdanh' and xiangci='$xiangci' and gongdanh='$gongdanh'";
            $result = Db::query($sql);
            $data[$key]['id'] = empty($result[0]['ID'])?'':$result[0]['ID'];
            $data[$key]['info_mark'] = $info_mark;
        }
        //处理线下工单号
        if (count($hangs)){
            $result = self::doXianxiaGongdan($hangs);
        }
        return $data;
    }

    public function checkHistortDingdanrbConfirm($data,$option,$zhizaobm,$type=''){
        foreach ($option['columnMap'] as $key=>$val){
            $titles[] = $key;
        }
        $tableName = 'PLAN_DINGDANRB';
        foreach($data as $key=>$val){
            $i= 0;
//            $hang = $key+$option['firstLine'];
//            $info_mark = $zhizaobm.date('YmdHis').$hang;
            foreach ($val as $keys=>$value){
                $res = empty($value)?0:$value;
                //工单号可为空 ,订单号,项次可为空
                if(in_array($titles[$i],array('工单号','订单号','项次'))){
                    $res = empty($res)?'':$res;
                }
                $data[$key][$keys] = $res;
                $i++;
            }
            $dingdanh = $val['dingdanh'];
            $xiangci = $val['xiangci'];
            $gongdanh = $val['gongdanh'];
//            $result = Db::query("select oeb01,oeb03,oeb_lot,OPERATE_PLAN from OEB_FILE where (OEB01='$dingdanh' and  OEB03='$xiangci') or OEB_LOT='$gongdanh'");
            $data[$key]['zhizaobm'] = $zhizaobm;
//            if (count($result) == 1){
//                $dingdanh = $result[0]['OEB01'];
//                $xiangci = $result[0]['OEB03'];
//                $gongdanh = $result[0]['OEB_LOT'];
//            }
            //线下工单号所在行归纳
//            if(empty($dingdanh) && !empty($gongdanh)){
//                $sql = "select ID from $tableName where  gongdanh='$gongdanh'";
//                $result = Db::query($sql);
//                $data[$key]['id'] = empty($result[0]['ID'])?'':$result[0]['ID'];
//                if (!count($result)){
//                    $hangs[$hang] = $info_mark;
//                    $data[$key]['info_mark'] = $info_mark;
//                }
//            }elseif(empty($dingdanh) && empty($gongdanh)){
//                $hangs[$hang] = $info_mark;
//                $data[$key]['id'] = '';
//                $data[$key]['info_mark'] = $info_mark;
//            }elseif(!empty($dingdanh)){
            $sql = "select * from $tableName where dingdanh='$dingdanh' and xiangci='$xiangci' and gongdanh='$gongdanh'";
            $result = Db::query($sql);
            $data[$key]['id'] = empty($result[0]['ID'])?'':$result[0]['ID'];
            if (!empty($type)){
                $data[$key]['info_mark'] = $type;
            }
//            }
//        }
            //处理线下工单号
//        if (count($hangs)){
//            $result = $this->doNoGoangdanh($hangs,$zhizaobm);
        }
        return $data;
    }

    public function doNoGoangdanh($hangs,$zhizaobm){
        $objPHPExcel = self::$PHPExcel;
        //默认为第一个sheet表
        $sheet = $objPHPExcel->getSheet(0);
        $data= $objPHPExcel->getActiveSheet()->toArray();
        // 总列数
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
        //excel列对应下标
        $option = self::getImportOption(0);
        $titleLine = $option['titleLine'];
        $productInfo = $option['productInfo'];
        $excelIndex = self::excelIndex($highestColumnIndex);
        //获取字段对应的下标
        foreach ($excelIndex as $val) {
            $titleName = excel_trim($objPHPExcel->getActiveSheet(0)->getCell($val . $titleLine)->getValue());
            if (array_key_exists($titleName, $productInfo)) {
                $arr[$productInfo[$titleName]] = $val;
            }
            //对日期列做riqi标记
            if (strpos($titleName, '日期')!==false || strpos($titleName, '时间')!==false) {
                $riqi[] = $val;
            }
        }
        //生成数据数组
        foreach ($hangs as $j=>$info_mark){
            $dataArr[$j]['info_mark'] = $info_mark;
            $dataArr[$j]['zhizaobm'] = $zhizaobm;
            foreach ($arr as $key => $val) {
                //excel日期处理
                $value = excel_trim($objPHPExcel->getActiveSheet(0)->getCell($val . $j)->getValue());
                if (in_array($val,$riqi) && (date('Y-m-d',strtotime($value)) != $value) && !empty($value)){
                    $value = date("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                    $riqi_index[] = $key;
                }
                $dataArr[$j][$key] = $value;
            }
        }
        //数据处理 整合sql
        foreach ($dataArr as $val){
            $this->checkInfoMark($val);
            $start ='';
            $end = '';
            foreach ($val as $k=>$v){
                $start .= $k.',';
                if (in_array($k,$riqi_index)){
                    $end .=  "to_date('$v','yyyy-mm-dd hh24:mi:ss'),";
                }else{
                    $end .= "'".$v."',";
                }
            }
            $sql = 'insert into PLAN_DINGDANRB_INFO('.trim($start,',').')values('.trim($end,',').')';
            //添加工单号
            Db::execute($sql);
        }
    }

    public function checkInfoMark($data){
        $datas = array(
            'zhizaobm' => $data['zhizaobm'],//制造部门
            'dingdanlb' => $data['dingdanlb'],//订单类别
            'wuliufhxx' => $data['wuliufhxx'],//物流信息
            'dangci' => $data['dangci'],//档次
            'menkuang' => $data['menkuang'],//门框
            'kuanghou' => $data['kuanghou'],//框厚
            'menkuangyq' => $data['menkuangyq'],//门框要求
            'menkuangcz' => $data['menkuangcz'],//门框材质
            'dikuangcl' => $data['dikuangcl'],//底框材料
            'menshan' => $data['menshan'],//门扇
            'menshancz' => $data['menshancz'],//门扇材质
            'guige' => $data['guige'],//规格
            'kaixiang' => $data['kaixiang'],//开向
            'jiaolian' => $data['jiaolian'],//铰链
            'huase' => $data['huase'],//花色
            'menshankk' => $data['menshankk'],//门扇开孔
            'biaomianfs' => $data['biaomianfs'],//表面方式
            'biaomianyq' => $data['biaomianyq'],//表面要求
            'chuanghua' => $data['chuanghua'],//窗花
            'maoyan' => $data['maoyan'],//猫眼
            'biaopai' => $data['biaopai'],//标牌
            'suoti' => $data['suoti'],//锁体
            'suoxin' => $data['suoxin'],//锁芯
            'fusuo' => $data['fusuo'],//副锁
            'suobaxx' => $data['suobaxx'],//副锁
            'biaojian' => $data['biaojian'],//标件
            'baozhuangpp' => $data['baozhuangpp'],//包装品牌
            'baozhuangfs' => $data['baozhuangfs'],//包装方式
            'qitatsyq' => $data['qitatsyq']//其他特殊要求
        );
        $sql = "select INFO_MARK from PLAN_DINGDANRB_INFO where ";
        foreach ($datas as $key=>$val) {
            if (is_null($val) || $val==''){
                $arr[] = "$key is null ";
            }else{
                $arr[] = "$key = '$val'";
            }
        }
        $str = implode(' and ',$arr);
        $sql = $sql.$str;
        $result = Db::query($sql);
        if (count($result)){
            $sql ="delete from PLAN_DINGDANRB_INFO where INFO_MARK='".$result[0]['INFO_MARK']."'";
            Db::execute($sql);
            $sql ="delete from PLAN_DINGDANRB where INFO_MARK='".$result[0]['INFO_MARK']."'";
            Db::execute($sql);
        }
        return true;
    }

    /**
     * 检查计划周期数据
     * @param $tableID
     * @param $table_type_id
     * @param $confirm_info
     * @param $data
     * @return array|mixed
     */
    public function checkPlanConfirm($tableID,$table_type_id,$confirm_info,$data,$isAddtional){
        switch ($table_type_id){
            case 0:
                $tableName = 'PLAN_DINGDANRB';
                break;
            case 1:
                $tableName = 'PLAN_TABLE_DATA_FENJIEB';
                break;
            case 2:
                $tableName = 'PLAN_TABLE_DATA_MENKUANG';
                break;
            case 3:
                $tableName = 'PLAN_TABLE_DATA_MENKUANG';
                break;
            case 4:
                $tableName = 'PLAN_TABLE_DATA_MENSHAN';
                break;
            case 5:
                $tableName = 'PLAN_TABLE_DATA_ZONGZHUANG';
                break;
        }
        foreach($data as $key=>$val){
            $kaigongdm = $val['kaigongdm'];
            if ($table_type_id ==1){
                $riqi = $val['kaigongrq'];
                $riqi_name = 'kaigongrq';
            }else{
                $riqi = $val['jihuarq'];
                $riqi_name = 'jihuarq';
            }
            $sql = "select * from $tableName where  $riqi_name =to_date('$riqi','yyyy-mm-dd hh24:mi:ss') and 
                      kaigongdm='$kaigongdm' and table_id=$tableID and isbuchong='$isAddtional'";
            $result = Db::query($sql);
            $data[$key]['id'] = empty($result[0]['ID'])?'':$result[0]['ID'];
        }
        $riqi = $confirm_info['jihuarq'];
        $sql = "select * from PLAN_CONFIRM_INFO where table_id =$tableID and JIHUARQ =to_date('$riqi','yyyy-mm-dd hh24:mi:ss') and isbuchong='$isAddtional'";
        $confirmId = Db::query($sql);
        $confirm_info['id'] = empty($confirmId[0]['ID'])?'':$confirmId[0]['ID'];
        $result = array(
            'confirm_info'=>$confirm_info,
            'table_data'=>$data
        );
        return $result;
    }
    /**
     * 通过表id获取option设置
     * @param $tableID
     * @return array
     */
    public function getImportOption($type)
    {
        switch ($type) {
            //订单日报以0 来获取option
            case 1:
                $option = array(
                    'titleLine' => 1,
                    'firstLine' => 2,
                    'columnMap' => array(
                        '科目代码' => 'subject_code',
                        '科目名称' => 'subject_name',
                    ),
                );
                break;
            case 2:
                $option = array(
                    'titleLine' => 2,
                    'firstLine' => 3,
                    'columnMap' => array(
                        '订单号' => 'dingdanh',
                        '项次' => 'xiangci',
                        '开工代码' => 'kaigongdm',
                        '成品工单' => 'gongdanh',
                        '数量' => 'shuliang',
                        '开工日期' => 'kaigongrq',
                        '完工数据' => 'wangongsj',
                        '尾货(门框)' => 'weihuo_menkuang',
                        '尾货(母扇)' => 'weihuo_mumen',
                        '尾货(子扇)' => 'weihuo_zimen',
                        '尾货(边门)' => 'weihuo_bianmen',
                        '备注' => 'remark')
                );
                break;
            case  2:
                $option = array(
                    'titleLine' => 2,
                    'firstLine' => 3,
                    'columnMap' => array(
                        '订单号' => 'dingdanh',
                        '项次' => 'xiangci',
                        '开工代码' => 'kaigongdm',
                        '成品工单' => 'gongdanh',
                        '计划日期' => 'jihuarq',
                        '计划量' => 'jihual',
                        '分解表数量'=>'fenjiebsl',
                        '延期数据' => 'yanqisj',
                        '完工数据' => 'wangongsj',
                        '门扇厚度' => 'menshanhd',
                        '加工序号' => 'jiagongxh',
                        '加工工段' => 'jiagonggd',
                        '班次' => 'banci',
                        '备注' => 'remark')
                );
                break;
            case 3:
                $option = array(
                    'titleLine' => 2,
                    'firstLine' => 3,
                    'columnMap' => array(
                        '订单号' => 'dingdanh',
                        '项次' => 'xiangci',
                        '开工代码' => 'kaigongdm',
                        '成品工单' => 'gongdanh',
                        '计划日期' => 'jihuarq',
                        '计划量' => 'jihual',
                        '分解表数量'=>'fenjiebsl',
                        '延期数据' => 'yanqisj',
                        '完工数据' => 'wangongsj',
                        '门扇厚度' => 'menshanhd',
                        '加工序号' => 'jiagongxh',
                        '加工工段' => 'jiagonggd',
                        '班次' => 'banci',
                        '备注' => 'remark')
                );
                break;
            case 4:
                $option = array(
                    'titleLine' => 2,
                    'firstLine' => 3,
                    'columnMap' => array(
                        '订单号' => 'dingdanh',
                        '项次' => 'xiangci',
                        '开工代码' => 'kaigongdm',
                        '成品工单' => 'gongdanh',
                        '母扇计划量' => 'mushanjhl',
                        '子扇计划量' => 'zishanjhl',
                        '边门计划量' => 'bianmenjhl',
                        '分解表数量(母扇)'=>'fenjiebsl_mushan',
                        '分解表数量(子扇)'=>'fenjiebsl_zishan',
                        '分解表数量(边门)'=>'fenjiebsl_bianmen',
                        '母扇延期数据' => 'mushanyqsj',
                        '子扇延期数据' => 'zishanyqsj',
                        '边门延期数据' => 'bianmenyqsj',
                        '母扇完工数据' => 'mushanwgsj',
                        '子扇完工数据' => 'zishanwgsj',
                        '边门完工数据' => 'bianmenwgsj',
                        '计划日期' => 'jihuarq',
                        '门框厚度' => 'menkuanghd',
                        '加工序号' => 'jiagongxh',
                        '加工工段' => 'jiagonggd',
                        '班次' => 'banci',
                        '备注' => 'remark')
                );
                break;
            case 5:
                $option = array(
                    'titleLine' => 2,
                    'firstLine' => 3,
                    'columnMap' => array(
                        '订单号' => 'dingdanh',
                        '项次' => 'xiangci',
                        '开工代码' => 'kaigongdm',
                        '成品工单' => 'gongdanh',
                        '计划量' => 'jihual',
                        '计划日期' => 'jihuarq',
                        '分解表数量'=>'fenjiebsl',
                        '延期数据' => 'yanqisj',
                        '完工数据' => 'wangongsj',
                        '工位号' => 'gongweih',
                        '加工序号' => 'jiagongxh',
                        '班次' => 'banci',
                        '备注' => 'remark')
                );
                break;
            case 6://计划补充
                $option = array(
                    'titleLine' => 2,
                    'firstLine' => 3,
                    'columnMap' => array(
                        '开工代码' => 'kaigongdm',
                        '数量' => 'shuliang',
                        '开工日期' => 'kaigongrq',
                        '尾货(门框)' => 'weihuo_menkuang',
                        '尾货(母扇)' => 'weihuo_mumen',
                        '尾货(子扇)' => 'weihuo_zimen',
                        '尾货(边门)' => 'weihuo_bianmen',
                        '备注' => 'remark')
                );
                break;
        }
        return $option;
    }

    /**
     * 报表导入文件处理
     * 上传文件的名称为 file
     * @param $option
     * @return array|string|\think\response\Json
     */
    public function reportImport($option)
    {
        if (!count($option)){
            return json_encode(array('resultcode' => -1, 'resultmsg' => 'option数组未获取到!'));
            exit();
        }
        //判断导入文件是否错误
        $error = $_FILES['file']['error'];
        if ($error) {
            return json_encode(array('resultcode' => -1, 'resultmsg' => '上传失败!'));
            exit();
        }
        // 自定义文件上传路径
        $uploadPath = ROOT_PATH . 'upload';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        //文件路径生成
        $tempFile = $_FILES['file']['tmp_name'];
        $extension = explode(".", $_FILES['file']['name']);
        $fileName = date("YmdHis") . rand(1, 100) . '.' . end($extension);
        $uploadFile = $uploadPath . DS . $fileName;
        //移动至自定义路径下
        $result = move_uploaded_file($tempFile, $uploadFile);
        //移动到框架应用目录upload下
        if ($result) {
            //上传文件路径
            self::$uploadPath = $uploadFile;
        } else {
            //上传失败获取错误信息
            return json_encode(array('resultcode' => -1, 'resultmsg' => '上传失败!'));
            exit();
        }
        if (!file_exists ( $uploadFile)){
            return json_encode(array('resultcode' => -1, 'resultmsg' => '文件不存在!'));
            exit();
        }
        ini_set('max_execution_time',0);
        ini_set('memory_limit', "-1");
        //处理文件
        $data = self::doReport($option, end($extension));
        //清理上传的文件
        $uploadFile = str_replace('\\', '/', realpath(self::$uploadPath));
        unlink($uploadFile);
        //返回数据
        return $data;
    }

    /**
     * 处理文件
     * @param array $option
     * @param $extension
     * @return array|\think\response\Json
     */
    private function doReport($option = array(), $extension)
    {
        //键名-键值对应关系
        $columnMap = $option['columnMap'];
        //数据开始行数
        $firstLine = $option['firstLine'];
        //标题行
        $titleLine = $option['titleLine'];
        //判断文件扩展名
        if ($extension == 'csv') {
            //处理csv文件
            $result = self::input_csv($columnMap, $firstLine, $titleLine);
        } elseif ($extension == 'xls' || $extension == 'xlsx') {
            //处理excel文件
            $result = self::input_excel($columnMap, $firstLine, $titleLine);
        } else {
            //不处理其他类型
            return json_encode(array('resultcode' => -1, 'resultmsg' => '文件格式错误!'));
            exit();
        }
        return $result;
    }

    /**
     * csv格式
     * @param $columnMap
     * @param $firstLine
     * @param $titleLine
     * @return array
     */
    private function input_csv($columnMap, $firstLine, $titleLine)
    {
        //存放csv文件的数据
        $out = array();
        $n = 0;
        $maxRow = 10000;
        $handle = fopen($this->uploadPath, 'r');
        //获取数据
        while ($data = fgetcsv($handle, $maxRow)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i++) {
                $val = excel_trim(iconv('gb2312', 'utf-8', $data[$i]));
                $out[$n][$i] = $val;
            }
            $n++;
        }
        //csv取数据是数据形式,下标从0开始
        $titleLine = $titleLine - 1;
        $firstLine = $firstLine - 1;
        //获取字段对应的下标
        foreach ($out[$titleLine] as $key => $val) {
            $titleName = $val;
            if (array_key_exists($titleName, $columnMap)) {
                $arr[$columnMap[$titleName]] = $key;
            }
        }
        if (count($arr) != count($columnMap)) {
            return json_encode(array('resultcode' => -1, 'resultmsg' => 'csv表头栏位匹配不对等，请检查表头栏位!'));
            exit();
        }
        $j = 0;
        //csv某行的列数是否正确,不正确认为无效数据
        $titleCount = count($out[$titleLine]);
        //总数据行
        $dataCount = count($out);
        $dataArr = array();
        for ($i = $firstLine; $i <= $dataCount; $i++) {
            $markStr = '';
            foreach ($arr as $key => $val) {
                if (count($out[$j]) == $titleCount) {
                    $dataArr[$j][$key] = $out[$i][$val];
                    $markStr .= $out[$i][$val];
                }
            }
            //删除全字段为空的数据
            if (empty($markStr)) {
                unset($dataArr[$j]);
            }
            $j++;
        }
        return $dataArr;
    }

    /**
     * excel格式
     * @param $columnMap
     * @param $firstLine
     * @param $titleLine
     * @return array|\think\response\Json
     */
    private function input_excel($columnMap, $firstLine, $titleLine)
    {
        //加载excel扩展
        vendor("PHPExcel.Classes.PHPExcel");
        $arr = array();
        $objPHPExcel = \PHPExcel_IOFactory::load(self::$uploadPath);
        self::$PHPExcel = $objPHPExcel;
        //默认为第一个sheet表
        $sheet = $objPHPExcel->getSheet(0);
        // 总行数
        $highestRow = $sheet->getHighestRow();
        // 总列数
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
        //excel列对应下标
        $excelIndex = self::excelIndex($highestColumnIndex);
        //获取字段对应的下标
        $riqi = array();
        foreach ($excelIndex as $val) {
            $titleName = excel_trim($objPHPExcel->getActiveSheet(0)->getCell($val . $titleLine)->getValue());
            if (array_key_exists($titleName, $columnMap)) {
                $arr[$columnMap[$titleName]] = $val;
            }
            //对日期列做riqi标记
            if (strpos($titleName, '日期')!==false || strpos($titleName, '时间')!==false) {
                $riqi[] = \PHPExcel_Shared_Date::ExcelToPHP($val);
//                $riqi[] = $val;
            }
        }
//        if (count($arr) != count($columnMap)) {
//            return json_encode(array('resultcode' => -1, 'resultmsg' => 'excel表头栏位匹配不对等，请检查表头栏位!'));
//            exit();
//        }
        //生成数据
        $j = 0;
        $dataArr = array();
        for ($i = $firstLine; $i <= $highestRow; $i++) {
            $markStr = '';
            foreach ($arr as $key => $val) {
                //excel日期处理
                $value = excel_trim($objPHPExcel->getActiveSheet(0)->getCell($val . $i)->getValue());
                if (in_array($val,$riqi) && (date('Y-m-d',strtotime($value)) != $value) && !empty($value)){
                    $value = date("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                }
                $dataArr[$j][$key] = $value;
                $markStr .= $value;
            }
            //删除全字段为空的数据
            if (empty($markStr)) {
                unset($dataArr[$j]);
            }
            $j++;
        }
        return $dataArr;
    }

    /**
     * excel下标
     * @param $highestColumnIndex
     * @return array
     */
    private function excelIndex($highestColumnIndex)
    {
        //Excel列下标顺序
        $currentColumn = 'A';
        for ($i = 1; $i <= $highestColumnIndex; $i++) {
            $a[] = $currentColumn++;
        }
        return $a;
    }
}
