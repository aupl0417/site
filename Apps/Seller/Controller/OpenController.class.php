<?php
namespace Seller\Controller;
class OpenController extends AuthController {
    protected $_checkOpen   =   [];
    
    
    public function _initialize() {
		//该入口已停用，直接跳至招商频道
		redirect(DM('zhaoshang'));
		exit();
		
        parent::_initialize();
        $this->authApi('/Erp/check_open_shop');
        $this->_checkOpen   =   $this->_data;
        if ($this->_checkOpen['code'] == 1) {
            $this->redirect('/setting');
            exit;
        }
        
    }
    
    public function checkOpen() {
        if($this->_checkOpen['code'] != 1) {
            //$this->redirect('/open');
        }
    }
    
    /**
     * 判断用户是否能够开店
     */
    public function index() {
        $this->assign('data', $this->_checkOpen);
        $this->authApi('/OpenShop/step')->with('step');
        //$step_name=['未开始','已登录联系人资料','已填写品牌/类目/资质','已填写店铺资料','已签订合同','不符合要求被拒绝','已保证金','开店成功'];
        //$this->_data['data']['step'] =   0;
        $msg    =   '';
        if (!empty($this->_data['data'])) {
            switch ($this->_data['data']['step']) {
                case 1:
                    $this->redirect('/open/step4');
                    exit;
                    break;
                case 2:
                    $this->redirect('/open/step5');
                    exit;
                    break;
                case 3:
                    $this->redirect('/open/step6');
                    exit;
                    break;
                case 4:
                    $this->redirect('/open/step7');
                    exit;
                    break;
                case 5:
                    $msg    =   '<div class="bg-warning pd10" style="max-width:200px;margin:0 auto;"><i class="fa fa-exclamation-circle fs16 fl text_yellow"></i><p class="ml20 mb0 text_yellow">'.$this->_data['data']['step_name'].'</p></div>';
                    break;
                case 6:
                    break;
                case 7:
                    $this->redirect('/setting');
                    exit;
                    break;
            }
        }
        $this->assign('msg', $msg);
        C('seo', ['title' => '我要开店']);
        $this->display();
    }
    
    /**
     * 开店入驻协议
     */
    public function step1() {
        $this->checkOpen();
        $this->api('/OpenShop/agreement')->with();
        C('seo', ['title' => '开店入住协议']);
        $this->display();
    }
    
    /**
     * 商家入驻须知
     */
    public function step2() {
        $this->checkOpen();
        $this->api('/OpenShop/notice')->with();
        C('seo', ['title' => '商家入驻须知']);
        $this->display();
    }
    
    /**
     * 登记联系人信息
     */
    public function step3() {
        $this->checkOpen();
        $this->api('/OpenShop/shop_type');      //获取店铺开店类型
        $type   =   [];
        foreach ($this->_data['data'] as $v) {
            $type[$v['id']] =   $v['type_name'];
        }
        $this->authApi('/OpenShop/contact_info');
        $this->builderForm()
        ->keyText('linkname', '店铺负责人姓名', 1)
        ->keyText('mobile', '店铺负责人手机', 1)
        ->keyText('tel', '店铺负责人电话', 0, '店铺负责人电话,格式020-88888888')
        ->keyText('email', '店铺负责人邮箱', 1)
        ->keyText('qq', '店铺负责人QQ', 1)
        ->keySelect('type_id', '店铺类型', $type, 1)
        /*->keyText('op_linkname', '运营负责人姓名')
        ->keyText('op_mobile', '运营负责人手机')
        ->keyText('op_tel', '运营负责人电话')
        ->keyText('op_email', '运营负责人邮箱')
        ->keyText('cs_linkname', '售后负责人姓名')
        ->keyText('cs_mobile', '售后负责人手机')
        ->keyText('cs_tel', '售后负责人电话')
        ->keyText('cs_email', '售后负责人邮箱')
        ->keyText('fc_linkname', '财务负责人姓名')
        ->keyText('fc_mobile', '财务负责人手机')
        ->keyText('fc_tel', '财务负责人电话')
        ->keyText('fc_email', '财务负责人邮箱')
        ->keyText('tc_linkname', '技术负责人姓名')
        ->keyText('tc_mobile', '技术负责人手机')
        ->keyText('tc_tel', '技术负责人电话')
        ->keyText('tc_email', '技术负责人邮箱')*/
        ->keyText('rf_linkname', '退货联系人姓名', 1)
        ->keyText('rf_mobile', '退货联系人手机', 1)
        ->keyText('rf_tel', '退货联系人电话', 0, '退货联系人电话电话,格式020-88888888')
        //->keyText('rf_email', '退货联系人邮箱', 1)
        ->keyCity(array('province' => 'rf_province', 'city' => 'rf_city', 'district' => 'rf_district', 'town' => 'town'), '退货地址', 1)
        ->keyTextArea('rf_street', '退货详细地址', 1)
        ->keyText('rf_postcode', '退货邮编')
        ->data($this->_data['data'])
        ->view();
        $this->assign('headers', $this->_data['data'] ? enCryptRestUri('/Open/step3_edit') : enCryptRestUri('/Open/step3_add'));
        $this->assign('btn', !empty($this->_data['data']) ? 1 : 0);
        C('seo', ['title' => '登记联系人信息']);
        $this->display();
    }
    
