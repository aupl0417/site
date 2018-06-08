<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodsparam89Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_param';
	protected $_validate = array(
        array('param_value','require','参数值不能为空!',1,'regex',3), 
        array('goods_id','require','宝贝ID不能为空!',1,'regex',3), 
        array('option_id','require','选项参数ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>