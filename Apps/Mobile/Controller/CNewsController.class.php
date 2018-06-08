<?php
/**
 * -------------------------------------------------
 * c+ 公告列表
 * -------------------------------------------------
 * Create by lizuheng
 * -------------------------------------------------
 * 2017-04-01
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller\RestController;

class CNewsController extends CommonController {
	
    /**
     * 列表
     */
    public function index(){
		//C('DEBUG_API',true);
		$data['openid'] = session('user')['openid'];
	    //全部消息
		$res = $this->doApi2('/Erp/get_news',$data);
		$this->assign('pagelist',$res['data']);
		
		//print_r($res);
		$this->display();
    }
	/**
     * 分页
     * Create by Lizuheng
     * 2017-03-29
     */
    public function page(){
        //C('DEBUG_API',true);
        $data['openid']     = session('user.openid');
        $data['p']          = I('get.p');
		$res = $this->doApi2('/Erp/get_news',$data);
        $this->ajaxReturn($res);
    }
	/**
     * 详情
     */
    public function view(){
		//C('DEBUG_API',true);
		$data['id'] = I('get.id');
		$is_header  = I('get.is_header')?I('get.is_header'):0;
		$res = $this->doApi2('/Erp/news_view',$data);
		//print_r($res['data']);
		$this->assign('data',$res['data']);
		$this->assign('is_header',$is_header);
		$this->display();
    }
}