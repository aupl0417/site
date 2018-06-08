<?php
namespace Cart\Controller;
use Common\Builder\Pays;
use Common\Common\Tangpay;
use Common\Common\TestUser;
use Common\Form\Form;

class PayController extends AuthController {
	
	public function _initialize() {
		parent::_initialize();
	}
    public function index() {
        //支付是否已关闭
        if ($this->cfg['site']['is_pay'] != 1) {
            //$this->display(T('Home@Public:tips'), 'utf8', 'text/html', htmlTips($this->cfg['site']['is_pay_tips']));exit;
        }
        $this->authApi('/Erp/account')->with('account');
        //dump($this->_data);
        /**
         * 获取支付方式
         */
        $this->api('/erp/get_erp_paytype', []);
        $payType = $this->_data['data'];
        /*
        foreach ($payType as &$v) {
            $v['id']    =   Tangpay::PAY_TYPE[$v['pc_code']];
        }
        unset($v);
        */


        //dump($payType);

        $id     =   I('get.ordersid');
        $snos   =   '';
        $this->authApi('/Orders/view', array('o_no' => $id))->with();

        if($this->_data['data']['is_tangbao_pay'] == 0){    //是否禁用唐宝支付
            foreach ($payType as $key => $v) {
                if($v['pg_id'] == 2) unset($payType[$key]);
            }
        }
        $this->assign('payType', $payType);

        if ($this->_data['data']) {
            //检测卖家库存积分是否充足
            $not_seller=[];

            $shops = array_reduce($this->_data['data']['orders_shop'], function (&$shop, $val) {
                $shop[$val['seller']['openid']][] = $val;
                return $shop;
            });

            foreach ($shops as $key => $val) {
                $shopScore      = 0;
                $inventoryType  = 0;
                foreach ($val as $k => $v) {
                    $shopScore     += $v['score'];
                    $inventoryType  = $v['inventory_type'];
                    $snos          .= $v['s_no'] . ',';
                }
                if ($inventoryType == 1) {  //库存结算方式,0为非即时结束扣钱,1为即时结算扣库存积分
                    $this->authApi('/Erp/account',['openid'=>$key]);
                    $a_storeScore = $this->_data['data']['a_storeScore'];
                    if ($shopScore > $a_storeScore) {
                        $not_seller[] = $val[0]['seller'];
                        $sms_data     = [];
                        $sms_data['content']    = $this->sms_tpl(16,'{nick}',session('user.nick'));
                        $sms_data['mobile']     = $val[0]['seller']['mobile'];
                        sms_send($sms_data);
                    }
                }
            }
            /*foreach($this->_data['data']['orders_shop'] as $val){
                if ($val['inventory_type'] == 1) {  //库存结算方式,0为非即时结束扣钱,1为即时结算扣库存积分
                    $this->authApi('/Erp/account',['openid'=>$val['seller']['openid']]);
                    //dump($this->_data);
                    if($this->_data['data']['a_storeScore'] < $val['score']) {
                        $not_seller[]=$val['seller'];
                        //发送通知短信
                        $sms_data=[];
                        $sms_data['content']    =$this->sms_tpl(16,'{nick}',session('user.nick'));
                        $sms_data['mobile']     =$val['seller']['mobile'];
                        sms_send($sms_data);
                    }
                }
            }*/

            if (C('DEFAULT_THEME') == 'v2') {
                $config['action'] = U('/Payment/index');
                $config['attrs'] = ' target="_blank"';
                $form = Form::getInstance($config)
                    ->hidden(['name' => 'o_no', 'value' => I('get.ordersid')])
                    ->hidden(['name' => 'paytype', 'value' => $payType[0]['pg_id']])
                    ->submit(['title' => '立即支付', 'class' => 'pay_submit'])
                    ->create();
                $this->assign('form', $form);
            }


            $this->assign('not_seller',$not_seller);
            $this->assign('snos', rtrim($snos, ','));
            C('seo' ,['title' => '订单支付']);
            /**
             * 用户测试支付
             */
            //$fileName = in_array(session('user.id'), TestUser::UID) ? 'index1' : 'index';
            $fileName = 'index1';
            $this->display($fileName);
        } else {
            $this->display(T('Home@Empty:404'));
        }
    }

    /**
     * 检测订单是否支付成功
     */
	public function check() {
	    if (IS_POST) {
	        $ono = I('post.ono');
            $data= [
                'code'  =>  1,
                'msg'   =>  '支付成功',
            ];
            $map = [
                's_no'  =>  ['in', $ono],
                'is_pay'=>  1,
                'status'=>  2,
            ];

            $tmp = M('orders_shop')->where($map)->getField('id');

            if (false == $tmp) {
                $data= [
                    'code'  =>  0,
                    'msg'   =>  '支付失败',
                ];
            }
            $this->ajaxReturn($data);
        }
    }

}