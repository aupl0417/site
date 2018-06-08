<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Luckdraw1category231Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='luckdraw1_category';
	protected $_validate = array(
        array('name','require','玩法名称不能为空!',1,'regex',3), 
        array('images','require','图片不能为空!',1,'regex',3), 
        array('intro','require','玩法简介不能为空!',1,'regex',3), 
        array('template','require','默认模板不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>