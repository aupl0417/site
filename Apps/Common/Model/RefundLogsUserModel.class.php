<?php
namespace Common\Model;
use Think\Model\RelationModel;
class RefundLogsUserModel extends RelationModel {

	protected $tableName = 'refund_logs';
	
	protected $_link = array(
		'userinfo' => array(
            'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'user', //映射的名称
            'class_name'        => 'user',  //关联的表名
            'foreign_key'       => 'id', // userinfo uid
            'mapping_key'       => 'uid', // user id
            'mapping_fields'     => 'nick,face',
            'as_fields'         => 'nick,face',
        ),

	);


}
?>