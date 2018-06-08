<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 城市列表
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-12
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class CityController extends ApiController {
    //protected $action_logs = array('ad');

    /**
     * subject: 读取城市列表
     * api: /City/city_level
     * author: Lazycat
     * day: 2017-01-12
     *
     * [字段名,类型,是否必传,说明]
     * param: level,int,1,读取层级，选填
     */
    public function city_level(){
        $this->check(array_keys(I('post.')),false);

        $res = $this->_city_level($this->post);
        $this->apiReturn($res);
    }


    public function _city_level($param=null){
        $level = $param['level'] ? $param['level'] : 1;
        $list=get_category(array('table'=>'area','field'=>'id,sid,a_name,sub_name','level'=> $level,'map'=>[['status' =>1],['status' =>1],['status' =>1]],'cache_name'=>'area_level_'.$level,'cache_time' => 2592000));

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '找不到地区！'];
    }

    /**
     * subject: 读取城市子级
     * api: /City/city_item
     * author: Lazycat
     * day: 2017-01-12
     *
     * [字段名,类型,是否必传,说明]
     * param: sid,int,0,父级ID，默认为0，有缓存
     */
    public function city_item(){
        $this->check(array_keys(I('post.')),false);

        $res = $this->_city_item($this->post);
        $this->apiReturn($res);
    }

    public function _city_item($param=null){
        $sid = $param['sid'] ? $param['sid'] : 0;

        $list = M('area')->cache(true)->where(['sid' => $sid])->field('id,sid,a_name,sub_name')->order('sort asc,id asc')->select();
        if($list){
            foreach($list as $key => $val){
                $list[$key]['dnum'] = M('area')->where(['sid' => $val['id']])->count();
            }

            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

}