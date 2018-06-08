<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 上午10:35:52   判断用户是否由设置支付密码
// +----------------------------------------------------------------------
 */
namespace Common\Behavior;
class InitCheckPayPasswordBehavior {
    public function run(&$content) {
        if ($_SESSION['user']['id'] > 0 && empty($_SESSION['user']['password_pay'])) {
            $pattern    =   '/<input([^>]*)\s*name="password_pay"([^>]*)\s*>/';
            preg_match_all($pattern,$content,$match);   
            $count      =   count($match[0]);
            if ($count > 0) {
                $tmp=explode($match[0][0],$content);
                $input  =   str_replace('class="', 'disabled=disabled class="', $match[0][0]);
                for($i=0;$i<$count;$i++){
                    $tmp[$i].= '<a href="javascript:;" data-type="vmodal" data-url="/account/payPass" data-title="设置安全密码" data-width="800px"><strong class="text_red">立即设置安全密码</strong></a>';
                }
                $content=implode('', $tmp);
                $pattern1   =   '/<button([^>]*)\s*passwordPayButton([^>]*)\s*>/';
                preg_match_all($pattern1,$content,$button);
                $tmp        =   explode($button[0][0],$content);
                $n          =   count($button[0]);
                if ($n > 0) {
                    for ($j=0; $j < $n; $j++) {
                        $tmp[$j]    .=   str_replace('class="', 'class="disabled ', $button[0][0]);
                    }
                    $content=implode('', $tmp);
                }
                
            }
        }
    }
}