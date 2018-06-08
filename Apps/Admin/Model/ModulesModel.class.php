<?php
namespace Admin\Model;
use Think\Model;
class ModulesModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $_validate = array(
		array('name','require','名称不能为空！'),
		array('controller','require','控制器不能为空！'),		
	);
	
	protected $_auto = array (
		array('atime','time',1,'function'),
		array('etime','time',2,'function'),
		array('ip','get_client_ip',3,'function'),
	);
	

	
}
?>