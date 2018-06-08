<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Orderapply222RelationModel extends RelationModel {
	protected $tableName='order_apply';
	protected $_link = array(
'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods_attr_list',
					'foreign_key'	=>'id',
					'mapping_key'	=>'attr_list_id',
					'mapping_name'	=>'attr_list',
					'mapping_fields'=>'id,attr_name,images',
				),
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'seller',
					'mapping_fields'=>'id,nick,is_auth',
				),
			'orders_goods_comment'=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders_goods_comment',
					'foreign_key'	=>'id',
					'mapping_key'	=>'c_id',
					'mapping_name'	=>'orders_goods_comment',
					'mapping_fields'=>'id,atime,status,rate,content,images,is_anonymous,is_sys,is_shuadan',
				),
			'orders_apply_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'orders_apply_logs',
					'foreign_key'	=>'apply_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'orders_apply_logs',
					'mapping_fields'=>'id,reason,images,status,remark,username,atime',
				),
			'orders_apply_reason'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders_apply_reason',
					'foreign_key'	=>'c_id',
					'mapping_key'	=>'c_id',
					'mapping_name'	=>'orders_apply_reason',
					'mapping_fields'=>'id,reason,atime',
				),
		);

}
?>