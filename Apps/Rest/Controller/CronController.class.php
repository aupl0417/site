<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 订单中各流程的超时处理
| 数据多的情况下要用队列来处理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Wap\Controller\CommonController;
use Common\Controller\OrdersExpireActionController;
use Common\Controller\TotalsController;
use Common\Builder\Queue;
set_time_limit(120);  //不限时处理
class CronController extends OrdersExpireActionController {
    protected $sw   = array();  //保存事务执行结果
    public function _initialize() {
        parent::_initialize();

        //由于请求接口需要认证身份，所以此项是必须的
        if(IS_POST){
            $_POST = array_merge($_POST,C('cfg.api'));
        }else $_POST=C('cfg.api');
    }


    /**
    * 获取任务item
    */
    public function item(){
        $type = I('post.type');
        if(empty($type)) exit();

        if(I('post.expire_time')) $this->expire_time = I('post.expire_time');
                
        $list=$this->$type();
        echo json_encode($list);
    }

    /**
    * 执行任务
    */
    public function job(){

        switch (I('post.type')) {
            //关闭超时未付款的订单
            case 'buyer_add_orders': 
                $res = $this->_close(I('post.val'));
            break;
            
            //买家长时间未确认收货，默认自动确认收货
            case 'buyer_confirm_orders':
                $res = $this->_confirm_orders(I('post.val'));
            break;

            //未发货退款，卖家长时间未响应，默认同意退款
            case 'seller_send_express':
                $res = $this->_refund_accept(I('post.val'));
            break;

            //已发货，退款，卖家长时间未响应，默认同意退款
            case 'buyer_refund_add':
                $res = $this->_refund_accept2(I('post.val'));
            break;

            //退款被拒绝后买家长时间未响应，默认取消退款
            case 'seller_not_accept':
                $res = $this->_refund_cancel(I('post.val'));
            break;

            //修改退款后卖家长时间未响应，默认为同意退款
            case 'buyer_refund_edit':
                $res = $this->_refund_accept2(I('post.val'));
            break;

            //卖家同意退货，买家长时间未响应默认取消退款
            case 'seller_accept':
                $res = $this->_refund_cancel(I('post.val'));
            break;

            //买家寄回退货，卖家长时间未确认，默认无异议自动退款
            case 'buyer_send_express':
                $res = $this->_refund_accept3(I('post.val'));
            break;

            //超时未评价的订单
            case 'buyer_rate':
                $res = $this->_buyer_rate(I('post.val'));
            break;  

            //宝贝主图检测
            case 'goods_images':
                $res = $this->_goods_images(I('post.val'));
            break;

            //宝贝主图检测
            case 'goods_attr_list_images':
                $res = $this->_goods_attr_list_images(I('post.val'));
                break;

            //宝贝属性图片检测
            case 'goods_attr_value_images':
                $res = $this->_goods_attr_value_images(I('post.val'));
                break;

            case 'service_buyer_confirm':   //售后买家长时间未确认收货
                $res = $this->_service_buyer_confirm(I('post.val'));
                break;
            case 'service_seller_confirm':  //售后卖家长时间未确认收货
                $res = $this->_service_seller_confirm(I('post.val'));
                break;
            case 'service_seller_accept':   //售后卖家长时间未同意售后
                $res = $this->_service_seller_accept(I('post.val'));
                break;
            case 'service_buyer_express':   //售后买家长时间未发货
                $res = $this->_service_buyer_express(I('post.val'));
                break;
            case 'service_seller_refuse':   //售后卖家拒绝后，买家长时间未操作则取消售后
                $res = $this->_service_seller_refuse(I('post.val'));
                break;
            case 'goods_images_check':
                $res = $this->_goods_images_check(I('post.val'));
            break;
            case 'activity_over' :  //活动完成
                $res = $this->_activity_over(I('post.val'));
                break;
            case 'activity_start' : //活动开始
                $res = $this->_activity_start(I('post.val'));
                break;
            case 'coupon_batch_over' : //优惠券批次过期
                $res = $this->_coupon_batch_over(I('post.val'));
                break;

            case 'goods_to_index':    //更新商品索引,by lazycat 2016-11-09
                $res = $this->_goods_to_index(I('post.val'));
                break;
            case 'shop_to_index':    //更新店铺索引,by lazycat 2016-11-09
                $res = $this->_shop_to_index(I('post.val'));
                break;
            case 'shop_to_adtj':   //广告统计
                $res = $this->doApi('/Tj/adShowClickData');
                break;
        }

        echo json_encode($res);
    }


