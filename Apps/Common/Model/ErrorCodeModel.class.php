<?php
namespace Common\Model;
use Think\Model;
class ErrorCodeModel extends Model {
	protected $tableName='error_code';
	//错误代码
	public function error_code(){
		$list=$this->cache('error_code',86400)->getField('code,msg',true);
		return $list;
	}

}
?>