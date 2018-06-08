<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Orderscode126Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='orders_code';
	protected $_validate = array(
        array('sid','require','类型不能为空!',1,'regex',3), 
        array('code','require','错误代码不能为空!',1,'regex',3), 
        array('msg','require','代码代表的意思不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>