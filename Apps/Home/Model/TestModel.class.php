<?php
namespace Home\Model;
use Think\Model\RelationModel;
class TestModel extends RelationModel {
    protected $patchValidate = true;
    protected $_link = array(
        'TestConditions' => array(
            'mapping_type' => RelationModel::HAS_MANY,
            'foreign_key'  => 'test_id',
            'mapping_fields' => 'id as condition_id,ads_name,ads_icon,banner,position,ads_type,ads_content,style_list,backgroundcolor,ads_icon',
            'mapping_order'=> 'sort asc,id asc'
        ),
        'TestSign' => array(
            'mapping_type' => RelationModel::HAS_MANY,
            'foreign_key'  => 'test_id',
            'mapping_order' => 'orderby asc',
            'condition' => 'active = 1',
            'mapping_fields' => 'id,price,images,seller_id,goods_id,cid,num',
            'mapping_order'=> 'orderby asc,id asc'
        ),
        /*'TestIntro' => array(
            'mapping_type' => RelationModel::HAS_ONE,
            'foreign_key'  => 'test_id',
            'as_fields'    => 'intro',
        ),*/
    );
}