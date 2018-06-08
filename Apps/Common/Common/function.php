<?php
use Common\Builder\SearchGoods;
/**
* 读取分类
* @param array $param
* @param string $param['table']  		要取的数据表，必填
* @param string $param['map']			where条件
* @param string $param['ac']			D|M 实例化类型
* @param string $param['field']			要获取的字段
* @param integer $param['level']		读取深度/级别
* @param integer $param['sid']			父级分类
* @param string $param['order']   		排序方式
* @param integer $param['noid']			排除读取的类目ID
* @param integer $param['id']			要读取的类目ID
* @param string $param['limit']			读取数量
* @param string $Param['cache_name']	缓存名称
* @param integer $param['cache_time'] 	缓存有效期
*/
function get_category($param=array()){
	$cache_name=$param['cache_name'];
	$cache_time=is_null($param['cache_time'])?C('DATA_CACHE_TIME'):$param['cache_time'];

	if($cache_name){
		$list=S($cache_name);
		if(!empty($list)) return $list;
	}



	$table=$param['table']; //表名
	$ac=$param['ac']?$param['ac']:'M';   //实例化类型
	$sql=$param['sql'];
	$field=$param['field']?$param['field']:'id,atime,active,sid,name';
	$level=$param['level']?$param['level']:1;  //类目层数
	$this_level=$param['this_level']?$param['this_level']:1;  //当前层数
	$sid=$param['sid']>0?$param['sid']:0;
	$cache=$param['cache']?1:0;  //是否启用缓存,默认不启用

	//$map=$param['map'];
	if(!empty($param['map'][$this_level-1])) $map=$param['map'][$this_level-1];
	if(!empty($param['map']['shop_id'])) $map['shop_id']=$param['map']['shop_id'];
	//dump($param['map']);

	if(is_array($param['order'])){
		$order=$param['order'][$this_level-1];
	}else{
		$order=$param['order']?$param['order']:'sort asc,id asc';
	}
	$noid=$param['noid'];//排除的ID记录
	$id=$param['id'];
	$limit=$param['limit'];

	if(!empty($param['limit_level'])){
		$limit=$param['limit_level'][$this_level-1];
	}
	
	//$map['active']=1;
	if($sql) $map['_string']=$sql;
	if($param['id']>0) $map['id']=$param['id'];
	else $map['sid']=$sid;
	if(!empty($noid)) $map['id']=array('not in',$noid);
	if(!empty($id)) $map['id']=$id;

	if(!empty($param['inid'])) $map['id']=array('in',$param['inid']);

	$do=$ac($table);


    //$map['status']  =   1;
	$list=$do->where($map)->field($field)->order($order)->limit($limit)->select();
	//echo $do->getLastSQL().'<br>';

	if(is_array($param['checked'])){
		for($i=0;$i<count($list);$i++){
			if(in_array($list[$i]['id'],$param['checked'])) $list[$i]['checked']='checked';
		}
	}

	//统计记录
	if(!empty($param['sid_table'])){
		$dos=M($param['sid_table']);
		foreach($list as $key=>$val){
			$list[$key]['dsnum']=$dos->where(array('active'=>1,'sid'=>$val['id']))->count();
		}
	}

	//echo $do->getLastSQL().'<br>';
	//dump($list);
	if($this_level<$level && $list){
		
		for($i=0;$i<count($list);$i++){
			$darr=array();
			$darr=$param;
			$darr['cache_name']='';
			$darr['id']='';
			$darr['sid']=$list[$i]['id'];
			$darr['this_level']=$this_level+1;
			$list[$i]['dlist']=get_category($darr);
			$list[$i]['dnum']=count($list[$i]['dlist']);
		}
		
	}

	//echo $cache_name.'<br>';
	if($cache_name) S($cache_name,$list,$cache_time);

	return $list;
	


}


function getAccess() {
    $list = D('ShopAuthModuleRelation')->relation(true)->where(['sid' => ['gt', 0]])->field('id,title')->order('sort asc, id desc')->select();
    //writeLog($list);
    return $list;
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 		需要转换的字符串
 * @param string $start 	开始位置
 * @param string $length 	截取长度
 * @param string $charset 	编码格式
 * @param string $suffix 	截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr")){
            if ($suffix && strlen($str)>$length)
                return mb_substr($str, $start, $length, $charset).((strlen($str)/3)>$length?"...":"");
        else
                 return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
            if ($suffix && strlen($str)>$length)
                return iconv_substr($str,$start,$length,$charset)."...";
        else
                return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}



/**
 * 简单对称加密算法之加密
 * @param String $string 需要加密的字串
 * @param String $skey 加密EKY
 * @author Anyon Zou <zoujingli@qq.com>
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
 function str_encode($string = '', $skey = 'enhong') {
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
 }

 /**
 * 简单对称加密算法之解密
 * @param String $string 需要解密的字串
 * @param String $skey 解密KEY
 * @author Anyon Zou <zoujingli@qq.com>
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
 function str_decode($string = '', $skey = 'enhong') {
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key <= $strCount && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
 }



/**
* 发送邮件
* @param array $api  SMTP接口信息
* @param array $param 发送内容参数
*
* @param string $param['readto']  	发送已阅读通知，即此参数须填要接收通知的邮件地址
* @param string $param['from']		发送邮件地址，
* @param string $param['from_name']	送发用户名称
* @param string $param['to']		收件邮箱地址，多个可用半角逗号隔开
* @param string $param['to_name'] 	收件人名称
* @param string $param['subject'] 	邮件标题
* @param string $param['body']		邮件内容
* @param string $param['att']		附件 格式: @C:/a.txt，多个可用半角逗号隔开
* @param string $param['reto']		回复邮箱地址
* @param string $param['reto_name'] 回复名称
* @param string $param['cc']		抄送，多个可用半角逗号隔开
* @param string $param['bcc']		密抄，多个可用半角逗号隔开
* @param string $param['charset']	邮件编码,默认为utf-8
* @return bool
*/
function sendmail($param,$api=null){

	$cfg['smtp']='smtp.163.com';
	$cfg['port']=25;
	$cfg['username']='13631261142@163.com';
	$cfg['password']='abc123456';
	$cfg['email']='13631261142@163.com';

	//使用该函数前请先加载PHPMAILER类
	$host=$api['host']?$api['host']:$cfg['smtp'];
	$port=$api['port']?$api['port']:$cfg['port'];
	$user=$api['user']?$api['user']:$cfg['username'];
	$password=$api['password']?$api['password']:$cfg['password'];

	$readto=$param['readto'];
	$from=$param['from']?$param['from']:$cfg['email'];
	$from_name=$param['from_name']?$param['from_name']:'乐兑';
	$to=$param['to'];
	$to_name=$param['to_name'];
	$subject=$param['subject'];
	$body=$param['body'];
	$att=$param['att'];
	$reto=$param['reto'];
	$reto_name=$param['reto_name'];
	$cc=$param['cc'];
	$bcc=$param['bcc'];
	$charset=$param['charset']?$param['charset']:'utf-8';

		Vendor('PHPMailer.class#phpmailer');
		$mail = new \PHPMailer();

		$mail->CharSet	  =$charset;
		$mail->IsSMTP();							// tell the class to use SMTP
		$mail->SMTPAuth   = true;					// enable SMTP authentication
		//$mail->SMTPKeepAlive = true;              // SMTP connection will not close after each email sent
		$mail->Port       = $port;                  // set the SMTP server port
		$mail->Host       = $host;					// SMTP server
		$mail->Username   = $user;					// SMTP server username
		$mail->Password   = $password;				// SMTP server password
		if($readto) $mail->ConfirmReadingTo=$readto;				//读后通知邮箱
		if($reto) $mail->AddReplyTo($reto,$reto_name);  //回复
		if($cc) $mail->AddCC($cc);  //密送
		if($bcc) $mail->AddBCC($bcc); //抄送
		$mail->From=$from;
		$mail->FromName=$from_name;
		$mail->AddAddress($to,$to_name);
		$mail->Subject  = $subject;
		$mail->AltBody    = "这是一封HTML邮件，请用HTML方式浏览!";
		$mail->WordWrap   = 80;
		$mail->MsgHTML($body);
		$mail->IsHTML(true);
		if($att) {
			if(!is_array($att)) $att[0]=$att;
			foreach($att as $val){
				$mail->AddAttachment($val); //附件
			}
		}

		if(!$mail->Send()){
			return false;
		}else{
			return true;
		}

}


/**
* 将内容中含有url自动加上连接
* @param string $foo
*/

function autolink($foo){
     $foo = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1" target=_blank>\\1</a>', $foo);
    if( strpos($foo, "http") === FALSE )
    {
        $foo = eregi_replace('(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="http://\\1" target=_blank>\\1</a>', $foo);
    }
    else
    {
        $foo = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2" target=_blank >\\2</a>', $foo);
    }
    return $foo; 
}

/**
* 格式化时间,将时间输出类似 n分钟前,n小时前,n天前
* @param integer $date
* @return string
*/
function date_cmp($date){
		$d = new \Org\Util\Date();
		$d->setDate($date);
		$result=$d->timeDiff(time());
		if($result=='前') $result='刚刚';
		return $result;
}


/**
* 取上级分类ID
* @param array $param
* @param string $param['table']  	数据表
* @param integer $param['id']		分类ID
* @param integer $param['rootid']	顶级分类ID
* @return ingeter
*/
function upsid($param=array()){
	$table=$param['table']; //操作表
	$id=$param['id'];		//当前id
	$rootid=$param['rootid']?$param['rootid']:0;  //根ID

	$do=M($table);
	$sid=array($id);
	if($rs=$do->where(array('id'=>$id,'sid'=>array('gt',0)))->field('sid')->find()){		
		$sid[]=$rs['sid'];
		if($rs['sid']!=$rootid){			
			$sid=array_merge(upsid(array('table'=>$table,'id'=>$rs['sid'],'rootid'=>$rootid)),$sid);
			$sid=array_unique($sid);
		}

		
	}
	return $sid;
}



/**
* 删除字符串中所有空格
* @param string $str
*/
function trimall($str)
{
	$qian=array(" ","　","t","n","r","　","&nbsp;","	","&bsp;","&dquo;",chr(10),chr(13));
	$hou=array("","","","","","","");
	return str_replace($qian,$hou,$str);	
}


/**
* 从文章内容中取得被@的用户名
* @param string $str
*/
function auser($str){
	//echo htmlspecialchars($str);
	preg_match_all("/@\S+[\s]|[\n]|[\t]$/i",strip_tags(str_replace(array('</','&nbsp;'),array(' </',' &nbsp;'),$str)),$match);
	//dump($match);
	if(!empty($match[0])){
		$do=M('member');
		foreach($match[0] as $val){
			$sql[]='username="'.str_replace('@','',trim($val)).'"';
		}

		$map['_string']=implode(' or ',$sql);

		if($list=$do->where($map)->field('id,username')->select()){
			foreach($list as $val){
				$str=str_replace('@'.$val['username'],'<a href="/p/'.$val['id'].'" target="_blank" class="btn btn-sm ">@<i class="fa fa-user"></i>'.$val['username'].'</a>',$str);
			}
		}
	}
	
	return $str;
}



/**
* 取二维数组ID
* @param array $param
* @param array $param['plist']	二维数组,如从数据表中读取出来的n条数据
* @param string $param['field']	要获取的ID字段名
* @return array
*/
function arr_id($param=array()){
	$plist=$param['plist'];
	$field=$param['field']?$param['field']:'id';

	foreach($plist as $key=>$val){
		$result[]=$val[$field];
	}

	return $result;	
}



/**
* 对像转换成数组
* @param object $param
* @return array
*/
function objectToArray($param){ 
	if(is_object($param)){
        $param = (array)$param;
        $param = objectToArray($param);
    }elseif(is_array($param)){
        foreach($param as $key=>$value){
            $param[$key] = objectToArray($value);
        }
    }
	//dump($param);
    return $param;
} 


