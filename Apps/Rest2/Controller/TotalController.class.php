<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 统计
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-17
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class TotalController extends ApiController {
    protected $action_logs = array();

	/**
     * subject: 统计商家在线商品数量
     * api: /Total/seller_goods_online
     * author: liangfeng
     * day: 2017-03-16
     *
     * [字段名,类型,是否必传,说明]
     * param: uid,string,1,用户ID
     */
    public function seller_goods_online($uid){
        $goods_num=M('goods')->where(['seller_id'=>$uid,'status'=>1,'num'=>['gt',0]])->count();
        M('shop')->where(['uid' => $uid])->save(['goods_num'=>$goods_num]);

        return $goods_num;   
    }
    /**
     * subject: Wap买家中心统计
     * api: /Total/buyer_total
     * author: Lazycat
     * day: 2017-01-17
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     */
    public function buyer_total(){
        $this->check('openid',false);
        $result['nick'] = $this->user['nick'];
        $result['fav_goods']    = $this->_user_fav_goods($this->user['id']);
        $result['fav_shop']     = $this->_user_fav_shop($this->user['id']);
        $result['coupon']       = $this->_user_coupon($this->user['id']);
        $result['orders']       = A('Orders')->_buyer_orders_count($this->user['id']);
        $result['service']      = A('Orders')->_buyer_service_count($this->user['id']);
        $result['refund']       = A('Orders')->_buyer_refund_count($this->user['id']);

        $this->apiReturn(['code' => 1,'data' => $result]);
    }


    /**
     * 关注商品统计
     * @param int $uid 用户ID
     */
    public function _user_fav_goods($uid){
        $count = M('goods_fav')->where(['uid' => $uid])->count();
        return $count;
    }

    /**
     * 关注的店铺
     * @param int $uid 用户ID
     */
    public function _user_fav_shop($uid){
        $count = M('shop_fav')->where(['uid' => $uid])->count();
        return $count;
    }

    /**
     * 可用优惠券数量
     * @param int $uid 用户ID
     */
    public function _user_coupon($uid){
        $count = M('coupon')->where(['uid' => $uid,'status' => 1,'is_use' => 0,'eday' => ['egt',date('Y-m-d')]])->count();
        return $count;
    }
	/**
    * 用户广告统计
    * @param int    $_POST['uid']   用户ID
    */
    public function user_ad($uid){
        //广告统计
        $do=M('ad');
        $result['status'][0]    =$do->where(['uid' => $uid,'status' => 0])->count();    //待付款
        $result['status'][1]    =$do->where(['uid' => $uid,'status' => 1])->count();    //已付款
        $result['status'][2]    =$do->where(['uid' => $uid,'status' => 2])->count();    //强制下架

        $result['status']['all']=array_sum($result['status']);

        $result['status'][3]    =$do->where(['uid' => $uid,'status' => 1,'days' => ['like','%'.date('Y-m-d').'%']])->count();   //投放中
        $result['status'][4]    =$do->where(['uid' => $uid,'status' => 1,'sday' => ['gt',date('Y-m-d')]])->count(); //待投放
        $result['status'][5]    =$do->where(['uid' => $uid,'status' => 1,'eday' => ['lt',date('Y-m-d')]])->count(); //已过期

        //素材统计
        $do=M('ad_sucai');
        $result['sucai'][0]     =$do->where(['uid' => $uid,'status' => 0])->count();    //待审核
        $result['sucai'][1]     =$do->where(['uid' => $uid,'status' => 1])->count();    //审核通过
        $result['sucai'][2]     =$do->where(['uid' => $uid,'status' => 2])->count();    //审核未通过

        $result['sucai']['all'] =array_sum($result['sucai']);

        //消费
        $result['money']=M('ad')->where(['uid' => $uid,'status' => 1])->sum('money_pay');

        return $result;
    }
}