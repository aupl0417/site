<?php
namespace Common\Model;
use Think\Model;
class ShopJoinContactModel extends Model {
	protected $tableName='shop_join_contact';
	protected $_validate = array(
                array('uid','require','用户ID不能为空!',1,'regex',3), 
                array('linkname','require','负责人姓名不能为空!',1,'regex',3), 
                array('mobile','require','负责人手机号码不能为空!',1,'regex',3), 
                array('tel','require','负责人电话不能为空!',1,'regex',3), 
                array('email','require','负责人邮箱不能为空!',1,'regex',3), 
                array('rf_linkname','require','退货联系人不能为空!',1,'regex',3), 
                array('rf_mobile','require','退货联系手机不能为空!',1,'regex',3), 
                array('rf_province','require','退货省分不能为空!',1,'regex',3), 
                array('rf_city','require','退货城市不能为空!',1,'regex',3), 
                array('rf_district','require','退货区域不能为空!',1,'regex',3), 
                array('rf_street','require','退货详细地址不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>