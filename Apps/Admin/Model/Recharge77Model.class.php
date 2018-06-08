<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Recharge77Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='recharge';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('pay_type','require','支付类型不能为空!',1,'regex',3), 
        array('r_no','require','充值流水号不能为空!',1,'regex',3), 
        array('money','require','充值金额不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>