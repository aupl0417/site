<?php
namespace Common\Model;
use Think\Model;
class ConfigModel extends Model {
	
	/*
	+-------------------------------
	+@param  $param['modules'] 要获取的分组名,数组类型
	+@param  $param['cache_name'] 缓存名称
	+@param  $param['cache'] 是否使用缓存
	+--------------------------------
	*/	
	public function config($param=null){
		$cache_name=$param['cache_name']?$param['cache_name']:md5(_SELF__);
		
		$result=S($cache_name);
		if($result) return $result;
		
		$do=M('config_sort');
		$map['active']=1;
		$map['sid']=array('gt',0);
		if(isset($param['ac'])) $map['ac']=array('in',$param['ac']);
		$list=$do->where($map)->field('id,ac')->select();
		
		foreach($list as $key=>$val){
			$dlist=$this->where(array('active'=>1,'sid'=>$val['id']))->field('id,name,value')->select();
			foreach($dlist as $v){
				$result[$val['ac']][$v['name']]=$v['value'];
			}
		}
		
		S($cache_name,$result,86400);		
		return $result;		
	}

}
?>