<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodsparamgroupoption88Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_param_group_option';
	protected $_validate = array(
        array('param_name','require','参数名称不能为空!',1,'regex',3), 
        array('group_id','require','参数分组不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>