<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Luckdrawprize184Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='luckdraw_prize';
	protected $_validate = array(
        array('prize_name','require','奖品名称不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>