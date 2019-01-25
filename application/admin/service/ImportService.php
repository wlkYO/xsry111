<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/09
 * Time: 16:11
 */

namespace app\admin\service;

use think\Controller;
use app\admin\service\KeyValueParamService as keyVal;
use think\Db;

class ImportService extends Controller
{
    /**
     * 文件上传路径
     * @var
     */
    public $uploadPath;
    public $option = array();
    /**
     * 报表导入文件处理
     * 上传文件的名称为 file
     * @param $option
     * @return array|string|\think\response\Json
     */
    public function reportImport($option,$dept_id)
    {
        header("Access-Control-Allow-Origin: *");
        $this->option = $option;
        //判断导入文件是否错误
//        var_dump($_FILES);
//        die();
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
        $tempFile = $_FILES ['file']['tmp_name'];
        $extension = explode(".", $_FILES['file'] ['name']);
        $fileName = date("YmdHis") . rand(1, 100) . '.' . end($extension);
        $uploadFile = $uploadPath . DS . $fileName;
        //移动至自定义路径下
        $result = move_uploaded_file($tempFile, $uploadFile);
        //移动到框架应用目录upload下
        if ($result) {
            //上传文件路径
            $this->uploadPath = $uploadFile;
        } else {
            //上传失败获取错误信息
            return json_encode(array('resultcode' => -1, 'resultmsg' => '上传失败!'));
            exit();
        }
        //处理文件
        $data = self::doReport(end($extension),$dept_id);
        //清理上传的文件
        $uploadFile = str_replace('\\', '/', realpath($this->uploadPath));
        unlink($uploadFile);
        //返回数据
        return $data;
    }

    /**
     * 处理文件
     * @param $extension
     * @return array|\think\response\Json
     */
    public function doReport($extension,$dept_id)
    {
        header("Access-Control-Allow-Origin: *");
        //判断文件扩展名
        if ($extension == 'csv') {
            //处理csv文件
            $result = self::input_csv($dept_id);
        } elseif ($extension == 'xls' || $extension == 'xlsx') {
            //处理excel文件
            $result = self::input_excel($dept_id);
        } else {
            //不处理其他类型
            return json_encode(array('resultcode' => -1, 'resultmsg' => '文件格式错误!'));
            exit();
        }
        return $result;
    }

    /**
     * excel格式
     * @return array|\think\response\Json
     */
    public function input_excel($dept_id)
    {
        header("Access-Control-Allow-Origin: *");
        //加载excel扩展
        vendor("PHPExcel.Classes.PHPExcel");
        $objPHPExcel = \PHPExcel_IOFactory::load($this->uploadPath);
        $sheet_names=$objPHPExcel->getSheetNames();
        foreach ($sheet_names as $key=>$sheet_name) {
            if ($this->option['table'] != $sheet_name)
                continue;
            if ($this->option['table'] == $sheet_name && $this->option['table'] != '日报格式')
                $out = $objPHPExcel->getSheet($key)->toArray();
            elseif($this->option['table'] == '日报格式')
                $out = $objPHPExcel->getActiveSheet()->toArray('',true,true,true);
        }
        if (empty($out)){
            return json_encode(array('resultcode' => -1, 'resultmsg' => '未检测到'.$this->option['table'].'的导入数据!'));
        }
        return $this->doData($out,$dept_id);
    }

    /**
     * csv格式
     * @return array
     */
    public function input_csv($dept_id)
    {
        header("Access-Control-Allow-Origin: *");
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
        return $this->doData($out,$dept_id);
    }

