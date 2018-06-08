<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/20
 * Time: 14:55
 */

namespace Home\Controller;


use Think\Controller;

/**
 * 授权跳转
 *
 * Class RedirectController
 * @package Home\Controller
 */

class RedirectController extends Controller
{
    public function index() {
        $cfg = getSiteConfig();
        C('cfg', getSiteConfig());
        $domain = I('get.domain');
        $action = I('get.action');
        $url = erp_url($cfg['erp']['domain'][$domain] . $action);
        redirect($url);
    }

    /**
     * subject: 聊天链接
     * api: im
     * author: Mercury
     * day: 2017-05-20 14:23
     * [字段名,类型,是否必传,说明]
     */
    public function im()
    {
        if (session('user')) {
            $url = 'https://imweb.dtfangyuan.com:9443/storeim/kufuhttp2.html?username=' . session('user.nick') . '*' . session('user.erp_uid');
        } else {
            $url = DM('user', '/login');
        }
        redirect($url);
    }
}