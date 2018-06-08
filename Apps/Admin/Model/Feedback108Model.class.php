<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Feedback108Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='feedback';
	protected $_validate = array(
        array('email','require','邮箱不能为空!',1,'regex',3), 
        array('content','require','意见内容不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>