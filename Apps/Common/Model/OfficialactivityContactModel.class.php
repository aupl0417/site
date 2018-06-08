<?php
namespace Common\Model;
use Think\Model;
class OfficialactivityContactModel extends Model {
	protected $tableName='officialactivity_contact';
	protected $_validate = array(
        array('shop_id','require','店铺ID不能为空！',1),
        array('uid','require','用户ID不能为空！',1),
        array('activity_id','require','活动ID不能为空！',1),
        array('linkname','require','联系人不能为空！',1),
        array('mobile','require','手机号码不能为空！',1),
        array('qq','require','QQ不能为空！',1),
        array('email','require','邮箱不能为空！',1),
        array('address','require','联系地址不能为空！',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>