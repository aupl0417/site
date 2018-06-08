<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2016/12/23
 * Time: 14:27
 */

namespace Home\Controller;

/**
 * 便民服务-用于跳转
 * Class ConvenienceController
 * @package Home\Controller
 */

class ConvenienceController extends CommonController
{
    protected $retUrls = [
        '/assetCenter/dataflow1',
        '/assetCenter/phonebill1',
    ];
    public function index() {
        $returl = I('get.returl');
        if ($returl && in_array($returl, $this->retUrls)) {
            $erp = getSiteConfig('erp');
            $url = erp_url($erp['domain']['pay'] . $returl);
            redirect($url);
        }
        redirect(DM('www'));
    }
}