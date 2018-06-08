<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopModulesRelationModel extends RelationModel {
    protected $tableName    =   'shop_modules_category';
    protected $_link = array(                    
            'shop_modules'   =>array(
                    'mapping_type'      =>self::HAS_MANY,
                    'foreign_key'       =>'sid',
                    'mapping_key'       =>'id',
                    'mapping_name'      =>'shop_modules',
                    'mapping_fields'    =>'etime,ip',
                    'mapping_fields_type' =>true,
                ),         
        );
}