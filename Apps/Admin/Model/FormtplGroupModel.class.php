<?php
namespace Admin\Model;
use Think\Model;
class FormtplGroupModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='formtpl_group';
	protected $_validate = array(
		array('group_name','require','分组名不能为空！',1),
		array('formtpl_id','require','表单模板ID不能为空！'),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',3,'function'),	
	);
}
?>