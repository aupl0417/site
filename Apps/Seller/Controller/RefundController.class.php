<?php
namespace Seller\Controller;
use Common\Form\Form;

class RefundController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 退款列表
     */
    public function index() {
        $sidArr = ['over' => 100, 'cancel' => 20, 'arb' => 10, 'buyer' => '2,4', 'seller' => '1,3,5'];
        $data = ['pagesize' => 10];
        if (isset($_GET['sno']) && !empty(I('get.sno'))) $data['s_no'] = I('get.sno');
        if (isset($_GET['goods_name']) && !empty(I('get.goods_name'))) $data['goods_name'] = I('get.goods_name');
        if (isset($_GET['rno']) && !empty(I('get.rno'))) $data['r_no'] = I('get.rno');
        if (isset($_GET['nick']) && !empty(I('get.nick'))) $data['nick'] = I('get.nick');
        if (isset($_GET['sid']) && array_key_exists(I('get.sid'), $sidArr))  $data['status'] = $sidArr[I('get.sid')];
        if (isset($_GET['sday']) && !empty(I('get.sday'))) $data['sday'] = I('get.sday');
        if (isset($_GET['eday']) && !empty(I('get.eday'))) $data['eday'] = I('get.eday');
        $this->authApi('/SellerRefund/refund_list', $data, 's_no,r_no,goods_name,nick,atime,status,sday,eday')->with();
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_退款列表']);
        $this->display();
    }
    
    /**
     * 退款详情
     */
    public function detail() {
        $sid    =   I('get.sid');
        $url    =   '/SellerRefund/view';
        if ($sid == 3) $url = '/SellerRefund3/view';
        $this->authApi($url, ['r_no' => I('get.id')])->with();
//        if (isset($this->_data['data']['express_company_id']) && isset($this->_data['data']['express_code'])) {
//            $this->api('/Express/query_express2', ['company_id' => $this->_data['data']['express_company_id'], 'express_code' => $this->_data['data']['express_code']])->with('express');
//        } elseif (isset($this->_data['data']['express']) && !empty($this->_data['data']['express'])) {
//            arsort($this->_data['data']['express']);
//            $express = (array_values($this->_data['data']['express']));
//            foreach ($express as $k => $v) {
//                $this->api('/Express/query_express2', ['company_id' => $v['express_company_id'], 'express_code' => $v['express_code']])->with('express_' . $k);
//            }
//            unset($express);
//        }
        C('seo', ['title' => '退款详情']);
        $this->display();
    }
    
    /**
     * 退款操作
     */
    public function opreat() {
        $type   =   I('get.type');
        $id     =   I('get.id');
        $status =   I('get.sid');
        $opType =   ['cancel', 'feuse', 'agree', 'refuse', 'receipt', 'notreceipt', 'appeal'];
        if (!in_array($type, $opType)) {    //不存在
            return;
        }
        $keyId          =   'r_no';
        $url            =   '/SellerRefund'.$status.'/view';
        $data[$keyId]   =   $id;
        $this->authApi($url, $data)->with();

        $data['s_no']   =   $this->_data['data']['s_no'];
        $data['r_no']   =   $this->_data['data']['r_no'];
        $data['type']   =   $this->_data['data']['type'];

        if($this->_data['data']['type'] == 1){  //退货时读取退货地址
            $res = $this->authApi('/SendAddress/address_list');
            $address = $this->_data['data'];
        }

        switch($type) {
            case 'close':   //关闭
                $this->builderForm()
                ->keyId($keyId)
                ->keyTextArea('reason', '关闭原因', 1)
                //->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
                break;
            case 'cancel':  //取消
                $this->builderForm()
                ->keyId($keyId)
                ->keyTextArea('reason', '关闭原因', 1)
                //->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
                break;
            case 'refuse':  //拒绝
                if ($status == 2) {
                    $this->builderForm()
                    ->keyId($keyId)
                    ->keyId('s_no')
                    ->keyHtmltext('订单号', $data['s_no'])
                    ->keyHtmltext('退款单号', $data['r_no'])
                    ->keyTextArea('reason', '拒绝原因', 1)
                    ->keyMultiImages('images', '图片')
                    ->data($data)
                    ->view();
                } else {
                    $this->builderForm()
                    ->keyId($keyId)
                    ->keyId('s_no')
                    ->keyHtmltext('订单号', $data['s_no'])
                    ->keyHtmltext('退款单号', $data['r_no'])
                    ->keyTextArea('reason', '拒绝原因', 1)
                    //->keyPass('password_pay', '安全密码', 1)
                    ->data($data)
                    ->view();
                }
                break;
            case 'agree' :  //同意
                $this->authApi('/Erp/account')->with('account');    //账户信息
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('退款单号', $data['r_no'])
                ->selectAddress(['name' => 'address_id','title' => '退货地址','isRequired' => 1,'other' => ['url' => DM('seller','/addr')]],$address,$data['type'])
                ->keyTextArea('reason', '备注信息', null, '最多200个字符')
                ->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
                break;
            case 'receipt' :    //已收到退货
                $this->authApi('/Erp/account')->with('account');    //账户信息
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('退款单号', $data['r_no'])
                ->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
                break;
            case 'appeal':
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('退款单号', $data['r_no'])
                ->keyTextArea('remark', '申诉原因', 1)
                ->keyMultiImages('images', '图片')
                ->data($data)
                ->view();
                break;
            case 'notreceipt' :    //未收到退货
                $this->authApi('/Erp/account')->with('account');    //账户信息 
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('退款单号', $data['r_no'])
                ->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
                break;
            default:    //默认
                $this->builderForm()
                ->keyId($keyId)
                ->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
        }
        
        $this->assign('header', enCryptRestUri('/Refund'.$status.'/' . $type));
        $this->display();
    }


    /**
     *
     * 申诉
     *
     */
    public function appeal() {
        $rno = I('get.id');
        $this->getData($rno);
        $config['action'] = U('/run/authrun');
        $config['gourl'] = '"' . U('/refund/detail', ['id' => $rno]) . '"';
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->textarea(['name' => 'remark', 'title' => '申诉原因', 'require' => 1, 'validate' => ['required', 'rangelngth' => '[5,300]']])
            ->mutilImages(['name' => 'images', 'title' => '图片'])
            ->submit(['title' => '提交申诉'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '申诉']);
        $this->display();
    }


    /**
     *
     * 未收到退货
     *
     */
    public function notreceipt() {

    }


    /**
     *
     * 已收到退货
     *
     */
    public function receipt() {
        $rno = I('get.id');
        $this->getData($rno);
        $config['action'] = U('/run/authrun');
        $config['gourl']  = '"' . U('/refund/detail', ['id' => $rno]) . '"';
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->password(['name' => 'password_pay', 'title' => '安全密码', 'require' => 1, 'validate' => ['required', 'number', 'ranglength' => '[6,6]']])
            ->submit(['title' => '提交表单'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '确认收货']);
        $this->display();
    }

    /**
     *
     * 同意退货
     *
     */
    public function agree() {
        $rno = I('get.id');
        $this->authApi('/Erp/account')->with('account');    //账户信息
        $this->authApi('/SendAddress/address_list');
        $address = $this->_data['data'];
        $this->getData($rno);
        //dump($this->_data['data']['orders_status']);
        //如果订单状态为2的话，则是已支付订单，如果为3的话则是已发货订单
        $flag = false;
        $title = '同意退款';
        if ($this->_data['data']['orders_status'] == 3) {
            if ($this->_data['data']['type'] == 1) {
                $title = '同意退货';
                $flag  = true;
            }
            $config['headers'] = enCryptRestUri('/Refund2/agree');
        }
        $config['action'] = U('/run/authrun');
        $config['gourl'] = '"' . U('/refund/detail', ['id' => $rno]) . '"';
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            //->address(['name' => 'address_id', 'title' => '收货地址', 'require' => 1, 'options' => $address, 'url' => U('/addr')])
            ->callback($flag, ['name' => 'address_id', 'title' => '收货地址', 'callback' => 'address', 'require' => 1, 'options' => $address, 'url' => U('/addr')])
            ->textarea(['name' => 'reason', 'title' => '备注信息', 'tips' => '最多可填写200个字符', 'validate' => ['maxlength' => 200]])
            ->password(['name' => 'password_pay', 'title' => '安全密码', 'require' => 1, 'validate' => ['required', 'number', 'rangelength' => '[6,6]']])
            ->submit(['title' => $title])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => $title]);
        $this->display();
    }


    /**
     *
     * 拒绝退货
     *
     */
    public function refuse() {
        $rno = I('get.id');
        $this->getData($rno);
        $config = [
            'action'    =>  U('/run/authrun'),
            'gourl'     =>  '"' . U('/refund/detail', ['id' => $rno]) . '"',
        ];
        if ($this->_data['data']['orders_status'] == 3) $config['headers'] = enCryptRestUri('/Refund2/refuse');
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->textarea(['name' => 'reason', 'title' => '拒绝原因', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->callback(($this->_data['data']['orders_status'] == 3 ? true : false), ['name' => 'images', 'title' => '图片', 'callback' => 'mutilImages'])
            ->submit(['title' => '拒绝退货'])
            ->create();

        $this->assign('form', $form);
        C('seo', ['title' => '拒绝退款']);
        $this->display();
    }

    /**
     * 获取数据
     *
     * @param $rno
     */
    private function getData($rno) {
        $url = '/SellerRefund/view';
        $this->authApi($url, ['r_no' => $rno])->with();
    }
}