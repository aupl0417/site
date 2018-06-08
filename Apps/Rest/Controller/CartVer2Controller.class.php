<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 购物车 Ver 2.0
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
| 2016-12-10
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
use Common\Builder\Activity;
use Common\Builder\Daigou;
//use Common\Builder\Queue;
class CartVer2Controller extends CommonController {
	protected $action_logs = array('add','delete','create_orders','create_activity_orders');
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
    * 购物车商品列表
    * @param string $_POST['openid']    用户openid
    */
    public function goods_list(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('Common/CartAttrListRelation');
        $goods = $do->relation(true)->where(array('uid'=>$this->uid,'is_display' => 0))->field('etime,ip',true)->select();
        //检测商品状态
        $shop_ids = array();
        $express_tpl_ids = array();
        $list = array();
        foreach($goods as $key => $val){
            $goods[$key]['images']      =myurl($val['attr_list']['images'],100);
            $goods[$key]['goods_name']  =$val['goods']['goods_name'];
            $goods[$key]['status']      =$val['goods']['status'];
            $goods[$key]['status_name'] =$val['goods']['status']==1?'正常':'异常';
            $goods[$key]['detail_url']  ='/Goods/view/id/'.$val['attr_list_id'].'.html';
            if($val['attr_id']!=$val['attr_list']['attr_id']){
                $goods[$key]['status']      =2;
                $goods[$key]['status_name'] ='商家已变更库存属性！';
            }
            if($val['num']>$val['attr_list']['num']){
                $goods[$key]['status']      =3;
                $goods[$key]['status_name'] ='库存不足，最多只能订购'.$val['attr_list']['num'].'件！';
            }
            //单价、重量、奖励比例、运费模板变更检测
            if($val['price']!=$val['attr_list']['price'] || $val['weight']!=$val['attr_list']['weight'] || $val['score_ratio'] != $val['goods']['score_ratio'] || $val['express_tpl_id'] != $val['goods']['express_tpl_id']){ //价格或重量是否有变更
                $do=D('Common/Cart');
                $goods[$key]['price']           =$val['attr_list']['price'];
                $goods[$key]['weight']          =$val['attr_list']['weight'];
                $goods[$key]['total_price']     =$goods[$key]['num']* $goods[$key]['price'];
                $goods[$key]['total_weight']    =$goods[$key]['num']* $goods[$key]['weight'];
                $goods[$key]['score_ratio']     =$val['goods']['score_ratio'];
                $goods[$key]['score']           =$goods[$key]['score_ratio'] * $goods[$key]['total_price'] * 100;
                $goods[$key]['express_tpl_id']   =$val['goods']['express_tpl_id'];

                if($do->create($goods[$key])) $do->save();
            }

            //商品取消参与官方秒杀活动(注，秒杀只能通过立即购买订购，返回至购物车将恢复原价)
            if($val['officialactivity_id'] == 250){
                M('cart')->where(['id' => $val['id']])->save(['officialactivity_id' => 0,'officialactivity_join_id' => 0]);
            }

            if(!in_array($val['shop_id'],$shop_ids)) $shop_ids[] = $val['shop_id'];
            if(!in_array($goods[$key]['express_tpl_id'],$express_tpl_ids)) $express_tpl_ids[] = $goods[$key]['express_tpl_id'];

            unset($goods[$key]['goods']);
            unset($goods[$key]['attr_list']);
            $list[$goods[$key]['express_tpl_id']]['goods'][]    = $goods[$key];
            $list[$goods[$key]['express_tpl_id']]['shop_id']    = $val['shop_id'];
            $list[$goods[$key]['express_tpl_id']]['seller_id']  = $val['seller_id'];
            //是否存在官方活动
            $list[$goods[$key]['express_tpl_id']]['is_officialactivity'] = $val['officialactivity_id'] > 0 ? 1 : 0;

            $total['num']++;
            $total['total_price'] += $goods[$key]['total_price'];
            $total['total_weight'] += $goods[$key]['total_weight'];
            $total['total_score'] += $goods[$key]['score'];
        }
        $total['total_price']   =   number_format($total['total_price'], 2);
        $total['shop_num']	=	count($list);

        foreach($list as $key => $val){
            $tmp = M('shop')->where(['id' => $val['shop_id']])->field('id,shop_name,shop_logo,domain,qq,wang,mobile')->find();
            $tmp['shop_url'] = shop_url($tmp['id'],$tmp['domain']);
            $list[$key]['seller_nick'] = M('user')->where(['uid' => $val['seller_id']])->cache(true)->getField('nick');
            $list[$key]['shop'] = $tmp;
            if(!$val['is_officialactivity']) $list[$key]['activity'] = getActivityGoods($val['shop_id'], true);
        }
        if($list){
            //兼容wap
            $tmp = array();
            foreach($list as $val){
                $tmp[] = $val;
            }
            $list = $tmp;
            $this->apiReturn(1,array('data'=>$list,'total'=>$total));
        }else{
            //购物车为空
            $this->apiReturn(3);
        }
    }



