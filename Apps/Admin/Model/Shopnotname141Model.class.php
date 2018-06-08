<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shopnotname141Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_notname';
	protected $_validate = array(
        array('name','require','禁用店铺名称,多个用逗号隔开不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>