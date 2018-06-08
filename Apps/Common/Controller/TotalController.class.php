<?php
/**
*-------------------------------------
* 每天数据统计
*-------------------------------------
* Author: Lazycat <673090083@qq.com>
* ------------------------------------ 
*/
namespace Common\Controller;
use Think\Controller;
class TotalController extends Controller {
    private $day;    //要统计的时间

    public function _initialize() {
        $this->day  =   date('Y-m-d',time()-86400); //默认为统计前一天
    }

    /**
    * 设置属性
    */
    public function __set($name,$v){
        return $this->$name=$v;
    }

    /**
    * 获取属性
    */
    public function __get($name){
        return isset($this->$name)?$this->$name:null;
    }
    
    /**
    * 销毁属性
    */
    public function __unset($name) {
        unset($this->$name);
    }    
    /**
    * 用户统计 
    */
    public function user(){
        $do=M('user');
        $result['num']=$do->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        return $result;
    }

    /**
    * 店铺
    */
    public function shop(){
        $do=M('shop');
        $result['num']  = $do->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();

        $result['join'] = M('shop_join_info')->where(['status'=>0,'_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        return $result;
    }

    /**
    * 商品
    */
    public function goods(){
        $do=M('goods');
        $result['num']      = $do->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();
        $result['illegl']   = M('goods_illegl')->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();

        return $result;
    }

    /**
    * 广告
    */
    public function ad(){
        $do=M('ad');
        $res    =$do->query('select count(*) as num,sum(money_pay) as money from '.C('DB_PREFIX').'ad where date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"');
        $result =$res[0];
        return $result;
    }

    /**
    * 素材
    */
    public function sucai(){
        $do=M('ad_sucai');
        //$res    =$do->query('select count(*) as num,count(if(status=0,true,null)) as num0,count(if(status=1,true,null)) as num1 from '.C('DB_PREFIX').'ad where date_format(pay_time,"%Y-%m-%d")="'.$this->day.'"');
        $result['num']  = $do->where(['_string' => 'date_format(atime,"%Y-%m-%d")="'.$this->day.'"'])->count();

        return $result;
    }

    /**
    * 订单
    */

}