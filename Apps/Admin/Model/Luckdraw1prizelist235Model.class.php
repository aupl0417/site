<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Luckdraw1prizelist235Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='luckdraw1_prize_list';
	protected $_validate = array(
        array('images','require','奖品图片不能为空!',1,'regex',3), 
        array('type_id','require','奖品类型不能为空!',1,'regex',3), 
        array('value','require','奖品参数不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>