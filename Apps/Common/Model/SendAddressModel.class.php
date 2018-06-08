<?php
namespace Common\Model;
use Think\Model;
class SendAddressModel extends Model {
	protected $tableName='send_address';
	protected $_validate = array(
        array('uid','require','用户ID不能为空！',1,'regex',3), 
        array('linkname','require','联系人姓名不能为空!',1,'regex',3), 
        array('mobile','checkform','手机号码格式错误!',1,'function',3,array('is_mobile')),
        array('tel','checkform','电话号码格式错误!',2,'function',3,array('is_phone')),
        array('postcode','checkform','邮编格式错误!',2,'function',3,array('is_zip')),
        array('province','require','省份ID不能为空!',1,'regex',3), 
        array('city','require','城市ID不能为空!',1,'regex',3), 
        array('district','require','区县ID不能为空!',1,'regex',3), 
        //array('town','require','街道/镇ID不能为空!',1,'regex',3), 
        array('street','require','详细地址不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);

}
?>