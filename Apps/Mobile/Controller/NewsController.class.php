<?php
/**
 * -------------------------------------------------
 * 乐兑公告列表
 * -------------------------------------------------
 * Create by lizuheng
 * -------------------------------------------------
 * 2017-04-01
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller\RestController;

class NewsController extends CommonController {
	
    /**
     * 列表
     */
    public function index(){
		//C('DEBUG_API',true);
	    //全部消息
		$res = $this->doApi2('/News/index');
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
        $data['p']          = I('get.p');
		$res = $this->doApi2('/News/index',$data);
        $this->ajaxReturn($res);
    }
	/**
     * 详情
     */
    public function view(){
		//C('DEBUG_API',true);
		$data['id'] = I('get.id');
		$res = $this->doApi2('/News/view',$data);
		$this->assign('data',$res['data']);
		
		$is_header  = I('get.is_header')?I('get.is_header'):0;
		$this->assign('is_header',$is_header);
		//print_r($res);
		$this->display();
    }
}