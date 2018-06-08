<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Workorder151Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='workorder';
	protected $_validate = array(
        array('w_no','require','工单号不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('mobile','require','手机不能为空!',0,'regex',2), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>