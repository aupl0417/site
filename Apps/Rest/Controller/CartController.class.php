<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 购物车
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
use Common\Builder\Activity;
use Common\Builder\Daigou;
//use Common\Builder\Queue;
class CartController extends CommonController {
	protected $action_logs = array('add','delete','create_orders','create_activity_orders');
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
     * 搭配商品加入购物车
     */
    public function groupAdd() {
        //必传参数检查
        $this->need_param=array('openid','ids','sign');
        $this->_need_param();
        $this->_check_sign();

        $ids = rtrim(I('post.ids'), ',');   //id

        //检查库存是否正常
        $do=D('Common/GoodsAttrListUpRelation');
        $attr=$do->relation(true)->where(array('id'=>['in', $ids]))->field('atime,etime,ip',true)->select();
        //writeLog($attr);
        $shop = M('shop')->where(['uid' => $attr[0]['seller_id']])->field('status')->find();
        //writeLog(M('shop')->getLastSql());
        if($shop['status'] != 1) $this->apiReturn(4,'',1,'店铺已暂停营业！');

        //取当前购物车是否已添加商品
        $do=M('cart');
        $rs=$do->where(array('uid'=>$this->uid,'attr_list_id'=>['in', $ids]))->field('id,num,attr_list_id')->select();
        $officialactivityModel = D('Common/OfficialactivityJoinUpRelation');
        $data = [];
        $do=D('Common/Cart');
        $do->startTrans();
        foreach ($attr as $k => $v) {
            if (!$v) $this->apiReturn(3); //商品已下架
            if ($v['goods']['status'] != 1) $this->apiReturn(172); //商品已下架
            if($this->uid==$v['goods']['seller_id']) $this->apiReturn(173); //不能订购自己的商品
            //检查商品是否参与官方秒杀活动
            $goods = M('goods')->where(['id' => $v['goods_id'],'officialactivity_join_id' => ['gt',0]])->field('officialactivity_join_id,officialactivity_price')->find();
            if ($goods) {
                $officialactivity = $officialactivityModel->relation(true)->where(['id' => $goods['officialactivity_join_id']])->field('id,activity_id,day,time,price,num')->find();
                $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
                if($time_dif > 0) $this->apiReturn(170);  //秒杀活动还未开始！
                if($time_dif < -86400) $this->apiReturn(169);   //秒杀活动已结束！

                //是否超过限购数量
                $buy_num = M('orders_goods')->where(['uid' => $this->uid,'officialactivity_join_id' => $goods['officialactivity_join_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in (2,3,4,5,6,11))'])->sum('num');
                if($officialactivity['officialactivity']['max_buy'] < ($buy_num + I('post.num'))) $this->apiReturn(165,'',1,str_replace('{n}',$officialactivity['officialactivity']['max_buy'],C('error_code.165')));    //活动商品每个ID限购{n}件！


                $v['price']         = $officialactivity['price'];
                $v['is_display']    = 1;

                $data[$k]['officialactivity_id']  		= $officialactivity['activity_id'] ? $officialactivity['activity_id'] : 0;
                $data[$k]['officialactivity_join_id']  	= $officialactivity['id'] ? $officialactivity['id'] : 0;

            }
            if(1>$v['num']) $this->apiReturn(171); //库存不足
            $data[$k]['uid']            =$this->uid;
            $data[$k]['goods_id']       =$v['goods_id'];
            $data[$k]['seller_id']      =$v['goods']['seller_id'];
            $data[$k]['shop_id']        =$v['goods']['shop_id'];
            $data['category_id']        =$v['goods']['category_id'];
            $data[$k]['attr_list_id']   =$v['id'];
            $data[$k]['attr_id']        =$v['attr_id'];
            $data[$k]['attr_name']      =$v['attr_name'];
            $data[$k]['price']          =$v['price'];
            $data[$k]['num']            =1;
            $data[$k]['weight']         =$v['weight'];
            $data[$k]['total_weight']   =$v['weight'];
            $data[$k]['total_price']    =$v['price'];
            $data[$k]['total_price_edit']=$data[$k]['total_price'];
            $data[$k]['score_ratio']    =$v['goods']['score_ratio'];
            $data[$k]['score']          =$v['goods']['score_ratio'] * $data[$k]['total_price_edit'] * 100;
            $data[$k]['is_display']     =$v['is_display'] ? $v['is_display'] : $v['goods']['is_daigou'];
            $data[$k]['express_tpl_id'] =$v['goods']['express_tpl_id'];
            $data[$k]['is_select']      =1;
            foreach ($rs as $val) {
                if ($val['attr_list_id'] == $v['id']) {
                    $data[$k]['id'] = $val['id'];
                    break;
                }
            }

            if ($do->create($data[$k]) == false) $this->apiReturn(4,'',1,'操作失败！'.$do->getError());
            //writeLog($data[$k]);
            if ($data[$k]['id']) {
                if ($do->save($data[$k]) === false) {
                    $do->rollback();
                    $this->apiReturn(0); //编辑失败
                }
            } else {
                if ($do->add($data[$k]) == false) {
                    $do->rollback();
                    $this->apiReturn(0); //添加失败
                }
            }
        }
        $do->commit();
        S(md5('cart_total_' . I('post.openid')),null);  //删除购物车数量缓存
        $this->apiReturn(1);
    }


    /**
    * 加入购物车
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['attr_list_id']  商品库存ID
    * @param int    $_POST['num']       数量
    * @param int    $_POST['type']      1为增加数量,2为减少数量,3设定数量
    */
    public function add(){
         //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','attr_list_id','num','type','sign');
        $this->_need_param();
        $this->_check_sign();

        $type=I('post.type')?I('post.type'):1;  //1为增加数量,2为减少数量,3设定数量
        if(I('post.atonce')==1) $type=3;

        //检查库存是否正常
        $do=D('Common/GoodsAttrListUpRelation');
        $attr=$do->relation(true)->where(array('id'=>I('post.attr_list_id')))->field('atime,etime,ip',true)->find();
        //var_dump($attr);

        $shop = M('shop')->where(['uid' => $attr['goods']['seller_id']])->field('status')->find();
        if($shop['status'] != 1) $this->apiReturn(4,'',1,'店铺已暂停营业！');

        /**
         * 指定店铺不能加入购物车
         * by Lazycat
         */
        //if(in_array($attr['goods']['seller_id'],array(692355,691037)) && date('Y-m-d') > '2017-01-14' && date('Y-m-d') < '2017-02-07'){
            //$this->apiReturn(0,'',1,'由于春节前后部份快递公司已停止收件，本店将于2017年1月16至2月6日停止营业及发货');
        //}

        if(!$attr) $this->apiReturn(3); //找不到库存记录
        if($attr['goods']['status']!=1) $this->apiReturn(172); //商品已下架    
        if($this->uid==$attr['goods']['seller_id']) $this->apiReturn(173); //不能订购自己的商品

        //取当前购物车是否已添加商品
        $do=M('cart');
        $rs=$do->where(array('uid'=>$this->uid,'attr_list_id'=>I('post.attr_list_id')))->field('id,num')->find();

        //检查商品是否参与官方秒杀活动
        if($goods = M('goods')->where(['id' => $attr['goods_id'],'officialactivity_join_id' => ['gt',0]])->field('officialactivity_join_id,officialactivity_price')->find()) {
            $officialactivity = D('Common/OfficialactivityJoinUpRelation')->relation(true)->where(['id' => $goods['officialactivity_join_id']])->field('id,activity_id,day,time,price,num')->find();
            $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
            if($time_dif > 0) $this->apiReturn(170);  //秒杀活动还未开始！
            if($time_dif < -86400) $this->apiReturn(169);   //秒杀活动已结束！

            //是否超过限购数量
            $buy_num = M('orders_goods')->where(['uid' => $this->uid,'officialactivity_join_id' => $goods['officialactivity_join_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in (2,3,4,5,6,11))'])->sum('num');
            if($officialactivity['officialactivity']['max_buy'] < ($buy_num + I('post.num'))) $this->apiReturn(165,'',1,str_replace('{n}',$officialactivity['officialactivity']['max_buy'],C('error_code.165')));    //活动商品每个ID限购{n}件！


            $attr['price']  = $officialactivity['price'];
            $data['is_display'] = 1;
        }
        //订购数量
        switch($type){
            case 2:
                $num=$rs['num']-I('post.num');
            break;
            case 3:
                $num=I('post.num');
            break;
            default:
                $num=$rs['num']+I('post.num');
            break;
        }
        
        if($num>$attr['num']) $this->apiReturn(171); //库存不足
        if($num<1) $this->apiReturn(174);   //订购商品数量至少1件以上

        $data['officialactivity_id']  		= $officialactivity['activity_id'] ? $officialactivity['activity_id'] : 0;
        $data['officialactivity_join_id']  	= $officialactivity['id'] ? $officialactivity['id'] : 0;
		
        $data['uid']            =$this->uid;
        $data['goods_id']       =$attr['goods_id'];
        $data['seller_id']      =$attr['goods']['seller_id'];
        $data['shop_id']        =$attr['goods']['shop_id'];
        $data['category_id']    =$attr['goods']['category_id'];
        $data['attr_list_id']   =$attr['id'];
        $data['attr_id']        =$attr['attr_id'];
        $data['attr_name']      =$attr['attr_name'];
        $data['price']          =$attr['price'];
        $data['num']            =$num;
        $data['weight']         =$attr['weight'];
        $data['total_weight']   =$data['num']*$data['weight'];
        $data['total_price']    =$data['num']*$data['price'];
        $data['total_price_edit']=$data['total_price'];
        $data['score_ratio']    =$attr['goods']['score_ratio'];
        $data['score']          =$data['score_ratio'] * $data['total_price_edit'] * 100;
        $data['is_display']     = $data['is_display'] ? $data['is_display'] : $attr['goods']['is_daigou'];
        $data['express_tpl_id'] =$attr['goods']['express_tpl_id'];
         if ($attr['goods']['activity_id'] && $data['officialactivity_id'] == 0){   //如有官方活动，自定义店铺活动失效
             $data['activity_id']=(new Activity($attr['goods']['activity_id'], $attr['goods_id'], $num))->getActivitys();    //活动处理
         }

         //print_r($data);exit();
        //file_put_contents('t.txt',var_export($data,true));
        $do=D('Common/Cart');
        //清除购物车统计缓存，避免统计错误
        if ($goods['is_daigou'] == 0) {
            S(md5('cart_total_' . I('post.openid')),null);
        }

        //已存在购物车
        if($rs){
            $data['id']=$rs['id'];
            if(!$do->create($data)) $this->apiReturn(4,'',1,'操作失败！'.$do->getError());
            if(false!==$do->save()){
				if(I('post.atonce')==1 || $data['officialactivity_join_id'] > 0) $this->atonce_goods($data['id']); //设定立即购买
                //修改成功
                $this->apiReturn(1,array('data'=>$data));
            }else $this->apiReturn(0); //修改失败              

        }else{ //未加入购物车-新增            
            if(!$do->create($data)) $this->apiReturn(4,'',1,'操作失败！'.$do->getError());
            if($do->add()){
                //添加成功
                $data['id']=$do->getLastInsID();
				if(I('post.atonce')==1 || $data['officialactivity_join_id'] > 0) $this->atonce_goods($data['id']); //设定立即购买
                $this->apiReturn(1,array('data'=>$data));
            }else $this->apiReturn(0); //添加失败           
        }
    }

    /**
    * 删除购物车中商品
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        购物车ID
    */
    public function delete(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('cart');
        if($do->where(['uid'=>$this->uid,'id'=>I('post.id')])->delete()){
            //清除购物车统计缓存，避免统计错误
            S(md5('cart_total_' . I('post.openid')),null);        
            //删除成功
            $this->apiReturn(1);
        }else{
            //删除失败
            $this->apiReturn(0);
        }
    }

    /**
    * 获取单条购物车商品记录
    * @param int $_POST['id']           购物车ID
    * @param string $_POST['openid']    用户openid
    */
    public function cart_item(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('CartAttrListRelation');
        $rs=$do->relation(true)->where(['id' => I('post.id')])->field('etime,ip',true)->find();
        if($rs){
            $rs['attr_list']['images']=myurl($rs['attr_list']['images'],100);
            $this->apiReturn(1,['data' => $rs]);
        }else{
            $this->apiReturn(3);
        }
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

        //商家
        $do=D('Common/CartShopRelation');
        $shop=$do->relation(true)->where(array('uid'=>$this->uid))->group('shop_id')->field('shop_id')->select();
        $do=D('Common/CartAttrListRelation');
        $list=array();
        $num=0;
        $allMoney=0;
        
        foreach($shop as $skey=>$sval){
			$sval['shop']['shop_url']	=shop_url($sval['shop']['id'],$sval['shop']['domain']);
            $goods=$do->relation(true)->where(array('uid'=>$this->uid,'shop_id'=>$sval['shop_id'], 'is_display' => 0))->field('etime,ip',true)->select();
            //返回数组格式化处理
			$total_price=0;
			$total_weight=0;
            $total_score=0;
            foreach($goods as $key=>$val){
                $num++;
                $goods[$key]['images']      =myurl($val['attr_list']['images'],100);
                $goods[$key]['goods_name']  =$val['goods']['goods_name'];
                $goods[$key]['status']      =$val['goods']['status'];
                $goods[$key]['status_name'] =$val['goods']['status']==1?'正常':'异常';
                $goods[$key]['detail_url']  ='/Goods/view/id/'.$val['attr_list_id'].'.html';
                if($val['attr_id']!=$val['attr_list']['attr_id']){
                    $goods[$key]['status']      =2;
                    $goods[$key]['status_name'] ='商家已变更库存属性！';
                }elseif($val['num']>$val['attr_list']['num']){
                    $goods[$key]['status']      =3;
                    $goods[$key]['status_name'] ='库存不足，最多只能订购'.$val['attr_list']['num'].'件！';                    
                }elseif($val['price']!=$val['attr_list']['price'] || $val['weight']!=$val['attr_list']['weight']){ //价格或重量是否有变更
                    $do=D('Common/Cart');
                    $goods[$key]['price']         =$val['attr_list']['price'];
                    $goods[$key]['weight']        =$val['attr_list']['weight'];
                    $goods[$key]['total_price']   =$goods[$key]['num']* $goods[$key]['price'];
                    $goods[$key]['total_weight']  =$goods[$key]['num']* $goods[$key]['weight'];

                    if($do->create($goods[$key])) $do->save();
                }

                //商品取消参与官方秒杀活动(注，秒杀只能通过立即购买订购，返回至购物车将恢复原价)
                if($val['officialactivity_id'] == 250){
                    M('cart')->where(['id' => $val['id']])->save(['officialactivity_id' => 0,'officialactivity_join_id' => 0]);
                }

                $allMoney       += $goods[$key]['total_price'];
				$total_price	+=$goods[$key]['total_price'];
				$total_weight	+=$goods[$key]['total_weight'];
                $total_score    +=$goods[$key]['score'];
                unset($goods[$key]['attr_list']);
                unset($goods[$key]['goods']);
            }

            $list[$skey]['shop']		=$sval['shop'];
            $list[$skey]['goods']		=$goods;
			$list[$skey]['goods_num']	=count($goods);
			$list[$skey]['total_price']	=$total_price;
			$list[$skey]['total_weight']=$total_weight;
            $list[$skey]['total_score'] =$total_score;
			
			$total['total_weight']		+=$total_weight;
            $total['total_score']       +=$total_score;
        }
        $total['num']        =   $num;
        $total['total_price']   =   number_format($allMoney, 2);
		$total['shop_num']	=	count($shop);
        //var_dump($list);
        if($list){
            $this->apiReturn(1,array('data'=>$list,'total'=>$total));
        }else{
            //购物车为空
            $this->apiReturn(3);
        }
    }

    /**
    * 标记已选中的商品(即要购买的商品)
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['ids']       购物车商品ID,多个用逗号隔开
    */
    public function selected(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','ids','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('cart');
        $do->where(array('uid'=>$this->uid))->setField('is_select',0);
        if($do->where(array('uid'=>$this->uid,'id'=>array('in',I('post.ids'))))->setField('is_select',1)){
        	$data=$do->where(array('uid'=>$this->uid,'is_select'=>1))->field('sum(total_price) as total_price,sum(total_weight) as total_weight,count(*) as num')->find();
        	$this->apiReturn(1,array('data'=>$data));
        }else{
        	$this->apiReturn(0);
        }
    }
	
	/**
	* 立即购买
	* @param int $id 购物车ID
	*/
	public function atonce_goods($id){
        $do=M('cart');
        $do->where(array('uid'=>$this->uid))->setField('is_select',0);
        if($do->where(array('uid'=>$this->uid,'id'=>array('in',$id)))->setField('is_select',1)){
        	$data=$do->where(array('uid'=>$this->uid,'is_select'=>1))->field('sum(total_price) as total_price,sum(total_weight) as total_weight,count(*) as num')->find();
        	$this->apiReturn(1,array('data'=>$data));
        }else{
        	$this->apiReturn(0);
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

        //检查购物车中是否有选中要支付的商品
        $do=M('cart');
        if($do->where(array('uid'=>$this->uid,'is_select'=>1))->count()<1) $this->apiReturn(175); 	//购物车中无选中要进行支付的商品        

        //商家
        $do=D('Common/CartSelectRelation');
        $shop=$do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1))->group('seller_id')->field('shop_id,uid,seller_id')->select();
        $do=D('Common/CartAttrListRelation');
        $list=array();
        $allMoney   =   0;
        foreach($shop as $skey=>$sval){
			//$shop[$skey]['shop']['shop_url']	=shop_url($sval['shop']['id'],$sval['shop']['domain']);
            $goods=$do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1,'seller_id'=>$sval['seller_id']))->field('etime,ip',true)->select();
            $list['allnum']+=count($goods);
            $isActivity[$skey]  =   false;
            $list['list'][$skey]['express_type']	=$this->get_express_type($goods);
            //返回数组格式化处理
            foreach($goods as $key=>$val){
                $goods[$key]['images']      =myurl($val['attr_list']['images'],100);
                $goods[$key]['goods_name']  =$val['goods']['goods_name'];
                $goods[$key]['status']      =1;
                $goods[$key]['status_name'] ='正常';
                $goods[$key]['detail_url']  ='/Goods/view/id/'.$val['attr_list_id'].'.html';
                $goods[$key]['is_daigou']   =$val['goods']['is_daigou'];
                
                
                if ($val['goods']['is_daigou'] > 0) {   //如果当前商品为代购
                    //$a = (new Daigou())->compute($val);
                    $dg = new Daigou();
                    $res= $dg->compute(['total_price' => $val['total_price'], 'daigou_ratio' => $val['goods']['daigou_ratio']]);
                    if ($res) {
                        $goods[$key]['price']       =   $val['attr_list']['price'];
                        $goods[$key]['total_price'] =   round($goods[$key]['total_price'] + $res['daigou_cost'], 2);
                    }
                } else {//0元购及秒杀促销活动
                    if (isset($_POST['spm']) && !empty($_POST['spm'])) {
                        //临时判断
                        $isSpike = M('activity_participate')->where(['activity_id' => ['in', '626,584,574,572,570,313'], 'status' => ['lt', 2], 'uid' => $this->uid])->getField('id');
                        if ($isSpike) {
                            $list['activity']   =   '您已经参加过，不可再次参加当前活动。';
                        } else {
                            $activitys  =   Activity::getSpikeAndRestriction($sval['shop']['id'], $val['goods_id'], null, $this->uid);
                            if ($activitys) {
                                if ($goods[$key]['num'] >= $activitys['max_num'] && $activitys['max_num'] > 0) {  //如果购买的数量大于活动最多购买的数量
                                    $num    =   $goods[$key]['num'] - $activitys['max_num'];
                                    $goods[$key]['total_price'] =   ($activitys['max_num'] * $activitys['full_money']) + ($num * $goods[$key]['price']);
                                    $goods[$key]['score']       =   ($goods[$key]['total_price'] * $val['score_ratio']) * 100;
                                } else {
                                    $goods[$key]['price']       =   $val['attr_list']['price'];
                                    $goods[$key]['total_price'] =   round($goods[$key]['num'] * $activitys['full_money'], 2);
                                    $goods[$key]['score']       =   ($goods[$key]['total_price'] * $val['score_ratio']) * 100;
                                }
                                $isActivity[$skey]          =   true;
                            } else {
                                $list['activity']   =   '您已经参加过，不可再次参加当前活动。';
                            }
                        }
                    }    
                }
                if($val['attr_id']!=$val['attr_list']['attr_id']){
                    $goods[$key]['status']      =2;
                    $goods[$key]['status_name'] ='商家已变更库存属性！';
                }elseif($val['num']>$val['attr_list']['num']){
                    $goods[$key]['status']      =3;
                    $goods[$key]['status_name'] ='库存不足，最多只能订购'.$val['attr_list']['num'].'件！';                    
                }elseif($val['price']!=$val['attr_list']['price'] || $val['weight']!=$val['attr_list']['weight']){ //价格或重量是否有变更
                    $is_edit = true;
                    //是否参与官方活动
                    if($val['goods']['officialactivity_join_id'] > 0){
                        $officialactivity = M('officialactivity_join')->cache(false)->where(['id' => $val['goods']['officialactivity_join_id']])->field('day,time')->find();
                        $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
                        if($time_dif > 0 || $time_dif < -86400) { //活动未开始或已过期

                        }else $is_edit = false;

                    }

                    if($is_edit == true) {
                        $do=D('Common/Cart');
                        $goods[$key]['price']         =$val['attr_list']['price'];
                        $goods[$key]['weight']        =$val['attr_list']['weight'];
                        $goods[$key]['total_price']   =$goods[$key]['num']* $goods[$key]['price'];
                        $goods[$key]['total_weight']  =$goods[$key]['num']* $goods[$key]['weight'];
                        if($do->create($goods[$key])) $do->save();
                    }
                }
                $allMoney   +=  $goods[$key]['total_price'];
                $sval['total_price']	+=$goods[$key]['total_price'];
                $sval['total_weight']	+=$goods[$key]['total_weight'];
                $sval['total_score']    +=$goods[$key]['score'];

                unset($goods[$key]['attr_list']);
                unset($goods[$key]['goods']);
            }
            if ($isActivity[$skey] == false) {  //如果没有参与活动，则可以选择优惠券 
                //可用优惠券
                $couponMap    =   [
                    'is_use'    =>0,
                    'status'    =>1,
                    'sday'      =>array('elt',date('Y-m-d')),
                    'eday'      =>array('egt',date('Y-m-d')),
                    'uid'       =>$this->uid,
                    'shop_id'   =>$sval['shop_id'],
                    'min_price' =>array('elt',round($sval['total_price'], 2)),
                ];
                $coupon=M('coupon')->where($couponMap)->field('id,code,price,count(id) as num')->order('price desc')->group('price')->select();
            }

            $list['list'][$skey]['shop']			=$sval['shop'];
            $list['list'][$skey]['total_price']		=sprintf('%.2f',$sval['total_price']);
            $list['list'][$skey]['total_weight']    =$sval['total_weight'];
            $list['list'][$skey]['total_score']     =$sval['total_score'];
            //$list['list'][$skey]['express']			=$sval['express'];
            $list['list'][$skey]['coupon']          =$coupon;
            $list['list'][$skey]['goods']			=$goods;
            
            $list['total_score']                    +=$sval['total_score'];
            
        }
        if($list){
            $list['allMoney']   =   number_format($allMoney, 2, '.', '');;
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
    public function get_express_type($orders_goods){
    	//dump($orders_goods);
    	foreach($orders_goods as $val){
    		$ids[] 	=	$val['goods']['express_tpl_id'];
    	}
    	$ids=array_unique($ids);

    	//取所有运费模板
    	$do=M('express_tpl');
    	$list=$do->where(array('id' => array('in',$ids)))->field('is_express,is_ems')->select();

    	$result			=array();
    	$express_type 	=array();

    	//列出支持的快递方式
    	foreach($list as $val){
    		if($val['is_express']==1 && !in_array(1,$result)) {
    			$result[]=1;
    			$express_type[]=array(
    					'name'	=>'快递',
    					'value'	=>1
    				);
    		}
    		if($val['is_ems']==1 && !in_array(2,$result)) {
    			$result[]=2;
    			$express_type[]=array(
    					'name'	=>'EMS',
    					'value'	=>2
    				);    			
    		}
    		if(count($result)==2) break;
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

        //商家
        $do=D('Common/CartSelectRelation');
        $shop=$do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1))->group('seller_id')->field('shop_id,uid,seller_id')->select();
        $o_no=$this->create_orderno('OG',$this->uid);  //订单号
        $pay_price=0;
        $goods_num=0;
        $score=0;
        $do->startTrans();

        //创建订单
        $data=array();
        $data['uid']        =$this->uid;
        $data['o_no']       =$o_no;
        $data['status']		=1;
        $data['province']   =$address['province'];
        $data['city']       =$address['city'];
        $data['district']   =$address['district'];
        $data['town']       =$address['town'] ? $address['town'] : 0;
        $data['street']     =$address['street'];
        $data['linkname']   =$address['linkname'];
        $data['mobile']     =$address['mobile'];
        $data['tel']        =$address['tel'];
        $data['postcode']   =$address['postcode'];
        $data['shop_num']   =count($shop);
        $data['terminal']   =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP

        //var_dump($data);
        $sw0=D('Common/Orders')->create($data);
        if(!$sw0){
            $msg=D('Common/Orders')->getError();
            $this->sw[]=$sw0;
            goto error;        	
        }
        $this->sw[]=D('Common/Orders')->add();
        $oid=D('Common/Orders')->getLastInsID();


        foreach($shop as $skey=>$sval){
            //检查要购买的商品库存是否正常
            $goods=$this->check_goods($this->uid,$sval['seller_id']);
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

            //运费
            $express_price=$this->_express_price($this->uid,$sval['seller_id'],I('post.address_id'),I('post.express_type_'.$sval['seller_id']));
            /*
            $express=$this->_express_price($this->uid,$sval['seller_id'],I('post.express_'.$sval['seller_id']),I('post.address_id'));
            if($express['code']!=1){
                //运费模板存在问题
                $msg=C('error_code')[$express['code']];
                goto error;                
            }
            */
            //var_dump($express);

            //创建商家订单
            $data=array();
            $data['o_no']           	=$o_no;
            $data['o_id']           	=$oid;
            $data['s_no']           	=$this->create_orderno('DD',$this->uid);
            $data['status']				=1;
            $data['inventory_type'] 	=$sval['shop']['inventory_type'];
            $data['shop_id']        	=$sval['shop_id'];
            $data['uid']            	=$this->uid;
            $data['seller_id']      	=$sval['seller_id'];
            $data['goods_price']        =$goods['total_price'];
            $data['goods_price_edit']   =$data['goods_price'];
            $data['express_type']		=I('post.express_type_'.$sval['seller_id']);
            $data['express_price']  	=$express_price;
            $data['express_price_edit'] =$data['express_price'];
            //$data['express_id']         =$express['data']['express']['id'];
			//$data['express_company_id']	=$express['data']['express']['express_company']['id'];
			//$data['express_company']	=$express['data']['express']['express_company']['sub_name'];
            $data['remark']         =I('post.remark_'.$sval['seller_id']);
            $data['goods_num']      =count($goods['goods']);
            //$data['score']          =$goods['total_score']+($data['express_price_edit']*100);
            $data['score']          =$goods['total_score']; //运费不赠送积分
            $data['terminal']       =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP
            $data['next_time']      = date('Y-m-d H:i:s',time() + C('cfg.orders')['add']);   //过了这个时间未付款将关闭订单
            
            
            $goods_num+=$data['goods_num'];
            $score+=$data['score'];
			//var_dump($data);

            //检查优惠券
            $isCoupon   =   0;
            if(I('post.coupon_'.$sval['seller_id'])){
                $coupon=M('coupon')->lock(true)->where(array(
                        'id'        =>I('post.coupon_'.$sval['seller_id']),
                        'uid'       =>$this->uid,
                        'shop_id'   =>$sval['shop_id'],
                        'is_use'    =>0,
                        'status'    =>1,
                        'sday'      =>array('elt',date('Y-m-d')),
                        'eday'      =>array('egt',date('Y-m-d')),
                        'min_price' =>array('elt',round($data['goods_price'], 2))
                    ))->field('id,price,b_id')->find();
				$this->sw[]=$sval['seller_id'].'-coupon:'.$coupon;
				if(!$coupon){                   
                    //优惠券不存在或已被使用
                    $msg=C('error_code')[191];
                    goto error;
                }
                
                $swLess =   M('coupon_batch')->where(['id' => $coupon['b_id']])->setInc('use_num', 1);  //使用+1
                if (!$swLess) {
                    //优惠券更新失败！
                    $msg=C('error_code')[192];
                    goto error;
                }
                $swc=M('coupon')->where(array('id'=>$coupon['id']))->save(array('is_use'=>1, 'status' => 2,'use_time'=>date('Y-m-d H:i:s'),'orders_id'=>$oid));
                $this->sw[]=$sval['seller_id'].'-coupon:'.$swc;
                if(!$swc){                    
                    //优惠券更新失败！
                    $msg=C('error_code')[192];
                    goto error;           
                }

                $data['coupon_price']       =$coupon['price'];
                $data['coupon_id']          =$coupon['id'];
                $data['goods_price_edit']  -=$coupon['price'];
//                 $data['score']             -=$data['coupon_price']*100;
//                 $data['score']              =$data['score']>0?$data['score']:0;
                $isCoupon                   =$data['coupon_price'];
            }

            $data['total_price']    =$data['goods_price']+$data['express_price_edit']-$data['coupon_price'];
            $data['pay_price']      =$data['total_price'];
            
            //常规活动
            $activity  =   Activity::participate($data, $isCoupon);
            if ($activity) {
                $data['express_price_edit']     =   $activity['express_price_edit'];   //优惠后的邮费
                $data['goods_price_edit']       =   $activity['goods_price_edit'];           //优惠后的商品金额
                $data['pay_price']              =   $activity['goods_price_edit'] + $data['express_price_edit']; //需要支付的金额
                $data['score']                  =   $activity['score'];                 //赠送积分
                $data['activity_id']            =   $activity['ids'];                   //参与的活动ID
                $data['coupon_percentage']      =   $activity['coupon_percentage'];     //优惠百分点；
            }
            //常规活动结束
            //goto error;
            $data['money']          =$data['pay_price'];
            $pay_price             +=$data['pay_price'];
            $sw1=D('Common/OrdersShop')->create($data);
            if(!$sw1) {
                $msg=D('Common/OrdersShop')->getError();
                $this->sw[]=$sval['seller_id'].':'.$sw1;
                goto error; 
            }  
            $sw1=D('Common/OrdersShop')->add();
            $s_id=D('Common/OrdersShop')->getLastInsID();
			$this->sw[]=$sval['seller_id'].':'.$sw1;

            //订单logs
            $logs_data=array(
            		'o_id'		=>$oid,
            		'o_no'		=>$o_no,
            		's_id'		=>$s_id,
            		's_no'		=>$data['s_no'],
            		'status'	=>1,
            		'remark'	=>'创建订单'
            	);
            $logs_sw=D('Common/OrdersLogs')->create($logs_data);
            if(!$logs_sw){
                $msg=D('Common/OrdersLogs')->getError();
                $this->sw[]=$sval['seller_id'].'-logs:'.$logs_sw;
                goto error;              	

            }
            $logs_sw=D('Common/OrdersLogs')->add();
			$this->sw[]=$sval['seller_id'].'-logs:'.$logs_sw;
			//$vPrice[$skey]  =   0;
            foreach($goods['goods'] as $k => $v){
                $v['s_id']              =$s_id;
                $v['s_no']              =$data['s_no'];
                $v['o_no']              =$data['o_no'];
                $v['o_id']              =$oid;
                $v['goods_service_days']=getGoodsServiceDays($v['goods_id']);//商品售后天数
                //常规活动满减
                if ((!empty($activity) && ($activity['coupon_price'] > 0)) || $isCoupon > 0) { //如果有参与活动并且参与了满减活动则更改当前订单金额及积分
                    if ($activity['coupon_price'] > 0) {
                        $coupon_price       =   round(round($v['total_price'] / $data['goods_price'], 2) * $activity['coupon_price'], 2);   //活动
                    } elseif ($isCoupon > 0) {
                        $coupon_price       =    round(round($v['total_price'] / $data['goods_price'], 2) * $isCoupon, 2);  //优惠券
                    }
                    $v['total_price_edit']  =   round($v['total_price'] - $coupon_price, 2);
                    $v['score']             =   $v['score'] - ($v['score_ratio'] * $coupon_price * 100);
                    $ordersScore           +=   $v['score'];
                } else {    //未满减的情况下
                    $ordersScore           +=   $v['score'];
                    $v['total_price_edit']  =   $v['total_price'];
                }
                //如果当前商品为代购商品
                if($v['is_daigou'] > 0) {
                    $dg = new Daigou();
                    $res= $dg->compute(['daigou_ratio' => $v['daigou_ratio'], 'total_price' => $v['total_price']]);
                    $res['pay_price']       =   round($data['pay_price']+$res['daigou_cost'], 2);
                    $pay_price             +=   $res['daigou_cost'];
                }
                
                //在未使用优惠券的情况下减去多余的钱
                //if ($isCoupon == 0) {
                $vPrice[$skey] +=  $v['total_price_edit'];
                if ($k == count($goods['goods']) - 1) {
                    if (round($vPrice[$skey], 2) < round($data['money'] - $data['express_price_edit'], 2)) { //如果使用活动后的金额 小于订单总金额，则需要加上一份
                        if ($k == count($goods['goods']) - 1) {
                            $v['total_price_edit']  +=  round(($data['money'] - $data['express_price_edit'] - $vPrice[$skey]), 2);   //加上不够的钱
                            $v['score']             +=  (round(($data['money'] - $data['express_price_edit'] - $vPrice[$skey]), 2) * $v['score_ratio']) * 100;
                            $ordersScore            +=  (round(($data['money'] - $data['express_price_edit'] - $vPrice[$skey]), 2) * $v['score_ratio']) * 100;
                        }
                    } elseif (round($vPrice[$skey], 2) > round($data['money'] - $data['express_price_edit'], 2)) {//如果使用活动后的金额 大于订单总金额，则需要减去一份
                        if ($k == count($goods['goods']) - 1) {
                            $v['total_price_edit']  -=  round(($vPrice[$skey] - ($data['money'] - $data['express_price_edit'])), 2);   //减去多余的钱
                            $v['score']             -=  (round(($vPrice[$skey] - ($data['money'] - $data['express_price_edit'])), 2) * $v['score_ratio']) * 100;
                            $ordersScore            -=  (round(($vPrice[$skey] - ($data['money'] - $data['express_price_edit'])), 2) * $v['score_ratio']) * 100;
                        }
                    }
                }
                //}
                unset($v['id']);
                $sw2=D('Common/OrdersGoods')->create($v);
                if(!$sw2){
                    $msg=D('Common/OrdersGoods')->getError();
                    $this->sw[]=$sval['seller_id'].'-goods:'.$sw2;
                    goto error;                	
                }
                $sw2=D('Common/OrdersGoods')->add();
                $this->sw[]=$sval['seller_id'].'-goods:'.$sw2;

                //拍下减库存
                if($v['is_subnum']==1){
                    //锁住记录
                    M('goods_attr_list')->lock(true)->where(array('id'=>$v['attr_list_id']))->field('id')->find();
                    $sw3=M('goods_attr_list')->where(array('id'=>$v['attr_list_id']))->setDec('num',$v['num']);   
                    $this->sw[]=$sval['seller_id'].'-attr_list:'.$sw2;                     
                    if(!$sw3){                        
                        //更新库存失败！
                        $msg=C('error_code')[193];
                        goto error;
                    }
                }
            }
            if (!empty($res) && $v['is_daigou'] > 0) {
                if(M('orders_shop')->where(['id' => $s_id])->save($res) == false) {
                    $msg =  '写入代购信息失败！';
                    goto error;
                }
            } 
            
            if ((!empty($activity) && ($activity['coupon_price'] > 0)) || $isCoupon > 0) { //如果有参与活动并且参与了满减活动则更改当前订单总积分
                $sData  =   ['score' => $ordersScore];
                if(M('orders_shop')->where(['id' => $s_id])->save($sData) == false) {
                    $msg =  '积分写入失败！';
                    goto error;
                }
            } 
            $scores +=  $ordersScore;   //运算后的赠送分总数
        }
        //删除购物车中商品
        $sw4=M('cart')->where(array('is_select'=>1,'uid'=>$this->uid))->delete();
        $this->sw[]=$sw4;
        if(!$sw4){
            //清除购物车中商品失败！
            $msg=C('error_code')[194];
            goto error;
        }  
        
        $sw5=M('orders')->where(array('id'=>$oid))->save(array('pay_price'=>$pay_price,'goods_num'=>$goods_num,'score'=>$scores));
        $this->sw[]=$sw5;
        if(!$sw5){
            //更新订单金额失败！
            $msg=C('error_code')[195].M('orders')->getLastSQL();
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
     * 创建秒杀及0元购订单
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
        $pay_price  = 0;
        $goods_num  = 0;
        $score      = 0;

        $do->startTrans();

        //创建合并订单
        $data=array();
        $data['uid']        =$this->uid;
        $data['o_no']       =$o_no;
        $data['status']		=1;
        $data['province']   =$address['province'];
        $data['city']       =$address['city'];
        $data['district']   =$address['district'];
        $data['town']       =$address['town'] ? $address['town'] : 0;
        $data['street']     =$address['street'];
        $data['linkname']   =$address['linkname'];
        $data['mobile']     =$address['mobile'];
        $data['tel']        =$address['tel'];
        $data['postcode']   =$address['postcode'];
        $data['shop_num']   =count($list);
        $data['terminal']   =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP

        if(!$this->sw[] = D('Common/Orders')->create($data)){
            $msg = D('Common/Orders')->getError();
            goto error;
        }

        if(!$this->sw[] = $oid = D('Common/Orders')->add()){
            $msg = '创建合并订单失败！';
            goto error;
        }

        foreach($list as $key => $val){
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
            $data['goods_num']          =count($goods['goods']);
            $data['score']              =$goods['total_score']; //运费不赠送积分
            $data['terminal']           =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP
            $data['next_time']          = date('Y-m-d H:i:s',time() + C('cfg.orders')['add']);   //过了这个时间未付款将关闭订单

            $goods_num+=$data['goods_num'];
            $score+=$data['score'];

            $data['total_price']    =$data['goods_price']+$data['express_price_edit']-$data['coupon_price'];
            $data['pay_price']      =$data['total_price'];

            //常规活动结束
            //goto error;
            $data['money']          =$data['pay_price'];
            $pay_price             +=$data['pay_price'];
            $sGoodsEditPrice        =$data['goods_price_edit'];
            $sw1=D('Common/OrdersShop')->create($data);
            if(!$sw1) {
                $msg=D('Common/OrdersShop')->getError();
                goto error;
            }
            $sw1=D('Common/OrdersShop')->add();
            if (!$sw1) {
                $msg = '创建商家订单失败';
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
            $logs_sw=D('Common/OrdersLogs')->create($logs_data);
            if(!$logs_sw){
                $msg=D('Common/OrdersLogs')->getError();
                goto error;

            }
            $logs_sw=D('Common/OrdersLogs')->add();
            if(!$logs_sw){
                $msg=D('Common/OrdersLogs')->getError();
                $this->sw[]=$data['seller_id'].'-logs:'.$logs_sw;
                goto error;

            }
            $logs_sw=D('Common/OrdersLogs')->add();
            $this->sw[]=$data['seller_id'].'-logs:'.$logs_sw;

            foreach($goods['goods'] as $k => $v){
                $v['s_id']              =$s_id;
                $v['s_no']              =$data['s_no'];
                $v['o_no']              =$data['o_no'];
                $v['o_id']              =$oid;
                $data['count_goods_num']=$v['num'];
                $v['goods_service_days']=getGoodsServiceDays($v['goods_id']);//商品售后天数


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
                $sw2=D('Common/OrdersGoods')->create($v);
                if(!$sw2){
                    $msg=D('Common/OrdersGoods')->getError();
                    goto error;
                }
                $sw2=D('Common/OrdersGoods')->add();


            }
            if (!empty($activity)) { //如果有参与活动并且参与了满减活动则更改当前订单总积分
                $sData  =   ['score' => $ordersScore];
                if ($sPay_price) {
                    $sData['pay_price']         =   $sPay_price;
                    $sData['goods_price_edit']  =   $sGoodsEditPrice;
                    $sData['activity_id']       =   $sActivityId;
                }
                if(M('orders_shop')->where(['id' => $s_id])->save($sData) == false) {
                    goto error;
                }
            }
            $scores +=  $ordersScore;   //运算后的赠送分总数
        }

        //删除购物车中商品
        $sw4=M('cart')->where(array('is_select'=>1,'uid'=>$this->uid))->delete();
        $this->sw[]=$sw4;
        if(!$sw4){
            //清除购物车中商品失败！
            $msg=C('error_code')[194];
            goto error;
        }

        $sw5=M('orders')->where(array('id'=>$oid))->save(array('pay_price'=>$pay_price,'goods_num'=>$goods_num,'score'=>$scores));
        $this->sw[]=$sw5;
        if(!$sw5){
            //更新订单金额失败！
            $msg=C('error_code')[195].M('orders')->getLastSQL();
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

    //秒杀，0元购活动订单创建
    public function create_activity_orders_bak(){
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
    
        //商家
        $do=D('Common/CartSelectRelation');
        $shop=$do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1))->group('seller_id')->field('shop_id,uid,seller_id')->select();
        $o_no=$this->create_orderno('OG',$this->uid);  //订单号
        $pay_price=0;
        $goods_num=0;
        $score=0;
        $do->startTrans();
    
        //创建订单
        $data=array();
        $data['uid']        =$this->uid;
        $data['o_no']       =$o_no;
        $data['status']		=1;
        $data['province']   =$address['province'];
        $data['city']       =$address['city'];
        $data['district']   =$address['district'];
        $data['town']       =$address['town'] ? $address['town'] : 0;
        $data['street']     =$address['street'];
        $data['linkname']   =$address['linkname'];
        $data['mobile']     =$address['mobile'];
        $data['tel']        =$address['tel'];
        $data['postcode']   =$address['postcode'];
        $data['shop_num']   =count($shop);
        $data['terminal']   =I('post.terminal') ? I('post.terminal') : 0; //终端,0=PC,1=WAP
    
        //var_dump($data);
        $sw0=D('Common/Orders')->create($data);
        if(!$sw0){
            $msg=D('Common/Orders')->getError();
            $this->sw[]=$sw0;
            goto error;
        }
        $this->sw[]=D('Common/Orders')->add();
        $oid=D('Common/Orders')->getLastInsID();
    
    
        foreach($shop as $skey=>$sval){
            //检查要购买的商品库存是否正常
            $goods=$this->check_goods($this->uid,$sval['seller_id']);
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
    
            //运费
            $express_price=$this->_express_price($this->uid,$sval['seller_id'],I('post.address_id'),I('post.express_type_'.$sval['seller_id']));
            /*
             $express=$this->_express_price($this->uid,$sval['seller_id'],I('post.express_'.$sval['seller_id']),I('post.address_id'));
             if($express['code']!=1){
             //运费模板存在问题
             $msg=C('error_code')[$express['code']];
             goto error;
             }
            */
            //var_dump($express);
    
            //创建商家订单
            $data=array();
            $data['o_no']           	=$o_no;
            $data['o_id']           	=$oid;
            $data['s_no']           	=$this->create_orderno('DD',$this->uid);
            $data['status']				=1;
            $data['inventory_type'] 	=$sval['shop']['inventory_type'];
            $data['shop_id']        	=$sval['shop_id'];
            $data['uid']            	=$this->uid;
            $data['seller_id']      	=$sval['seller_id'];
            $data['goods_price']        =$goods['total_price'];
            $data['goods_price_edit']   =$data['goods_price'];
            $data['express_type']		=I('post.express_type_'.$sval['seller_id']);
            $data['express_price']  	=$express_price;
            $data['express_price_edit'] =$data['express_price'];
            //$data['express_id']         =$express['data']['express']['id'];
            //$data['express_company_id']	=$express['data']['express']['express_company']['id'];
            //$data['express_company']	=$express['data']['express']['express_company']['sub_name'];
            $data['remark']         =I('post.remark_'.$sval['seller_id']);
            $data['goods_num']      =count($goods['goods']);
            //$data['score']          =$goods['total_score']+($data['express_price_edit']*100);
            $data['score']          =$goods['total_score']; //运费不赠送积分
            $data['terminal']       =I('post.terminal') ? I('post.terminal') : 0;  //终端,0=PC,1=WAP
    
    
            $goods_num+=$data['goods_num'];
            $score+=$data['score'];
            //var_dump($data);
    
            
    
            $data['total_price']    =$data['goods_price']+$data['express_price_edit']-$data['coupon_price'];
            $data['pay_price']      =$data['total_price'];
    
    
            
    
            $data['money']          =$data['pay_price'];
            $pay_price             +=$data['pay_price'];
            $sGoodsEditPrice        =$data['goods_price_edit'];
            $sw1=D('Common/OrdersShop')->create($data);
            if(!$sw1) {
                $msg=D('Common/OrdersShop')->getError();
                $this->sw[]=$sval['seller_id'].':'.$sw1;
                goto error;
            }
            $sw1=D('Common/OrdersShop')->add();
            $s_id=D('Common/OrdersShop')->getLastInsID();
            $this->sw[]=$sval['seller_id'].':'.$sw1;
    
            //订单logs
            $logs_data=array(
                'o_id'		=>$oid,
                'o_no'		=>$o_no,
                's_id'		=>$s_id,
                's_no'		=>$data['s_no'],
                'status'	=>1,
                'remark'	=>'创建订单'
            );
            $logs_sw=D('Common/OrdersLogs')->create($logs_data);
            if(!$logs_sw){
                $msg=D('Common/OrdersLogs')->getError();
                $this->sw[]=$sval['seller_id'].'-logs:'.$logs_sw;
                goto error;
    
            }
            $logs_sw=D('Common/OrdersLogs')->add();
            $this->sw[]=$sval['seller_id'].'-logs:'.$logs_sw;
            //$sActivityId                =0;
            foreach($goods['goods'] as $v){
                $v['s_id']              =$s_id;
                $v['s_no']              =$data['s_no'];
                $v['o_no']              =$data['o_no'];
                $v['o_id']              =$oid;
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
                    
                        //入列
                        //                     if (Queue::intoQueue(Queue::$queueName['spike'], ['time' => NOW_TIME + (15 * 60), 'map' => ['activity' => ['id' => $activity['participateId']], 'orders' => ['id' => $s_id]]]) == false) {
                        //                         $msg    =   '加入队列失败!';
                        //                         goto error;
                        //                     }
                    } else {    //没有活动的情况下
                        $ordersScore               +=   $v['score'];
                        $v['total_price_edit']      =   $v['total_price'];
                    }
                }
                
                unset($v['id']);
                //var_dump($v);
                $sw2=D('Common/OrdersGoods')->create($v);
                if(!$sw2){
                    $msg=D('Common/OrdersGoods')->getError();
                    $this->sw[]=$sval['seller_id'].'-goods:'.$sw2;
                    goto error;
                }
                $sw2=D('Common/OrdersGoods')->add();
                $this->sw[]=$sval['seller_id'].'-goods:'.$sw2;
    
                //拍下减库存
                if($v['is_subnum']==1){
                    //锁住记录
                    M('goods_attr_list')->lock(true)->where(array('id'=>$v['attr_list_id']))->field('id')->find();
                    $sw3=M('goods_attr_list')->where(array('id'=>$v['attr_list_id']))->setDec('num',$v['num']);
                    $this->sw[]=$sval['seller_id'].'-attr_list:'.$sw2;
                    if(!$sw3){
                        //更新库存失败！
                        $msg=C('error_code')[193];
                        goto error;
                    }
                }
            }
            if (!empty($activity)) { //如果有参与活动并且参与了满减活动则更改当前订单总积分
                $sData  =   ['score' => $ordersScore];
                if ($sPay_price) {
                    $sData['pay_price']         =   $sPay_price;
                    $sData['goods_price_edit']  =   $sGoodsEditPrice;
                    $sData['activity_id']       =   $sActivityId;
                }
                if(M('orders_shop')->where(['id' => $s_id])->save($sData) == false) {
                    goto error;
                }
            }
            $scores +=  $ordersScore;   //运算后的赠送分总数
        }
        //goto error;
        //删除购物车中商品
        $sw4=M('cart')->where(array('is_select'=>1,'uid'=>$this->uid))->delete();
        $this->sw[]=$sw4;
        if(!$sw4){
            //清除购物车中商品失败！
            $msg=C('error_code')[194];
            goto error;
        }
    
        $sw5=M('orders')->where(array('id'=>$oid))->save(array('pay_price'=>$pay_price,'goods_num'=>$goods_num,'score'=>$scores));
        $this->sw[]=$sw5;
        if(!$sw5){
            //更新订单金额失败！
            //$msg=C('error_code')[195];
            $msg='订单金额不能小于0.1元';
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
    public function check_goods($uid,$seller_id){
        $do=D('Common/CartAttrListRelation');
        $goods=$do->relation(true)->where(array('uid'=>$this->uid,'is_select'=>1,'seller_id'=>$seller_id))->field('etime,ip',true)->order('price asc')->select();
        //var_dump($list);
        $result['error']=0; //不正常的记录数量
        if($goods){
            //返回数组格式化处理
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
                }elseif($val['num']>$val['attr_list']['num']){
                    $goods[$key]['status']      =3;
                    $goods[$key]['status_name'] ='库存不足，最多只能订购'.$val['attr_list']['num'].'件！';
                    $result['error']++;                
                }elseif($val['price']!=$val['attr_list']['price'] || $val['weight']!=$val['attr_list']['weight']){ //价格或重量是否有变更
                    $is_edit = true;
                    //是否参与官方活动
                    if($val['goods']['officialactivity_join_id'] > 0){
                        $officialactivity = M('officialactivity_join')->cache(false)->where(['id' => $val['goods']['officialactivity_join_id']])->field('day,time')->find();
                        $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
                        if($time_dif > 0 || $time_dif < -86400) { //活动未开始或已过期

                        }else $is_edit = false;

                    }

                    if($is_edit == true) {
                        $do=D('Common/Cart');
                        $goods[$key]['price']         =$val['attr_list']['price'];
                        $goods[$key]['weight']        =$val['attr_list']['weight'];
                        $goods[$key]['total_price']   =$goods[$key]['num']* $goods[$key]['price'];
                        $goods[$key]['total_weight']  =$goods[$key]['num']* $goods[$key]['weight'];
                        if($do->create($goods[$key])) $do->save();
                    }

                }

                $result['total_weight']+=$goods[$key]['total_weight'];
                $result['total_price']+=$goods[$key]['total_price'];
                $result['total_score']+=$goods[$key]['score'];

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
        $this->need_param=array('openid','address_id','seller_id','express_type','sign');
        $this->_need_param();
        $this->_check_sign();

        //发货方式错误
        if(!in_array(I('post.express_type'), array(1,2))) $this->apiReturn(180);

        //$this->apiReturn(1,array('data'=>['express_price'=>rand(8,20)]));

        $express_price=$this->_express_price($this->uid,I('post.seller_id'),I('post.address_id'),I('post.express_type'));
        $this->apiReturn(1,['data' => ['express_price' => $express_price]]);
    }

    /**
    * @param int 	$uid 买家ID
    * @param int 	$uid 卖家ID
    * @param int  	$address_id 收货地址ID
    * @param int 	$express_type 发货方式,1=快递 ,2=Ems
    */
    public function _express_price($uid,$seller_id,$address_id,$express_type){
        //统计
        $do=M('cart');
        $rs=$do->where(array('uid'=>$uid,'is_select'=>1,'seller_id'=>$seller_id))->field('sum(num) as num,sum(total_weight) as total_weight')->find();

        if($rs['num']<1) $this->apiReturn(175);  //购物车中无选中要进行支付的商品 
        

        $do=M('shopping_address');
        if(!$city=$do->where(array('id' => $address_id,'uid' => $uid))->getField('city')) {
            //收货地址不存在
            return array('code'=>177); 
        }

        //待付款商品
        $do=D('Common/CartAttrListRelation');
        $goods=$do->relation('goods')->where(array('uid'=>$uid,'is_select'=>1,'seller_id'=>$seller_id))->field('goods_id,num,total_weight')->select();

        

        //获取不包邮的运费模板
        foreach($goods as $val){
        	$ids[]=$val['goods']['express_tpl_id'];
        }
        $ids=array_unique($ids);

        $map        =array('id' => array('in',$ids),'is_free'=>0);

        //取不包邮的运费模板
        $do=D('Common/ExpressTplRelation');
        $tmp 	=$do->relation(true)
        			->where($map)
        			->field('id,is_free,unit,is_express,express_default_first_unit,express_default_first_price,express_default_next_unit,express_default_next_price,is_ems,ems_default_first_unit,ems_default_first_price,ems_default_next_unit,ems_default_next_price')
        			->select();
     
        foreach($tmp as $i => $val){
        	$express_tpl[$val['id']]=$val;
        }

        //同一运费模板的商品数量及重量累加，包邮的商品过滤掉
        $goods_total=array();
        foreach($goods as $val){
        	if(isset($express_tpl[$val['goods']['express_tpl_id']])){        		
        		$tpl_id=$val['goods']['express_tpl_id'];

        		$goods_total[$tpl_id]['num'] 			+=$val['num'];
        		$goods_total[$tpl_id]['total_weight'] 	+=$val['total_weight'];
        	}
        }


        //商品
        //按件计的运费
        $res[1]		=array(
        		'num'	=>0,	//不同运模板笔数
        		'first'	=>0,	//首重/件金额
        		'next'	=>0		//续重/件金额
        	);	

        //按重量计的运费
        $res[2]		=array(
        		'num'	=>0,
        		'first'	=>0,
        		'next'	=>0
        	);

        //同类型运费模板首重/件取最大值，续重或件费用累加
        foreach($goods_total as $key => $val){
        	$res[$express_tpl[$key]['unit']]['num']++;
        	$price 						=$this->_express_goods_price($express_tpl[$key],$val,$city,$express_type);
        	//$res[$express_tpl[$key]['unit']]['first']	+=$price['first'];
            if($res[$express_tpl[$key]['unit']]['first']> $price['first']){
                $res[$express_tpl[$key]['unit']]['next']    +=$price['next2'];
            }else{
                $res[$express_tpl[$key]['unit']]['next']    +=$price['next'];
                $res[$express_tpl[$key]['unit']]['first']   =$price['first'];
            }
            //$res[$express_tpl[$key]['unit']]['first']   =max($res[$express_tpl[$key]['unit']]['first'],$price['first']);
        	

        }

        
        $express_price=round(($res[1]['first']+$res[1]['next']),2) + round(($res[2]['first']+$res[2]['next']),2);

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