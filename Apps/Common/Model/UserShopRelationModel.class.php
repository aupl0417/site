<?php
namespace Common\Model;
use Think\Model\RelationModel;

class UserShopRelationModel extends RelationModel
{

	protected $tableName = 'user';

	protected $_link = array(
        'shop' => array(
            'mapping_type'         	=> self::HAS_ONE,
            'mapping_name'         	=> 'shop',
            'class_name'        	=> 'shop',
            'foreign_key'       	=> 'uid',
            'mapping_key'       	=> 'id',
            'mapping_fields'     	=> 'shop_name,shop_logo,id as shop_id',
            'as_fields'         	=> 'shop_name,shop_logo,shop_id',
        ),
    );












}