    /**
    * 自动刷新宝贝上架时间
    * 将超过7天的宝贝重新更新上架时间
    * 前端列表页缓存时间为10分钟，所以每隔10分钟执行一次
    */
    public function goods_uptime(){

        $do     =   M();
        $sql    =   'update '.C('DB_PREFIX').'goods set uptime = DATE_ADD(uptime,INTERVAL 7 DAY) where uptime < "'.(date('Y-m-d H:i:s',time()-86400*7)).'" limit 2000'; //一次最多只能更新2000条

        $max    =   2000;
        $num    =   0;
        while($max == 2000){
            $res    =   $do->execute($sql);
            $num    +=$res;
            if($res < $max) break;
            usleep(rand(10,50));
        }

        $log    =   [
            'atime'         =>date('Y-m-d H:i:s'),
            'ip'            =>get_client_ip(),
            'num'           =>$num
        ];

        //写入日志
        log_add('goods_uptime',$log);

        echo json_encode(['code' => ($num > 0 ? 1 : 0)]);

    }

    /**
    * 商城基础数据统计
    * linux crontab定时任务，在每天凌晨4点执行
    */
    public function totals(){
        $t = new TotalsController();

        /*
        for($i=30;$i>0;$i--){
            $t->day = date('Y-m-d',time()-86400*$i);
            $t->total_date();
        }
        */

        $t->total_date();
    }
    
    //执行任务
    public function work() {
        Queue::work();
    }
    
    /**
     * 定时执行
     */
    public function cronIntoQueue() {
        $type       =   I('get.type');
        $typeArr    =   ['couponBatch', 'activity', 'spikes'];
        if (in_array($type, $typeArr)) {
            $flag   =   Queue::cronIntoQueue($type);
            if ($flag == true) {
                Queue::put();   //zhi xing dui lie
            }
        }
    }


    /**
     * 当商品下架或删除时直接从索引中删除，每隔一分钟执行一次
     */
    public function xs_remove_goods_index(){
        $res = $this->_xs_remove_goods_index();
    }

    /**
     * 当店铺暂停营或关闭时直接从索引中删除，每隔一分钟执行一次
     */
    public function xs_remove_shop_index(){
        $res = $this->_xs_remove_shop_index();
    }

    /**
     * 转盘抽奖，清除昨天没有抽奖的机会
     */
    public function clean_luckdraw(){
        $res = $this->doApi('/Luckdraw/timed_task');
    }

    public function test(){
        $list = $this->shop_to_adtj();
        dump($list);
    }

    /**
     * 官方活动 - 秒杀上线倒计时、秒杀结束后恢复商品状态
     * Author: Lazycat
     * 2016-12-20
     */
    public function officialactivity()
    {
        //$list = M('officialactivity_schedule')->where(['status' => 1,'activity_id' => 250,'day' => date('Y-m-d',time()-86400),'time' => date('H:i')])->field('id')->select();
        $list = M('officialactivity_schedule')->where(['status' => 1,'activity_id' => 250,'_string' => 'unix_timestamp(concat(day," ",time)) < '.(time()-3600*24)])->field('id')->select();
        foreach($list as $val){
            $this->officialactivity_recovery($val['id']);
        }

        //离活动开始的前12小时开始倒计时（考虑定时任务执行可能出现时间误差）
        $list = M('officialactivity_schedule')->where(['status' => 0,'activity_id' => 250,'_string' => 'unix_timestamp(concat(day," ",time)) < '.(time()+3600*12)])->field('id')->select();
        //dump(M('officialactivity_schedule')->getLastSql());exit();
        foreach($list as $val){
            $this->officialactivity_dectime($val['id']);
        }

        //file_put_contents('of.txt',date('Y-m-d H:i:s'),FILE_APPEND);

    }
    /**
     * 部分订单异常处理
     * 已付款未发货订单，同意退款
     */
    public function fix_orders_refund(){
        //$r_no = '2016111516272703160799';
        //$res = $this->_refund_accept($r_no);

    }

    /**
     * 补上漏掉的统计
     */
    public function totals_fix(){
        
        $day = ['2016-10-10','2016-10-11','2016-10-12','2016-10-13','2016-10-14','2016-10-15','2016-10-16','2016-10-17','2016-10-18','2016-11-18','2016-11-19','2016-11-20','2016-11-21'];
        foreach($day as $val){
            $t = new TotalsController();
            $t->day = $val;
            $t->total_date();
        }
        
    }

    /**
     * 补上漏掉的店铺销售统计
     */
    public function shop_totals_fix(){

        $count = M('shop')->count();
        $page = ceil($count / 2);

        $p = I('get.p') ? I('get.p') : 1;


        $list = M('shop')->page($p)->limit(2)->select();

        if(empty($list)) {
            echo 'end';
            exit();
        }
        for($i=100;$i>=0;$i--) {
            foreach ($list as $val) {
                if($val['atime'] < date('Y-m-d',time() - 86400 * $i)) {
                    $t = new TotalsController();
                    $t->day = date('Y-m-d', time() - 86400 * $i);
                    $res = $t->shop_totals($val['id']);
                    dump($res);
                }
            }
        }

        gourl('/Cron/shop_totals_fix/p/'.($p+1));

    }

