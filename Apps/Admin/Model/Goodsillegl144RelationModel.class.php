<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Goodsillegl144RelationModel extends RelationModel {
	protected $tableName='goods_illegl';
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
					'mapping_name'	=>'seller',
					'mapping_fields'	=>'id,nick,face',
				),	
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'	=>'id,shop_name,shop_logo,domain,qq,mobile',
				),
		);

}
?>