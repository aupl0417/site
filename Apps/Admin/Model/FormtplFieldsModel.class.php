<?php
namespace Admin\Model;
use Think\Model;
class FormtplFieldsModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='formtpl_fields';
	protected $_validate = array(
		array('name','require','字段名不能为空！',1),
		array('label','require','标签不能为空！',1),
		array('formtype','require','表单类型不能为空！',1),
		array('formtpl_id','require','表单模板ID不能为空！'),
		array('group_id','require','表单分组不能为空！'),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',3,'function'),	
	);

}
?>