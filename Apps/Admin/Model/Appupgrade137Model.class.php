<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Appupgrade137Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='app_upgrade';
	protected $_validate = array(
        array('terminal','require','终端不能为空!',1,'regex',3), 
        array('type','require','类型不能为空!',1,'regex',3), 
        array('version','require','版本不能为空!',1,'regex',3), 
        array('is_force','require','是否强制更新不能为空!',1,'regex',3), 
        array('down_url','require','下载地址不能为空!',1,'regex',3), 
        array('content','require','更新内容不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>