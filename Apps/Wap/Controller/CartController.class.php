<?php
namespace Wap\Controller;
use Common\Common\TestUser;
use Think\Controller;
class CartController extends CommonController {
    public function index(){		
		$this->display();
    }
	
    public function cart_next() {
        $apiUrl =   '/CartVer2/create_orders';
        if (isset($_GET['spm']) && I('get.spm') != 'undefined') {
            $spm    =   I('get.spm');
            if (!empty($spm)) $spm = deCryptRestUri($spm);
            //$spm    =   strstr($spm, '_', true);    //取出活动ID
            $spm    =   strpos($spm, '_');
            if ($spm !== false) {
                $apiUrl =   '/CartVer2/create_activity_orders';
            }
        }
        $this->assign('apiurl', $apiUrl);
        $this->display();
    }

    /**
     * 选择支付类型
     */
    public function paytype() {
        $file = '';
        if (in_array($_SESSION['user']['id'], TestUser::UID)) $file = 'dt_paytype';
        $this->display($file);
    }
}