    /**
     * 品牌、资质、类目
     */
    public function step4() {
        $this->checkOpen();
        $this->authApi('/OpenShop/brand')->with('brand');   //品牌
        $this->authApi('/OpenShop/cert')->with('cert');     //资质
        $this->authApi('/OpenShop/category')->with('cate'); //分类
        C('seo', ['title' => '添加资质']);
        $this->display();
    }
    
    /**
     * 店铺设置
     */
    public function step5() {
        $this->checkOpen();
        $this->authApi('/OpenShop/shop_info');
        $data               =   $this->_data['data'];
        $data['type_id']    =   $this->_data['shop_type']['id'];
        $data['inventory_type'] =   1;
        $this->builderForm()
        ->keyId('type_id')
        ->keyHtmltext('店铺类型', $this->_data['shop_type']['type_name'])
        ->keyText('shop_name', '店铺名称', 1)
        ->keyId('inventory_type')
        //->keySelect('inventory_type', '库存积分结算方式', [0 => '非即时结算', 1 => '即时结算'], 1, '<span class="text_yellow">注：非即时结算（即不用先购买库存积分，直接抵扣现金）,即时结算（须先购买库存积分）</span>')
        ->keyTextArea('about', '店铺描述', 1)
        ->data($data)
        ->view();
        C('seo', ['title' => '店铺设置']);
        $this->assign('btn', !empty($this->_data['data']) ? 1 : 0);
        $this->display();
    }
    
    /**
     * 阅读开店入驻合同
     */
    public function step6() {
        $this->checkOpen();
        $this->api('/OpenShop/contract')->with();
        C('seo', ['title' => '开店入驻合同']);
        $this->display();
    }
    
    /**
     * 确认已签订合同
     */
    public function step7() {
        $this->checkOpen();
        $this->authApi('/OpenShop/accept_contract')->with();
        $title  =   $this->_data['data'] ? '确认入驻合同' : '等待审核';
        C('seo', ['title' => $title]);
        $this->display();
    }
    
    /**
     * 资质，类目，品牌添加
     */
    public function addCheckInfo() {
        $type   =   I('get.type');
        $typeArr=   ['category', 'cert', 'brand'];
        if (!in_array($type, $typeArr)) {
            return;
        }
        
        if ($type == 'category' || $type == 'cert') {   //获取经验类目
            $options    =   [];
            $this->api('/OpenShop/category_list');
            foreach ($this->_data['data'] as $v) {
                $options[$v['id']]  =   $v['category_name'];
            }
        }
        
        switch ($type) {
            case 'category':
                $this->builderForm()
                ->keySelect('category_id', '一级类目', $options, 1)
                ->view();
                break;
            case 'cert':
                $this->builderForm()
                ->keySelect('category_id', '资质类目', $options, 1)
                ->keyText('cert_name', '资质名称', 1)
                ->keySingleImages('cert_images', '资质证书照片', 1)
                ->keyDate('expire_day', '期限')
                ->view();
                break;
            case 'brand':
                $this->builderForm()
                ->keyText('b_name', '品牌中文名', 1)->keyText('b_ename', '品牌英文名')
                ->keySingleImages('b_logo', '品牌LOGO', 1)
                ->keySingleImages('b_images', '品牌商标证书照片', '', '<span class="text_yellow">当没有品牌商标注册号的时候可为空</span>')
                ->keySingleImages('b_images2', '商标授理书照片', '', '<span class="text_yellow">当没有品牌商标证书照片的时候不能为空</span>')
                ->keyText('b_master', '品牌所有者', 1)
                ->keyText('b_code', '品牌商标注册号', 0, '当有品牌商标证书照片的时候不能为空')
                ->keyText('b_type', '品牌类型', 1)
                ->keyText('b_scope', '经营类型', 1)
                ->view();
                break;
        }
        
        $this->assign('uploadAction', enCryptRestUri('/Upload/curlUpload'));
        $this->assign('header', enCryptRestUri('/addCheckInfo/' . $type));
        $this->display();
    }

    /**
     * 检测域名
     */
    public function check() {
        $name   =   I('get.shop_name');
        $this->authApi('/OpenShop/check_shop_name', ['shop_name' => $name]);
        if ($this->_data['code'] == 1) {
            echo 'true';
        } else {
            echo 'false';
        }
    }
}