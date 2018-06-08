<?php
namespace Seller\Controller;
use Common\Builder\R;
use Common\Form\Form;

class OrdersController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 订单列表
     */
    public function index() {
        //$this->authApi('/Orders/orders_count')->with('count');//订单统计
        $ordersType  =   [
            1   =>  '待付款',
            2   =>  '待发货',
            3   =>  '已发货',
            4   =>  '已收货',
            5   =>  '已评价',
            //10  =>  '已关闭',
            //20  =>  '退货订单',
            //30  =>  '售后订单',
            40  =>  '待分账'
        ];
		if(I('get.s_no')) $map['s_no'] =I('get.s_no');
		if(I('get.sday')) $map['sday'] =I('get.sday');
		if(I('get.eday')) $map['eday'] =I('get.eday');
		if(I('get.nick')) $map['nick'] =I('get.nick');
		if(I('get.goods_name')) $map['goods_name'] =I('get.goods_name');
		$param = $map;
		$map['pagesize'] =15;
		/*
        $map    =   [
            's_no'          =>I('get.s_no'),
            'sday'          =>I('get.sday'),
            'eday'          =>I('get.eday'),
            'goods_name'    =>I('get.goods_name'),
            'nick'    		=>I('get.nick'),
            'pagesize'      =>15
        ];
		*/
		
        if (isset($_GET['sid']) && array_key_exists(I('get.sid'), $ordersType)) {
            $param['status'] = $map['status']  =   I('get.sid');
        }


        if ($param['status'] == 40) {   //分账
            $res = R::getInstance(['url' => '/erp/getSellerWaitPaymentOrderList', 'isAjax' => false, 'data' => ['pagesize' => 15, 'p' => I('get.p', 1, 'int')]])->auth();
            if ($res['code'] == 1) {
                $sid = array_column($res['data'], 'orderID');
                if (count($sid) < 15) $last = 1;
                if (!empty($sid)) $map['s_no'] = join(',', array_filter($sid));
            }
        }

        $this->authApi('/SellerOrders/orders_list', $map,'sday,eday,goods_name,s_no,pagesize,nick,receipt_time,s_no')->with();
		if(I('post.is_out_excel') == 1){
			//echo '<pre>';
			$order_ids = explode('|',I('post.order_ids'));
			foreach($this->_data['data']['list'] as $k => $v){
				if(in_array($v['id'],$order_ids)){
					//print_r($v);
					$tmp_array = array();
					
					$tmp_array['s_no'] = ' '.$v['s_no'];
					$tmp_array['pay_time'] = $v['pay_time'];
					$tmp_array['status_name'] = $v['status_name'];
					$tmp_array['goods_num'] = $v['goods_num'];
					$tmp_array['goods_price'] = $v['goods_price'];
					$tmp_array['express_price'] = $v['express_price'];
					$tmp_array['pay_price'] = $v['pay_price'];
					$tmp_array['linkname'] = $v['orders']['linkname'];
					$tmp_array['inventory_type'] = $v['inventory_type']==1?'即时结算':'非即时结束';
					$tmp_array['shop_name'] = $this->shop_info['shop_name'];
					$tmp_array['buyer_nick'] = $v['buyer']['nick'];
					
					
					$out_array[] = $tmp_array;
				}
				
			}
			
			$option = array(
				'A' => array('descript'=>'订单号','field'=>'s_no'),
				'B' => array('descript'=>'订单状态','field'=>'status_name'),
				'C' => array('descript'=>'付款时间','field'=>'pay_time'),
				'D' => array('descript'=>'商品数量','field'=>'goods_num'),
				'E' => array('descript'=>'商品总额','field'=>'goods_price'),
				'F' => array('descript'=>'运费','field'=>'express_price'),
				'G' => array('descript'=>'付款金额','field'=>'pay_price'),
				'H' => array('descript'=>'收货人名','field'=>'linkname'),
				'I' => array('descript'=>'结算方式','field'=>'inventory_type'),
				'J' => array('descript'=>'店铺名称','field'=>'shop_name'),
				'K' => array('descript'=>'会员名称','field'=>'buyer_nick'),
			);
			
			D('Admin/Excel')->outExcel($out_array,$option,'订单表');
			
			
		}else{
			$this->assign('param',$param);
			$this->assign('shop_type',session('user.shop_type'));
			$this->assign('ordersType', $ordersType);
			C('seo', ['title' => '第' . I('p',1, 'int') . '页_订单管理']);
			$this->assign('p', I('get.p', 1, 'int'));
			$this->assign('last', $last);
			$this->display();
		}
    }

    /**
     * subject: 数据导出
     * api: outExcel
     * author: liangfeng
     * day: 2017-06-02 20:04
     * [字段名,类型,是否必传,说明]
     */
    public function outExcel()
    {
		
		if(!I('post.type')){
			exit();
		}
		//开始时间
		if(I('post.sday')){
			$map['sday'] =I('post.sday');
		}else{
			$map['sday'] = date('Y-m-d H:i:s',time()-86400*7);
		}
		//结算时间
        if(I('post.eday')){
			$map['eday'] =I('post.eday');
		}else{
			$map['eday'] = date('Y-m-d H:i:s',time());
		}
	
		if(I('post.type') == 1){
			$where['seller_id'] = session('user.id');
			$where['status'] = ['in','2,3'];
			$where['_string'] = 'date_format(pay_time,"%Y-%m-%d")>"'.$map['sday'].'" and date_format(pay_time,"%Y-%m-%d")<"'.$map['eday'].'"';
			$res = M('orders_shop')->field('s_no,pay_time,express_code,goods_price,goods_price_edit,pay_price,express_price_edit,refund_price,uid')->where($where)->order('pay_time desc')->limit(500)->select();
			//dump(M()->getlastsql());
			//dump($res);
			//定义字段
			$fields = [
				['订单号','s_no'],
				['付款时间','pay_time'],
				['商品名称','goods_name'],
				['原价金额','total_price'],
				['原价总金额','orders_goods_price_edit'],
				['优惠金额','discount_price'],
				['优惠总金额','orders_discount_price'],
				['实际付款','pay_price'],
				['付款总金额','orders_pay_price'],
				['运费','orders_express_price'],
				['商品退款金额','refund_price'],
				['商品退款总金额','orders_refund_price'],
				['积分倍数','score_ratio'],
				['运单号','express_code'],
				['买家ID','uid'],
			];
			
			foreach($res as $v){
				//订单信息
				$tmp_array['s_no']          				= ' '.$v['s_no'];
				$tmp_array['pay_time']          			= $v['pay_time'];
				
				$tmp_array['orders_goods_price_edit']       = $v['goods_price'];
				$tmp_array['orders_discount_price']         = (string)round($v['goods_price']-$v['goods_price_edit'],2);
				$tmp_array['orders_pay_price']        		= $v['pay_price'];
				$tmp_array['orders_express_price']        	= $v['express_price_edit'];
				$tmp_array['orders_refund_price']        	= $v['refund_price'];
				$tmp_array['express_code']        			= $v['express_code'];
				$tmp_array['uid']        					= M('user')->where(['id'=>$v['uid']])->getField('nick');
				$out_array[]                = $tmp_array;
				
				$orders_goods = M('orders_goods')->field('goods_name,total_price,total_price_edit,refund_price,score_ratio')->where(['s_no'=>$v['s_no']])->select();
				foreach($orders_goods as $va){
					//订单商品信息
					$tmp_array2['goods_name']          			= $va['goods_name'];
					$tmp_array2['total_price']          		= $va['total_price'];
					$tmp_array2['discount_price']          		= (string)round($va['total_price']-$va['total_price_edit'],2);
					$tmp_array2['pay_price']          			= $va['total_price_edit'];
					$tmp_array2['refund_price']          		= $va['refund_price'];
					$tmp_array2['score_ratio']          		= ($va['score_ratio']*100).'%';
					$out_array[]                = $tmp_array2;
				}
				
			}
			
			$title = '进行中订单';
		}else if(I('post.type') == 2){
			$map['openid'] = session('user.openid');
			$map['type'] = 1;
			$this->authApi('/Erp/get_received_orders_list', $map,'sday,eday')->with();
			$res = $this->_data['data'];
			//dump($res);
			//定义字段
			$fields = [
				['订单号','s_no'],
				['确认收货时间','receipt_time'],
				['商品名称','goods_name'],
				['原价金额','total_price'],
				['原价总金额','orders_total_price'],
				['优惠金额','discount_price'],
				['优惠总金额','orders_discount_price'],
				['实际付款','pay_price'],
				['付款总金额','orders_pay_price'],
				['运费','orders_express_price'],
				['运费退款','orders_refund_express'],
				['商品退款金额','refund_price'],
				['商品退款总金额','orders_refund_price'],
				['结算货款','orders_received_price'],
				['积分倍数','score_ratio'],
				['赠送积分','score'],
				['商城服务费','service_price'],
				['商城增值费','increment_price'],
				['服务费总计','orders_service_price'],
				['运单号','express_code'],
				['买家ID','uid'],
			];
			
			foreach($res as $v){
				$orders_info = M('orders_shop')->field('s_no,uid,receipt_time,goods_price,goods_price_edit,pay_price,express_price_edit,refund_express,refund_price,express_code')->where(['s_no'=>$v['orderID']])->find();
				
				//订单信息
				$tmp_array['s_no']          			= ' '.$orders_info['s_no'];
				$tmp_array['receipt_time']          	= $orders_info['receipt_time'];
				$tmp_array['orders_total_price']        = $orders_info['goods_price'];
				$tmp_array['orders_discount_price']     = (string)round($v['goods_price']-$v['goods_price_edit'],2);
				$tmp_array['orders_pay_price']     		= $orders_info['pay_price'];
				$tmp_array['orders_express_price']     	= $orders_info['express_price_edit'];
				$tmp_array['orders_refund_express']     = $orders_info['refund_express'];
				$tmp_array['orders_refund_price']     	= $orders_info['refund_price'];
				$tmp_array['orders_received_price']     = (string)round($v['money'],2);
				$tmp_array['increment_price']     		= $v['serviceCharge'];
				$tmp_array['orders_service_price']     	= (string)round($v['sellerPayment']+$v['serviceCharge'],2);
				$tmp_array['express_code']        		= $orders_info['express_code'];
				$tmp_array['uid']        				= M('user')->where(['id'=>$orders_info['uid']])->getField('nick');
				$out_array[]                = $tmp_array;
				
				$orders_goods = M('orders_goods')->field('goods_name,total_price,total_price_edit,refund_price,score_ratio,score,refund_score')->where(['s_no'=>$v['orderID']])->select();
				foreach($orders_goods as $va){
					//订单商品信息
					$tmp_array2['goods_name']          			= $va['goods_name'];
					$tmp_array2['total_price']          		= $va['total_price'];
					$tmp_array2['discount_price']          		= (string)round($va['total_price']-$va['total_price_edit'],2);
					$tmp_array2['pay_price']          			= $va['total_price_edit'];
					$tmp_array2['refund_price']          		= $va['refund_price'];
					$tmp_array2['score_ratio']          		= ($va['score_ratio']*100).'%';
					$tmp_array2['score']          				= $va['score']-$va['refund_score'];
					$tmp_array2['service_price']          		= (string)round((($va['score']-$va['refund_score'])*0.08/100),2);
					$out_array[]                = $tmp_array2;
				}
			}
			$title = '分账中订单';
			//dump($res);
		}else if(I('post.type') == 3){
			$map['type'] = 2;
			$map['openid'] = session('user.openid');
			$this->authApi('/Erp/get_received_orders_list', $map,'sday,eday')->with();
			$res = $this->_data['data'];
			//dump($res);
			//定义字段
			$fields = [
				['订单号','s_no'],
				['货款到账时间','received_time'],
				['商品名称','goods_name'],
				['原价金额','total_price'],
				['原价总金额','orders_total_price'],
				['优惠金额','discount_price'],
				['优惠总金额','orders_discount_price'],
				['实际付款','pay_price'],
				['付款总金额','orders_pay_price'],
				['运费','orders_express_price'],
				['运费退款','orders_refund_express'],
				['商品退款金额','refund_price'],
				['商品退款总金额','orders_refund_price'],
				['结算货款','orders_received_price'],
				['积分倍数','score_ratio'],
				['赠送积分','score'],
				['商城服务费','service_price'],
				['商城增值费','increment_price'],
				['服务费总计','orders_service_price'],
				['分账模式','inventory_type'],
			];
			
			foreach($res as $v){
				$orders_info = M('orders_shop')->field('s_no,goods_price,goods_price_edit,total_price,pay_price,express_price_edit,refund_express,refund_price')->where(['s_no'=>$v['orderID']])->find();
				
				//订单信息
				$tmp_array['s_no']          			= ' '.$orders_info['s_no'];
				$tmp_array['received_time']          	= $v['receivedTime'];
				$tmp_array['orders_total_price']        = $orders_info['goods_price'];
				$tmp_array['orders_discount_price']     = (string)round($v['goods_price']-$v['goods_price_edit'],2);
				$tmp_array['orders_pay_price']     		= $orders_info['pay_price'];
				$tmp_array['orders_express_price']     	= $orders_info['express_price_edit'];
				$tmp_array['orders_refund_express']     = $orders_info['refund_express'];
				$tmp_array['orders_refund_price']     	= $orders_info['refund_price'];
				$tmp_array['orders_received_price']     = (string)round($v['money']-$v['refundMoney'],2);
				$tmp_array['increment_price']     		= $v['serviceCharge'];
				$tmp_array['orders_service_price']     	= (string)round($v['sellerPayment']+$v['serviceCharge'],2);
				$tmp_array['inventory_type']     		= $v['type'] == 1 ? '即时结算' : '非即时结算' ;

				$out_array[]                = $tmp_array;
				
				$orders_goods = M('orders_goods')->field('goods_name,total_price,total_price_edit,refund_price,score_ratio,score,refund_score')->where(['s_no'=>$v['orderID']])->select();
				foreach($orders_goods as $va){
					//订单商品信息
					$tmp_array2['goods_name']          			= $va['goods_name'];
					$tmp_array2['total_price']          		= $va['total_price'];
					$tmp_array2['discount_price']          		= (string)round($va['total_price']-$va['total_price_edit'],2);
					$tmp_array2['pay_price']          			= $va['total_price_edit'];
					$tmp_array2['refund_price']          		= $va['refund_price'];
					$tmp_array2['score_ratio']          		= ($va['score_ratio']*100).'%';
					$tmp_array2['score']          				= (string)round($va['score']-$va['refund_score'],2);
					$tmp_array2['service_price']          		= $v['type'] == 1 ? 0 : (string)round((($va['score']-$va['refund_score'])*0.08/100),2); 
				
					$out_array[]                = $tmp_array2;
				}
				
				
			}
			$title = '货款到账订单';
		}
		
		//excel横列排序
		$option_orders = 'A';
		foreach($fields as $k => $v){
			$option[$option_orders]['descript'] = $v[0];
			$option[$option_orders]['field'] = $v[1];
			$option_orders++;
		}
		D('Admin/Excel')->outExcel($out_array,$option,$title);
    }

    /**
     * subject: 数据导出展示页面
     * api: outChoose
     * author: Mercury
     * day: 2017-04-18 20:03
     * [字段名,类型,是否必传,说明]
     */
    public function outChoose()
    {
        $ordersType  =   [
            1   =>  '待付款',
            2   =>  '待发货',
            3   =>  '已发货',
            4   =>  '已收货',
            5   =>  '已评价',
            10  =>  '已关闭',
            20  =>  '退货订单',
            30  =>  '售后订单',
            40  =>  '待分账'
        ];
        $config = [
            'action' => U('/orders/outExcel'),
            'ajax'   => false
        ];
        $form = Form::getInstance($config)
            //->input(['name' => 'goods_name', 'title' => '商品名称'])
            //->input(['name' => 'nick', 'title' => '买家昵称'])
            ->select(['name' => 'type', 'title' => '账单类型', 'options' => [1=>'进行中订单',2=>'分账中订单',3=>'货款到账订单'],'require'=>'1','validate' => ['required']])
            ->datetime(['name' => 'sday', 'title' => '开始时间', 'options' => ['format' => 'yyyy-mm-dd hh:ii']])
            ->datetime(['name' => 'eday', 'title' => '结束时间', 'options' => ['format' => 'yyyy-mm-dd hh:ii']])
            ->submit(['title' => '导出数据'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }
    
    /**
     * 订单详情
     */
    public function detail() {
        $id =   I('get.id');
        $this->authApi('/SellerOrders/view', ['s_no' => $id])->with();
		if ($this->_data['data']['status'] >= 3) {
            //$this->api('/Express/query_express2', ['company_id' => $this->_data['data']['express_company_id'], 'express_code' => $this->_data['data']['express_code']])->with('express');
        }
        $this->authApi('/SellerOrders/orders_logs', ['s_no' => $id])->with('logs');
        C('seo', ['title' => '订单详情']);
        $this->display();
    }
    
    /**
     * 操作
     */
    public function opreat() {
        $type   =   I('get.type');
        $id     =   I('get.id');
        $typeArr=   ['close', 'ship'];
        if (!in_array($type, $typeArr)) {
            return;
        }
        $keyId          =   's_no';
        $url            =   '/SellerOrders/view';
        $data[$keyId]   =   $id;
        $this->authApi($url, $data)->with();

        $data['express_company_id'] =   $this->_data['data']['express']['express_company_id'];
        switch ($type) {
            case 'close':
                $header =   enCryptRestUri('/Opreat/close');
                $this->builderForm()
                ->keyId($keyId)
                ->keyTextArea('reason', '关闭理由', 1)
                ->data($data)
                ->view();
                break;
            case 'ship':
                //$header =   enCryptRestUri('/SellerOrders/express_send');
//                 $this->api('/SellerExpress/express_company');
//                 $eOptions   =   [];
//                 foreach ($this->_data['data'] as $val) {
//                     foreach ($val['dlist'] as $v) {
//                         $eOptions[$v['id']] =   $v['company'];
//                     }
//                 }
                $header =   enCryptRestUri('/Opreat/express');
                $this->builderForm()
                ->keyId($keyId)
                //->keySelect('express_company_id', '快递公司', $eOptions, 1, '<span class="text_yellow">未经买家许可请谨慎修改。</span>')
                ->keySearchSelect('express_company_id', '快递公司', '输入快递公司名称后在下方选择要使用的快递公司', 1)
                ->keyText('express_code', '快递单号', 1)
                ->keyTextArea('express_remark', '发货备注',null,'选填，不得超过200个字！')
                ->data($data)
                ->view();
                break;
        }
		$this->assign('openid',session('user.openid'));
		$this->assign('shop_type',session('user.shop_type'));
		$this->assign('type', $type);
        $this->assign('header', $header);
        $this->display();
    }


    /**
     *
     * 关闭订单
     *
     */
    public function close() {
        $sno = I('get.id');
        $this->getData($sno);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/orders/detail', ['id' => $sno]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 's_no', 'value' => $sno])
            ->textarea(['name' => 'reason', 'title' => '关闭理由', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->submit(['title' => '关闭订单'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '关闭订单']);
        $this->display();
    }


    /**
     * 邮寄商品
     */
    public function express() {
        $sno = I('get.id');
        $this->getData($sno);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/orders/detail', ['id' => $sno]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 's_no', 'value' => $sno])
            ->modal(['name' => 'express_company_id', 'title' => '快递公司', 'url' => U('/tool/expressCompany', ['inputName' => 'express_company_id']), 'require' => 1, 'validate' => ['required']])
            //->text(['name' => 'express_company_id', 'title' => '快递公司', 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'express_code', 'title' => '快递单号', 'require' => 1, 'validate' => ['required']])
            ->textarea(['name' => 'express_remark', 'tips' => '选填，不得超过200个字', 'title' => '发货备注', 'validate' => ['maxlength' => 200]])
            ->submit(['title' => '邮寄商品'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '邮寄商品']);
        $this->display();
    }


    /**
     *
     * 修改发货信息
     *
     */
    public function editExpress() {
        $sno = I('get.id');
        $this->getData($sno);
        $config = [
            'action' => U('/orders/edit_express_save'),
            'gourl'  => '"' . U('/orders/detail', ['id' => $sno]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 's_no', 'value' => $sno])
            ->modal(['name' => 'express_company_id', 'title' => '快递公司', 'url' => U('/tool/expressCompany', ['inputName' => 'express_company_id']), 'require' => 1, 'validate' => ['required']])
            //->text(['name' => 'express_company_id', 'title' => '快递公司', 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'express_code', 'title' => '快递单号', 'require' => 1, 'validate' => ['required']])
            ->textarea(['name' => 'express_remark', 'tips' => '选填，不得超过200个字', 'title' => '发货备注', 'validate' => ['maxlength' => 200]])
            ->submit(['title' => '修改发货信息'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '修改发货信息']);
        $this->display();
    }


    /**
     * 获取订单数据
     *
     * @param $sno
     */
    private function getData($sno) {
        $url            =   '/SellerOrders/view';
        $this->authApi($url, ['s_no' => $sno])->with();
    }


    /**
     * 更改发货方式
     * Author: enhong <2016-10-14>
     */
    public function edit_express(){
        $res = $this->doApi('/SellerOrders/get_orders_express',['openid' => session('user.openid'),'s_no' => I('get.s_no')]);
        $this->assign('rs',objectToArray($res->data));
        //dump($res);
        C('seo', ['title' => '修改邮寄方式']);
        $this->display();
    }

    public function edit_express_save(){
        $data['openid']             = session('user.openid');
        $data['s_no']               = I('post.s_no');
        $data['express_code']       = I('post.express_code');
        $data['express_company_id'] = I('post.express_company_id');
        $data['express_remark']     = I('post.express_remark');

        $res = $this->doApi('/SellerOrders/edit_express',$data,'express_remark');

        $this->ajaxReturn($res);
    }

    /**
     * 发货
     */
    public function ship() {
        $this->display();
    }
    
    /**
     * 修改价格
     */
    public function editPrice() {
        $this->authApi('/SellerOrders/orders_goods', ['s_no' => I('get.id')])->with();
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/orders/detail', ['id' => I('get.id')]) . '"',
            'headers'=> enCryptRestUri('/Orders/priceEdit')
        ];
        $form = Form::getInstance($config)
            ->text(['name' => 'goods_price', 'title' => '商品金额', 'value' => $this->_data['data']['goods_price_edit'],'validate' => ['required','min' => round($this->_data['data']['goods_price'] * 0.5,2),'max' => round($this->_data['data']['goods_price'] * 1.5,2)],'require' => true])
            ->text(['name' => 'express_price', 'title' => '运费', 'value' => $this->_data['data']['express_price_edit'],'validate' => ['required','min' => 0,'number'],'require' => true])
            ->hidden(['name' => 's_no','value' => I('get.id')])
            ->submit(['title' => '修改价格'])
            ->create();
        $this->assign('form', $form);

        $this->display();
    }

    /**
     * 添加订单备注
     * Author:enhong <2016-10-11>
     */
    public function remark_add(){
        $rs = M('orders_shop')->where(['s_no' => I('get.s_no'),'seller_id' => session('user.id')])->field('id,s_no,seller_remark,seller_remark_color')->find();
        $this->assign('rs',$rs);
        $this->display();
    }

    public function remark_add_save(){
        $data = array(
            'openid'            => session('user.openid'),
            's_no'              => I('post.s_no'),
            'seller_remark'      => I('post.seller_remark'),
            'seller_remark_color'=> I('post.seller_remark_color')
        );

        //C('DEBUG_API',true);
        $res = $this->doApi('/SellerOrders/remark_add',$data);
        //dump($res);
        $this->ajaxReturn($res);

    }
 	/**
     * 录入页面
     * Author:lizuheng <2016-11-18>
     */
	public function scm(){
        $id     =   I('get.id');
        $keyId          =   's_no';
        $url            =   '/SellerOrders/view';
        $data[$keyId]   =   $id;
        $this->authApi($url, $data)->with();
		
		$this->assign('openid',session('user.openid'));
		$this->assign('shop_type',session('user.shop_type'));
        $this->display();
    }
	
	/**
     * 利润
     * Author:lizuheng <2016-11-17>
     */
	public function scm_save(){
		if(session('user.shop_type') != 1){
			$this->ajaxReturn(array('code'=>0,'msg'=>'权限错误'));
		}
        $data['openid']           = session('user.openid');
        $data['profit_price']     = I('post.profit_price');
        $data['purchase_price']   = I('post.purchase_price');
        $data['id']               = I('post.id');
        $data['other_price']      = I('post.other_price');
		$data['num']              = I('post.num');

        $res = $this->authApi('/Scm/save',$data,'other_price');
        $this->ajaxReturn($res->_data);
    }
}