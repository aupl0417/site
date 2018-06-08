<?php
namespace Common\Model;
use Think\Model;
class ShopInfoModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop';
	protected $_validate = array(
		array('about','require','店铺描述不能为空!',1,'regex',3), 
		array('province','require','省份不能为空!',1,'regex',3), 
		array('city','require','城市不能为空!',1,'regex',3), 
		array('district','require','区域不能为空!',1,'regex',3), 
		array('street','require','详细地址不能为空!',1,'regex',3), 
		array('mobile','require','手机号不能为空!',1,'regex',3), 
		array('qq','require','客服QQ不能为空!',1,'regex',3),
		array('about','require','店铺描述不能为空!',1,'regex',3),
		array('inventory_type','require','库存积分结算方式不能为空!',0,'regex',self::MODEL_INSERT),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>