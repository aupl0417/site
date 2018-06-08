<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shopmenu216Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_menu';
	protected $_validate = array(
        array('name','require','导航名称不能为空!',1,'regex',3), 
        array('url','require','链接地址不能为空!',1,'regex',3), 
        array('controller','require','所属控制器不能为空!',1,'regex',3), 
        array('target','require','打开方式不能为空!',1,'regex',3), 
        array('position','require','所在位置不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>