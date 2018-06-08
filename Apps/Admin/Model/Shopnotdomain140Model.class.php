<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shopnotdomain140Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_notdomain';
	protected $_validate = array(
        array('domain','require','禁用域名,多个用逗号隔开不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>