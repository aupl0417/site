<?php
namespace Seller\Controller;
use Common\Form\Form;

class ServiceController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    
    public function index() {
        $data = ['pagesize' => 10];
        $sidArr = ['over' => 100, 'cancel' => 20, 'arb' => 10, 'buyer' => '2,3,6', 'seller' => '1,4,5'];
        if (isset($_GET['sno']) && !empty(I('get.sno'))) $data['s_no'] = I('get.sno');
        if (isset($_GET['goods_name']) && !empty(I('get.goods_name'))) $data['goods_name'] = I('get.goods_name');
        if (isset($_GET['rno']) && !empty(I('get.rno'))) $data['r_no'] = I('get.rno');
        if (isset($_GET['nick']) && !empty(I('get.nick'))) $data['nick'] = I('get.nick');
        if (isset($_GET['sid']) && array_key_exists(I('get.sid'), $sidArr))  $data['status'] = $sidArr[I('get.sid')];
        if (isset($_GET['sday']) && !empty(I('get.sday'))) $data['sday'] = I('get.sday');
        if (isset($_GET['eday']) && !empty(I('get.eday'))) $data['eday'] = I('get.eday');
        $this->authApi('/SellerRefund3/lists', $data, 's_no,r_no,goods_name,nick,atime,status,sday,eday')->with();
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_售后列表']);
        $this->display();
    }
    
    /**
     * 
     * @return boolean
     */
    public function opreat() {
        $typeArr        =   ['reject', 'accept', 'accept1', 'send_express', 'appeal'];
        $type           =   I('get.type');
        $id             =   I('get.id');
        if (!in_array($type, $typeArr)) return false;
        $keyId          =   'r_no';
        $data[$keyId]   =   $id;
        $this->authApi('/SellerRefund3/view', $data)->with();
        if ($this->_data['code'] != 1) return false;
        $data['s_no']   =   $this->_data['data']['s_no'];
        $data['r_no']   =   $this->_data['data']['r_no'];
        $header     =   enCryptRestUri('/Service/opreat/' . $type);
        switch ($type) {
            case 'reject':  //拒绝
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('售后单号', $data['r_no'])
                ->keyTextArea('reason', '拒绝原因', 1)
                ->keyMultiImages('images', '图片')
                ->data($data)
                ->view();
            break;
             case 'accept':  //同意
                 $this->authApi('/SendAddress/address_list');
                 $address = $this->_data['data'];
                 $this->builderForm()
                 ->keyId($keyId)
                 ->keyId('s_no')
                 ->keyHtmltext('订单号', $data['s_no'])
                 ->keyHtmltext('售后单号', $data['r_no'])
                 ->selectAddress(['name' => 'address_id', 'title' => '收货地址','isRequired' => 1,'other' => ['url' => DM('seller','/addr')]], $address)
                 ->keyPass('password_pay', '安全密码', 1)
                 ->data($data)
                 ->view();
                 break;
//             case 'accept1': //收到商品
//                 break;
            case 'send_express':
//                 $this->api('/SellerExpress/express_company');
//                 $eOptions   =   [];
//                 foreach ($this->_data['data'] as $val) {
//                     foreach ($val['dlist'] as $v) {
//                         $eOptions[$v['id']] =   $v['company'];
//                     }
//                 }
                $addr   =   M('refund_logs')->where(['r_no' => $id])->cache(true)->order('id desc')->getField('remark');
                $this->assign('addr', $addr);
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('退款单号', $data['r_no'])
                //->keySelect('express_company_id', '快递公司', $eOptions, 1)
                ->keySearchSelect('express_company_id', '快递公司', '输入快递公司名称后在下方选择要使用的快递公司', 1)
                ->keyText('express_code', '快递单号', 1)
                ->data($data)
                ->view();
                break;      //邮寄商品
            case 'appeal':
                //申诉
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('售后单号', $data['r_no'])
                ->keyTextArea('remark', '申诉原因', 1)
                ->keyMultiImages('images', '图片')
                ->data($data)
                ->view();
                break;
            default:    //同意，及已收到货
                $this->builderForm()
                ->keyId($keyId)
                ->keyId('s_no')
                ->keyHtmltext('订单号', $data['s_no'])
                ->keyHtmltext('售后单号', $data['r_no'])
                ->keyPass('password_pay', '安全密码', 1)
                ->data($data)
                ->view();
            break;
        }
        $this->assign('header', $header);
        $this->display();
    }

    /**
     *
     * 拒绝售后
     *
     */
    public function reject() {
        $rno = I('get.id');
        $this->getData($rno);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/service/detail', ['id' => I('get.id')]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->textarea(['name' => 'reason', 'title' => '拒绝原因', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->mutilImages(['name' => 'images', 'title' => '图片'])
            ->submit(['title' => '拒绝售后'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }

    /**
     *
     * 同意售后
     *
     */
    public function agree() {
        $rno = I('get.id');
        $this->authApi('/SendAddress/address_list');
        $address = $this->_data['data'];
        $this->getData($rno);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/service/detail', ['id' => I('get.id')]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->address(['name' => 'address_id', 'title' => '收货地址', 'options' => $address, 'url' => U('/addr'), 'require' => 1])
            ->password(['name' => 'password_pay', 'title' => '安全密码', 'require' => 1, 'validate' => ['required', 'number', 'rangelength' => '[6,6]']])
            ->submit(['title' => '同意售后'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }


    /**
     *
     * 收到货
     *
     */
    public function accept() {
        $rno = I('get.id');
        $this->getData($rno);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/service/detail', ['id' => I('get.id')]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            //->address(['name' => 'address_id', 'title' => '收货地址', 'options' => $address, 'url' => U('/addr'), 'require' => 1])
            ->password(['name' => 'password_pay', 'title' => '安全密码', 'require' => 1, 'validate' => ['required', 'number', 'rangelength' => '[6,6]']])
            ->submit(['title' => '已收到商品'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }


    /**
     *
     * 邮寄商品
     *
     */
    public function express() {
        $addr   =   M('refund_logs')->where(['r_no' => $id])->cache(true)->order('id desc')->getField('remark');
        $this->assign('addr', $addr);
        $rno = I('get.id');
        $this->getData($rno);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/service/detail', ['id' => I('get.id')]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->modal(['name' => 'express_company_id', 'title' => '快递公司', 'url' => U('/tool/expressCompany', ['inputName' => 'express_company_id']), 'require' => 1, 'validate' => ['required']])
            //->text(['name' => 'express_company_id', 'title' => '快递公司', 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'express_code', 'title' => '快递单号', 'require' => 1, 'validate' => ['required']])
            ->submit(['title' => '邮寄商品'])
            ->create();
        $this->assign('form', $form);
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
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/service/detail', ['id' => I('get.id')]) . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'r_no', 'value' => $rno])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->textarea(['name' => 'remark', 'title' => '申诉原因', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->mutilImages(['name' => 'images', 'title' => '图片'])
            ->submit(['title' => '提交申诉'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }

    /**
     * 售后详情
     */
    public function detail() {
        $url = '/SellerRefund3/view';
        $this->authApi($url, ['r_no' => I('get.id')])->with();
        $express = [];
        if (!empty($this->_data['data']['express'])) {
            arsort($this->_data['data']['express']);
            $express = array_values($this->_data['data']['express']);
        }
        $this->assign('express', $express);
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
        C('seo', ['title' => '售后详情']);
        $this->display();
    }

    /**
     * 获取数据
     *
     * @param $rno
     */
    private function getData($rno) {
        $this->authApi('/SellerRefund3/view', ['r_no' => $rno])->with();
    }
}