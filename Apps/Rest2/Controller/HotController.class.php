<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 推荐
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-13
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class HotController extends ApiController {
    //protected $action_logs = array('ad');

    /**
     * subject: Wap推荐列表
     * api: /Hot/hot
     * author: Lazycat
     * day: 2017-01-13
     *
     * [字段名,类型,是否必传,说明]
     * param: pagesize,int,1,每页读取记录数量，默认12条
     */
    public function hot(){
        $this->check(array_keys(I('post.')),false);

        $res = $this->_hot($this->post);
        $this->apiReturn($res);
    }


    /**
     * 读取广告
     * @param int $position_id 广告位ID
     */
    public function _hot($param=null){
        $map['status'] = 1;
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 12;
        $list = pagelist(array(
            'table'     => 'hot',
            'map'       => $map,
            'fields'    => 'id,images,name,url',
            'pagesize'  => $pagesize,
            'p'         => $param['p']
        ));

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '找不到地区！'];
    }
}