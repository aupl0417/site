<?php
namespace Common\Model;
use Think\Model\RelationModel;

class TuanApplyRelationModel extends RelationModel
{


	protected $tableName = 'tuan_apply';

	protected $_link = array(
        'tuan_apply_list' => array(
            'mapping_type'         	=> self::HAS_MANY,
            'mapping_name'         	=> 'tuan_apply_list',
            'class_name'        	=> 'tuan_apply_list',
            'foreign_key'       	=> 'tuan_apply_id',
            'mapping_key'       	=> 'id',
            'mapping_fields'     	=> '*',
        ),
        'user' => array(
            'mapping_type'          => self::HAS_ONE,
            'mapping_name'          => 'user',
            'class_name'            => 'user',
            'foreign_key'           => 'id',
            'mapping_key'           => 'uid',
            'mapping_fields'        => 'id,nick',
        ),
        'shop' => array(
            'mapping_type'          => self::HAS_ONE,
            'mapping_name'          => 'shop',
            'class_name'            => 'shop',
            'foreign_key'           => 'uid',
            'mapping_key'           => 'uid',
            'mapping_fields'        => 'id,uid,shop_name,shop_logo',
        ),
        'goods' => array(
            'mapping_type'          => self::HAS_ONE,
            'mapping_name'          => 'goods',
            'class_name'            => 'goods',
            'foreign_key'           => 'id',
            'mapping_key'           => 'goods_id',
            'mapping_fields'        => 'id,goods_name,images,status,seller_id,score_ratio,is_best,atime,sale_num,num,price,code',
        ),
    );
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),
	);





	
}