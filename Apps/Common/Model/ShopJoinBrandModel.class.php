<?php
namespace Common\Model;
use Think\Model;
class ShopJoinBrandModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_join_brand';
	protected $_validate = array(
		array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('b_name','require','品牌中文名不能为空!',1,'regex',3), 
        array('b_logo','require','品牌logo不能为空!',1,'regex',3), 
        //array('b_images','require','品牌证书或商标授理书图片不能为空!',1,'regex',3), 
        array('b_master','require','品牌所有者不能为空!',1,'regex',3), 
        //array('b_code','require','提现流水号不能为空!',1,'regex',3), 
        array('b_type','require','品牌类型不能为空!',1,'regex',3), 
        array('b_scope','require','经营类型不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>