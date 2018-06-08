<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Store116Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='store';
	protected $_validate = array(
        array('store_name','require','店铺名称不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('about','require','店铺简介不能为空!',1,'regex',3), 
        array('mobile','require','手机号码不能为空!',1,'regex',3), 
        array('qq','require','ＱＱ不能为空!',1,'regex',3), 
        array('province','require','省份ID不能为空!',1,'regex',3), 
        array('city','require','城市ID不能为空!',1,'regex',3), 
        array('district','require','区ID不能为空!',1,'regex',3), 
        array('town','require','镇ID不能为空!',1,'regex',3), 
        array('street','require','详细地址不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>