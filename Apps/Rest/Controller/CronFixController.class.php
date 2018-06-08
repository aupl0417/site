<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 用于修复各种任务错误
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
class CronFixController extends OrdersExpireActionController {
    protected $sw   = array();  //保存事务执行结果
    public function _initialize() {
        parent::_initialize();

        //由于请求接口需要认证身份，所以此项是必须的
        if(IS_POST){
            $_POST = array_merge($_POST,C('cfg.api'));
        }else $_POST=C('cfg.api');
    }


	public function luckdraw_total_fix(){
	    $sday = '2016-11-11';
        //dump(date('Y-m-d H:i:s',strtotime('+1 day',strtotime($day))));
        $eday = date('Y-m-d');
        while($sday < $eday) {
            $sday = date('Y-m-d', strtotime('+1 day', strtotime($sday)));

            $this->doApi('/Luckdraw/luckdraw_statistics',['day' => $sday],'day');
        }
    }

    public function luckdraw_total_fix2(){
        $day = I('get.day');
        if(empty($day)) $day = '2017-01-20';

        if($day >= date('Y-m-d')) {
            echo 'end';
            exit();
        }

        //C('DEBUG_API',true);
        $res=$this->doApi('/Luckdraw/luckdraw_statistics',['day' => $day],'day');
        //dump($res);

        $day = date('Y-m-d', strtotime('+1 day', strtotime($day)));
        sleep(1);

        gourl('/CronFix/luckdraw_total_fix2/day/'.$day);
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
        gourl('/CronFix/goods_pr/p/'.($p+1));
    }


