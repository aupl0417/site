<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/8
 * Time: 9:13
 */

namespace Sellergoods\Model;


use Think\Model;

class GoodsAttrValueModel extends Model
{
    protected $tableName = 'goods_attr_value';

    protected $_validate = array(
        array('attr_id','require','库存属性ID不能为空!',1,'regex',3),
        //array('goods_id','require','商品ID不能为空!',1,'regex',3),

    );

    protected $_auto = array (
        array('ip','get_client_ip',1,'function'),
    );
}