<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Swoolecrontab181Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='swoole_crontab';
	protected $_validate = array(
        array('taskname','require','任务名称不能为空!',1,'regex',3), 
        array('rule','require','规则不能为空!',1,'regex',3), 
        array('execute','require','运行这个任务的类不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>