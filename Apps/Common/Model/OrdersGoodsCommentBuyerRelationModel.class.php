<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersGoodsCommentBuyerRelationModel extends RelationModel {
	protected $tableName='orders_goods_comment';
	protected $_link = array(			
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'orders_goods',
					'foreign_key'		=>'id',
					'mapping_key'		=>'orders_goods_id',
					'mapping_name'		=>'orders_goods',
					'mapping_fields'	=>'attr_name,images,price,num,goods_name,concat("/Goods/view/id/",attr_list_id,".html") as detail_url',
				),
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'seller_id',
					'mapping_name'		=>'seller',
					'mapping_fields'	=>'nick,name,face,level_id',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'shop',
					'foreign_key'		=>'id',
					'mapping_key'		=>'shop_id',
					'mapping_name'		=>'shop',
					'mapping_fields'	=>'id,shop_name,shop_logo,shop_level,domain,qq',
				),
            'orders_goods_comment_reply'    =>  array(
                'mapping_type'		=>self::HAS_MANY,
                'class_name'		=>'orders_goods_comment_reply',
                'foreign_key'		=>'comment_id',
                'mapping_key'		=>'id',
                'mapping_name'		=>'reply',
                'mapping_order'     =>'atime asc',
                'mapping_fields'	=>'comment_id,atime,seller_id,type,content,images,uid,etime',
            ),

		);

}
?>