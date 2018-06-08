<?php
//自动加截目录函数
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

auto_load('../../../../ThinkPHP/Library/Vendor/Qiniu','listpath.php');