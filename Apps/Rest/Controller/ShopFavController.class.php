<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 店铺关注
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class ShopFavController extends CommonController {
	//protected $action_logs = array('add','delete');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 添加收藏
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['shop_id']   店铺ID
    */
    public function add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('Common/ShopFav');

        $data['uid']		=$this->uid;
        $data['shop_id']	=I('post.shop_id');
        
        if (M('shop')->where(['uid' => $this->uid, 'id' => I('post.shop_id')])->find()) {
            $this->apiReturn(163);
        }

        if(!$data=$do->create($data)) $this->apiReturn(4,'',1,$do->getError());
        if($do->where($data)->find()){
        	//已被收藏
        	$this->apiReturn(162);
        }

        if($do->add()){
            M('shop')->where(['id'=>I('post.shop_id')])->setInc('fav_num');
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
    }

    /**
    * 店铺收藏列表
    * @param string $_POST['openid']    用户openid
    */
    public function shop_list(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $map['uid']=$this->uid;
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'FavShopView',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                //'fields'    =>'id,shop_id',
                'pagesize'  =>$pagesize,
                //'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('query'),
                'p'			=>I('post.p'),
                //'cache_name'=>md5(implode(',',$_POST).__SELF__),
                //'cache_time'=>C('CACHE_LEVEL.L'),                
            ));



        if($pagelist['list']){
			$area 	=	$this->cache_table('area');			
			foreach($pagelist['list'] as $key=>$val){
				$pagelist['list'][$key]['shop']['province']    =$area[$val['shop']['province']];
				$pagelist['list'][$key]['shop']['city']        =$area[$val['shop']['city']];
				$pagelist['list'][$key]['shop']['district']    =$area[$val['shop']['district']];
				$pagelist['list'][$key]['shop']['town']        =$area[$val['shop']['town']];

				$pagelist['list'][$key]['shop']['shop_logo']   =myurl($val['shop']['shop_logo'],80);
				$pagelist['list'][$key]['shop']['shop_url']    =shop_url($val['shop']['id'],$val['shop']['domain']);
			}			
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }        

    }

    /**
    * 删除店铺收藏
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['shop_id']   店铺ID    
    */
    public function delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $map['uid']	=$this->uid;
        $map['shop_id']	=array('in',I('post.shop_id'));

        $do=M('shop_fav');
        if($do->where($map)->delete()){
            M('shop')->where(['id'=>['in',I('post.shop_id')]])->setDec('fav_num');
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
    }

}