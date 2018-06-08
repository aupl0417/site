<?php
namespace Admin\Model;
use Think\Model;
class ControllerModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='controller';
	protected $_validate = array(
        array('type','require','类别不能为空!',1,'regex',3), 
        array('controller_name','require','控制器名称不能为空!',1,'regex',3), 
        array('controller','require','控制器不能为空!',1,'regex',3), 
        array('formtpl_id','require','表单模板ID不能为空!',2,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>