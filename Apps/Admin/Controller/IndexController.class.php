<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
        if(session('admin.id')){
			$do=D('AdminMenu');
			$list=$do->all(array('id'=>array('in',C('admin.menuid')),'status'=>1));
			$this->assign('list',$list);

		}
	
		$this->display();
    }

    public function main(){
		
		$day1  =   date('Y-m-d',time()-86400);   //昨天
		$day2  =   date('Y-m-d',time()-86400*2); //前天
		$day7 = date('Y-m-d',time()-86400*7);
		
		$totals_basic_week = M('totals_basic')->cache(true)->field('sum(member) as week_member,sum(open_store_success) as week_open_store,sum(goods_num) as week_goods_num')->where(['_string' => 'date_format(atime,"%Y-%m-%d")>"'.$day7.'"'])->order('day desc')->find();
		$totals_basic = M('totals_basic')->cache(true)->field('member,total_member,open_store_success,normal_store,goods_num,online_goods_num')->order('day desc')->find();
		$totals_basic['store_examine'] = M('shop_join_info')->cache(true)->where(['status' => 0])->count();
		$totals_basic['goods_illegl'] = M('goods')->cache(true)->where(['status'=>4])->count();
		$totals_basic['goods_badimg'] = M('goods')->cache(true)->where(['status'=>3])->count();
		
		$totals_trans_week = M('totals_trans')->cache(true)->field('sum(order_success) as week_order_success')->where(['_string' => 'date_format(atime,"%Y-%m-%d")>"'.$day7.'"'])->order('day desc')->find();
		$totals_trans = M('totals_trans')->cache(true)->field('order_num_total,order_success,success_order_total')->order('day desc')->find();
		$totals_trans['refund'] = M("refund")->cache(true)->where(['type'=>array(neq,1),'_string' => 'date_format(accept_time,"%Y-%m-%d")="0000-00-00" and date_format(cancel_time,"%Y-%m-%d")="0000-00-00"'])->count();
		$totals_trans['refund2'] =M("refund")->cache(true)->where(['type'=>1,'_string' => 'date_format(accept_time,"%Y-%m-%d")="0000-00-00" and date_format(cancel_time,"%Y-%m-%d")="0000-00-00"'])->count();
		
		
		$totals_promotion_week = M('totals_promotion')->cache(true)->field('sum(ad_num) as week_ad_num')->where(['_string' => 'date_format(atime,"%Y-%m-%d")>"'.$day7.'"'])->order('day desc')->find();
		$totals_promotion = M('totals_promotion')->cache(true)->field('ad_num,ad_total_num')->order('day desc')->find();
		$totals_promotion['sucai'] =M('ad_sucai')->cache(true)->where(['status' => 0])->count();
		
		$this->assign('totals_basic_week',$totals_basic_week);
		$this->assign('totals_basic',$totals_basic);
		$this->assign('totals_trans_week',$totals_trans_week);
		$this->assign('totals_trans',$totals_trans);
		$this->assign('totals_promotion_week',$totals_promotion_week);
		$this->assign('totals_promotion',$totals_promotion);

		
		//商城昨日和前天销售金额统计
		$day_shop_data1 = M('totals_shop')->cache(true)->field('money_pay,shop_name,shop_id')->join('LEFT JOIN '.C('DB_PREFIX').'shop on '.C('DB_PREFIX').'totals_shop.shop_id = '.C('DB_PREFIX').'shop.id')->where('day="'.$day1.'"')->order('money_pay DESC')->limit(10)->select();
		foreach($day_shop_data1 as $key=>$val){
			$day_shop_data2[$key] = M('totals_shop')->cache(true)->field('money_pay,shop_name')->join('LEFT JOIN '.C('DB_PREFIX').'shop on '.C('DB_PREFIX').'totals_shop.shop_id = '.C('DB_PREFIX').'shop.id')->where('day="'.$day2.'" and shop_id="'.$val['shop_id'].'"')->find();
		}
		$this->assign('day_shop_data1',$day_shop_data1);
		$this->assign('day_shop_data2',$day_shop_data2);
		
		//商城累计销售金额统计
		$total_shop_data = M('shop')->cache(true)->field('total_money_pay,shop_name')->order('total_money_pay DESC')->limit(10)->select();
		$this->assign('total_shop_data',$total_shop_data);
	
		//在线雇员
        $this->assign('online_user',S('online_admin'));

		//手机充值统计
		//总和
        /*
		$do = M('mobile_orders_totals');
		$result = $do->field('sum(num_totals) as num_totals,sum(money_totals) as money_totals,sum(score_totals) as score_totals,sum(recharge_success_num) as recharge_success_num,sum(recharge_success_money) as recharge_success_money,sum(recharge_success_avg) as recharge_success_avg,sum(recharge_success_score) as recharge_success_score,sum(fare_totals_num) as fare_totals_num,sum(flow_totals_num) as flow_totals_num,sum(fare_totals_money) as fare_totals_money,sum(flow_totals_money) as flow_totals_money,sum(fare_totals_score) as fare_totals_score,sum(flow_totals_score) as flow_totals_score,sum(flow_totals_score) as flow_totals_score,sum(fare_balance_pay_num) as fare_balance_pay_num,sum(fare_weixin_pay_num) as fare_weixin_pay_num,sum(fare_alipay_pay_num) as fare_alipay_pay_num,sum(fare_bank_pay_num) as fare_bank_pay_num,sum(fare_pc_recharge_num) as fare_pc_recharge_num,sum(fare_wap_recharge_num) as fare_wap_recharge_num,sum(fare_ios_recharge_num) as fare_ios_recharge_num,sum(fare_android_recharge_num) as fare_android_recharge_num,sum(fare_success_num) as fare_success_num,sum(fare_success_money) as fare_success_money,sum(fare_success_score) as fare_success_score,sum(flow_success_num) as flow_success_num,sum(flow_success_money) as flow_success_money,sum(flow_success_score) as flow_success_score,sum(fare_success_avg) as fare_success_avg,sum(flow_success_avg) as flow_success_avg,sum(flow_balance_pay_num) as flow_balance_pay_num,sum(flow_weixin_pay_num) as flow_weixin_pay_num,sum(flow_alipay_pay_num) as flow_alipay_pay_num,sum(flow_bank_pay_num) as flow_bank_pay_num,sum(flow_pc_recharge_num) as flow_pc_recharge_num,sum(flow_wap_recharge_num) as flow_wap_recharge_num,sum(flow_ios_recharge_num) as flow_ios_recharge_num,sum(flow_android_recharge_num) as flow_android_recharge_num,sum(fare_move_operator_num) as fare_move_operator_num,sum(flow_move_operator_num) as flow_move_operator_num,sum(fare_unicom_operator_num) as fare_unicom_operator_num,sum(flow_unicom_operator_num) as flow_unicom_operator_num,sum(fare_telecom_operator_num) as fare_telecom_operator_num,sum(flow_telecom_operator_num) as flow_telecom_operator_num,sum(flow_pay_num) as flow_pay_num,sum(fare_pay_num) as fare_pay_num,sum(pay_totals) as pay_totals')->find();

		$last_mobile_totals = $do->order('id desc,day')->find();

		//话费成功充值运营商
		$type1['title'] = array(
			'text'    => "话费成功充值运营商",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type1['legend'] =array(
			0 =>'移动',
			1 =>'联通',
			2 =>'电信',
		);
		$type1["x_title"] = "left";      //legend 位置(left,right,center)
		$type1["name"]    = "运营商";  //图表用途名称
		$type1['data'] = array(//数据

			array(
				'value' => $last_mobile_totals['fare_move_operator_num']?$last_mobile_totals['fare_move_operator_num']:0,
				'name'  => "移动",
			),
			array(
				'value' => $last_mobile_totals['fare_unicom_operator_num']?$last_mobile_totals['fare_unicom_operator_num']:0,
				'name'  => "联通",
			),
			array(
				'value' => $last_mobile_totals['fare_telecom_operator_num']?$last_mobile_totals['fare_telecom_operator_num']:0,
				'name'  => "电信",
			),
		);
		
		//话费支付方式
		$type3['title'] = array(
			'text'    => "话费充值支付方式",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type3['legend'] =array(
			0 =>'余额',
			1 =>'支付宝',
			2 =>'微信',
			3 =>'网银',
		);
		$type3["x_title"] = "left";      //legend 位置(left,right,center)
		$type3["name"]    = "支付方式";  //图表用途名称
		$type3['data'] = array(//数据
			array(
				'value' => $last_mobile_totals['fare_balance_pay_num']?$last_mobile_totals['fare_balance_pay_num']:0,
				'name'  => "余额",
			),
			array(
				'value' => $last_mobile_totals['fare_alipay_pay_num']?$last_mobile_totals['fare_alipay_pay_num']:0,
				'name'  => "支付宝",
			),
			array(
				'value' => $last_mobile_totals['fare_weixin_pay_num']?$last_mobile_totals['fare_weixin_pay_num']:0,
				'name'  => "微信",
			),
			array(
				'value' => $last_mobile_totals['fare_bank_pay_num']?$last_mobile_totals['fare_bank_pay_num']:0,
				'name'  => "网银",
			),
		);
		
		//话费充值下单渠道
		$type2['title'] = array(
			'text'    => "话费充值下单渠道",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type2['legend'] =array(
			0 =>'pc',
			1 =>'wap',
			2 =>'ios',
			3 =>'android',
		);
		$type2["x_title"] = "left";      //legend 位置(left,right,center)
		$type2["name"]    = "下单渠道";  //图表用途名称
		$type2['data'] = array(//数据
			array(
				'value' => $last_mobile_totals['fare_pc_recharge_num']?$last_mobile_totals['fare_pc_recharge_num']:0,
				'name'  => "pc",
			),
			array(
				'value' => $last_mobile_totals['fare_wap_recharge_num']?$last_mobile_totals['fare_wap_recharge_num']:0,
				'name'  => "wap",
			),
			array(
				'value' => $last_mobile_totals['fare_ios_recharge_num']?$last_mobile_totals['fare_ios_recharge_num']:0,
				'name'  => "ios",
			),
			array(
				'value' => $last_mobile_totals['fare_android_recharge_num']?$last_mobile_totals['fare_android_recharge_num']:0,
				'name'  => "android",
			),
		);	


		//流量成功充值运营商
		$type4['title'] = array(
			'text'    => "流量成功充值运营商",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type4['legend'] =array(
			0 =>'移动',
			1 =>'联通',
			2 =>'电信',
		);
		$type4["x_title"] = "left";      //legend 位置(left,right,center)
		$type4["name"]    = "运营商";  //图表用途名称
		$type4['data'] = array(//数据

			array(
				'value' => $last_mobile_totals['flow_move_operator_num']?$last_mobile_totals['flow_move_operator_num']:0,
				'name'  => "移动",
			),
			array(
				'value' => $last_mobile_totals['flow_unicom_operator_num']?$last_mobile_totals['flow_unicom_operator_num']:0,
				'name'  => "联通",
			),
			array(
				'value' => $last_mobile_totals['flow_telecom_operator_num']?$last_mobile_totals['flow_telecom_operator_num']:0,
				'name'  => "电信",
			),
		);
		
		//流量支付方式
		$type5['title'] = array(
			'text'    => "流量充值支付方式",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type5['legend'] =array(
			0 =>'余额',
			1 =>'支付宝',
			2 =>'微信',
			3 =>'网银',
		);
		$type5["x_title"] = "left";      //legend 位置(left,right,center)
		$type5["name"]    = "支付方式";  //图表用途名称
		$type5['data'] = array(//数据
			array(
				'value' => $last_mobile_totals['flow_balance_pay_num']?$last_mobile_totals['flow_balance_pay_num']:0,
				'name'  => "余额",
			),
			array(
				'value' => $last_mobile_totals['flow_alipay_pay_num']?$last_mobile_totals['flow_alipay_pay_num']:0,
				'name'  => "支付宝",
			),
			array(
				'value' => $last_mobile_totals['flow_weixin_pay_num']?$last_mobile_totals['flow_weixin_pay_num']:0,
				'name'  => "微信",
			),
			array(
				'value' => $last_mobile_totals['flow_bank_pay_num']?$last_mobile_totals['flow_bank_pay_num']:0,
				'name'  => "网银",
			),
		);
		
		//流量充值下单渠道
		$type6['title'] = array(
			'text'    => "流量充值下单渠道",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type6['legend'] =array(
			0 =>'pc',
			1 =>'wap',
			2 =>'ios',
			3 =>'android',
		);
		$type6["x_title"] = "left";      //legend 位置(left,right,center)
		$type6["name"]    = "下单渠道";  //图表用途名称
		$type6['data'] = array(//数据
			array(
				'value' => $last_mobile_totals['flow_pc_recharge_num']?$last_mobile_totals['flow_pc_recharge_num']:0,
				'name'  => "pc",
			),
			array(
				'value' => $last_mobile_totals['flow_wap_recharge_num']?$last_mobile_totals['flow_wap_recharge_num']:0,
				'name'  => "wap",
			),
			array(
				'value' => $last_mobile_totals['flow_ios_recharge_num']?$last_mobile_totals['flow_ios_recharge_num']:0,
				'name'  => "ios",
			),
			array(
				'value' => $last_mobile_totals['flow_android_recharge_num']?$last_mobile_totals['flow_android_recharge_num']:0,
				'name'  => "android",
			),
		);
        $this->assign('type1',$type1);        
        $this->assign('type2',$type2);        
        $this->assign('type3',$type3);
        $this->assign('type4',$type4);        
        $this->assign('type5',$type5);        
        $this->assign('type6',$type6);
		$this->assign('result',$result);*/
		$this->display();
	
		
		
		
		
		/*
    	$do=M('user');
    	$get_data = M('totals');
    	
    	$week = array();
    	$sql = 'SELECT SUM(member) as member,sum(open_store_success) as open_store_success,sum(goods_num) as goods_num,sum(order_num) as order_num,sum(ad_num) as ad_num FROM `ylh_totals` where day BETWEEN "'.date('Y-m-d',(time()-86400*7)).'" and "'.date('Y-m-d').'"';
    	$result = $get_data->query($sql);
    	$all_data = $get_data->order('day desc')->find();

        $week['member'] = $result[0]['member'];
        $week['open_store_success'] = $result[0]['open_store_success'];
        $week['goods_num'] = $result[0]['goods_num'];
        $week['order_num'] = $result[0]['order_num'];
        $week['ad_num']    = $result[0]['ad_num'];
        
    	//用户
    	$total['user']['all']		=$do->count();
    	$total['user']['week']		=$week['member'];
    	$total['user']['yestoday']	=$all_data['member'];


    	//店铺
    	$do=M('shop');
    	$total['shop']['all']		=$do->count();
    	$total['shop']['week']		=$week['open_store_success'];
    	$total['shop']['yestoday']	=$all_data['open_store_success'];
    	$total['shop']['join']		=M('shop_join_info')->where(['status' => 0])->count();

    	//商品
    	$do=M('goods');
    	$total['goods']['all']		=$do->count();
    	$total['goods']['week']		=$week['goods_num'];
    	$total['goods']['yestoday']	=$all_data['goods_num'];
    	$total['goods']['illegl']	=$do->where(['status'=>4])->count();
    	$total['goods']['badimg']	=$do->where(['status'=>3])->count();
    	$total['goods']['online_goods_num'] = $do->where(["_string" => "status=1"])->count();

    	//交易
    	$do=M('orders_shop');
    	$total['orders']['all']		=$do->count();
    	$total['orders']['week']    =$week['order_num'];
    	$total['orders']['yestoday']=$all_data['order_num'];
    	$total['orders']['refund']  =M("refund")->where(['type'=>array(neq,1),'_string' => 'date_format(accept_time,"%Y-%m-%d")="0000-00-00" and date_format(cancel_time,"%Y-%m-%d")="0000-00-00"'])->count();
    	$total['orders']['refund2']	=M("refund")->where(['type'=>1,'_string' => 'date_format(accept_time,"%Y-%m-%d")="0000-00-00" and date_format(cancel_time,"%Y-%m-%d")="0000-00-00"'])->count();
    	$total['orders']['success_order_total'] = $all_data['success_order_total'];
    	
    	//广告
    	$do=M('ad');
    	$total['ad']['all']			=$do->where(["is_default"=>0])->count();
    	$total['ad']['week']		=$week['ad_num'];
    	$total['ad']['yestoday']	=$all_data['ad_num'];
    	$total['ad']['sucai']       =M('ad_sucai')->where(['status' => 0])->count();
		
		//代购
    	$total['daigou']['pc_daigou_order']		   = $all_data['pc_daigou_order'];
    	$total['daigou']['wap_daigou_order']	   = $all_data['wap_daigou_order'];
    	$total['daigou']['total_daigou_order']	   = $all_data['total_daigou_order'];
    	$total['daigou']['pc_pay_daigou']          = $all_data['pc_pay_daigou'];
    	$total['daigou']['wap_pay_daigou']		   = $all_data['wap_pay_daigou'];
    	$total['daigou']['total_pay_daigou']	   = $all_data['total_pay_daigou'];
    	$total['daigou']['pc_pay_rate']	           = $all_data['pc_pay_rate'];
    	$total['daigou']['wap_pay_rate']           = $all_data['wap_pay_rate'];
    	$total['daigou']['total_pay_rate']		   = $all_data['total_pay_rate'];
    	$total['daigou']['pc_daigou_total']		   = $all_data['pc_daigou_total'];
    	$total['daigou']['wap_daigou_total']	   = $all_data['wap_daigou_total'];
    	$total['daigou']['daigou_total_money']     = $all_data['daigou_total_money'];	
    	$total['daigou']['pc_daigou_avg_price']	   = $all_data['pc_daigou_avg_price'];
    	$total['daigou']['wap_daigou_avg_price']   = $all_data['wap_daigou_avg_price'];
    	$total['daigou']['total_daigou_avg_price'] = $all_data['total_daigou_avg_price'];
		$total['daigou']['daigou_alipay_pay']	   = $all_data['daigou_alipay_pay'];
    	$total['daigou']['daigou_tangbao_pay']     = $all_data['daigou_tangbao_pay'];
    	$total['daigou']['daigou_money_pay']       = $all_data['daigou_money_pay'];
		
    	$this->assign('total',$total);
		
		//商城累计销售金额统计
		$do=M('shop');
		$total_shop_data = $do->cache(true)->field('total_money_pay,shop_name')->order('total_money_pay DESC')->limit(10)->select();
		//dump($total_shop_data);
		$this->assign('total_shop_data',$total_shop_data);
		
		//商城昨日和前天销售金额统计
		$day1  =   date('Y-m-d',time()-86400);   //昨天
		$day2  =   date('Y-m-d',time()-86400*2); //前天

		$do=M('totals_shop');
		$day_shop_data1 = $do->cache(true)->field('money_pay,shop_name,shop_id')->join('LEFT JOIN '.C('DB_PREFIX').'shop on '.C('DB_PREFIX').'totals_shop.shop_id = '.C('DB_PREFIX').'shop.id')->where('day="'.$day1.'"')->order('money_pay DESC')->limit(10)->select();
		foreach($day_shop_data1 as $key=>$val){
			$day_shop_data2[$key] = $do->cache(true)->field('money_pay,shop_name')->join('LEFT JOIN '.C('DB_PREFIX').'shop on '.C('DB_PREFIX').'totals_shop.shop_id = '.C('DB_PREFIX').'shop.id')->where('day="'.$day2.'" and shop_id="'.$val['shop_id'].'"')->find();
		}
 		
		//dump($day_shop_data1);
		//dump($day_shop_data2); 
		$this->assign('day_shop_data1',$day_shop_data1);
		$this->assign('day_shop_data2',$day_shop_data2);
		
        //在线雇员
        //$online_user = M('admin_online')->select();
        $online_user = S('online_admin');
        $this->assign('online_user',$online_user);

    	$this->display();
		*/
    }

    public function offline(){
        $do     = M('qa');
        $count  = $do->where(['status' => 1])->count();
        $limit  = rand(0,$count-1);
        $rs     = $do->where(['status' => 1])->field('id,question')->limit($limit.',1')->select();
        $this->assign('rs',$rs[0]);

        $this->display();
    }

    public function offline_save(){
        //只有管理员才可以踢人下线
        if(!in_array(session('admin.sid'),array(100810427,1))){
            $this->ajaxReturn(['status' => 'warning','msg' => '只有管理员才可以踢用户下线！']);
        }

        $success = array('恭喜您答对了！','算你还有点本事！','嗯，我们走着瞧！');
        $error = array('没文化，真可怕！','读多点书再来吧！','真失败，这么简单的问题都答错了！');

        if(I('post.answer')=='') $this->ajaxReturn(['status' => 'info','msg' => '请输入答案！']);
        $do = M('qa');
        if($do->where(['answer' => I('post.answer')])->find()){
            //M('admin_online')->where(['admin_id' => I('post.admin_id')])->delete();
            $online_admin = S('online_admin');
            if(!empty($online_admin[I('post.admin_id')])) {
                unset($online_admin[I('post.admin_id')]);
            }
            S('online_admin',$online_admin,0);

            $this->ajaxReturn(['status' => 'success','msg' => '答对了：'. $success[array_rand($success,1)]]);
        }else{
            $this->ajaxReturn(['status' => 'warning','msg' =>'答错了：'. $error[array_rand($error,1)]]);
        }
    }

    //在线雇员刷新时间
    public function online_updatetime(){
        //if(empty($_SESSION['admin'])) $this->ajaxReturn(['code' => 0,'msg' => '被踢下线或登录已失效！']);

        $online_admin = S('online_admin');
        if(!empty($online_admin[$_SESSION['admin']['id']])) $online_admin[$_SESSION['admin']['id']]['time'] = time();
        else{   //可能被人踢下线或memcached被清除
            //session('admin',null);
            //cookie('admin',null);
            //$this->ajaxReturn(['code' => 0,'msg' => '被踢下线或memcached已失效！']);
        }

        if($online_admin){  //清除登录时间超过5分钟未更新的雇员
            foreach($online_admin as $key => $val){
                if(time() - $val['time'] > (5 * 60)){
                    unset($online_admin[$key]);
                }
            }
        }

        S('online_admin',$online_admin,0);
    }

}