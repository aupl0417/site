<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Refund149Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='refund';
	protected $_validate = array(
        array('status','require','退款状态不能为空!',1,'regex',3), 
        array('type','require','退款类型不能为空!',1,'regex',3), 
        array('money','require','退款金额不能为空!',1,'regex',3), 
        array('orders_status','require','订单状态不能为空!',1,'regex',3), 
        array('uid','require','买家ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
        array('s_id','require','商家订单ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>