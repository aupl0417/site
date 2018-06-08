<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodsparamgroup87Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_param_group';
	protected $_validate = array(
        array('group_name','require','参数分组名称不能为空!',1,'regex',3), 
        array('category_id','require','商品类目ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>