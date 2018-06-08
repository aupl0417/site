<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Mobileorders257Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='mobile_orders';
	protected $_validate = array(
        array('pay_type','require','付款方式不能为空!',1,'regex',3), 
        array('recharge_type','require','充值类型不能为空!',1,'regex',3), 
        array('status','require','付款状态不能为空!',1,'regex',3), 
        array('type','require','充值类型不能为空!',1,'regex',3), 
        array('s_no','require','流水号不能为空!',1,'regex',3), 
        array('mobile','require','手机号码不能为空!',1,'regex',3), 
        array('desc','require','充值描述不能为空!',1,'regex',3), 
        array('fare','require','话费面值/流量不能为空!',1,'regex',3), 
        array('pay_price','require','实付金额不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>