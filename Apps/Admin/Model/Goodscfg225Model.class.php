<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodscfg225Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_cfg';
	protected $_validate = array(
        array('cfg_name','require','赠送说明不能为空!',1,'regex',3), 
        array('score_ratio','require','赠送积分比例不能为空!',1,'regex',3), 
        array('inventory_ratio','require','扣库存积分比例不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>