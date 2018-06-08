<?php
namespace Mobile\Controller;
use Think\Controller;
use \Mobile\Controller\MessageController as Message;
class IndexController extends CommonController {

    public function index(){
//var_dump(__ACTION__);
        $ads = $this->doApi2('/Ad/ads',['position_id' => '177,178,179,180,181,182,183,184,187,191,192,193,194,195']);
        $this->assign('ads',$ads);
        //print_r($ads);

        //C('DEBUG_API',true);
        $data = [];
        if (session('user')) $data['openid'] = session('user.openid');
        $love = $this->doApi2('/Search/love_list', $data);
        $this->assign('pagelist',$love['data']);
        //dump($love);
		

        //消息数量
//		$count = 0;
//		$res = $this->doApi2('/Notice/notice_count',['openid' => session('user.openid')]);
//		foreach($res['data'] as $v){
//			if($v['is_read']==0){
//				$count += $v['num'];
//			}
//		}
        /*
		$res = $this->doApi2('/Im/have_message',['openid' => session('user.openid')]);
		$count += $res['code'];
		$this->assign('count',$count > 99 ? 99 : $count);
		*/

        //秒杀
        //C('DEBUG_API',true);
//        $res = $this->doApi2('/Miaosha/top_goods',['num' => 12]);
//        $this->assign('miaosha',$res['data']);
        //print_r($res);

		$this->display();
    }

    public function love_page(){
        $res = $this->doApi2('/Search/love_list',I('get.'));
        $this->ajaxReturn($res);
    }

    /**
     * 关于乐兑
     * Create by Lazycat
     * 2017-02-10
     */
    public function about(){
        $res = $this->doApi2('/Help/about');
        $this->assign('rs',$res['data']);

        $this->display();
    }

    public function love()
    {
        dump(session('user.erp_uid'));
        $res = $this->doApi2('/search/love', ['num' => 4, 'score_type' => 1, 'erp_uid' => session('user.erp_uid')]);
        //$res    = $this->doApi2('/app/ad', ['erp_uid' => session('user.erp_uid')]);
        dump($res['data']);
    }
    public function love1()
    {
        dump(session('user.erp_uid'));
        //$res = $this->doApi2('/search/love', ['num' => 4, 'score_type' => 1, 'erp_uid' => session('user.erp_uid')]);
        $res    = $this->doApi2('/app/ad', ['erp_uid' => session('user.erp_uid')]);
        dump($res['data']);
    }
}