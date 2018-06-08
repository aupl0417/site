<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 转盘抽奖
+----------------------------------------------------------------------
| Author: 梁丰
|----------------------------------------------------------------------
| 2016-10-25
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class LuckdrawController extends CommonController {
	protected $action_logs = array('give_free_chance','inside_give_free_chance','give_tangbao_chance','luckDraw','award_winning','timed_task','luckdraw_statistics');

    public function _initialize() {
        parent::_initialize();
        $action = ACTION_NAME;
        if(!in_array('_'.$action,get_class_methods($this))) $this->apiReturn(1501);  //请求的方法不存在
        $this->_api(['method' => $action]);
    }


    /**
     * 各方法的必签字段
     * @param string $method     方法
     */
    public function _sign_field($method){
        $sign_field = [
            //'_about'                => array('require_check' => false,'field'=>'openid,s_no'),    //招商介绍
			//'_about'                => 'tt1,tt2',    //测试
			
			'_get_goods_list'				=> '',						//获取奖池奖品清单
			'_get_winner'					=> '',						//获取中奖记录top20
			'_get_my_winner'				=> 'openid,pagesize,p',		//获取我的中奖记录
			
			'_luckDraw'             		=> array('require_check' => 1,'field' => 'openid'),    			//进行抽奖
			'_checkFrequency'				=> array('require_check' => 1,'field' => 'openid'),				//检查是否可以抽奖
			
			//'_receive'						=> 'openid,winner_id',		//领奖
			
			'_get_user_chance'				=> 'openid',				//获取用户的所有抽奖机会
			
			'_inside_give_free_chance'		=> 'openid,num',			//给予用户免费抽奖机会,已暂停该接口
			'_give_free_chance'				=> array('require_check' => 1,'require_check_time'=>2,'field' => 'erp_uid,num'),			//给予用户免费抽奖机会
			'_give_tangbao_chance'			=> array('require_check' => 1,'field' => 'openid'),				//唐宝兑换的抽奖机会
			
			
			'_get_winning'					=> 'openid,uid,no',			//领奖-获取奖品信息
			'_award_winning'				=> array('require_check' => 1,'field' => 'openid,uid,no'),			//领奖-领奖操作
			'_query_express'				=> 'no',					//查询物流信息
			
			
			'_timed_task'					=> '',						//定时任务(将昨天没有领取的奖品修改为已过期)
			'_luckdraw_statistics'			=> '',						//定时任务(统计抽奖数据)


        ];

        $result=$sign_field[$method];
        return $result;
    }

	
    public function index(){
    	redirect(C('sub_domain.www'));
    }
	/**
     * 获取奖池奖品清单
     */
	public function _get_goods_list(){
		$goods_list = $this->_prize_arr();
		
		$tmp_goods_list = array();
		
		foreach($goods_list as $k => $v){
			//清除除了奖品名称和奖品图片的其他信息
			$tmp = array();
			$tmp['prize_name'] = $v['prize_name'];
			$tmp['images'] = myurl($v['images'],70);
			$tmp_goods_list[] = $tmp;
		}
		if($tmp_goods_list){
			return ['code' => 1,'data' => $tmp_goods_list];
		}else{
			return ['code' => 3];
		}
	}
	/**
     * 获取我的中奖记录
     */
	public function _get_my_winner(){
		$map['uid'] = $this->uid;
		if(I('post.ac_type')>0){$map['ac_type'] = I('post.ac_type');}
		$pagesize=I('post.pagesize')?I('post.pagesize'):20;
		
		
		$pagelist=pagelist(array(
			'table'     =>'winning_list',
			'do'        =>'M',
			'map'       =>$map,
			'order'     =>'atime desc',
			'fields'    =>'atime,prize_name,is_receive,is_deliver,ac_type,award_url',
			'pagesize'  =>$pagesize,
			'p'                 =>I('post.p'),
		));
		
		
		foreach($pagelist['list'] as $k=>$v){
			$pagelist['list'][$k]['day'] = substr($v['atime'],0,-9);
			if($v['ac_type'] == 1){
				$pagelist['list'][$k]['ac_type'] = '转盘送壕礼';
			}else{
				$pagelist['list'][$k]['ac_type'] = '其他活动';
			}
		}
		if($pagelist){
			return ['code' => 1,'data' => $pagelist];
		}else{
			return ['code' => 3];
		}
	}
	/**
     * 获取中奖记录top20
     */
	public function _get_winner(){
		$list = M('winning_list')->field('mobile,prize_name')->order('atime desc')->limit(20)->select();
		foreach($list as $k =>$v){
			$list[$k]['mobile'] = substr_replace($v['mobile'],'****',3,4);
		}
		if($list){
			return ['code' => 1,'data' => $list];
		}else{
			return ['code' => 3];
		}
	}
	/**
     * 获取用户抽奖次数
     */
	public function _get_user_chance(){
		$now_day = date('Y-m-d',time());
		$free_num = M('luckdraw_chance_free')->where('atime > "'.$now_day.'" and uid = "'.$this->uid.'" and status = 1')->count();
		
		$res = M('luckdraw_chance')->field('free_chance,tangbao_chance')->where('uid = '.$this->uid)->find();
		if($res){
			if($res['free_chance'] != $free_num){
				M('luckdraw_chance')->where('uid = '.$this->uid)->data(['free_chance'=>$free_num])->save();
				$total = $free_num+$res['tangbao_chance'];
			}else{
				$total = $res['free_chance']+$res['tangbao_chance'];
			}
			return ['code' => 1,'data' => ['chance_num'=>$total]];
		}else{
			$res = M('luckdraw_chance')->add(['uid'=>$this->uid,'free_chance'=>$free_num]);
			if($res){
				return ['code' => 1,'data' => ['chance_num'=>0]];
			}else{
				return ['code' => 0];
			}
			
		}
	}
	/**
     * 检查是否可以抽奖
     * return int 0.没有机会 1.免费机会 2.唐宝机会 
     */
	public function _checkFrequency(){
		$free_num = M('luckdraw_chance_free')->where('atime > "'.$now_day.'" and uid = "'.$this->uid.'" and status = 1')->count();
		$res = M('luckdraw_chance')->field('free_chance,tangbao_chance')->where('uid = '.$this->uid)->find();
		if($free_num > 0){
			return 'free_chance';
		}else if($res['tangbao_chance'] > 0){
			return 'tangbao_chance';
		}else{
			return false;
		}
	}
	/**
     * 进行抽奖
     */
	public function _luckDraw(){
		$open = C('CFG.luckdraw')['luckdraw_open'];
		if(!$open){
			return ['code' => 0,'msg' => '数据维护中，请稍候再试！'];
		}
		
		$chance_type = $this->_checkFrequency();
		if($chance_type){
		
			$prize_arr = $this->_prize_arr();
			
			foreach ($prize_arr as $key => $val) {   
				$arr[$val['no']] = $val['probability'];   
				if($val['is_luck'] == 0){
					//不中奖的编号
					$unluck_rid = $val['no'];
				}
			}   
			$rid = $this->_get_rand($arr); //根据概率获取奖项id   
			/*
			//如是黑名单用户，修改为不中奖
			if(M('luckdraw_blacklist')->field('id')->where(['uid'=>$this->uid])->find()){
				$rid = $unluck_rid;
			}
			*/
			//当天0点到0点5分内，不中奖
            $unluck_time = strtotime(date('Y-m-d'))+300;
            $now_time = time();
            if($now_time<$unluck_time){
                $rid = $unluck_rid;
            }

			$res['yes'] = $prize_arr[$rid-1]['prize_name']; //中奖项   
			$res['yes_is_luck'] = $prize_arr[$rid-1]['is_luck']; //是否中奖   
			$res['yes_prize_type'] = $prize_arr[$rid-1]['prize_type']; //奖品类型
			$res['yes_angle'] = $this->_get_angle($prize_arr[$rid-1]['no']); //中奖项角度   angle
			$res['chance_type'] = $chance_type;


            $do = M();
            $do->startTrans();

			//抽奖机会减一
			if($chance_type == 'free_chance'){
				$free_list = M('luckdraw_chance_free')->field('id')->where('atime > "'.$now_day.'" and uid = "'.$this->uid.'" and status = 1')->order('atime asc')->find();
				if(!$this->sw[] = M('luckdraw_chance_free')->where('id="'.$free_list['id'].'"')->data(['status'=>2])->save()){
				    goto error;
                }
				if(!$this->sw[] = M('luckdraw_chance')->where('uid = '.$this->uid)->setDec($chance_type)){
				    goto error;
                }
			}else if($chance_type == 'tangbao_chance'){
				if(!$this->sw[] = M('luckdraw_chance')->where('uid = '.$this->uid)->setDec($chance_type)){
				    goto error;
                }
			}
			
			//记录抽奖的信息
			$data['uid'] = $this->uid;
			$data['no'] = $this->create_orderno('LC',$this->uid);
			
			$data['chance_type'] = $chance_type == 'tangbao_chance' ? 2 : 1;
			$data['prize_id'] = $prize_arr[$rid-1]['id'];
			$data['prize_name'] = $prize_arr[$rid-1]['prize_name'];
			$data['images'] = $prize_arr[$rid-1]['images'];
			$data['is_luck'] = $prize_arr[$rid-1]['is_luck'];
			$data['prize_type'] = $prize_arr[$rid-1]['prize_type'];
			$data['express_price'] = $prize_arr[$rid-1]['express_price'];
			$data['score'] = $prize_arr[$rid-1]['score'];
			
			if(!$this->sw[] = $res['id'] = M('luckdraw_list')->add($data)){
			    goto error;
            }

			//记录中奖信息
			if($data['is_luck'] == 1){
				$user_info = M('user')->field('mobile')->find($this->uid);
				$data2['uid'] = $this->uid;
				$data2['ac_type'] = 1;
				$data2['mobile'] = $user_info['mobile'];
				$data2['prize_name'] = $prize_arr[$rid-1]['prize_name'];
				$data2['images'] = $prize_arr[$rid-1]['images'];
				$data2['no'] = $data['no'];
				$data2['award_url'] = $this->_award_url($data['no'],$this->uid);
				$data2['is_receive'] = 1;
				$data2['express_price'] = $prize_arr[$rid-1]['express_price'];
				//如果是实物奖品，生成付运费的订单号
				if($data['prize_type'] == 1){
					$data2['pay_express_no'] = $this->create_orderno('LL',$this->uid);
				//如果是积分奖品，生成领取积分的订单号
				}else if($data['prize_type'] == 2){
					$data2['score_no'] = $this->create_orderno('LS',$this->uid);
				}
				
				if(!$this->sw[] = M('winning_list')->add($data2)){
				    goto error;
                }
				$res['yes_award_url'] = $data2['award_url'];
				$res['no'] = $data['no'];
			}

			$do->commit();
			return ['code' => 1,'data' => $res];

            error:
                $do->rollback();
                return ['code' => 0,'msg' => '抽奖失败！'];
		}else{
			return ['code' => 4,'msg' => '没有抽奖次数'];
		}
    }
	/**
     * 给予用户的免费抽奖机会 暂无使用
     */
	public function _inside_give_free_chance(){
		return ['code' => 0,'msg'=>'操作失败'];
		$res = M('luckdraw_chance')->field('free_chance')->where('uid = '.$this->uid)->find();
		if($res){
			$res = M('luckdraw_chance')->where('uid = '.$this->uid)->setInc('free_chance',I('post.num'));
			if($res){
				return ['code' => 1,'msg'=>'免费机会已添加'];
			}else{
				return ['code' => 0,'msg'=>'免费机会增加失败'];
			}
		}else{
			
			$res = M('luckdraw_chance')->add(['uid'=>$this->uid,'free_chance'=>I('post.num')]);
			if($res){
				return ['code' => 1,'msg'=>'免费机会添加'];
			}else{
				return ['code' => 0,'msg'=>'新增账户失败'];
			}
		}
		
	}
	/**
     * 给予用户的免费抽奖机会
     */
	public function _give_free_chance(){
		$now_day = date('Y-m-d',time());
		$day7 = date('Y-m-d',time()-86400*7);
		
		$user_info = M('user')->field('id')->where('erp_uid = "'.I('post.erp_uid').'"')->find();


		//$num = I('post.num');
        $num =1;    //暂时只启用一次机会
		//默认为签到
		$type = 1;
		
		if($num < 1 || $num > 10){
			return ['code' => 3,'msg'=>'请传入1-10的次数'];
		}

		if(!$user_info){
			//查询erp是否存在该用户
			$user_info['id'] = A('Erp')->luckdraw_user_info(['erp_uid'=>I('post.erp_uid')]);
			if($user_info['id'] == 0){
				return ['code' => 4,'msg'=>'用户不存在'];
			}
			
		}
		
		if($type == 1){
			$res = M('luckdraw_chance_free')->where('atime > "'.$now_day.'" and uid = "'.$user_info['id'].'" and type = 1')->find();
			if($res){
				return ['code' => 2,'msg'=>'该用户当天已获得签到抽奖机会'];
			}
		}
		
		
		//增加记录表
        $do = M();
        $do->startTrans();
		//清除7天之前的抽奖机会记录
		if(false === $this->sw[] = $return['del_num'] =M('luckdraw_chance_free')->where(['uid'=>$user_info['id'],'_string'=>'date_format(`atime`,"%Y-%m-%d") < "'.$day7.'"'])->limit(5)->delete()){
			goto error;
			break;
		}
		for($i=1;$i<=$num;$i++){
			if(!$this->sw[] = M('luckdraw_chance_free')->add(['no'=>$this->create_orderno('LA',$this->uid),'uid'=>$user_info['id']])){
			    goto error;
                break;
            }
		}
		$res = M('luckdraw_chance')->field('free_chance')->where('uid = '.$user_info['id'])->find();
		$return['free_num'] = $free_num = M('luckdraw_chance_free')->where('atime > "'.$now_day.'" and uid = "'.$user_info['id'].'" and status = 1')->count();
		if($res){
			if(!$this->sw[] = M('luckdraw_chance')->where('uid = '.$user_info['id'])->setInc('free_chance',$free_num)){
			    goto error;
            }
		}else{
            if(!$this->sw[] = M('luckdraw_chance')->add(['uid'=>$user_info['id'],'free_chance'=>$free_num])){
                goto error;
            }
		}


		$do->commit();
        return ['code' => 1,'data'=>$return,'msg'=>'免费机会已添加！'];

        error:
            $do->rollback();
            return ['code' => 0,'data'=>$return,'msg'=>'免费机会增加失败！'];

	}
	/**
     * 唐宝兑换的抽奖机会
     */
	public function _give_tangbao_chance(){
		
		
		//生成唐宝兑换订单
		$data['order_no'] = $this->create_orderno('LT',$this->uid);
		$data['uid'] = $this->uid;
		$data['pay_status'] = 1;
		$data['tangbao'] = C('CFG.luckdraw')['luckdraw_tangbao'];
		
		$id = M('luckdraw_chance_exchange')->add($data);
		
		if(!$id){
			return ['code' => 0,'msg'=>'兑换失败！'];
		}
		
		$param['orderID'] = $data['order_no'];
		$param['tangbao'] = $data['tangbao'];
		
		$res = A('Erp')->luckdraw_tangbao_chance($param);
		//支付成功
		if($res->code==1){
		    $do = M();
            $do->startTrans();
			//将订单改为成功
			if(!$this->sw[] = M('luckdraw_chance_exchange')->where('id = '.$id)->data(['pay_status'=>2])->save()){
			    goto error;
            }
			//唐宝抽奖机会加一
			if(!$this->sw[] = M('luckdraw_chance')->where('uid = '.$this->uid)->setInc('tangbao_chance')){
			    goto error;
            }
            //$this->apiReturn(1,['msg'=>$res->info]);
            $do->commit();
            return ['code' => 1,'msg' => $res->info];

            error:
                $do->rollback();
                return ['code' => 0,'msg' => '兑换唐宝失败！'];

		}else return ['code' => $res->code,'msg' => $res->info];
	}
	/**
     * 领奖-获取奖品信息
     */
	public function _get_winning(){
		$res = $this->_award_check(I('post.uid'),I('post.no'));
		
		if($res){
			
			return ['code' => 1,'data'=>$res];
		}else{
			return ['code' => 0,'msg'=>'没有此奖品信息，或奖品已领取'];
		}
		
	}
	/**
     * 领奖操作
     */
	public function _award_winning(){
        $open = C('CFG.luckdraw')['luckdraw_open'];
        if(!$open){
            return ['code' => 0,'msg' => '数据维护中，请稍候再试！'];
        }
		$res = $this->_award_check(I('post.uid'),I('post.no'));
		if($res){
			//实物
			if($res['prize_type'] == 1){
				if(empty(I('post.addr'))){
					return ['code' => 0,'msg'=>'请选择收货地址'];
				}
				if(empty(I('post.pay_type'))){
					return ['code' => 0,'msg'=>'请选择支付方式'];
				}
				if(empty(I('post.safe_password'))){
					return ['code' => 0,'msg'=>'请填写安全密码'];
				}
				//检查安全密码
				$this->check_password_pay(I('post.safe_password'));
				
				//扣除运费/***********************/
				$param['orderID'] = $res['pay_express_no'];
				$param['payType'] = I('post.pay_type');
				$param['money'] = $res['express_price'];

                //查找发货地址
                $address_info = M('shopping_address')->where('id = '.I('post.addr').' and uid = '.$this->uid)->find();
                $data['province'] = $address_info['province'];
                $data['city'] = $address_info['city'];
                $data['district'] = $address_info['district'];
                $data['town'] = $address_info['town'];
                $data['street'] = $address_info['street'];
                $data['linkname'] = $address_info['linkname'];
                $data['tel'] = $address_info['tel'];
                $data['link_mobile'] = $address_info['mobile'];
                $data['postcode'] = $address_info['postcode'];
                $data['pay_type'] = I('post.pay_type');


                if(M('winning_list')->where('no = '.$res['no'])->data($data)->save() !== false){

                }else return ['code' => 0,'msg'=>'领取失败！'];

				$erp_res = A('Erp')->luckdraw_express_price($param);
				if($erp_res->code==1){

					//将中奖记录改为已领取
					$data2['is_receive'] = 2;
					if(M('winning_list')->where('no = '.$res['no'])->data($data2)->save()){
                        return ['code' => 1,'msg'=>$erp_res->info];
                    }else return ['code' => 0,'msg'=>'领取失败！'];

				}else{
					return ['code' => 0,'msg'=>$erp_res->info];
					
				}
				
				
			//积分
			}else if($res['prize_type'] == 2){
				//调用erp获取积分
				$param['orderID'] = $res['score_no'];
				$param['score'] = $res['score'];
				$erp_res = A('Erp')->luckdraw_award_score($param);
				if($erp_res->code==1){
					//将中奖记录改为已领取，已发放
					$data['is_receive'] = 2;
					$data['is_deliver'] = 2;
					M('winning_list')->where('no = '.$res['no'])->data($data)->save();
					
				}
				return ['code' => $erp_res->code,'data'=>$erp_res,'msg'=>$erp_res->info];
			}else{
				return ['code' => 0,'msg'=>'没有此奖品信息，或奖品已领取！'];
			}
		}else{
			return ['code' => 0,'msg'=>'没有此奖品信息，或奖品已领取'];
		}
		
	}
	/**
	* 物流跟踪
	* return $_POST['no'] 中奖编号 
	*/
	public function _query_express(){
		$cache_name='query_express_'.I('post.no');
		if(S($cache_name) == false){
			$winning_info = M('winning_list')->field('express_company_id,express_code,express_time')->where('no = '.I('post.no'))->find();
			$rs = M('express_company')->field('company,logo,code,website,tel')->find($winning_info['express_company_id']);
			$rs['express_code'] = $winning_info['express_code'];
			$rs['express_time'] = $winning_info['express_time'];
			if($rs){
				$url='https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.$winning_info['express_code'];
				$res=$this->curl_get($url);
				$res=json_decode($res);

				if($res) {
					$rs['express'] = $res;
					//S($cache_name, $rs);
				}
				
			}else{
				//找不到快递公司
				return ['code' => 0,'msg'=>'没有物流信息'];
			}		
		}else{
			$rs = S($cache_name);
		}
		return ['code' => 1,'data'=>$rs];
	}
	
	/**
     * 清除免费机会以及将3天没有领取奖品修改为已过期
     */
	public function _timed_task(){
		$day3 = date('Y-m-d',time()-86400*3);
		$day1 = date('Y-m-d',time()-86400);
		$end = date('Y-m-d',time());
		$update_limit = 2000;//更新条数
		$sleep_time = 10000;//每次更新睡眠时间
		
		//将昨天获得的免费抽奖机会清空
		$clean_count = M('luckdraw_chance_free')->where('atime < "'.$end.'" and status = 1')->count();
		$num = ceil($clean_count/$update_limit);
		
		$result['free_chance'] = $clean_count;//需要更新的数量
		$result['clear_free_chance']=0;//成功的更新数量
		for($i=1;$i<=$num;$i++){
			$clear_free_chance = M('luckdraw_chance_free')->where('atime < "'.$end.'" and status = 1')->data(['status'=>3])->limit($update_limit)->save();
			if($clear_free_chance === false){
				$result['clear_free_chance_false'][] = M('luckdraw_chance_free')->getlastsql();
			}else{
				$result['clear_free_chance'] += $clear_free_chance;
			}
			usleep($sleep_time);
		}
		
		//将3天内没有领取的奖品修改为已过期
		$clean_count = M('winning_list')->where('atime < "'.$day3.'" and is_receive = 1')->count();
		$num = ceil($clean_count/$update_limit);
		
		$result['winning_overdue'] = $clean_count;//需要更新的数量
		$result['clear_winning_overdue'] = 0;//成功的更新数量
		for($i=1;$i<=$num;$i++){
			$clear_winning_overdue = M('winning_list')->where('atime < "'.$day3.'" and is_receive = 1')->data(['is_receive'=>3])->limit($update_limit)->save();
			if($clear_winning_overdue === false){
				$result['clear_winning_overdue_false'][] = M('winning_list')->getlastsql();
			}else{
				$result['clear_winning_overdue'] += $clear_winning_overdue;
			}
			usleep($sleep_time);
		}

		return ['code' => 1,'data'=>$result];
	}
	
	/**
     * 统计抽奖数据
     * @string $day  年月日 XXXX-XX-XX格式
     */
	public function _luckdraw_statistics(){
	    $day = I('post.day');
        $day = empty($day) ? date('Y-m-d',time()-86400) : $day;
        $where1['_string'] = $where2['_string'] = $where3['_string'] = $where4['_string'] = $where5['_string'] = 'date_format(`atime`,"%Y-%m-%d") = "'.$day.'"';

        $data['day'] = $day;


        //抽奖次数
        $data['luck_num'] = M('luckdraw_list')->where($where1)->count();

        $where2['is_luck'] = 1;
        //中奖次数
        $data['winning_num'] = M('luckdraw_list')->field('uid')->where($where2)->count();

        //中奖积分总数
        $data['winning_score'] = M('luckdraw_list')->field('uid')->where($where2)->sum('score');
        $data['winning_score'] = $data['winning_score'] ? $data['winning_score'] : 0;

        //消耗唐宝
        $where3['chance_type'] = 2;
        $use_tangbao_num = M('luckdraw_list')->field('uid')->where($where3)->count();
        $data['use_tangbao'] = $use_tangbao_num * C('CFG.luckdraw')['luckdraw_tangbao'];

        //实物中奖次数
        $where4['pay_express_no'] = ['neq',0];
        $data['entity_winning_num'] = M('winning_list')->where($where4)->count();

        //实物领奖次数
        $where4['is_receive'] = 2;
        $data['entity_receive_num'] = M('winning_list')->where($where4)->count();

        //已付款运费
        $data['pay_express_price'] = M('winning_list')->where($where4)->sum('express_price');
        $data['pay_express_price'] = $data['pay_express_price'] ? $data['pay_express_price'] : 0;

        //实物领奖率
        $data['entity_receive_percen'] = $data['entity_receive_num']/$data['entity_winning_num']*100;



        //检查是否已经存在数据
        $check = M('luckdraw_statistics')->field('id')->where(['day'=>$day])->find();
        if(!$check) {
            if(!$r = M('luckdraw_statistics')->add($data)){
                return ['code' => 0,'msg'=>'基本统计添加失败','data'=>$data];
            }
        }


        //奖品比例
        $data['prize_count'] = M('winning_list')->field('prize_name,count(prize_name) as winning_num,count(if(is_receive = 2,prize_name,null)) as receive_num')->where($where1)->group('prize_name')->select();

        foreach($data['prize_count'] as $k => $v){
            $data['prize_count'][$k]['day'] = $day;
            $data['prize_count'][$k]['receive_percen'] = $v['receive_num']/$v['winning_num']*100;
        }
        if(!$r = M('luckdraw_prize_statistics')->addAll($data['prize_count'])){
            return ['code' => 0,'msg'=>'奖品统计添加失败','data'=>$data];
        }

        return ['code' => 1,'data'=>$data];


	}
	/********************************************/
	//
	/**
     * 检查是否中奖
	 * @param int $uid 中奖的用户id
	 * @param int $no  中奖的订单号
     */
	public function _award_check($uid,$no){
		//比对中奖用户和当前登录用户是否一致
		if($this->uid != $uid){
			return false;
		}
		$res = M('winning_list')->field('no,prize_name,images,score_no,express_price,pay_express_no,is_receive,is_deliver,express_company,express_code,express_time')->where('uid = '.$this->uid.' and no = "'.$no.'"')->find();
		if($res){
			//查找奖品是否实物
			$list = M('luckdraw_list')->field('prize_type,score')->where(['no' => $res['no']])->find();
            if($list) $res = array_merge($res,$list);
			return $res;
		}else{
			return false;
		}
		
	}
	/**
     * 生成领奖url
	 * @param int $no  中奖的订单号
	 * @param int $uid 中奖的用户id
	 * return string $url 生成的url
     */
	public function _award_url($no,$uid){
		$url = 'http';
		if ($_SERVER["HTTPS"] == "on") {
			$url .= "s";
		}
		$url .= '://wap.'.C('DOMAIN').'/Luckdraw/award/n/'.$no.'/u/'.$uid;
		return $url;
	}
	
	/**
     * 获取中奖id的角度
	 * @param int $no  奖品的顺序
	 * return int $angle 转盘的角度
     */
	public function _get_angle($no){
		$start = ($no - 1)*45+5;
		$end = $no*45-5;
		$angle = rand($start,$end);
		return $angle;
	}
	/**
     * 获取奖品清单
	 * return array $list 奖品清单
     */
	public function _prize_arr(){
		/* 
		 * 奖项数组 
		 * 是一个二维数组，记录了所有本次抽奖的奖项信息， 
		 * 其中id表示中奖等级，prize表示奖品，v表示中奖概率。 
		 * 注意其中的v必须为整数，你可以将对应的 奖项的v设置成0，即意味着该奖项抽中的几率是0， 
		 * 数组中v的总和（基数），基数越大越能体现概率的准确性。 
		 * 本例中v的总和为100，那么平板电脑对应的 中奖概率就是1%， 
		 * 如果v的总和是10000，那中奖概率就是万分之一了。 
		 */ 
		  /*
		$prize_arr = array(   
			'0' => array('id'=>1,'prize'=>'下次没准就能中哦','v'=>925),   
			'1' => array('id'=>2,'prize'=>'1000积分','v'=>10),   
			'2' => array('id'=>3,'prize'=>'5000积分','v'=>2),   
			'3' => array('id'=>4,'prize'=>'自拍杆','v'=>30),   
			'4' => array('id'=>5,'prize'=>'收纳包','v'=>20),   
			'5' => array('id'=>6,'prize'=>'毛巾','v'=>8),   
			'6' => array('id'=>7,'prize'=>'毛巾被','v'=>4),   
			'7' => array('id'=>8,'prize'=>'IPhone7','v'=>1),   
		); 
		
*/		
		$list = M('luckdraw_prize')->field('id,images,prize_name,probability,prize_type,is_luck,express_price,score')->where('status = 1')->order('sort asc,id asc')->limit(8)->select();
		$no = 1;
		foreach($list as $k=>$v){
			$list[$k]['no'] = $no;
			$no++;
		}

		return $list;
	}
	/* 
	 * 经典的概率算法， 
	 * $proArr是一个预先设置的数组， 
	 * 假设数组为：array(100,200,300，400)， 
	 * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，  
	 * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间， 
	 * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。 
	 * 这样 筛选到最终，总会有一个数满足要求。 
	 * 就相当于去一个箱子里摸东西， 
	 * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。 
	 * 这个算法简单，而且效率非常 高， 
	 * 关键是这个算法已在我们以前的项目中有应用，尤其是大数据量的项目中效率非常棒。 
	 */  
	public function _get_rand($proArr) {   
		$result = '';    
		//概率数组的总概率精度   
		$proSum = array_sum($proArr);    
		//概率数组循环   
		foreach ($proArr as $key => $proCur) {   
			$randNum = mt_rand(1, $proSum);   
			if ($randNum <= $proCur) {   
				$result = $key;   
				break;   
			} else {   
				$proSum -= $proCur;   
			}         
		}   
		unset ($proArr);    
		return $result;   
	}   
	
}