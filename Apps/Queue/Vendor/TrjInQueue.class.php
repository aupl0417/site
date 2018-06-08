<?php
/**
 * 唐人街相关任务入列
 * Create by Lazycat
 * 2017-03-18
 */
class TrjInQueue{
    private $db;
    private $task;
    private $limit_time;        //最近时间段
    private $goods_limit_time;  //商品更新时间段
    private $shop_limit_time;   //店铺更新时间段

    private $orders_cfg;        //订单超时配置
    private $pagesize   = 500;    //分页数量
    private $sleep_time = 1;    //睡眠时间
    private $days       = 0.0015;    //只操作最近的30天内的数据
    private $goods_time = 11;    //1分钟内变更的数据
    private $shop_time  = 11;    //1分钟内变更的数据

    private $bs;        //beanstalkd句柄
    /**
     * 构造函数，初始化
     */
    public function __construct($option=null){
        if(isset($option['days'])) $this->days = $option['days'];
        if(isset($option['goods_time'])) $this->days = $option['goods_time'];
        if(isset($option['goods_time'])) $this->days = $option['goods_time'];

        $this->limit_time       = date('Y-m-d H:i:s', time() - intval(86400 * $this->days));
        $this->goods_limit_time = date('Y-m-d H:i:s', time() - 60 * $this->goods_time);
        $this->shop_limit_time  = date('Y-m-d H:i:s', time() - 60 * $this->shop_time);

        $this->db           = new db();
        //$this->task = $this->get_task();
        $this->orders_cfg   = $this->orders_cfg();

        //连接beanstalkd
        $this->bs = new Beanstalkd();
        if(!$this->bs->connect()){
            throw new Exception('连接Beanstalkd失败！');
        }

    }



    //================ 订单超时处理 =======================

    /**
     * 获取订单超时时间参数设置
     * 2017-03-18
     */
    public function orders_cfg(){
        $list = $this->db->table('config')->where(['sid' => 100827575])->field('name,value')->select();
        $config = [];
        foreach($list as $val){
            $config[$val['name']]   = $val['value'];
        }

        return $config;
    }

    /**
     * 获取任务
     * 2017-03-18
     */
    public function get_task(){
        $list = $this->db->table('swoole_crontab')->select();
        foreach($list as $key => $val){
            if($val['args']) $val['args'] = eval(html_entity_decode($val['args']));

            $list[$key]  = $val;
        }
        return $list;
    }

    /**
     * 关闭订单：取超时未付款订单
     * 2017-03-18
     */
    public function orders_close(){
        $data = [
            'status'    => 1,
            'next_time' => ['gt',$this->limit_time],
            '_string'   => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'      => __FUNCTION__,
        ];
        $this->_orders_expire($data);
        exit();
    }


    /**
     * 待收货订单
     * 2017-03-18
     */
    public function orders_confirm(){
        $data = [
            'status'    => 3,
            'next_time' => ['gt',$this->limit_time],
            '_string'   => 'next_time < "'.date('Y-m-d H:i:s').'" AND id not in (select DISTINCT s_id from '.DB_PREFIX.'refund where orders_status=3 and status not in (20,100))',
            'tube'      => __FUNCTION__,
        ];
        $this->_orders_expire($data);
        exit();

    }

    /**
     * 当退款取消或退款完成时，订单恢复正常时的待确认收货订单处理
     * 2017-03-20
     */
    public function orders_confirm_refund_finished(){
        $data = [
            'status'    => 5,
            'next_time' => ['lt',date('Y-m-d H:i:s')],
            '_string'   => 'id in (select DISTINCT s_id from '.DB_PREFIX.'refund where (status=20 and cancel_time > "'.$this->limit_time.'") or (status=100 and accept_time > "'.$this->limit_time.'") ) and id not in (select DISTINCT s_id from '.DB_PREFIX.'refund where status not in (20,100))',
            'tube'      => __FUNCTION__,
        ];
        $this->_orders_expire($data);
        exit();
    }

