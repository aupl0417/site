<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/28
 * Time: 11:22
 */

namespace Common\Model;


use Think\Model\RelationModel;

/**
 * 权限模块关联模型
 *
 * Class ShopAuthModuleRelationModel
 * @package Common\Model
 */

class ShopAuthModuleRelationModel extends RelationModel
{
    protected $tableName = 'shop_auth_module';

    protected $_link     = [
        'shop_auth_function'    =>  [
            'mapping_type'  =>  self::HAS_MANY,
            'class_name'    =>  'shop_auth_function',
            'foreign_key'   =>  'cid',
            'parent_key'    =>  'id',
            'mapping_fields'=>  'id,page_name as title',
            'mapping_name'  =>  'dlist',
        ],
    ];
}