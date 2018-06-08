<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 快递电子面单批量打印
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
| 2016-10-15
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
vendor('Kdniao.ExpressBill#class'); //引入快递鸟接口
class ExpressPrintController extends CommonController {
    protected $kdn; //快递鸟句柄

    public function _initialize() {
        parent::_initialize();
        $this->kdn =new \Kdniao\ExpressBill(C('cfg.kdniao'));

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
            '_get_cfg'              => 'openid',    //获取设置信息
            '_cfg_save'             => 'openid,is_come,is_send,default_company_id', //保存设置
            '_company'              => '',          //支持电子面单的快递公司
            '_orders'               => 'openid',    //待发货订单
            '_batch_express_bill'   => 'openid,orders',    //批量生成快递电子面单
            '_express_bill'         => 'openid,s_no,express_company_id,is_send,is_come',    //生成快递电子面单
            '_batch_send_express'   => 'openid,orders',    //批量发货
            '_goods_list'           => 'openid,s_no',       //生成出货单

        ];

        $result=$sign_field[$method];
        return $result;
    }
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
     * 获取设置信息
     */
    public function _get_cfg(){
        $do = M('express_print_cfg');

        $rs = $do->where(['uid' => $this->uid])->field('id,is_come,is_send,default_company_id')->find();
        if($rs) return ['code' => 1,'data' => $rs];
        else return ['code' => 3];

    }

    /**
     * 保存设置
     * @param string $_POST['openid'] 用户ID
     * @param int $_POST['is_come'] 是否通知快递上门取件
     * @param int $_POST['is_send'] 是否自动标记发货
     * @param int $_POST['default_company_id'] 默认发货快递公司
     */
    public function _cfg_save(){
        $do = D('Common/ExpressPrintCfg');
        $id = $do->where(['uid' => $this->uid])->getField('id');

        $data           = I('post.');
        $data['uid']    = $this->uid;
        if($id) $data['id'] = $id;

        if(!$do->create($data)) return ['code' => 4,'msg' => $do->getError()];

        if($id){    //修改
            if(false !== $do->save()) return ['code' => 1];
            else return ['code' => 0];
        }elseif($do->add()) {   //新增
            return ['code' => 1];
        }else return ['code' => 0];
    }

    /**
     * 获取支持电子面单的快递公司
     */
    public function _company(){
        $do = M('express_company');
        $list = $do->cache(true)->where(['status' => 1,'kdniao_code' => ['neq','']])->field('atime,etime,ip',true)->order('id desc')->select();
        if($list) return ['code' => 1,'data' => $list];
        return ['code' => 3];
    }

    /**
     * 获取待发货订单
     * @param int $_POST['p']   第n页
     * @param int $_POST['pagesize']    每页记录数
     */
    public function _orders(){
        $res['p']           = I('post.p') ? I('post.p') : 1;
        $res['pagesize']    = I('post.pagesize') ? I('post.pagesize') : 20;
		
        $do = M('orders_shop');
        $map['seller_id']   = $this->uid;
        $map['status']      = 2;
		
		if(isset($_POST['s_no']) && I('post.s_no') !=""){
			$map['s_no']    = I('post.s_no');
		}
		if(isset($_POST['sday']) && I('post.sday') !="" && isset($_POST['eday']) && I('post.eday') !=""){
			$map['pay_time']   = array(array('egt',I('post.sday')),array('elt',I('post.eday')));
		}else if(isset($_POST['sday']) && I('post.sday') !="" && I('post.eday') ==""){
			$map['pay_time']   = array('egt',I('post.sday'));
		}else if(isset($_POST['eday']) && I('post.eday') !="" && I('post.sday') ==""){
			$map['pay_time']   = array('elt',I('post.eday'));
		}
		
        $res['count']       = $do->where($map)->count();
        $res['page']        = ceil($res['count'] / $res['pagesize']);
		
        if($res['p'] > $res['page']) $res['p'] = $res['page'];

        $area =	$this->cache_table('area');
        $rs['orders']['province']	=	$area[$rs['orders']['province']];
        $rs['orders']['city']		=	$area[$rs['orders']['city']];
        $rs['orders']['district']	=	$area[$rs['orders']['district']];
        $rs['orders']['town']		=	$area[$rs['orders']['town']];

        $res['list']  = D('Common/OrdersShopSellerRelation')->relation(['orders_goods','buyer','orders','shop'])->where($map)->field('id,atime,o_id,s_no,status,shop_id,uid,seller_id,pay_price,pay_time,remark')->limit($res['pagesize'])->page($res['p'])->select();
        if($res['list']) {
            //数据格式化输出
            foreach ($res['list'] as $key => $val) {
                foreach ($val['orders_goods'] as $v) {
                    $res['list'][$key]['goods_num'] += $v['num'];
                    $res['list'][$key]['weight'] += $v['total_weight'];
                }

                unset($res['list'][$key]['orders_goods']);
                $res['list'][$key]['goods_name'] = $val['orders_goods'][0]['goods_name'] . '……（共' . $res['list'][$key]['goods_num'] . '件）';

                $res['list'][$key]['orders']['province']    = $area[$res['list'][$key]['orders']['province']];
                $res['list'][$key]['orders']['city']        = $area[$res['list'][$key]['orders']['city']];
                $res['list'][$key]['orders']['district']    = $area[$res['list'][$key]['orders']['district']];
                $res['list'][$key]['orders']['town']        = $area[$res['list'][$key]['orders']['town']];
            }

            return ['code' =>1,'data' => $res];
        }else return ['code' =>3];

    }

    /**
     * 批量生成快递电子面单
     * @param string $_POST['openid']   用户openid
     * @param string $_POST['orders']   待处理的订单，经过序列化的数据
     */
    public function _batch_express_bill(){
        $orders = unserialize(html_entity_decode(I('post.orders')));

        $bill = [];
        foreach($orders['s_no'] as $key => $val){
            $tmp = $this->_create_express_bill($val,$this->uid,$orders['express_company_id'][$key],$orders['is_send'][$key],$orders['is_come'][$key]);
            if($tmp['code'] == 1){
                $bill[] = $tmp['data']['PrintTemplate'];
            }
            usleep(1000);
        }

        if(!empty($bill)) return ['code' => 1,'data' =>$bill];
        else return ['code' => 0];
    }

    /**
     * 生成单张快递电子面单
     * @param string $_POST['s_no']  订单号
     * @param int    $_POST['express_company_id'] 快递公司ID
     * @param int    $_POST['is_send'] 是否标记发货
     * @param int    $_POST['is_come'] 是否通知快递员上门取件
     */
    public function _express_bill(){
        $res = $this->_create_express_bill(I('post.s_no'),$this->uid,I('post.express_company_id'),I('post.is_send'),I('post.is_come'));
        return $res;
    }

    /**
     * 生成快递电子面单
     * @param string $s_no  订单号
     * @param int    $express_company_id 快递公司ID
     * @param int    $is_send 是否标记发货
     * @param int    $is_come 是否通知快递员上门取件
     */
    public function _create_express_bill($s_no,$seller_id,$express_company_id,$is_send=1,$is_come=1){
        $tmp = $this->_get_cfg();
        if($tmp['code'] == 1) $cfg=$tmp['data'];
        else return ['code' => 1803];   //未设置默认参数

        //默认快递公司ID
        $express_company_id = $express_company_id ? $express_company_id : $cfg['company_id'];

        $do=D('Common/OrdersShopSellerRelation');
        $rs=$do->relation(['orders_goods','buyer','orders','shop'])->where(['s_no' => $s_no,'seller_id' => $seller_id])->field('id,atime,o_id,s_no,status,shop_id,uid,seller_id,pay_price,pay_time,remark,express_company_id,express_code')->find();

        if(!$rs) return $this->apiReturn(3);

        if(!in_array($rs['status'],array(2,3))){
            //echo '该订单状态下不可再次创建快递单！';
            return ['code' => 1800];
        }

        $weight = 0;
        $num    = 0;
        foreach($rs['orders_goods'] as $key => $val){
            $weight += $val['total_weight'];
            $num    += $val['num'] - $val['refund_num'];
        }

        //dump($rs);
        //快递公司
        $express = M('express_company')->where(['id' => $express_company_id])->field('id,sub_name,kdniao_code,customer_name,customer_pwd,month_code,send_site')->find();
        //dump($express);
        if(empty($express['kdniao_code'])) {
            //echo '该快递公司不支持电子面单！';
            return ['code' => 1801];
        }

        //发货人资料
        $from = M('send_address')->where(['uid' => $rs['seller_id']])->field('id,atime,etime,ip,uid,is_default',true)->order('is_default desc')->find();
        if(!$from) {
            //echo '未设置发货地址！';
            return ['code' => 1802];
        }

        $area =	$this->cache_table('area');
        $from['province']	=	$area[$from['province']];
        $from['city']		=	$area[$from['city']];
        $from['district']	=	$area[$from['district']];
        $from['town']		=	$area[$from['town']];
        //dump($from);


        $eorder = [];
        $eorder["ShipperCode"]  = $express['kdniao_code'];
        $eorder["OrderCode"]    = $rs['s_no'];
        $eorder["PayType"]      = 1;    //1-现付，2-到付，3-月结，4-第三方支付
        $eorder["ExpType"]      = 1;    //快递类型：1-标准快件
        $eorder["Weight"]       = $weight;
        $eorder["Quantity"]     = $num;
        $eorder['IsNotice']     = $is_come;
        $eorder['Remark']       = $rs['remark'];

        //快递公司账号密码密钥私钥
        $eorder['CustomerName']     = $express['customer_name'];
        $eorder['CustomerPwd']      = $express['customer_pwd'];
        $eorder['MonthCode']        = $express['month_code'];
        $eorder['SendSite']         = $express['send_site'];

        //发货人
        $sender = [];
        $sender["Name"]         = $from['linkname'];
        if($from['mobile']) $sender["Mobile"]       = $from['mobile'];
        if($from['tel']) $sender["Tel"]          = $from['tel'];
        $sender["ProvinceName"] = $from['province'];
        $sender["CityName"]     = $from['city'];
        $sender["ExpAreaName"]  = $from['district'].' '.$from['town'];
        $sender["Address"]      = $from['street'];


        $receiver = [];
        $receiver["Name"]           = $rs['orders']['linkname'];
        if($rs['orders']['mobile']) $receiver["Mobile"]         = $rs['orders']['mobile'];
        if($rs['orders']['tel']) $receiver["Tel"]            = $rs['orders']['tel'];

        $receiver["ProvinceName"]   = $area[$rs['orders']['province']];
        $receiver["CityName"]       = $area[$rs['orders']['city']];
        $receiver["ExpAreaName"]    = $area[$rs['orders']['district']].' '.$area[$rs['orders']['town']];
        $receiver["Address"]        = $rs['orders']['street'];

        $goods          = '['.$rs['s_no'].']'.$rs['orders_goods'][0]['goods_name'] . '……（共件'.$num.'商品）';
        $commodity[]    = [
                'GoodsName'     => $goods,
                'Goodsquantity' => $num,
            ];


        $res = $this->kdn->create_express_bill($eorder,$sender,$receiver,$commodity);

        //是否发货
        if($is_send == 1 && !empty($res['Order']['LogisticCode']) && ($express_company_id != $rs['express_company_id'] || $res['Order']['LogisticCode'] != $rs['express_code'])){
            //dump($res['Order']['LogisticCode']);
            $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>$rs['s_no'],'seller_id'=>$rs['seller_id']));

            $data = [
                'express_company_id'    => $express['id'],
                'express_code'          => $res['Order']['LogisticCode']
            ];

            if(empty($rs['express_code'])){ //发货
                $orders->send_express($data);
            }else{  //修改发货
                $tmp = $orders->edit_express($data);
            }
        }

        if($res['PrintTemplate']){
            if($express['kdniao_code'] == 'STO') $res['PrintTemplate'] = str_replace('商家自定义区',$goods,$res['PrintTemplate']);
            return ['code' => 1,'data' => $res];
        }else{
            return ['code' => 0,'data' => $res];
        }
    }

    /**
     * 批量发货
     * @param string $_POST['openid']   用户openid
     * @param string $_POST['orders']   待处理的订单，经过序列化的数据
     */

    public function _batch_send_express(){
        $orders = unserialize(html_entity_decode(I('post.orders')));
        $res = [];
        foreach($orders['s_no'] as $key => $val){
            if($orders['express_company_id'][$key] && $orders['express_code'][$key]) {
                $o = new \Common\Controller\SellerOrdersController(array('s_no' => $val, 'seller_id' => $this->uid));
                $tmp = $o->send_express(['express_company_id' => $orders['express_company_id'][$key], 'express_code' => $orders['express_code'][$key], 'express_remark' => $orders['express_remark'][$key]]);
                if($tmp['code'] == 1) $res[] = $tmp['data'];
            }
        }

        if(!empty($res)) return ['code' => 1,'data' =>$res];
        else return ['code' => 0];
    }

    /**
     * 生成出货单
     */
    public function _goods_list(){
        $do=D('Admin/OrdersShopViewRelation');

        $rs=$do->relation(true)->where(['seller_id' => $this->uid,'s_no' => I('post.s_no')])->find();

        if($rs) {
            $area = $this->cache_table('area');
            $rs['orders']['province'] = $area[$rs['orders']['province']];
            $rs['orders']['city'] = $area[$rs['orders']['city']];
            $rs['orders']['district'] = $area[$rs['orders']['district']];
            $rs['orders']['town'] = $area[$rs['orders']['town']];
            //dump($rs);
            $this->assign('rs', $rs);

            $html = $this->fetch('goods_list');

            return ['code' =>1,'data' => $html];
        }else return ['code' => 0];
    }

}