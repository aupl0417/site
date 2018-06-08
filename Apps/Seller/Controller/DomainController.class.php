<?php
namespace Seller\Controller;
use Common\Form\Form;

class DomainController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        //$this->authApi('/ShopSetting/shop_info');
        $scheme =   $_SERVER['HTTPS']=='on'?'https://':'http://';
        $domain =   substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'],'.'));
        $domain =   $this->shop_info['domain'] ? $scheme .  $this->shop_info['domain'] . $domain : '未设置域名';
        if (C('DEFAULT_THEME') == 'default') {
            $this->builderForm()
                ->keyHtmltext('店铺当前域名', $domain)
                ->keyText('domain', '域名前缀', 1)
                ->view();
        } else {
            $config['action'] = U('/run/authrun');
            $form = Form::getInstance($config)
                ->text(['name' => 'domain', 'title' => '域名前缀', 'require' => 1, 'validate' => ['required']])
                ->submit(['title' => '设置域名'])
                ->create();
            $this->assign('domain', $domain);
            $this->assign('form', $form);
        }

        C('seo', ['title' => '域名设置']);
        $this->display();
    }
    
    /**
     * 域名检测
     */
    public function check() {
        $domain =   I('get.domain');
        $this->authApi('/OpenShop/check_domain', ['domain' => $domain]);
        if ($this->_data['code'] == 1) {
            echo 'true';
        } else {
            echo 'false';
        }
    }
}