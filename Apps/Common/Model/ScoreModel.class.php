<?php
namespace Common\Model;
use Think\Model;
class ScoreModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='score';
	protected $_validate = array(
        array('s_no','require','流水号不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('score','checkform','积分不能为空且须大于0!',1,'function',3,array('gt0')), 
        array('money','require','消费金额不能为空且须大于0!',1,'function',3,array('gt0')), 
        array('pay_money','require','实付金额不能为空且须大于0!',1,'function',3,array('gt0')), 
        array('ratio','require','兑换比例不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>