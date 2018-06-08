<?php
namespace User\Controller;
class IndexController extends AuthController {
    
    public function index() {
        if (isset($_SESSION['user'])) {
            redirect(DM('seller'));
        } else {
            redirect(DM('user', '/login'));
        }
    }
}