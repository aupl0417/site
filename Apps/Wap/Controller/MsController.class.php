<?php
/**
 * -------------------------------------
 * 年底活动 - 秒杀、满减
 * -------------------------------------
 * Author：Lazycat <673090083@qq.com>
 * -------------------------------------
 * 2016-12-19
 * -------------------------------------
 */
namespace Wap\Controller;
class MsController extends CommonController{
    public function index(){
        $do = new \Wap\Controller\MiaoshaController();
        $this->assign('current_num',$do->current_num());
        $arr = array(
            array('day' => date('Y-m-d'),'time' => '10:00'),
            array('day' => date('Y-m-d'),'time' => '15:00'),
            array('day' => date('Y-m-d',time()+86400),'time' => '10:00'),
            //array('day' => date('Y-m-d',time()+86400),'time' => '15:00'),
        );
        foreach($arr as $val){
            $list[] = [
                'day' => $val,
                'data'=> $do->item($val),
            ];
        }
        # print_r($list);exit;

        $this->assign('miaosha_list',$list);
        $this->display();
    }
}