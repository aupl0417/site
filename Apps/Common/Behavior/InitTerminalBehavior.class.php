<?php
/**
 * 终端判断跳转
 */
namespace Common\Behavior;
use Think\Behavior;
class InitTerminalBehavior extends Behavior {
    
    private $arrDomain = ['oauth2.tangmall.net','pic.tangmall.net','img.tangmall.net'];
    private $oldHost   = 'tangmall.net';
    private $newHost   = 'trj.cc';
    
    
    public function run(&$content) {
        $domains     = strtolower(substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.')));
        //手机访问301定向到手wap版
        $host = strtolower($_SERVER['HTTP_HOST']);
        if ($this->is_wap() && !array_key_exists($domains, $this->domain()) && strpos(strtolower($_SERVER['PHP_SELF']),'/thumb/') === false) {
            header('HTTP/1.1 301 Moved Permanently');//发出301头部
            header('location:' . DM('m') . $this->parseUrl($domains));
            exit();
        }elseif(strpos($host,$this->oldHost) && !in_array($host,$this->arrDomain)){
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            header('HTTP/1.1 301 Moved Permanently');//发出301头部
            header('Location: https://'. str_replace($this->oldHost,$this->newHost,$host) . $request_uri);
            exit();
        }
    }
    
    /**
     *是否移动端访问访问
     *@return bool
     */
    private function is_wap(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
    
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
    
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array (
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
    
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            }
        }
    
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 不需要跳转的域名
     */
    private function domain() {
        $domains = C('APP_SUB_DOMAIN_RULES');
        unset($domains['www'], $domains['item']);
        return $domains;
    }
    
    //url解析
    private function parseUrl($domain) {
        if ($domain == 'www') return;
        if ($domain == 'item') {   //如果是商品链接
            $goodsId = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
            if (!is_numeric($goodsId)) {
                $goodsId = I('get.id');
            }
            $redirectUrl = '/Goods/view/id/'.$goodsId;
        } else {   //其他域名
            $redirectUrl = '/Shop/index/shop_id/' . $domain;
        }
        return $redirectUrl;
    }
}