<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodspackage91Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_package';
	protected $_validate = array(
        array('package_name','require','包装模板名称不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('content','require','包装详情不能为空!',0,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>