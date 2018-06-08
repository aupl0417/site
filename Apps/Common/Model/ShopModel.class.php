<?php
namespace Common\Model;
use Think\Model;
class ShopModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop';
	protected $_validate = array(
		array('status','require','店铺状态不能为空!',1,'regex',3), 
		array('uid','require','用户ID不能为空!',1,'regex',3), 
		array('shop_name','require','店铺名称不能为空!',1,'regex',3), 
		array('about','require','店铺描述不能为空!',1,'regex',3), 
		array('province','require','省份不能为空!',1,'regex',3), 
		array('city','require','城市不能为空!',1,'regex',3), 
		array('district','require','区域不能为空!',1,'regex',3), 
		array('street','require','详细地址不能为空!',1,'regex',3), 
		array('mobile','require','手机号不能为空!',1,'regex',3), 
		array('type_id','require','店铺类型不能为空!',1,'regex',3), 
		array('category_id','require','类目不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>