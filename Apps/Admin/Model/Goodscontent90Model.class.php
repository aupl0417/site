<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodscontent90Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_content';
	protected $_validate = array(
        array('goods_id','require','商品ID不能为空!',1,'regex',3), 
        array('content','require','商品详情不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>