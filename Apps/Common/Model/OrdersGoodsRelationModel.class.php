<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersGoodsRelationModel extends RelationModel {
	protected $tableName='orders_goods';
	protected $_link = array(
			'goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods',
					'foreign_key'	=>'id',
					'mapping_key'	=>'goods_id',
					'mapping_name'	=>'goods',
					'mapping_fields'=>'goods_name,score_ratio,express_tpl_id',
				),			
		);

}
?>