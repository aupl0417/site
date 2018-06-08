<?php
/**
 * -------------------------------------------------
 * 消息列表
 * -------------------------------------------------
 * Create by lizuheng <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-21
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller\RestController;
use Common\Notice\System;
class NoticeController extends CommonController {
	
    /**
     * 消息列表
     */
    public function index(){
		$this->check_logined();
		$data['openid'] = session('user')['openid'];
	    //全部消息
		$res1 = $this->doApi2('/Notice/index',$data);
		$this->assign('all_list',$res1['data']);
		//未读消息
		$data['status'] = 0;
		$res2 = $this->doApi2('/Notice/index',$data);
		$this->assign('read_list',$res2['data']);
		//已读消息
		$data['status'] = 1;
		$res3 = $this->doApi2('/Notice/index',$data);
		$this->assign('no_read_list',$res3['data']);

		$this->display();
    }
	
	/**
     * 消息分页
     * Create by lizuheng
     * 2017-03-06
     */
    public function notice_page(){
		$this->ajax_check_logined();
        //C('DEBUG_API',true);
        $data['openid']     = session('user.openid');
        $data['p']          = I('get.p');
		$data['status']     = I('get.status');

		$res = $this->doApi2('/Notice/index',$data);
        $this->ajaxReturn($res);
    }
    /**
     * 消息详情
     */
    public function view(){
		$this->check_logined();
		$data['id']     = I('get.id');
		$data['openid'] = session('user')['openid'];
		$res = $this->doApi2('/Notice/read',$data);

		$this->assign('data',$res['data']);
		$this->display();
    }
	/**
     * 标记为已读和未读
     */
    public function set_read(){
		$this->ajax_check_logined();
		//C('DEBUG_API',true);
		$data['id']      = I('post.id');
		$data['is_read'] = I('post.is_read');
		$data['openid']  = session('user')['openid'];

		$res = $this->doApi2('/Notice/saveRead',$data);
		$this->ajaxReturn($res);
    }
	
	/**
     * 删除消息
     */
    public function delete_notice(){
		$this->ajax_check_logined();
		//C('DEBUG_API',true);
		$data['id']     = I('post.id');
		$data['openid']    = session('user')['openid'];

		$res = $this->doApi2('/Notice/delete_notice',$data);
		$this->ajaxReturn($res);
    }
}