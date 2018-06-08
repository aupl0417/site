<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shopvr256Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_vr';
	protected $_validate = array(
		array('shop_name','require','商家名称不能为空!'), 
		array('mobile','require','手机号码不能为空!'), 
		array('uid','require','用户ID不能为空!'),
		array('type','require','严重程度不能为空!'), 
		array('wrongdoing','require','违规行为不能为空!'),
		array('plot','require','违规情节不能为空!'),
		array('point','require','扣分分值不能为空!'),
		array('punishment','require','处罚方式不能为空!'),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>