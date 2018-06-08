<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Tuanapply218RelationModel extends RelationModel {
	protected $tableName='tuan_apply';
	protected $_link = array(
'tuan_apply_list' => array(
            'mapping_type'         	=> self::HAS_MANY,
            'mapping_name'         	=> 'tuan_apply_list',
            'class_name'        	=> 'tuan_apply_list',
            'foreign_key'       	=> 'ta_no',
            'mapping_key'       	=> 'ta_no',
            'mapping_fields'     	=> '*',
            # 'as_fields'         	=> 'ta_no',
        ),
        'tuan_apply_logs' => array(
            'mapping_type'          => self::HAS_MANY,
            'mapping_name'          => 'tuan_apply_logs',
            'class_name'            => 'tuan_apply_logs',
            'foreign_key'           => 'ta_no',
            'mapping_key'           => 'ta_no',
            'mapping_fields'        => '*',
            # 'as_fields'           => 'ta_no',
        ),
		'shop'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
			'class_name'    => 'shop',
            'foreign_key'   =>  'uid',
			'mapping_key'   => 'uid',
            'mapping_fields'=>  'id,status,shop_name,domain,qq,mobile,tel,email,wang,fav_num,goods_num,sale_num,fraction_speed,fraction_service,fraction_desc,fraction,inventory_type,illegl_point',
        ],
		 'user'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'uid',
            'mapping_fields'=>  'nick',
        ],
		);

}
?>