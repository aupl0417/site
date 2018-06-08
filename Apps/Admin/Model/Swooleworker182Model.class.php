<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Swooleworker182Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='swoole_worker';
	protected $_validate = array(
        array('name','require','Worker名称不能为空!',1,'regex',3), 
        array('execute','require','要执行的Worker类不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>