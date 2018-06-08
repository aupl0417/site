<?php
namespace User\Controller;
use Think\Controller;
class LogoutController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['user']) && !isset($_COOKIE['remember'])) {
            redirect(DM('user'));
            exit;
        }
        session('user', null);
        session('supplier_info', null);
        session_destroy();
        cookie('remember', null);
        redirect(DM('user'));
    }
}