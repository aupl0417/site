<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午7:11:13    页面侧边
// +----------------------------------------------------------------------
 */
namespace Common\Widget;
use Think\Controller;
class SideWidget extends Controller {
    
    /**
     * 帮助中心分类
     */
    public function help($id = null) {
        $data   =   $this->curl('/help/category', null, 1);
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        $this->assign('data', $data);
        $this->display(T('Common@Widget:sideHelp'));
    }
    
    public function news($id = null) {
        $data   =   $this->curl('/news/category', null, 1);
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        $this->assign('data', $data);
        $this->display(T('Common@Widget:sideNews'));
    }
    
    /**
     * 头部购物车
     */
    public function cart() {
        $data['data']['style_num']   =   0;
        if (isset($_SESSION['user'])) {
            $data   =   $this->curl('/Cart/cart_total', ['openid' => session('user.openid')], 1);
        }
        $this->assign('data', $data['data']);
        $this->display(T('Common@Widget:cart'));
    }
    
    /**
     * 卖家中心底部
     */
    public function sellerBottom() {
        $this->display(T('Common@Widget:sellerBottom'));
    }
    
    /*
     * 右侧边栏
     */
    public function allRight() {
        //$this->display(T('Common@Widget:allRight'));
    }
    
    /**
     * 分类
     * @param array $position
     */
    public function menu($position = 'index') {
        $data   =   $this->curl('/Goods/category', null, 1);    //分类
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        $this->assign('data', $data);
        $this->display(T('Common@Widget:menu'));
    }
    
    public function newmenu($position = 'index') {
        $data   =   $this->curl('/Goods/category', null, 1);    //分类
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        $this->assign('data', $data);
        $this->display(T('Common@Widget:category'));
    }
    
    /**
     * 支付方式
     */
    public function pays($ordersId, $key = 'o_no') {
        $account    =   $this->curl('/Erp/account', ['openid' => session('user.openid')], 1);    //账户信息
        if (isset($account['data']) && !empty($account['data'])) $account    =   $account['data'];
        $this->assign('account', $account);
        $pays   =   [
                1   =>  [
                    'name'  =>  '使用余额支付',
                    'val'   =>  '账户余额：' . $account['a_freeMoney'],
                    'money'   =>  $account['a_freeMoney'],
                ],
                2   =>  [
                    'name'  =>  '使用唐宝支付',
                    'val'   =>  '唐宝余额：' . $account['a_tangBao'],
					'money'   =>  $account['a_tangBao'],
                ],
            //3   =>  '使用支付宝支付',
            //4   =>  '使用微信支付',
        ];
        $api    =   $key == 'o_no' ? '/Orders/view' : '/Orders/orders_shop_view';
        $data = $this->curl($api, [$key => $ordersId, 'openid' => session('user.openid')], 1);
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        if ($data['data']) {
            $this->builderForm()
            ->keyId($key)
            ->keyId('paytype')
            ->keyPass('password_pay', '安全密码', 1)
            ->data([$key => $ordersId])
            ->view();
        }
        $this->assign('data', $data);
        $this->assign('pays', $pays);
        $this->display(T('Common@Widget:pays'));
    }
    
    /**
     * 猜你喜欢
     */
    public function like() {
        $data   =   $this->curl('/Goods/love_goods', null, 1);
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        $this->assign('like', $data);
        $this->display(T('Common@Widget:like'));
    }
    
    /**
     * 为你推荐
     */
    public function recom() {
        //$this->api('/Goods/love_goods',['score_ratio' => 2],'score_ratio')->with('recom');
        $data   =   $this->curl('/Goods/hot_goods', null, 1);
        if (isset($data['data']) && !empty($data['data'])) $data    =   $data['data'];
        $this->assign('data', $data);
        $this->display(T('Common@Widget:recom'));
    }
    
    /**
     * 导航
     */
    public function nav() {
        $this->assign('data', C('WEB_CHANNEL'));
        $this->display(T('Common@Widget:nav'));
    }
}