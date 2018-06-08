<?php
namespace Cart\Controller;
use Home\Controller\CommonController;
class AuthController extends CommonController {
    public function _initialize() {
        parent::_initialize();
        if (!isset($_SESSION['user'])) {
            redirect(DM('user', '/login'));
            exit;
        }
    }
}