/**
* CURL读取
* @param array $param
* @param string $param['url']	要访问的URL
*/
function curl_file($param){
		$url=is_array($param)?$param['url']:$param;
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
* 简单字符串截取,用于采集内容
* @param string $str
* @param string $s
* @param string $e
*/
function MidStr($str,$s,$e){
	$ostr=@explode($s,$str);
	$ostr=@explode($e,$ostr[1]);
	return trim($ostr[0]);
}

/**
* 按数组键名对应的值进行替换
* @param array $param
* @param string $param['str']	要替换的字符串
* @param array $param['list']	要替换的数据
*/
function keyword_strstr($param=array()){
	$str=$param['str'];
	$list=$param['list'];

	foreach($list as $val){
		if(strstr($val,$str)) {
			return true;
			break;
		}
	}

	return false;
}


/**
* 关键词替换
* @param array $param
* @param array $param['rs'] 	一条数据记录
* @param string $param['field']	字段名
*/
function keyword_replace($param=array()){
	$rs=$param['rs'];
	$field=$param['field'];
	$do=M('Keyword');
	$cache_name='keyword_about';
	$list=F($cache_name);
	if(empty($list)){
		$list=$do->field('id,name,url')->order('slen desc')->select();
		F($cache_name,$list);
	}

	//dump($list);

		$karr=array();
		foreach($list as $val){
			if(!keyword_strstr(array('str'=>$val['name'],'list'=>$karr))){

				foreach($field as $v){
					if(strstr($rs[$v],$val['name'])){
						//$rs[$v]=str_replace($val['name'],'<a href="'.$val['url'].'" target="_blank">'.$val['name'].'</a>',$rs[$v]);
						$rs[$v]=str_replace_once($val['name'],'<a href="'.($val['url']?$val['url']:U($param['url'],array('q'=>$val['name']))).'" target="_blank">'.$val['name'].'</a>',$rs[$v]);
						$karr[]=$val['name'];
					}
				}
			}
		
		}

		return $rs;

}

/**
* 只替换一次
* @param string $needle 	要查找的字符串
* @param string $replace 	替换的内容
* @param string $haystack	要替换的字符串
*/
function str_replace_once($needle, $replace, $haystack) {
	$pos = strpos($haystack, $needle);
		if ($pos === false) {
		return $haystack;
	}
	return substr_replace($haystack, $replace, $pos, strlen($needle));
}


/**
* 取下级所有分类ID
* @param array $param
* @param string $param['cache_name']  	缓存名称
* @param integer $param['cache_time']	缓存有效期
* @param string $param['table']			数据表
* @param integer $param['sid']			分类ID
* @param string $param['map']			where条件
* @return array
*/
function sortid($param=array()){
	$cache_time=is_null($param['cache_time'])?C('DATA_CACHE_TIME'):$param['cache_time'];

	if($param['cache_name']){
		$id=S($param['cache_name']);
		if($id) return $id;
	}


	$table=$param['table'];
	$sid=$param['sid'];
	if($param['map']) $map=$param['map'];

	$do=M($table);
	$id=array();
	$id[]=$sid;
	$map['sid']=$sid;
	if($list=$do->where($map)->field('id')->select()){
		//echo $do->getLastSQL().'<br>';exit;
		foreach($list as $val){
			$id[]=$val['id'];
			$nid=array();
			$nid=sortid(array('table'=>$table,'sid'=>$val['id']));
			if(is_array($nid)){
				$id=array_merge($id,$nid);
			}
		}
			
	}
	$id=array_unique($id);

	if($param['cache_name']){
		S($param['cache_name'],$id,$cache_time);
	}


	return $id;
}


/**
* 取下级所有符合条件的记录
* @param array $param
* @param string $param['table']			数据表
* @param integer $param['sid']			分类ID
* @param string $param['map']			where条件
* @param string $param['field']			获取字段
* @param string $param['cache_name'] 	缓存名称，不为空时即启用缓存
* @return array
*/
function sortds($param=array()){
	$cache_name=$param['cache_name']?$param['cache_name']:'';
	if($cache_name){
		$list=S($cache_name);
		if($list) return $list;
	}

	$table=$param['table'];
	$map=$param['map'];
	$field=$param['field']?$param['field']:'id,atime,active,sid,name,ishome';
	$do=M($table);

	$tmp=explode(',',$param['sid']);
	$ids=array();
	foreach($tmp as $val){
		$ids=array_merge($ids,sortid(array('table'=>$table,'sid'=>$val)));
	}


	$map['id']=array('in',implode(',',$ids));

	$list=$do->where(array($map))->field($field)->select();

	if($cache_name){
		S($cache_name,$list);
	}

	return $list;
}

//url转换
function url_cmp($param=array()){
	$url_rex=$param['url_rex'];
	$rs=$param['rs'];
	$prev=$param['prev'];
	foreach($rs as $key=>$val){
	    if($key) {
            $url_rex = str_replace('[' . $prev . $key . ']', $val, $url_rex);
        }
	}
	return $url_rex;
}

/**
* 网站meta设置
* @param array $param['seo']  自定义meta
*/
function meta($param=null){
	$default=C('cfg.seo_default');
	//缺省meta
	//$default=$param['seo']?$param['seo']:$param['default'];	
	
	$result['title']=($param['seo']['title']?$param['seo']['title']:$default['title']).' - '.$default['name'];
	$result['keywords']=$param['seo']['keywords']?$param['seo']['keywords']:$default['keywords'];
	$result['description']=$param['seo']['description']?$param['seo']['description']:$default['description'];
	return $result;


}

/**
* curl上传文件
* @param array $param
* @param string $param['post_url']	提交的地址
* @param string $param['file']		要上传的文件，格式 $param['file']=array('file'=>'@'.$param['file']);
*/
function curl_upload($param=array()){
	//dump($param);
	//$param['file']=array('file'=>'@'.$param['file']);  ////文件路径，前面要加@，表明是文件上传.
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $param['post_url']);
	curl_setopt($curl, CURLOPT_POST, 1 );
	curl_setopt($curl, CURLOPT_POSTFIELDS, $param['file']);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl,CURLOPT_USERAGENT,"Mozilla/4.0");

	//$param['headers']=array('Expect:');
	curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
	if(!empty($param['headers'])) curl_setopt($curl, CURLOPT_HTTPHEADER,$param['headers']);

	$result = curl_exec($curl);
	$error = curl_error($curl);
	return $error ? $error : $result;
}


/**
* url跳转
* @param string $url 要跳转的url
*/
function gourl($url){
	$js='<script>';
	$js.='window.location.href="'.$url.'";';
	$js.='</script>';
	echo $js;
	exit;
}


/**
* POST数据处理，配合后台表单自动生成提交使用
* @param array $param  $_POST数据
*/
function post_field_cmp($param=array()){
	$table=strtolower($param['table']);

	$do=D('FieldView');
	$map['active']=1;
	$map['table_name']=$table;
	$fields=$do->where($map)->order('tables_field.sort asc,tables_field.id asc')->select();
	//echo $do->getLastSQL();
	//dump($fields);exit;
	
	//dump($_POST);
	foreach($_POST as $key=>$val){
		//处理checkbox为空时
		if(substr($key,0,10)=='_checkbox_'){
			if(empty($_POST[substr($key,10,strlen($key))])) $_POST[substr($key,10,strlen($key))]='';
			unset($_POST[$key]);
		}
		//radio-switch处理
		if(substr($key,0,14)=='_radio_switch_'){
			if(empty($_POST[substr($key,14,strlen($key))])) $_POST[substr($key,14,strlen($key))]='0';
			unset($_POST[$key]);
		}
		
		//密码处理
		if(substr($key,0,10)=='_password_'){
			//echo $key.'<br>';
			if(($_POST[substr($key,10,strlen($key))]!=$val && !empty($val)) || empty($val)) $_POST[substr($key,10,strlen($key))]=md5($_POST[substr($key,10,strlen($key))]);

			unset($_POST[$key]);
		}

	
		//商品SKU
		if($key=='_sku_'){
			$_POST['sku']=price_insert_before();
			unset($_POST[$key]);
		}
		
		//运费模板
		if(substr($key,0,9)=='_express_'){
			$_POST[substr($key,9,strlen($key))]=express_insert_before(substr($key,9,strlen($key)));
		}
		
		
		
		foreach($fields as $v){

			if($key==$v['name'] && $v['fun_before']!=''){
				//dump($v['fun_before']);
				if(!empty($_POST[$key])) {
					$v['fun_before']=url_cmp(array('rs'=>$val,'url_rex'=>html_entity_decode($v['fun_before'])));
					//dump($fun_before);
					$_POST[$key]=@eval($v['fun_before']);
				}

			}

		}

	}
	//dump(I('post.code'));
	//dump($_POST);exit;
	//dump($fields);exit;

	
}


/**
* 获取POST / GET 字段数据
* @param array $param  $_GET或$_POST数据
*/
function get_data($param=array()){
	$data=array();
	
	if($param['type']=='U') $data['etime']=time();
	else $data['atime']=time();

	$data['douser']=session('admin.username');

	if(!empty($param['default'])){
		foreach($param['default'] as $key=>$val){
			$data[$key]=$val;
		}
	}

	foreach($param['field'] as $val){
		$data[$val]=I($val);
	}

	return $data;
}

/**
* 数据库的CURD操作
* @param array $param
* @param string $param['cache_name']	缓存名称
* @param integer $param['cache_time']	缓存有效期
* @param string $param['table']			要操作的数据表
* @param string $param['do']			实例化类型 D｜M
* @param string $param['type']			操作类型 C 插入数据；U 更新数据；F 获取一条数据；D 删除数据；G 根据GROUP获取数据，R 为默认项，读取符合条件的数据
*/
function CURD($param=array()){
	$cache_time=is_null($param['cache_time'])?C('DATA_CACHE_TIME'):$param['cache_time'];
	if($param['cache_name']) {
		if($cache_data=S($param['cache_name'])) return $cache_data;
	}
	
	
	if(!empty($param['value'])) $data=$param['value'];
	elseif(!empty($param['data'])) $data=get_data($param['data']);
	
	$param['do']=$param['do']?$param['do']:'M';
	$do=$param['do']($param['table']); //实例化数据表
	//dump($data);

	switch($param['type']){
		case 'C':
			if($do->add($data)){
				$insid=$do->getLastInsID();
				return $insid;
			}else {
				return false;
			}
		break;
		case 'U':
			if($do->where($param['map'])->save($data)){
				return true;
			}else {
				return false;
			}
		break;
		case 'D':
			if($do->where($param['map'])->delete()){
				return true;
			}else {
				return false;
			}
		break;
		case 'F':		
			if($list=$do->where($param['map'])->field($param['field'])->find()){
				if($param['hit']) $do->where('id='.$list['id'])->setInc('hit');
				//echo $do->getLastSQL().'<br>';
				if($param['cache_name']) S($param['cache_name'],$list,$cache_time);
				return $list;
			}else return false;
		break;
		case 'G':	
			$list=$do->where($param['map'])->field($param['field'])->order($param['order'])->group($param['group'])->limit($param['limit'])->select();
			//echo $do->getLastSQL();

			if($param['cache_name']) S($param['cache_name'],$list,$cache_time);
			return $list;
		break;	
		default:  //R
			if($param['relation']){
				$list=$do->relation($param['relation'])->where($param['map'])->field($param['field'])->order($param['order'])->limit($param['limit'])->select();
			}else{
				$list=$do->where($param['map'])->field($param['field'])->order($param['order'])->limit($param['limit'])->select();
			}

			if($param['cache_name']) S($param['cache_name'],$list,$cache_time);
			return $list;
		break;

	}
}


/**
* radio / select 输出选择,用于tp模板
* @param string $v 		匹配数据
* @param array $param 	数组
* @param string $echo 	输出内容
*/

function input_checked($v,$param,$echo){
	if(in_array($v,$param)) echo $echo;
}


/**
* 生成表单参数,select 中的option选项或radio、checkbox选项，配合input_option函数使用
* @param array $param
* @param string $param['table']		要读取的数据表
*/
function form_option($param=array()){
	$table=strtolower($param['table']);

	$do=M('tables');
	$trs=$do->where(array('name'=>$table))->find();
		
	$do=M('tables_fieldsort');
	$list=$do->where('tid='.$trs['id'])->order('sort asc,id asc')->select();

	$do=M('tables_field');
	foreach($list as $key=>$val){
		$tmp=$do->where(array('sid'=>$val['id'],'active'=>1))->order('sort asc,id asc')->select();
		foreach($tmp as $v){
			$tv=array();
			if($v['data']){
				$tv=@eval(html_entity_decode($v['data']));
				//dump($v);
			}


			if($tv['group']==1){
				$data=array();
				$data['type']='group';
				$data['textname']=$tv['group_name'];
				//dump($option);
				$data['items'][]=input_option(array('field'=>$v,'rs'=>$param['rs']));

				$items=$do->where(array('name'=>array('in',$tv['item']),'sid'=>$val['id']))->order('sort asc,id asc')->select();
				//echo $do->getLastSQL();
				//dump($item);
				foreach($items as $it){
					$data['items'][]=input_option(array('field'=>$it,'rs'=>$param['rs']));
				}

				//dump($data);
				$list[$key]['option'][]=$data;
			}else{
				$list[$key]['option'][]=input_option(array('field'=>$v,'rs'=>$param['rs']));
			}





		}
		//dump($list);

	}

	//$d=array('field'=>array('value','name'),'data'=>array(array('value'=>1,'name'=>'是'),array('value'=>0,'name'=>'否')));
	//$d=array('table'=>'article_sort');
	//$d=array('field'=>array('id','name'),'function'=>'CURD','option'=>array('table'=>'article_sort','map'=>array('sid'=>0)));
	//echo json_encode($d);

	return $list;

}

/**
* 生成表单选项,select 中的option选项或radio、checkbox选项
* @param array $param
* @param string $param['field']		要读取的字段
*/
function input_option($param=array()){
	$field=$param['field'];
	$rs=$param['rs'];
	//dump($param);

	if($field['data']){
		//dump($field['data']);
		$field=array_merge($field,@eval(html_entity_decode($field['data'])));
	}

	if($field['option']){
		$field=array_merge($field,@eval(html_entity_decode($field['option'])));
	}


	$data=$field;
	$placeholder='请输入'.$field['title'];


	$data['field']=$data['field']?$data['field']:array('value','name');
	

	
	//插入数据时执行函数，生成表单此操作不需用到
	//if($field['function_c']) {
		//$data['function_c']=url_cmp(array('rs'=>$rs,'url_rex'=>html_entity_decode($field['function_c'])));
	//}

	if($field['isdefault']) $data['value']=$field['isdefault'];
	if($_GET[$field['name']]) $data['value']=$_GET[$field['name']];
	if($rs) $data['value']=$rs[$field['name']];

	

	//读取数据时执行函数，在修改数据时才需要读取数据
	if($field['fun_read'] && $rs) {
		$data['fun_read']=url_cmp(array('rs'=>$rs,'url_rex'=>html_entity_decode($field['fun_read'])));
		//dump($data['function_r']);
		$data['value']=@eval($data['fun_read']);
		//dump($data['value']);
	}

	$data['type']		=$field['type'];
	$data['name']		=$field['name'];
	$data['textname']	=html_entity_decode($field['nickname']);
	$data['is_need']	=$field['is_need'];
	$data['attr']		=html_entity_decode($field['attr']);
	$data['rule']		=html_entity_decode($field['rule']);
	$data['placeholder']=$field['placeholder']?$field['placeholder']:$placeholder;
	
	//dump($data);
	return $data;
	
}


/**
* 删除下级分类，支持无限级分类
* @param array $param
* @param string $param['string']	要读取的数据表
* @param string $param['map']		Where
* @param ingeger $param['id']		要删除的记录分类ID
*/
function sort_delete($param=array()){
	$table=$param['table'];
	$do=M($table);
	
	if($list=$do->where(array('sid'=>$param['id']))->select()){
		foreach($list as $val){
			sort_delete(array('table'=>$table,'id'=>$val['id'],'map'=>$param['map']));
		}
	}
	
	$map=$param['map'];
	$map['id']=$param['id'];		
	$do->where($map)->delete();		
}

/**
* 分类格式化为select、radio、checkbox选项
* @param array $param
* @param array $param['option']		见CURD函数
*/
function list2form($param=array()){
	if($param['type']==1) $list=CURD($param['option']);
	else $list=get_category($param['option']);
	$result=list2form_html(array('list'=>$list,'value'=>$param['value'],'first'=>$param['first']));

	return $result;
}

//配合上面函数使用
function list2form_html($param=array()){
	$list=$param['list'];
	$first=$param['first'];

	$select=$first['select'];
	foreach($list as $val){
		if($val['dlist']){
			$select.='<optgroup label="'.$val['name'].'">'.chr(13).chr(10);
			$select.=list2form_html(array('list'=>$val['dlist'],'value'=>$param['value']));
			$select.='</optgroup>'.chr(13).chr(10);
		}else{
			$select.='<option value="'.$val['id'].'" '.($val['id']==$param['value']?'selected':'').'>'.$val['name'].'</option>'.chr(13).chr(10);
		}
	}

	return $select;
}