    /**
     * 生成数据数组
     * @param $data
     * @return array|string
     */
    public function doData($data,$dept_id){
        header("Access-Control-Allow-Origin: *");
        $riqi = array();
        //键名-键值对应关系
        $columnMap = $this->option['columnMap'];
        //数据开始行数
        $firstLine = $this->option['firstLine'];
        //标题行
        $titleLine = $this->option['titleLine'];
        $arr = array();

            //提取title数据库字段对应数据
            foreach ($data[$titleLine] as $key=>$datum) {
                if (array_key_exists($datum, $columnMap)) {
                    $arr[$columnMap[$datum]] = $key;
                }
                //对日期,时间列做riqi标记
                if (strpos($datum, '日期')!==false || strpos($datum, '时间')!==false) {
                    $riqi[] = $key;
                }
            }
//        }
        if (!count($arr)) {
            return json_encode(array('resultcode' => -1, 'resultmsg' => '字段匹配option不正确!'));
            exit();
        }
        //生成数据
        $dataArr = array();
        foreach ($data as $key=>$datum) {
            //从数据行开始
            if ($key<$firstLine){
                continue;
            }
            //判断全空行
            $markStr = '';
            foreach ($arr as $column => $index) {
                $value = $data[$key][$index];
                //时间,日期格式数据转换
                if (in_array($index,$riqi)){
                    if(date('Y-m-d',strtotime($value)) == $value){
                        $value = date('Y-m-d',strtotime($value));
                    }elseif(date('Ymd',strtotime($value)) == $value){
                        $value = date('Y-m-d',strtotime($value));
                    }elseif (is_numeric($value)){
                        $value = date("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                    }elseif(!empty($value)){
                        //程序还未处理到的时间格式
                        $a=explode('-',$value);
                        $temp1=$a[0];
                        $temp=$a[2]+2000;
                        $a[0] = $temp;
                        $a[2] = $a[1];
                        $a[1]=$temp1;
                        $value = implode('-',$a);
                    }
                }
//                if ($this->option['table'] == '支付调整'){
//                    if ($column == 'base' && !empty($value))
//                        $base = $value;
//                    if ($column == 'dept' && !empty($value))
//                        $dept = $value;
//
//                    if ($column == 'dept_name' && empty($value))
//                        $value = $base;
//
//                    if ($column == 'base' && empty($value))
//                        $value = $base;
//                    if ($column == 'dept' && empty($value))
//                        $value = $dept;
//                    if ($column == 'dept' && empty($value))
//                        $value = $base;
//                }
                $dataArr[$key][$column] = $value;
                $markStr .= $value;
            }

            //删除全字段为空的数据
            if (empty($markStr)) {
                unset($dataArr[$key]);
            }
        }
        $deptname = Db::query("select pid,dname from xsrb_department WHERE id=$dept_id");
        $de = $deptname[0]["pid"];
        $pianqu = Db::query("select dname from xsrb_department WHERE id=$de");
        if($this->option['table'] == "现金日记账"){
            $datas = array();
            foreach ($dataArr as $k=>$v){
                $xmtype = Db::table('xjrjz_xm_type')->field('id')->where('name',$v["xm_type"])->find();
                $yijimx = Db::table('xjrjz_xm_type') ->field('id')->where('name',$v["yijimx"])->find();
                $ywtype = Db::table('xjrjz_yw_type') ->field('id')->where('yw_type_name',$v["yw_type"])->find();
                $erjimx = Db::table('xjrjz_xm_type') ->field('id')->where('name',$v["erjimx"])->find();
                if(!empty($erjimx)){
                    $v["erjimx"] = $erjimx["id"];
                }
                $v["xm_type"] = $xmtype["id"];
                $v["create_time"] = date('Y-m-d H:i:s',time());
                $v["yijimx"] = $yijimx["id"];
                $v["yw_type"] = $ywtype["id"];
                $v["dept_id"] = $dept_id;
                array_push($datas,$v);
            }
            $dataArr = $datas;
        }
        else {
            $error = array();
            if($this->option['table'] == "应收账款期初"){
                $errorheader = array(
                    array("headerName"=>"行数","field"=>"line"),
                    array("headerName"=>"片区","field"=>"area"),
                    array("headerName"=>"经营部","field"=>"dept"),
                    array("headerName"=>"客户姓名","field"=>"cname"),
                    array("headerName"=>"期初欠款日期","field"=>"date"),
                    array("headerName"=>"业务类别","field"=>"yw_type_name"),
                    array("headerName"=>"期初","field"=>"qichu"),
                    array("headerName"=>"经手人","field"=>"handlers"),
                    array("headerName"=>"备注","field"=>"remark"),
                );
            }
            elseif ($this->option['table'] == "预收账款期初"||$this->option['table'] == "暂存款期初"||$this->option['table'] == "应付账款期初"){
                $errorheader = array(
                    array("headerName"=>"行数","field"=>"line"),
                    array("headerName"=>"片区","field"=>"area"),
                    array("headerName"=>"经营部","field"=>"dept"),
                    array("headerName"=>"客户姓名","field"=>"cname"),
                    array("headerName"=>"业务类别","field"=>"yw_type_name"),
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
                    array("headerName"=>"业务类别","field"=>"yw_type_name"),
                    array("headerName"=>"初期","field"=>"qichu"),
                    array("headerName"=>"经手人","field"=>"handlers"),
                    array("headerName"=>"备注","field"=>"remark"),
                );
            }
            $datas = array();
            foreach ($dataArr as $k=>$v) {
                if($this->option['table'] == "现金结存期初"){
                    $v["qc_type_id"] = 1;
                    $v["dept_id"] = $dept_id;
                }
                if($this->option['table'] == "应收账款期初"){
                    if($v["dept"]!==$deptname[0]["dname"]||$v["area"]!==$pianqu[0]["dname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的片区或经营部不正确";
                        array_push($error,$v);
                    }
                    $v["qc_type_id"] = 2;
                    $v["dept_id"] = $dept_id;
                }
                if($this->option['table'] == "预收账款期初"){
                    if($v["dept"]!==$deptname[0]["dname"]||$v["area"]!==$pianqu[0]["dname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的片区或经营部不正确";
                        array_push($error,$v);
                    }
                    $v["qc_type_id"] = 3;
                    $v["dept_id"] = $dept_id;
                }
                if($this->option['table'] == "暂存款期初"){
                    if($v["dept"]!==$deptname[0]["dname"]||$v["area"]!==$pianqu[0]["dname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的片区或经营部不正确";
                        array_push($error,$v);
                    }
                    $v["qc_type_id"] = 4;
                    $v["dept_id"] = $dept_id;
                }
                if($this->option['table'] == "应付账款期初"){
                    if($v["dept"]!==$deptname[0]["dname"]||$v["area"]!==$pianqu[0]["dname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的片区或经营部不正确";
                        array_push($error,$v);
                    }
                    $v["qc_type_id"] = 5;
                    $v["dept_id"] = $dept_id;
                }
                if($this->option['table'] == "其他应收账款期初"){
                    if($v["dept"]!==$deptname[0]["dname"]||$v["area"]!==$pianqu[0]["dname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的片区或经营部不正确";
                        array_push($error,$v);
                    }
                    $v["qc_type_id"] = 6;
                    $v["dept_id"] = $dept_id;
                }
                if($this->option['table'] == "其他应付账款期初"){
                    if($v["dept"]!==$deptname[0]["dname"]||$v["area"]!==$pianqu[0]["dname"]){
                        $k1=$k+1;
//                        return array("resultcode"=>-1,"resultmsg"=>"第 $k1 条数据的客户存在日记账，暂时不能修改客户名称","data"=>null);
                        $v["line"] = $k1;
                        $v["remark"] = "该条数据的片区或经营部不正确";
                        array_push($error,$v);
                    }
                    $v["qc_type_id"] = 7;
                    $v["dept_id"] = $dept_id;
                }
                $ywtype = Db::table('xjrjz_yw_type')->field('id')->where('yw_type_name', $v["yw_type"])->find();
                $v["yw_type"] = $ywtype["id"];
                $v["create_time"] = date('Y-m-d H:i:s',time());
                $v["month"] = date('Y-m',time());
                array_push($datas, $v);
            }
            $dataArr = $datas;
        }
        if (empty($error)){
            return array("code"=>1,"arr"=>$dataArr);
        }else{
            return array("code"=>0,"arr"=>array("error"=>$error,"errorheader"=>$errorheader));
        }

    }

    /**
     * excel下标
     * @param $highestColumnIndex
     * @return array
     */
    public function excelIndex($highestColumnIndex)
    {
        header("Access-Control-Allow-Origin: *");
        //Excel列下标顺序
        $currentColumn = 'A';
        for ($i = 1; $i <= $highestColumnIndex; $i++) {
            $a[] = $currentColumn++;
        }
        return $a;
    }

    /**
     * 二维数组添加新键值
     * @param $value
     * @param $data
     * @return mixed
     */
    public function addNewColumn($value,$data){
        header("Access-Control-Allow-Origin: *");
        foreach ($data as $key=>$val){
            $data[$key] = array_merge($val,$value);
        }
        return $data;
    }
}
