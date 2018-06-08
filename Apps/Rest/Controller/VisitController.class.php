<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 浏览记录
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class VisitController extends CommonController {
	//protected $action_logs = array('goods_add','goods_delete');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 新增浏览
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['goods_id']  商品ID
    */
    public function goods_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','goods_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('Common/GoodsVisit');

        $data['uid']		=$this->uid;
        $data['goods_id']	=I('post.goods_id');


        if(!$data=$do->create($data)) $this->apiReturn(4,'',1,$do->getError());

        if($do->where($data)->find()){
        	$do->where($data)->setInc('visit_num',1,C('CACHE_LEVEL.S'));
        	$this->apiReturn(1);
        }

        if($do->add()){
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
    }

    /**
    * @param int $param['uid']  用户ID
    * @param int $param['goods_id'] 商品ID
    */
    public function _add($param){
        $do=D('Common/GoodsVisit');

        if($do->where($param)->field('id')->find()){
            $do->where($param)->setInc('visit_num',1,C('CACHE_LEVEL.S'));
            return true;
        }

        if(!$do->create($param)) return false;
        if(!$do->add()) return false;
        return true;
    }

    /**
    * 获取最近n条浏览记录
    * @param string $_POST['openid']    用户openid
    */
    public function goods_list(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):20;
        if($limit>100) $limit=100;

        $map['uid']=$this->uid;
        
        $do=D('Common/GoodsVisitRelation');
        //->cache(true,C('CACHE_LEVEL.XXS'))
        $list=$do->relation(true)->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_id')->order('etime desc')->limit($limit)->select();

        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }

    }

    /**
    * 删除浏览记录
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['ig']        浏览记录ID
    */
    public function goods_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $map['uid']	=$this->uid;
        $map['id']	=I('post.id');

        $do=M('goods_visit');
        if($do->where($map)->delete()){
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
    }

}