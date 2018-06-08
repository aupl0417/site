<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Help68Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='help';
	protected $_validate = array(
        array('name','require','标题不能为空!',1,'请选择附加规则',3), 
        array('content','require','内容不能为空!',1,'请选择附加规则',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>