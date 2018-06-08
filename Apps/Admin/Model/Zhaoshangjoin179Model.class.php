<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Zhaoshangjoin179Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='zhaoshang_join';
	protected $_validate = array(
        array('status','require','审核状态不能为空!',1,'regex',3), 
        array('step','require','当前步骤不能为空!',1,'regex',3), 
        array('shop_type_id','require','店铺类型不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('first_category','require','一级类目不能为空!',1,'regex',3), 
        array('second_category','require','二级类目不能为空!',1,'regex',3), 
        array('inventory_type','require','分账模式不能为空!',1,'regex',3), 
        array('shop_name','require','店铺名称不能为空!',1,'regex',3), 
        array('about','require','店铺简介不能为空!',1,'regex',3), 
        array('linkname','require','联系人不能为空!',1,'regex',3), 
        array('mobile','require','手机不能为空!',1,'regex',3), 
        array('qq','require','qq不能为空!',1,'regex',3), 
        array('email','require','邮箱不能为空!',1,'regex',3), 
        array('province','require','省份不能为空!',1,'regex',3), 
        array('city','require','城市不能为空!',1,'regex',3), 
        array('district','require','区县不能为空!',1,'regex',3), 
        array('street','require','详细地址不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>