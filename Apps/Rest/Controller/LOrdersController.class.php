<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/16
 * Time: 20:51
 */

namespace Rest\Controller;


use Think\Controller\RestController;

class LOrdersController extends RestController
{
    protected $s = ['695622','692355','691037', '690505'];
    protected function _initialize() {
        if (!in_array(I('post.s'), $this->s) || !IS_POST) exit();
    }

    public function orders() {
        $do     = D('Common/GoodsTmallViewRelation');
        $map    = [
            'status'    =>  2,
            'seller_id' =>  I('post.s'),
        ];
        $list           = $do->relation(true)->where($map)->order('id asc')->select();
        $districtModel  = M('area');
        $tModel         = M('goods_tmall');
        $field          = 'tk_url2,tk_url,detail_url,coupon_url,coupon_price';
        $field          = true;
        foreach ($list as &$v) {
            $v['province_name'] =   $districtModel->cache(true)->where(['id' => $v['province']])->getField('a_name');
            $v['city_name']     =   $districtModel->cache(true)->where(['id' => $v['city']])->getField('a_name');
            $v['district_name'] =   $districtModel->cache(true)->where(['id' => $v['district']])->getField('a_name');
            $v['town_name']     =   $districtModel->cache(true)->where(['id' => $v['town']])->getField('a_name');
            foreach ($v['goods'] as &$val) {
                $val['taobao']   =   $tModel->cache(true)->where(['goods_id' => $val['goods_id']])->field($field)->find();
            }
        }
        if ($list) $this->ajaxReturn(['code' => 1, 'data' => $list]);
        $this->ajaxReturn(['code' => 0, 'msg' => '没有订单']);
    }
}