    /**
     * 官方秒杀 - 设置活动倒计时
     */
    public function officialactivity_dectime($schedule_id){
        $schedule = M('officialactivity_schedule')->where(['id' => $schedule_id])->field('atime,etime,ip',true)->find();
        if($schedule['status'] != 0) return false; //该状态下不充许进行设置！

        //只能在活动开始的前24个小时内进行设置
        $time_dif = strtotime($schedule['day'].' '.$schedule['time']) - time();
        if($time_dif < 0 || $time_dif > 3600 * 24) return false; //只能在活动开始的前6个小时内进行设置！
        //dump($time_dif);

        //取活动名额
        $ren_num = M('officialactivity_floor')->where(['schedule_id' => $schedule_id])->sum('num');

        //取活动商品
        $join_ids = M('officialactivity_floor_goods')->where(['schedule_id' => $schedule_id])->getField('join_id',true);
        if(count($join_ids) < $ren_num) $this->ajaxReturn(['status' => 'warning','msg' => '活动名额为'.$ren_num.'，目前只确定了'.count($join_ids).'，请先筹备并确定好名额后再执行此操作！']);

        //取商品ID
        $join_goods = M('officialactivity_join')->where(['id' => ['in' , $join_ids]])->field('id,goods_id,price,num')->select();

        $do = M();
        $do->startTrans();

        foreach($join_goods as $val){
            if(!$this->sw[] = M('goods')->where(['id' => $val['goods_id']])->save(['officialactivity_price' => $val['price'],'officialactivity_join_id' => $val['id']])) goto error;

            //检测库存数量是否与报名的数量相同，不相等时需要进行修改
            $goods = M('goods')->where(['id' => $val['goods_id']])->field('num')->find();
            if($goods['num'] != $val['num']) {
                $dif        = $goods['num'] - $val['num'];
                $attr_list  = M('goods_attr_list')->where(['goods_id' => $val['goods_id']])->getField('id',true);
                $count      = count($attr_list);

                //平摊库存数量差额
                $t = abs($dif);
                if($t >= $count){   //差额大于库存笔数时
                    $t = intval(abs($dif) / $count);    //每笔库存要平摊的数量
                    $t_last = abs($dif) - ($t * ($count - 1)); //最后一条

                    foreach ($attr_list as $i => $v){
                        $tmp = 0;
                        if($i == $count - 1) $tmp = $t_last;
                        else $tmp = $t;
                        if($dif > 0){   //当实际库存大于报名库存时要减
                            $tmp = $tmp * -1;
                        }
                        if(!$this->sw[] = M('goods_attr_list')->where(['id' => $v])->setInc('num',$tmp)){
                            goto error;
                            break;
                        }
                    }
                }else{  //差额小于库存笔数时
                    for($i=0;$i<$t;$i++){
                        $tmp = $dif > 0 ? -1 : 1;
                        if(!$this->sw = M('goods_attr_list')->where(['id' => $attr_list[$i]])->setInc('num',$tmp)){
                            goto error;
                            break;
                        }
                    }
                }
                //更新商品库存
                if(!$this->sw[] = M('goods')->where(['id' => $val['goods_id']])->save(['num' => $val['num']])){
                    goto error;
                }
            }

        }

        if(!$this->sw[] = M('officialactivity_schedule')->where(['id' => $schedule_id])->save(['status' => 1])) goto error;

        $do->commit();
        return true;

        error:
        $do->rollback();
        return false;
    }


    /**
     * 官方秒杀 - 结束秒杀活动，并恢复商品原始状态
     */
    public function officialactivity_recovery($schedule_id){
        $schedule = M('officialactivity_schedule')->where(['id' => $schedule_id])->field('atime,etime,ip',true)->find();
        if($schedule['status'] != 1) return false; //该状态下不充许进行设置！

        //活动时长为24小时，须24小时后方可结束活动
        $time_dif = strtotime($schedule['day'].' '.$schedule['time']) - time();
        if($time_dif > 3600 * 24 * -1) return false; //活动还未结束！

        //取参与活动的商品
        $goods_ids = M('officialactivity_join')->where(['activity_id' => $schedule['activity_id'],'day' => $schedule['day'],'time' => $schedule['time']])->getField('goods_id',true);

        $do = M();
        $do->startTrans();

        if($goods_ids) {
            if(!$this->sw[] = M('goods')->where(['id' => ['in',$goods_ids]])->save(['officialactivity_join_id' => 0,'officialactivity_price' => 0])) goto error;
        }
        if(!$this->sw[] = M('officialactivity_schedule')->where(['id' => $schedule_id])->save(['status' => 2])) goto error;

        $do->commit();
        return true;

        error:
        $do->rollback();
        return false;
    }

    /**
     * 修补商品属性图片搬家
     */
    public function attr_value_images_fix(){
        $list = $this->goods_attr_value_images();
        foreach($list as $key => $val) {
            $this->_goods_attr_value_images($val);
            if($key > 5){
                gourl('/Cron/attr_value_images_fix');
                exit();
            }
        }
    }

    /**
     * 抽奖统计，每天凌晨2点统计前一天的抽奖数据
     */
    public function luckdraw_total(){
        $this->doApi('/Luckdraw/luckdraw_statistics',[],'day');
    }
	

}