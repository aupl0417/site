<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 买家评价管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class RateController extends CommonController
{
	protected $action_logs = array('rate_goods_edit');
	public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
    * 买家对商品的评价
    * @param string $_POST['openid']    用户openid
    * @param flost  $_POST['rate']      评价等级
    * @param int    $_POST['pagesize']  分页数量
    */
    public function rate_goods_list(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $rate_name=[0 => '中评',1 => '好评','-1' => '差评'];
        $status_name=['未生效','已生效'];

        $map['uid']   =$this->uid;
        if(I('post.rate')!='') $map['rate']=I('post.rate');

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'OrdersGoodsCommentBuyerRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,status,like_num,s_no,orders_goods_id,goods_id,shop_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,is_anonymous',
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
                'cache_name'=>md5(implode(',',$_POST).__SELF__),
                'cache_time'=>C('CACHE_LEVEL.XL'),                
            ));

        if($pagelist['listnum']>0) {
			$user_level 	=	$this->cache_table('user_level');
			//数据格式化
			foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['images']               =imgsize_cmp($val['images'],50);
				$pagelist['list'][$key]['seller']				=imgsize_list($val['seller'],'face',80);
				$pagelist['list'][$key]['seller']['level_name']	=$user_level[$val['seller']['level_id']];
				$pagelist['list'][$key]['orders_goods']			=imgsize_list($val['orders_goods'],'images',160);
				$pagelist['list'][$key]['shop']					=imgsize_list($val['shop'],'shop_logo',100);
				$pagelist['list'][$key]['rate_name']			=$rate_name[$val['rate']];
				$pagelist['list'][$key]['status_name']			=$status_name[$val['status']];
			}			
			$this->apiReturn(1,['data' => $pagelist]);
		}
        else $this->apiReturn(3);
    }


    /**
    * 获取可修改的中差评记录详情
    * @param string $_POST['openid']    用户openid
    * @param int 	$_POST['id']		评价记录ID
    */
    public function rate_goods_view(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rate_name=[0 => '中评',1 => '好评','-1' => '差评'];
        $status_name=['未生效','已生效'];

        $rs=D('OrdersGoodsCommentBuyerRelation')->relation(true)->where(['uid' => $this->uid,'id' => I('post.id')])->field('etime,ip',true)->find();

        //找不到记录
        if(!$rs) $this->apiReturn(3);	

        //评价已生效，不可更改
        //if($rs['status']==1) $this->apiReturn(804);	

        //已是好评，不可更改！
        //if($rs['rate']==1) $this->apiReturn(805); 

        //您已修改过评价，不可再次修改！
        //if($rs['is_change']==1) $this->apiReturn(806);	

        //评价已超过30天，不可修改！
        //if($rs['atime'] < date('Y-m-d H:i:s',time()-86400*30)) $this->apiReturn(807);
		
		$user_level 	=	$this->cache_table('user_level');
        //数据格式化
        $rs['seller']				=imgsize_list($rs['seller'],'face',80);
        $rs['seller']['level_name']	=$user_level[$rs['seller']['level_id']];
        $rs['orders_goods']			=imgsize_list($rs['orders_goods'],'images',160);
        $rs['shop']					=imgsize_list($rs['shop'],'shop_logo',100);
        $rs['rate_name']			=$rate_name[$rs['rate']];
        $rs['status_name']			=$status_name[$rs['status']];
		
		//评价店铺信息
		$rs['shop_comment'] = M('orders_shop_comment')->where(['uid' => $this->uid,'s_no' => $rs['s_no']])->find();

        $this->apiReturn(1,['data' => $rs]);
    }    

	
    /**
    * 修改评价
    * @param string $_POST['openid']    用户openid
    * @param int 	$_POST['id']		评价记录ID
    * @param string 	$_POST['content']		评价内容
    * @param string 	$_POST['images']		晒图片
    */
    public function rate_goods_edit(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','content','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('uid'=>$this->uid));
        $res=$orders->b_goods_rate_edit(I('post.'));
        $this->apiReturn($res['code'],'',1,$res['msg']);
    }

    /**
     * 获取评价详情
     */
    public function view() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign', 'id');
        $this->_need_param();
        $this->_check_sign();
        $rate_name=[0 => '中评',1 => '好评','-1' => '差评'];
        $status_name=['未生效','已生效'];
        $map['uid']         = $this->uid;
        $map['id']          = I('post.id');
        $model              = D('OrdersGoodsCommentBuyerRelation');
        $fields             = 'id,atime,status,like_num,s_no,orders_goods_id,goods_id,shop_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,is_anonymous';
        $data               = $model->relation(true)->where($map)->field($fields)->find();
        if ($data) {
            $data['status_name']    =   $status_name[$data['status']];
            $data['rate_name']      =   $rate_name[$data['rate']];
            $this->apiReturn(1, ['data' => $data]);
        }
        $this->apiReturn(0);
    }

    /**
     * 回复
     */
    public function reply() {
        //to do your coding
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign', 'comment_id', 'content');
        $this->_need_param();
        $this->_check_sign();

        $data = I('post.');
        $data['type']       =   2;  //1为卖家
        $data['uid']        =   $this->uid;
        $map  = [
            'uid'           =>  $this->uid,
            'reply_count'   =>  1,
            'id'            =>  $data['comment_id'],
        ];

        $data['seller_id']  = M('orders_goods_comment')->where($map)->getField('seller_id');

        if (!$data['seller_id']) $this->apiReturn(0, '', 1, '您无权限回复！');

        $model = D('GoodsCommentReply');

        if (!$model->create($data)) $this->apiReturn(0, '', 1, $model->getError());
        $model->startTrans();
        try {
            if (!$model->add()) throw new Exception('回复评论失败！');
            if (!M('orders_goods_comment')->where($map)->setInc('reply_count')) throw new Exception('更新评价失败！');
            $model->commit();
            $this->apiReturn(1);
        } catch (Exception $e) {
            $model->rollback();
            $this->apiReturn(0, '', 1, $e->getMessage());
        }
    }
}