<?php
namespace Admin\Model;
use Think\Model;
class FormtplModel extends Model {
	//protected $patchValidate = true; //批量验证
	//protected $tableName='formtpl';
	protected $_validate = array(
		array('tpl_name','require','模板名不能为空！',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',3,'function'),	
	);

}
?>