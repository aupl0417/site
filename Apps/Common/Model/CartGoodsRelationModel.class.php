<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CartGoodsRelationModel extends RelationModel {
	protected $tableName='cart';
	protected $_link = array(
			'goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods',
					'foreign_key'	=>'id',
					'mapping_key'	=>'goods_id',
					'mapping_name'	=>'goods',
					'mapping_fields'=>'goods_name,images',
				),
		);

}
?>