/**
* 配合后台表单生成器，读取数据表时，字段格式输出
* @param array $param
* @param string $param['table']		要读取的数据表
* @param array 	$param['list']		数据记录
*/
function read_field_cmp($param=array()){
	$table=$param['table'];
	$list=$param['list'];

	$do=D('Admin/TableFieldView');
	$map['active']=1;
	$map['table_name']=$table;
	$fields=$do->where($map)->order('field.sort asc,field.id asc')->select();
	

	foreach($list as $key=>$val){
		foreach($fields as $v){
			if(!empty($v['function_r']) && !empty($val[$v['name']])){
				//dump($function_r);
				$v['function_r']=url_cmp(array('rs'=>$val,'url_rex'=>html_entity_decode($v['function_r'])));
				//dump($v['function_r']);
					
					//dump($function_c);
				$val[$v['name'].'_r']=@eval($v['function_r']);
			}

			if(!empty($v['function_f']) && !empty($val[$v['name']])){
				//dump($function_r);
				$v['function_f']=url_cmp(array('rs'=>$val,'url_rex'=>html_entity_decode($v['function_f'])));
					//dump($v['function_f']);
				$val[$v['name'].'_f']=@eval($v['function_f']);
			}

		}

		$list[$key]=$val;
	}

	return $list;
	
}

/**
* 用于后台，取数据表配置
* @param array $param
* @param string $param['table']		数据表名
*/
function get_table_cfg($param=array()){
	$table=$param['table'];
	$do=D('TableConfigView');
	$map['table_name']=$table;
	$map['isdefault']=1;
	
	$order='id desc';
	if($rs=$do->where($map)->order($order)->find()){
		if($rs['do']=='D') $rs['table']='TableConfig'.$rs['id'].'View';
		else $rs['table']=$table;
	}else{
		$rs['do']='M';
		$rs['table']=$table;
		$rs['pagesize']=20;
	}
	
	return $rs;
}

/**
* 取目录中所有下级目录
* @param array $param
* @param string $param['path']	要读取的目录路径
* @param array 返回所有目录
*/
function get_dir($param=array()){
			$path=$param['path'];
			$dir=new \Org\Util\Dir();
			$list=$dir->getList($path);
			//dump($list);
			/*
			if(!file_exists($path.'/readme.html') && count($list)>2 && $param['readme']==1) {
				file_put_contents($path.'/readme.html','');
				echo $path.'/readme.html<br>';
			}
			*/			
			
			$dirlist=array();
			foreach($list as $key=>$val){
				if(is_dir($path.'/'.$val) && $val!='.' && $val!='..' && $val!='.git'){
					//echo $val.'<br>';
					//$dirlist[$key]['name']=$val;
					//$dirlist[$key]['path']=$path.'/'.$val;	
				
					//$dirlist[$key]['dlist']=get_dir(array('path'=>$path.'/'.$val));
					//$dirlist=array_merge($dirlist,get_dir(array('path'=>$path.'/'.$val)));
					$res=get_dir(array('path'=>$path.'/'.$val));
					//dump($res);
					$dirlist=array_merge($dirlist,$res);
					//dump($dirlist);

				}elseif($val!='.' && $val!='..' && $val!='.git'){
					//echo $path.'/'.$val.'<br>';
					$dirlist[]=$path.'/'.$val;
				}
			}

			return $dirlist;
}


/**
* 自动加截目录中的PHP文件
* @param string $path 		要读取的目录
* @param string $listpath	文件列表文件，当该文件存在时，直接读取该文件不再读取目录
* @param string $ext 		要加载的文件后缀
*/
function auto_load($path,$listpath=null,$ext='.php'){
	if($listpath){
		$file=include_once($path.'/'.$listpath);
		foreach($file as $key=>$val){
			$file[$key]=$path.'/'.$val;
		}
	}else{
		$file=get_auto_load_file($path,$ext);
	}


	foreach($file as $val){
		include_once($val);		
	}
	return $file;
}

//配合上面函数使用
function get_auto_load_file($path,$ext='.php'){
	$dir=new \Org\Util\Dir();
	$list=$dir->getList($path);
	//dump($list);
	$result=array();
	foreach($list as $key=>$val){
		
		if(is_dir($path.'/'.$val) && !strstr($val,'.')){
			$tmp=get_auto_load_file($path.'/'.$val,$ext);	
			if(is_array($tmp)) $result=array_merge($result,$tmp);
		}else{
			if(substr($val,-4,4)==$ext) {
				//echo $path.'/'.$val.'<br>';
				$result[]=$path.'/'.$val;
				//include_once($path.'/'.$val);			
			}
		}
	}

	return $result;
	
}

/**
* 文件大小，用于模板使用
* @param integer $size
*/
function fsize($size){
	$result=number_format($size/1024,2);
	
	return($result);
}

/**
* 同implode,用于模板使用
*/
function arr_implode($param=array()){
	$ext=$param['ext']?$param['ext']:',';
	$result=implode($ext,$param['data']);
	return $result;
}

/**
* 同explode,用于模板使用
*/
function str_explode($param=array()){
	$ext=$param['ext']?$param['ext']:',';
	if(!empty($param['data'])) $result=explode($ext,$param['data']);
	return $result;
}

/**
* 将字符串分隔成数组并取第一个值
*/
function str_explode_first($str){
	$str=explode(',',$str);
	return $str[0];
}

/**
* query_str 不能使用时再用此函数
*/
function query_str_($query){
	$query=urldecode(html_entity_decode($query));
	if(strstr($query,'=')){
        $query=@explode('&',$query);
        foreach($query as $val){
        	$val=explode('=',$val);
        	$nquery[$val[0]]=$val[1];
        }
        //var_dump($query);
	}else{
		if(substr($query,0,1)=='/') $query=substr($query,1);
		$query=explode('/',$query);	
		for($i=0;$i<count($query);$i=$i+2){
			$nquery[$query[$i]]=$query[$i+1];
		}			
	}

    return $nquery;
}



/**
* 分页
* @param array $param
* @param string $param['cache_name']  	缓存名称，默认有效期有10分钟
* @param string $param['table']			要读取的数据表或视图模型
* @param string $param['do']			实例化类型 D 或 M
* @param integer $param['pagesize']		分页记录数
* @param string $param['fields']		要读取的字段
* @param string $param['order']			排序
* @param string $param['map']			where
* @param string $param['group']			SQL GROUP
* @param string $param['maxpage']		最多显示分页数量
* @param string $param['tagid']			启用ajax分页时，tagid 为请求的标签
* @param ingeter $param['is_mongo']		1 表示连接操作为mongodb
* @param bool|string $param['relation'] 关联数据模型
*/
//默认记录列表分页
function pagelist($param=array()){
		if($param['cache_name']){
			$result=S($param['cache_name']);
			if($result) return $result;
		}

		$table=$param['table']?$param['table']:CONTROLLER_NAME;
		$table_sort=$param['table_sort']?$param['table_sort']:$table.'_sort'; //数据分类表

		$param['do']=$param['do']?$param['do']:'M';
		//是否操作MnogoDB
		if($param['is_mongo']){
			$do=new \Think\Model\MongoModel(C('DB_PREFIX').$table,null,C('DB_MONGO_CONFIG'));
		}else{
			$do=$param['do']($table); //实例化数据表 D 或 M
		}
		//dump($do);

		$num=$param['pagesize']?$param['pagesize']:12; //每页记录数
		$fields=$param['fields']?$param['fields']:'';	//读取字段

		$order=$param['order']?$param['order'].' ,id desc':'id desc';  //排序
		//$order=I('request.order')?I('request.order'):$order;

		$group=$param['group']?$param['group']:'';

		$map=$param['map']?$param['map']:'';
		

		//$field=I('get.field')?I('get.field'):'name';  //默认搜索字段
		//if(I('get.q')) $map[$field]=array('like','%'.I('get.q').'%');
		
		if($param['type']=='sort'){
			if(I('request.sid')) $map['sid']=I('request.sid');
			else $map['sid']=0;
		}

		//dump($map);exit;

		if($param['attr']){
			$map['_string'] = 1;
			foreach ( $param['attr'] as $k =>$v ){
				$map['_string'] .= " AND (`attribute_id` like '%".$v."%')";
			}
		}
		//dump($map);
        if($param['is_mongo']){
            $count = $do->where($map)->count();
        }else{
            $count = $do->where($map)->group($group)->count();
        }
		$count=$param['count']?$param['count']:$count;
		//echo $do->getLastSQL();exit;
		//dump($param);
		/*
		if($param['maxpage']){ //最多显示页数
			$allpage=ceil($count / $num);
			if($allpage>$param['maxpage']) $count=$num*$param['maxpage'];
		}
		*/
		
		//print_r($param);
	
		$p = new \Think\Page($count, $num,$param['query'],$param['maxpage'],$param['action'],$param['p']);

		if($param['ajax']==1){
			if($param['page_js']) $p->setConfig('page_js',$param['page_js']);
			$page = $p->show_ajax_btn();
		}else{
			$page = $p->show_btn();
			//$result['wap_page']=$p->wap_btn();
		}
		
		$result['allpage']=$p->allpage();

        //当前页
        $thispage=$_GET['p']?$_GET['p']:1;
        $thispage=$param['p']?$param['p']:$thispage;
        $thispage=$thispage > $result['allpage'] ? $result['allpage'] : $thispage;
        $limit=$thispage.','.$num;

		//MnogoDB
		if($param['is_mongo']){ //MnogoDB
			$list =  $do->where($map)->order($order)->field($fields)->page($limit)->select();
		}elseif($param['relation']){
			$list =  $do->relation($param['relation'])->relationWhere($param['relationWhere'][0],$param['relationWhere'][1])->relationOrder($param['relationOrder'][0],$param['relationOrder'][1])->relationField($param['relationField'][0],$param['relationField'][1])->relationLimit($param['relationLimit'][0],$param['relationLimit'][1])->where($map)->order($order)->field($fields,$param['fields_type'])->group($group)->page($limit)->select();
		}else{
			$list =  $do->where($map)->order($order)->field($fields,$param['fields_type'])->group($group)->page($limit)->select();
		}
		//echo $do->getLastSQL();
		$result['sql']=$do->getLastSQL();
		//dump($_GET);

		//dump($list);


		$listnum=count($list);
		
		$result['list']=$list;
		$result['listnum']=$listnum;
		$result['allnum']=$count;
		$result['page']=$page;

        //用于新增的分页函数
        $result['pageinfo'] = ['pagesize' => $num,'p' => $thispage,'count' => $count,'page' => $result['allpage']];

		if($param['cache_name']) S($param['cache_name'],$result,$param['cache_time']);
		return $result;
		
}


/**
* 用于模板,输出数据中某字段
* @param string $v 	要输出的键名
* @param array $param 
*/
function field_out($v,$param=array()){
	$rs=CURD(array('table'=>$param['table'],'type'=>'F','map'=>array($param['key'],$v),'field'=>$param['field']));
	return $rs[$param['field']];
}



/**
*用于图片路径处理  七牛缩略图处理
* @param String $url 图片地址
* @param integer $param['w']  	宽度
* @param integer $param['h']	高度
* @param integer $param['t']	剪裁类型 1(等比)|2(按尺寸)|3
* @param integer $h 			等同于$param['h']
* @param integer $t 			等同于$param['t']
* @param string  $nopic 		当图片不存在时默认显示的图片
* @param integer $type 			七牛的另一种缩略图方式
*/

function myurl($url,$param=null,$h='',$t=2,$nopic='',$type=''){
	$nopic=$nopic?$nopic:'http://orxwhobqu.bkt.clouddn.com/FkwMsGFJX_eV_0jGDQHKswrI7pdC';
	if($_SERVER['HTTPS']=='on') $scheme='https://';
	else $scheme='http://';

	if(is_array($url)){
		$tmp_url=$url[0]?$url[0]:$url[1];
		$url=$tmp_url;
	}

	if(isset($param) && !is_array($param)) {
		$cfg['w']=$param;
		$cfg['h']=$h;
		$cfg['t']=$t;
		$param=$cfg;
	}

	if(empty($url)) $url=$param['nopic']?$param['nopic']:$nopic;
	
	$tmp=parse_url($url);

	$param['t']=$param['t']?$param['t']:2;
	$param['h']=$param['h']?$param['h']:$param['w'];
	

	if($tmp['scheme']=='http' || $tmp['scheme']=='https'){
		if($param['t'] && $param['w'] && $param['h']){
			if(strpos($tmp['host'],'.qiniucdn.com') || strpos($tmp['host'],'.clouddn.com') || strstr($tmp['host'],'pic.tangmall.net') || strstr($tmp['host'],'img.tangmall.net') || strstr($tmp['host'],'img.trj.cc')){
				//$url=$url.'?imageView2/'.$param['t'].'/w/'.$param['w'].'/h/'.$param['h'];
				if($type==1) $url=$url.'?imageView2/'.$param['t'].'/w/'.$param['w'].'/h/'.$param['h'];
				else $url=$url.'?imageMogr2/thumbnail/!'.$param['w'].'x'.$param['h'].'r/gravity/Center/crop/'.$param['w'].'x'.$param['h'];

				//?imageMogr2/thumbnail/!300x300r/gravity/Center/crop/300x300
			}elseif(strpos($tmp['host'],'dttx.com')){
                //return $url;
            }else{
				$url=$scheme.'www.'.C('DOMAIN').'/Thumb/index?src='.$url.'&w='.$param['w'].'&h='.$param['h'].'&zc='.$param['t'];
			}
		}

	}else{
		if($param['t'] && $param['w'] && $param['h']){
			$url=$scheme.'www.'.C('DOMAIN').'/Thumb/index?src='.$url.'&w='.$param['w'].'&h='.$param['h'].'&zc='.$param['t'];
		}
	}

	return $url;
}

/**
* 计算日期差
* @param date $eday 	结束日期
* @param string $t 		t=y年/m月/d日/h小时/i分钟/s秒
*/
function diff_date($eday,$t='s'){
	$d=new \Org\Util\Date();
    $result=$d->dateDiff($eday.' 23:59:59',$t);

    return $result;
}

/**
* 时间差计算格式化输出
* @param integer $time
*/
function diff_format($time){
	//时差8小时
	$time=$time-(8*3600);
	$result=getdate($time);

	$result['yday']=$result['yday']>0?$result['yday']:0;
	$result['hours']=$result['hours']>0?$result['hours']:0;
	$result['minutes']=$result['minutes']>0?$result['minutes']:0;
	$result['seconds']=$result['seconds']>0?$result['seconds']:0;
	
	$str='<span class="day">'.$result['yday'].'</span>天<span class="hours">'.$result['hours'].'</span>时<span class="minutes">'.$result['minutes'].'</span>分<span class="seconds">'.$result['seconds'].'</span>秒';
	return $str;
}


