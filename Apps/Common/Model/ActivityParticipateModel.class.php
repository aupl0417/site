<?php
namespace Common\Model;
use Think\Model;
class ActivityParticipateModel extends Model {
    protected $tableName    =   'activity_participate';
    
    protected $_validate    =   [
        ['activity_id', 'require', '促销活动不能为空', 1],
        ['s_no', 'require', '订单号不能为空', 1],
        ['calc_before_money', 'require', '结算前金额不能为空', 1],
        ['calc_after_money', 'require', '结算后金额不能为空', 1],
        ['full_value', 'require', '优惠信息不能为空', 1],
        ['max_num', 'require', '最大购买数量不能为空', 1],
        ['full_money', 'require', '最低消费金额不能为空', 1],
        ['type_id', 'require', '活动类型ID不能为空', 1],
        ['shop_id', 'require', '促销活动不能为空', 1],
        ['uid', 'require', '用户不能为空', 1]
    ];
}