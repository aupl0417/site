<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodscommitted217Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_committed';
	protected $_validate = array(
        array('name','require','承诺名称不能为空!',1,'regex',3), 
        array('intro','require','承诺描述不能为空!',1,'regex',3), 
        array('status','require','0隐藏，1正常不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>