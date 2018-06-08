<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/5/3
 * Time: 15:41
 */

namespace Mobile\Controller;


class DrawluckController extends CommonController
{
    /**
     * subject: 抽奖聚合页面
     * api: index
     * author: Mercury
     * day: 2017-05-09 14:14
     * [字段名,类型,是否必传,说明]
     */
    public function index()
    {
        if (session('user') == false) if (session('user') == false) $this->redirect('/ucenter');
        $res = '';//$this->doApi2('/luckdraw/isFree', ['erp_uid' => session('user.erp_uid')]);
        $this->assign('res', $res);
        $this->display();
    }

    /**
     * subject: 已中奖列表
     * api: lists
     * author: Mercury
     * day: 2017-05-09 14:14
     * [字段名,类型,是否必传,说明]
     */
    public function lists()
    {
        if (session('user') == false) $this->redirect('/ucenter');
        $p = I('get.p', 1, 'int');  //分页
        $res = $this->doApi2('/luckdraw/lists', ['openid' => session('user.openid'), 'p' => $p]);
        //if (IS_AJAX) $this->ajaxReturn(['code' => $res['code'], 'msg' => $res['msg'], 'data' => $res['data']['list']]);
        $this->assign('data', $res['data']);
        $this->display();
    }

    public function test()
    {
        $openid = 'b3afac450126aea7e45edb9588ad5323';
        $p      = I('get.p', 1, 'int');
        for ($i=0;$i<50;$i++) {
            $res = $this->doApi2('/luckdraw/post', ['id' => 37, 'openid' => $openid]);
            if ($res['code'] != 1) writeLog($res);    //如果请求失败则写入日志
            usleep(1);
        }
        if ($p > 2) exit();
        gourl(DM('m', '/drawluck/test/p/' . ($p+1)));
    }

    /**
     * subject: 异步请求
     * api: ajaxLists
     * author: Mercury
     * day: 2017-05-12 11:02
     * [字段名,类型,是否必传,说明]
     */
    public function ajaxLists()
    {
        if (session('user') == false) $this->ajaxReturn(['code' => 401]);
        $p = I('get.p', 1, 'int');  //分页
        $res = $this->doApi2('/luckdraw/lists', ['openid' => session('user.openid'), 'p' => $p]);
        $this->ajaxReturn(['code' => $res['code'], 'msg' => $res['msg'], 'data' => $res['data']['list']]);
    }

    /**
     * subject: 进行抽奖
     * api: post
     * author: Mercury
     * day: 2017-05-09 14:14
     * [字段名,类型,是否必传,说明]
     */
    public function post()
    {
        if (IS_POST) {
            $s = microtime(true);
            $id = I('post.id', 0, 'int');
            //C('DEBUG_API', true);
            if (session('user') == false) $this->ajaxReturn(['code' => 401, 'msg' => '请登录']);
            $res = $this->doApi2('/luckdraw/post', ['id' => $id, 'openid' => session('user.openid')]);
            $e = microtime(true);
            //writeLog($e - $s);
            $this->ajaxReturn($res);
            //$this->ajaxReturn(['code' => 1, 'msg' => 'ok', 'data' => rand(1,4)]);
        }
    }

    /**
     * subject: 抽奖
     * api: item
     * author: Mercury
     * day: 2017-05-09 14:14
     * [字段名,类型,是否必传,说明]
     */
    public function item()
    {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $res = $this->doApi2('/luckdraw/item', ['id' => $id]);
            if ($res['code'] == 1) {
                $this->assign('data', $res['data']);
                $this->display();
            }
        }
    }

    /**
     * subject: 购买抽奖机会
     * api: buy
     * author: Mercury
     * day: 2017-05-10 17:48
     * [字段名,类型,是否必传,说明]
     */
    public function buy()
    {
        $id = I('post.id', 0, 'int');
        if ($id > 0) {
            //C('DEBUG_API', true);
            if (session('user') == false) $this->ajaxReturn(['code' => 401, 'msg' => '请登录']);
            $res = $this->doApi2('/luckdraw/buy', ['id' => $id, 'openid' => session('user.openid'), 'random' => md5(microtime(true))]);
            $this->ajaxReturn($res);
        }
    }

    public function receiveScore()
    {
        $id = I('post.id', 0, 'int');
        if ($id > 0) {
            //C('DEBUG_API', true);
            if (session('user') == false) $this->ajaxReturn(['code' => 401, 'msg' => '请登录']);
            $res = $this->doApi2('/luckdraw/receiveScore', ['id' => $id, 'openid' => session('user.openid'), 'random' => md5(microtime(true))]);
            $this->ajaxReturn($res);
        }
    }

    public function free() {
        $res = $this->doApi2('/luckdraw/free', ['erp_uid' => session('user.erp_uid')]);
        $this->ajaxReturn($res);
    }
}