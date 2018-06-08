<?php

/*
 * @description   文件上传方法
 * @author widuu  http://www.widuu.com
 * @mktime 08/01/2014
 */
 
global $QINIU_ACCESS_KEY;
global $QINIU_SECRET_KEY;

$QINIU_UP_HOST	= 'http://up-z2.qiniu.com';
$QINIU_RS_HOST	= 'http://rs.qbox.me';
$QINIU_RSF_HOST	= 'http://rsf.qbox.me';

//if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') $scheme='https://';
//else $scheme='http://';
//
//$api_host=$scheme.'rest'.strstr($_SERVER['HTTP_HOST'],'.');
//$res=curl_post($api_host.'/Qiniu/Qiniu',[]);
//$res=json_decode($res);

//file_put_contents('t.txt',var_export($res,true));

//配置$QINIU_ACCESS_KEY和$QINIU_SECRET_KEY 为你自己的key
/*
$QINIU_ACCESS_KEY	= 'UTUnjDFD2yh85KXDLJEuUmErltbLiJN8sCvQPos1';
$QINIU_SECRET_KEY	= '2w2oIUnqxzvSfnCGmj_WTiiwRqAjCS6tn92Rwa5L';

//配置bucket为你的bucket
$BUCKET = "onlinefad";

//配置你的域名访问地址
$HOST  = "http://7xl685.com1.z0.glb.clouddn.com";
*/

$QINIU_ACCESS_KEY	= 'Ix2vsZCIzffxF11Rp5WTJ9gn4Ps-4vuRzfgCocZK';
$QINIU_SECRET_KEY	= '5NpvtxzggC8GSNcu5QbuONUFIfYo_gd6mUTlBwIa';
$BUCKET 			= 'images';
$HOST  				= 'http://orxwhobqu.bkt.clouddn.com';

//上传超时时间
$TIMEOUT = "3600";

//保存规则
$SAVETYPE = "date";

//开启水印
$USEWATER = false;
$WATERIMAGEURL = "http://gitwiduu.u.qiniudn.com/ueditor-bg.png"; //七牛上的图片地址
//水印透明度
$DISSOLVE = 50;
//水印位置
$GRAVITY = "SouthEast";
//边距横向位置
$DX  = 10;
//边距纵向位置
$DY  = 10;

function urlsafe_base64_encode($data){
	$find = array('+', '/');
	$replace = array('-', '_');
	return str_replace($find, $replace, base64_encode($data));
}


function curl_post($url,$data,$param=null){
        $curl = curl_init($url);// 要访问的地址 

        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据
        $res = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $res;        
}