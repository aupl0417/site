<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/24
 * Time: 13:47
 */

namespace Seller\Controller;


use Common\Builder\Auth;
use Common\Builder\R;
use Think\Controller;

class InitController extends Controller
{
    protected $shop_info = [];
    protected function _initialize() {
        if (!isset($_SESSION['user'])) redirect(DM('user', '/login'));  //没登陆则跳转到登陆界面
//        if (session('user.parent_uid')) {
//            if (Auth::getInstance()->check() == false) {
//                $this->display('Public/noAccess');
//                exit();
//            }
//        }
        if (IS_POST) {
            $url = I('get.ret');
            if ($url) {
                $data= I('post.');
                foreach ($data as &$v) {
                    if (is_array($v)) $v = join(',',$v);
                }
                unset($v);
                R::getInstance(['url' => $url, 'data' => $data])->auth();
            }
        } else {
            $shopConfig = [
                'url'   =>  '/ShopSetting/shop_info',
                'isAjax'=>  false,
                'rest'  =>  'rest',
            ];
            $shopInfo = R::getInstance($shopConfig)->auth();    //获取店铺信息
            if ($shopInfo['code'] != 1) redirect(DM('zhaoshang').'/Joinshop');
            $this->shop_info = $shopInfo['data'];
            $this->assign('shop_info', $this->shop_info);
        }
        C('cfg',getSiteConfig());
    }

    /**
     * subject: 找不到方法时来到这里
     * api: _empty
     * author: Mercury
     * day: 2017-03-24 15:27
     * [字段名,类型,是否必传,说明]
     */
    public function _empty() {

    }
}