<?php
namespace User\Controller;
use Think\Controller;
class VerifyController extends Controller {
    /**
     * 验证码
     */
    public function index() {
        ob_clean();
        $h  =   isset($_GET['h']) ? I('get.h') : 35;
        $w  =   isset($_GET['w']) ? I('get.w') : 100;
        $s  =   isset($_GET['s']) ? I('get.s') : 14;
        $l  =   isset($_GET['l']) ? I('get.l') : 4;
        $Verify = new \Think\Verify;
        $Verify->useImgBg = false;
        $Verify->imageH   = $h;
        $Verify->imageW   = $w;
        $Verify->fontSize = $s;
        $Verify->fontttf  = '5.ttf';
        $Verify->useNoise = false;
        $Verify->length   = $l;
        $Verify->entry();
    }
}