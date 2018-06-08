<?php
/**
* 买家订单管理
*/
namespace Common\Controller;
use Think\Controller;
use Common\Builder\Activity;
class OrdersController extends Controller {
	protected $o_id;	           //订单ID
    protected $o_no;            //主订单号
    protected $s_no;            //商家订单号
    protected $s_id;            //商家订单ID    
    protected $uid;             //买家ID
    protected $seller_id;       //卖家ID
    //protected $do_user;         //设置读取订单的用户，1=买家,2=卖家,3=系统
    protected $sw;              //事务结果，用于记录日志
    protected $flag_arr=array(    //来源用户子账户1=现金账户,2=积分账户,3=理财账户
                1=>'ac_cash',
                2=>'ac_score',
                3=>'ac_finance',
                4=>'ac_cash_lock'
            );

    protected $pay_typename = array(
                1 => '余额支付',
                2 => '唐宝支付',
                3 => '微信支付',
                5 => '支付宝支付',
                7 => '银联支付',
            );

    private $action_logs      =array('b_close','b_orders_shop_close','b_delete','pay','confirm_goods','b_goods_rate','b_goods_rate_edit','b_shop_rate');   //须要记录日志的方法
    /**
     * 架构函数
     * @access public
     * @param string $o_no  订单号
     * @param int   $uid    买家ID
     */
    public function __construct($param=array()) {

        //站点配置
        $this->cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
        C('cfg',$cfg);
        C('TOKEN_ON',false);

        $this->o_no         =$param['o_no'];
        $this->s_no         =$param['s_no'];
        $this->uid          =$param['uid'];
        $this->seller_id    =$param['seller_id'];

        C('API_LOG',true);
    }


	/**
	* 设置属性
	*/
	public function __set($name,$v){
		return $this->$name=$v;
	}

	/**
	* 获取属性
	*/
	public function __get($name){
		return isset($this->$name)?$this->$name:null;
	}
	
	/**
	* 销毁属性
	*/
    public function __unset($name) {
        unset($this->$name);
    }


    /**
    * 检查订单
    */
    public function check_orders(){
        if(empty($this->o_no)) {
            //缺少订单号
            return $this->apiReturn(201);
        }

        $do=D('Common/OrdersViewRelation');
        $map['o_no']=$this->o_no;
        if(!$rs=$do->relation(true)->where($map)->field('etime,ip',true)->find()) {
            //订单不存在
            return $this->apiReturn(3);
        }

        //检查订单所有者
        if($this->uid){
            if($this->uid!=$rs['uid']){
                return $this->apiReturn(196);
            }
        }

        $this->o_id=$rs['id'];
        return $this->apiReturn(1,array('data'=>$rs));        
    }

    /**
    * 检查商家订单
    * @param int $check 必须检查类型，1为卖家，2为买家
    */
    public function check_s_orders($check=1){
        if(empty($this->s_no)) {
            //缺少订单号
            return $this->apiReturn(201);
        }   

        $do=M('orders_shop');
        $map['s_no']=$this->s_no;
        if(!$rs=$do->where($map)->field('etime,ip',true)->find()) {
            //订单不存在
            return $this->apiReturn(3);
        }

        //检查订单所有者
        if($this->seller_id || $check==1){
            if($this->seller_id!=$rs['seller_id']){
                return $this->apiReturn(196);
            }
        }

        if($this->uid || $check==2){
            if($this->uid!=$rs['uid']){
                return $this->apiReturn(196);
            }
        }

        $this->s_id=$rs['id'];
        return $this->apiReturn(1,array('data'=>$rs));                
    }

