<?php

/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/4
 * Time: 9:21
 */

namespace Sellergoods\Model;

use Think\Model\RelationModel;

class GoodsCreateRelationModel extends RelationModel
{
    protected $tableName = 'goods';

    protected $_link    =   [
        'goods_attr_list'	=>array(
            'mapping_type'		=>self::HAS_MANY,
            'class_name'		=>'GoodsAttrList',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            //'mapping_name'		=>'attr_list',
        ),
        'goods_attr_value'	=>array(
            'mapping_type'		=>self::HAS_MANY,
            'class_name'		=>'GoodsAttrValue',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            //'mapping_name'		=>'attr_list',
        ),
        'goods_content'     =>array(
            'mapping_type'		=>self::HAS_ONE,
            'class_name'		=>'GoodsContent',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'as_fields'         =>'content',
        ),
        'goods_param'	    =>array(
            'mapping_type'		=>self::HAS_MANY,
            'class_name'		=>'GoodsParams',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            //'mapping_name'		=>'params',
            //'mapping_fields'    =>'option_id,param_value',
        ),
        'goods_collocation' =>array(    //搭配商品
            'mapping_type'		=>self::HAS_ONE,
            'class_name'		=>'GoodsCollocation',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'as_fields'         =>'collocations',
        ),
    ];


    protected $_validate = array(
        array('goods_name','require','宝贝标题不能为空!',1,'regex',3),
        array('category_id','require','宝贝分类不能为空!',0,'',3),
        array('score_ratio','require','赠送积分比例不能为空!',1,'regex',3),
        array('images','require','主图不能为空!',1,'regex',3),
        array('express_tpl_id','require','运费模板ID不能为空!',1,'regex',3),
        array('shop_id','require','店铺ID不能为空!',1,'regex',1),
        array('content','require','商品详情不能为空!',1,'regex',3),
        array('service_days', 'isNumber', '售后天数必须是数字类型', 1, 'callback'),
        array('service_days', 'checkDays', '售后天数不正确', 1, 'callback'),
    );

    protected $_auto = array (
        array('ip','get_client_ip',1,'function'),
    );

    /**
     * 判断售后天数是否大于指定天数
     * @param unknown $var
     */
    protected function checkDays($var) {
        $serviceDays = M('goods_category')->where(['id' => I('post.category_id')])->getField('cate_service_days');
        if($serviceDays > 0 && $var < $serviceDays) {
            return false;
        }
        return true;
    }

    /**
     * 判断service_days是否为数字类型
     * @param unknown $var
     */
    protected function isNumber($var) {
        if (!is_numeric($var)) return false;
        return true;
    }



}