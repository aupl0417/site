<?php
/**
 * 执行的任务
 */
namespace Common\Builder;
class Cron {
    
    /**
     * 关闭活动
     * @param array $map
     * 活动到期后关闭
     */
    public static function closeActivity($map) {
        if (M('activity')->where($map)->save(['status' => 2, 'is_sys' => 1])) {
            return true;  //已完成
        }
        return false;
    }
    
    /**
     * 关闭优惠券
     * @param array $map
     * 优惠券到期后关闭
     */
    public static function closeCouponBatch($map) {
        if (M('coupon_batch')->where($map)->save(['status' => 2, 'is_sys' => 1])) {
            return true;
        }
        return false;
    }

    /**
     * close coupon
     * @param $map array
     * @return bool
     */
    public static function closeCoupon($map) {
        if (M('coupon')->where($map)->save(['status' => 3, 'is_sys' => 1])) {
            return true;
        }
        return false;
    }
    
    /**
     * 关闭参与的活动
     * 15分钟过期，同时需要关闭订单
     */
    public static function closeParticipate($map) {
        $model  =   M();
        $model->startTrans();
        $sw1    =   M('activity_participate')->where($map['activity'])->save(['status' => 2, 'is_sys' => 1]);     //关闭参与的活动
        if (!$sw1) goto error;
        $sw2    =   M('orders_shop')->where($map['orders'])->save(['status' => 10, 'is_sys' => 1]);             //关闭订单
        if (!$sw2) goto error;
        
        $model->commit();
        return true;
        error:
            $model->rollback();
            return false;
    }
}