<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/29
 * Time: 9:26
 */

namespace Common\Behavior;


use Common\Builder\Auth;
use Common\Builder\R;
use Think\Behavior;

/**
 * 权限检测
 *
 * Class InitCheckAccess
 * @package Common\Behavior
 */

class InitCheckAccessBehavior extends Behavior
{
    public function run(&$content)
    {
        if (session('user.parent_uid')) {   //权限判断
            $key    = 'concat';
            $module = null;
            if (IS_AJAX && IS_POST && (__ACTION__ == '/Api/api' || isset($_GET['ret']))) {
                $key    = 'mapping_rest';
                $module = I('post.apiurl') ? : I('get.ret');
            }
            if (Auth::getInstance($key, $module)->check() == false) {
                if (IS_AJAX) {
                    if (IS_POST) {
                        R::ajaxReturn(['code' => 0, 'msg' => '您没有权限进行访问或操作！']);
                    }
                    die('<div class="text-center"><i class="fa fa-lock" style="font-size: 80px;color: #F0F0F0;"></i></div> <div class="text-center text_red fs18">您没有权限进行访问或操作！</div>');
                }
                redirect(DM('www', '/noAccess'));
                exit();
            }
        }
    }
}