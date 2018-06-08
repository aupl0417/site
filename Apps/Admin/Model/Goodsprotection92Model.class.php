<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodsprotection92Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_protection';
	protected $_validate = array(
        array('protection_name','require','商品保障模板ID不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('content','require','保障详情不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>