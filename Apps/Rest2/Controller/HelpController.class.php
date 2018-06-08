<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 帮助中心
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-19
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class HelpController extends ApiController {
    protected $action_logs = array();

    /**
     * subject: 会员注册协议
     * api: /Help/agreement
     * author: Lazycat
     * day: 2017-01-19
     *
     * [字段名,类型,是否必传,说明]
     */
    public function agreement(){
        $this->check('',false);

        $res = $this->_agreement($this->post);
        $this->apiReturn($res);
    }

    public function _agreement($param){
        $do=M('help');
        $rs=$do->cache(true)->where(['id' => 4])->field('name,content')->find();
        if($rs) {
            $do->where(['id' => 4])->setInc('hit',1,60);
            $rs['content']=html_entity_decode($rs['content']);

            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];   //找不到记录
    }


    /**
     * subject: 关于乐兑
     * api: /Help/about
     * author: Lazycat
     * day: 2017-02-10
     *
     * [字段名,类型,是否必传,说明]
     */
    public function about(){
        $this->check('',false);

        $res = $this->_about($this->post);
        $this->apiReturn($res);
    }

    public function _about($param){
        $do=M('help');
        $rs=$do->cache(true)->where(['id' => 3])->field('name,content')->find();
        if($rs) {
            $do->where(['id' => 4])->setInc('hit',1,60);
            $rs['content']=html_entity_decode($rs['content']);

            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];   //找不到记录
    }

}