/**
* 最后一个钟倒计时
* @param integer $time
*/
function diff_task($time){
	//echo date('Y-m-d H:i:s',$time+3600).'<br>';
	$d=new \Org\Util\Date();
	$result=$d->dateDiff(date('Y-m-d H:i:s',$time+3600),'s');
	return $result;
}


/**
* 获取IP地址详情
* @param string $ip
*/
function iplocal($ip){
    $ip=new \Org\Net\IpLocation();
    $result=$ip->getlocation($ip);

    return $result;
}

/**
* 去除文件中的注释
* @param string $buffer
*/
function compress($buffer) {
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	return $buffer;
}

/**
* 获取n层上级资料
* @param array $param
* @param integer 	$param['level'] 	深度
* @param integer 	$param['uid'] 		上级ID
* @param integer 	$param['id'] 		当然用户ID
* @param string 	$param['field']		要读取的字段
*/
function up_user($param=null){
	$do=M('member');
	$n=$param['level'];
	
	$uid=$param['uid'];
	if(empty($uid)){
		$rs=$do->where(array('id'=>$param['id']))->field($param['field'])->find();
		$uid=$rs['uid'];
		if($uid==0) return $rs;
	}
	
	
	$i=1;
	while($uid>0){
		$lrs=$do->where(array('id'=>$uid))->find();
		$uid=$lrs['uid'];
		if($uid==0){
			break;
		}elseif($i==$n){
			//if($lrs['level']<$n) {
				//$lrs=up_user(array('id'=>$lrs['id'],'level'=>$param['level']));
				//break;
			//}
			break;
		}else{
			$i++;
		}

	}

	return $lrs;

}


/**
* 判断是否为微信浏览器
*/
function is_weixin(){ 
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }  
    return false;
}

/**
* 腾讯cos 文件显示
*/
function file_ext($file){
	$path=pathinfo($file);
	$ext=$path['extension'];
	
	switch($ext){
		case 'jpg':
			echo '<a class="zooming" href="'.myurl($file).'" title="'.basename($file).'"><img src="'.myurl($file).'"></a>';
		break;
		case 'jpeg':
			echo '<a class="zooming" href="'.myurl($file).'" title="'.basename($file).'"><img src="'.myurl($file).'"></a>';
		break;
		case 'gif':
			echo '<a class="zooming" href="'.myurl($file).'" title="'.basename($file).'"><img src="'.myurl($file).'"></a>';
		break;
		case 'png':
			echo '<a class="zooming" href="'.myurl($file).'" title="'.basename($file).'"><img src="'.myurl($file).'"></a>';
		break;
		default:
			echo $ext;
	}
}

/**
* 腾讯COS分页
*/
function cos_page($param=null){
	$p=$param['p'];
	$prev=$p-1>0?$p-1:1;
	$next=$p+1;
	
	$url=__CONTROLLER__.'/index?dir='.I('get.dir').'&updir='.I('get.updir');
	
	if($param['has_more']){
		$page='<a href="'.$url.'&p='.$prev.'" class="btn btn-default">上一页</a>';
		$page.='<a href="'.$url.'&p='.$next.'" class="btn btn-default">下一页</a>';
	}
	//dump($page);
	return $page;
}

/**
* 七牛分页
*/
function qiniu_page($param=null){
	$prev=$param['prev']?$param['prev']:'';
	$next=$param['next']?$param['next']:'';	
	
	$url=__CONTROLLER__.'/index';
	
	$page=$prev?'<a href="javascript:void(0)" onclick="back()" class="btn btn-rad btn-trans btn-default">上一页</a>':'<span class="btn btn-rad btn-trans btn-default disabled">上一页</span>';
	$page.=$next?'<a href="'.$url.'?p='.$next.'" class="btn btn-rad btn-trans btn-default">下一页</a>':'<span class="btn btn-rad btn-trans btn-default disabled">下一页</span>';

	//dump($page);
	return $page;
}


/**
* 商品SKU，插入数据库前格式化
* @param array $param  $_POST数据
*/
function price_insert_before($param=null){
	$color=array_unique(I('post.sku_color'));
	$colorid=array_values(array_unique(I('post.sku_colorid')));	
	$size=array_unique(I('post.sku_size'));
	$sizeid=array_values(array_unique(I('post.sku_sizeid')));

	
	if(empty($size)){	
		foreach($color as $key=>$val){			
			$item[$colorid[$key]]['colorid']=$colorid[$key];
			$item[$colorid[$key]]['color']=$val;
			$item[$colorid[$key]]['price']=$_POST['sku_price'][$key];
			$item[$colorid[$key]]['num']=$_POST['sku_num'][$key];
			$item[$colorid[$key]]['code']=$_POST['sku_code'][$key];
			$item[$colorid[$key]]['barcode']=$_POST['sku_barcode'][$key];
			
		}
	}else{
		$sizenum=count($size);
		foreach($color as $key=>$val){
			//echo $colorid[$key].'<br>';
			$item[$colorid[$key]]['colorid']=$colorid[$key];
			$item[$colorid[$key]]['color']=$val;	
			foreach($size as $vkey =>$v){
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['colorid']=$colorid[$key];
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['color']=$val;
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['sizeid']=$sizeid[$vkey];
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['size']=$v;
				
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['price']=$_POST['sku_price'][$key*$sizenum+$vkey];
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['num']=$_POST['sku_num'][$key*$sizenum+$vkey];
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['code']=$_POST['sku_code'][$key*$sizenum+$vkey];
				$item[$colorid[$key]]['dlist'][$sizeid[$vkey]]['barcode']=$_POST['sku_barcode'][$key*$sizenum+$vkey];
			}
		}
		
		
	}
	
	$data['color']=$color;
	$data['colorid']=$colorid;
	$data['size']=$size;
	$data['sizeid']=$sizeid;
	$data['item']=$item;
	
	//dump($data);exit;
	
	return 'return '.var_export($data,true).';';
}

/**
* 商品SKU，读取数据前格式化
* @param string $param 	修改SKU是从数据表读出的数据
*/
function price_read_before($param=null){
	$data=eval(html_entity_decode($param));
	//dump($data);
	return $data;
}



/**
* 运费模板，插入数据前格式化
* @param string $field 	字段名
*/
function express_insert_before($field,$param=null){
	$result['default']['first']=$_POST[$field.'_default_first'];
	$result['default']['price']=$_POST[$field.'_default_price'];
	$result['default']['addmore']=$_POST[$field.'_default_addmore'];
	$result['default']['pricemore']=$_POST[$field.'_default_pricemore'];
	
	foreach($_POST[$field.'_ids'] as $key=>$val){
		$result['item'][$key]['ids']=$val;
		$result['item'][$key]['names']=$_POST[$field.'_names'][$key];
		$result['item'][$key]['first']=$_POST[$field.'_first'][$key];
		$result['item'][$key]['price']=$_POST[$field.'_price'][$key];
		$result['item'][$key]['addmore']=$_POST[$field.'_addmore'][$key];
		$result['item'][$key]['pricemore']=$_POST[$field.'_pricemore'][$key];	
	}	
	return 'return '.var_export($result,true).';';
}



/**
* 时间转化 转化为时间戳，用于模板输出日期
* @param string $key 	$_POST中的键名
*/
function time_insert_before($key){
	$data=@strtotime($_POST[$key]);         
	return $data;
}

/**
* 时间转化 转化为Y-m-d H:i:s
* @param integer $time
*/
function time_read_before($time){
	$data=date('Y-m-d H:i:s',$time);               
	return $data;
}





/**
*取分类导航
* @param array $param
* @param string 	$param['table'] 		操作的数据表
* @param string 	$param['icon']   		分隔符ICON
* @param string 	$param['cache_name]  	缓存名称
* @param string 	$param['field']			读取字段
* @param string 	$param['link']			连接地址, U('/index/index',['id' => $rs['id']]);
*/

function nav_sort($param){
	if($param['cache_name']){		
		$html=S($param['cache_name']);
		if(!empty($html)) return $html;
	}
	
	$table=$param['table'];
	$icon=$param['icon']?$param['icon']:' <i class="fa fa-angle-right"></i> ';
	$field=$param['field']?$param['field']:'id,sid,name';
	$key=$param['key']?$param['key']:'name';
	$do=M($table);
	
	$rs=$do->where(array('id'=>$param['id']))->field($field)->find();	
	if($param['link']){
		//dump(urldecode($param['link']));
		$html='<a href="'.str_replace('[id]', $rs['id'], urldecode($param['link'])).'">'.$rs[$key].'</a>';
	}else $html=$rs[$key];
	
	if($rs['sid']>0){
		$param['id']=$rs['sid'];
		$html=nav_sort($param).$icon.$html;
	}
	
	if($param['cache_name']) S($param['cache_name'],$html);
	
	return $html;
}

/**
* 采了了淘宝API获取IP归属地
* @param string $ip 	IP地址
*/
function ip_local($ip=''){
	$ip=$ip?$ip:get_client_ip();	
	$local=objectToArray(json_decode(curl_file(array('url'=>C('IP_API').$ip))));
	return $local;
}


/**
*计算运费
* @param
* @param string 	$param['express'] 	运费设置内容
* @param integer 	$param['type'] 		计费方式
* @param integer 	$param['cityid'] 	城市ID
* @param float 		$param['weight'] 	重量
* @param integer 	$param['num'] 		件数
*/
function express_calc($param){
    $express=eval(html_entity_decode($param['express']));                       
    //默认运费 
    if($param['type']==1){ //按重量
        $price=$express['default']['price'];
        //计价方式
        $p=ceil(($param['weight']-$express['default']['first'])/$express['default']['addmore']);

        if($p>0){
            $price+=$p * $express['default']['pricemore'];
        }

        
        //只按续重的方式
        $price2=ceil($param['weight']/$express['default']['addmore']) * $express['default']['pricemore'];

        foreach($express['item'] as $v){
            if(in_array($param['cityid'],explode(',',$v['ids']))){
               	$price=$v['price'];
                //计价方式
                $p=ceil(($param['weight']-$v['first'])/$v['addmore']);
                if($p>0){
                    $price+=$p*$v['pricemore'];
                }

                $price2=ceil($param['weight']/$v['addmore']) * $v['pricemore'];                                   
                break;
            }
       	}          

    }else{ //按件数
        $price=$express['default']['price'];
        //计价方式
        $p=ceil(($param['num']-$express['default']['first'])/$express['default']['addmore']);
        if($p>0){
            $price+=$p*$express['default']['pricemore'];
        }



        //只按续重的方式
        $price2=ceil($param['num']/$express['default']['addmore']) * $express['default']['pricemore'];



        foreach($express['item'] as $v){
            if(in_array($param['cityid'],explode(',',$v['ids']))){
               	$price=$v['price'];

                //计价方式
                $p=ceil(($param['num']-$v['first'])/$v['addmore']);
                if($p>0){
                    $price+=$p*$v['pricemore'];
                }

                $price2=ceil($param['num']/$v['addmore']) * $v['pricemore'];     

                break;
            }
       	}  
    }

    $result['price']=$price;
    $result['price2']=$price2;

    return $result;
}


/**
* 购物车 商家统计运费
* @param integer $addressid  收货地址ID
* @param array $param  	附加参数
* @param integer $member 会员ID
* @return array
*/
function express_seller_total($addressid,$param=null,$memberid=''){
		$memberid=$memberid?$memberid:session('user.id');

        $do=M('address');
        $address=$do->find($addressid);

        $do=D('Cart/CartView');
        if($param['sellerid']) $arr['map']['sellerid']=$param['sellerid'];
        $arr['map']['is_select']=1;
        $cart=$do->cartList($memberid,$arr);

        $list=$cart['list'];
        foreach($list as $key=>$val){
            $list[$key]['express_price']=0;
            $list[$key]['ems_price']=0;


            $n1=0;
            $n2=0;

            //快递
            if($list[$key]['weight']>0){
                $result=express_calc(array(
                    'cityid'    =>$address['city'],
                    'type'      =>1,
                    'weight'    =>$list[$key]['weight'],
                    'express'   =>$list[$key]['express_weight']['express']
                ));
                $list[$key]['express_price']+=$result['price'];
            	$n1++;
            }            

            if($list[$key]['num']>0){
                $result=express_calc(array(
                    'cityid'    =>$address['city'],
                    'type'      =>0,
                    'num'    	=>$list[$key]['num'],
                    'express'   =>$list[$key]['express_num']['express']
                ));

                if($n1==0) $list[$key]['express_price']+=$result['price'];
                else $list[$key]['express_price']+=$result['price2'];
            	$n1++;
            }

            //ems
            if($list[$key]['is_ems']==1){
	            if($list[$key]['weight']>0){
	                $result=express_calc(array(
	                    'cityid'    =>$address['city'],
	                    'type'      =>1,
	                    'weight'    =>$list[$key]['weight'],
	                    'express'   =>$list[$key]['express_weight']['ems']
	                ));
	                $list[$key]['ems_price']+=$result['price'];
	            	$n2++;
	            }            

	            if($list[$key]['num']>0){
	                $result=express_calc(array(
	                    'cityid'    =>$address['city'],
	                    'type'      =>0,
	                    'num'    	=>$list[$key]['num'],
	                    'express'   =>$list[$key]['express_num']['ems']
	                ));

	                if($n2==0) $list[$key]['ems_price']+=$result['price'];
	                else $list[$key]['ems_price']+=$result['price2'];
	            	$n2++;
	            }            	
            }
            


            /*
            foreach($val['dlist'] as $vkey=>$v){
                if($list[$key]['is_express']==1){
                    if($v['is_express']==0) $list[$key]['is_express']=0;
                }
                if($list[$key]['is_ems']==1){
                    if($v['is_ems']==0) $list[$key]['is_ems']=0;
                }


                if($v['is_free']){                    
                    //快递
                    if($v['express'] && $v['is_express']) {
                        $result=express_calc(array(
                            'cityid'    =>$address['city'],
                            'type'      =>$v['type'],
                            'weight'    =>$v['weight_all'],
                            'num'       =>$v['num'],
                            'express'   =>$v['express']
                        ));

                        if($n1==0) $list[$key]['express_price']+=$result['price'];
                        else $list[$key]['express_price']+=$result['price2'];

                        $n1++;
                    }

                    //EMS
                    if($v['ems'] && $v['is_ems']) {
                        $result=express_calc(array(
                            'cityid'    =>$address['city'],
                            'type'      =>$v['type'],
                            'weight'    =>$v['weight_all'],
                            'num'       =>$v['num'],
                            'express'   =>$v['ems']
                        ));

                        if($n2==0) $list[$key]['ems_price']+=$result['price'];
                        else $list[$key]['ems_price']+=$result['price2'];

                        $n2++;
                    }
                }                
            }
            */

        }

        $cart['list']=$list;
        return $cart;
}


