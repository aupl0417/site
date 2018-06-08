<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 订单中各流程的超时处理
| 数据多的情况下要用队列来处理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class ApiController extends RestController {
	protected $allowMethod    	= array('get','post','put'); // REST允许的请求类型列表
	protected $allowType      	= array('html','xml','json'); // REST允许请求的资源类型列表
	
    /**
    * 获取任务item
    */
    public function url(){
		$result['code'] =1;
		$result['url'] 	= 'http://wap.yunzhiluo.cn';
		
		$this->response($result);
    }


}