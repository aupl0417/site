<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shopjoininfo143Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_join_info';
	protected $_validate = array(
        array('shop_name','require','店铺名称不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('type_id','require','店铺类型不能为空!',1,'regex',3), 
        array('about','require','店铺描述不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>