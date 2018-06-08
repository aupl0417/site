<?php
namespace Admin\Model;
use Think\Model;
class ConfigSortModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName = 'config_sort';
	protected $_validate = array(
		array('name','require','名称不能为空！'),
		array('ac','require','参数不能为空！'),
	);
	
	protected $_auto = array (
		array('atime','time',1,'function'),
		array('etime','time',2,'function'),
		array('ip','get_client_ip',3,'function'),	
	);
	
	public function all($map=''){
		$list=get_category(array(
			'table'	=>'config_sort',
			'field'	=>'*',
			'map'	=>$map,
			'level'	=>2,
		));
		return $list;
	}
}
?>