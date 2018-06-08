<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodsfav101Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_fav';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>