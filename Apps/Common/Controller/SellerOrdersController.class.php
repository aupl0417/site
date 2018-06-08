<?php
/**
* 卖家订单管理
*/
namespace Common\Controller;
use Think\Controller;
use Common\Builder\Activity;
class SellerOrdersController extends OrdersController {
    private $action_logs      =array('send_express','edit_express','s_close','edit_price','orders_price_edit');   //须要记录日志的方法
    public function s_count(){
        if(empty($this->seller_id)) {
            //缺少卖家ID
            return $this->apiReturn(220);
        }

        //未付款订单
        $result['data'][1]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>1))->count();

        //已付款订单
        $result['data'][2]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>2))->count();

        //已发货订单
        $result['data'][3]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>3))->count();

        //已收货订单
        $result['data'][4]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>4))->count();

        //已评价订单
        $result['data'][5]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>5))->count();

        //已归档订单
        $result['data'][6]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>6))->count();

        //已关闭订单
        $result['data'][10]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'status'=>array('in','10,11')))->count();

        //退款中订单
        $result['data'][20]=M('refund')->where(array('seller_id'=>$this->seller_id,'status'=>['not in','20,100'], 'orders_status' => ['lt', 4]))->count();
        //售后中订单
        $result['data'][21]=M('refund')->where(array('seller_id'=>$this->seller_id,'status'=>['not in','20,100'], 'orders_status' => ['gt', 3]))->count();

        //售后中订单
        $result['data'][30]=M('orders_shop')->where(array('seller_id'=>$this->seller_id,'support_num'=>array('gt',0),'status'=>array('in','4,5')))->count();

        $result['data']['all']=M('orders_shop')->where(array('seller_id'=>$this->seller_id))->count();

        $result['code']=1;
        return $result;
    }

    /**
    * 发货
    * @param string $express_code 快递单号
     * @param int $express_company_id 快递公司ID
     * @param string $express_remark 发货备注
    */
    public function send_express($param){
        //$express_code,$express_company_id,$express_remark=''

        $res=$this->check_s_orders();
        if($res['code']!=1){
            return $res;
        }

        $rs=$res['data'];
        //只有已付款状态方可发货
        if($rs['status']!=2){
            return $this->apiReturn(205);
        }

        //如果商品款已退完只剩下运费是不可以发货的
        if($rs['goods_price']==$rs['refund_price']) $this->apiReturn(1020);

        //是否存在退款记录
        $refund=M('refund')->where(['s_no' => $this->s_no,'orders_status' => $rs['status'],'status' => ['not in','20,100']])->field('id,r_no,type')->select();

        $do=M();
		$do->startTrans();
		
		$express_company=$rs['express_company'];

        
		//是否更改快递公司
		if($rs['express_company_id']!=$param['express_company_id']){
			$express=M('express_company')->cache(true)->where(array('id'=>$param['express_company_id']))->field('id,sub_name')->find();
			$is_change=1;
			$express_company=$express['sub_name'];
		}
		
        if(!$this->sw[]=M('orders_shop')->where(array('s_no'=>$this->s_no))->save(array('status'=>3,'express_company_id'=>$param['express_company_id'],'express_company'=>$express_company,'express_code'=>$param['express_code'],'express_remark'=>$param['express_remark'],'express_time'=>date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),'is_problem' => 0))){
            //更新订单状态失败
            $result['code']=200;
            goto error;
        }

        //写入订单日志
        $logs_data=array(
            'o_id'      =>$rs['o_id'],
            'o_no'      =>$rs['o_no'],
            's_id'      =>$rs['id'],
            's_no'      =>$rs['s_no'],
            'status'    =>3,
            'remark'    =>'卖家已发货',
            //'reason'    =>($is_change==1?'<div>更改了快递公司，将'.$rs['express_company'].'改为'.$express_company.'</div>':'').$express_company.'，快递单号：'.$express_code
            'reason'    =>$express_company.'，快递单号：'.$param['express_code']. ($param['express_remark'] ? '<br>'.$param['express_remark'] : '')
        );
        if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
            $result['code']=4;
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/OrdersLogs')->add()){
            //添加订单日志失败
            $result['code']=202;
            goto error;
        }

        //如果存在退款记录，则取消退款
        if($refund){
            foreach($refund as $val){
                if(!$this->sw[]=M('refund')->where(['id' => $val['id']])->save(['status' => 20,'cancel_time' => date('Y-m-d H:i:s')])) goto error;

                //日志数据
                $logs=[
                    'r_id'          =>$val['id'],
                    'r_no'          =>$val['r_no'],
                    'uid'           =>$this->seller_id,
                    'status'        =>20,
                    'type'          =>$val['type'],
                    'remark'        =>C('error_code.1008'), //卖家发货，退款默认取消！
                ];

                if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
                    $msg=D('Common/RefundLogs')->getError();
                    goto error;            
                }
                if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;                
            }
        }

        $do->commit();

        //发短信通知
        $sms_data['mobile']=M('orders')->where(['id' => $rs['o_id']])->getField('mobile');
        if(!empty($sms_data['mobile'])){
            $shop_name=M('shop')->where(['id' => $rs['shop_id']])->getField('shop_name');
            $sms_data['content']=$this->sms_tpl(13,
                    ['{shop_name}','{orderno}','{express_company}','{express_code}'],
                    [$shop_name,$rs['s_no'],$express_company,$param['express_code']]
                );

            if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
        }
		
		//发送订单消息
		$msg_data = ['tpl_tag'=>'orders_deliver','s_no'=>$this->s_no];
		tag('send_msg',$msg_data);
        
        return $this->apiReturn(1,array('data'=>array('s_no'=>$rs['s_no'],'express_code'=>$param['express_code'])));

        error:
            $do->rollback();
            return $this->apiReturn(4,'',C('error_code.0').$msg);

    }

    /**
     * 修改发货快递
     * @param string $param['express_code'] 快递单号
     * @param int $param['express_company_id'] 快递公司ID
     * @param string $param['express_remark'] 发货备注
     */
    public function edit_express($param){
        $res=$this->check_s_orders();
        if($res['code']!=1){
            return $res;
        }

        $rs=$res['data'];
        //已发货且未确认收货的状态下方可更改快递方式！
        if($rs['status'] != 3){
            return $this->apiReturn(209);
        }

        $express=M('express_company')->cache(true)->where(array('id'=>$param['express_company_id']))->field('id,sub_name')->find();

        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('orders_shop')->where(array('s_no'=>$this->s_no))->save(array('express_company_id'=>$param['express_company_id'],'express_company'=>$express['sub_name'],'express_code'=>$param['express_code'],'express_remark'=>$param['express_remark']))){
            //更新订单状态失败
            $result['code']=200;
            goto error;
        }

        //写入订单日志
        $logs_data=array(
            'o_id'      =>$rs['o_id'],
            'o_no'      =>$rs['o_no'],
            's_id'      =>$rs['id'],
            's_no'      =>$rs['s_no'],
            'status'    =>3,
            'remark'    =>'更改发货方式',
            'reason'    =>$express['sub_name'].'，快递单号：'.$param['express_code']. ($param['express_remark'] ? '<br>'.$param['express_remark'] : '')
        );

        if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
            $result['code']=4;
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/OrdersLogs')->add()){
            //添加订单日志失败
            $result['code']=202;
            goto error;
        }

        $do->commit();
        return $this->apiReturn(1,array('data'=>array('s_no'=>$rs['s_no'],'express_code'=>$param['express_code'])));

        error:
        $do->rollback();
        return $this->apiReturn(4,'',C('error_code.0').$msg);

    }

    /**
    * 商家关闭订单
    */
    public function s_close($reason){
        $res=$this->check_s_orders();
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

        //主订单
        $ors=M('orders')->where(array('id'=>$rs['o_id']))->field('shop_num,refund_num,pay_price')->find();

        $do=M();
        $do->startTrans();
        //订单日志      
        
        $logs_data=array(
                'o_id'      =>$rs['o_id'],
                'o_no'      =>$rs['o_no'],
                's_id'      =>$rs['id'],
                's_no'      =>$rs['s_no'],
                'status'    =>10,
                'remark'    =>'卖家关闭订单',
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
        
        //关闭活动
        Activity::setStatus($rs['s_no'], $rs['uid'], 2);
        
        $do->commit();
        return $this->apiReturn(1,array('data'=>array('o_no'=>$rs['o_no'],'s_no'=>$rs['s_no'])));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);                         
    }    


    /**
    * 卖家改价
    */
    public function edit_price($pay_price){
        $res=$this->check_s_orders();
        if($res['code']!=1){
            return $res;
        }

        $rs=$res['data'];
        if($rs['status']!=1){
            //该订单状态下不充许关闭订单！
            return $this->apiReturn(197);
        }

        //改价范围不超过50%
        if($rs['total_price']*0.5 >$pay_price || $rs['total_price']*1.5 < $pay_price){
            //更新价格不得低于订单金额的50%或超过50%！
            return $this->apiReturn(215);
        }

        $do=M();
        $do->startTrans();
        //改价不写入订单日志
        if(!$this->sw[]=M('orders_shop')->where(array('id'=>$rs['id']))->save(array('pay_price'=>$pay_price))){
            $result['code']=216;            
            goto error;
        }

        $edit_price=($rs['pay_price']-$pay_price)*-1;
        //if($edit_price>0) $edit_price=$edit_price * -1;
        if(!$this->sw[]=M('orders')->where(array('id'=>$rs['o_id']))->setInc('pay_price',$edit_price)){
            $result['code']=216;
            goto error;
        }

        $do->commit();
        return $this->apiReturn(1,array('data'=>array('o_no'=>$rs['o_no'],'s_no'=>$rs['s_no'],'pay_price'=>$pay_price)));

        error:
            $do->rollback();
            return $this->apiReturn($result['code'],'',$msg);         

    }

    /**
    * 卖家获取订单列表
    * @param int $param['pagesize'] 分页记录数
    * @param string $param['action'] 分页链接前缀
    * @param string $param['query'] 查询参数
    * @param int $param['p']    第n页
    */
    public function s_orders_list($param=array()){
        if(empty($this->seller_id)) {
            //缺少买家ID
            return $this->apiReturn(199);
        }
        $pay_typename = $this->pay_typename;
        $map['seller_id']=$this->seller_id;

        switch($param['status']){
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
            case 40: //待分账
//                $map['status']      =array('in','4,5');
//                $map['inventory_type']  = 0;
//                $map['receipt_time'] = ['gt',date('Y-m-d H:i:s', time() - (86400 * 10))];
                if (empty(I('post.s_no'))) return $this->apiReturn(3);
                break;
            default:
                if($param['status']!='') $map['status']=$param['status'];
            break;
        }
		//增加供货商id读取
		if($param['supplier_id']) $map['supplier_id'] = $param['supplier_id'];

        if(I('post.s_no')) {
            if (strpos(I('post.s_no'), ',') !== false) {
                $map['s_no'] = ['in', I('post.s_no')];
            } else {
                $map['s_no'] =I('post.s_no');
            }
        }
        if(I('post.sday')!='' && I('post.eday')!='') $map['atime']  = ['between',[I('post.sday'),I('post.eday')]];
        if(I('post.sday')!='' && I('post.eday')=='') $map['atime']  = ['egt',I('post.sday')];
        if(I('post.sday')=='' && I('post.eday')!='') $map['atime']  = ['elt',I('post.eday')];
        if(I('post.ids') && !empty(I('post.ids'))) $map['id'] = ['in', I('post.ids')];

        if(I('post.goods_name')) $map['_string'] = 'id in (select s_id from '.C('DB_PREFIX').'orders_goods where goods_name like "%'.I('post.goods_name').'%" and seller_id='.$this->seller_id.')';
        if(I('post.is_scm') == 1) {
            $sqlstr = 'id in (select s_id from '.C('DB_PREFIX').'orders_goods where date_format(purchase_time,"%Y-%m-%d")!="0000-00-00")';
            $map['_string'] = $map['_string'] ? $map['_string'] .' AND '.$sqlstr : $sqlstr;
        }elseif(I('post.is_scm') == 2) {
            $sqlstr = 'id in (select s_id from '.C('DB_PREFIX').'orders_goods where date_format(purchase_time,"%Y-%m-%d")="0000-00-00")';
            $map['_string'] = $map['_string'] ? $map['_string'] .' AND '.$sqlstr : $sqlstr;
        }
        if(I('post.nick')) {
            $uid=M('user')->cache(true)->where(['nick' => I('post.nick')])->getField('id');
            if($uid) $map['uid']    =$uid;
            else return $this->apiReturn(3);
        }
        $pagesize=$param['pagesize']?$param['pagesize']:10;
        $pagelist=pagelist(array(
                'table'     =>'Common/OrdersShopSellerRelation',
                'do'        =>'D',
                'pagesize'  =>$pagesize,
                'map'       =>$map,
                'order'     =>'atime desc',
                'relation'  =>true,
                'action'    =>$param['action'],
                'query'     =>$param['query'],
                'p'         =>$param['p'],
            ));
        $pagelist['count']=$this->s_count()['data'];

        if($pagelist['allnum']>0){
            $area   =   $this->cache_table('area');
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['status_name']          =C('orders_code')[7][$val['status']];
                $pagelist['list'][$key]['orders']['province']   =$area[$val['orders']['province']];
                $pagelist['list'][$key]['orders']['city']       =$area[$val['orders']['city']];
                $pagelist['list'][$key]['orders']['district']   =$area[$val['orders']['district']];
                $pagelist['list'][$key]['orders']['town']       =$area[$val['orders']['town']];
                $pagelist['list'][$key]['pay_typename']         =$pay_typename[$val['pay_type']];
                $pagelist['list'][$key]['orders_goods']         =imgsize_list($val['orders_goods'],'images',160);
                $pagelist['list'][$key]['buyer']                =imgsize_list($val['buyer'],'face',80);

				//获取用户参与的活动
				$order_activity = Activity::getActivityByOrder($val['s_no']);
				foreach($order_activity as $k => $v){
					//将唐宝支付的消费升级促销去掉
					if($val['pay_type'] == 2 && $v['type_id'] == 7 ){
						unset($order_activity[$k]);
					}
				}
				$pagelist['list'][$key]['activity'] =  $order_activity;
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
				    }
				}
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
    * 订单详情
    */
    public function s_view(){
        $res=$this->check_s_orders();
        if($res['code']!=1) return $res;

        $rs=$res['data'];
        $pay_typename = $this->pay_typename;

        $do=D('Common/OrdersShopSellerRelation');
        $rs=$do->relation(true)->where(array('s_no'=>$this->s_no))->field('etime,ip',true)->find();
        //$rs['express']              =D('Common/ExpressViewRelation')->relation(true)->where(array('id'=>$rs['express_id']))->field('express_company_id')->find();
        //$rs['express']              =imgsize_list($rs['express'],'logo',150,50);
        $rs['status_name']          = C('orders_code')[7][$rs['status']];
        $rs['pay_typename']         =$pay_typename[$rs['pay_type']];

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
        $rs['buyer']                =imgsize_list($rs['buyer'],'face',80);

        //订单是否有退款中商品
        $rs['refund']               =M('refund')->where(['s_id' => $rs['id'], 'status' => ['notin', '20, 100']])->count();

        //商品金额是否已全部退完
        if($rs['goods_price']==$rs['refund_price']) $rs['refund_off']=1;

        return $this->apiReturn(1,array('data'=>$rs));
    }

    /**
    * 列出改价商品
    */
    public function orders_goods(){
        $res=$this->check_s_orders();
        if($res['code']!=1) return $res;

        //只有未付款订单才可以改价！
        if($res['data']['status']!=1) return $this->apiReturn(250);

        //dump($res);
        $rs=[
            's_no'              =>$res['data']['s_no'],
            'express_price'     =>$res['data']['express_price'],
            'express_price_edit'=>$res['data']['express_price_edit'],
            'coupon_price'      =>$res['data']['coupon_price'],
            'goods_price'       =>$res['data']['goods_price'],
            'goods_price_edit'  =>$res['data']['goods_price_edit'],
            'discount_price'    =>$res['data']['discount_price'],
            'total_price'       =>$res['data']['total_price'],
            'pay_price'         =>$res['data']['pay_price'],
        ];

        $rs['orders_goods']     =M('orders_goods')->where(['s_no' => $res['data']['s_no']])->field('id,goods_id,attr_list_id,attr_name,price,weight,num,total_price,total_price_edit,total_weight,goods_name,images')->select();
        $rs['orders_goods']     =imgsize_list($rs['orders_goods'],'images',160);

        return $this->apiReturn(1,array('data'=>$rs));
    }

    /**
    * 修改价格
    * @param float $_POST['express_price']  运费
    * @param string $_POST['goods_price']   商品价格
    */
    public function orders_price_edit($param){
        $res=$this->check_s_orders();
        if($res['code']!=1) return $res;

        $rs=$res['data'];

        //订单正在支付流程中时不可以更改价格
        $tmp = S('paying_'.$rs['s_no']);
        if($tmp) return $this->apiReturn(4,'','订单正在支付流程中，不允许修改价格，请1分钟后再试！');

        //只有未付款订单才可以改价！
        if($rs['status']!=1) return $this->apiReturn(250);

        $pay_price      = $param['goods_price'] + $param['express_price'];
        $discount_price = $rs['goods_price'] - $param['goods_price'];   // > 0 表示优惠，<0 表示加了价格
        if($param['goods_price'] == $rs['goods_price_edit'] && $param['express_price'] == $rs['express_price_edit']) $this->apiReturn(1); //没有任何修改直接返回

        //订单金额必须大于等于0.1
        if($pay_price < 0.1) return $this->apiReturn(179);

        //修改的运费不符合要求，暂是验证运费不要修改得太离谱
        //运费不需要限制，因为运费不赠送积分
        //if($param['express_price']<0 || ($rs['goods_price']>100 && $param['express_price']>$rs['goods_price']*0.5) || ($rs['goods_price']<=100 && $rs['goods_price']>30 && $param['express_price']>$rs['goods_price']*0.8)) return $this->apiReturn(251);
        if($param['express_price'] < 0) return $this->apiReturn(252);   //运费金额必须大于或等于0

        //改价金额幅度不得超过50%;
        if($param['goods_price'] < round($rs['goods_price']*0.5,2) || $param['goods_price'] > round($rs['goods_price']*1.5,2)) return $this->apiReturn(215);

        //每款商品都不得低于0.1于
        if($param['goods_price'] < $rs['goods_num'] * 0.1) $this->apiReturn(4,'','商品金额不得低于￥'.($rs['goods_num'] * 0.1));

        $list=M('orders_goods')->where(['s_no'=> $rs['s_no']])->field('id,price,total_price,total_price_edit,attr_name,goods_name,score_ratio')->select();


        $do=M();
        $do->startTrans();

        //由于退款要退积分，所以优惠金额分摊至各个商品中去
        $score          = 0;
        $items_price    = 0;
        $sale_price     = abs($discount_price); //转为正数

        foreach($list as $key => $val){
            $item_point             = $val['total_price'] / $rs['goods_price'];             //在订单中占比
            $item_price             = number_formats($sale_price * $item_point ,2);    //不进行四舍五入，最后多出的分给最后一个

            if($key == count($list)-1 && $sale_price - $items_price != $item_price){  //由于没有四舍五入，最后一个有可能多出
                $item_price         = $sale_price - $items_price;
            }
            if($item_price < 0)     $item_price = 0;
            $items_price            += $item_price;

            //writeLog('item-price-' . $item_price);

            $val['total_price_edit']  = $val['total_price'] - ($discount_price > 0 ? $item_price : $item_price * -1);
            $val['score']             = $val['total_price_edit'] * $val['score_ratio'] * 100;
            $score += $val['score'];

            if($this->sw[] = false === M('orders_goods')->save($val)){
                $msg = '第'.$key.'款商品更新价格失败！';
                goto error;
            }
        }

        if(!$this->sw[]=M('orders_shop')->where(['s_no' => $rs['s_no']])->save(['goods_price_edit'=>$param['goods_price'],'pay_price' => $pay_price,'money'=>$pay_price,'discount_price' => $discount_price,'score' => $score,'express_price_edit' => $param['express_price']])) goto error;
        
        //合并订单价格更新
        $sum_price=M('orders_shop')->where(['o_no' => $rs['o_no']])->field('sum(pay_price) as pay_price,sum(score) as score')->find();
        if(false===$this->sw[]=M('orders')->where(['o_no' => $rs['o_no']])->save($sum_price)) goto error;


        $do->commit();
        return $this->apiReturn(1);

        error:
            $do->rollback();
            return $this->apiReturn(0);

    }

    /**
     * 卖家添加备注
     * @param int $check  检查订单所有者，0不检验，1检查卖家，2为检查买家
     * @param array $data 备注数据
     */
    public function s_remark_add($data,$check=1){
        $res=$this->check_s_orders($check);
        if($res['code']!=1){
            return $res;
        }

        $remark['seller_remark']         = $data['seller_remark'];
        $remark['seller_remark_color']   = $data['seller_remark_color'];

        if(false !== M('orders_shop')->where(['id' => $res['data']['id']])->save($remark)){
            return $this->apiReturn(1,array('data' => $remark));
        }else return $this->apiReturn(0);
    }

}