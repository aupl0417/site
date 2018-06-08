<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersGoodsCommentRelationModel extends RelationModel {
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
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'uid',
					'mapping_name'		=>'user',
					'mapping_fields'	=>'nick,name,face,level_id',
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