/**
* 获取某页面所有广告
* @param array $param 
* @param string $param['modules']  		分组模块
* @param string $param['controller'] 	控制器
* @param string $param['action']  		方法
* @return json格式字符串
*/
function ads($param=null){
	$modules=$param['modules']?$param['modules']:MODULE_NAME;
	$controller=$param['controller']?$param['controller']:CONTROLLER_NAME;
	$action=$param['action']?$param['action']:ACTION_NAME;

	$url=C('sub_domain.rest').'/Ad/ads/modules/'.$modules.'/controller/'.$controller.'/action/'.$action;

	$cache_name=md5($url);

	$result=S($cache_name);
	if(empty($result)){
		$result=curl_file(array('url'=>$url));
		S($cache_name,$result,60*20); //20分钟有效期
	}

	return $result;
}

/**
* 格式化日期时间
* @param integer $time 	时间戳
*/
function mdate($time = NULL) {
	$text = '';
	$time = $time === NULL || $time > time() ? time() : intval($time);
	$t = time() - $time; //时间差 （秒）
	$y = date('Y', $time)-date('Y', time());//是否跨年
	switch($t){
		case $t == 0:
			$text = '刚刚';
			break;
		case $t < 60:
			$text = $t . '秒前'; // 一分钟内
			break;
		case $t < 60 * 60:
			$text = floor($t / 60) . '分钟前'; //一小时内
			break;
		case $t < 60 * 60 * 24:
			$text = floor($t / (60 * 60)) . '小时前'; // 一天内
			break;
		case $t < 60 * 60 * 24 * 3:
			$text = floor($time/(60*60*24)) ==1 ?'昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time) ; //昨天和前天
			break;
		case $t < 60 * 60 * 24 * 30:
			$text = date('m月d日 H:i', $time); //一个月内
			break;
		case $t < 60 * 60 * 24 * 365&&$y==0:
			$text = date('m月d日', $time); //一年内
			break;
		default:
			$text = date('Y年m月d日', $time); //一年以前
			break;
	}

	return $text;
}

/**
* 根据信誉得分计算等级，参照淘宝信用评分
* @param integer $point 信用评分
* @return integer 
*/
function level($point){
	if($point<5) $level=0;
	if($point<11) $level=1;
	elseif($point<41) $level=2;
	elseif($point<91) $level=3;
	elseif($point<151) $level=4;
	elseif($point<251) $level=5;
	elseif($point<501) $level=6;
	elseif($point<1001) $level=7;
	elseif($point<2001) $level=8;
	elseif($point<5001) $level=9;
	else $level=10;

	return $level;
}


/**
* 店铺评分计算
* @param integer $sellerid 卖家用户ID
* @return array 
*/
function rate_store($sellerid){
	$do=M('evaluate_store');
	$count=$do->where(array('sellerid'=>$sellerid,'active'=>1))->count();

	$result['rate1']=5.0;
	$result['rate2']=5.0;
	$result['rate3']=5.0;
	$result['rate4']=5.0;
	$result['rate5']=5.0;
	$result['rate6']=5.0;
	$result['rate7']=5.0;

	$result['level_rate']=100;
	$result['level1']=0;
	$result['level2']=0;
	$result['level3']=0;
	$result['level_point']=0;
	$result['level']=0;
	
	if($count>0){
		$sum=$do->query('select sum(rate1) as rate1,sum(rate2) as rate2,sum(rate3) as rate3,sum(rate4) as rate4,sum(rate5) as rate5,sum(rate6) as rate6,sum(rate7) as rate7,sum(level1) as level1,sum(level2) as level2,sum(level3) as level3,sum(point) as point from '.C('DB_PREFIX').'evaluate_store where active=1 and sellerid='.$sellerid);

		$result['rate1']=($sum[0]['rate1']+50*5.0)/(50+$count);   //+50为基数，避免小于50条记录时评分的平均值变动太大
		$result['rate2']=($sum[0]['rate2']+50*5.0)/(50+$count);
		$result['rate3']=($sum[0]['rate3']+50*5.0)/(50+$count);
		$result['rate4']=($sum[0]['rate4']+50*5.0)/(50+$count);
		$result['rate5']=($sum[0]['rate5']+50*5.0)/(50+$count);
		$result['rate6']=($sum[0]['rate6']+50*5.0)/(50+$count);
		$result['rate7']=($sum[0]['rate7']+50*5.0)/(50+$count);
		$result['level_point']=$sum[0]['point'];		
		

		$result['level1']=$sum[0]['level1'];
		$result['level2']=$sum[0]['level2'];
		$result['level3']=$sum[0]['level3'];
		$result['level_point']=$sum[0]['point'];
		
		$result['level_rate']=($result['level1']+100)/(100+$result['level1']+$result['level2']+$result['level3'])*100;		
	}
	$result['level']=level($result['level_point']);

	
	return $result;
}

/**
* 宝贝评分计算
* @param integer $productsid  宝贝ID
* @return array 
*/

function rate_products($productsid){
	$do=M('evaluate');
	$count=$do->where(array('productsid'=>$productsid,'sid'=>1,'active'=>1))->count();

	$result['rate']=5.0;
	$result['rate_num']=0;
	
	$result['level_rate']=100;
	$result['level1']=0;
	$result['level2']=0;
	$result['level3']=0;


	if($count>0){
		$sum=$do->where(array('productsid'=>$productsid,'sid'=>1,'active'=>1))->sum('rate');

		$result['rate']=($sum+50*5.0)/(50+$count);
		$result['rate_num']=$count;
		
		$result['level1']=$do->where(array('productsid'=>$productsid,'level'=>1,'sid'=>1,'active'=>1))->count();
		$result['level2']=$do->where(array('productsid'=>$productsid,'level'=>2,'sid'=>1,'active'=>1))->count();
		$result['level3']=$do->where(array('productsid'=>$productsid,'level'=>3,'sid'=>1,'active'=>1))->count();

		$result['level_rate']=($result['level1']+100)/(100+$count)*100;		
	}
	

	return $result;
}

/**
* 买家好评率计算
* @param integer $memberid 买家ID
* @return array
*/
function rate_buyer($memberid){
	$do=M('evaluate_member');
	
	$count=$do->where(array('memberid'=>$memberid,'active'=>1))->count();
	
	$result['level_rate']=100;
	$result['level1']=0;
	$result['level2']=0;
	$result['level3']=0;
	$result['level_point']=0;
	$result['level']=0;
	if($count>0){
		$sum=$do->query('select sum(level1) as level1,sum(level2) as level2,sum(level3) as level3,sum(point) as point from '.C('DB_PREFIX').'evaluate_member where active=1 and memberid='.$memberid);
		
		$result['level1']=$sum[0]['level1'];
		$result['level2']=$sum[0]['level2'];
		$result['level3']=$sum[0]['level3'];
		$result['level_point']=$sum[0]['point'];
		
		$result['level_rate']=($result['level1']+100)/(100+$result['level1']+$result['level2']+$result['level3'])*100;

	}
	$result['level']=level($result['level_point']);
	return $result;	
}

/**
* 商家在售宝贝统计及店铺权重
* @param integer $sellerid	卖家ID
*/

function store_pr($sellerid){
	$do=M('products');
	$pr=0;
	$count=$do->where(array('sellerid'=>$sellerid,'active'=>1,'num'=>array('gt',0)))->count();

	if($count>4) $pr+=5;
	else{
		$pr+=$count;
	}

	if($pr>0){
		M('store')->where(array('memberid'=>$sellerid))->save(array('num'=>$count,'pr'=>$pr));
	}

}

/**
* 生成卖店铺域名连接
* @param integer $shop_id 店铺ID
* @param string $domain 卖绑定的店铺域名前缀
*/

function shop_url($shop_id,$domain=null){
	if($_SERVER['HTTPS']=='on') $scheme='https://';
	else $scheme='http://';

	$domain=$domain?$domain:$shop_id;
	$domain=$scheme.$domain.'.'.C('DOMAIN');
	return $domain;
}


/**
*发送手机短信,并返回数组
* @param array $api  手机短信接口
* @param array $param
* @param string $param['mobile'] 	接收短信的手机号码，可以是数组或半角逗号隔开的多个手机号码
* @param string $param['content'] 	要发送的内容，请注意内容不要太长，通常在60个字以内
* @return bool
*/
function sms_send($param,$api=null){
	$param['userid']	=$api['userid']?$api['userid']:C('cfg.sms')['userid'];
	$param['account']	=$api['account']?$api['account']:C('cfg.sms')['account'];
	$param['password']	=$api['password']?$api['password']:C('cfg.sms')['password'];	
	$param['action']	='send';
	if(is_array($param['mobile'])) $param['mobile']=@implode(',',$param['mobile']);

	$api=$api['apiurl']?$api['apiurl']:C('cfg.sms')['sms'];

	$res=curl_post($api,$param);
	$xml = simplexml_load_string($res);
	//print_r($res);
	if($xml->returnstatus=='Success'){	
		return true;
	}else{
		return false;
	}
}



/**
* 生成日期表，用于广告模块
* 生成最近12个月日历表
* @param array $param 
*
* @param date 		$param['sday'] 	广告开始时间
* @param array 		$param['days'] 	投放日期列表
* @param integer 	$param['isuse'] 投放日期是否已被占用
*/

function calendar($param=null){
    $year=date('Y');
    $month=date('m');

    if($param['sday']) {
    	$sday=explode('-',$param['sday']);
    	$year=$sday[0];
    	$month=$sday[1];
    }


    $date[]=array('year'=>$year,'month'=>$month,'days'=>$param['days'],'isuse'=>$param['isuse']);

    for ($i=0; $i < 11; $i++) { 
	    $month++;
	    if($month>12) {
	    	$month=1;
	    	$year++;
	    }
	    $date[]=array('year'=>$year,'month'=>$month,'days'=>$param['days'],'isuse'=>$param['isuse']);
    }

    foreach($date as $key=>$val){
    	$cal=new \Org\Util\Calendar($val);
    	$date[$key]['cal']=$cal->display();
    }

    return $date;

}




/**
* 用于广告模块,检查日期是否被其它用户使用
* @param array|string $dyas 日期列表,可以是数组，也可以是逗号分隔的日期列表
* @param integer $pid  广告位ID
* 返回正常日期和冲突日期
*/
function ad_days_check($days,$pid,$sort=0,$return=0){
	if(!is_array($days)) $days=@explode(',', $days);

	$do=M('ad');
	$map['status']=1;
	$map['position_id']=$pid;
	$map['eday']=array('gt',date('Y-m-d'));
	$map['sort']=$sort;

	$result['ok']=array();
	$result['no']=array();
	//取广告位投放中的记录
	if($list=$do->where($map)->field('days')->select()){
		$days_use=array();
		foreach($list as $val){
			$days_use=array_merge($days_use,@explode(',',$val['days']));
		}
		$days_use=array_unique($days_use);

		foreach($days as $val){
			if(in_array($val,$days_use)) $result['no'][]=$val;
			else $result['ok'][]=$val;
		}

		if(empty($result['no'])) $result['code']=true;
		else $result['code']=false;

		$result['days_use']=$days_use;

	}else{
		$result['code']=true;
	}

	//return $result;
	return $return==1?$result:$result['code'];
}

/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param string $user_name 姓名
 * @return string 格式化后的姓名
 */
function substr_cut($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}


/**
*格式化宝贝属性
*2015-11-27  by enhong
* @param array $param 从$_POST中获数据并格式化
* @return array
*/
function attr_cmp($param=null){
	//从$_POST数据中获取
	$result=array();
	foreach($param as $key=>$val){
		if(strstr($key,'attr_select_') || strstr($key,'attr_radio_')){
			$tmp=explode(':',$val);
			$result[$tmp[0]]=$val;
		}
		elseif(substr($key, 0,14)=='attr_checkbox_'){
			$tmp=explode(':',$val[0]);
			$result[$tmp[0]]=$val;
			//$result=array_merge($result,array($tmp[0]=>$val));
		}
	}


	//dump($result);exit;

	return $result;
}

/**
* 格式化输出宝贝属性
* 2015-11-28  by enhong
*
* @param string $attr  为字串格式的json数据
* @return array
*/
function attr_cmp_out($attr){
	$attr=objectToArray(json_decode(html_entity_decode($attr)));
	//dump($attr);
	$result=array();
	$ids=array();
	foreach($attr as $key=>$val){
		$ids[]=intval($key);
		if(is_array($val)){
			$tmp=array();
			foreach($val as $v){
				$tmp[]=@explode(':',$v)[2];
			}
			$result[intval($key)]['value']=@implode(',',$tmp);
		}else $result[intval($key)]['value']=@explode(':',$val)[2];
	}

	$do=M('attribute_sort');
	$list=$do->where(array('id'=>array('in',$ids)))->field('id,name')->order('sort asc')->select();

	foreach($list as $val){
		$result[$val['id']]['name']=$val['name'];
	}

	//dump($result);
	return $result;
}

/**
* 宝贝权重简单评估 - 后续再完善
* 资料完整性+最近7天销售量+最近7天人气+收藏数量
* @param integer $id  宝贝ID
*/

