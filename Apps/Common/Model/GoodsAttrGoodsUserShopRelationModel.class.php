<?php
namespace Common\Model;
use Think\Model\RelationModel;

class GoodsAttrGoodsUserShopRelationModel extends RelationModel
{


	protected $tableName = 'goods_attr_list';

	protected $_link = array(
		'goods' => array(
            'mapping_type'      => self::HAS_ONE,
            'mapping_name'      => 'goods',
            'class_name'        => 'goods',
            'foreign_key'       => 'id',
            'mapping_key'       => 'goods_id',
            'mapping_fields'    => 'goods_name',
            'as_fields'         => 'goods_name',
        ),
		'user' => array(
            'mapping_type'      => self::HAS_ONE,
            'mapping_name'      => 'user',
            'class_name'        => 'user',
            'foreign_key'       => 'id',
            'mapping_key'       => 'seller_id',
            'mapping_fields'    => 'nick as seller_name',
            'as_fields'         => 'seller_name',
        ),

		'shop' => array(
            'mapping_type'      => self::HAS_ONE,
            'mapping_name'      => 'shop',
            'class_name'        => 'shop',
            'foreign_key'       => 'uid',
            'mapping_key'       => 'seller_id',
            'mapping_fields'    => 'shop_name,fraction_speed,fraction_service,fraction_desc,fraction',
            'as_fields'         => 'shop_name,fraction_speed,fraction_service,fraction_desc,fraction',
        ),

		



	);








}