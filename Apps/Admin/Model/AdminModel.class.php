<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $_validate = array(
		array('name','require','名称不能为空！'),
	);
	
	protected $_auto = array (
		array('atime','time',1,'function'),
		array('etime','time',2,'function'),
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>