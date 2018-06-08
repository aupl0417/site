<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Formtpl157Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='formtpl';
	protected $_validate = array(
        array('tpl_name','require','模板名称不能为空!',1,'regex',3), 
        array('tables','require','数据表不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>