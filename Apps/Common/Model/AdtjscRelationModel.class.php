<?php
namespace Common\Model;
use Think\Model\RelationModel as Relation;

class AdtjscRelationModel extends Relation
{

	protected $tableName = 'ad_tj_sucai';

	protected $_link = array(
		'ad_sucai' => array(
            'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'ad_sucai', //映射的名称
            'class_name'        => 'ad_sucai',  //关联的表名
            'foreign_key'       => 'id',
            'mapping_key'       => 'sucai_id',
            'mapping_fields'     => 'sucai_name as name,images,width,height',
            # 'as_fields'         => 'sucai_images',
        ),
        'ad_position' => array(
        	'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'ad_position', //映射的名称
            'class_name'        => 'ad_position',  //关联的表名
            'foreign_key'       => 'id',
            'mapping_key'       => 'position_id',
            'mapping_fields'     => 'position_name,url as position_url',
            # 'as_fields'         => 'position_name,position_url',
        ),
	);

}