    /**
     * 待评价订单
     * 2017-03-20
     */
    public function orders_rate(){
        $data = [
            'status'    => 4,
            'next_time' => ['gt',$this->limit_time],
            '_string'   => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'      => __FUNCTION__,
        ];
        $this->_orders_expire($data);
        exit();
    }


    /**
     * 待归档订单
     * 2017-03-20
     */
    public function orders_history(){
        $data = [
            'status'    => 5,
            'next_time' => ['gt',$this->limit_time],
            '_string'   => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'      => __FUNCTION__,
        ];
        $this->_orders_expire($data);
        exit();
    }

    /**
     * 超时未处理订单入列
     * 2017-03-20
     */
    private function _orders_expire($param){
        $map['status']      = $param['status'];
        $map['next_time']   = $param['next_time'];
        $map['is_problem']  = ['lt',10];
        if($param['_string']) $map['_string']     = $param['_string'];

        /*
        $count = $this->db->table('orders_shop')->where($map)->count();
        //file_put_contents('/tmp/tmp/sql.log',$this->db->getSql().PHP_EOL,FILE_APPEND);
        $page = ceil($count / $this->pagesize);

        for($i = $page;$i > 0;$i--){
            $limit = (($i - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('orders_shop')->where($map)->field('s_no')->limit($limit)->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$i.']'.$this->db->getSql(),true);
            //print_r($list);
            foreach ($list as $val){
                //$this->logs($val['s_no'].'.log',$val['s_no']);
                $this->put($param['tube'],['execute' => 'TrjWorker','args' => ['type' => $param['tube'],'val' => $val['s_no']]]);
            }
            //usleep($this->sleep_time);
            if($i>1) usleep($this->sleep_time);
        }
        */

        /**
         * 当数量非常大的情况下，不要先去count表
         */
        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('orders_shop')->where($map)->field('s_no')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put($param['tube'],['execute' => 'TrjWorker','args' => ['type' => $param['tube'],'val' => $val['s_no']]]);
            }
            usleep($this->sleep_time);

