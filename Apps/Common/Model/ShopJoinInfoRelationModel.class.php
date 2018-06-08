<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopJoinInfoRelationModel extends RelationModel {
	protected $tableName='shop_join_info';
	protected $_link = array(
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,is_auth,company,type',
				),	
			'shop_type'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'	=>'id',
					'mapping_key'	=>'type_id',
					'mapping_name'	=>'shop_type',
					'mapping_fields'=>'atime,etime,ip',
					'mapping_fields_type'	=>true,
				),						
			'shop_join_brand'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'foreign_key'		=>'uid',
					'mapping_key'		=>'uid',
					'mapping_name'		=>'brand',
					'mapping_fields'	=>'etime,ip',
					'mapping_fields_type' =>true,
				),
			'shop_join_category'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'		=>'uid',
					'mapping_key'		=>'uid',
					'mapping_name'		=>'category',
					'mapping_fields'	=>'etime,ip',
					'mapping_fields_type' =>true,
				),
			'shop_join_cert'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'foreign_key'		=>'uid',
					'mapping_key'		=>'uid',
					'mapping_name'		=>'cert',
					'mapping_fields'	=>'etime,ip',
					'mapping_fields_type' =>true,
				),
			'shop_join_contact'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'		=>'uid',
					'mapping_key'		=>'uid',
					'mapping_name'		=>'contact',
					'mapping_fields'	=>'etime,ip',
					'mapping_fields_type' =>true,
				),
			'shop_join_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'foreign_key'	=>'shop_join_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,a_uid,status,reason,remark',
					'mapping_order' =>'id desc',
				),			
		);

}
?>