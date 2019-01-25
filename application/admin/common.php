<?php

//配置 memorycache 名称
define("MEM_CACHE_NAME","m-bp1be9dbba6220c4.memcache.rds.aliyuncs.com");
//配置 memorycache 名称
define("MEM_CACHE_PWD","11211");
//配置 memorycache 缓存时间 7*24*3600=604800
define("MEM_CACHE_TIME",604800);
//配置系统名
define("CHAOGE_SYS_NAME","xsrb");
use think\Db;
//
//输出函数
function pp($value)
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
//生成token

//检查token是否存在
// function checktoken($token)
//{
//
//    $cache = new \Memcache();
//    $cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
//    $userinfo = $cache->get($token);
//    if(!$userinfo)//token过期或不存在
//    {
//        return false;
//    }
//    else//token存在
//    {
//        $s_arr = json_decode($userinfo,true);
//        $last_user_token = $cache->get("user".$s_arr["uid"]);
//        if($last_user_token == $token)
//        {
//            return $s_arr;
//            //return true;
//        }
//        else
//        {
//            $cache->delete($token);
//            return false;
//        }
//    }
//}

function checktoken($token)
{
    if(!isset($token) || empty($token))
        return false;
    $user = Db::query("SELECT token,user_name,dept_id,(SELECT pid FROM	xsrb_department	WHERE
			xsrb_department.id = sell_users.dept_id) AS pid,(SELECT qt1 FROM xsrb_department
		WHERE xsrb_department.id = sell_users.dept_id) AS qt1 FROM
	sell_users WHERE token='$token' AND dept_id IN (SELECT id	FROM xsrb_department)");
    return $user[0];
}
//	function updatetoken($token,$uid,$userinfo)
//	{
//		$cache = new \Memcache;
//		$cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
//		$oldtoken = $cache->get($uid . CHAOGE_SYS_NAME . "uid-token");
//		if($cache->set($uid . CHAOGE_SYS_NAME . "uid-token",$token,MEM_CACHE_TIME))
//		{
//			$cache->delete(CHAOGE_SYS_NAME . $oldtoken);
//			if($cache->set(CHAOGE_SYS_NAME . $token,json_encode($userinfo),MEM_CACHE_TIME))
//			{
//				return true;
//			}
//			else
//				return false;
//		}
//		else
//		{
//			return false;
//		}
//	}

function updatetoken($token,$uid,$userinfo)
{
    $cache = new \Memcache;
    $cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
    $oldtoken = $cache->get($uid . CHAOGE_SYS_NAME . "uid-token");
    if($cache->set($uid . CHAOGE_SYS_NAME . "uid-token",$token,0,MEM_CACHE_TIME))
    {
        $cache->delete(CHAOGE_SYS_NAME . $oldtoken);
        if($cache->set(CHAOGE_SYS_NAME . $token,json_encode($userinfo),0,MEM_CACHE_TIME))
        {
            return true;
        }
        else
            return false;
    }
    else
    {
        return false;
    }
}
	
	function updatevalue($token,$key,$value)
	{
		$cache = new \Memcache;
		$cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
		$jsonstr = $cache->get(CHAOGE_SYS_NAME . $token);
		if($jsonstr)
		{
			$json_arr = json_decode($jsonstr,true);
			$json_arr[$key] = $value;
			if($cache->set(CHAOGE_SYS_NAME . $token,json_encode($json_arr),MEM_CACHE_TIME))
			{
				return true;
			}
			else
				return false;
		}
		else
		{
			return false;
		}
	}
	
	function updatevaluebyuid($uid,$key,$value)
	{
		$cache = new \Memcache;
		$cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
		$token = $cache->get($uid . CHAOGE_SYS_NAME . "uid-token");
		return updatevalue($token,$key,$value);
	}
	//清楚缓存
	function delkey($uid)
	{
		$cache = new \Memcache;
		$cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
		$token = $cache->get($uid . CHAOGE_SYS_NAME . "uid-token");
		$ret = $cache->delete(CHAOGE_SYS_NAME . $token);
		return true;
	}
    function delkey_app($uid)
	{
		$cache = new \Memcache;
		$cache->connect(MEM_CACHE_NAME, MEM_CACHE_PWD);
		$token = $cache->get($uid . "chaogePTuid-token");
		$ret = $cache->delete('chaogePT' . $token);
		return true;
	}