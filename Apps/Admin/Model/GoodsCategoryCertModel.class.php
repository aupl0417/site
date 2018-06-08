<?php
namespace Admin\Model;
use Think\Model;
class GoodsCategoryCertModel extends Model {
    protected $tableName    =   'goods_category_cert';
    protected $_valiate     =   [
        ['category_id', 'require', '分类不能为空', 1],
        ['cert_name', 'require', '证书名称不能为空', 1]
    ];
}