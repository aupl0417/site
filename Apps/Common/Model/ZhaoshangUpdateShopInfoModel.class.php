<?php
namespace Common\Model;
use Think\Model;
class ZhaoshangupdateShopInfoModel extends Model {
	protected $tableName='zhaoshang_upgrade';
	protected $_validate = array(
        array('shop_name','require','店铺名称不能为空或店铺命名格式错误!',1,'regex',3),
        array('about','checkform','店铺简介不能为空且在5~200个字!',1,'function',3,array('string_range',5,600)),
        array('inventory_type','require','分账模式不能为空!',1,'regex',3),
        array('province','require','省分不能为空!',1,'regex',3),
        array('city','require','城市不能为空!',1,'regex',3),
        array('district','require','区、县不能为空!',1,'regex',3),
        array('street','require','详细地址不能为空!',1,'regex',3),
        array('linkname','require','店铺负责人不能为空!',1,'regex',3),
        array('mobile','checkform','手机号码不能为空或格式错误!',1,'function',3,array('is_mobile')),
        array('tel','checkform','电话号码格式错误!',2,'function',3,array('is_phone')),
        array('qq','checkform','QQ号码不能为空或格式错误!',1,'function',3,array('is_qq')),
        array('email','checkform','邮箱不能为空或格式错误!',1,'function',3,array('is_email')),

    );
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>