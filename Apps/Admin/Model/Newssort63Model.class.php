<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Newssort63Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='news_sort';
	protected $_validate = array(
        array('sort_name','require','名称不能为空!',1,'请选择附加规则',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>