<?php
namespace Seller\Controller;
use Common\Form\Form;

class SettingController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        # 获取店铺开店类型
        $this->api('/OpenShop/shop_type');
        $type   =   [];
        foreach ($this->_data['data'] as $v) {
            $type[$v['id']] =   $v['type_name'];
        }

        if (C('DEFAULT_THEME') == 'default') {
            $this->builderForm()
                ->keyHtmltext('店铺名称', $this->shop_info['shop_name'])
                # ->keyHtmltext('店铺类型', $type[$this->shop_info['type_id']])
                ->keySingleImages('shop_logo', '店铺logo', 1)
                ->keyTextArea('about', '店铺描述', 1)
                ->keyText('mobile', '手机号码', 1)
                ->keyText('tel', '电话号码')
                ->keyText('wang', '阿里旺旺')
                ->keyText('qq', '腾讯QQ', 1)
                ->keyText('email', '邮箱')
                # ->keyText('huoyan', '货源', 1)
                ->keyCity(array('province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'), '地区', 1)
                ->keyTextArea('street', '详细地址', 1)
                # ->keySelect('inventory_type', '结算方式', session('user.type') == 1 ? [0=>'扣除货款方式',1=>'库存积分分发方式'] : [0=>'扣除货款方式'], 1)
                # ->keyTextArea('remark', '备注')
                ->data($this->shop_info)
                ->view();
        } else {
            $config['action'] = U('/run/authrun');
            //dump($this->shop_info);
            $form = Form::getInstance($config)
                ->textarea(['name' => 'about', 'title' => '店铺描述', 'value' => $this->shop_info['about'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
                ->singleImages(['name' => 'shop_logo', 'title' => '店铺logo', 'require' => 1, 'value' => $this->shop_info['shop_logo']])
                ->text(['name' => 'mobile', 'title' => '手机号码', 'value' => $this->shop_info['mobile'], 'require' => 1, 'validate' => ['required', 'isMobile']])
                ->text(['name' => 'tel', 'title' => '电话号码', 'value' => $this->shop_info['tel'], 'value' => '', 'validate' => ['isTel']])
                ->text(['name' => 'qq', 'title' => '腾讯QQ', 'value' => $this->shop_info['qq'], 'require' => 1, 'validate' => ['number', 'min' => 10001, 'max' => 100000000000]])
                ->text(['name' => 'wang', 'title' => '阿里旺旺', 'value' => $this->shop_info['wang']])
                ->text(['name' => 'email', 'title' => '电子邮箱', 'value' => $this->shop_info['email'], 'validate' => ['email']])
                ->district(['name' => ['province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'], 'title' => '所在地区', 'value' => ['province' => $this->shop_info['province'], 'city' => $this->shop_info['city'], 'district' => $this->shop_info['district'], 'town' => $this->shop_info['town']], 'require' => 1])
                ->textarea(['name' => 'street', 'title' => '详细地址', 'value' => $this->shop_info['street'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
                ->submit(['title' => '保存店铺信息'])
                ->create();
            $this->assign('form', $form);
        }
        C('seo', ['title' => '店铺设置']);
        $this->display();
    }
    

    public function inventory_type(){
        # 获取店铺设置信息
        $this->authApi('/ShopSetting/shop_info');
        # dump(session());
        # dump($this->_data['data']);
        # echo deCryptRestUri('MDAwMDAwMDAwMK5nh57JfYrSx8_Fr5aKbZyNkc1v');
        C('seo', ['title' => '结算方式设置']);
        # echo $this->_data['data']['inventory_type'];
        $this->assign('shop_info',$this->_data['data']);
        $this->display();
    }

    /**
     * 结算方式
     */
/*     public function inventory() {
        $this->authApi('/ShopSetting/shop_info')->with();
        $options[] = '扣除货款方式';
        if (session('user.type') == 1) $options[] = '库存积分方式';

        $form = Form::getInstance(['action' => U('/run/authrun')])
            ->radio(['name' => 'inventory_type', 'title' => '结算方式', 'value' => $this->_data['data']['inventory_type'], 'options' => $options, 'require' => 1, 'validate' => ['required']])
            ->password(['name' => 'password_pay', 'title' => '安全密码', 'require' => 1, 'validate' => ['required', 'rangelength' => '[6,6]', 'number']])
            ->submit(['title' => '设置结算方式'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '结算方式设置']);
        $this->display();
    } */

    /**
     * 公告设置
     */
    public function shop_news(){
        $infoMessage = $this->doApi('/ShopNews/info',['shop_id' => session('user.shop_id')],'',1);

        $info = $infoMessage['code'] == 1 ? $infoMessage['data'] : [];
        # var_dump($info);exit;
        $info['remark'] = html_entity_decode($info['remark']);
        $this->builderForm()->keyUeditor('remark', '公告内容','', 1,'公告内容，将在本店商品详情页面展示。')->keySelect('status','状态', [1 => '显示',0 => '禁用'],1)->data($info)->view();
        $this->assign('ACTION', enCryptRestUri('/Setting/create'));
        $this->display();
    }

    /**
     *
     * 店铺公告设置
     *
     */
    public function news() {
        $data = $this->doApi('/ShopNews/info',['shop_id' => session('user.shop_id')],'',1);
        $config = [
            'action'    =>  U('/run/authRun'),
            'headers'   =>  enCryptRestUri('/Setting/create'),
        ];
        $form = Form::getInstance($config)
            //->ueditor(['name' => 'remark', 'title' => '公告内容', 'tips' => '当前公告将在本店商品详情页面展示', 'value' => html_entity_decode($data['data']['remark']), 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,10000]']])
            ->textarea(['name' => 'remark', 'title' => '公告内容', 'tips' => '', 'value' => $data['data']['remark'], 'require' => 1, 'validate' => ['required'],'style'=>'height:300px;'])
            ->radio(['name' => 'status', 'title' => '状态', 'value' => $data['data']['status'], 'options' => ['隐藏', '显示'], 'require' => 1, 'validate' => ['required']])
            ->submit(['title' => '提交表单'])
            ->create();
        C('seo', ['title' => '设置店铺公告']);
        $this->assign('form', $form);
        $this->display();
    }

} 