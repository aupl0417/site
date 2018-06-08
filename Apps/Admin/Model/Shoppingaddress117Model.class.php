<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shoppingaddress117Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shopping_address';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('linkname','require','联系人不能为空!',1,'regex',3), 
        array('mobile','require','手机不能为空!',1,'regex',3), 
        array('province','require','省份不能为空!',1,'regex',3), 
        array('city','require','城市不能为空!',1,'regex',3), 
        array('district','require','区县不能为空!',1,'regex',3), 
        array('town','require','镇不能为空!',1,'regex',3), 
        array('street','require','详细地址不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>