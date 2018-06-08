<?php
/**
 * -------------------------------------------------
 * 购物车
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-21
 * -------------------------------------------------
 */
namespace Mobile\Controller;
class DtpayReturnController extends CommonController {

	/**
     * 合并订单同步返回
     */
    public function return_multi(){
        $id     = I('get.id');
        $token  = I('get._s');
        if ($this->check_return($id, $token) === true) {
            $res = $this->_multi_orders_check($id);
            if($res['code'] == 1) $this->redirect('/Orders/index/status/2');
            else $this->redirect('/Orders/index/status/1');
        }
        $this->redirect('/Orders/index/status/1');
    }
	/**
     * 合并付款订单是否支付成功
     * Create by 梁丰
     * 2017-03-06
     */
	public function multi_orders_check(){
		if(empty($_SESSION['user'])) $this->ajaxReturn(['code' => 10,'msg' => '请先登录！']);
        $res = $this->_multi_orders_check(I('post.o_no'));
        $this->ajaxReturn($res);
	}

	public function _multi_orders_check($o_no){
        $list   = M('orders_shop')->where(['o_no' => $o_no])->field('id,shop_id,s_no,status,uid')->select();
        $result = ['code' => 0,'msg' => '支付失败！','count' => count($list),'o_no' => $o_no,'success' => 0,'error' => 0];
		if(!empty($list)){
			foreach($list as $val){
				//在此次可考虑再次与ERP中的订单较对，防止异步出问题是可以修正
				//do samething……
				$orders['shop_id'] = $val['shop_id'];
				$orders['s_no'] = $val['s_no'];
				$orders['code'] = 0;
				$orders['msg'] = '支付失败！';
				
				if($val['status'] ==2 ){
					$result['success']++;
					$orders['code'] = 1;
					$orders['msg'] = '支付成功！';
				}elseif($val['status'] == 1){
					$res = $this->doApi2('/Erp/orders_in_erp_status',['s_no' => $val['s_no']]);
					//print_r($res);
					if($res['data']['o_orderState'] == 1){
						$tmp =  A('Cart/DtpayReturn')->fix_orders($val['s_no'],$val['uid'],$res['data']);
						if($tmp == true){
							$result['success']++;
							$orders['code'] = 1;
							$orders['msg'] = '支付成功！';
						}
					}
				}else{
					$result['error']++;
				}
				
				$result['orders'][] = $orders;
			}

			if($result['success'] == count($list)){
				$result['code'] = 1;
				$result['msg']  = '支付成功！';
			}else if($result['success'] > 0){
				$result['code'] = 2;
				$result['msg']  = '存在部分子订单支付失败！';
			}
		}
		//查询店铺信息
		if($result['code'] > 0){
			foreach($result['orders'] as $k => $v){
				$result['orders'][$k]['shop_name'] = M('shop')->where(['id'=>$v['shop_id']])->getField('shop_name');
			}
		}

        return $result;

    }
	
	/**
     * 单订单同步返回
     */
    public function return_single(){
        $id     = I('get.id');
        $token  = I('get._s');
		if ($this->check_return($id, $token) === true) {
            $res = $this->_single_orders_check($id);
			if($res['code'] == 1) $this->redirect('/Orders/index/status/2');
            else $this->redirect('/Orders/index/status/1');
        }
        $this->redirect('/Orders/index/status/1');
    }
	/**
     * 同步认证
     *
     * @param $string
     * @param $token
     * @return bool
     */
    public function check_return($string, $token)
    {
        return md5( $string . '0.13820200 1481726001' ) === $token;
    }
	/**
     * 单个订单付款，检查是否支付成功
     * Author: Lazycat
     * 2017-01-06
     */
    public function single_orders_check(){
		if(empty($_SESSION['user'])) $this->ajaxReturn(['code' => 10,'msg' => '请先登录！']);
        $res = $this->_single_orders_check(I('post.s_no'));
        $this->ajaxReturn($res);
    }

    public function _single_orders_check($s_no){
        $result = ['code' => 0,'msg' => '支付失败！','s_no' => $s_no];

        $rs = M('orders_shop')->where(['s_no' => $s_no])->field('id,status,s_no,uid')->find();
        if($rs['status'] == 2){
            $result['code'] = 1;
            $result['msg']  = '支付成功！';
        }elseif($rs['status'] == 1){
            //在此次可考虑再次与ERP中的订单较对，防止异步出问题是可以修正
            //do samething……
            $res = $this->doApi2('/Erp/orders_in_erp_status',['s_no' => $s_no]);
            if($res['data']['o_orderState'] == 1){
                $tmp = A('Cart/DtpayReturn')->fix_orders($s_no,$rs['uid'],$res['data']);
				
                if($tmp == true){
                    $result = ['code' => 1,'msg' => '支付成功！','s_no' => $s_no];
                }
            }
        }

        return $result;
    }

}