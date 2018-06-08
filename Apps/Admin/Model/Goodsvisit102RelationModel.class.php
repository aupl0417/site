<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Goodsvisit102RelationModel extends RelationModel {
	protected $tableName='goods_visit';
	protected $_link = array(
			'Goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'	=>'id',
					'mapping_key'	=>'goods_id',
					'mapping_name'	=>'goods',
					'mapping_fields'	=>'id,goods_name,images',
				),		
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'	=>'id,nick,face',
				),	
		);

}
?>