<?php
namespace Common\Model;
use Think\Model;
class ShopMakeModulesModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_make_modules';
	protected $_validate = array(
		array('make_layout_id','require','布局单ID不能为空!',1,'regex',3), 
		array('mod_name','require','模块名称不能为空!',1,'regex',3), 
		array('show_title','require','是否显示标题不能为空!',1,'regex',3), 
		array('col_index','require','单元顺序不能为空!',1,'regex',3), 
		array('type','require','单元类型不能为空!',1,'regex',3),
		array('page_id','require','页面ID不能为空!',1,'regex',3),
		array('data','require','单元参数不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>