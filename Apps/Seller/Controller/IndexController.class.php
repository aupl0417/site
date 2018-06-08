<?php
namespace Seller\Controller;
use Common\Builder\R;

class IndexController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
//        $this->authApi('/ShopSetting/shop_info')->with('shop');
//        $s = microtime(true);
//        $this->authApi('/Seller/total')->with('total');
//        $e = microtime(true);
//        dump($e - $s);
        //$this->authApi('/Erp/account')->with('account');
        //$shopWaitPayment = R::getInstance(['url' => '/erp/get_seller_wait_payment', 'isAjax' => false])->auth();
        //多并发
        //$a = R::getInstance(['url' => ['payment' => '/erp/get_seller_wait_payment', 'shop' => '/shopSetting/shop_info', 'total' => '/seller/total', 'account' => '/erp/account'], 'rest' => ['rest2', 'rest', 'rest', 'rest'], 'isAjax' => false, 'data' => [['openid' => session('user.openid')],['openid' => session('user.openid')],['openid' => session('user.openid')],['openid' => session('user.openid')]]])->multiCurl();
        //$this->assign('shopWaitPayment', $shopWaitPayment['data']);
        $config = [
            'url'   => [
                'total' => '/Seller/total'
            ],
            'rest'  => [
                'rest',
            ],
            'data'  => [
                ['openid' => session('user.openid')]
            ],
        ];

        $res = R::getInstance($config)->multiCurl();
        $this->assign('total', $res['total']['data']);
        $this->assign('shop', $this->shop_info);
        C('seo', ['title' => '卖家中心']);
        $this->display();
    }
}