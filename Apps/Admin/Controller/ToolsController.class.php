<?php
/**
 * ------------------------------------------
 * 辅助工具
 * ------------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ------------------------------------------
 * 2016-11-14
 * ------------------------------------------
 */
namespace Admin\Controller;
use Think\Controller;
class ToolsController extends CommonModulesController {
    public function index(){
        $this->display();
    }


    /**
     * 更新商品权重
     */
    public function goods_pr(){
        $map['status'] = 1;
        $count = M('goods')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('goods')->where($map)->page($p)->limit(200)->order('id desc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }
        goods_pr($list);

        usleep(1000);
        gourl('/Tools/goods_pr/p/'.($p+1));
    }

    /**
     * 更新店铺权重
     */
    public function shop_pr(){
        $map['status'] = 1;
        $count = M('shop')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('shop')->where($map)->page($p)->limit(200)->order('id desc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        foreach($list as $val){
            shop_pr($val);
        }


        usleep(1000);
        gourl('/Tools/shop_pr/p/'.($p+1));
    }


    /**
     * 更新店铺权重
     */
    public function shop_goods_num(){
        $map['status'] = 1;
        $count = M('shop')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('shop')->where($map)->page($p)->limit(200)->order('id desc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        foreach($list as $val){
            $goods_num = M('goods')->where(['shop_id' => $val,'status' => 1])->count();
            M('shop')->where(['id' => $val])->save(['goods_num' => $goods_num]);
            usleep(500);
        }

        gourl('/Tools/shop_goods_num/p/'.($p+1));
    }

    /**
     * 店铺商品按时间段均匀分布
     */
    public function avg_uptime(){
        $map['status'] = 1;
        $count = M('shop')->where($map)->count();
        $page = ceil($count/50);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('shop')->where($map)->page($p)->limit(50)->order('id desc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        foreach($list as $val){
            $this->shop_avg_uptime($val);
        }

        gourl('/Tools/avg_uptime/p/'.($p+1));
    }

    public function test(){
        $this->shop_avg_uptime(160);
    }

    public function shop_avg_uptime($shop_id){
        //时间段及商品分布占比，靠前的时间段为优先
        $times = [
            ['10:00',7],
            ['11:00',7],
            ['14:00',7],
            ['15:00',7],
            ['16:00',7],
            ['17:00',7],
            ['19:00',7],
            ['20:00',7],
            ['21:00',7],
            ['22:00',7],
            ['13:00',4],
            ['12:00',4],
            ['18:00',4],
            ['23:00',3],
            ['09:00',3],
            ['00:00',2],
            ['08:00',2],
            ['07:00',2],
            ['01:00',2],
            ['02:00',1],
            ['06:00',1],
            ['03:00',1],
            ['04:00',0.5],
            ['05:00',0.5],
        ];

        $goods_num = M('goods')->where(['shop_id' => $shop_id,'status' => 1])->count();

        $ids = M('goods')->where(['shop_id' => $shop_id,'status' => 1])->getField('id',true);
        //dump($goods_num);
        //$day = date('Y-m-d',time() - 86400);
        $n=0;
        foreach($times as $k => $v){
            $num = ceil(($v[1] / 100) * $goods_num);
            $n +=$num;
            if($n > $goods_num || $num == 0) break;
            //dump($num);
            $ids_day = array_slice($ids,$k * $num,$num);
            //dump(implode(',',$ids_day));

            //7天同个时间段均匀分布
            $num7 = ceil($num / 7);
            for($i=1;$i<8;$i++){
                if($num7 * $i > $num) {
                    $tmp = $num - ($num7 * ($i-1));
                    if($tmp < 1) break;

                    $ids_time = array_slice($ids_day,($i-1) * $num7,$tmp);
                    $num7 = $tmp;
                }else{
                    $ids_time = array_slice($ids_day,($i-1) * $num7,$num7);
                }

                //dump(implode(',',$ids_time));

                //1小时中均匀分布
                $sec = intval(3600 / $num7);    //每隔$sec秒上架一款商品
                //dump($sec);
                foreach($ids_time as $key => $vl){
                    $day = strtotime(date('Y-m-d',time() - (86400 * $i)) . ' '.$v[0]);
                    $day = date('Y-m-d H:i:s',$day + $key * $sec);
                    //dump($day);
                    M('goods')->where(['id' => $vl])->save(['uptime' => $day]);
                    usleep(rand(5,20));
                }
            }

            //echo '<br>-------------------------------<br>';
        }

        //echo '<br>=================================<br>';
    }

    /**
     * 修改抽奖BUG
     */
    public function luckdraw_fix(){
        $limit = '0,1000';
        if(I('get.limit')) $limit=I('get.limit');

        $map = ['_string'=>'type=1 and date_format(atime,"%Y-%m-%d")="2016-12-11"'];
        $list =M('luckdraw_chance_free')->where($map)->field('count(*) as num,atime,uid,status')->group('uid')->limit($limit)->select();

        //dump($list);
        foreach($list as $val){
            if($val['num'] > 1){
                //dump($val);
                $map['uid'] = $val['uid'];
                $ls = M('luckdraw_chance_free')->where($map)->select();
                //dump($ls);
                $ids=array();
                foreach($ls as $k => $v){
                    if($k>0 && $v['status']==1) $ids[]=$v['id'];
                }

                if(!empty($ids)){
                    dump($ids);
                    M('luckdraw_chance_free')->where(['id' => ['in',$ids]])->save(['status'=>3]);
                }
            }
        }
    }


}


