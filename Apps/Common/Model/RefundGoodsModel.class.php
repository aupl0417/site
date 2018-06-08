<?php
namespace Common\Model;
use Think\Model\RelationModel;
class RefundGoodsModel extends RelationModel
{
        protected $tableName = 'refund';
        protected $_link = array(
        'orders_goods' => array(
            'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'orders_goods', //映射的名称
            'class_name'        => 'orders_goods',  //关联的表名
            'foreign_key'       => 'id', // 关联的表被关联的id
            'mapping_key'       => 'og_id', // 主表的关联id
            'mapping_fields'     => 'images as g_images,goods_name,attr_list_id,attr_name',// 查询被关联的表字段
            'as_fields'         => 'g_images,goods_name,attr_list_id,attr_name', // 不定义会变成子集的形式
        ),
    );


}
?>