    public function enhong(){
        $do = M('goods');

        $list = $do->where(['shop_id' => ['in',[243]],'is_best' => 0,'status' => 1])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(4,8));
        }

        $list = $do->where(['shop_id' => ['in',[243]],'is_best' => 1,'status' => 1])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(0,2));
        }
    }

    //三只松鼠
    public function enhong2(){
        $do = M('goods');

        $list = $do->where(['shop_id' => ['in',[243]],'is_best' => 0,'status' => 1,'goods_name'=>['like','%三只%']])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(4,8));
        }

        $list = $do->where(['shop_id' => ['in',[243]],'is_best' => 1,'status' => 1,'goods_name'=>['like','%三只%']])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(0,2));
        }
    }

    //女包
    public function enhong3(){
        $do = M('goods');

        $sort = M('shop_goods_category')->where(['sid' => 100850116,'shop_id' => 243])->getField('id',true);

        foreach($sort as $sid) {
            $list = $do->where(['shop_id' => ['in', [243]], 'is_best' => 0, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra', rand(5, 8));
            }

            $list = $do->where(['shop_id' => ['in', [243]], 'is_best' => 1, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra', rand(0, 2));
            }
        }
    }

    //男包
    public function enhong4(){
        $do = M('goods');

        $sort = M('shop_goods_category')->where(['sid' => 100850099,'shop_id' => 243])->getField('id',true);

        foreach($sort as $sid) {
            $list = $do->where(['shop_id' => ['in', [243]], 'is_best' => 0, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra', rand(5, 8));
            }

            $list = $do->where(['shop_id' => ['in', [243]], 'is_best' => 1, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra',0);
            }
        }
    }


    //皮带
    public function enhong5(){
        $do = M('goods');

        $sort = M('shop_goods_category')->where(['sid' => 100850140,'shop_id' => 243])->getField('id',true);

        foreach($sort as $sid) {
            $list = $do->where(['shop_id' => ['in', [243]], 'is_best' => 0, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra', rand(5, 8));
            }

            $list = $do->where(['shop_id' => ['in', [243]], 'is_best' => 1, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra',0);
            }
        }
    }    

    public function lazycat(){
        $do = M('goods');

        $list = $do->where(['shop_id' => ['in',[1581]],'is_best' => 0,'status' => 1])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(8,14));
        }

        $list = $do->where(['shop_id' => ['in',[1581]],'is_best' => 1,'status' => 1])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(0,4));
        }


        $list = $do->where(['shop_id' => ['in',[1581]],'is_best' => 0,'status' => 1,'_string' => 'find_in_set (100854337,shop_category_id)'])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(5,10));
        }

        $list = $do->where(['shop_id' => ['in',[1581]],'is_best' => 1,'status' => 1,'_string' => 'find_in_set (100854337,shop_category_id)'])->getField('id',true);
        foreach($list as $val){
            $do->where(['id' => $val])->setField('pr_extra',rand(0,1));
        }
    }

    //干货
    public function lazycat2(){
        $do = M('goods');

        $sort = M('shop_goods_category')->where(['sid' => 100855220,'shop_id' => 1581])->getField('id',true);

        foreach($sort as $sid) {

            $list = $do->where(['shop_id' => ['in', [1581]], 'is_best' => 0, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra', rand(5, 12));
            }

            $list = $do->where(['shop_id' => ['in', [1581]], 'is_best' => 1, 'status' => 1, '_string' => 'find_in_set ('.$sid.',shop_category_id)'])->getField('id', true);
            foreach ($list as $val) {
                $do->where(['id' => $val])->setField('pr_extra', rand(0, 3));
            }
        }
    }


    public function goods_best(){
        $map['status'] = 1;
        $count = M('shop')->where($map)->count();
        $page = ceil($count/5);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('shop')->where($map)->page($p)->limit(5)->order('id desc')->field('id,shop_name,max_best')->select();
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        foreach($list as $key => $val){
            $val['best_num'] = M('goods')->where(['shop_id' => $val['id'],'status' => 1,'is_best' => 1])->count();
            if($val['best_num'] > $val['max_best']) {
                $res = M('goods')->where(['shop_id' => $val['id'],'status' => 1,'is_best' => 1])->limit($val['best_num']-$val['max_best'])->setField('is_best',0);
                dump($val);
                dump($res);
                sleep(1);
            }
        }

        usleep(1000);
        gourl('/CronFix/goods_best/p/'.($p+1));
    }

    public function enhong_uptime(){
        $this->shop_avg_uptime(243);
    }

    public function lazycat_uptime(){
        $this->shop_avg_uptime(1581);
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
     * 处理刷单订单
     */
    public function shuadan(){
        $orders_ids = M('orders_shop')->where(['is_shuadan' => 1])->getField('id',true);
        dump($orders_ids);
        $goods_ids = M('orders_goods')->where(['s_id' => ['in',$orders_ids]])->getField('goods_id',true);

        $goods_ids = array_unique($goods_ids);
        dump($goods_ids);

        M('goods')->where(['id' => ['in',$goods_ids]])->save(['is_display' => 0,'pr_extra' => -20]);
    }


    /**
     * 店铺评分计算
     */
    public function shop_rate_item($shop_id){
        $shop_rate = M()->query('select count(*) as num,sum(fraction_speed) as fraction_speed,sum(fraction_service) as fraction_service,sum(fraction_desc) as fraction_desc,sum(fraction) as fraction from '.C('DB_PREFIX').'orders_shop_comment where shop_id='.$shop_id);

        //系统默认赠送100个5分好评，避免评价少时计算出来的分数太差
        $tmp = [];
        $tmp['fraction_speed']      = ($shop_rate[0]['fraction_speed'] + (100 * 5)) / (100 + $shop_rate[0]['num']);
        $tmp['fraction_service']    = ($shop_rate[0]['fraction_service'] + (100 * 5)) / (100 + $shop_rate[0]['num']);
        $tmp['fraction_desc']       = ($shop_rate[0]['fraction_desc'] + (100 * 5)) / (100 + $shop_rate[0]['num']);
        $tmp['fraction']            = ($shop_rate[0]['fraction'] + (100 * 5)) / (100 + $shop_rate[0]['num']);

        M('shop')->where(['id' => $shop_id])->save($tmp);
    }

    /**
     * 店铺评分更新
     */
    public function shop_rate(){
        $map['status'] = 1;
        $count = M('shop')->where($map)->count();
        $page = ceil($count/10);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('shop')->where($map)->page($p)->limit(10)->order('id desc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        foreach($list as $val){
            $this->shop_rate_item($val);
        }

        usleep(1000);
        gourl('/CronFix/shop_rate/p/'.($p+1));
    }


    /**
     * 问题订单检测
     * Create by Lazycat
     * 2017-03-15
     */
    public function orders_check(){

        if(I('get.shop_id')) $map['shop_id'] = I('get.shop_id');

        $count = M('orders_shop')->where($map)->count();

        $pagesize = I('get.pagesize') ? I('get.pagesize') : 20;
        $page = ceil($count/$pagesize);


        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_shop')->where($map)->field('id,atime,status,s_no,receipt_time,inventory_type')->page($p)->limit($pagesize)->order('id asc')->select();
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        echo '<div><a href="'.'/CronFix/orders_check/p/'.($p+1).(I('get.shop_id') ? '/shop_id/'.I('get.shop_id') : '').(I('get.pagesize') ? '/pagesize/'.I('get.pagesize') : '').'">下一页</a></div>';


        //C('DEBUG_API',ture);
        foreach($list as $key => $val){
            echo '<br>----------------------------------<br>';
            echo $val['s_no'].'<br>';
            $res = $this->doApi('/Erp/check_orders_status',['s_no' => $val['s_no']]);
            //dump($res);
            switch($val['status']){
                case 0:
                    echo '<h2>关闭状态</h2>';
                    if(empty($res)) {
                        echo '<font color="red">订单检测无返回！</font><br>';
                        dump($val);

                    }elseif($res->data->o_orderState){
                        echo '</font color="red">状态异常！</font><br>';
                        dump($val);
                        dump($res);
                        $tmp = $val;
                        $tmp['erp_status'] = $res->data->o_orderState;
                        $tmp['erp_status_name'] = $res->data->o_orderState_text;
                        $tmp['erp_receipt'] = $res->data->o_isReceived;
                        log_add('tmp_orders_check',$tmp);
                    }
                    break;
                case 1:
                        echo '<h2>拍下状态</h2>';
                        if(empty($res)) {
                            echo '<font color="red">订单检测无返回！</font><br>';
                            dump($val);

                        }elseif($res->data->o_orderState != 0){
                            echo '</font color="red">状态异常！</font><br>';
                            dump($val);
                            dump($res);
                            $tmp = $val;
                            $tmp['erp_status'] = $res->data->o_orderState;
                            $tmp['erp_status_name'] = $res->data->o_orderState_text;
                            $tmp['erp_receipt'] = $res->data->o_isReceived;
                            log_add('tmp_orders_check',$tmp);
                        }
                    break;

                case 2:
                    echo '<h2>已付款状态</h2>';
                    if(empty($res)) {
                        echo '<font color="red">订单检测无返回！</font><br>';
                        dump($val);

                    }elseif($res->data->o_orderState != 1){
                        echo '</font color="red">状态异常！</font><br>';
                        dump($val);
                        dump($res);
                        $tmp = $val;
                        $tmp['erp_status'] = $res->data->o_orderState;
                        $tmp['erp_status_name'] = $res->data->o_orderState_text;
                        $tmp['erp_receipt'] = $res->data->o_isReceived;
                        log_add('tmp_orders_check',$tmp);
                    }
                    break;
                case 3:
                    echo '<h2>已发货状态</h2>';
                    if(empty($res)) {
                        echo '<font color="red">订单检测无返回！</font><br>';
                        dump($val);

                    }elseif($res->data->o_orderState != 1){
                        echo '</font color="red">状态异常！</font><br>';
                        dump($val);
                        dump($res);
                        $tmp = $val;
                        $tmp['erp_status'] = $res->data->o_orderState;
                        $tmp['erp_status_name'] = $res->data->o_orderState_text;
                        $tmp['erp_receipt'] = $res->data->o_isReceived;
                        log_add('tmp_orders_check',$tmp);
                    }
                    break;
                case 4:
                    echo '<h2>已收货状态</h2>';
                    if(empty($res)) {
                        echo '<font color="red">订单检测无返回！</font><br>';
                        dump($val);

                    }elseif($val['inventory_type'] ==1){
                        if($res->data->o_totalScore>0 && $res->data->o_orderState != 5 || $res->data->o_isReceived != 1){
                            echo '</font color="red">库存积分，状态异常或未分账！</font><br>';
                            dump($val);
                            dump($res);
                            $tmp = $val;
                            $tmp['erp_status'] = $res->data->o_orderState;
                            $tmp['erp_status_name'] = $res->data->o_orderState_text;
                            $tmp['erp_receipt'] = $res->data->o_isReceived;
                            log_add('tmp_orders_check',$tmp);
                        }

                    }elseif($res->data->o_totalScore>0 && $res->data->o_orderState != 5 || ($res->data->o_isReceived != 1 && (strtotime($val['receipt_time'])+864000) < time())){
                        echo '</font color="red">扣款，状态异常或未分账！</font><br>';
                        dump($val);
                        dump($res);
                        $tmp = $val;
                        $tmp['erp_status'] = $res->data->o_orderState;
                        $tmp['erp_status_name'] = $res->data->o_orderState_text;
                        $tmp['erp_receipt'] = $res->data->o_isReceived;
                        log_add('tmp_orders_check',$tmp);
                    }
                    break;
                case 5:
                    echo '<h2>已评价状态</h2>';
                    if(empty($res)) {
                        echo '<font color="red">订单检测无返回！</font><br>';
                        dump($val);

                    }elseif($val['inventory_type'] ==1){
                        if($res->data->o_totalScore>0 && $res->data->o_orderState != 5 || $res->data->o_isReceived != 1){
                            echo '</font color="red">库存积分，状态异常或未分账！</font><br>';
                            dump($val);
                            dump($res);
                            $tmp = $val;
                            $tmp['erp_status'] = $res->data->o_orderState;
                            $tmp['erp_status_name'] = $res->data->o_orderState_text;
                            $tmp['erp_receipt'] = $res->data->o_isReceived;
                            log_add('tmp_orders_check',$tmp);
                        }

                    }elseif($res->data->o_totalScore>0 && $res->data->o_orderState != 5 || ($res->data->o_isReceived != 1 && (strtotime($val['receipt_time'])+864000) < time())){
                        echo '</font color="red">扣款，状态异常或未分账！</font><br>';
                        dump($val);
                        dump($res);
                        $tmp = $val;
                        $tmp['erp_status'] = $res->data->o_orderState;
                        $tmp['erp_status_name'] = $res->data->o_orderState_text;
                        $tmp['erp_receipt'] = $res->data->o_isReceived;
                        log_add('tmp_orders_check',$tmp);
                    }
                    break;

            }
        }



        usleep(1000);
        gourl('/CronFix/orders_check/p/'.($p+1).(I('get.shop_id') ? '/shop_id/'.I('get.shop_id') : '').(I('get.pagesize') ? '/pagesize/'.I('get.pagesize') : ''));
        //echo '<div><a href="'.'/CronFix/orders_check/p/'.($p+1).(I('get.shop_id') ? '/shop_id/'.I('get.shop_id') : '').(I('get.pagesize') ? '/pagesize/'.I('get.pagesize') : '').'">下一页</a></div>';
    }


    /**
     * 补上漏掉的统计
     */
    public function totals_fix(){

        $day = ['2017-05-22','2017-05-23','2017-05-24','2017-05-25','2017-05-26','2017-05-27','2017-05-28','2017-05-29','2017-05-30'];
        foreach($day as $val){
            $t = new TotalsController();
            $t->day = $val;
            $t->total_date();
        }

    }
}