    /**
    * 订单统计
    */
    public function b_count(){
        if(empty($this->uid)) {
            //缺少买家ID
            return $this->apiReturn(199);
        }

        //未付款订单
        $result['data'][1]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>1))->count();

        //已付款订单
        $result['data'][2]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>2))->count();

        //已发货订单
        $result['data'][3]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>3))->count();

        //已收货订单
        $result['data'][4]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>4))->count();

        //已评价订单
        $result['data'][5]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>5))->count();

        //已归档订单
        $result['data'][6]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>6))->count();

        //已关闭订单
        $result['data'][10]=M('orders_shop')->where(array('uid'=>$this->uid,'status'=>array('in','10,11')))->count();

        //退款中订单
        //$result['data'][20]=M('refund')->where(array('uid'=>$this->uid,'status'=>['not in','20,100']))->distinct(true)->count();
        $result['data'][20]=M('refund')->where(array('uid'=>$this->uid,'status'=>['not in','20,100'], 'orders_status' => ['lt', 4]))->count();
        //售后中的订单
        $result['data'][21]=M('refund')->where(array('uid'=>$this->uid,'status'=>['not in','20,100'], 'orders_status' => ['gt', 3]))->count();
        //代购统计
        $result['data'][22]=M('daigou')->where(['uid' => $this->uid, 'status' => ['gt', 0]])->count();
        
        //优惠券统计
        $result['data']['23']=M('coupon')->where(['uid' => $this->uid, 'status' => 1])->count();
        
        //售后中订单
        $result['data'][30]=M('orders_shop')->where(array('uid'=>$this->uid,'support_num'=>array('gt',0),'status'=>array('in','4,5')))->count();

        $result['data']['all']=M('orders_shop')->where(array('uid'=>$this->uid))->count();

        $result['code']=1;
        return $result;
    }

    /**
    * 获取订单详情
    */
    public function view(){
        $res=$this->check_orders();

        if($res['code']!=1){
            return $res;
        }

        $pay_typename = $this->pay_typename;

        $rs=$res['data'];
        $area   =   $this->cache_table('area');
        $rs['province']    =$area[$rs['province']];
        $rs['city']        =$area[$rs['city']];
        $rs['district']    =$area[$rs['district']];
        $rs['town']        =$area[$rs['town']];

        $rs['pay_typename']=$pay_typename[$rs['pay_type']];

        //$rs['status_name']  = C('orders_code')[7][$rs['status']];
        $tang_pay   =   null;
        foreach($rs['orders_shop'] as $key=>$val){
            //dump($val);
            $rs['orders_shop'][$key]['seller']=D('Common/ShopUserRelation')->relation(true)->where(['id'=>$val['shop_id']])->field('id,uid,shop_name,shop_logo,mobile,qq,wang')->find();
                //唐宝折扣活动
                $activitys  =   Activity::tangPaysActivity($val, 1);
                if ($activitys) {
                    $rs['orders_shop'][$key]['tangpay']   = number_formats((($val['pay_price']) - ($val['express_price_edit'] + $val['daigou_cost'])) * number_formats(($activitys['full_value'] * 0.1), 3), 3) + $val['express_price_edit'] + $val['daigou_cost'];
                    $tang_pay   +=  number_formats($rs['orders_shop'][$key]['tangpay'], 2);
                } else {
                    $tang_pay   +=  number_formats($val['pay_price'], 2);
                }
            $rs['orders_shop'][$key]['orders_goods']=M('orders_goods')->where(array('s_id'=>$val['id']))->field('etime,ip',ture)->select();
        }

        //判断订单中是否有使用官方优惠券，如果有的话就不支持唐宝支付
        $rs['is_tangbao_pay']   = 1;
        $coupon = M('orders_shop')->where(['o_no' => $this->o_no])->getField('coupon_id',true);
        if($coupon){
            $ids = implode(',',$coupon);
            $count = M('coupon')->where(['type' => 2,'id' => ['in',$ids]])->count();
            if($count > 0) $rs['is_tangbao_pay'] = 0;
        }
        
        //订单是否有退款中商品
        $rs['refund']       =M('refund')->where(['s_id' => $rs['id'], 'status' => ['notin', '20, 100']])->count();
        $rs['tangpay']      =round($tang_pay, 2);
        return $this->apiReturn(1,array('data'=>$rs));
    }
	
	/**
	* 商家订单详情
	* @param int $check 检查订单所有者，0不检验，1检查卖家，2为检查买家
	*/
	public function orders_shop_view($check=0){
		$res=$this->check_s_orders($check);		
        if($res['code']!=1){
            return $res;
        }
        $pay_typename = $this->pay_typename;
		$rs=$res['data'];
        
		
		$do=D('Common/OrdersShopBuyerRelation');
		$rs=$do->relation(true)->where(array('s_no'=>$this->s_no))->field('etime,ip',true)->find();
		$rs['express']=D('Common/ExpressViewRelation')->relation(true)->where(array('id'=>$rs['express_id']))->field('express_company_id')->find();
        $rs['status_name']  = C('orders_code')[7][$rs['status']];
        $rs['pay_typename']=$pay_typename[$rs['pay_type']];

        $area   =   $this->cache_table('area');
        $rs['orders']['province']   =$area[$rs['orders']['province']];
        $rs['orders']['city']       =$area[$rs['orders']['city']];
        $rs['orders']['district']   =$area[$rs['orders']['district']];
        $rs['orders']['town']       =$area[$rs['orders']['town']];
		//获取参与的活动
		$order_activity = Activity::getActivityByOrder($rs['s_no']);
		foreach($order_activity as $k => $v){
			//将唐宝支付的消费升级促销去掉
			if($rs['pay_type'] == 2 && $v['type_id'] == 7 ){
				unset($order_activity[$k]);
			}
		}
		$rs['activity']  =  $order_activity;
		
		//是否使用了官方优惠券
        $rs['is_official_coupon'] = 0;
        if($rs['coupon_id']){
            $official_coupon_count      = M('coupon')->where(['id' => ['in',$rs['coupon_id']],'type' => 2])->count();
            $rs['is_official_coupon']   = $official_coupon_count > 0 ? 1 : 0;
        }
		
        $rs['orders_goods']         =imgsize_list($rs['orders_goods'],'images',160);
        
        foreach ($rs['orders_goods'] as &$v) {
            //退款中的数量
            if (in_array($rs['status'], [2,3])) {
                //退款中
                $v['refunding']=M('refund')->where(['status' => ['notin', '100,20'], 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '2,3']])->count();
                //退款已完成
                $v['refundover']=M('refund')->where(['status' => 100, 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '2,3']])->count();
            } else if (in_array($rs['status'], [4,5])) {    //售后中的数量
                //售后中
                $v['serviceing']=M('refund')->where(['status' => ['notin', '100,20'], 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '4,5']])->count();
                //售后已完成
                $v['serviceover']=M('refund')->where(['status' => 100, 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '4,5']])->count();
            }
        }
        unset($v);
        $rs['tangpay']  =   round($rs['pay_price'] * 100);
        $activitys  =   Activity::getActivityByShopOrders($rs, 4);
        if ($activitys) {
            $rs['tangpay']  =   round(((($rs['pay_price'] - $rs['express_price_edit']) * ($activitys['full_value'] * 0.1)) + ($rs['express_price_edit']))  * 100);
        }
        //订单是否有退款中商品
        $rs['refund']               =M('refund')->where(['s_id' => $rs['id'], 'status' => ['notin', '20, 100']])->count();

        //是否使用了官方优惠券
        $rs['is_official_coupon']   = 0;
        $rs['is_tangbao_pay']       = 1;
        if($rs['coupon_id']){
            $official_coupon_count      = M('coupon')->where(['id' => ['in',$rs['coupon_id']],'type' => 2])->count();
            $rs['is_official_coupon']   = $official_coupon_count > 0 ? 1 : 0;
            $rs['is_tangbao_pay']       = $official_coupon_count > 0 ? 0 : 1;
        }

		return $this->apiReturn(1,array('data'=>$rs));
	}
	
	/**
	* 买家获取订单列表
    * @param int $param['pagesize'] 分页记录数
    * @param string $param['action'] 分页链接前缀
    * @param string $param['query'] 查询参数
    * @param int $param['p']    第n页
	*/
	public function b_list($param=array()){
		if(empty($this->uid)) {
            //缺少买家ID
            return $this->apiReturn(199);
        }

        $pay_typename = $this->pay_typename;

        $map['uid']=$this->uid;
        if($param['status']) $map['status']=$param['status'];

        $pagesize=$param['pagesize']?$param['pagesize']:20;
        $pagelist=pagelist(array(
                'table'     =>'Common/OrdersBuyerRelation',
                'do'        =>'D',
                'pagesize'  =>$pagesize,
                'map'       =>$map,
                'order'     =>'atime desc',
                'relation'  =>true,
                'action'    =>$param['action'],
                'query'     =>$param['query'],
                'p'         =>$param['p'],
            ));

        $pagelist['count']=$this->b_count()['data'];

        if($pagelist['allnum']>0){
            $area   =   $this->cache_table('area');
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['status_name']=C('orders_code')[7][$val['status']];
                $pagelist['list'][$key]['province']    =$area[$val['province']];
                $pagelist['list'][$key]['city']        =$area[$val['city']];
                $pagelist['list'][$key]['district']    =$area[$val['district']];
                $pagelist['list'][$key]['town']        =$area[$val['town']];
                $pagelist['list'][$key]['pay_typename']          =$pay_typename[$val['pay_type']];

                foreach($pagelist['list'][$key]['orders_shop'] as $skey=>$v){
                    $pagelist['list'][$key]['orders_shop'][$skey]['seller']=D('Common/ShopUserRelation')->relation(true)->field('uid,shop_name,shop_logo,mobile,qq,wang')->find();
                    $pagelist['list'][$key]['orders_shop'][$skey]['orders_goods']=M('orders_goods')->where(array('s_id'=>$v['id']))->field('etime,ip',ture)->select();
                }           
            }            

            return $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            //没有记录
            return $this->apiReturn(3);
        }
     
	}


    /**
    * 买家获取不同状态下的订单列表
    * @param int $param['pagesize'] 分页记录数
    * @param string $param['action'] 分页链接前缀
    * @param string $param['query'] 查询参数
    * @param int $param['p']    第n页
    */
    public function b_orders_list($param=array()){
        if(empty($this->uid)) {
            //缺少买家ID
            return $this->apiReturn(199);
        }

        $pay_typename = $this->pay_typename;
        $map['uid']=$this->uid;

        switch($param['status']){
            //case 1: //待付款
            //case '':
                //return $this->b_list($param);
            //break;
            case 10: //已付款
                $map['status']=array('in','10,11');
            break;
            case 20: //退款中
                $map['status']      =array('in','2,3');
                $map['refund_num']  =array('gt',0);
            break;
            case 30: //售后中
                $map['status']      =array('in','4,5');
                $map['support_num']  =array('gt',0);
            break;
            default:
                if($param['status']!='') $map['status']=$param['status'];
            break;
        }
		
		//var_dump($map);
        if(I('post.s_no')) $map['s_no'] =I('post.s_no');
        if(I('post.sday')!='' && I('post.eday')!='') $map['atime']  = ['between',[I('post.sday'),I('post.eday')]];
        if(I('post.sday')!='' && I('post.eday')=='') $map['atime']  = ['egt',I('post.sday')];
        if(I('post.sday')=='' && I('post.eday')!='') $map['atime']  = ['elt',I('post.eday')];

        if(I('post.goods_name')) $map['_string'] = 'id in (select s_id from '.C('DB_PREFIX').'orders_goods where goods_name like "%'.I('post.goods_name').'%" and uid='.$this->uid.')';

        if(I('post.nick')) {
            $uid=M('user')->cache(true)->where(['nick' => I('post.nick')])->getField('id');
            if($uid) $map['seller_id']    =$uid;
            else return $this->apiReturn(3);
        }        
        
        $pagesize=$param['pagesize']?$param['pagesize']:10;
        $pagelist=pagelist(array(
                'table'     =>'Common/OrdersShopBuyerRelation',
                'do'        =>'D',
                'pagesize'  =>$pagesize,
                'map'       =>$map,
                'order'     =>'atime desc',
                'relation'  =>true,
                'action'    =>$param['action'],
                'query'     =>$param['query'],
                'p'         =>$param['p'],
            ));
			
		//var_dump($pagelist);

        $pagelist['count']=$this->b_count()['data'];
        //数据格式化
        if($pagelist['allnum']>0){
            $area   =   $this->cache_table('area');
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['status_name']=C('orders_code')[7][$val['status']];
                $pagelist['list'][$key]['orders']['province']    =$area[$val['orders']['province']];
                $pagelist['list'][$key]['orders']['city']        =$area[$val['orders']['city']];
                $pagelist['list'][$key]['orders']['district']    =$area[$val['orders']['district']];
                $pagelist['list'][$key]['orders']['town']        =$area[$val['orders']['town']];
                $pagelist['list'][$key]['pay_typename']          =$pay_typename[$val['pay_type']];
                
				//已发货订单才获取快递信息
				if(in_array($val['status'],array(3,4,5,6))){
					$pagelist['list'][$key]['express']=D('Common/ExpressViewRelation')->relation(true)->where(array('id'=>$val['express_id']))->field('express_company_id')->find();
				}
				//$rTotal[$key] =   M('refund')->field('SUM(num) as num')->where(['_string' => 'status != 100', 's_no' => $val['s_no']])->find();
                $pagelist['list'][$key]['is_service'] = 0;  //可售后的几款商品
                $pagelist['list'][$key]['strtotime'] = strtotime($val['receipt_time']);
                foreach($val['orders_goods'] as $i=>$v){
                    $pagelist['list'][$key]['attrIds']  .=  $v['attr_list_id'] . ',';
                    $pagelist['list'][$key]['orders_goods'][$i]['images']=myurl($v['images'],100);
                    $pagelist['list'][$key]['appeal']+=$v['num'] - ($v['refund_num'] + $v['service_num']);  //可售后数量
                    //退款中的数量
                    if (in_array($val['status'], [2,3])) {
                        //退款中
                        $pagelist['list'][$key]['refunding']=M('refund')->where(['status' => ['notin', '100,20'], 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '2,3']])->count();
                        //退款已完成
                        $pagelist['list'][$key]['refundover']=M('refund')->where(['status' => 100, 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '2,3']])->count();
                    } else if (in_array($val['status'], [4,5])) {    //售后中的数量
                        //售后中
                        $pagelist['list'][$key]['serviceing']=M('refund')->where(['status' => ['notin', '100,20'], 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '4,5']])->count();
                        //售后已完成
                        $pagelist['list'][$key]['serviceover']=M('refund')->where(['status' => 100, 'orders_goods_id' => $v['id'], 'orders_status' => ['in', '4,5']])->count();
                        //可售后商品的数量，如果大于0则可以售后
                        if (($pagelist['list'][$key]['strtotime'] + (($v['goods_service_days'] > 0 ? $v['goods_service_days'] : getGoodsServiceDays($v['goods_id'])) * 24 * 3600)) >= NOW_TIME) {
                            $pagelist['list'][$key]['is_service']++;
                        }
                    }
                }

                
                //获取参与的活动
				$order_activity = Activity::getActivityByOrder($val['s_no']);
				foreach($order_activity as $k => $v){
					//将唐宝支付的消费升级促销去掉
					if($val['pay_type'] == 2 && $v['type_id'] == 7 ){
						unset($order_activity[$k]);
					}
				}
                $pagelist['list'][$key]['activity'] =  $order_activity;
                


                //订单是否有退款中商品
                $pagelist['list'][$key]['refund']               =M('refund')->where(['s_id' => $val['id'],'status' =>['not in','20,100']])->count();                

            }
            
            return $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            //没有记录
            return $this->apiReturn(3,array('data'=>$pagelist));
        }
     
    }

    /**
    * 买家关闭订单
    * @param string $reason 关闭原因
    */
    public function b_close($reason){
        $res=$this->check_orders();
        if($res['code']!=1){
            return $res;
        }

        $rs=$res['data'];
        if($rs['status']!=1){
            //该订单状态下不充许关闭订单！
            return $this->apiReturn(197);
        }

        //订单正在支付流程中时不可以关闭
        $tmp = S('paying_'.$rs['s_no']);
        if($tmp) return $this->apiReturn(4,'','订单正在支付流程中，不允许关闭，请稍候后再试！');

        $do=M();
        $do->startTrans();
        //订单日志
        foreach($rs['orders_shop'] as $val){
            $logs_data=array(
                    'o_id'      =>$val['o_id'],
                    'o_no'      =>$val['o_no'],
                    's_id'      =>$val['id'],
                    's_no'      =>$val['s_no'],
                    'status'    =>10,
                    'remark'    =>'买家关闭订单',
                    'reason'    =>$reason
                );
            if($logs_sw=D('Common/OrdersLogs')->create($logs_data)){
                if(!$this->sw[]=D('Common/OrdersLogs')->add()){
                    //添加订单日志失败
                    $result['code']=202;
                    goto error;
                }
            }else{
                $this->sw[]=$logs_sw;
                $result['code']=4;
                $msg=D('Common/OrdersLogs')->getError();
                goto error;
            }
        }

        //更新商家订单
        if(!$this->sw[]=M('orders_shop')->where(array('o_id'=>$rs['id']))->save(array('status'=>10))){
            //更新订单状态失败！
            $result['code']=200;
            goto error;
        }

        //更新主订单        
        if(!$this->sw[]=M('orders')->where(array('id'=>$rs['id']))->save(array('status'=>10))){
            //更新订单状态失败！
            $result['code']=200;
            goto error;
        }

        $do->commit();
        return $this->apiReturn(1,array('data'=>array('o_no'=>$rs['o_no'])));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);
    }

    /**
    * 关闭某商家的订单
    * @param string $reason 关闭原因
    * @param int    $check_type 订单检验类型，1为卖家，2为买家，0不检验
    * @param int    $is_sys        1为系统操作
    */
    public function b_orders_shop_close($reason,$check_type=2,$is_sys=0){
        $res=$this->check_s_orders($check_type);
        if($res['code']!=1){
            return $res;
        }

        $rs=$res['data'];
        if($rs['status']!=1){
            //该订单状态下不充许关闭订单！
            return $this->apiReturn(197);
        }

        //主订单
        $ors=M('orders')->where(array('id'=>$rs['o_id']))->field('shop_num,refund_num,pay_price')->find();
        //Activity::setStatus($rs['s_no'], $rs['uid'], 2);
        $do=M();
        $do->startTrans();

        //关闭参与活动
        Activity::setStatus($rs['s_no'], $rs['uid'], 2);

        //订单日志
        $logs_data=array(
                'o_id'      =>$rs['o_id'],
                'o_no'      =>$rs['o_no'],
                's_id'      =>$rs['id'],
                's_no'      =>$rs['s_no'],
                'status'    =>10,
                'remark'    =>'买家关闭订单',
                'reason'    =>$reason,
                'is_sys'    =>$is_sys?$is_sys:0
            );
        if($logs_sw=D('Common/OrdersLogs')->create($logs_data)){
            if(!$this->sw[]=D('Common/OrdersLogs')->add()){
                //添加订单日志失败
                $result['code']=202;
                goto error;
            }
        }else{
            $this->sw[]=$logs_sw;
            $result['code']=4;
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }   

        //更新商家订单
        if(!$this->sw[]=M('orders_shop')->where(array('id'=>$rs['id']))->save(array('status'=>10))){
            //更新订单状态失败！
            $result['code']=200;
            goto error;
        }

        $ors['shop_num']--;
        $ors['goods_num']-=$rs['goods_num'];
        $ors['pay_price']-=$rs['pay_price'];

        if($ors['shop_num']<1){
            if(!$this->sw[]=M('orders')->where(array('id'=>$rs['o_id']))->save(array('status'=>10))){
                //更新订单状态失败！
                $result['code']=200;
                goto error;                
            }
        }else{
            if(!$this->sw[]=M('orders')->where(array('id'=>$rs['o_id']))->save($ors)){
                //更新订单状态失败！
                $result['code']=200;
                goto error;
            }
        }
        
        $do->commit();
        return $this->apiReturn(1,array('data'=>array('o_no'=>$rs['o_no'],'s_no'=>$rs['s_no'])));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);                         
    }

    /**
    * 买家删除订单
    */
    public function b_delete(){
        $res=$this->check_orders();
        if($res['code']!=1){
            return $res;
        }

        $rs=$res['data'];
        if(!in_array($rs['status'],array(1,10))){
            //只有未付款订单或已关闭的订单方可删除！
            return $this->apiReturn(204);
        }
    }

    /**
    * 订单付款
    */
    public function pay(){
        if(empty($this->uid)) {
            //缺少参数买家ID
            return $this->apiReturn(199);
        }

        $res=$this->check_orders();
        if($res['code']!=1){
            return $res;
        }
        $rs=$res['data'];

        if($rs['status']!=1){
            //该订单状态下不充许付款！
            return $this->apiReturn(198);
        }

        //异动金额
        $money=$rs['pay_price'];

        //检查买家账户余额
        $from_account=$this->check_account($this->uid,2,$money);
        if($from_account['code']!=1){
            return $from_account;
        }
        $to_account=$this->check_account(1);
        if($to_account['code']!=1){
            return $to_account;
        }

        //买家转入admin
        $from_account['data']['ac_score']        -=$money;
        $to_account['data']['ac_score']          +=$money;

        $from_account['data']['crc']    =$this->crc($from_account['data']);
        $to_account['data']['crc']      =$this->crc($to_account['data']);

        //买家资金转出异动
        $data=array();
        $data['uid']            =$this->uid;
        $data['money']          =$money * -1;
        $data['c_no']           =$this->create_orderno();
        $data['status']         =2;     //状态
        $data['from_uid']       =$this->uid;     //1为系统账户
        $data['from_flag']      =2;     //现金账户
        $data['from_account']   =$from_account['data']['ac_cash'];

        $data['to_uid']         =1; 
        $data['to_flag']        =2;
        $data['to_account']     =$to_account['data']['ac_cash'];

        $data['type_id']        =13;     //购买商品
        $data['ordersno']       =$rs['o_no'];



        $do=M();
        $do->startTrans();
        if($sw1=D('Common/ChangeScore')->token(false)->create($data)){
            if(!$this->sw[]=D('Common/ChangeScore')->add()){
                //更新账户失败
                $result['code']=119;
                goto error;
            }
        }else{
            $this->sw[]=$sw1;
            $result['code']=4;
            $msg=D('Common/ChangeScore')->getError();
            goto error;
        }

        //接收方异动 
        $to_data=$data;
        $to_data['uid']            =1;
        $to_data['money']          =$money;
        $to_data['c_no']           =$this->create_orderno();

        if($sw2=D('Common/ChangeScore')->create($to_data)){
            if(!$this->sw[]=D('Common/ChangeScore')->add()){
                //更新账户失败
                $result['code']=119;
                goto error;                
            }
        }else{
            $this->sw[]=$sw2;
            $result['code']=4;
            $msg=D('Common/ChangeScore')->getError();
            goto error;
        }
        
        //更新账户
        if(!$sw3=M('account')->where('uid='.$data['from_uid'])->save($from_account['data'])) {
            //更新账户失败！
            $result['code']=119;
            goto error;        
        }
        if(!$sw4=M('account')->where('uid='.$data['to_uid'])->save($to_account['data'])) {
            //更新账户失败！
            $result['code']=119;
            goto error;
        }



        //更新订单
        if(!$sw5=M('orders')->where(array('id'=>$this->o_id))->save(array('is_pay'=>1,'pay_time'=>date('Y-m-d H:i:s'),'status'=>2))){
            //更新订单状态失败！
            $result['code']=200;
            goto error; 
        }

        if(!$sw6=M('orders_shop')->where(array('o_id'=>$this->o_id,'status'=>1))->save(array('is_pay'=>1,'pay_time'=>date('Y-m-d H:i:s'),'status'=>2))){
            //更新订单状态失败！
            $result['code']=200;
            goto error;             
        }

        //订单日志
        foreach($rs['orders_shop'] as $val){
            $logs_data=array(
                    'o_id'      =>$val['o_id'],
                    'o_no'      =>$val['o_no'],
                    's_id'      =>$val['id'],
                    's_no'      =>$val['s_no'],
                    'status'    =>2,
                    'remark'    =>'买家已付款'
                );

            if($logs_sw=D('Common/OrdersLogs')->create($logs_data)){
                if(!$this->sw[]=D('Common/OrdersLogs')->add()){
                    //添加订单日志失败！
                    $result['code']=202;
                    goto error;
                }
            }else{
                $this->sw[]=$logs_sw;
                $result['code']=4;
                $msg=D('Common/OrdersLogs')->getError();
                goto error;
            }
            
        }


        //付款成功，事务提交
        $do->commit();
        return $this->apiReturn(1,array('data'=>array('o_id'=>$rs['id'],'o_no'=>$rs['o_no'])));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);

    }

    /**
    * 检查订单中商品库存是否足够或是否变更过属性
    */
    public function check_goods_attr($s_no=''){
        $s_no = $s_no ? $s_no : $this->s_no;
        $list=M('orders_goods')->where(['s_no' => $s_no])->field('id,s_no,uid,goods_id,attr_list_id,attr_name,price,num,goods_name,images,score_ratio,officialactivity_id,officialactivity_join_id')->select();
        $ids=arr_id(['plist' => $list,'field' => 'attr_list_id']);

        $do=D('Common/GoodsAttrListUpRelation');
        $tmp=$do->relation(true)->where(['id' => ['in',$ids]])->field('id,goods_id,price,num')->select();
        foreach($tmp as $val){
            $goods[$val['id']]=$val;
        }

        $status_name = [
            'price'             =>'价格已变更，请重新下单付款！',
            'activity_start'    =>'活动还款开始！',
            'activity_over'     =>'活动已结束！',
            'activity_max_num'  =>'订购的商品超过活动限够数量！',
            'delete'            =>'商品库存属性已删除！'
        ];
        $status = array();

        $result=array();
        foreach($list as $i => $val){
            if(isset($goods[$val['attr_list_id']])){
                $tmp = $goods[$val['attr_list_id']];
                if($tmp['goods']['status'] !=1) $result['offline'][]     =   $val;   //已下架
                elseif($tmp['num'] < $val['num']) $result['inventory'][]  =   $val;   //库存不足
                elseif($tmp['goods']['score_ratio'] != $val['score_ratio']) $result['score_ratio'][]  =   $val;   //积分赠送比例已变更
                elseif($tmp['price'] != $val['price'] && $val['officialactivity_join_id']==0) { //价格已变更
                    $result['price'][] = $val;
                    if(!in_array('price',$status)) $msg[] = $status_name['price'];
                }

                //是否参与官方活动
                if($val['officialactivity_join_id'] > 0){
                    $officialactivity = D('Common/OfficialactivityJoinUpRelation')->relation(true)->where(['id' => $val['officialactivity_join_id']])->field('activity_id,day,time')->find();
                    $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();

                    if($time_dif > 0) {//活动还款开始
                        $result['activity_start'][] = $val;
                        if(!in_array('activity_start',$status)) $msg[] = $status_name['activity_start'];
                    }

                    if($time_dif < -86400) {//活动已结束
                        $result['activity_over'][]  = $val;
                        if(!in_array('activity_over',$status)) $msg[] = $status_name['activity_over'];
                    }

                    //是否超过限购数量
                    $val['max_buy'] = M('orders_goods')->where(['uid' => $val['uid'],'officialactivity_join_id' => $val['officialactivity_join_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in (2,3,4,5,6,11))'])->sum('num');
                    if($officialactivity['officialactivity']['max_buy'] < ($val['num']+$val['max_buy'])) {//超过限购数量
                        $result['activity_max_num'][]   = $val;
                        if(!in_array('activity_max_num',$status)) $msg[] = $status_name['activity_max_num'];
                    }
                }
            }else{
                //属性已被删除
                $result['delete'][]=$val;
                if(!in_array('delete',$status)) $msg[] = $status_name['delete'];
            }
        }

        if(empty($result)) return $this->apiReturn(1,['data' => $list]);

        //订单中部分商品已下架、库存不足或属性已变更，请重新下单！
        else return $this->apiReturn(0,['data' => $result],implode(',',$msg));
    }

    /**
    * 买家确认收货
    */
    public function confirm_goods(){
        if(empty($this->uid)) {
            //缺少参数买家ID
            return $this->apiReturn(199);
        }

        $res=$this->check_s_orders(2);
        if($res['code']!=1){
            return $res;
        }
        $rs=$res['data'];

        if($rs['status']!=3){
            //只有已发货状态下方可确认收货！
            return $this->apiReturn(207);
        }

        //异动金额
        $money=$rs['pay_price'];

        //检查买家账户余额
        $from_account=$this->check_account(1);
        if($from_account['code']!=1){
            return $from_account;
        }
        $to_account=$this->check_account($rs['seller_id']);
        if($to_account['code']!=1){
            return $to_account;
        }
        //admin转到卖家
        $from_account['data']['ac_cash']        -=$money;
        $to_account['data']['ac_cash']          +=$money;

        $from_account['data']['crc']    =$this->crc($from_account['data']);
        $to_account['data']['crc']      =$this->crc($to_account['data']);

        //买家资金转出异动
        $data=array();
        $data['uid']            =1;
        $data['money']          =$money * -1;
        $data['a_no']           =$this->create_orderno();
        $data['status']         =2;     //状态
        $data['from_uid']       =1;     //1为系统账户
        $data['from_flag']      =1;     //现金账户
        $data['from_account']   =$from_account['data']['ac_cash'];

        $data['to_uid']         =$rs['seller_id']; 
        $data['to_flag']        =1;
        $data['to_account']     =$to_account['data']['ac_cash'];

        $data['type_id']        =14;     //买家确认收货
        $data['ordersno']       =$rs['s_no'];

        $do=M();
        $do->startTrans();
        if($sw1=D('Common/ChangeCash')->create($data)){
            if(!$this->sw[]=D('Common/ChangeCash')->add()){
                //更新账户失败
                $result['code']=119;
                goto error;
            }
        }else{
            $this->sw[]=$sw1;
            $result['code']=4;
            $msg=D('Common/ChangeCash')->getError();
            goto error;
        }

        //接收方异动 
        $to_data=$data;
        $to_data['uid']            =$rs['seller_id'];
        $to_data['money']          =$money;
        $to_data['a_no']           =$this->create_orderno();

        if($sw2=D('Common/ChangeCash')->create($to_data)){
            if(!$this->sw[]=D('Common/ChangeCash')->add()){
                //更新账户失败
                $result['code']=119;
                goto error;                
            }
        }else{
            $this->sw[]=$sw2;
            $result['code']=4;
            $msg=D('Common/ChangeCash')->getError();
            goto error;
        }
        
        //更新账户
        if(!$sw3=M('account')->where('uid='.$data['from_uid'])->save($from_account['data'])) {
            //更新账户失败！
            $result['code']=119;
            goto error;        
        }
        if(!$sw4=M('account')->where('uid='.$data['to_uid'])->save($to_account['data'])) {
            //更新账户失败！
            $result['code']=119;
            goto error;
        }

        //更新订单
        if(!$sw5=M('orders_shop')->where(array('id'=>$rs['id']))->save(array('status'=>4,'receipt_time'=>date('Y-m-d H:i:s')))){
            //更新订单状态失败！
            $result['code']=200;
            goto error; 
        }

        //订单日志
        $logs_data=array(
                'o_id'      =>$rs['o_id'],
                'o_no'      =>$rs['o_no'],
                's_id'      =>$rs['id'],
                's_no'      =>$rs['s_no'],
                'status'    =>4,
                'remark'    =>'买家确认收货'
            );
        
        if($logs_sw=D('Common/OrdersLogs')->create($logs_data)){
            if(!$this->sw[]=D('Common/OrdersLogs')->add()){
                //添加订单日志失败！
                $result['code']=202;
                goto error;
            }
        }else{
            $this->sw[]=$logs_sw;
            $result['code']=4;
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }

        //付款成功，事务提交
        $do->commit();
        return $this->apiReturn(1,array('data'=>array('o_no'=>$rs['o_no'],'s_no'=>$rs['s_no'])));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);

    }

    /**
    * 买家评价
    */
    public function b_goods_rate($param){
        if(empty($this->uid)) {
            //缺少参数买家ID
            return $this->apiReturn(199);
        }

        //$do=M('orders_goods');
        $do=D('Common/OrdersGoodsOrdersShopRelation');
        $rs=$do->relation(true)->relationField('orders_shop','id,status,goods_num,rate_num,is_shuadan')->where(array('uid'=>$this->uid,'id'=>$param['orders_goods_id']))->field('id,o_id,o_no,s_id,s_no,seller_id,uid,shop_id,goods_id,attr_list_id,is_rate')->find();

        //var_dump($rs);

        if(!$rs) return $this->apiReturn(3); //找不到记录
        if($rs['orders_shop']['status']!=4) return $this->apiReturn(208); //只有已收货状态下方可评价！
        if($rs['is_rate']==1) return $this->apiReturn(210); //该宝贝已经评价，请不要重复评价

        //兼容新的评分方式
        switch($param['rate']){
            case 0:
                $fraction_desc = 3;
                break;
            case -1:
                $fraction_desc = 2;
                break;
            default:
                $fraction_desc = 5;
        }

        $point = ($param['rate'] == 1 && $rs['orders_shop']['is_shuadan'] > 0) ? 0 : $param['rate'];    //得分，用于计算店铺等级
        $data=array(
                's_id'          =>$rs['s_id'],
                's_no'          =>$rs['s_no'],
                'status'        =>1,
                'shop_id'       =>$rs['shop_id'],
                'uid'           =>$rs['uid'],
                'seller_id'     =>$rs['seller_id'],
                'orders_goods_id'=>$rs['id'],
                'goods_id'      =>$rs['goods_id'],
                'attr_list_id'  =>$rs['attr_list_id'],
                'rate'          =>$param['rate'],
                'is_anonymous'  =>$param['is_anonymous']?$param['is_anonymous']:0,
                'content'       =>$param['content'],
                'images'        =>$param['images'],
                'is_sys'        =>$param['is_sys']?$param['is_sys']:0,
                'fraction_desc' =>$fraction_desc,
                'point'         => $point,
                'is_shuadan'    => $rs['orders_shop']['is_shuadan'],
            );

        $do=M();
        $do->startTrans();
        //添加评价
        if($sw=D('Common/OrdersGoodsComment')->create($data)){
            if(!$this->sw[]=D('Common/OrdersGoodsComment')->add()){
                //添加评价记录失败！
                $result['code']=211;
                goto error;
            }
        }else{
            $this->sw[]=$sw;
            $result['code']=4;
            $msg=D('Common/OrdersGoodsComment')->getError();
            goto error;
        }

        //更新订单商品评价状态
        if(!$this->sw[]=M('orders_goods')->where(array('id'=>$rs['id']))->save(array('rate'=>$param['rate'],'is_rate'=>1,'rate_time'=>date('Y-m-d H:i:s')))){
            //更新订单商品评价状态失败！
            $result['code']=212;
            goto error;
        }

        $rate=array('1'=>'rate_good','0'=>'rate_middle','-1'=>'rate_bad');
        //更新商品库存评价，卖家可能会删除库存，所以可不列入事务一定要执行成功        
        $this->sw[]=M('goods_attr_list')->where(array('id'=>$rs['attr_list_id']))->setInc($rate[$param['rate']]);

        //更新商品好评率
        $goods=M('goods')->lock(true)->where(array('id'=>$rs['goods_id']))->field('rate_num,rate_good,rate_middle,rate_bad')->find();
        $goods[$rate[$param['rate']]]++;
        $goods['rate_num']++;

        $goods['fraction']=round(($goods['rate_good']+100)/($goods['rate_good']+$goods['rate_middle']+$goods['rate_bad']+100),2);
        if(!$this->sw[]=M('goods')->where(array('id'=>$rs['goods_id']))->save($goods)){
            //更新商品好评率失败！
            $result['code']=213;
            goto error;
        }

        //更新订单商品评价数量
        /*
        if($rs['orders_shop']['goods_num']>$rs['orders_shop']['rate_num']+1){
            if(!$this->sw[]=M('orders_shop')->where(array('id'=>$rs['s_id']))->setInc('rate_num')){
                //更新订单商品评价数量错误！
                $result['code']=214;
                goto error;            
            }
        }else{
            if(!$this->sw[]=M('orders_shop')->where(array('id'=>$rs['s_id']))->save(array('rate_num'=>$rs['orders_shop']['goods_num'],'status'=>5))){
                //更新订单商品评价数量错误！
                $result['code']=214;
                goto error;            
            }
            //订单日志
            $logs_data=array(
                    'o_id'      =>$rs['o_id'],
                    'o_no'      =>$rs['o_no'],
                    's_id'      =>$rs['s_id'],
                    's_no'      =>$rs['s_no'],
                    'status'    =>5,
                    'remark'    =>'买家已评价'
                );
            
            if($logs_sw=D('Common/OrdersLogs')->create($logs_data)){
                if(!$this->sw[]=D('Common/OrdersLogs')->add()){
                    //添加订单日志失败！
                    $result['code']=202;
                    goto error;
                }
            }else{
                $this->sw[]=$logs_sw;
                $result['code']=4;
                $msg=D('Common/OrdersLogs')->getError();
                goto error;
            }            
        }
        */

        $do->commit();

        //如果商品及店铺都已评价，则更新订单状态为5
        $this->_orders_rate_status($rs['s_no']);
        goods_pr($rs['goods_id']);  //更新宝贝PR
        return $this->apiReturn(1,array('data'=>array('s_no'=>$rs['s_no'])));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);
    }

    /**
    * 买家修改评价
    */
    public function b_goods_rate_edit($param){
        if(empty($this->uid)) {
            //缺少参数买家ID
            return $this->apiReturn(199);
        }        

        $rs=M('orders_goods_comment')->where(['uid' => $this->uid,'id' => $param['id']])->field('etime,ip',true)->find();

        //找不到记录
        if(!$rs) return $this->apiReturn(3);

        //评价已生效，不可更改
        //if($rs['status']==1) $this->apiReturn(804);   

        //已是好评，不可更改！
        if($rs['rate']==1) return $this->apiReturn(805); 

        //您已修改过评价，不可再次修改！
        if($rs['is_change']==1) return $this->apiReturn(806);  

        //评价已超过30天，不可修改！
        if($rs['atime'] < date('Y-m-d H:i:s',time()-86400*30)) return $this->apiReturn(807);

        $do=M();
        $do->startTrans();

        $data=[
            'is_change'     =>1,
            'change_time'   =>date('Y-m-d H:i:s'),
            'rate'          =>1,
            'status'        =>1,
            'content'       =>$param['content'],
            'images'        =>$param['images']
        ];
        if(!$this->sw[]=M('orders_goods_comment')->where(['id' => $param['id']])->save($data)) goto error;


        $rate=array('1'=>'rate_good','0'=>'rate_middle','-1'=>'rate_bad');
        //更新商品库存评价，卖家可能会删除库存，所以可不列入事务一定要执行成功        
        $this->sw[]=$do->execute('update '.C('DB_PREFIX').'goods_attr_list set rate_good=rate_good+1,'.$rate[$rs['rate']].'='.$rate[$rs['rate']].'-1 where id='.$rs['attr_list_id']);

        //更新商品好评率
        $goods=M('goods')->where(array('id'=>$rs['goods_id']))->field('rate_good,rate_middle,rate_bad')->find();
        $goods[$rate[$rs['rate']]]--;
        $goods['rate_good']++;

        $goods['fraction']=round(($goods['rate_good']+100)/($goods['rate_good']+$goods['rate_middle']+$goods['rate_bad']+100),2);
        if(!$this->sw[]=M('goods')->where(array('id'=>$rs['goods_id']))->save($goods)){
            //更新商品好评率失败！
            $result['code']=213;
            goto error;
        }

        $do->commit();

        goods_pr($rs['goods_id']);  //更新宝贝PR
        return $this->apiReturn(1);

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);        
    }

    /**
    * 商家店铺评价
    * @param string $param['content']   评价内容
    * @param string $param['fraction_speed']    物流速度评分
    * @param string $param['fraction_service']  服务态度评分
    * @param string $param['fraction_desc'] 描述相符
    */
    public function b_shop_rate($param){     
        if(empty($this->uid)) {
            //缺少参数买家ID
            return $this->apiReturn(199);
        }

        $res=$this->check_s_orders(2);   
        if($res['code']!=1) return $res;

        $rs=$res['data'];

        //只有已收货订单方可评价
        if($rs['status']!=4) return $this->apiReturn(801);

        $do=M('orders_shop_comment');
        if($rate=$do->where(['s_id' => $rs['id']])->count()>0){
            //已评价，请不要重复操作
            return $this->apiReturn(800);
        }

        $fraction=($param['fraction_speed']+$param['fraction_service']+$param['fraction_desc'])/3;

        $data=[
            's_id'              =>$rs['id'],
            's_no'              =>$rs['s_no'],
            'shop_id'           =>$rs['shop_id'],
            'uid'               =>$this->uid,
            'seller_id'         =>$rs['seller_id'],
            'content'           =>$param['content'],
            'fraction_speed'    =>$param['fraction_speed'],
            'fraction_service'  =>$param['fraction_service'],
            'fraction_desc'     =>$param['fraction_desc'],
            'fraction'          =>$fraction,
            'is_sys'            =>$param['is_sys']?$param['is_sys']:0
        ];

        $do=M();
        $do->startTrans();
        if(!$this->sw[]=D('Common/OrdersShopComment')->create($data)){
            $msg=D('Common/OrdersShopComment')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/OrdersShopComment')->add()) goto error;

        //更新店铺评分
        //$shop_rate['fraction_speed']    =   M('orders_shop_comment')->where(['shop_id' => $rs['shop_id']])->avg('fraction_speed');
        //$shop_rate=$do->query('select avg(fraction_speed) as fraction_speed,avg(fraction_service) as fraction_service,avg(fraction_desc) as fraction_desc,avg(fraction) as fraction from '.C('DB_PREFIX').'orders_shop_comment where shop_id='.$rs['shop_id']);

        //店铺总体综合评价
        $shop_rate = $do->query('select count(*) as num,sum(fraction_speed) as fraction_speed,sum(fraction_service) as fraction_service,sum(fraction_desc) as fraction_desc,sum(fraction) as fraction from '.C('DB_PREFIX').'orders_shop_comment where shop_id='.$rs['shop_id']);
        //print_r($shop_rate[0]);

        //系统默认赠送8个5分,2个4分（即10笔=48分，相当于默认评分为4.8分），避免评价少时计算出来的分数太差，
        $give   = 10;
        $tmp = [];
        $tmp['fraction_speed']      = number_formats(($shop_rate[0]['fraction_speed'] + 48) / ($give + $shop_rate[0]['num']),2);
        $tmp['fraction_service']    = number_formats(($shop_rate[0]['fraction_service'] + 48) / ($give + $shop_rate[0]['num']),2);
        $tmp['fraction_desc']       = number_formats(($shop_rate[0]['fraction_desc'] + 48) / ($give + $shop_rate[0]['num']),2);
        $tmp['fraction']            = number_formats(($shop_rate[0]['fraction'] + 48) / ($give + $shop_rate[0]['num']),2);

        if(false===M('shop')->where(['id' => $rs['shop_id']])->save($tmp)) goto error;

        $do->commit();
        //如果商品及店铺都已评价，则更新订单状态为5
        $this->_orders_rate_status($rs['s_no'],1);

        //刷单检测、评价检测及店铺计分
        $this->_check_orders_shuadan(['s_no' => $rs['s_no']]);

        //更新店铺PR
        shop_pr($rs['shop_id']);

        return $this->apiReturn(1,['data' => ['s_no' =>$rs['s_no']]]);
        error:
            $do->rollback();
            return $this->apiReturn(0,'',$msg);
    }

    /**
    * 如果商品及店铺都已评价，则更新订单状态为5
    * @param array $s_no 商家订单号
    * @param int   $shop  1表示店铺已评价
    * 无须返回
    */
    public function _orders_rate_status($s_no,$shop=''){
        $rs=M('orders_shop')->where(['s_no' => $s_no ])->field('id,s_no,o_id,o_no')->find();

        $goods=M('orders_goods')->where(['s_no' => $rs['s_no'] , 'is_rate' => 0 ,'_string'=>'refund_price < total_price '])->count();
        if($shop==''){
            $shop=M('orders_shop_comment')->where(['s_no' => $rs['s_no'] ])->count();
        }

        if($goods==0 && $shop==1){
            $do=M();
            $do->startTrans();

            //更改订单状态
            if(!$this->sw[]=M('orders_shop')->where(['s_no' => $rs['s_no']])->save(['rate_time' => date('Y-m-d H:i:s'), 'status' => 5,'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['orders_history']),'is_problem' => 0])) {
            	$result['code']=200;
            	goto error;
            }

            //订单日志
            $logs_data=array(
                    'o_id'      =>$rs['o_id'],
                    'o_no'      =>$rs['o_no'],
                    's_id'      =>$rs['id'],
                    's_no'      =>$rs['s_no'],
                    'status'    =>5,
                    'remark'    =>'买家已评价'
                );
            
            if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
                $result['code']=4;
                $msg=D('Common/OrdersLogs')->getError();
                goto error;            	
            }

            if(!$this->sw[]=D('Common/OrdersLogs')->add()){
            	$result['code']=202;
            	goto error;
            }

            $do->commit();
            return true;
            error:
            	$do->rollback();
            	//return $this->apiReturn($result['code'],'',$msg);  
        }
    }

	
	/**
	* 物流跟踪
	*/
	public function query_express(){
		$res=$this->check_s_orders(0);		
        if($res['code']!=1){
            return $res;
        }
		
		$cache_name='query_express_'.$this->s_no;
		$rs=S($cache_name);		
		if($rs['express']){
			return $this->apiReturn(1,array('data'=>$rs));
		}
		
		$ors=$res['data'];		
		$do=M('express_company');
		$rs=$do->where(array('id'=>$ors['express_company_id']))->field('id,company,sub_name,logo,website,tel,code')->find();
		
		if($rs){
			$rs['express_code']=$ors['express_code'];
			$rs['express_time']=$ors['express_time'];
			$url='https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.$rs['express_code'];
			$res=$this->curl_get($url);
			$res=json_decode($res);

            if($res) {
                $rs['express'] = objectToArray($res)['data'];
                S($cache_name, $rs);
            }
			return $this->apiReturn(1,array('data'=>$rs));
		}else{
			//找不到快递公司
			return $this->apiReturn(3);
		}		
	}
	/**
	* 阿里云物流跟踪
	
	
	"showapi_res_code": 0,//showapi平台返回码,0为成功,其他为失败
	"showapi_res_error": "",//showapi平台返回的错误信息
	"showapi_res_body": {
		"mailNo": "968018776110",//快递单号
		"update": 1466926312666,//数据最后查询的时间
		"updateStr": "2016-06-26 15:31:52",//数据最后更新的时间
		"ret_code": 0,//接口调用是否成功,0为成功,其他为失败
		"flag": true,//物流信息是否获取成功
		"status": 4,-1 待查询 0 查询异常 1 暂无记录 2 在途中 3 派送中 4 已签收 5 用户拒签 6 疑难件 7 无效单
 8 超时单 9 签收失败 10 退回
		"tel": "400-889-5543",//快递公司电话
		"expSpellName": "shentong",//快递字母简称
		"data": [//具体快递路径信息
			{
				"time": "2016-06-26 12:26",
				"context": "已签收,签收人是:【本人】"
			},
		]
		"expTextName": "申通快递"//快递公司名
	*/
	public function query_express_aliyun(){
		$res=$this->check_s_orders(0);		
		
        if($res['code']!=1){
            return $res;
        }
		
		$cache_name='query_express_aliyun_'.$this->s_no;
		$rs=S($cache_name);		
		if($rs['express']){
			return $this->apiReturn(1,array('data'=>$rs));
		}
		
		$ors=$res['data'];		
		$do=M('express_company');
		$rs=$do->where(array('id'=>$ors['express_company_id']))->field('id,company,sub_name,logo,website,tel,code')->find();
		
		$data = getSiteConfig('logistics');

		if($rs){
			$method = "GET";
			$appcode = $data['appcode'];
			$headers = array();
			array_push($headers, "Authorization:APPCODE " . $appcode);
			$querys = "com=".$rs['code']."&nu=".$res['data']['express_code'];
			$bodys = "";
			$url = $data['apiurl'] . "?" . $querys;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			if (1 == strpos("$".$host, "https://"))
			{
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}
			$result = json_decode(curl_exec($curl),true);
			$result['logo'] = $rs['logo'];
			if($result['showapi_res_code'] !=0){
				$result['showapi_res_body']['expTextName'] = $rs['sub_name'];
				$result['showapi_res_body']['updateStr'] = $param['express_time'];
				$result['showapi_res_body']['mailNo'] = $param['express_code'];
				$result['showapi_res_body']['msg'] = $result['showapi_res_error'];
			}
			if($result['showapi_res_body']['ret_code'] != 0){
				$result['showapi_res_body']['expTextName'] = $rs['sub_name'];
				$result['showapi_res_body']['updateStr'] = $res['data']['express_time'];
				$result['showapi_res_body']['mailNo'] = $res['data']['express_code'];
			}
			return $this->apiReturn(1,array('data'=>$result));
		}
		return $this->apiReturn(3);	
	}	
	/**
	* 订单处理记录(处理日志)
	* @param int $check  检查订单所有者，0不检验，1检查卖家，2为检查买家
	*/
	public function orders_logs($check=0){
		$res=$this->check_s_orders($check);		
        if($res['code']!=1){
            return $res;
        }

		$do=M('orders_logs');
		$list=$do->where(array('s_no'=>$this->s_no))->field('id,atime,o_no,status,images,reason,remark')->order('id asc')->select();

		if($list){
			return $this->apiReturn(1,array('data'=>$list));
		}else{
			return $this->apiReturn(3);
		}
	}

    /**
     * 买家添加备注
     * @param int $check  检查订单所有者，0不检验，1检查卖家，2为检查买家
     * @param array $data 备注数据
     */
    public function b_remark_add($data,$check=2){
        $res=$this->check_s_orders($check);
        if($res['code']!=1){
            return $res;
        }

        $remark['buyer_remark']         = $data['buyer_remark'];
        $remark['buyer_remark_color']   = $data['buyer_remark_color'];

        if(false !== M('orders_shop')->where(['id' => $res['data']['id']])->save($remark)){
            return $this->apiReturn(1,array('data' => $remark));
        }else return $this->apiReturn(0);
    }

    /**
    * 检查账户是否正常
    */
    public function check_account($uid,$flag=0,$money=0){

        $do=M('account');
        $rs=$do->lock(true)->where('uid='.$uid)->field('atime,etime,ip',true)->find();
        if($rs){
            $data['ac_cash']        =$rs['ac_cash'];
            $data['ac_score']       =$rs['ac_score'];
            $data['ac_finance']     =$rs['ac_finance'];
            $data['ac_cash_lock']   =$rs['ac_cash_lock'];

            $sign=$this->crc($data);
            //dump($data);
            //dump($sign);


            if($rs['crc']!=$sign && $uid!=1) $rs['status']=5;

            //检查余额是否足够
            if($flag>0){
                if($rs[$this->flag_arr[$flag]]<$money){
                    $rs['status']=6;
                }
            }

            //状态（0-冻结，1-正常，2-注销）
            switch($rs['status']){
                case 0:
                    //账户被冻结
                    $result['code']=83;
                break;
                case 2:
                    //账户已注销
                    $result['code']=84;
                break;
                case 5:
                    //CRC签名错误
                    $result['code']=85;
                break;
                case 6:
                    //余额不足
                    $result['code']=86;
                break;
                default:
                    $result['code']=1;
                    $result['data']=$data;
                break;
            }

        }else{
            //账户不存在
            $result['code']=82;
        }

        //$result['msg']=C('error_code')[$result['code']];
        //return $result;

        return $this->apiReturn($result['code'],array('data'=>$result['data']));
    }


    /**
    * 方法返回，方便记录日志
    * @param integer $code 错误代码
    * @param string $msg 错误信息
    * @param array $data 要一并返回的数据
    * @param string $msg 自定义错误信息
    */
    public function apiReturn($code,$data=array(),$msg=''){
        $result['code']=$code;
        $result['msg']=$msg?$msg:C('error_code')[$code];
        if(!empty($data)) $result=array_merge($result,$data);

        if(C('API_LOG') && in_array(ACTION_NAME,$this->action_logs)){
            //在此记录日志，方便接口错误调试
            $data['atime']  =date('Y-m-d H:i:s');
            $data['s_no']   =$this->s_no;
            $data['code']   =$result['code'];
            $data['msg']    =$result['msg'];        
            $data['url']    =($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['HTTP_HOST'].__SELF__;
            $data['sw']     =@implode(',',$this->sw);
            $data['data']   =@var_export($data,true);
                    
            log_add('orders',$data);
        } 

        return $result;
    }





    /**
     * 通过订单号检测是否为刷单订单
     * 针对修改商品金额小于原价20%的订单
     * Create by lazycat
     * 2017-05-01
     */

    public function _check_orders_shuadan($param){
        $count = M('orders_shop')->where(['s_no' => $param['s_no'],'status' => ['in','5,6'],'is_shuadan' => 0])->count();   //只有已评价的订单才参与检测
        if($count == 0) return ['code' => 100,'msg' => '不符合检测规则！'];

        $orders_goods   = M('orders_goods')->where(['s_no' => $param['s_no']])->field('id,goods_id,num,total_price_edit,attr_list_id')->select();
        $goods_ids      = arr_id(['plist' => $orders_goods,'field' => 'goods_id']);
        $goods          = M('goods')->where(['id' => ['in',$goods_ids]])->getField('id,price,sale_num,pr_extra',true);


        $n      = 0;
        $ids    = [];
        foreach ($orders_goods as $val){
            if(($val['total_price_edit'] / $val['num']) < $goods[$val['goods_id']]['price'] * 0.2) {
                $ids[] = $val['id'];
                $goods[$val['goods_id']]['is_shuadan']++;
                $goods[$val['goods_id']]['num'] += $val['num'];
                $n++;
            }
        }

        if($n > 0){
            $do = M();
            $do->startTrans();

            if($this->sw[] = false === M('orders_shop')->where(['s_no' => $param['s_no']])->setField('is_shuadan',2)){
                $msg = '更新刷单记录状态失败！';
                goto error;
            }

            if($this->sw[] = false === M('orders_goods_comment')->where(['orders_goods_id' => ['in',$ids],'rate' => 1])->save(['is_shuadan' => 2,'point' => 0])){
                $msg = '更新评价记录得分失败！';
                goto error;
            }

            //删减相对应的销量
            $goods_ids = [];
            foreach($goods as $val){
                if($val['is_shuadan'] > 0) {
                    $goods_ids[] = $val['id'];
                    //刷销量的商品降低权重
                    $sale_num_str = $val['sale_num'] > $val['num'] ? 'sale_num= sale_num-' . $val['num'] : 'sale_num = 0';
                    $pr_extra_str = $val['pr_extra'] < -30 ? 'is_display=0' : 'pr_extra=pr_extra+' . (strlen($val['num']) * -3);
                    $sql = 'update ' . C('DB_PREFIX') . 'goods set ' . $sale_num_str . ',' . $pr_extra_str . ' where id=' . $val['id'];
                    if (!$this->sw[] = $do->execute($sql)) {
                        $msg = '[goods_id=' . $val['id'] . ']删减销量失败！';
                    }
                }
            }

            //删减属性销量，有可能存在属性变更情况，所以不列入事务
            foreach($orders_goods as $val){
                if(in_array($val['goods_id'],$goods_ids)) {
                    M('goods_attr_list')->where(['id' => $val['attr_list_id'],'sale_num' => ['gt',$val['num']]])->setDec('sale_num',$val['num']);
                }
            }

            $do->commit();

            //评价处理
            $this->_orders_point(['s_no' => $param['s_no']]);
            return ['code' => 1,'msg' => '更新刷单记录状态成功！'];

            error:
            $do->rollback();
            return ['code' => 0,'msg' => $msg ? $msg : '更新刷单记录状态失败！'];
        }

        return ['code' => 100,'msg' => '正常订单！'];
    }


    /**
     * 订单评价计分处理
     */
    public function _orders_point($param){
        $ors = M('orders_shop')->cache(true)->where(['s_no' => $param['s_no']])->field('status,pay_time,uid,seller_id')->find();
        if(!in_array($ors['status'],[5,6])) return ['code' => 0,'msg' => '订单状态错误！'];

        $list = M('orders_goods_comment')->where(['s_no' => $param['s_no']])->field('id,s_no,shop_id,goods_id,rate')->order('id asc')->select();
        foreach($list as $val){
            $res[] = $this->_rate_point(['id' => $val['id']],$val,$ors);
        }
        return ['code' => 1,'data' => $res];
    }

    /**
     * 评价加分判定
     * Create by layzcat
     * 2017-05-01
     * 计分规则(含匿名评价)：
     *　1)每个自然月中，相同买家和卖家之间的评价计分不得超过6分(以淘宝订单创建的时间计算)。超出计分规则范围的评价将不计分。
     *　(解释：每个自然月同买卖家之间评价计分在[-6,+6]之间，每个自然月相同买卖家之间总分不超过6分，也就是说总分在-6和+6之间，例如买家先给卖家6个差评，再给1个好评和1个差评，则7个差评都会生效计分。)
     *　2)若14天内(以订单创建的时间(付款)计算)相同买卖家之间就同一个商品进行评价，多个好评只计一分，多个差评只记-1分。
     */

    public function _rate_point($param,$comment=null,$ors=null){
        if(is_null($comment)) {
            $comment = M('orders_goods_comment')->where(['id' => $param['id']])->field('id,s_no,shop_id,goods_id,rate')->find();
        }
        if(empty($comment)) return ['code' => 0,'msg' => '记录不存在！'];
        if($comment['rate'] == 0) return ['code' => 0,'msg' => '中好评，不做处理！'];

        if(is_null($ors)) {
            $ors = M('orders_shop')->cache(true)->where(['s_no' => $comment['s_no']])->field('status,pay_time,uid,seller_id')->find();
        }
        if(!in_array($ors['status'],[5,6])) return ['code' => 0,'msg' => '订单状态错误！'];

        $point  = $comment['rate'];

        //自然月同一买卖双方
        $month_point    = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'uid' => $ors['uid'],'seller_id' => $ors['seller_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where pay_time >="'.date('Y-m-d',strtotime($ors['pay_time'])).' 00:00:00" and pay_time <="'.$ors['pay_time'].'")'])->sum('point');
        if(!is_null($month_point) && ($month_point >=6 || $month_point <= -6)) $point = 0;

        //14天内同一商品评价
        $point_14       = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'uid' => $ors['uid'],'goods_id' => $comment['goods_id'],'seller_id' => $ors['seller_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where pay_time >="'.date('Y-m-d H:i:s',strtotime($ors['pay_time']) - 86400 * 14).'" and pay_time <="'.$ors['pay_time'].'")'])->sum('point');
        if(!is_null($point_14) && ($point_14 >= 1 || $point_14 <= -1)) $point = 0;


        $do = M();
        $do->startTrans();
        if($this->sw[] = false === M('orders_goods_comment')->where(['id' => $comment['id']])->setField('point',$point)){
            $msg = '更新评价记录分数失败！';
            goto error;
        }

        $shop_point     = M('orders_goods_comment')->where(['id' => ['lt',$comment['id']],'shop_id' => $comment['shop_id']])->sum('point');
        if($this->sw[] = false === M('shop')->where(['id' => $comment['shop_id']])->save(['shop_point' => $shop_point,'shop_level' => $this->_shop_level($shop_point)])){
            $msg = '更新店铺分数失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => '更新分数失败'];
    }

    /**
     * 计算店铺等级
     * Create by lazycat
     * 2017-04-28
     */

    /**
     * D（铜币）	5-20
     * D+（2铜币）	21-40
     * D++（3铜币）	41-60
     * C--（铜宝）	61-80
     * C-（1铜宝）	81-100
     * C（2铜宝）	101-200
     * C+（3铜宝）	201-400
     * C++（4铜宝）	401-700
     * B--（银宝）	701-1000
     * B-（1银宝）	1001-1500
     * B（2银宝）	1501-3000
     * B+（3银宝）	3001-5000
     * B++（4银宝）	5001-10000
     * A--（金宝）	10001-20000
     * A-（1金宝）	20001-50000
     * A（2金宝）	50001-80000
     * A+（3金宝）	80001-150000
     * A++（4金宝）	150001-200000
     * S(玉如意)	200000以上
     */
    public function _shop_level($point){
        $level = 0;
        if($point >= 5 && $point < 41) $level = 1;
        elseif($point >= 41 && $point < 61) $level = 2;
        elseif($point >= 61 && $point < 81) $level = 3;
        elseif($point >= 81 && $point < 101) $level = 4;
        elseif($point >= 101 && $point < 201) $level = 5;
        elseif($point >= 201 && $point < 401) $level = 6;
        elseif($point >= 401 && $point < 701) $level = 7;
        elseif($point >= 701 && $point < 1001) $level = 8;
        elseif($point >= 1001 && $point < 1501) $level = 9;
        elseif($point >= 1501 && $point < 3001) $level = 10;
        elseif($point >= 3001 && $point < 5001) $level = 11;
        elseif($point >= 5001 && $point < 10001) $level = 12;
        elseif($point >= 10001 && $point < 20001) $level = 13;
        elseif($point >= 20001 && $point < 50001) $level = 14;
        elseif($point >= 50001 && $point < 80001) $level = 15;
        elseif($point >= 80001 && $point < 150001) $level = 16;
        elseif($point >= 150001 && $point < 200001) $level = 17;
        elseif($point >= 200001) $level = 18;

        return $level;
    }


    //析构方法
    public function __destruct(){}
}