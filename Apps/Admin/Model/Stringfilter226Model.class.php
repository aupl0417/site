<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Stringfilter226Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='string_filter';
	protected $_validate = array(
        array('name','require','过滤字符不能为空!',1,'regex',3), 
        array('type','require','过滤类型不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>