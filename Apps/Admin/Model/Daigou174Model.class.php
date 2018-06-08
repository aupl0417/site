<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Daigou174Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='daigou';
	protected $_validate = array(
        array('status','require','状态不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('goods_name','require','代购商品名称不能为空!',1,'regex',3), 
        array('attr_name','require','商品属性(颜色或尺码)不能为空!',1,'regex',3), 
        array('url','require','商品在第三方平台的链接不能为空!',1,'regex',3), 
        array('price','require','代购所需金额不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>