    /**
    * 列出选中待付款的商品
    * @param string $_POST['openid']    用户openid
    */
    public function selected_goods(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();


        $do=D('Common/CartAttrListRelation');
        $goods = $do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1))->field('etime,ip',true)->select();

        if(empty($goods)) $this->apiReturn(175); 	//购物车中无选中要进行支付的商品
        //检测商品状态
        $shop_ids = array();
        $express_tpl_ids = array();
        $list = array();
        foreach($goods as $key => $val){
            $isActivity = false; //是否参与了活动
            $goods[$key]['images']      =myurl($val['attr_list']['images'],100);
            $goods[$key]['goods_name']  =$val['goods']['goods_name'];
            $goods[$key]['status']      =$val['goods']['status'];
            $goods[$key]['status_name'] =$val['goods']['status']==1?'正常':'异常';
            $goods[$key]['detail_url']  ='/Goods/view/id/'.$val['attr_list_id'].'.html';

            if($val['attr_id']!=$val['attr_list']['attr_id']){
                $goods[$key]['status']      =2;
                $goods[$key]['status_name'] ='商家已变更库存属性！';
            }
            if($val['num']>$val['attr_list']['num']){
                $goods[$key]['status']      =3;
                $goods[$key]['status_name'] ='库存不足，最多只能订购'.$val['attr_list']['num'].'件！';
            }
            //单价、重量、奖励比例、运费模板变更检测
            $is_edit = true;
            if($val['price']!=$val['attr_list']['price'] || $val['weight']!=$val['attr_list']['weight'] || $val['score_ratio'] != $val['goods']['score_ratio'] || $val['express_tpl_id'] != $val['goods']['express_tpl_id']){ //价格或重量是否有变更
                //是否参与官方活动
                if($val['goods']['officialactivity_join_id'] > 0){
                    $officialactivity = M('officialactivity_join')->where(['id' => $val['goods']['officialactivity_join_id']])->field('day,time')->find();
                    $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
                    if($time_dif > 0 || $time_dif < -86400) { //活动未开始或已过期
                    }else $is_edit = false;
                }

                if($is_edit == true) {
                    $do=D('Common/Cart');
                    $goods[$key]['price']           =$val['attr_list']['price'];
                    $goods[$key]['weight']          =$val['attr_list']['weight'];
                    $goods[$key]['total_price']     =$goods[$key]['num']* $goods[$key]['price'];
                    $goods[$key]['total_weight']    =$goods[$key]['num']* $goods[$key]['weight'];
                    $goods[$key]['score_ratio']     =$val['goods']['score_ratio'];
                    $goods[$key]['score']           =$goods[$key]['score_ratio'] * $goods[$key]['total_price'] * 100;
                    $goods[$key]['express_tpl_id']  =$val['goods']['express_tpl_id'];
                    if($do->create($goods[$key])) $do->save();
                }

            }

            //商品取消参与官方秒杀活动(注，秒杀只能通过立即购买订购，返回至购物车将恢复原价)
            if($val['officialactivity_id'] == 250 && $is_edit == true){
                M('cart')->where(['id' => $val['id']])->save(['officialactivity_id' => 0,'officialactivity_join_id' => 0]);
            }
            if($val['goods']['officialactivity_join_id'] == 0) {    //没有官方活动的情况下
                if ($val['goods']['is_daigou'] > 0) {   //如果当前商品为代购
                    //$a = (new Daigou())->compute($val);
                    $dg = new Daigou();
                    $res = $dg->compute(['total_price' => $val['total_price'], 'daigou_ratio' => $val['goods']['daigou_ratio']]);
                    if ($res) {
                        $goods[$key]['price'] = $val['attr_list']['price'];
                        $goods[$key]['total_price'] = round($goods[$key]['total_price'] + $res['daigou_cost'], 2);
                    }
                } else {//0元购及秒杀促销活动
                    if (isset($_POST['spm']) && !empty($_POST['spm'])) {
                        //临时判断
                        $isSpike = M('activity_participate')->where(['activity_id' => ['in', '626,584,574,572,570,313'], 'status' => ['lt', 2], 'uid' => $this->uid])->getField('id');
                        if ($isSpike) {
                            $list['activity'] = '您已经参加过，不可再次参加当前活动。';
                        } else {
                            $activitys = Activity::getSpikeAndRestriction($val['shop_id'], $val['goods_id'], null, $this->uid);
                            if ($activitys) {
                                if ($goods[$key]['num'] >= $activitys['max_num'] && $activitys['max_num'] > 0) {  //如果购买的数量大于活动最多购买的数量
                                    $num = $goods[$key]['num'] - $activitys['max_num'];
                                    $goods[$key]['total_price'] = ($activitys['max_num'] * $activitys['full_money']) + ($num * $goods[$key]['price']);
                                    $goods[$key]['score'] = ($goods[$key]['total_price'] * $val['score_ratio']) * 100;
                                } else {
                                    $goods[$key]['price'] = $val['attr_list']['price'];
                                    $goods[$key]['total_price'] = round($goods[$key]['num'] * $activitys['full_money'], 2);
                                    $goods[$key]['score'] = ($goods[$key]['total_price'] * $val['score_ratio']) * 100;
                                }
                                $isActivity = true;
                            } else {
                                $list['activity'] = '您已经参加过，不可再次参加当前活动。';
                            }
                        }
                    }
                }
            }else{
                //存在官方活动
                $list['list'][$goods[$key]['express_tpl_id']]['is_officialactivity']    = 1;
            }

            if(!in_array($val['shop_id'],$shop_ids)) $shop_ids[] = $val['shop_id'];
            if(!in_array($goods[$key]['express_tpl_id'],$express_tpl_ids)) $express_tpl_ids[] = $goods[$key]['express_tpl_id'];

            unset($goods[$key]['goods']);
            unset($goods[$key]['attr_list']);
            $list['list'][$goods[$key]['express_tpl_id']]['express_tpl_id'] = $goods[$key]['express_tpl_id'];
            $list['list'][$goods[$key]['express_tpl_id']]['goods'][]        = $goods[$key];
            $list['list'][$goods[$key]['express_tpl_id']]['shop_id']        = $val['shop_id'];
            $list['list'][$goods[$key]['express_tpl_id']]['seller_id']      = $val['seller_id'];
            $list['list'][$goods[$key]['express_tpl_id']]['isActivity']     = $isActivity;
            $list['list'][$goods[$key]['express_tpl_id']]['total_price']    += $goods[$key]['total_price'];
            $list['list'][$goods[$key]['express_tpl_id']]['total_weight']   += $goods[$key]['total_weight'];
            $list['list'][$goods[$key]['express_tpl_id']]['total_score']    += $goods[$key]['score'];

            $list['num']++;
            $list['total_price'] += $goods[$key]['total_price'];
            $list['total_weight'] += $goods[$key]['total_weight'];
            $list['total_score'] += $goods[$key]['score'];
        }
        $list['allMoney']   =   round($list['total_price'], 2);
        $list['shop_num']	=	count($list['list']);
        $list['full_reduction'] = 0;    //满减的金额
        foreach($list['list'] as $key => $val){
            $tmp = M('shop')->where(['id' => $val['shop_id']])->field('id,shop_name,shop_logo,domain,qq,wang,mobile')->find();
            $tmp['shop_url'] = shop_url($tmp['id'],$tmp['domain']);
            $list['list'][$key]['shop'] = $tmp;

            if($val['is_officialactivity'] != 1) {
                $activity[$key] = [];
                $coupon = 0;
                if ($val['isActivity'] == false) {  //如果没有参与活动，则可以选择优惠券,并且计算普通促销优惠金额
                    //可用优惠券
                    $couponMap = [
                        'is_use' => 0,
                        'status' => 1,
                        'sday' => array('elt', date('Y-m-d')),
                        'eday' => array('egt', date('Y-m-d')),
                        'uid' => $this->uid,
                        'min_price' => array('elt', round($val['total_price'], 2)),
                    ];
                    $couponMap['_string'] = '(type=1 and shop_id='.$val['shop_id'].') or (type=2)';
                    $coupon = M('coupon')->where($couponMap)->field('id,code,price,use_type,type')->order('price desc')->limit(10)->select();

                    //writeLog(M('coupon')->getLastSql());

                    //$list['list'][$key]['activity'] = Activity::calcTotalPrice($val['shop_id'], $val['total_price']);
                    $activity[$key] = Activity::calcTotalPrice($val['shop_id'], $val['total_price']);
                    if ($activity[$key] !== false) {    //如果有活动
                        if (!empty($activity[$key]['less'])) {
                            $list['list'][$key]['full_reduction'] = number_format($activity[$key]['less'], 2);
                            $list['list'][$key]['total_price'] -= $activity[$key]['less'];
                            $list['full_reduction'] += $activity[$key]['less'];
                            //计算奖励积分
                            foreach ($val['goods'] as $v) {
                                $tmpg = number_formats($activity[$key]['less'] * number_format($v['total_price'] / $val['total_price'], 2));
                                $list['list'][$key]['total_score'] -= ($tmpg * $v['score_ratio'] * 100);
                                $list['total_score'] -= ($tmpg * $v['score_ratio'] * 100);
                            }
                        }
                        if (!empty($activity[$key]['gift'])) $list['list'][$key]['gift'] = $activity[$key]['gift'];
                        if (!empty($activity[$key]['express'])) $list['list'][$key]['free_express'] = 1;
                    }
                }
                $list['list'][$key]['coupon'] = $coupon;
            }
            $list['list'][$key]['express_type']	= $this->get_express_type($val['express_tpl_id']);
        }

        if($list){
            $list['allMoney']   -= $list['full_reduction'];
            $list['total_price'] = $list['allMoney'];
            //兼容wap
            $tmp = array();
            foreach($list['list'] as $val){
                $tmp[] = $val;
            }
            $list['list'] = $tmp;
            $this->apiReturn(1,array('data'=>$list));
        }else{
            //购物车为空
            $this->apiReturn(3);
        }
    }

    /**
    * 根据商品取某商家支持的快递方式
    * @param array $orders_goods 购物车中已选中待付款的商品
    */
    public function get_express_type($express_tpl_id){
    	//取所有运费模板
    	$do=M('express_tpl');
    	$rs=$do->where(['id' => $express_tpl_id])->field('is_express,is_ems')->find();

        if($rs['is_express']==1) {
            $express_type[]=array(
                'name'	=>'快递',
                'value'	=>1
            );
        }
        if($rs['is_ems']==1) {
            $express_type[]=array(
                'name'	=>'EMS',
                'value'	=>2
            );
        }

    	//当全部为包邮模板时
    	if(empty($express_type)){
    		$express_type[]=array(
    				'name'	=>'快递',
    				'value'	=>1
    			);    		
    	}

    	return $express_type;
    }

    /**
    * 创建订单
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['address_id']收货地址ID
    */
    public function create_orders(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //取发货方式字段加入必签，字段名为 express_type_卖家ID
        $add_field=array();
        foreach($_POST as $key=>$val){
            if(strstr($key,'express_type_')) $add_field[]=$key;
        }

        //必传参数检查
        $this->need_param=array('openid','address_id','sign');
        $this->need_param=array_merge($this->need_param,$add_field);
        $this->_need_param();
        $this->_check_sign();

        //检查购物车中是否有选中要支付的商品
        $do=M('cart');
        if($do->where(array('uid'=>$this->uid,'is_select'=>1))->count()<1) $this->apiReturn(175); 	//购物车中无选中要进行支付的商品

        $do=M('shopping_address');
        if(!$address=$do->where(array('id'=>I('post.address_id'),'uid' => $this->uid))->field('atime,etime,ip',true)->find()) {
            //收货地址不存在
            $this->apiReturn(177);
        }

        //要创建的订单分组数量
        $list = M('cart')->where(['uid'=>$this->uid,'is_select'=>1])->field('uid,shop_id,seller_id,express_tpl_id')->group('express_tpl_id')->select();

        $o_no       = $this->create_orderno('OG',$this->uid);  //订单号
        $pay_price  = 0;    //合并付款金额
        $goods_num  = 0;    //合并商品数量
        $score      = 0;    //合并赠送积分

        $do->startTrans();

        //创建合并订单

        $a_data=array();
        $a_data['uid']        =$this->uid;
        $a_data['o_no']       =$o_no;
        $a_data['status']		=1;
        $a_data['province']   =$address['province'];
        $a_data['city']       =$address['city'];
        $a_data['district']   =$address['district'];
        $a_data['town']       =$address['town'] ? $address['town'] : 0;
        $a_data['street']     =$address['street'];
        $a_data['linkname']   =$address['linkname'];
        $a_data['mobile']     =$address['mobile'];
        $a_data['tel']        =$address['tel'];
        $a_data['postcode']   =$address['postcode'];
        $a_data['shop_num']   =count($list);
        $a_data['terminal']   =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP

        if(!$this->sw[] = D('Common/Orders')->create($a_data)){
            $msg = D('Common/Orders')->getError();
            goto error;
        }

        if(!$this->sw[] = $oid = D('Common/Orders')->add()){
            $msg = '创建合并订单失败！';
            goto error;
        }

        foreach($list as $key => $val){
            //print_r($val);
            //检查要购买的商品库存是否正常
            $goods=$this->check_goods($this->uid,$val['seller_id'],$val['express_tpl_id']);

            if($goods['error']>0) {
                //购物车中存在着异常商品记录！
                $msg=C('error_code')[178];
                goto error;
            }

            if($goods['total_price']<0.1) {
                //订单商品金额必须大于0.1元
                $msg=C('error_code')[179];
                goto error;
            }
            //是否为代购订单
            $is_daigou = 0;

            //计算运费
            $express_price = $this->_express_price($this->uid,$val['seller_id'],I('post.address_id'),I('post.express_type_'.$val['express_tpl_id']),$val['express_tpl_id']);
            $shop = M('shop')->where(['id' => $val['shop_id']])->field('id,inventory_type')->find();

            //创建商家订单
            $data=array();
            $data['o_no']           	=$o_no;
            $data['o_id']           	=$oid;
            $data['s_no']           	=$this->create_orderno('DD',$this->uid);
            $data['status']				=1;
            $data['inventory_type'] 	=$shop['inventory_type'];
            $data['shop_id']        	=$val['shop_id'];
            $data['uid']            	=$this->uid;
            $data['seller_id']      	=$val['seller_id'];
            $data['goods_price']        =$goods['total_price'];
            $data['goods_price_edit']   =$data['goods_price'];
            $data['express_type']		=I('post.express_type_'.$val['express_tpl_id']);
            $data['express_price']  	=$express_price;
            $data['express_price_edit'] =$data['express_price'];
            $data['remark']             =I('post.remark_'.$val['express_tpl_id']);
            $data['goods_num']          =$goods['goods_num'];
            $data['score']              =$goods['total_score']; //运费不赠送积分
            $data['terminal']           =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP
            $data['coupon_price']       =0;
            $data['next_time']          = date('Y-m-d H:i:s',time() + C('cfg.orders')['add']);   //过了这个时间未付款将关闭订单

            //检查优惠券，官方活动不参与优惠
            $isCoupon   =   0;
            $coupon_type=   0; //是否使用了官方优惠券
            if(I('post.coupon_'.$val['express_tpl_id']) && $goods['is_officialactivity'] == 0){
                $coupon=M('coupon')->lock(true)->where(array(
                    'id'        =>I('post.coupon_'.$val['express_tpl_id']),
                    'uid'       =>$this->uid,
                    'is_use'    =>0,
                    'status'    =>1,
                    'sday'      =>array('elt',date('Y-m-d')),
                    'eday'      =>array('egt',date('Y-m-d')),
                    'min_price' =>array('elt',round($data['goods_price_edit'], 2))
                ))->field('id,price,b_id,use_type,type')->find();

                if(!$this->sw[] = $coupon){
                    //优惠券不存在或已被使用
                    $msg=C('error_code')[191];
                    goto error;
                }

                if($coupon['type'] == 2) $coupon_type++;    //使用了官方优惠券

                if(!$this->sw[] = M('coupon_batch')->where(['id' => $coupon['b_id']])->setInc('use_num', 1)){  //使用+1
                    //优惠券更新失败！
                    $msg=C('error_code')[192];
                    goto error;
                }

                if(!$this->sw[] = M('coupon')->where(array('id'=>$coupon['id']))->save(array('is_use'=>1,'orders_no' => $data['s_no'],'use_time'=>date('Y-m-d H:i:s'),'orders_id'=>$oid))){
                    //优惠券更新失败！
                    $msg=C('error_code')[192];
                    goto error;
                }

                $data['coupon_price']       =$coupon['price'];
                $data['coupon_id']          =$coupon['id'];
                $data['goods_price_edit']  -=$coupon['price'];
                $isCoupon                   =$data['coupon_price'];
                //writeLog($isCoupon);
            }
            $data['total_price']        = $data['goods_price_edit']+$data['express_price_edit'];
            $data['pay_price']          = $data['total_price'];

            //常规活动
            if($goods['is_officialactivity'] == 0) {
                $activity = Activity::participate($data, $isCoupon);
                if ($activity) {
                    $data['express_price_edit'] = $activity['express_price_edit'];   //优惠后的邮费
                    $data['goods_price_edit'] = $activity['goods_price_edit'];           //优惠后的商品金额
                    $data['pay_price'] = $activity['goods_price_edit'] + $data['express_price_edit']; //需要支付的金额
                    $data['score'] = $activity['score'];                 //赠送积分
                    $data['activity_id'] = $activity['ids'];                   //参与的活动ID
                    $data['coupon_percentage'] = $activity['coupon_percentage'];     //优惠百分点；
                }
            }


            $data['money']   = $data['pay_price'];
            $goods_num      += $data['goods_num'];
            //$score      +=$data['score'];
            $pay_price      += $data['pay_price'];

            if(!$this->sw[] = D('Common/OrdersShop')->create($data)){
                $msg=D('Common/OrdersShop')->getError();
                goto error;
            }

            if(!$this->sw[] = D('Common/OrdersShop')->add()){
                $msg = '创建订单失败！';
                goto error;
            }
            $s_id=D('Common/OrdersShop')->getLastInsID();

            //订单logs
            $logs_data=array(
                'o_id'		=>$oid,
                'o_no'		=>$o_no,
                's_id'		=>$s_id,
                's_no'		=>$data['s_no'],
                'status'	=>1,
                'remark'	=>'创建订单'
            );

            if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
                $msg=D('Common/OrdersLogs')->getError();
                goto error;
            }
            if(!$this->sw[] = D('Common/OrdersLogs')->add()){
                $msg = '写入订单日志失败！';
                goto error;
            }

            //商品移至已订购的宝表中
            $ordersScore = 0;
            $vPrice[$key]= 0;
            foreach($goods['goods'] as $k => $v){
                $v['s_id']              =$s_id;
                $v['s_no']              =$data['s_no'];
                $v['o_no']              =$data['o_no'];
                $v['o_id']              =$oid;
                $v['goods_service_days']=getGoodsServiceDays($v['goods_id']);//商品售后天数
                $v['is_can_refund']     = $coupon_type > 0 ? 0 : 1; //使用官方优惠券时订单不允许退款

                //常规活动满减
                if ((!empty($activity) && ($activity['coupon_price'] > 0)) || $isCoupon > 0 && $goods['is_officialactivity'] == 0) { //如果有参与活动并且参与了满减活动则更改当前订单金额及积分
                    if ($activity['coupon_price'] > 0) {
                        $coupon_price       =   round(round($v['total_price'] / $data['goods_price'], 2) * $activity['coupon_price'], 2);   //活动
                    } elseif ($isCoupon > 0) {
                        $coupon_price       =   round(round($v['total_price'] / $data['goods_price'], 2) * $isCoupon, 2);  //优惠券
                    }
                    $v['total_price_edit']  =   round($v['total_price'] - $coupon_price, 2);
                    $v['score']             =   $v['score'] - ($v['score_ratio'] * $coupon_price * 100);
                    $ordersScore           +=   $v['score'];
                } else {    //未满减的情况下
                    $ordersScore           +=   $v['score'];
                    $v['total_price_edit']  =   $v['total_price'];
                }
                //平摊优惠金额
                $vPrice[$key] +=  $v['total_price_edit'];
                if ($k == count($goods['goods']) - 1) {
                    if (round($vPrice[$key], 2) < round($data['money'] - $data['express_price_edit'], 2)) { //如果使用活动后的金额 小于订单总金额，则需要加上一份
                        if ($k == count($goods['goods']) - 1) {
                            $v['total_price_edit']  +=  round(($data['money'] - $data['express_price_edit'] - $vPrice[$key]), 2);   //加上不够的钱
                            $v['score']             +=  (round(($data['money'] - $data['express_price_edit'] - $vPrice[$key]), 2) * $v['score_ratio']) * 100;
                            $ordersScore            +=  (round(($data['money'] - $data['express_price_edit'] - $vPrice[$key]), 2) * $v['score_ratio']) * 100;
                        }
                    } elseif (round($vPrice[$key], 2) > round($data['money'] - $data['express_price_edit'], 2)) {//如果使用活动后的金额 大于订单总金额，则需要减去一份
                        if ($k == count($goods['goods']) - 1) {
                            $v['total_price_edit']  -=  round(($vPrice[$key] - ($data['money'] - $data['express_price_edit'])), 2);   //减去多余的钱
                            $v['score']             -=  (round(($vPrice[$key] - ($data['money'] - $data['express_price_edit'])), 2) * $v['score_ratio']) * 100;
                            $ordersScore            -=  (round(($vPrice[$key] - ($data['money'] - $data['express_price_edit'])), 2) * $v['score_ratio']) * 100;
                        }
                    }
                }
                //如果当前商品为代购商品
                if($v['is_daigou'] > 0) {
                    $is_daigou++;
                    $dg = new Daigou();
                    $res= $dg->compute(['daigou_ratio' => $v['daigou_ratio'], 'total_price' => $v['total_price']]);
                    //$res['pay_price']       =   round($data['pay_price']+$res['daigou_cost'], 2);

                    $data['daigou_ratio']    = $res['daigou_ratio'];
                    $data['daigou_cost']    += $res['daigou_cost'];
                    $data['money']          += $res['daigou_cost'];
                    $data['pay_price']       = $data['money'];
                    $pay_price              += $data['daigou_cost'];
                }
                unset($v['id']);

                if(!$this->sw[] = D('Common/OrdersGoods')->create($v)){
                    $msg=D('Common/OrdersGoods')->getError();
                    goto error;
                }
                if(!$this->sw[] = D('Common/OrdersGoods')->add()){
                    $msg = '第'.$k.'个商品移入已订购商品表失败！';
                    goto error;
                }
            }


            $score += $ordersScore;

            /**
             * 2017-03-23 ERP小组通知要求订单赠送积分不得小于50积分
             */
            if($ordersScore > 0 && $ordersScore < C('cfg.orders')['min_score']){
                $msg = '创建订单失败，第'.$key.'个商家订单奖励积分小于'.C('cfg.orders')['min_score'].'分！';
                goto error;
            }

            $tmp = array();
            $tmp['score'] = $ordersScore;
            //更新订单代购信息
            if($is_daigou > 0){
                $tmp['daigou_ratio']    = $data['daigou_ratio'];
                $tmp['daigou_cost']     = $data['daigou_cost'];
                $tmp['money']           = $data['money'];
                $tmp['pay_price']       = $data['pay_price'];
            }
            if($this->sw[] = false === M('orders_shop')->where(['id' => $s_id])->save($tmp)){
                $msg = '更新积分失败！';
                goto error;
            }

        }//创建商家订单完成
        //删除购物车中商品
        if(!$this->sw[] = M('cart')->where(array('is_select'=>1,'uid'=>$this->uid))->delete()){
            //清除购物车中商品失败！
            $msg=C('error_code')[194];
            goto error;
        }

        if(!$this->sw[] = M('orders')->where(array('id'=>$oid))->save(array('pay_price'=>$pay_price,'goods_num'=>$goods_num,'score'=>$score))){
            //更新订单金额失败！
            $msg=C('error_code')[195];
            goto error;
        }


        //提交事务
        $do->commit();
        S(md5('cart_total_' . I('post.openid')),null);
        $this->apiReturn(1,array('data'=>array('o_id'=>$oid,'o_no'=>$o_no)));

        error:
            $do->rollback();
            $this->apiReturn(4,'',1,'创建订单失败！'.$msg);


    }

    /**
     * 创建秒杀、0元购订单
     */
    public function create_activity_orders() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //取发货方式字段加入必签，字段名为 express_type_卖家ID
        $add_field=array();
        foreach($_POST as $key=>$val){
            if(strstr($key,'express_type_')) $add_field[]=$key;
        }

        //必传参数检查
        $this->need_param=array('openid','address_id','sign');
        $this->need_param=array_merge($this->need_param,$add_field);
        $this->_need_param();
        $this->_check_sign();

        //检查购物车中是否有选中要支付的商品
        $do=M('cart');
        if($do->where(array('uid'=>$this->uid,'is_select'=>1))->count()<1) $this->apiReturn(175); 	//购物车中无选中要进行支付的商品

        $do=M('shopping_address');
        if(!$address=$do->where(array('id'=>I('post.address_id'),'uid' => $this->uid))->field('atime,etime,ip',true)->find()) {
            //收货地址不存在
            $this->apiReturn(177);
        }

        //要创建的订单分组数量
        $list = M('cart')->where(['uid'=>$this->uid,'is_select'=>1])->field('uid,shop_id,seller_id,express_tpl_id')->group('express_tpl_id')->select();

        $o_no       = $this->create_orderno('OG',$this->uid);  //订单号
        $pay_price  = 0;    //合并付款金额
        $goods_num  = 0;    //合并商品数量
        $score      = 0;    //合并赠送积分

        $do->startTrans();

        //创建合并订单

        $a_data=array();
        $a_data['uid']        =$this->uid;
        $a_data['o_no']       =$o_no;
        $a_data['status']		=1;
        $a_data['province']   =$address['province'];
        $a_data['city']       =$address['city'];
        $a_data['district']   =$address['district'];
        $a_data['town']       =$address['town'] ? $address['town'] : 0;
        $a_data['street']     =$address['street'];
        $a_data['linkname']   =$address['linkname'];
        $a_data['mobile']     =$address['mobile'];
        $a_data['tel']        =$address['tel'];
        $a_data['postcode']   =$address['postcode'];
        $a_data['shop_num']   =count($list);
        $a_data['terminal']   =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP

        if(!$this->sw[] = D('Common/Orders')->create($a_data)){
            $msg = D('Common/Orders')->getError();
            goto error;
        }

        if(!$this->sw[] = $oid = D('Common/Orders')->add()){
            $msg = '创建合并订单失败！';
            goto error;
        }

        foreach($list as $key => $val) {
            //print_r($val);
            //检查要购买的商品库存是否正常
            $goods = $this->check_goods($this->uid, $val['seller_id'], $val['express_tpl_id']);

            if ($goods['error'] > 0) {
                //购物车中存在着异常商品记录！
                $msg = C('error_code')[178];
                goto error;
            }

            if ($goods['total_price'] < 0.1) {
                //订单商品金额必须大于0.1元
                $msg = C('error_code')[179];
                goto error;
            }

            //是否为代购订单
            //$is_daigou = 0;

            //计算运费
            $express_price = $this->_express_price($this->uid, $val['seller_id'], I('post.address_id'), I('post.express_type_' . $val['express_tpl_id']), $val['express_tpl_id']);
            $shop = M('shop')->where(['id' => $val['shop_id']])->field('id,inventory_type')->find();

            //创建商家订单
            $data                       = array();
            $data['o_no']               = $o_no;
            $data['o_id']               = $oid;
            $data['s_no']               = $this->create_orderno('DD',$this->uid);
            $data['status']             = 1;
            $data['inventory_type']     = $shop['inventory_type'];
            $data['shop_id']            = $val['shop_id'];
            $data['uid']                = $this->uid;
            $data['seller_id']          = $val['seller_id'];
            $data['goods_price']        = $goods['total_price'];
            $data['goods_price_edit']   = $data['goods_price'];
            $data['express_type']       = I('post.express_type_' . $val['express_tpl_id']);
            $data['express_price']      = $express_price;
            $data['express_price_edit'] = $data['express_price'];
            $data['remark']             = I('post.remark_' . $val['express_tpl_id']);
            $data['goods_num']          = $goods['goods_num'];
            $data['score']              = $goods['total_score']; //运费不赠送积分
            $data['terminal']           = I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP
            $data['coupon_price']       = 0;

            $data['total_price']        = $data['goods_price_edit']+$data['express_price_edit']-$data['coupon_price'];
            $data['money']              = $data['total_price'];
            $data['pay_price']          = $data['money'];
            $data['next_time']          = date('Y-m-d H:i:s',time() + C('cfg.orders')['add']);   //过了这个时间未付款将关闭订单

            $pay_price                 += $data['pay_price'];
            $sGoodsEditPrice            = $data['goods_price_edit'];
            $goods_num                 += $data['goods_num'];
            //$score                     += $data['score'];

            if(!$this->sw[] = D('Common/OrdersShop')->create($data)){
                $msg=D('Common/OrdersShop')->getError();
                goto error;
            }

            if(!$this->sw[] = D('Common/OrdersShop')->add()){
                $msg = '创建订单失败！';
                goto error;
            }
            $s_id   = D('Common/OrdersShop')->getLastInsID();

            //订单logs
            $logs_data  = array(
                'o_id'		=> $oid,
                'o_no'		=> $o_no,
                's_id'		=> $s_id,
                's_no'		=> $data['s_no'],
                'status'	=> 1,
                'remark'	=> '创建订单'
            );

            if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
                $msg=D('Common/OrdersLogs')->getError();
                goto error;
            }
            if(!$this->sw[] = D('Common/OrdersLogs')->add()){
                $msg = '写入订单日志失败！';
                goto error;
            }

            $ordersScore = 0;
            foreach($goods['goods'] as $k => $v) {
                $v['s_id'] = $s_id;
                $v['s_no'] = $data['s_no'];
                $v['o_no'] = $data['o_no'];
                $v['o_id'] = $oid;
                $v['goods_service_days'] = getGoodsServiceDays($v['goods_id']);//商品售后天数
                $data['count_goods_num']=$v['num'];


                //常规活动满减
                //临时判断
                $isSpike = M('activity_participate')->where(['activity_id' => ['in', '626,584,574,572,570,313'], 'status' => ['lt', 2], 'uid' => $this->uid])->getField('id');
                if ($isSpike) {
                    $ordersScore               +=   $v['score'];
                    $v['total_price_edit']      =   $v['total_price'];
                } else {
                    $activity  =   Activity::getSpikeAndRestriction($v['shop_id'], $v['goods_id'], $data);
                    if ($activity) {
                        if ($v['num'] >= $activity['max_num'] && $activity['max_num'] > 0) {
                            $num                    =   $v['num'] - $activity['max_num'];
                            $v['total_price_edit']  =   ($num * $v['price']) + ($activity['max_num'] * $activity['full_money']);
                        } else {
                            $v['total_price_edit']  =   $v['num'] * $activity['full_money'];
                        }
                        $sActivityId                =   $activity['id'];
                        $v['score']                 =   ($v['total_price_edit'] * $v['score_ratio']) * 100;
                        $ordersScore               +=   $v['score'];
                        $sExpress_price_edit        =   $express_price;
                        $pay_price                  =   ($pay_price - $v['total_price']) + $v['total_price_edit'];
                        $sPay_price                 =   $pay_price;
                        //$v['total_price_edit']     +=   $express_price;
                        $sGoodsEditPrice            =   ($sGoodsEditPrice - $v['total_price']) + $v['total_price_edit'];

                    } else {    //没有活动的情况下
                        $ordersScore               +=   $v['score'];
                        $v['total_price_edit']      =   $v['total_price'];
                    }
                }

                unset($v['id']);
                if(!$this->sw[] = D('Common/OrdersGoods')->create($v)){
                    $msg = D('Common/OrdersGoods')->getError();
                    goto error;
                }
                if (!$this->sw[] = D('Common/OrdersGoods')->add()) {
                    $msg = '订单商品添加失败!';
                    goto error;
                }
            }
            if (!empty($activity)) { //如果有参与活动并且参与了满减活动则更改当前订单总积分
                $sData  =   ['score' => $ordersScore];
                if ($sPay_price) {
                    $sData['pay_price']         =   $sPay_price;
                    $sData['goods_price_edit']  =   $sGoodsEditPrice;
                    $sData['activity_id']       =   $sActivityId;
                }
                if(M('orders_shop')->where(['id' => $s_id])->save($sData) === false) {
                    $msg = '更新商家订单失败！';
                    goto error;
                }
            }
            $score +=  $ordersScore;   //运算后的赠送分总数

            /**
             * 2017-03-23 ERP小组通知要求订单赠送积分不得小于50积分
             */
            if($ordersScore > 0 && $ordersScore < C('cfg.orders')['min_score']){
                $msg = '创建订单失败，第'.$key.'个商家订单奖励积分小于'.C('cfg.orders')['min_score'].'分！';
                goto error;
            }
        }

        //删除购物车中商品
        if(!$this->sw[] = M('cart')->where(array('is_select'=>1,'uid'=>$this->uid))->delete()){
            //清除购物车中商品失败！
            $msg=C('error_code')[194];
            goto error;
        }

        if(!$this->sw[] = M('orders')->where(array('id'=>$oid))->save(array('pay_price'=>$pay_price,'goods_num'=>$goods_num,'score'=>$score))){
            //更新订单金额失败！
            $msg=C('error_code')[195];
            goto error;
        }


        //提交事务
        $do->commit();
        S(md5('cart_total_' . I('post.openid')),null);
        $this->apiReturn(1,array('data'=>array('o_id'=>$oid,'o_no'=>$o_no)));

        error:
        $do->rollback();
        $this->apiReturn(4,'',1,'创建订单失败！'.$msg);
    }
    
    
    /**
    * 检查要购买的商品库存是否正常
    * @param int $uid       买家ID
    * @param int $seller_id 卖家iD
    */
    public function check_goods($uid,$seller_id,$express_tpl_id){
        $do=D('Common/CartAttrListRelation');
        $goods=$do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1,'seller_id'=>$seller_id,'express_tpl_id' => $express_tpl_id))->field('etime,ip',true)->order('price asc')->select();
        //var_dump($list);
        $result['error']        = 0; //不正常的记录数量
        $result['goods_num']    = 0;
        if($goods){
            //返回数组格式化处理
            $result['is_officialactivity'] = 0;
            foreach($goods as $key=>$val){
                $goods[$key]['images']      =$val['attr_list']['images'];
                $goods[$key]['goods_name']  =$val['goods']['goods_name'];
                $goods[$key]['status']      =1;
                $goods[$key]['status_name'] ='正常';
                $goods[$key]['detail_url']='/Goods/view/id/'.$val['attr_list_id'].'.html';
                $goods[$key]['is_daigou']   =$val['goods']['is_daigou'];
                $goods[$key]['daigou_ratio']=$val['goods']['daigou_ratio'];
                if($val['attr_id']!=$val['attr_list']['attr_id']){
                    $goods[$key]['status']      =2;
                    $goods[$key]['status_name'] ='商家已变更库存属性！';
                    $result['error']++;
                }
                if($val['num']>$val['attr_list']['num']){
                    $goods[$key]['status']      =3;
                    $goods[$key]['status_name'] ='库存不足，最多只能订购'.$val['attr_list']['num'].'件！';
                    $result['error']++;                
                }

                if($val['price']!=$val['attr_list']['price'] || $val['weight']!=$val['attr_list']['weight'] || $val['score_ratio'] != $val['goods']['score_ratio'] || $val['express_tpl_id'] != $val['goods']['express_tpl_id']){ //价格或重量是否有变更
                    $is_edit = true;
                    //是否参与官方活动
                    if($val['goods']['officialactivity_join_id'] > 0){
                        $officialactivity = M('officialactivity_join')->cache(false)->where(['id' => $val['goods']['officialactivity_join_id']])->field('day,time')->find();
                        $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
                        if($time_dif > 0 || $time_dif < -86400) { //活动未开始或已过期

                        }else {
                            $is_edit = false;
                            $result['is_officialactivity']++;
                        }

                    }

                    if($is_edit == true) {
                        $do=D('Common/Cart');
                        $goods[$key]['price']           =$val['attr_list']['price'];
                        $goods[$key]['weight']          =$val['attr_list']['weight'];
                        $goods[$key]['total_price']     =$goods[$key]['num']* $goods[$key]['price'];
                        $goods[$key]['total_weight']    =$goods[$key]['num']* $goods[$key]['weight'];
                        $goods[$key]['score_ratio']     =$val['goods']['score_ratio'];
                        $goods[$key]['score']           =$goods[$key]['score_ratio'] * $goods[$key]['total_price'] * 100;
                        $goods[$key]['express_tpl_id']  =$val['goods']['express_tpl_id'];
                        if($do->create($goods[$key])) $do->save();
                    }

                }

                $result['total_weight'] +=$goods[$key]['total_weight'];
                $result['total_price']  +=$goods[$key]['total_price'];
                $result['total_score']  +=$goods[$key]['score'];
                $result['goods_num']    +=$goods[$key]['num'];

                unset($goods[$key]['attr_list']);
                unset($goods[$key]['goods']);
                unset($goods[$key]['atime']);
            }

            //var_dump($goods);
            $result['goods']=$goods;
            return $result;
        }else{
            //没有商品记录
            return array('code'=>3);
        }   
    }

    /**
    * 计算购物车中某一个商家商品运费
    * @param string $_POST['openid'] 		用户openid
    * @param int 	$_POST['address_id']	收货地址ID
    * @param int 	$_POST['seller_id']		卖家ID
	* @param int 	$_POST['express_type']	发货方式,1=快递 ,2=Ems
    */
    public function express_price(){
        //频繁请求限制,间隔300毫秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','address_id','seller_id','express_type','express_tpl_id','sign');
        $this->_need_param();
        $this->_check_sign();

        //发货方式错误
        if(!in_array(I('post.express_type'), array(1,2))) $this->apiReturn(180);

        //$this->apiReturn(1,array('data'=>['express_price'=>rand(8,20)]));

        $express_price=$this->_express_price($this->uid,I('post.seller_id'),I('post.address_id'),I('post.express_type'),I('post.express_tpl_id'));
        $this->apiReturn(1,['data' => ['express_price' => $express_price]]);
    }

    /**
    * @param int 	$uid 买家ID
    * @param int 	$uid 卖家ID
    * @param int  	$address_id 收货地址ID
    * @param int 	$express_type 发货方式,1=快递 ,2=Ems
    * @param int    $express_tpl_id 运费模板ID
    */
    public function _express_price($uid,$seller_id,$address_id,$express_type,$express_tpl_id){
        //统计
        $do=M('cart');
        $rs=$do->where(array('uid'=>$uid,'is_select'=>1,'seller_id'=>$seller_id,'express_tpl_id' => $express_tpl_id))->field('sum(num) as num,sum(total_weight) as total_weight')->find();

        if($rs['num']<1) $this->apiReturn(175);  //购物车中无选中要进行支付的商品 
        

        $do=M('shopping_address');
        if(!$city=$do->where(array('id' => $address_id,'uid' => $uid))->getField('city')) {
            //收货地址不存在
            return array('code'=>177); 
        }

        //取运费模板
        $tpl = M('express_tpl')->where(['id' => $express_tpl_id])->field('atime,etime,ip',true)->find();
        $express_price = 0;
        if($tpl['is_free'] == 1) return $express_price;  //包邮



        $total = $tpl['unit'] == 1 ? $rs['num'] : $rs['total_weight'];
        //print_r($tpl);echo '<br>';
        //print_r($total);

        if($express_type == 2){ //EMS默认运费
            $result['first'] = $tpl['ems_default_first_price'];
            if ($total > $tpl['ems_default_first_unit']) {
                $result['next'] = ceil(($total - $tpl['ems_default_first_unit']) / $tpl['ems_default_next_unit']) * $tpl['ems_default_next_price'];
            }

        }else { //快递默认运费
            $result['first'] = $tpl['express_default_first_price'];
            if ($total > $tpl['express_default_first_unit']) {
                $result['next'] = ceil(($total - $tpl['express_default_first_unit']) / $tpl['express_default_next_unit']) * $tpl['express_default_next_price'];
            }
        }

        //print_r($result);

        //取地区自定义模板
        $area = M('express_area')->where(['tpl_id' => $express_tpl_id,'type' => $express_type])->field('atime,etime,ip',true)->select();

        //自定义地区运费
        if($area) {
            foreach ($area as $val) {
                $city_ids = explode(',', $val['city_ids']);
                if (in_array($city, $city_ids)) {
                    //print_r($val);
                    //print_r($total);
                    $result = array();
                    $result['first'] = $val['first_price'];
                    if ($total > $val['first_unit']) {
                        $result['next'] = ceil(($total - $val['first_unit']) / $val['next_unit']) * $val['next_price'];
                    }
                    //print_r($result);
                    break;
                }
            }
        }

        
        $express_price=$result['first'] + $result['next'];

        //dump($express_price);
        return $express_price;
    }    

    /**
    * @param array 	$tpl 运费模板
    * @param array 	$goods 待计运费的商品
    * @param int 	$city 	城市ID
    * @param int 	$express_type 发货方式,1=快递 ,2=Ems
    */
    public function _express_goods_price($tpl,$goods,$city,$express_type){
        if($express_type==1 && $tpl['is_express']==1){	//快递默认运费
	        $logsic=array(
	                'unit'          =>$tpl['unit'],
	                'first_unit'    =>$tpl['express_default_first_unit'],
	                'first_price'   =>$tpl['express_default_first_price'],
	                'next_unit'     =>$tpl['express_default_next_unit'],
	                'next_price'    =>$tpl['express_default_next_price'],
	            );
	        $express_type=1;
	    }else{	//EMS默认运费，如果选择的发货方式为快递是，运费模板中没有启用快递将默认按EMS计算
	        $logsic=array(
	                'unit'          =>$tpl['unit'],
	                'first_unit'    =>$tpl['ems_default_first_unit'],
	                'first_price'   =>$tpl['ems_default_first_price'],
	                'next_unit'     =>$tpl['ems_default_next_unit'],
	                'next_price'    =>$tpl['ems_default_next_price'],
	            );
	        $express_type=2;
	    }

        //根据地区查找运费配置
        if($tpl['express_area']){
            foreach($tpl['express_area'] as $val){
            	if($val['type']==$express_type){
	                $val['city_ids']=explode(',',$val['city_ids']);
	                if(in_array($city, $val['city_ids'])){
	                    $logsic['first_unit']   =$val['first_unit'];
	                    $logsic['first_price']  =$val['first_price'];
	                    $logsic['next_unit']    =$val['next_unit'];
	                    $logsic['next_price']   =$val['next_price'];
	                    //dump($city);
	                    break;
	                }
            	}
            }
        }

        //dump($logsic);

        //dump($goods);

        $price['first']=$logsic['first_price'];	//首重/件费用

        //续重/件费用
        if($logsic['unit']==2){ //计重方式
            if($goods['total_weight']>$logsic['first_unit']){
                $price['next'] = ceil(($goods['total_weight']-$logsic['first_unit'])/$logsic['next_unit']) * $logsic['next_price'];
                
                //将首重也纳为续重
                $price['next2'] = ceil($goods['total_weight']/$logsic['next_unit']) * $logsic['next_price'];
            }
        }else{  //计件方式
            if($goods['num']>$logsic['first_unit']){
                $price['next'] = ceil(($goods['num']-$logsic['first_unit'])/$logsic['next_unit']) * $logsic['next_price'];

                //将首件也纳为续件
                $price['next2'] = ceil($goods['num']/$logsic['next_unit']) * $logsic['next_price'];           
            }
        }

        return $price; 	
    }

    /**
    * @param int $uid　  买家ID
    * @param int $seller_id 卖家ID
    * @param int $express_id 运费模板ID
    * @param int $address_id 收货地址ID
    * 方法已作废
    */
    public function _express_price_bak($uid,$seller_id,$express_id,$address_id){
        //统计
        $do=M('cart');
        $rs=$do->where(array('uid'=>$uid,'is_select'=>1,'seller_id'=>$seller_id))->field('sum(num) as num,sum(total_weight) as total_weight')->find();

        if($rs['num']<1) return array('code'=>175);  //购物车中无选中要进行支付的商品 
        
        $do=D('Common/ExpressRelation');
        if(!$express=$do->relation(true)->cache(true,C('CACHE_LEVEL.XXS'))->where(array('id'=>$express_id))->field('atime,etime,ip,remark',ture)->find()) {
            //运费模板不存在
            return array('code'=>176); 
        }

        $do=M('shopping_address');
        if(!$address=$do->cache(true,C('CACHE_LEVEL.XXS'))->where(array('id'=>$address_id))->field('atime,etime,ip',true)->find()) {
            //收货地址不存在
            return array('code'=>177); 
        }

        //dump($express);
        //当前运费配置
        $logsic=array(
                'unit'          =>$express['unit'],
                'first_unit'    =>$express['first_unit'],
                'first_price'   =>$express['first_price'],
                'next_unit'     =>$express['next_unit'],
                'next_price'    =>$express['next_price'],
            );
        //根据地区查找运费配置
        if($express['express_area']){
            foreach($express['express_area'] as $val){
                $val['city_ids']=explode(',',$val['city_ids']);
                if(in_array($address['city'], $val['city_ids'])){
                    $logsic['first_unit']   =$val['first_unit'];
                    $logsic['first_price']  =$val['first_price'];
                    $logsic['next_unit']    =$val['next_unit'];
                    $logsic['next_price']   =$val['next_price'];
                    break;
                }
            }
        }

        //dump($logsic);

        $express_price=$logsic['first_price'];
        if($logsic['unit']=='Kg'){ //计重方式
            if($rs['total_weight']>$logsic['first_unit']){
                $express_price += ceil(($rs['total_weight']-$logsic['first_unit'])/$logsic['next_unit']) * $logsic['next_price'];
            }
        }else{  //计件方式
            if($rs['num']>$logsic['first_unit']){
                $express_price += ceil(($rs['num']-$logsic['first_unit'])/$logsic['next_unit']) * $logsic['next_price'];
            }
        }

        return array('code'=>1,'data'=>array('price'=>$express_price,'address'=>$address,'express'=>$express));
    }


    /**
    * 统计购物车中商品数量
    * @param string $_POST['openid']  用户openid
    */
    public function cart_total(){
         //频繁请求限制,间隔300毫秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $cache_name=md5('cart_total_' . I('post.openid'));
        $res=S($cache_name);

        if(empty($res)){
            $res=M()->query('select count(*) as style_num,sum(num) as num,sum(total_weight) as weight,sum(score) as score,sum(is_select) as selected,sum(total_price) as price from '.C('DB_PREFIX').'cart where uid='.$this->uid . ' AND is_display = 0');

            $res=$res[0];
            foreach($res as $key=>$val){
                if(is_null($val)) $res[$key]=0;
            }

            S($cache_name,$res);
        }

        $this->apiReturn(1,['data' => $res]);
    }
    
    
    /**
     * 再次购买
     * @param string $_POST['openid']   用户openid
     * @param string $_POST['ids']      商品库存ID,多个用逗号隔开
     */
    public function copyOrders() {
        //频繁请求限制,间隔2秒
        //$this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','ids','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $type=I('post.type')?I('post.type'):1;  //1为增加数量,2为减少数量,3设定数量
        if(I('post.atonce')==1) $type=3;
        
        //检查库存是否正常
        $do=D('Common/GoodsAttrListUpRelation');
        $map    =   [
            'id'    =>  ['in', I('post.ids')],
        ];
        $attr=$do->relation(true)->where($map)->field('atime,etime,ip',true)->select();
        $flag   =   false;
        $model  =   D('Common/Cart');
        $model->startTrans();
        $msg    =   '未往下操作';
        foreach ($attr as &$val) {
            if ($val['goods']['status'] != 1) {
                unset($val);
            } else if ($this->uid == $val['goods']['seller_id']) {
                unset($val);
            } else {
                if(M('cart')->where(array('uid'=>$this->uid,'attr_list_id'=>$val['id']))->field('id,num')->find()) {
                    $msg = '购物车已存在此商品';
                    goto error;
                    unset($val);
                } else {
                    $flag   =   true;
                    $data['uid']            =$this->uid;
                    $data['goods_id']       =$val['goods_id'];
                    $data['seller_id']      =$val['goods']['seller_id'];
                    $data['shop_id']        =$val['goods']['shop_id'];
                    $data['attr_list_id']   =$val['id'];
                    $data['attr_id']        =$val['attr_id'];
                    $data['attr_name']      =$val['attr_name'];
                    $data['price']          =$val['price'];
                    $data['num']            =1;
                    $data['weight']         =$val['weight'];
                    $data['total_weight']   =$val['weight'];
                    $data['total_price']    =$val['price'];
                    $data['total_price_edit']=$data['total_price'];
                    $data['score_ratio']    =$val['goods']['score_ratio'];
                    $data['score']          =$data['score_ratio'] * $data['total_price_edit'] * 100;
                    $data['express_tpl_id'] =$val['goods']['express_tpl_id'];
                    if (!empty($val['goods']['activity_id'])){
                        $data['activity_id']=(new Activity($val['goods']['activity_id'], $val['goods_id'], 1))->getActivitys();    //活动处理
                    }
                    
                    if (!$model->create($data)) {
                        $msg    =   $model->getError();
                        goto error;
                        break;
                    }
                    if (!$model->add()) {
                        $msg    =  '加入购物车失败';
                        goto error;
                        break;
                    }
                }
            }
        }
        
        if ($flag == false) {
            $msg    =   '没有可加入购物车的商品';
            goto error;
        }
        
        //清除购物车统计缓存，避免统计错误
        S(md5('cart_total_' . I('post.openid')),null);
        unset($val, $data, $attr);
        //取当前购物车是否已添加商品
        $model->commit();
        $this->apiReturn(1,[]);
        error:
            $model->rollback();
            $this->apiReturn(4,'',1,$msg);
    }
}