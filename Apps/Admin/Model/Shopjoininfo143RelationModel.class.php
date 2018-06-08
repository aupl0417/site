<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class Shopjoininfo143RelationModel extends RelationModel {
    protected $tableName    =   'shop_join_info';
    protected $_link        =   [
        'shop_join_contact' =>  [
            'mapping_type'  =>  self::HAS_ONE,    
        ],
    ];
}