function goods_pr($id){
    if(!is_array($id)) $id = array($id);
	$do = M('goods');
	$list = $do->where(['id' => ['in',$id]])->getField('id,goods_name,category_id,fraction,brand_id,code,is_self,is_best,is_love,free_express,sale_num,view,fav_num,package_id,protection_id',true);

    //含有以下关键词将扣分
    $instr = ['补运费','补拍','补差价','运费补','补邮费'];

    $lcs = new \Org\Util\Lcs();
    $other_id = sortid(['table' => 'goods_category','sid' => 100845542]);
	foreach($list as $key => $val){
		$pr=0;

        if(!in_array($val['category_id'],$other_id)) {
            $title_len = strlen($val['goods_name']);
            if ($title_len > 20) $pr += 20;
            elseif ($title_len > 10) $pr += 15;
            else $pr += 1;

            foreach($instr as $v){
                if(strstr($val['goods_name'],$v)) $pr -= 1000;
            }

            //综合评价
            $pr += $val['fraction'];

            //品牌
            if ($val['brand_id']) {
                $pr += 0.5;
                $brand = M('brand')->where(['id' => $val['brand_id']])->getField('b_name');
                $tmp = $lcs->getSimilar($val['goods_name'], $brand);
                $pr += $tmp;
                //dump($tmp);
            }

            //属性
            $attr = M('goods_attr_value')->where(['goods_id' => $val['id']])->getField('attr_value',true);
            if(!empty($attr)){
                $tmp = $lcs->getSimilar($val['goods_name'], implode(',',$attr));
                $pr += $tmp;
                //dump($tmp);
            }

            //参数
            $option = M('goods_param')->where(['goods_id' => $val['id']])->getField('param_value',true);
            if(!empty($option)){
                $tmp = $lcs->getSimilar($val['goods_name'], implode(',',$option));
                $pr += $tmp;

                //dump($tmp);
            }

            //类目
            $category = M('goods_category')->where(['id' => $val['category_id']])->getField('category_name');
            $pr += $lcs->getSimilar($val['goods_name'], $category);

            if ($val['code']) $pr += 0.2;
            if ($val['package_id']) $pr += 0.1;
            if ($val['protection_id']) $pr += 0.1;
            //if ($val['is_self']) $pr += 1;
            if ($val['free_express']) $pr +=0.5;

            //橱窗推荐+10分
            if ($val['is_best']) $pr += 10;
            if ($val['is_love']) $pr += 0.1;

            //最近7天销售量

            //累计销量
            if($val['sale_num'] > 0) {
                $pr += (strlen($val['sale_num']) * 2) + ($val['sale_num'] / 1000) + 2;
            }

            //人气
            $pr += ($val['view'] / 10000);

            //商品收藏
            $pr += ($val['fav_num'] / 10000);

            //商品详情
            $content = M('goods_content')->where(['goods_id' => $val['id']])->getField('content');
            $content = html_entity_decode($content);
            preg_match_all("/<img([\s\S]*?)src=\"([\s\S]*?)\"/ies",$content,$images);
            if(empty($images[2])) $pr -= 10;

            //dump($pr);
        }
		$do->where('id='.$val['id'])->setField('pr',$pr);

		$result[$key] = round($pr,2);

	}
	return $result;
}

/**
* 店铺权重
* @param int $id 店铺ID
* @param int $uid 用户ID
*/
function shop_pr($id='',$uid=''){
	if($id=='' &&  $uid=='') return;

	if($id) $map['id']	= $id;
	elseif($uid)	$map['uid']	= $uid;

	$do = M('shop');
	$rs = $do->where($map)->field('id,goods_num,sale_num,fraction')->find();

	$pr = $rs['fraction'];

	//最近7天销售量

	//累计销量
	$pr += $rs['sale_num'] / 100;

	if($rs['goods_num'] > 4) $pr +=5;
	else $pr += $rs['goods_num'];

	$do->where($map)->setField('pr',$pr);

	return $pr;
}


/**
* 无限级分类生成下拉表单选项
* @param array $param
* $param['table']  	数据表
* $param['do']		实例化类型 M or D
* $param['map']		SQL条件
* $param['sid']		分类ID
*
* @param string $first   下接第一个选项
* @param string $cache_name	缓存名称
* @param integer $cache_time 缓存有效时间
*/
function option_create($param,$first=null,$cache_name=null,$cache_time=null){
	$cache_time=is_null($cache_time)?C('DATA_CACHE_TIME'):$cache_time;
	if($cache_name){
		if($result=S($cache_name)) return $result;
	}

	$result=option_data($param,$first);
	if($cache_name) S($cache_name,$result,$cache_time);

	return $result;
}

/**
* 生成表单选项，配合option_create函数使用
* @param array $param
* @param string 	$param['do']	实例化，M|D
* @param string 	$param['table']	要读取的数据表
* @param array 		$param['map']	where查询条件
* @param integer 	$param['sid']	分类ID
*
* @param array 		$first 			第一个选项的数据
* @param string 	$first['name']	option的text文本
* @param String 	$first['value'] option的value
*
* @param integer 	$level 			深度/级别
*/

function option_data($param,$first=null,$level=0){
	$ac=$param['do']?$param['do']:'M';  //实例化类型
	$do=$ac($param['table']);

	$map=$param['map']?$param['map']:'';
	$map['sid']=$param['sid']?$param['sid']:0;

	if(empty($param['key'])) $param['key']=array('id','name');

	if($first){
		$result='<option value="'.$first['value'].'">'.$first['name'].'</option>';
	}



	if($list=$do->where($map)->order('sort asc')->field($param['field'])->select()){
		$level++;
		$str='';
		if($level>1){
			for($i=0;$i<$level;$i++){
				$str.='　';
			}
			$str.='|— ';
		}
		foreach($list as $val){
			$result.='<option value="'.$val[$param['key'][0]].'">'.$str.$val[$param['key'][1]].'</option>';
			$param['sid']=$val[$param['key'][0]];
			$result.=option_data($param,'',$level);
		}
	}

	return $result;

}

/**
* 生成跳到ERP的连接
* @param string $url 要跳转的URL
* @param string $code 
*/
function erp_url($url,$code=''){
	if(empty($_SESSION['user']['erp_uid'])) return $url;

	$param['parterId']	=C('cfg.erp')['pid'];
	$param['userID']	=session('user.erp_uid');
    if (session('user.sub_id')) return DM('www', '/noAccess');
	ksort($param);
	$query=http_build_query($param).'&'.C('cfg.erp')['sign'];
	$param['signValue'] = md5($query);
	$res = curl_post(C('cfg.erp')['apiurl'].'/auth.json',$param);
	$res = json_decode($res);

	if($res->id ==1001){
		$url = C('cfg.erp')['apiurl'].'/authLogin.json?token='.$res->info.'&redirect_url='.urlencode($url);
		return $url;
	}else{
		return $url;
	}
}


/**
* 生成卖家客服连接
* @param array 	$param
* @param string 	$param['qq']  		qq号码
* @param string 	$param['wang'] 		旺旺号
* @param string 	$param['username'] 	昵称
* @param string 	$param['shop_name'] 	店铺名称
*/
function im_link($param){
	$html='';
	if($param['qq']) $html.='<a href="http://wpa.qq.com/msgrd?v=3&uin='.$param['qq'].'&site=qq&menu=yes" target="_blank" title="'.$param['qq'].'"><img src="/Public/images/chat_qq.gif" border="0"></a> ';
	if($param['wang']) $html.='<a href="http://amos1.taobao.com/msg.ww?v=2&uid='.$param['wang'].'&s=2" target="_blank" title="'.$param['wang'].'"><img alt="点击这里给我发消息" src="http://amos1.taobao.com/online.ww?v=2&uid='.$param['wang'].'&s=2" align="absBottom" border="0"></a> ';
	if($param['username']) $html.=$param['username'];
	if($param['shop_name']) $html.='<div><a href="'.shop_domain($param['memberid'],$param['domain']).'" target="_blank">'.$param['shop_name'].'</a></div>';

	return $html;
}




/**
* 后台雇员操作日志
* @param string $table  数据表
* @param string $sql    SQL词句
* @param integer $result  1|0,1表示执行成功,0执行失败
* @param integer $insid   插入数据成功后返回的ID
*/
function admin_log($table,$sql,$result='',$insid=''){
	preg_match('/^(UPDATE|INSERT|DELETE)\s*/ies',$sql,$out);

	$action=array('UPDATE','INSERT','DELETE'); //只记录三种操作

	if(in_array($out[1],$action)){
		$data=array(
			'session_id'=>session_id(),
			'atime'		=>date('Y-m-d H:i:s'),  		//时间
			'ip'		=>get_client_ip(),				//IP
			'userid'	=>session('admin.id'),			//雇员ID
			'name'		=>session('admin.username'),		//雇员姓名
			'table'		=>substr($table,1,-1),			//数据表
			'type'		=>$out[1],						//操作类型 UPDATE|INSERT|DELETE
			'sql'		=>$sql,							//SQL
			'res'		=>$result,						//操作结果 1成功,0失败
			'insid'		=>$insid,						//INSERT成功返回的ID
			'url'		=>__SELF__,
			'modules'	=>MODULE_NAME,
			'controller'=>CONTROLLER_NAME,
			'action'	=>ACTION_NAME
		);

		$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'admin',null,C('DB_MONGO_CONFIG'));
		$do->add($data);


		//$data=session('admin.id').'|'.session('admin.username').'|'.substr($table,1,-1).'|'.$out[1].'|'.$result.'|'.get_client_ip().'|'.date('Y-m-d H:i:s').'|'.$sql;
		//Think\Log::record($data,Think\Log::SQL,true);
	}


}


/**
*是否移动端访问访问
*@return bool
*/
function isMobile(){ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    } 

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){ 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 

    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array (
        	'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 

        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        }
    } 

    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])){ 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
        } 
    } 
    return false;
}


/**
* curl post数据
* @param string $url
* @param array $data  要POST的内容
* @param array 额外参数
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
        $res = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        return $res;        
}

/**
* 解决分布式生成文件同步出错问题
* 将文件写入到指定同步的源服务器后将自动同步到其它服务器
* @param array $param  要POST提交的内容
* @param array $api    api接口参数
*/
function sync_file_upload($param,$api=null){
    $param['appid'] =$api['appid']?$api['appid']:'300020160217';
    $param['secret']=$api['secret']?$api['secret']:'4c2d580154fd71bfa9aac2c2bb19b23d';
    $apiurl=$api['apiurl']?$api['apiurl']:'https://up.yunlianhui.com/Upload/savefile';

    $res=curl_post($apiurl,$param);
    return $res;
}


/**
* 取得记录集中某键值的数据记录，适合记录取分类，最好加上缓存
* @param array $param
* @param string 	$param['table']		要读取的数据表
* @param array 		$param['map']		WHERE
* @param string 	$param['field']		读取字段
* @param string 	$param['cache_name']缓存名称
* @param integer 	$param['cache_time']缓存有效期
* @param string 	$param['key']
* @param array 		$param['return']    要返回的字段数据
*/
function get_key_by_list($param){	
	$cache_name=$param['cache_name']?$param['cache_name']:'';
	$cache_time=$param['cache_time']?$param['cache_time']:'';
	$field=$param['field']?$param['field']:'id,name';
	$map=$param['map']?$param['map']:'';

	if($cache_name) $list=S($cache_name);
	if(empty($list)) {
		$list=M($param['table'])->where($map)->getField($field);
		if($cache_name) S($cache_name,$list,$cache_time);
	}

	/*
	if($param['return'] && is_null($param['list'])){
		if(is_array($param['return'])){
			$result=array();
			foreach($param['return'] as $key=>$val){
				$result[$key]=$list[$val];
			}
			return $result;
		}
		else return $list[$param['return']];
	}elseif(is_array($param['return']) && $param['list']){
		foreach($param['list'] as $vkey=>$vo){
			$result=array();
			foreach($param['return'] as $key=>$val){
				$result[$key]=$list[$vo[$val]];
			}

			$param['list'][$vkey]=array_merge($vo,$result);
		}

		return $param['list'];
	}
	*/

	return $list[$param['key_val']];
}

/**
* 数据验证-用于配合TP数据模型自动验证
* @param string $value 要验证的数据
* @param string|array 验证的方法及参数  
*/
function checkform($value,$action){
	if(is_array($action)){
		if(is_array($action[0])){
			foreach($action as $val){
				if(!checkform_($value,$val)) return false;
			}
		}
	}
	return checkform_($value,$action);
}
function checkform_($value,$action){
	$check=new \Org\Util\CheckData($value);

	if(is_array($action)) {
		$ac=$action[0];
		array_shift($action);
		return call_user_func_array(array($check,$ac),$action);
	}
	else return $check->$action();
	
}
/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 单位 秒
 * @return string
 */
