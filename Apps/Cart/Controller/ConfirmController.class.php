<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午2:14:59
// +----------------------------------------------------------------------
 */
namespace Cart\Controller;
use Common\Form\Form;

class ConfirmController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $spm    =   I('get.spm');
        if (!empty($spm)) $spm = deCryptRestUri($spm);
        $spm    =   strstr($spm, '_', true);    //取出活动ID
        $this->authApi('/CartVer2/selected_goods', ['spm' => $spm], 'spm')->with();
        if (!$this->_data['data']['list']) {
            redirect(DM('www'));exit;
        }
        //取出无需签名参数
        $nosign = '';
        foreach ($this->_data['data']['list'] as $k => $v) {
            $nosign .=  'coupon_' . $v['express_tpl_id'] . ',' . 'remark_' . $v['express_tpl_id'] . ',';
        }
        $this->authApi('/Address/address_list')->with('addr');
        $this->assign('nosign', enCryptRestUri(trim($nosign, ',')));
        $this->assign('action', $spm ? enCryptRestUri('/Confirm/spm') : enCryptRestUri('/Confirm/index'));
        $this->assign('expressAction', enCryptRestUri('/Cart/express_price'));
        C('seo', ['title' => '确认订单']);
        $this->display();
    }
    
    public function addr() {
        if (C('DEFAULT_THEME') == 'default') {
            $this->builderForm()
                ->keyText('linkname', '收货人姓名', 1)
                ->keyText('mobile', '收货人手机号', 1)
                ->keyText('tel', '收货人电话', '', '电话号码格式为:020-22882288')
                ->keyCity(array('province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'), '选择地区', 1)
                ->keyTextArea('street', '详细地址', 1)
                ->keyText('postcode', '邮政编码')
                ->keyCheckBox('is_default', '设为默认', [1 => '设为默认'])
                ->view('data');
            $this->assign('Addradd', enCryptRestUri('/Addradd'));
        } else {
            $config['headers'] = enCryptRestUri('/Addradd');
            $config['action'] = U('/run/authrun');
            $form = Form::getInstance($config)
                ->text(['name' => 'linkname', 'title' => '收货人姓名', 'require' => 1, 'validate' => ['required', 'rangelength' => '[1,10]']])
                ->number(['name' => 'mobile', 'title' => '收货人手机号码', 'require' => 1, 'validate' => ['required', 'number', 'isMobile']])
                ->text(['name' => 'tel', 'title' => '收货人电话号码', 'validate' => ['isTel'], 'tips' => '电话号码格式为:020-22882288'])
                ->district(['name' => ['district' => 'district', 'province' => 'province', 'city' => 'city', 'town' => 'town'], 'title' => '选择地区', 'require' => 1])
                ->textarea(['name' => 'street', 'title' => '详细地址', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
                ->number(['name' => 'postcode', 'title' => '邮政编码', 'validate' => ['rangelength' => '[5,6]', 'number']])
                ->checkbox(['name' => 'is_default', 'title' => '设置为默认', 'options' => [1 => '设置默认']])
                ->submit(['title' => '创建收货地址'])
                ->create();
            $this->assign('form', $form);
        }

        $this->display();
    }
	
	//获取地址欣喜
	public function update_addr(){
		$this->authApi('/Address/address_list', '', 1);
		$data = $this->_data['data'];
		if ($data) {
		    foreach ($data as &$v) {
		        $v['mobile'] = hiddenStr($v['mobile']);
            }
        }
        unset($v);
		$this->ajaxReturn($data);
	}
}