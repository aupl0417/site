<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Mobilefare265Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='mobile_fare';
	protected $_validate = array(
        array('fare','require','面值不能为空!',1,'regex',3), 
        array('price','require','价格不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>