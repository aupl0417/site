<?php
namespace Common\Model;
use Think\Model\RelationModel;
class RechargeRelationModel extends RelationModel {
    protected $tableName    =   'recharge';
    protected $_link        =   array(
            'user'  =>array(
                    'mapping_type'      =>self::HAS_ONE,
                    'class_name'        =>'user',
                    'foreign_key'       =>'id',
                    'mapping_key'       =>'uid',
                    'mapping_name'      =>'user',
                    'mapping_fields'    =>'openid,nick,erp_uid',
                    'as_fields'         =>'openid,nick,erp_uid',
                ),
    );
}