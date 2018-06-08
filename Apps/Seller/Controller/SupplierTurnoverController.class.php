<?php
namespace Seller\Controller;
use Common\Builder\R;
use Home\Controller\CommonController;
class SupplierTurnoverController extends CommonController {
    public function _initialize() {
        //parent::_initialize();
		C('cfg',getSiteConfig());
    }
	
	/**
     * 检查是否已经登录
     * Create by liangfeng
     * 2017-09-07
     */
	private function check_login(){
		if (!isset($_SESSION['user'])) {
            redirect(DM('user', '/login'));
            exit;
        }
	}
	/**
     * 检查是否已经登录(ajax)
     * Create by liangfeng
     * 2017-09-07
     */
	private function check_ajax_login(){
		if (!isset($_SESSION['user'])) {
            $this->ajaxReturn(['code'=>0,'msg'=>'请登录！']);
        }
	}
	
	/**
     * 营业额明细
     * Create by liangfeng
     * 2017-09-18
     */
	public function index(){		
		$this->check_login();
		$res = R::getInstance(['url' => ['sale_list' => '/Supplier/sale_list',], 'rest' => ['rest2'], 'data' => [['openid'=>session('user.openid'),'pagesize'=>10,'p'=>I('get.p')]]])->multiCurl();

		$this->assign('sale_list',$res['sale_list']);
		//$this->assign('withdrawals_list',$res['withdrawals_list']);
		$this->display();
	}
	/**
     * 提现明细明细
     * Create by liangfeng
     * 2017-09-18
     */
	public function withdrawals_list(){		
		$this->check_login();
		$res = R::getInstance(['url' => ['withdrawals_list'=>'/Supplier/withdrawals_list'], 'rest' => ['rest2'], 'data' => [['openid'=>session('user.openid'),'pagesize'=>10,'p'=>I('get.p')]]])->multiCurl();
		//dump($res['withdrawals_list']);

		//$this->assign('sale_list',$res['sale_list']);
		$this->assign('withdrawals_list',$res['withdrawals_list']);
		$this->display();
	}
	/**
     * 提现
     * Create by liangfeng
     * 2017-09-18
     */
	public function withdrawals(){
		$this->check_login();
		$res = R::getInstance(['url' => ['info' => '/Supplier/get_info'], 'rest' => ['rest2'], 'data' => [['openid'=>session('user.openid')]]])->multiCurl();
		//dump($res['info']['data']);
		//可提现金额
		$money = $res['info']['data']['sale_money'] - $res['info']['data']['withdrawals_money'];
		$this->assign('money',$money);
		
		$this->assign('charge',C('cfg.supplier')['charge']);
		$this->display();
	}
	
	
	/**
     * 提交提现申请
     * Create by liangfeng
     * 2017-09-18
     */
	public function ajax_withdrawals(){
		$this->check_ajax_login();
		//dump(I('post.'));
		//C('DEBUG_API');
		$res = R::getInstance(['url' => ['withdrawals' => '/Supplier/withdrawals'], 'rest' => ['rest2'], 'data' => [['openid'=>session('user.openid'),'money'=>I('post.money')]]])->multiCurl();
		//dump($res);
		$res = $res['withdrawals'];
		$this->ajaxReturn($res);
	}
}