<?php
namespace Seller\Controller;
use Common\Builder\Auth;
use Common\Builder\R;
use Home\Controller\CommonController;
class AuthController extends CommonController {
    protected $_map =   [];
	protected $shop_info 	=[];
    
    public function _initialize() {
        parent::_initialize();
        if (!isset($_SESSION['user'])) {
            redirect(DM('user', '/login'));
            exit;
        }

//        if (session('user.parent_uid')) {   //权限判断
//            if (Auth::getInstance()->check() == false) {
//                $this->display('Public/noAccess');
//                exit();
//            }
//        }
        $isOpen = session('user.is_open');
        $this->shop_info = session('user.shop_info');
        if ($isOpen == false) {
            $config = [
                'url' => [
                    'data' => '/OpenShop/is_open',
                    'shop_info' => '/ShopSetting/shop_info'
                ],
                'rest'=> [
                    'rest',
                    'rest'
                ],
                'data'=> [
                    ['openid' => session('user.openid')],
                    ['openid' => session('user.openid')]
                ],
            ];
            $res = R::getInstance($config)->multiCurl();
            if ($res['data']['code'] == 1) session('user.is_open', $res['data']['code']);
            if ($res['shop_info']['code'] == 1) session('user.shop_info', $res['shop_info']['data']);
            $isOpen = session('user.is_open');
            $this->shop_info = session('user.shop_info');
        }

        //是否已开店
//        $this->authApi('/OpenShop/is_open');
//        $this->assign('isOpen', $this->_data['code']);
//		$this->authApi('/ShopSetting/shop_info')->with('shop_info');
//		$this->shop_info=$this->_data['data'];

        if(session('user.level_id') == 9){
            $path_info = strtolower($_SERVER['PATH_INFO']);
            if($path_info == 'setting'){
                redirect(U('/Supplier/setting'));
            }else {
                redirect(U('/Supplier'));
            }
        }

        if(CONTROLLER_NAME!='Opens' && empty($this->shop_info)){
        	//redirect(DM('zhaoshang').'/Joinshop');
            redirect(DM('faq'));
        }
        $this->assign('isOpen', $isOpen);
        $this->_map['id']   =   $this->shop_info['id'];
        session('user.shop_type', $this->shop_info['type_id']);
    }





}