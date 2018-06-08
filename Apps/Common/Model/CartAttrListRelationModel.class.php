<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CartAttrListRelationModel extends RelationModel {
	protected $tableName='cart';
	protected $_link = array(
			'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods_attr_list',
					'foreign_key'	=>'id',
					'mapping_key'	=>'attr_list_id',
					'mapping_name'	=>'attr_list',
					'mapping_fields'=>'attr_id,attr_name,goods_id,price,weight,num,images,concat("/Goods/view/id/",id,".html") as detail_url',
				),
			'goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods',
					'foreign_key'	=>'id',
					'mapping_key'	=>'goods_id',
					'mapping_name'	=>'goods',
					'mapping_fields'=>'goods_name,score_ratio,express_tpl_id,officialactivity_join_id,officialactivity_price,is_daigou,daigou_ratio,status',
				),			
		);

}
?>