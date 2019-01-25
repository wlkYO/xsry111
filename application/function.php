<?php
use think\Db;
use app\common\oracle\OciConnection;
//定义当前录入时间
$now_hour=date("H");
if((int)$now_hour<4)
    define("TODAY",date("Ymd",strtotime("-1 day")));
//define("TODAY",date("Ymd"));
else
    define("TODAY",date("Ymd"));
define("XSRB_IP","http://".$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]);
define("RB_YF_SJ_DATA",'2018-12-01');
define("TODAY1",date("Ymd",strtotime("-1 day")));
//输出打印
function p($value)
{
    if (is_bool($value))
    {
        var_dump($value);
    }elseif (is_null($value))
    {
        var_dump(NULL);
    }else
    {
        echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>".print_r($value,true)."</pre>";
    }
}
//返回函数
function retmsg($retcode, $retdata=null, $retmessage=null)
{
    $retmsg = "";
    switch ($retcode) {
        case 0: { $retmsg = "操作成功"; break; }
        case -1: { $retmsg = "操作失败"; break; }
        case -2: { $retmsg = "token验证失败"; break; }
        default: { $retmsg = "未知错误";}
    }
    //处理orale大写转成小写
    if (!empty($retdata)) {
        foreach ($retdata as $k=>$v) {
            if (is_array($retdata[$k])) {
                $retdata[$k]=array_change_key_case($retdata[$k]);
            }
        }
    }
    return array("resultcode"=>$retcode,"resultmsg"=>empty($retmessage)?$retmsg:$retmessage,"data"=>$retdata);
}

//导入excel去空格
function excel_trim($content)
{
    if (is_object($content)) {
        $str=preg_replace("/(\s|\&nbsp\;||\xc2\xa0)/", "", $content->__toString());
    }
    $str=preg_replace("/(\s|\&nbsp\;||\xc2\xa0)/", "", $content);
    return $str;
}

//获取接口数据
function curl_get($url, $timeout=10)
{
    //初始化
    $ch = curl_init();

    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    return $output;
}

function curl_post($url,$timeout=20,$postData) {
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    $postData = json_encode($postData);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_TIMEOUT,$timeout);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}

function findNum($str='')
{
    $e = "/\d+/";
    preg_match_all($e, $str, $arr);
    return $arr[0][0];
}

/**
 * 修改pdo缓存计算错误
 * 使用cast(class_name as VARCHAR2(60)) as class_name方式组合列名
 * @param $tableName
 * @param $columnStr 指定列名以逗号隔开
 * @param $columnPrefix 列名前缀
 * @param $multiple //缓存设置倍数,默认2
 * @param 是否格式化日期
 * @return string
 */
function getColumnName($tableName, $columnStr = '', $columnPrefix = '', $multiple = 2, $formatDate = 1)
{
    $tableName=strtoupper($tableName);
    if ($columnStr!='') {
        $columnStr=strtoupper($columnStr);
        $columnStrArray=explode(',', excel_trim($columnStr));
        $columnArray=array();
    }
    $columnName='';//转化后的列
    $sql="select * from user_tab_columns where Table_Name='$tableName' ";
    $changeDataType=['VARCHAR2','CHAR'];//待转换的数据类型
    $data=DB::query($sql);
    foreach ($data as $k => $v) {
        $dataType=$v['DATA_TYPE'];
        $tempColumnName=$v['COLUMN_NAME'];
        if (empty($columnStrArray)||in_array($tempColumnName, $columnStrArray)) {
            if (in_array($dataType, $changeDataType)) {
                $dataLength=$v['DATA_LENGTH']*$multiple;
                $temp="cast($columnPrefix$tempColumnName as $dataType ($dataLength)) as ". '"'.$columnPrefix.$tempColumnName.'"';
                $columnName.=" $temp,";
            } else {
                $temp=$columnPrefix.$tempColumnName.' as "'.$columnPrefix.$tempColumnName.'"';
                if ($formatDate&&$dataType=='DATE') {
                    $temp="to_char($columnPrefix$tempColumnName, 'yyyy-MM-dd')".' as "'.$columnPrefix.$tempColumnName.'"';
                }
                $columnName.=$temp.',';
            }
            $columnArray[$tempColumnName]=$temp;
        }
    }
    //列名排序按$columnStr的顺序排序$columnName
    if (!empty($columnStrArray)) {
        $coulumn=array();
        foreach ($columnStrArray as $k => $v) {
            $coulumn[]=$columnArray[$v];
        }
        $columnName=implode(',', $coulumn);
    }
    return empty($columnName)?$columnStr:rtrim($columnName, ',');
}

function two_array_merge(&$array1, $array2)
{
    foreach ($array2 as $k=>$v) {
        array_push($array1, $v);
    }
}

/**
 * 数组键转成小写
 * @param $array oracle查询出的二维索引数组
 */
function array_change_keycase(&$array)
{
    foreach ($array as $k => $v) {
        if (is_array($array[$k])) {
            $array[$k]=array_change_key_case($array[$k]);
        }
    }
    return $array;
}

//查询结果集健名转小写
function changeCase($result, $flag = 0)
{
    $arr = array();
    $combine = array();
    foreach ($result as $key => $val) {
        foreach ($val as $k => $v) {
            if ($flag) {
                $arr[strtoupper($k)] = $v;
            } else {
                $arr[strtolower($k)] = $v;
            }
        }
        array_push($combine, $arr);
    }
    return $combine;
}

if (!function_exists('M')) {
    /**
     * 实例化数据库类
     * @param array|string  $config 数据库配置参数
     * @param bool          $force 是否强制重新连接
     * @return \think\db\Query
     */
    /*function M($config = [], $force = false)
    {
        $config = !empty($config)?$config:config('database');
        return \OciConnection::getInstance($config, $force);
    }*/
}

function arrayToString($array) {
    $str = '';
    foreach ($array as $k => $v) {
        $str .= "$k=>$v";
    }
    return $str;
}




