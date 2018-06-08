<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家-评价管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
use Think\Exception;

class SellerRateController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
    * 买家对商品的评价
    * @param string $_POST['openid']    用户openid
    * @param flost  $_POST['rate']      评价等级
    * @param int    $_POST['pagesize']  分页数量
    * @param int    $_POST['is_shuadan']刷单(2:怀疑刷单，1:刷单)
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

        $map['seller_id']   =$this->uid;
        if(I('post.rate')!='') $map['rate']=I('post.rate');
        if(I('post.is_shuadan')!='') $map['is_shuadan']=I('post.is_shuadan');
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'OrdersGoodsCommentRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,status,like_num,s_no,orders_goods_id,goods_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,is_anonymous,is_shuadan',
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
			foreach($pagelist['list'] as $key=>$val){
				$pagelist['list'][$key]['user']					=imgsize_list($val['user'],'face',80);
				$pagelist['list'][$key]['user']['level_name']	=$user_level[$val['user']['level_id']];
				$pagelist['list'][$key]['orders_goods']			=imgsize_list($val['orders_goods'],'images',160);
				$pagelist['list'][$key]['rate_name']			=$rate_name[$val['rate']];
				$pagelist['list'][$key]['status_name']			=$status_name[$val['status']];
				
				if($val['is_shuadan'] == 2){
					$result = M('order_apply')->where(['c_id'=>$val['id']])->find();
					if($result){
						$pagelist['list'][$key]['shuadan'] = 1;
					}
				}
			}			
			$this->apiReturn(1,['data' => $pagelist]);
		}else $this->apiReturn(3);           
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
        $map['seller_id']   = $this->uid;
        $map['id']          = I('post.id');
        $model              = D('OrdersGoodsCommentRelation');
        $fields             = 'id,atime,status,like_num,s_no,orders_goods_id,goods_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,is_anonymous';
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
        $data['type']       =   1;  //1为卖家
        $data['seller_id']  =   $this->uid;
        $map  = [
            'seller_id'     =>  $this->uid,
            'reply_count'   =>  0,
            'id'            =>  $data['comment_id'],
        ];

        $data['uid']  = M('orders_goods_comment')->where($map)->getField('uid');

        if (!$data['uid']) $this->apiReturn(0, '', 1, '您无权限回复！');

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

    /**
     * 卖家刷单申诉
     */
    public function appeal() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign','remark','s_no','attr_list_id','c_id');
        $this->_need_param();
        $this->_check_sign();

		$data = I('post.');
		
		$do = M('order_apply');
		$rs = $do->where(['c_id'=>$data['c_id'],'status'=>array('neq','3')])->find();
		if($rs){
			$this->apiReturn(0,array('data'=>$res['data']),1,"该订单已经申诉过了，不能重复申诉！"); 
		}
        $map  = [
            'seller_id'     =>  $this->uid,
            'c_id'          =>  $data['c_id'],
			's_no'          =>  $data['s_no'],
            'remark'        =>  $data['remark'],
			'attr_list_id'  =>  $data['attr_list_id'],
			'status'        =>  1,
			'atime'         =>  date('Y-m-d H:i:s'),
			'ip'            =>  $this->ip,
			'images'        =>  $data['images'],
        ];
        $result  = $do->add($map);
		if($result !==false){
			$this->apiReturn(1);
		}
		$this->apiReturn(0);
    }
	
	/**
    * 刷单申诉列表 
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['pagesize']  分页数量
    * @param int    $_POST['p']         当前页数
    */
    public function order_apply_list(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name=['拒绝','待审核','通过',"取消申诉"];

        $map['seller_id']   =$this->uid;
        if(I('post.cid')!='') $map['c_id']=I('post.cid');

        $pagesize=I('post.pagesize')?I('post.pagesize'):10;
        $pagelist=pagelist(array(
                'table'     =>'order_apply',
                'do'        =>'M',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,status,s_no,remark,images',
                'pagesize'  =>$pagesize,
                'p'         =>I('post.p')?I('post.p'):1,
              /*   'cache_name'=>md5(implode(',',$_POST).__SELF__),
                'cache_time'=>C('CACHE_LEVEL.XL'), 		 */		
            ));

        if($pagelist['listnum']>0) {
			$user_level 	=	$this->cache_table('user_level');
			foreach($pagelist['list'] as $key=>$val){
				$pagelist['list'][$key]['status_name']			=$status_name[$val['status']];
			}			
			$this->apiReturn(1,['data' => $pagelist]);
		}else $this->apiReturn(3);           
    }
	
	/**
     * 获取申诉详情
     */
    public function apply_view() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign', 'id');
        $this->_need_param();
        $this->_check_sign();
		
        $status_name=['拒绝','待审核','通过',"取消申诉"];
        $map['seller_id']   = $this->uid;
		$map['id']          = I('post.id');
		
        $data               = M("order_apply")->cache(true)->where($map)->find();
        if ($data) {
            $data['status_name']    =   $status_name[$data['status']];
            $this->apiReturn(1, ['data' => $data]);
        }
        $this->apiReturn(0);
    }
	/**
     * 取消申诉
     */
    public function cancel() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign', 'id');
        $this->_need_param();
        $this->_check_sign();
		
        $map['seller_id']   = $this->uid;
		$map['id']          = I('post.id');
    
        $data               = M("order_apply")->where($map)->save(['status'=>3]);
        if ($data) {
            $this->apiReturn(1);
        }
        $this->apiReturn(0);
    }
}