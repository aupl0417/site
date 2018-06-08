<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 订单管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class OrdersController extends CommonController {
	protected $action_logs = array('pay','close','orders_shop_close','confirm_goods','goods_rate','shop_rate');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 订单详情
    * @param string $_POST['openid']    用户openid
    * @param strint $_POST['o_no']      合并订单号
    */
    public function view(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','o_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>$this->uid));
        $res=$orders->view();

        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
	
	/**
	* 某个商家订单详情
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
	*/
	public function orders_shop_view(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();	
		
        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $res=$orders->orders_shop_view(2);

        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}

    /**
    * 订单付款
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['o_no']      合并订单号
    * @param string $_POST['password_pay'] 安全密码
    */
    public function pay(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','o_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();
        
        //验证支付密码
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('password_pay')->find();
        if(md5(I('post.password_pay'))!=$urs['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }
        
        $orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>$this->uid)); 

        $res=$orders->pay();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);        
    }

    /**
    * 订单列表
    * @param string $_POST['openid']    用户openid
    */
    public function plist(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('uid'=>$this->uid));
        $res=$orders->b_list(array('pagesize'=>I('post.pagesize'),'action'=>I('post.action'),'query'=>I('post.query'),'p'=>I('post.p')));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']); 
    }

    /**
    * 订单列表
    * @param string $_POST['openid']    用户openid
    */
    public function orders_plist(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('uid'=>$this->uid));
        $res=$orders->b_orders_list(array('status'=>I('post.status'),'pagesize'=>I('post.pagesize'),'action'=>I('post.action'),'query'=>I('post.query'),'p'=>I('post.p')));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']); 
    }

    /**
    * 买家关闭订单
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['o_no']      合并订单号
    * @param string $_POST['reason']    关闭原因
    */
    public function close(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','o_no','reason','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>$this->uid));
        $res=$orders->b_close(I('post.reason'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
    * 关闭某个商家的订单
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
    * @param string $_POST['reason']    关闭原因
    */
    public function orders_shop_close(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','reason','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $res=$orders->b_orders_shop_close(I('post.reason'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
    * 删除订单
    * 未付款的订单方可删除
    */
    public function delete(){

    }

    /**
    * 买家确认收货
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
    * @param string $_POST['password_pay']    安全密码    
    */
    public function confirm_goods(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();

        //检查支付密码
        $this->check_password_pay(I('post.password_pay'));

        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $res=$orders->confirm_goods();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
    * 对已购买的商品进行评价
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['orders_goods_d']    商品ID
    * @param int    $_POST['rate']  评价,1好评，0中评，-1差评
    * @param string $_POST['content']   评价内容
    * @param int    $_POST['is_anonymous']  是否匿名，1为匿名
    */
    public function goods_rate(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','orders_goods_id','rate','content','sign','is_anonymous');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('uid'=>$this->uid));
        $res=$orders->b_goods_rate(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
    * 订单统计
    * @param string $_POST['openid']    用户openid
    */
    public function orders_count(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('uid'=>$this->uid));
        $res=$orders->b_count();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);        
    }
	
	/**
	* 获取订单处理日志
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
	*/
	public function orders_logs(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('uid'=>$this->uid,'s_no'=>I('post.s_no')));
        $res=$orders->orders_logs(2);
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);  		
	}

    /**
    * 获取可评价的商品
    * @param string $_POST['openid']    用户openid
    */
    public function wait_rate_goods(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        if(isset($_POST['s_no'])) $this->need_param[]='s_no';

        $this->_need_param();
        $this->_check_sign();

        $list = $this->_wait_rate_godos($this->uid,I('post.s_no'));
        if($list){
            $this->apiReturn(1,['data' => $list ]);
        }else{
            $this->apiReturn(3);
        }

    }

    public function _wait_rate_godos($uid,$s_no=''){
        //退款中的商品不充许评价
        $do=D('Common/OrdersGoodsOrdersShopView');

        if($s_no) $map['s_no'] =$s_no;
        $map['uid']                 =$uid;
        $map['is_rate']             =0;
        $map['orders_shop.status']  =4;
        $map['_string']             ='orders_goods.refund_price < orders_goods.total_price';

        $list=$do->where($map)->field('id,s_id,s_no,goods_id,attr_list_id,attr_name,price,num,goods_name,images')->select();

        return $list;        
    }

    /**
    * 根据订单号获取卖家信息
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
    */
    public function shop_from_sno(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $shop_id=M('orders_shop')->where(['openid' => $this->uid, 's_no' => I('post.s_no')])->getField('shop_id');

        if($shop_id){
            $do=D('Common/ShopRelation');
            $rs=$do->relation(true)->where(['id'=>$shop_id])->field('etime,ip',true)->find();

            if($rs) {
				$area	=	$this->cache_table('area');
                $rs['province']    =	$area[$rs['province']];
                $rs['city']        =	$area[$rs['city']];
                $rs['district']    =	$area[$rs['district']];
                $rs['town']        =	$area[$rs['town']];          

                $this->apiReturn(1,['data' => $rs]);
            }else $this->apiReturn(3);
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 根据订单号获取待评价卖家店铺
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
    */
    public function rate_shop_info(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        //检查是否已评价
        if(M('orders_shop_comment')->where(['s_no' => I('post.s_no')])->count()>0) $this->apiReturn(803); //该笔订单的店铺已评价

        if(!$shop_id=M('orders_shop')->where(['openid' => $this->uid, 's_no' => I('post.s_no')])->getField('shop_id')) $this->apiReturn(3);

        $do=D('Common/ShopRelation');
        $rs=$do->relation(true)->where(['id'=>$shop_id])->field('etime,ip',true)->find();
        $rs['s_no']=I('post.s_no');
        
        if($rs) {
			$area	=	$this->cache_table('area');
            $rs['province']    =	$area[$rs['province']];
            $rs['city']        =	$area[$rs['city']];
            $rs['district']    =	$area[$rs['district']];
            $rs['town']        =	$area[$rs['town']];           

            $this->apiReturn(1,['data' => $rs]);
        }else $this->apiReturn(3);
   
    }

    /**
    * 对卖家店铺评价
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
    * @param string $_POST['fraction_speed']    物流评分
    * @param string $_POST['fraction_service']  服务评分
    * @param string $_POST['fraction_desc']     描述评分
    * @param string $_POST['content']           评价内容
    */
    public function shop_rate(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','fraction_speed','fraction_service','fraction_desc','content','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $res=$orders->b_shop_rate(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);        

    }


    /**
     * 添加订单备注
     * @param string $_POST['openid'] 用户openid
     * @param string $_POST['s_no'] 订单号
     * @param string $_POST['remark'] 备注内容，255字以内
     * @param string $_POST['color'] 备注颜色，如：red,green,#EE22ff
     */
    public function remark_add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','buyer_remark','buyer_remark_color');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $res=$orders->b_remark_add(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

}