            $p++;
        }
    }


    //==================== 退款超时处理 ====================

    /**
     * 未发货退款
     * 2017-03-20
     */
    public function refund_buyer_not_express(){
        $data = [
            'status'        => 1,
            'orders_status' => 2,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 已发货退款申请
     * 2017-03-20
     */
    public function refund_buyer_add(){
        $data = [
            'status'        => 1,
            'orders_status' => 3,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 卖家拒绝退款
     * 2017-03-20
     */
    public function refund_seller_reject(){
        $data = [
            'status'        => 2,
            'orders_status' => 3,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 买家修改退款
     * 2017-03-20
     */
    public function refund_buyer_edit(){
        $data = [
            'status'        => 3,
            'orders_status' => 3,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 卖家同意退货退款
     * 2017-03-20
     */
    /*
    public function refund_seller_goods_accept(){
        $stime = microtime(true);
        $data = [
            'status'        => 4,
            'orders_status' => 3,
            '_string'       => 'next_time > "'.$this->limit_time.'" AND type=1',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);

        $etime = microtime(true);
        $this->logs('end.log',$etime - $stime);
    }
    */

    /**
     * 卖家同意退货退款
     * 2017-03-20
     */
    public function refund_seller_accept(){
        $data = [
            'status'        => 4,
            'orders_status' => 3,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 买家寄回退货
     * 2017-03-20
     */
    public function refund_buyer_send_express(){
        $data = [
            'status'        => 5,
            'orders_status' => 3,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    //====================== 售后超时处理 =========================
    /**
     * 买家申请售后
     * 2017-03-20
     */
    public function service_buyer_add(){
        $data = [
            'status'        => 1,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }


    /**
     * 卖家拒绝售后申请
     * 2017-03-20
     */
    public function service_seller_reject(){
        $data = [
            'status'        => 2,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 卖家同意售后申请
     * 2017-03-20
     */
    public function service_seller_accept(){
        $data = [
            'status'        => 3,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 买家寄回商品
     * 2017-03-20
     */
    public function service_buyer_send_express(){
        $data = [
            'status'        => 4,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }


    /**
     * 卖家收到商品
     * 2017-03-20
     */
    /*
    public function service_seller_goods_received(){
        $stime = microtime(true);
        $data = [
            'status'        => 5,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['gt',$this->limit_time],
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);

        $etime = microtime(true);
        $this->logs('end.log',$etime - $stime);

        //exit();
    }
    */

    /**
     * 卖家完成售后并寄回商品
     * 2017-03-20
     */
    public function service_seller_finished(){
        $data = [
            'status'        => 6,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_refund_expire($data);
        exit();
    }

    /**
     * 退款超时处理
     * 2017-03-20
     */
    private function _refund_expire($param){
        $map['status']          = $param['status'];
        $map['orders_status']   = $param['orders_status'];
        $map['next_time']       = $param['next_time'];
        $map['is_problem']      = ['lt',10];
        if($param['_string']) $map['_string']         = $param['_string'];

        /*
        $count = $this->db->table('refund')->where($map)->count();
        //file_put_contents('/tmp/tmp/sql.log',$this->db->getSql().PHP_EOL,FILE_APPEND);

        $page = ceil($count / $this->pagesize);

        for($i = $page;$i > 0;$i--){
            $limit = (($i - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('refund')->where($map)->field('r_no')->limit($limit)->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$i.']'.$this->db->getSql(),true);
            //print_r($list);
            foreach ($list as $val){
                //$this->logs($val['r_no'].'.log',$val['r_no']);
                $this->put($param['tube'],['execute' => 'TrjWorker','args' => ['type' => $param['tube'],'val' => $val['r_no']]]);
            }
            //usleep($this->sleep_time);
            if($i>1) usleep($this->sleep_time);
        }
        */


        /**
         * 当数量非常大的情况下，不要先去count表
         */
        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('refund')->where($map)->field('r_no')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put($param['tube'],['execute' => 'TrjWorker','args' => ['type' => $param['tube'],'val' => $val['r_no']]]);
            }
            usleep($this->sleep_time);

            $p++;
        }

    }


    //================== 商品新增、修改 ====================
    /**
     * 商品更新
     * 2017-03-20
     */
    public function goods_update(){
        //$map['etime']   = ['lt',date('Y-m-d H:i:s')];
        $map['_string'] = 'etime > "'.$this->goods_limit_time.'"';

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('goods')->where($map)->field('id')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put('goods_update',['execute' => 'TrjWorker','args' => ['type' => 'goods_update','val' => $val['id']]]);
            }
            usleep($this->sleep_time);
            $p++;
        }
        exit();
    }

    /**
     * 商品主图搬家
     * 2017-03-20
     */
    public function goods_images(){
        $map['status']      = 1;
        //$map['_string']     = 'etime > "'.$this->goods_limit_time.'"';
        $map['_string']		='images!="" and images not like "%clouddn.com%" and images not like "%tangmall.net%" and images not like "%qiniudns.com%" and images not like "%trj.cc%"';

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('goods')->where($map)->field('id')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put('goods_images',['execute' => 'TrjWorker','args' => ['type' => 'goods_images','val' => $val['id']]]);
            }
            usleep($this->sleep_time);
            $p++;
        }
        exit();
    }

    /**
     * 商品库存主图搬家
     * 2017-03-20
     */
    public function goods_attr_list_images(){
        //$map['_string']     = 'etime > "'.$this->goods_limit_time.'"';
        $map['_string']		='images!="" and images not like "%clouddn.com%" and images not like "%tangmall.net%" and images not like "%qiniudns.com%" and images not like "%trj.cc%"';

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('goods_attr_list')->where($map)->field('id')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put('goods_attr_list_images',['execute' => 'TrjWorker','args' => ['type' => 'goods_attr_list_images','val' => $val['id']]]);
            }
            usleep($this->sleep_time);
            $p++;
        }
        exit();
    }


    /**
     * 商品库存属性图片搬家
     * 2017-03-20
     */
    public function goods_attr_value_images(){
        $map['_string']		='attr_images!="" and attr_images not like "%clouddn.com%" and attr_images not like "%tangmall.net%" and attr_images not like "%qiniudns.com%" and attr_images not like "%trj.cc%"';

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('goods_attr_value')->where($map)->field('id')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put('goods_attr_value_images',['execute' => 'TrjWorker','args' => ['type' => 'goods_attr_value_images','val' => $val['id']]]);
            }
            usleep($this->sleep_time);
            $p++;
        }
        exit();
    }

    //================= 店铺索引更新 ======================

    /**
     * 店铺更新
     * 2017-03-20
     */
    public function shop_update(){
        $map['_string'] = 'etime > "'.$this->shop_limit_time.'"';

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('shop')->where($map)->field('id')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put('shop_update',['execute' => 'TrjWorker','args' => ['type' => 'shop_update','val' => $val['id']]]);
            }
            usleep($this->sleep_time);
            $p++;
        }
        exit();
    }


    /**
     * 店铺数据统计，要统计的店铺入列
     * 2017-03-20
     */
    public function shop_total(){
        $this->_shop_list('shop_total');
        exit();
    }

    /**
     * 将所有店铺入列
     * 2017-03-20
     */
    public function _shop_list($tube='shop_list'){
        $map['status'] = 1;

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('shop')->where($map)->field('id')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put($tube,['execute' => 'TrjWorker','args' => ['type' => $tube,'val' => $val['id']]]);
            }
            usleep($this->sleep_time);
            $p++;
        }
    }

    //========================== 话费、流量订单 ========================

    /**
     * 话费、流量充值处理（当付款成功，但提交到充值平台未被正确接收处理的，队列将重新发起请求）
     * Create by lazycat
     * 2017-05-11
     */
    public function mobile_orders_repost(){
        $data = [
            'status'        => 2,
            'next_time'     => ['gt',date('Y-m-d H:i:s')],
            'transtat'      => ['not in','1,3,4,10,18'],
            //'return_status' => ['not in','1,10,28,29'],
            'tube'          => __FUNCTION__,
        ];
        $this->_mobile_orders_expire($data);
        exit();
    }

    /**
     * 话费、流量充值（当提交至充值平台超过1小时用无果后，表明充值失败，直接将款退给用户）
     * Create by lazycat
     * 2017-05-11
     */
    public function mobile_orders_refund(){
        $data = [
            'status'        => 2,
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
            'transtat'      => ['not in','1,3,4,10,18'],
            'return_status' => ['not in','1,10,28,29'],
            'tube'          => __FUNCTION__,
        ];
        $this->_mobile_orders_expire($data);
        exit();
    }


    /**
     * 超时未付款待关闭的订单
     * Create by lazycat
     * 2017-05-13
     */
    public function mobile_orders_close(){
        $data = [
            'status'        => 1,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_mobile_orders_expire($data);
        exit();
    }

    /**
     * 超时待确认收货的订单
     * Create by lazycat
     * 2017-05-13
     */
    public function mobile_orders_confirm(){
        $data = [
            'status'        => 3,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_mobile_orders_expire($data);
        exit();
    }

    /**
     * 超时未处理订单入列
     * 2017-03-20
     */
    private function _mobile_orders_expire($param){
        $map['status']      = $param['status'];
        $map['next_time']   = $param['next_time'];
        $map['is_problem']  = ['lt',10];
        if(isset($param['_string']) && $param['_string'])               $map['_string']         = $param['_string'];
        if(isset($param['transtat']) && $param['transtat'])             $map['transtat']        = $param['transtat'];
        if(isset($param['return_status']) && $param['return_status'])   $map['return_status']   = $param['return_status'];

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('mobile_orders')->where($map)->field('s_no')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put($param['tube'],['execute' => 'TrjWorker','args' => ['type' => $param['tube'],'val' => $val['s_no']]]);
            }
            usleep($this->sleep_time);

            $p++;
        }
    }

    //========================== 话费退款 ==============================
    /**
     * 已发货退款申请
     * 2017-03-20
     */
    public function mobile_orders_refund_add(){
        $data = [
            'status'        => 1,
            'orders_status' => 2,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_mobile_orders_refund_expire($data);
        exit();
    }

    /**
     * 卖家拒绝退款
     * 2017-03-20
     */
    public function mobile_orders_refund_reject(){
        $data = [
            'status'        => 2,
            'orders_status' => 2,
            'next_time'     => ['gt',$this->limit_time],
            '_string'       => 'next_time < "'.date('Y-m-d H:i:s').'"',
            'tube'          => __FUNCTION__,
        ];
        $this->_mobile_orders_refund_expire($data);
        exit();
    }

    /**
     * 退款超时处理
     * 2017-03-20
     */
    private function _mobile_orders_refund_expire($param){
        $map['status']          = $param['status'];
        $map['orders_status']   = $param['orders_status'];
        $map['next_time']       = $param['next_time'];
        $map['is_problem']      = ['lt',10];
        if($param['_string']) $map['_string']         = $param['_string'];

        $loop   = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
            $list = $this->db->table('mobile_orders_refund')->where($map)->field('r_no')->limit($limit)->order('id asc')->select();
            $this->logs('cron_sql_'.date('Y-m-d').'.log','['.$p.']'.$this->db->getSql(),true);
            if(empty($list)){
                $loop = false;
                break;
            }

            foreach ($list as $val){
                $this->put($param['tube'],['execute' => 'TrjWorker','args' => ['type' => $param['tube'],'val' => $val['r_no']]]);
            }
            usleep($this->sleep_time);

            $p++;
        }

    }


    //================= 队列补漏 ==========================
    public function in_queue_fix(){
        $this->limit_time = date('Y-m-d H:i:s', time() - 86400 * 60);    //提取2月内的数据
        $this->orders_confirm();
        $this->orders_close();
        $this->orders_rate();
        //$this->orders_history();

        $this->refund_buyer_not_express();
        $this->refund_buyer_add();
        $this->refund_buyer_edit();
        $this->refund_buyer_send_express();
        $this->refund_seller_accept();
        $this->refund_seller_reject();

        $this->service_buyer_add();
        $this->service_buyer_send_express();
        $this->service_seller_accept();
        $this->service_seller_reject();
        $this->service_seller_finished();
        exit();
    }

    public function in_queue_days(){
        $this->orders_confirm();
        $this->orders_close();
        $this->orders_rate();
        //$this->orders_history();

        $this->refund_buyer_not_express();
        $this->refund_buyer_add();
        $this->refund_buyer_edit();
        $this->refund_buyer_send_express();
        $this->refund_seller_accept();
        $this->refund_seller_reject();

        $this->service_buyer_add();
        $this->service_buyer_send_express();
        $this->service_seller_accept();
        $this->service_seller_reject();
        $this->service_seller_finished();
        exit();
    }

    //================= Beanstalkd ======================
    /**
     * 加入队列
     * 2017-03-20
     * @param string $tube 队列名
     * @param array|string $data 入队列数据
     * @param int $param['pri'] 任务优先级（0~1024，数值越小优先级越高）
     * @param int $param['delay'] 延迟处理时间
     * @param int $param['ttr'] 充许任务执行的最长时间
     * 入列数据格式 array('execute' => '','args' => '')
     */
    private function put($tube,$data,$param=null){
        if(empty($data) || empty($tube)) return false;
        $this->bs->useTube(TUBE_PREFIX . $tube);
        $data           = is_array($data) ? serialize($data) : $data;
        $param['pri']   = isset($param['pri']) ? $param['pri'] : 0;
        $param['delay'] = isset($param['delay']) ? $param['delay'] : 0;
        $param['ttr']   = isset($param['ttr']) ? $param['ttr'] : 30;
        $this->bs->put($param['pri'],$param['delay'],$param['ttr'],$data);
    }

    /**
     * 批量入队
     * 2017-03-20
     */
    private function puts($data,$param=null){
        foreach($data as $val) {
            $this->put($val,$param);
        }
    }

    /**
     * 写日志
     * 2017-03-18
     * @param $file
     * @param $data
     */
    private function logs($file,$data,$append=false){
        $path = ROOT_PATH . '/Logs/' .$file;
        if($append) @file_put_contents($path,$data.PHP_EOL,FILE_APPEND);
        else @file_put_contents($path,$data);
    }


    /**
     * 释放资源
     */
    public function __destruct(){
        $this->db->close();
        $this->bs->disconnect();
    }
}