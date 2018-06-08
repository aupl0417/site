<?php
/**
* CURL读取
* @param array $param
* @param string $param['url']   要访问的URL
*/
function curl_file($param)
{
    $url = is_array($param) ? $param['url'] : $param;
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    //curl_setopt($curl, CURLOPT_REFERER, $param['referer']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // return web page 返回网页
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $param['header']);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 不显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回


    $html = curl_exec($curl);
    curl_close($curl);

    return $html;
}

/**
 * 创建订单号
 * @param string $str
 * @return string
 */
function create_orderno($str='') {
    $str=md5(uniqid(md5(microtime(true)),true));
    $prefix=date('YmdHis');
    $orderno=$prefix.substr(uniqid(md5(microtime(true).$str),true),-8,8);
    return $orderno;
}

/**
 * CURL请求
 * @param string $url 	请求地址
 * @param array 	$data 	post数据
 */
function curl_post($url,$data,$param=null){
    $curl = curl_init($url);// 要访问的地址
    //curl_setopt($curl, CURLOPT_REFERER, $param['referer']);

    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl,CURLOPT_POST,true); // post传输数据
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据

    //是否为上传文件
    if(!is_null($param)) curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);

    $res = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $res;
}

/**
 * 生成签名
 * @param array $data 要进行签名的数据
 * @param string $field 要进行签字的键名,为空是表是所有key
 * @param integer $type　1表示$field为不进行签名的Key
 */
function sign($sign_code,$data,$field='',$type=1){
    //$data=array_merge($data,$this->api_cfg);
    //清除不进行签名的字段
    if(isset($data['random'])) unset($data['random']);
    if(!empty($field) && $type==1){
        $field=is_array($field)?$field:@explode(',',$field);
        foreach($data as $key=>$val){
            if(in_array($key, $field)) unset($data[$key]);
        }
    }elseif(!empty($field) && $type!=1){
        $field=is_array($field)?$field:@explode(',',$field);
        foreach($data as $key=>$val){
            if(!in_array($key, $field)) unset($data[$key]);
        }
    }

    ksort($data);
    $query=http_build_query($data).'&'.$sign_code;
    $query=urldecode($query);
    return md5($query);
}

/**
 * 接口请求
 * @param string $api  调用接口
 * @param array $data 提交的数据
 * @param string $nfield 不参与签名的字段
 * @param int $type 1返回数据格式
 */
function doApi($api,$data='',$nfield='',$type=''){
    $data['sign']       =   sign($data,$nfield);
    $data['random']     =   $data['random']?$data['random']:session_id();

    $res 	=curl_post($api,$data);
    $res 	= $type == 1 ? json_decode($res,true) : json_decode($res);
    return $res;
}


?>