<?php
namespace Ad\Controller;
use Home\Controller\CommonController;

class AuthController extends CommonController {
    protected $_map =   [];
    public function _initialize() {
        parent::_initialize();
        if (!isset($_SESSION['user'])) {
            redirect(DM('user', '/login'));
            exit;
        }
        $this->_map['id']   =   1;
        //是否已开店
        $this->authApi('/OpenShop/is_open');
        if($this->_data['code']!=1){
            redirect(DM('seller', '/opens'));
        }
    }
}