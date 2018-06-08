<?php
namespace Common\Model;
use Think\Model;
class OfficialactivityJoinModel extends Model {
	protected $tableName='officialactivity_join';
	protected $_validate = array(
        array('day','require','报名期数数不能为空！',1),
        array('shop_id','require','店铺ID不能为空！',1),
        array('uid','require','用户ID不能为空！',1),
        array('activity_id','require','活动ID不能为空！',1),
        array('subject','require','活动标题不能为空！',1),
        array('images','require','活动图片不能为空！',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>