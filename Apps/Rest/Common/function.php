<?php

/**
* 检查必须参数
* @param array $nkey 必传参数的key名，如 array('appid','skid')
* @param array $param 参数，如$_POST中的数据
*/
function need_param($nkey=null,$param){
	if(is_null($nkey)) return true;
	//if(empty($param)) return false;

	$count=count($nkey);
	$n=0;
	
	$fkey=array();
	foreach($param as $key=>$val){
		//if(in_array($key,$nkey) && trim($val)!='') $n++;
		//else $result['nfield'][]=$key;
		$fkey[]=$key;
	}
	
	$nokey=array();
	foreach($nkey as $val){
		if(in_array($val,$fkey) && trim($param[$val])!='') $n++;
		else{
			$nokey[]=$val;
		}
	}
	
	$result['nokey']=@implode(',',$nokey);
	
	if($n!=$count) $result['code']=0;
	else $result['code']=1;
	
	//file_put_contents('t.txt',var_export($result,true));

	return $result;
}