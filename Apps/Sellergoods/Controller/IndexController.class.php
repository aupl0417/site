<?php
namespace Sellergoods\Controller;
use Common\Builder\Auth;

class IndexController extends InitController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
		if(!empty($_SESSION['supplier_info'])) redirect(U('/Index/supplier_index'));
        $res = $this->doApi('/SellerGoodsManage/category',['openid' => session('user.openid')]);
        $res = json_decode(json_encode(($res)),TRUE);
        $this->assign('cate_list',$res['data']);


        if (!empty(I('get.url'))) {
            if(I('get.url') == 'cate'){
                $url = U('/cate');
            } else {
                $url = U(I('get.url'));
            }
        } else {
            $url = U('/goods');
        }

        $this->assign('first_url', $url);

//        if(I('get.url') == 'cate'){
//            $this->assign('first_url','/Cate/index');
//        }else{
//            $this->assign('first_url','/Goods/index');
//        }
        C('seo', ['title' => '商品管理']);
        $this->display();
    }

	/**
     * 供货商-管理首页
     * Create by liangfeng
     * 2017-09-13
     */
	public function supplier_index() {
		
        $res = $this->doApi('/SupplierGoodsManage/category',['openid' => session('user.openid')]);
        $res = json_decode(json_encode(($res)),TRUE);
        $this->assign('cate_list',$res['data']);

		if (!empty(I('get.url'))) {
            
            $url = U(I('get.url'));
            
        } else {
             $url = U('/Supplier/goods');
        }
        
           
        

        $this->assign('first_url', $url);

//        if(I('get.url') == 'cate'){
//            $this->assign('first_url','/Cate/index');
//        }else{
//            $this->assign('first_url','/Goods/index');
//        }
        C('seo', ['title' => '商品管理']);
        $this->display('Supplier/index');
    }
	
}