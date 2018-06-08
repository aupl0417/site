<?php
namespace Seller\Controller;
use Common\Form\Form;

class AddrController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 地址列表
     */
    public function index() {
        $this->authApi('/SendAddress/address_list')->with();  //地址列表
        C('seo', ['title' => '我的发货地址']);
        $this->display();
    }
    
    
    public function add($id = null) {
        $this->_data['data']    =   null;
        if ($id > 0) {
            $this->authApi('/SendAddress/view', ['id' => I('get.id')]);
            $this->assign('header', enCryptRestUri('/Addr/edit'));
        }
        $this->builderForm()->keyId()
        ->keyText('linkname', '收货人姓名', 1)
        ->keyText('mobile', '收货人手机号', 1)
        ->keyText('tel', '收货人电话', '', '电话号码格式为:020-22882288')
        ->keyCity(array('province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'), '选择地区', 1)
        ->keyTextArea('street', '详细地址', 1)
        ->keyText('postcode', '邮政编码')
        ->keySelect('is_default', '是否为默认', array(1=>'设为默认',0=>'不为默认'),1)
        ->data($this->_data['data'])
        ->view();
        $this->assign('title', $id > 0 ? '编辑' : '添加');
        C('seo', ['title' => $id > 0 ? '编辑地址' : '添加地址']);
        $this->display();
    }

    /**
     * 创建地址
     */
    public function create() {
        $data = [];
        $id = I('get.id', 0, 'int');
        $config['action'] = U('/run/authrun');
        $title = '创建收货地址';
        if ($id > 0) {
            $this->authApi('/SendAddress/view', ['id' => I('get.id')]);
            if ($this->_data['code'] == 1) $data = $this->_data['data'];
            $config['headers'] = enCryptRestUri('/Addr/edit');
            $title = '修改收货地址';
            //$this->assign('header', enCryptRestUri('/Addr/edit'));
        }

        $config['gourl']    =   '"' . U('/addr') . '"';

        $form = Form::getInstance($config)
            ->hidden(['name' => 'id', 'value' => $data['id']])
            ->text(['name' => 'linkname', 'title' => '收货人姓名', 'value' => $data['linkname'], 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'mobile', 'title' => '手机号码', 'value' => $data['mobile'], 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'tel', 'title' => '电话号码', 'value' => $data['tel'], 'tips' => '电话号码格式为:020-22882288', 'validate' => ['isTel']])
            ->district(['name' => ['province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'], 'value' => ['province' => $data['province'], 'city' => $data['city'], 'district' => $data['district'], 'town' => $data['town']], 'title' => '选择地区', 'require' => 1])
            ->textarea(['name' => 'street', 'title' => '详细地址', 'value' => $data['street'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,255]']])
            ->number(['name' => 'postcode', 'title' => '邮政编码', 'value' => $data['postcode'], 'validate' => ['number', 'rangelength' => '5,6']])
            ->checkbox(['name' => 'is_default', 'title' => '设为默认', 'value' => $data['is_default'], 'options' => [1 => '设为默认']])
            ->submit(['title' => $title])
            ->create();

        $this->assign('title', $title);
        $this->assign('form', $form);
        $this->display();

    }
}