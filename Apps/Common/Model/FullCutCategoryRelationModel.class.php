<?php
namespace Common\Model;
use Think\Model\RelationModel;

class FullCutCategoryRelationModel extends RelationModel
{


	protected $tableName = 'full_cut_category';
	
	protected $_link = array(

		'FullCut' => array(
			'mapping_type'   	=> self::HAS_MANY,
                  'mapping_name'   	=> 'fullcut',
                  'class_name'     	=> 'full_cut',
                  'foreign_key'    	=> 'category_id',
                  'mapping_key'    	=> 'id',
                  'mapping_fields'	=> '*',
                  
		),
	);


















}