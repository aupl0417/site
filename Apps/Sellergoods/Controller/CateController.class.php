<?php
namespace Sellergoods\Controller;
class CateController extends ApiController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        //读取所有分类列表
        $res = $this->doApi('/SellerGoodsManage/category',['openid' => session('user.openid')],'',1);
		$this->assign('list',$res['data']);


        $this->assign('category_type', $this->category_type);
        C('seo', ['title' => '商品分类管理']);
        $this->display();
    }
    public function autoCate(){
        $this->display();
    }

}