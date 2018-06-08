<?php
namespace Common\Model;
use Think\Model\RelationModel as Relation;

class AdtjgoodsRelationModel extends Relation
{

	protected $tableName = 'ad_tj_goods';

	protected $_link = array(
		'goods' => array(
            'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'goods', //映射的名称
            'class_name'        => 'goods',  //关联的表名
            'foreign_key'       => 'id',
            'mapping_key'       => 'goods_id',
            'mapping_fields'     => 'images as goods_images,goods_name',
            'as_fields'         => 'goods_images,goods_name',
        ),
        
	);

}