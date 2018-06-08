<?php
namespace Common\Model;
use Think\Model;
class CouponRecomModel extends Model {
    protected $tableName = 'coupon_recom';
    
    protected $_validate = [
        ['category_id', 'require', '推荐分类不能为空', 1],
        ['coupon_id', 'require', '推荐的优惠券不能为空', 1],
    ];
    
}