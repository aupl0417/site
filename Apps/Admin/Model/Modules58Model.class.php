<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Modules58Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='modules';
	protected $_validate = array(
        array('module_name','require','模块名称不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>