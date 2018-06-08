<?php
namespace Common\Model;
use Think\Model;
class OrdersModel extends Model {
	protected $tableName='orders';
	protected $_validate = array(
                array('uid','require','买家ID不能为空!',1,'regex',3), 
                array('o_no','require','订单号不能为空!',1,'regex',3), 
                //array('pay_price','require','实付金额不能为空!',1,'regex',3), 
                array('province','require','省份ID不能为空!',1,'regex',3), 
                array('city','require','城市ID不能为空!',1,'regex',3), 
                array('district','require','地区ID不能为空!',1,'regex',3), 
                //array('town','require','城镇/街道ID不能为空!',1,'regex',3), 
                array('street','require','详细地址不能为空!',1,'regex',3), 
                array('linkname','require','收货人不能为空!',1,'regex',3), 
                array('mobile','checkform','手机号码为空或格式错误!',2,'function',3,array('is_mobile')),
                array('tel','checkform','电话为空或格式错误!',2,'function',3,array('is_phone')), 
                array('postcode','checkform','邮编为空或格式错误!',2,'function',3,array('is_zip')), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>