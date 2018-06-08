<?php
namespace Home\Controller;
class ChatController extends CommonController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $user   =   '';
        if (isset($_SESSION['user']['id'])) $user   =   session('user.nick') . '*' . session('user.erp_uid');
        $this->assign('user', $user);
        $this->display();
    }
}