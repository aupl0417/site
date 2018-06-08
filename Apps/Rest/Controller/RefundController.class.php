<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 买家退款 - 已付款未发货退款
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
class RefundController extends CommonController{
	protected $action_logs = array('add','cancel','express_add');
	/**
	* 可退商品
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['s_no']		订单号
	* @param int 	$_POST['imgsize']	图片尺寸
    * @param int    $_POST['orders_goods_id']   订单中商品ID
	*/
	public function goods(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();	

		$orders=new \Common\Controller\RefundController(['uid' => $this->uid,'s_no' => I('post.s_no')]);
		$res=$orders->goods(I('post.'));
		$this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}

	/**
	* 创建退款订单
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['s_no']		订单号	
    * @param int    $_POST['orders_goods_id']   订单中商品ID
    * @param float  $_POST['price']             退款金额
    * @param int    $_POST['num']               退掉商品数量
    * @param string $_POST['reason']            退款原因
	*/
	public function add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','s_no','reason','orders_goods_id','sign');
        $this->_need_param();
        $this->_check_sign();
        	
		$orders=new \Common\Controller\RefundController(['uid' => $this->uid,'s_no' => I('post.s_no')]);
		$res=$orders->add(I('post.'));
		$this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}
    
	/**
	 * 修改退款申请
	 * @param string $_POST['openid']    用户openid
	 * @param string $_POST['s_no']      订单号
	 * @param int    $_POST['r_no']              退款单号
	 * @param float  $_POST['price']             退款金额
	 * @param int    $_POST['num']               退掉商品数量
	 * @param int    $_POST['type']              1退货退款，2只退款
	 * @param string $_POST['reason']            退款原因
	 * @param string $_POST['images']            证据图片，多张用逗号隔开
	 */
	public function edit(){
	    //频繁请求限制,间隔300毫秒
	    $this->_request_check();
	
	    //必传参数检查
	    $this->need_param=array('openid','r_no','s_no','reason','sign');
	    $this->_need_param();
	    $this->_check_sign();
	
	    $orders=new \Common\Controller\RefundController(['uid' => $this->uid,'s_no' => I('post.s_no')]);
	    $res=$orders->edit(I('post.'));
	    $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}
	
	/**
	* 取消退款
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['s_no']		订单号		
	* @param string $_POST['r_no']		退款单号		
	*/
	public function cancel(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','sign');
        $this->_need_param();
        $this->_check_sign();

		$orders=new \Common\Controller\RefundController(['uid' => $this->uid,'s_no' => I('post.s_no')]);
		$res=$orders->cancel(I('post.r_no'));
		$this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}
	
	/**
	* 创建运费退款
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['s_no']		订单号	
    * @param float  $_POST['price']             退款金额
    * @param string $_POST['reason']            退款原因
	*/
	public function express_add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','price','reason','sign');
        $this->_need_param();
        $this->_check_sign();
        	
		$orders=new \Common\Controller\RefundController(['uid' => $this->uid,'s_no' => I('post.s_no')]);
		$res=$orders->express_add(I('post.'));
		$this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}	

	/**
	* 退款详情
	* @param string $_POST['openid']	用户openid	
	* @param string $_POST['r_no']	退款单号
	*/
	public function view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','r_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $type_name	=['','退货并退款','只退款','只退运费'];
        $status_name=[1=>'买家申请退款',2=>'卖家拒绝退款',3=>'买家修改退款',4=>'卖家同意退款退货',5=>'买家寄回退货', 10 => '等待仲裁',20=>'买家取消退款',100=>'退款成功'];

        $do=D('Common/RefundRelation');
        $rs=$do->relation(['seller','shop','orders_goods','logs','orders_shop'])->where(['r_no' => I('post.r_no'),'uid' => $this->uid])->field('etime,ip',true)->find();
        if(!$rs) $this->apiReturn(3);
        
        //当前可以退的运费
        $countRefundExpress         =   M('refund')->where(['s_no' => $rs['s_no'], 'id' => ['neq', $rs['id']], 'status' => ['notin', '20,100']])->field('SUM(refund_express) as countrefundexpress')->find();
        $rs['express_money']        =   $rs['orders_shop']['express_price_edit'];
        if ($countRefundExpress['countrefundexpress'] && $countRefundExpress['countrefundexpress'] > 0 ) {
            $rs['express_money']    =   round( $rs['orders_shop']['express_price_edit'] - $countRefundExpress['countrefundexpress'], 2);
        }
        $rs['is_refund_express']    =   $rs['express_money'] > 0 ? 1 : 0;   //是否可以退运费
        
        $rs['logs']         =D('Common/RefundLogsRelation')->relation(true)->where(['r_id' => $rs['id']])->field('etime,ip',true)->order('id desc')->select();
        //数据格式化
        foreach($rs['logs'] as $i => $val){
            if($val['remark']) $rs['logs'][$i]['remark'] = html_entity_decode($val['remark']);
            if($val['images']) $rs['logs'][$i]['images'] = explode(',',rtrim($val['images'], ','));
            if(!empty($val['express_company_id']) && !empty($val['express_code'])) {
                $rs['express_company_id']   =   $val['express_company_id'];
                $rs['express_code']         =   $val['express_code'];
                $rs['express_company']      =   M('express_company')->where(['id' => $rs['express_company_id']])->cache(true)->getField('company');
            }
            $rs['logs'][$i]['status_name']  =   $status_name[$val['status']];
            //是否有退货地址
            if($val['status']==4){
                $rs['address']=html_entity_decode($val['remark']);
            }            
        }
        $rs['can_money']    =number_format($rs['money'] - ($rs['activity_money']), 2);
        $rs['orders_goods']	=imgsize_list($rs['orders_goods'],'images',160);
        $rs['shop']			=imgsize_list($rs['shop'],'shop_logo',100);
        $rs['type_name']	=$type_name[$rs['type']];
        $rs['status_name']	=$status_name[$rs['status']];   
        $rs['can_express']  =number_format($rs['orders_shop']['express_price_edit'] - $rs['orders_shop']['refund_express'], 2);
		if(!empty($rs['orders_goods'])){
			//获取该商品的数量和总金额
			$goods=M('orders_goods')
					->where(['id' => $rs['orders_goods_id']])
					->field('id,score_ratio,refund_action_num,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price')
					->find();
			//已申请退款中的商品的数量和金额
			$total=M('refund')
				->where([
				'orders_goods_id'   => $rs['orders_goods_id'],
				'orders_status'     => $rs['orders_status'],
				'status'            => ['not in','100,20'],
				'id'				=> ['neq',$rs['id']],
				])
			->field('sum(num) as num,sum(money) as money')
			->find();
			//计算还可以退款的数量和金额
			$rs['can_num'] = $goods['can_num'] - $total['num'];
			$rs['can_money'] = round(($goods['can_price'] - ($total['money'])),2);
		}else{
			$rs['can_num'] = $rs['num'];
			$rs['can_money'] = round($rs['money'], 2);
		}
        $this->apiReturn(1,['data' => $rs]);
	}

	/**
	* 列出某订单中申请退款的商品
	* @param string $_POST['openid']	用户openid	
	* @param string $_POST['s_no']		订单号	
	*/
	public function item_list(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();	

        $type_name	=['','退货并退款','只退款','只退运费'];
        $status_name=[1=>'退款中',2=>'卖家拒绝退款',3=>'买家修改退款',4=>'卖家同意退款',5=>'买家寄回退货', 10 => '等待仲裁',20=>'取消退款',100=>'退款成功'];

        $do=D('Common/RefundRelation');
        $list=$do->relation(['seller','shop','orders_goods'])->where(['s_no' => I('post.s_no'),'uid' => $this->uid])->field('etime,ip',true)->order('id desc')->select();
        if(!$list) $this->apiReturn(3);

        foreach($list as $i => $val){
	        $list[$i]['orders_goods']	=imgsize_list($val['orders_goods'],'images',160);
	        $list[$i]['shop']			=imgsize_list($val['shop'],'shop_logo',100);
	        $list[$i]['type_name']		=$type_name[$val['type']];
	        $list[$i]['status_name']	=$status_name[$val['status']];           	
        }
     
        $this->apiReturn(1,['data' => $list]);        	
	}

    /**
    * 退款商品列表
	* @param string $_POST['openid']	用户openid    
    */
    public function refund_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $type_name	=['','退货并退款','只退款','只退运费'];
        $status_name=[1=>'退款中',2=>'卖家拒绝退款',3=>'买家修改退款',4=>'卖家同意退款退货',5=>'买家寄回退货', 10 => '等待仲裁',20=>'取消退款',100=>'退款成功'];

        $map['uid']	= $this->uid;
        $map['orders_status']   =   ['lt', 4];
        if (isset($_POST['s_no']) && !empty(I('post.s_no'))) $map['s_no'] = I('post.s_no');
        if (isset($_POST['r_no']) && !empty(I('post.r_no'))) $map['r_no'] = I('post.r_no');
        if (isset($_POST['nick']) && !empty(I('post.nick'))) $map['nick'] = I('post.nick');
        if (isset($_POST['goods_name']) && !empty(I('post.goods_name'))) $map['goods_name'] = I('post.goods_name');
        if (isset($_POST['status']) && !empty(I('post.status'))) {
            if (strpos(I('post.status'), ',') !== false) {
                $map['status'] = ['in', I('post.status')];
            } else {
                $map['status'] = I('post.status');
            }
        }
        if (!empty($_POST['sday']) || !empty($_POST['eday'])) {
            if (empty(I('post.sday'))) {
                $map['atime'] = ['lt', I('post.eday')];
            } elseif (empty(I('post.eday'))) {
                $map['atime'] = ['gt', I('post.sday')];
            } else {
                $map['atime'] = ['between', I('post.sday') . ',' . I('post.eday')];
            }
        }
        $pagesize=I('post.pagesize')?I('post.pagesize'):12;        

        $order=I('post.order')?I('post.order'):'id desc';
        if(I('post.sort')){
            $order=str_replace('-', ' ', I('post.sort'));
        }

        $pagelist=pagelist(array(
                'table'     		=>'Common/RefundRelation',
                'do'        		=>'D',
                'map'       		=>$map,
                'order'     		=>'atime desc',
                //'fields'    =>'',
                'order'     		=>$order,
                'pagesize'  		=>$pagesize,
                'relation'  		=>['seller','shop','orders_goods'],
                'action'            =>I('post.action'),
                'query'             =>I('post.query')?query_str_(I('post.query')):'',
                'p'                 =>I('post.p'),
				//'cache_name'        =>md5(implode(',',$_POST).__SELF__),
                //'cache_time'        =>C('CACHE_LEVEL.L'),

            ));


        if($pagelist['list']){
        	foreach($pagelist['list'] as $i => $val){
		        $pagelist['list'][$i]['orders_goods']	=imgsize_list($val['orders_goods'],'images',160);
		        $pagelist['list'][$i]['shop']			=imgsize_list($val['shop'],'shop_logo',100);
		        $pagelist['list'][$i]['type_name']		=$type_name[$val['type']];
		        $pagelist['list'][$i]['status_name']	=$status_name[$val['status']];        		
        	}

            $this->apiReturn(1,array('data' => $pagelist));
        }else{
            $this->apiReturn(3);
        }
    }	
}