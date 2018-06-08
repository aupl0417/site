<?php
namespace Common\Model;
use Think\Model;
class WithdrawModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='withdraw';
	protected $_validate = array(
        array('w_no','require','提现流水号不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('money','require','提现金额不能为空!',1,'regex',3), 
        array('card_id','require','提现账号ID不能为空!',1,'regex',3), 
        array('account','require','提现账号不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>