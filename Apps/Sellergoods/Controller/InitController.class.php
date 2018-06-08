<?php
namespace Sellergoods\Controller;
use Common\Builder\Auth;
use Wap\Controller\CommonController;	//继承wap模块的公共文件
class InitController extends CommonController {
   

    protected $userInfo;
	protected $category_type = array(
        '1' => '手动分类',
        '2' => '自动分类'
    );
    public function _initialize() {
		parent::_initialize();
        $this->check_login();
        $this->assign('cfg', C('cfg'));
	}


	/**
     * 是否登录
     */
	public function check_login(){
	    if(empty($_SESSION['user'])){
	        redirect(C('sub_domain.user').'/login.html');
        }elseif(session('user.level_id') == 9 && !empty($_SESSION['supplier_info'])){ //供货商
			
        }elseif(session('user.shop_id') <= 0){ //未已开店
            //redirect(C('sub_domain.zhaoshang'));
            redirect(C('sub_domain.seller'));
        }
    }
}
