<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Officialactivityjoin168RelationModel extends RelationModel {
	protected $tableName='officialactivity_join';
	protected $_link = array(
'goods_attr_list'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'goods_id',
            'mapping_key'   =>  'goods_id',
            'mapping_fields'=>  'id,attr_name,images,price,num',
        ],
        'goods'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'goods_id',
            'mapping_fields'=>  'goods_name,images,price,price_max,num,sale_num,fraction,view,fav_num,score_ratio,free_express,is_self,pr',
        ],
        'user'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'uid',
            'mapping_fields'=>  'id,nick',
        ],
        'shop'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'uid',
            'mapping_key'   =>  'uid',
            'mapping_fields'=>  'id,status,shop_name,domain,qq,mobile,tel,email,wang,fav_num,goods_num,sale_num,fraction_speed,fraction_service,fraction_desc,fraction,inventory_type,illegl_point',
        ],
        'officialactivity_contact'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'uid',
            'mapping_key'   =>  'uid',
            'mapping_name'  =>  'contact',
        ],
		);

}
?>