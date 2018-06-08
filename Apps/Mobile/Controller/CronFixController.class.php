<?php
/**
 * -------------------------------------------------
 * 测试
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-03-02
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class CronFixController extends CommonController {
    public function _initialize() {
        parent::_initialize();

        if(empty($_SESSION['admin'])){
            echo 'nologin';
            exit();
        }
    }
    public function orders_sql(){
        set_time_limit(0);
        //待关闭订单
        $sql = 'update '.C('DB_PREFIX').'orders_shop set is_problem=0,next_time=date_add(atime,interval '.C('cfg.orders')['add'].' second) where status=1';
        echo $sql.';<br>';
        //M()->execute($sql);

        //待收货订单
        $sql = 'update '.C('DB_PREFIX').'orders_shop set is_problem=0,next_time=date_add(express_time,interval '.C('cfg.orders')['confirm_orders'].' second) where status=3';
        echo $sql.';<br>';
        //M()->execute($sql);

        //待评价订单
        $sql = 'update '.C('DB_PREFIX').'orders_shop set is_problem=0,next_time=date_add(receipt_time,interval '.C('cfg.orders')['rate_add'].' second) where status=4';
        echo $sql.';<br>';
        //M()->execute($sql);

        //待归档订单
        $sql = 'update '.C('DB_PREFIX').'orders_shop set is_problem=0,next_time=date_add(rate_time,interval '.C('cfg.orders')['orders_history'].' second) where status=5 and rate_time !="0000-00-00 00:00:00"';
        echo $sql.';<br>';
        //M()->execute($sql);


        //======================================================

        //未发货，买家申请退款
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(atime,interval '.C('cfg.orders')['refund_not_express'].' second) where status=1 and orders_status=2';
        echo $sql.';<br>';
        //M()->execute($sql);

        //已发货，买家申请退款
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(atime,interval '.C('cfg.orders')['refund_express'].' second) where status=1 and orders_status=3';
        echo $sql.';<br>';
        //M()->execute($sql);


        //卖家拒绝退款
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['refund_express'].' second) where status=2 and orders_status=3';
        echo $sql.';<br>';
        //M()->execute($sql);

        //买家修改退款
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['refund_express'].' second) where status=3 and orders_status=3';
        echo $sql.';<br>';
        //M()->execute($sql);

        //卖家同意退款退货
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['refund_express'].' second) where status=4 and orders_status=3';
        echo $sql.';<br>';
        //M()->execute($sql);

        //买家寄回退货
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['confirm_orders'].' second) where status=5 and orders_status=3';
        echo $sql.';<br>';
        //M()->execute($sql);

        //===============================================

        //已发货，买家申请售后
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(atime,interval '.C('cfg.orders')['refund_express'].' second) where status=1 and orders_status=4';
        echo $sql.';<br>';
        //M()->execute($sql);


        //卖家拒绝售后
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['refund_express'].' second) where status=2 and orders_status=4';
        echo $sql.';<br>';
        //M()->execute($sql);

        //卖家同意售后
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['refund_express'].' second) where status=3 and orders_status=4';
        echo $sql.';<br>';
        //M()->execute($sql);

        //买家寄回商品
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['confirm_orders'].' second) where status=4 and orders_status=4';
        echo $sql.';<br>';
        //M()->execute($sql);

        //卖家收到商品
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['refund_express'].' second) where status=5 and orders_status=4';
        echo $sql.';<br>';
        //M()->execute($sql);

        //卖家寄出商品
        $sql = 'update '.C('DB_PREFIX').'refund set is_problem=0,next_time=date_add(dotime,interval '.C('cfg.orders')['confirm_orders'].' second) where status=6 and orders_status=4';
        echo $sql.';<br>';
        //M()->execute($sql);

    }

    /**
     * 检测已关闭的订单与ERP数据对比
     * Create by Lazycat
     * 2017-03-29
     */
    public function orders_close_check(){
        if(I('get.shop_id')) $map['shop_id'] = I('get.shop_id');
        $map['status'] = ['in','1,10'];
        $count = M('orders_shop')->where($map)->count();

        $pagesize = I('get.pagesize') ? I('get.pagesize') : 20;
        $page = ceil($count/$pagesize);


        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_shop')->where($map)->field('id,atime,status,s_no,receipt_time,inventory_type')->page($p)->limit($pagesize)->order('id desc')->select();
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        $n = 0;
        foreach ($list as $val){
            //dump($val);
            $res = $this->doApi2('/Orders/check_ordres_in_erp',['s_no' => $val['s_no']]);
            //dump($res);
            if($res['code'] == 1){
                dump($res['data']);
                $n++;
            }
            usleep(5000);
        }

        $url = '/CronFix/orders_close_check/p/'.($p+1).(I('get.shop_id') ? '/shop_id/'.I('get.shop_id') : '').(I('get.pagesize') ? '/pagesize/'.I('get.pagesize') : '');
        if($n == 0){
            gourl($url);
        }else{
            echo '<a href="'.$url.'">下一页</a>';
        }

    }

    /**
     * 检测已发货订单与ERP数据对比
     * Create by Lazycat
     * 2017-03-29
     */
    public function orders_express_check(){
        if(I('get.shop_id')) $map['shop_id'] = I('get.shop_id');
        $map['status'] = 3;
        $count = M('orders_shop')->where($map)->count();

        $pagesize = I('get.pagesize') ? I('get.pagesize') : 20;
        $page = ceil($count/$pagesize);


        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_shop')->where($map)->field('id,atime,status,s_no,receipt_time,inventory_type')->page($p)->limit($pagesize)->order('id desc')->select();
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        $n = 0;
        foreach ($list as $val){
            //dump($val);
            $res = $this->doApi2('/Orders/check_ordres_in_erp',['s_no' => $val['s_no']]);
            //dump($res);
            if($res['code'] == 1){
                dump($res['data']);
                $n++;
            }
            usleep(5000);
        }

        $url = '/CronFix/orders_express_check/p/'.($p+1).(I('get.shop_id') ? '/shop_id/'.I('get.shop_id') : '').(I('get.pagesize') ? '/pagesize/'.I('get.pagesize') : '');
        if($n == 0){
            gourl($url);
        }else{
            echo '<a href="'.$url.'">下一页</a>';
        }
    }

    /**
     * 支付状态不一致较对
     * Create by Lazycat
     * 2017-03-29
     */
    public function orders_paytype_check(){
        if(I('get.shop_id')) $map['shop_id'] = I('get.shop_id');
        $map['status'] = ['not in','0,1,10'];
        $map['pay_time'] = ['gt','2017-02-01'];
        $count = M('orders_shop')->where($map)->count();

        $pagesize = I('get.pagesize') ? I('get.pagesize') : 20;
        $page = ceil($count/$pagesize);


        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_shop')->where($map)->field('id,atime,status,s_no,receipt_time,inventory_type')->page($p)->limit($pagesize)->order('id desc')->select();
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        $n = 0;
        foreach ($list as $val){
            //dump($val);
            $res = $this->doApi2('/Orders/check_orders_paytype',['s_no' => $val['s_no']]);
            //dump($res);
            if($res['code'] == 1){
                dump($res['data']);
                $n++;
            }
            usleep(5000);
        }

        $url = '/CronFix/orders_paytype_check/p/'.($p+1).(I('get.shop_id') ? '/shop_id/'.I('get.shop_id') : '').(I('get.pagesize') ? '/pagesize/'.I('get.pagesize') : '');
        gourl($url);
        /*
        if($n == 0){
            gourl($url);
        }else{
            echo '<a href="'.$url.'">下一页</a>';
        }
        */
    }

    /**
     * 检查某个订单是否正常
     */

}