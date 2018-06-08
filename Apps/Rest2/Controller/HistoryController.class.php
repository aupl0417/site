<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 历史记录
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-03-03
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class HistoryController extends ApiController {
    protected $action_logs = array();

    /**
     * subject: 商品浏览历史
     * api: /History/goods
     * author: Lazycat
     * day: 2017-03-03
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     */
    public function goods(){
        $this->check('',false);

        $res = $this->_agreement($this->post);
        $this->apiReturn($res);
    }

    public function _goods($param){
        $do=M('help');
        $rs=$do->cache(true)->where(['id' => 4])->field('name,content')->find();
        if($rs) {
            $do->where(['id' => 4])->setInc('hit',1,60);
            $rs['content']=html_entity_decode($rs['content']);

            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];   //找不到记录
    }


}