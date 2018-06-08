<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Luckdraw1couponcondition237Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='luckdraw1_coupon_condition';
	protected $_validate = array(
        array('name','require','名称不能为空!',1,'regex',3), 
        array('min_price','require','最低消费不能为空!',1,'regex',3), 
        array('price','require','抵扣金额不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>