function think_encrypt($data, $key = '', $expire = 0)
{
    $key = md5(empty($key) ? C('CRYPT_PREFIX') : $key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key 加密密钥
 * @return string
 */
function think_decrypt($data, $key = '')
{
    $key = md5(empty($key) ? C('CRYPT_PREFIX') : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 获取站点配置文件
 * $key 配置中的某项
 * @return mixed
 */
function getSiteConfig($key = null) {
    $data = S('CAHCE_SITECONFIG');
    if (!$data) {
        $cfg = D('Common/Config')->config();
        $cfg['erp']['domain']=eval(html_entity_decode($cfg['erp']['domain']));
        $data = serialize($cfg);
        S('CAHCE_SITECONFIG', $data);
    }
    $data = unserialize($data);
    if (is_null($key)) {
        return $data;
    }
    return $data[$key];
}

/**
 * 获取API配置
 * @return mixed
 */
function getApiCfg() {
    $config = getSiteConfig();
    $apiCfg = $config['api'];
    unset($apiCfg['apiurl']);
    return $apiCfg;
}

/**
 * 生成签名
 * @param array $data 要进行签名的数据
 * @param string $field 要进行签字的键名,为空是表是所有key
 * @param integer $type　1表示$field为不进行签名的Key
 */
function _sign($data,$field='',$type=1){
    //$data=array_merge($data,$this->api_cfg);
    //清除不进行签名的字段
    if(isset($data['random'])) unset($data['random']);	//随机码不加入签名

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
    $cfg = getApiCfg();
    $query=http_build_query($data).'&'.$cfg['sign_code'];
    $query=urldecode($query);
    return md5($query);
}
/**
 * 获取二级域名
 * @param string $prefix   二级域名
 */
function DM($prefix = '', $uri = null) {
    $rulus  =   C('APP_SUB_DOMAIN_RULES');
    $scheme =   $_SERVER['HTTPS']=='on'?'https://':'http://';
    //$host   =   substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.'));
    $host   =   C('DOMAIN');
    //dump($host);
    /*if (!array_key_exists($prefix, $rulus)) {
        $uri    =   '/sorry' . C('TMPL_TEMPLATE_SUFFIX');
        return $scheme . 'www' . $host . $uri;
    }*/
    if ($uri) $uri .= C('TMPL_TEMPLATE_SUFFIX');
    if ($prefix) $prefix .= '.';
    return $scheme . $prefix . $host . $uri;
}

/**
 * 生成各频道域名连接
 */
function sub_domain(){
    if($_SERVER['HTTPS']=='on') $scheme='https://';
    else $scheme='http://';

    $result['www']=$scheme.C('DOMAIN');

    foreach(C('APP_SUB_DOMAIN_RULES') as $key=>$val){
        $result[$key]=$scheme.$key.'.'.C('DOMAIN');
    }

    return $result;
}
/**
 * 加密接口URI
 * @return string
 */
function enCryptRestUri($action = null) {
    $actions    =   $action != null ? $action : __ACTION__;
    return think_encrypt($actions, C('CRYPT_PREFIX'));
}

/**
 * 解密接口URI
 * @param unknown $data
 * @return string
 */
function deCryptRestUri($data) {
    return think_decrypt($data, C('CRYPT_PREFIX'));
}
/**
 * 写日志
 * @param unknown $text
 */
function writeLog($text) {
    if (C('APP_WRITE_TEXT_LOG') == false) {
        //return;
    }
    $fp = fopen('log'.date('Ymd', NOW_TIME).'.txt', 'a+');
    if (!is_string($text)) {
        $text   =   var_export($text, true);
    }
    fwrite($fp, $text . "\r\n");
    fclose($fp);
}

/**
 * 隐藏字符串
 * @param string $str
 * @param number $start     开始位置 字符真正位数-2
 * @param number $end      	字符结尾保留位数
 */
function hiddenStr($str, $start = 2, $end = 3) {
    $length =   (mb_strlen($str, 'utf8') - $end);
    for ($i = 0; $i < $length; $i++) {
        if ($i > ($start)) {
            $str[$i]    =   '*';
        }
    }
    return $str;
}

/**
 * 显示提示内容
 * @param unknown $str
 * @return string
 */
function htmlTips($str) {
    return '<div style="text-align:center;margin-top:200px;">'.$str.'</div>';
}

/**
 * 分页处理
 * @param string $data  分页数据
 * @param string $replace 要替换的action
 * @param string $action 替换成后的action
 */
function paresePageAction($data, $replace, $action = __ACTION__) {
    /*if (is_array($action)) {
        $str = '/';
        if (isset($action['p'])) unset($action['p']);
        foreach ($action as $k => $v) {
            $str .= $k . '/' . $v . '/';
        }
        $action =   __ACTION__ . rtrim($str, '/');
    } else {
        $url    =   U(__ACTION__, $_GET);
        if (strpos(strtolower($action), strtolower(__ACTION__)) === false) {
            $action .=   strstr($url, '.html', true);
        } else {
            $action =   strstr($url, '.html', true);
            $action =   preg_replace('/\/p\/(\d)+/', '', $action);
        }
    }*/
    if (isset($_GET['p'])) {
        unset($_GET['p']);
    }
    $get  =   array_unique($_GET);
    $qu  =   http_build_query($get);
    if ($replace != $action) {
        //return str_replace($replace, $action, $data);;
    }
    return preg_replace("/href=\"([^>]*)\s*\">/", 'href="${1}?' . $qu . '">', $data);
}

/**
 * 模态框加载页面
 * @param unknown $data
 * @param unknown $replace
 * @param unknown $reTo
 * @return mixed
 */
function paresePageActionAjax($data, $replace, $reTo) {
    if (isset($_GET['p'])) {
        unset($_GET['p']);
    }
    $get  =   array_unique($_GET);
    $qu  =   http_build_query($get);
    return preg_replace("/href=\"([^>]*)\s*\">/", $reTo . '${1}?' . $qu . '">', $data);
}

/**
 * 品牌添加签名处理
 * @param array     $data   post数据
 * @param string    $field  字段
 * @return string
 */
function brandChangeNosign($data, $field = null) {
    $nosign =   '';
    if (!empty($data['b_code'])) {
        $nosign =   'b_images2';
    } elseif (empty($data['b_code'])) {
        $nosign =   'b_code,b_images';
    }
    unset($data, $field);
    return $nosign;
}

/**
 * 商品属性不需要签名的字段
 * @param unknown $data
 * @param string $field
 * @return string
 */
function changeSkuNosign($data, $field = null) {
    $nosign =   'price_market,price_purchase,weight,code,barcode';
    return $nosign;
}

/**
 * 忘记密码缓存数据
 * @param array $data   要缓存的数据
 * @param string $name  缓存名称
 * @param int $expire   缓存时长
 */
function forgetPass($data, $name, $expire = 3600) {
    $name   =   'forgetPass_' . $name;
    if(S($name, $data) == false) {
        return false;
    }
    return true;
}

/** 
* 二维数组中某字段进行字符截取
* @param array $list 二维数组
* @param string $cut 截取配置，如：content,100|remark,100 即content和remark截取100个字符
*/
function cut_list($list,$cut){
	$cut=explode('|',$cut);
	foreach($list as $i=>$val){
		foreach($cut as $v){
			$cut_cfg=explode(',',$v);

			if($val[$cut_cfg[0]]) $list[$i][$cut_cfg[0]]=msubstr(strip_tags(html_entity_decode($val[$cut_cfg[0]])),0,$cut_cfg[1]);
		}
	}
	return $list;
}

/**
* 数组中某图片字段缩略图尺寸设置
* @param array $list 		数组
* @param string|array $key 	字段名
* @param int|array $width  	图片宽度
* @param int|array $height 	图片高度
*/
function imgsize_list($list,$key='images',$width=100,$height='',$t=2,$nopic='',$type=''){
	$height = $height!=''?$height:$width;

	$key 	= is_array($key) ? $key : explode(',',$key);

	foreach($list as $ikey => $val){
		if(is_array($val)){
			$list[$ikey] = imgsize_list($val,$key,$width,$height);
		}else{
			foreach($key as $i => $v){
				if($ikey==$v) {
					$list[$ikey.'_'] 	= $list[$ikey];
					$list[$ikey] 		= imgsize_item($val,is_array($width)?$width[$i]:$width,is_array($height)?$height[$i]:$height,$t,$nopic,$type);
					
				}
			}
		}
	}
	return $list;
}
/**
* 图片格式化生成缩略图
* @param string|array $url 图片url,多张可以用数组传入或逗号隔开
*/
function imgsize_item($url,$width,$height='',$t=2,$nopic='',$type=''){
	if(empty($url)) return ;	
	$height = $height!='' ? $height : $width;

	if(!is_array($url) && !strpos($url,',')){
		return myurl($url,$width,$height,$t,$nopic,$type);
	}

	if(!is_array($url) && strpos($url,',')) $url = explode(',',$url);

	foreach($url as $val){
		if($val){
			$result[]	=	myurl($val,$width,$height,$t,$nopic,$type);
		}
	}

	return $result;
}

/** 
* 图片尺寸格式化，返回数组
* @param string|array $url 图片url,多张可以用数组传入或逗号隔开
*/
function imgsize_cmp($url,$width,$height='',$t=2,$nopic='',$type=''){
	if(empty($url)) return ;	
	$height = $height!='' ? $height : $width;

	if(!is_array($url) && !strpos($url,',')){
		$url = array($url);
	}elseif(strpos($url,',')) $url = explode(',',$url);

	foreach($url as $val){
		if($val){
			$result[]	=	myurl($val,$width,$height,$t,$nopic,$type);
		}
	}

	return $result;
}

/**
 * 解析多为数组 CURL不支持二维数组提交，只能将数组用http_build_query转成字符串提交
 * @param 数据源 $data
 * @param 字段  $fields
 * @return string
 */
function httpBuilder($data, $fields) {
    foreach($fields as $val){
        if(isset($data[$val])) $data[$val]=http_build_query($data[$val]);
    }
    return $data;
}

/**
 * 获取用户ID
 * @return Ambigous <mixed, NULL, unknown>|boolean
 */
function getUid() {
    if (isset($_SESSION['user'])) {
        return session('user.id');
    }
    return false;
}




/**
 * 获取一级类目
 */
function getLevelOne($param){
	$table = $param['table'];
	$field = $param['field'];
	$where = $param['where'];
	$list = M($table)->where($where)->select();
	$data = array();
	foreach ($list as $ko => $vo) {
		$data[$vo['id']] = $vo;
	}
	return $data;
}
/**
 * 数组无限级分类树
 * @param array $Set 初始数据集合
 * @return array
 */
function arrayTrees(array $Set, $now = 0, $id = 'id', $pid = 'pid'){
	$tree = array();
	foreach($Set as $ko => $vo){
		if($vo[$pid] == $now){
			$tree[] = $vo;
			unset($Set[$ko]);
		}
	}
	if(! empty($tree)){
		foreach($tree as $k => $v){
			$arr = arrayTrees($Set, $v[$id], $id, $pid);
			if(! empty($arr)){
				$tree[$k]['child'] = $arr;
			}
		}
	}
	return $tree;
}
/**
 * 获取下级所有分类id
 * @param array $Set 初始数据集合
 * @return array
 */
function childArray(array $Set, $option = array()){
	$now 	= $option['now'] 	? $option['now'] 	: 0;
	$id 	= $option['id'] 	? $option['id'] 	: 'id';
	$pid 	= $option['pid'] 	? $option['pid'] 	: 'pid';
	$tree 	= [];

	foreach($Set as $ko => $vo){
		if($vo[$pid] == $now){
			$tree[] = $vo[$id];
			unset($Set[$ko]);
		}
	}
	if(! empty($tree)){
		foreach($tree as $v){
			$arr = childArray($Set, array('now' => $v,'id' => $id, 'pid' => $pid));
			if(! empty($arr)){
				$tree = array_merge($tree, $arr);
			}
		}
	}
	return $tree;
}

/**
 * 获取认证证书
 * @return mixed
 */
function getCategoryCert() {
    $data   =   S('goods_category_cert_lists');
    if (!$data) {
        $data   =   M('goods_category_cert')->where(['status' => 1])->getField('id,cert_name');
        $data   =   serialize($data);
        S('goods_category_cert_lists', $data);
    }
    return unserialize($data);
}
/**
 * 生成订单号
 * @param number $length
 */
function createOrdersNumber($length = 10) {
    $str    =   date('YmdHis', NOW_TIME);
    for ($i = 0; $i < $length; $i++) {
        $str .= rand(0, 9);
    }
    return $str;
}

/**
* 添加mongo日志
* @param string $table 表名
* @param array  $data 日志内容(一维数组)
*/
function log_add($table,$data){
	if(empty($table) || empty($data) || !is_array($data)) return false;

	$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').$table,null,C('DB_MONGO_CONFIG'));
	if($do->add($data)) return true;
	else return false;
}

/**
 * 获取银行列表
 * @param string $bankId 银行ID，有值的话则直接返回银行名称
 * @return mixed
 */
function getBank($bankId = null) {
    $data   =   S('cache_work_bank');
    if (!$data) {
        $data   =   M('bank_name')->order('id asc')->getField('id,bank_name');
        $data   =   serialize($data);
        S('cache_work_bank', $data);
    }
    $data   =   unserialize($data);
    if ($bankId > 0) {
        return $data[$bankId];
    } else {
        return $data;
    }
}

/**
 * 获取店铺类型
 * @param string $typeId
 * @return mixed
 */
function getShopType($typeId = null) {
    $data   =   S('cache_work_shop_type');
    if (!$data) {
        $data   =   M('shop_type')->field('atime,ip', true)->order('id desc')->select();
        $data   =   serialize($data);
        S('cache_work_shop_type', $data);
    }
    $data   =   unserialize($data);
    if ($typeId) {
        $current    =   [];
        foreach ($data as $key => $val) {
            if ($val['id'] == $typeId) {
                $current    =   $val;
            }
        }
        unset($val,$data);
        return $current;
    } else {
        return $data;
    }
}

/**
 * 获取能够开店选择的分类
 * @return mixed
 */
function getOpenShopCategory() {
    $data   =   S('seller_opens_getCategory');
    if (!$data) {
        $field  =   'id,category_name';
        $data   =   M('goods_category')->where(['status' => 1, 'sid' => 0])->order('sort asc')->field($field)->select();
        foreach($data as &$val) {
            $val['child']  =   (M('goods_category')->where(['status' => 1, 'sid' => $val['id']])->order('sort asc')->getField($field));
        }
        $data   =   serialize($data);
        S('seller_opens_getCategory', $data);
    }
    return unserialize($data);
}

/**
 * 判断是否有不能开店的类目
 * @param unknown $cate
 * @return boolean
 */
function checkOpensShopCategory($cate) {
    $cates  =   [];
    if (is_string($cate)) {
        $cate   =   explode(',', $cate);
    }
    foreach (getOpenShopCategory() as $k => $v) {
        foreach ($v['child'] as $key => $val) {
            if (in_array($key, $cate)) {
                $cates[]    =   $key;
            }
        }
    }
    $flag   =   array_diff($cate, $cates);
    unset($cate,$k,$key,$v,$val,$cates);
    if (empty($flag)) { //如果相等则为空
        return true;
    }
    return false;
}

/**
 * 返回品牌数量个数错误信息
 * @return string
 */
function shopOpensCheckBrandNum() {
    $typeId   =   M('shop_join_contact')->where(['uid' => getUid()])->getField(['type_id']);
    $shopType =   getShopType($typeId);
    return $shopType['type_name'] . '最多只能添加' . $shopType['max_brand'] . '个品牌';
}
/**
 * 返回开店类目错误信息
 * @return string
 */
function shopOpensCheckCateNum() {
    $typeId   =   M('shop_join_contact')->where(['uid' => getUid()])->getField(['type_id']);
    $shopType =   getShopType($typeId);
    return $shopType['type_name'] . '最多只能选择' . $shopType['max_category'] . '个分类';
}

/**
 * 判断商标类型
 * @param unknown $var
 * @param unknown $data
 * @return string
 */
function checkRegDataMsg($var, $data = []) {
    if ($var == 1) {
        if (empty($data[0])) {
            return '商标注册人不能为空!';
        }
        if (empty($data[1])) {
            return '商标注册号不能为空！';
        }
    } elseif ($var == 2) {
        if (empty($data[0])) {
            return '商标申请人不能为空!';
        }
        if (empty($data[1])) {
            return '商标申请号不能为空！';
        }
    }
}

/**
 * 获取活动类型
 * @param int $id   传ID过来的话则返回单条记录
 * @return Ambigous <mixed, object>
 */
function getActivityType($id = null) {
    $data   =   S('cache_activity_type');
    if (!$data) {
        $data   =   M('activity_type')->where(['status' => 1])->field('atime,etime,status', true)->order('id asc')->select();
        $data   =   serialize($data);
        S('cache_activity_type', $data);
    }
    $data   =   unserialize($data);
    if ($id) {
        $tmp    =   [];
        foreach ($data as $v) {
            if ($v['id'] == $id) {
                $tmp    =   $v;
            }
        }
        unset($data,$v);
        if (!empty($tmp)) {
            return $tmp;
        } else {
            return false;    
        }
    } else {
        //获取店铺类型
        $shopType   =   M('shop')->cache(true)->where(['uid' => getUid()])->getField('type_id');
        foreach ($data as $k => $v) {
            if (strpos($v['shop_type'], "$shopType") === false) {
                unset($data[$k]);   //店铺类型是否不允许此活动
            }
        }
        return $data;
    }
}

/**
 * 获取参与活动赠送的商品
 * @param string $goods 商品ID
 * @param int $shop 店铺ID
 * @return Ambigous <mixed, boolean, multitype:, unknown, object>
 */
function getActivityFullvalueGoods($goods, $shop) {
    return D('GoodsRelation')->relation(true)->cache(true,C('CACHE_LEVEL.L'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url')->relationLimit('goods_attr_list',1)->where(['id' => ['in', $goods, 'status' => 1, 'shop_id' => $shop]])->field('id,goods_name,images,price,sale_num,shop_id,seller_id')->select();
}

/**
 * 获取当前商品参与的包邮、满就送、满就减、唐宝支付折扣、消费累积升级活动
 * @param unknown $activitys
 * @param unknown $shopId
 * @return Ambigous <mixed, boolean, multitype:, unknown, object>
 */
function getActivityGoods($shopId, $isCartView = false) {
    $map   =   [
        //'id'    =>  ['in', $activitys],
        'type_id'       =>  ['in', '1,2,3,4,7'],
        'start_time'    =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],    //开始时间小于当前时间
        'end_time'      =>  ['egt', date('Y-m-d H:i:s', NOW_TIME)],      //结束时间大于当前时间
        'status'        =>  1,
        'shop_id'       =>  $shopId,
    ];
    $data  =   D('ActivityView')->where($map)->field('activity_name,id,type_id,start_time,end_time,shop_id,full_money,full_value,max_num,sku_num,sale_num,highest,is_accumulation')->order('atime desc')->group('type_id')->select();
    $title = '本商品';
    if ($isCartView == true) $title = null;
    foreach ($data as &$val) {
        switch ($val['type_id']) {
            case 1:
                $val['desc']    =   $val['full_money'] > 0 ? $title . '满 <strong class="text_red">' . $val['full_money'] .'</strong> ' . '免邮费' : '本商品免邮费';
                break;
            case 2:
                $val['desc']    =   $val['full_money'] > 0 ? $title . '满 <strong class="text_red">' . $val['full_money'] . '</strong> 就送' : '购买本商品即送';
                $val['goods']   =   getActivityFullvalueGoods($val['full_value'], $val['shop_id']);
                break;
            case 3:
                $val['desc']    =   $val['full_money'] > 0 ? $title . ($val['is_accumulation'] == 1 ? '每满 ' : '满 ').'<strong class="text_red">' . $val['full_money'] . '</strong> 减 <strong class="text_red">' . number_format($val['full_value'], 2) . '</strong> ': (is_null($title) ? '' : '购买本商品') . '立减 <strong class="text_red">' . number_format($val['full_value'], 2) . '</strong>';
                if ($val['is_accumulation'] == 1) {
                    if ($val['highest'] > 0) {
                        $val['desc'] .= ' 最高可减 <strong class="text_red">' . $val['highest'] . ' </strong>元';
                    } else {
                        $val['desc'] .= ' <strong class="text_red">上不封顶</strong>';
                    }
                }
                break;
            case 4:
                $val['desc']    =   '使用唐宝支付享  <strong class="text_red">' . $val['full_value'] . '</strong> 折优惠';
                break;
            case 7:
                $val['desc']    =   '<strong class="text_red">在线消费，可累积升级（唐宝支付的金额不参与累积）</strong>';
                break;
        }
    }
    return $data;
}

/**
 * 获取店铺ID
 * @return Ambigous <mixed, NULL, unknown, multitype:Ambigous <string, unknown> unknown , object>
 */
function getShopId() {
    return M('shop')->cache(true)->where(['uid' => getUid()])->getField('id');
}

/**
 * 中文隐藏
 * @param unknown $str
 * @param number $start
 * @param number $end
 * @return string
 */
function hiddenChineseStr($str, $start = 1, $end = 1) {
    $length =   mb_strlen($str);
    $s      =   mb_substr($str, 0, $start);
    $e      =   mb_substr($str, ($end * -1), $end);
    $hLen   =   $length - ($start + $end);
    if ($hLen > 0) {
        $str    =   '';
        for ($i = 0; $i < $hLen; $i++) {
            $s  .=  '*';
        }
    }
    return $s .=  $e;
}

/**
 * 小时
 * @return string
 */
function hour() {
    return date('H');
}

/**
 * 分钟
 * @return string
 */
function minute() {
    return date('i');
}

/**
 * 生成分页html
 * @param int $param['page'] 总页数
 * @param int $param['p']   当前页码
 * @param int $param['count'] 总记录数
 * @param int $param['pagesize'] 每页数量
 */
function page_html($param){
    $allpage = $param['page'];
    if(isset($param['max']) && $param['max'] < $param['page']) $param['page'] =  $param['max'];

    if($param['page'] > 1) {
        $first = '<a class="btn-p page-s ' . ($param['p'] < 2 ? 'disabled' : '') . '" ' . ($param['p'] > 1 ? ' href="' . U(__ACTION__, array_merge(I('get.'), array('p' => $param['p'] - 1))) . '"' : '') . '>上一页</a>';
        $first .= '<a class="btn-p page-no ' . ($param['p'] == 1 ? 'active' : '') . '" ' . ($param['p'] != 1 ? ' href="' . U(__ACTION__, array_merge(I('get.'), array('p' => 1))) . '"' : '') . '>1</a>';
        $last = '<a class="btn-p page-no ' . ($param['p'] == $param['page'] ? 'active' : '') . '" ' . ($param['p'] != $param['page'] ? ' href="' . U(__ACTION__, array_merge(I('get.'), array('p' => $param['page']))) . '"' : '') . '>' . $param['page'] . '</a>';
        $last .= '<a class="btn-p page-s ' . ($param['p'] >= $param['page'] ? 'disabled' : '') . '" ' . ($param['p'] < $param['page'] ? ' href="' . U(__ACTION__, array_merge(I('get.'), array('p' => $param['p'] + 1))) . '"' : '') . '>下一页</a>';



        if ($param['page'] < 9) {
            for ($i = 2; $i < $param['page']; $i++) {
                $page_num[] = $i;
            }
        } elseif ($param['p'] >= 6 && $param['p'] + 2 < $param['page']) {
            $page_num = [
                '',
                $param['p'] - 2,
                $param['p'] - 1,
                $param['p'],
                $param['p'] + 1,
                $param['p'] + 2,
                ''
            ];
        } elseif ($param['p'] <= 5 && $param['page'] >= 8) {
            for ($i = 2; $i <= 7; $i++) {
                $page_num[] = $i;
            }
            $page_num[] = '';
        } elseif ($param['page'] - $param['p'] <= 4) {
            $page_num[] = '';
            for ($i = $param['page'] - 7; $i < $param['page']; $i++) {
                $page_num[] = $i;
            }
        }

        foreach ($page_num as $val) {
            if ($val == '') $middle .= '<a class="page-nobox">…</a>';
            else $middle .= '<a class="btn-p page-no ' . ($param['p'] == $val ? 'active' : '') . '" ' . ($param['p'] != $val ? ' href="' . U(__ACTION__, array_merge(I('get.'), array('p' => $val))) . '"' : '') . '>' . $val . '</a>';
        }

        $total = '<div class="page-total">'.$param['count'].'条记录/共'.$allpage.'页</div>';

        echo $first . $middle . $last . $total;
    }elseif($param['page'] == 1 && $param['view']){
        $total = '<div class="page-total">'.$param['count'].'条记录/共'.$allpage.'页</div>';
        echo $total;
    }
}

/**
 * 更新迅搜索引
 * @param integer $goodsId
 */
function updateSearchGoods($goodsId) {
    $xs = new SearchGoods();
    $xs->setParams($goodsId);
    $xs->update();
}

/**
 * 获取商品售后服务天数
 * @param integer $goodsIds
 * @return Ambigous <mixed, NULL, unknown, multitype:Ambigous <string, unknown> unknown , object>
 */
function getGoodsServiceDays($goodsIds) {
    $goods = M('goods')->where(['id' => $goodsIds])->field('service_days,category_id')->find();
    if ($goods['service_days'] > 0) return $goods['service_days'];
    $serviceDays = M('goods_category')->where(['id' => $goods['category_id']])->getField('cate_service_days');
    return $serviceDays;
}

/**
 * @param string $var
 * @return \Common\Cache\RedisDB
 */
function redisRead($var = 'salves') {
    return new \Common\Cache\RedisDB($var);
}

/**
 * @param string $var
 * @return \Common\Cache\RedisDB
 */
function redisWrite($var = 'master') {
    return new \Common\Cache\RedisDB($var);
}

/**
* 生成买家圈子咨询页面
* @param array 	$param
* @param string 	$param['sendert']           发送信息用户的昵称
* @param string 	$param['receiver'] 	        接收信息用户的昵称
* @param string 	$param['commodity_name']    商品名称
* @param string 	$param['commodity_price']   价格
* @param string 	$param['commodity_pattern'] 付款人数
* @param string 	$param['commodity_print']   数量
* @param string 	$param['integral']          商品积分
* @param string 	$param['commodity_image']   图片地址
* @param string 	$param['shop_name']         店铺名称
*/
function im_url($param){
	$url   = 'https://imweb.dtfangyuan.com:9443/storeim/quanzi.html?';
	$param = http_build_query($param);
	$url  .= $param;
	return $url;
}

/**
 * 读取excel文件，分解成数组
 */
function excel_parse($file){
    if(!file_exists($file)){
        echo '文件不存在！';
        return false;
    }

    vendor('PHPExcel.Classes.PHPExcel.IOFactory');
    $reader = \PHPExcel_IOFactory::createReader('Excel5');
    $PHPExcel = $reader->load($file); // 载入excel文件
    $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    $highestColumm = $sheet->getHighestColumn(); // 取得总列数

    /** 循环读取每个单元格的数据 */
    for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
        for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
            $dataset[] = $sheet->getCell($column.$row)->getValue();
            //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";
            $data[$row][$column] = $sheet->getCell($column.$row)->getValue();
        }
    }

    return $data;
}

/**
 * numberformat操作
 *
 * @param $num
 * @param int $dec
 * @return string
 */
function number_formats($num, $dec = 2) {
    return number_format($num, $dec, '.', '');
}


/**
 * 商品SKU状态，主要验证库存
 * 只有最后一个属性才进行验证
 * @return string not not字符串为css的class,表示库存不足
 */
function sku_status($option,$index,$attr_id,$attr_list,$attr){
    $count = count($attr)-1;
    if($index < $count) return;

    $attr_id = explode(',',trim($attr_id));
    //var_dump($attr_id);

    if(count($attr_id) > 1){
        $attr_id[count($attr_id)-1] = $option['attr'];
        $attr_id = implode(',',$attr_id);

        foreach($attr_list as $key => $val){
            if($val['attr_id'] == $attr_id && $val['num'] < 1) return 'not';
        }
    }else{  //当只有一组属性时
        foreach($attr_list as $key => $val){
            if($val['attr_id'] == $option['attr'] && $val['num'] < 1) return 'not';
        }
    }

}


/**
 * 计算剩余天时分。
 * $endtime string 终止日期的Unix时间
 * @author Lazycat
 */
function diff_time($endtime=0)
{
    if ($endtime <= time()) { // 如果过了活动终止日期
        return '0天0时0分';
    }

    // 使用当前日期时间到活动截至日期时间的毫秒数来计算剩余天时分
    $time = $endtime - time();

    $days = 0;
    if ($time >= 86400) { // 如果大于1天
        $days = (int)($time / 86400);
        $time = $time % 86400; // 计算天后剩余的毫秒数
    }

    $hour = 0;
    if ($time >= 3600) { // 如果大于1小时
        $hour = (int)($time / 3600);
        $time = $time % 3600; // 计算小时后剩余的毫秒数
    }

    $minute = (int)($time / 60); // 剩下的毫秒数都算作分

    return $days.'天'.$hour.'时'.$minute.'分';
}

/**
 * subject: 字符串过滤检测
 * api: filterString
 * author: Mercury
 * day: 2017-04-17 11:52
 * [字段名,类型,是否必传,说明]
 * @param $string
 * @param int $type 1商品名称，2用户昵称
 * @return bool|string
 */
function filterString($string, $type = 1) {
    $map = [
        'status' => 1,
        'type'   => $type,
    ];
    $list = M('string_filter')->where($map)->getField('id,name', true);
    $pattern = implode('|', $list);
    preg_match_all("/{$pattern}/", $string, $return);
    if (empty($return[0])) return true;
    return implode(',', $return[0]);
}


/**
 * 短网址生成
 * Create by lazycat 整理
 * 2017-04-24
 * 算法原理
 * 1)将长网址md5生成32位签名串,分为4段, 每段8个字节;
 * 2)对这四段循环处理, 取8个字节, 将他看成16进制串与0x3fffffff(30位1)与操作, 即超过30位的忽略处理;
 * 3)这30位分成6段, 每5位的数字作为字母表的索引取得特定字符, 依次进行获得6位字符串;
 * 4)总的md5串可以获得4个6位串; 取里面的任意一个就可作为这个长url的短url地址;
 */
function shortUrl2($link){ //返回数组
    $base32 = array (
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', '0', '1', '2', '3', '4', '5'
    );

    $hex = md5($link);
    $hexLength = strlen($hex);
    $subHexLen = $hexLength / 8;

    $output = array();
    for ($i = 0; $i < $subHexLen; $i++) {
        //每循环一次取到8位
        $subHex = substr ($hex, $i * 8, 8);
        $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
        $out = '';

        for ($j = 0; $j < 6; $j++) {
            $val = 0x0000001F & $int;
            $out .= $base32[$val];
            $int = $int >> 5;
        }

        $output[] = $out;
    }
    return $output;
}

function shortUrl($link){ //返回字符串
    $result = sprintf("%u",crc32($link));
    $show = '';
    while($result  >0){
        $s = $result % 62;
        if($s > 35){
            $s=chr($s+61);
        }elseif($s>9 && $s<=35){
            $s=chr($s+55);
        }
        $show .= $s;
        $result = floor($result / 62);
    }

    return $show;
}

//检查是否属于购票商品
function check_ticket($category_id){
    $ticks_cids_list = M('goods_category')->cache(true)->field('id')->where(['sid'=>['in',C('CFG.site')['ticket_category']]])->select();
    $ticks_cids = array();
    foreach($ticks_cids_list as $v){
        $ticks_cids[] = $v['id'];
    }
    if(in_array($category_id,$ticks_cids)){
        return true;
    }else{
       return false;
    }
}