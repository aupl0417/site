<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 收藏管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class FavController extends CommonController {
	protected $action_logs = array('goods_add','goods_delete');

    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 添加收藏
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
        if (M('goods')->where(['seller_id' => $this->uid, 'id' => I('post.goods_id')])->find()) {
            $this->apiReturn(164);
        }
        $do=D('Common/GoodsFav');

        $data['uid']		=$this->uid;
        $data['goods_id']	=I('post.goods_id');


        if(!$data=$do->create($data)) $this->apiReturn(4,'',1,$do->getError());

        if($do->where($data)->find()){
        	//已被收藏
        	$this->apiReturn(161);
        }

        if($do->add()){
            M('goods')->where(['id' => $data['goods_id']])->setInc('fav_num', 1);   //加一
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
    }

    /**
    * 商品收藏列表
    * @param string $_POST['openid']    用户openid
    */
    public function goods_list(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $map['uid']=$this->uid;
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'GoodsFavRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'id,goods_id',
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'relationLimit'     =>array('goods_attr_list',1),
                'action'    =>I('post.action'),
                'query'     =>I('query'),
                'p'			=>I('post.p'),
                //'cache_name'=>'fav_goods_list_' . $this->uid,
                //'cache_time'=>C('CACHE_LEVEL.L'),
            ));

        if($pagelist['list']){
            if(I('post.imgsize')){
                foreach($pagelist['list'] as $key=>$val){
                    $pagelist['list'][$key]['goods']['images']=myurl($val['goods']['images'],I('post.imgsize'));
                }
            }        
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }        

    }

    /**
    * 删除商品收藏
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        收藏记录ID
    */
    public function goods_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $map['uid']	=$this->uid;
        $map['id']	=array('in',I('post.id'));

        $do=M('goods_fav');
        $goods  =   $do->where($map)->field('goods_id')->select();
        foreach ($goods as $val) {
            $ids    .=  $val['goods_id'] . ',';
        }
        unset($goods,$val);
        if($do->where($map)->delete()){
            M('goods')->where(['id' => ['in', trim($ids, ',')]])->setDec('fav_num');  //减一
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
    }


    /**
    * 取最新n条收藏记录
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['limit']     提取记录数量
    */
    public function goods_topN(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):8;

        $do=D('GoodsFavRelation');
        $list=$do->relation(true)->relationLimit('goods_attr_list',1)->cache(true,C('CACHE_LEVEL.XS'))->where(['uid' => $this->uid])->field('id,goods_id')->order('id desc')->limit($limit)->select();

        if($list){
            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);
    }

}