<?php
namespace Common\Model;
use Think\Model\RelationModel;
class PositionRelationModel extends RelationModel {
	protected $tableName='ad_position';
	protected $_link = array(
			'ad'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'ad',
					'foreign_key'	=>'position_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'ads',
					'mapping_fields'=>'name,sort,images,url,id,sday,eday,days,is_default,goods_id,shop_id,type,background_images,subcontent',
					'mapping_order' =>'sort asc',
				),
		);
}
?>