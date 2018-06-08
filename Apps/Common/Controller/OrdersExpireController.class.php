<?php
/**
* 订单中各流程超时未操作数据提取
* 当数据非常大的时候，可以直接在此处将数据压入队列
*/
namespace Common\Controller;
use Wap\Controller\CommonController;
class OrdersExpireController extends CommonController {
    protected $expire_time    =120;   //时间区间长度，区间取1个月，反正被处理过的数据不会被重复获取
    protected $limit          =500;    //提取记录数量,不超过1000,太多可能会影响效率
    protected $sw;  //记录事务执行结果
    /**
    * 最近$expire_tiem秒内超时未付款的订单
    * 也可利用mysql触发器来完成关闭日志写入
    */
    public function buyer_add_orders(){
        $do=M('orders_shop');
        $map['status']  =1;
        $sday           =date('Y-m-d H:i:s',(time() - C('cfg.orders')['add'] - $this->expire_time));
        $eday           =date('Y-m-d H:i:s',(time() - C('cfg.orders')['add']));
        //$map['atime']   =['lt',$eday];
        $map['atime']   =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }

    /**
    * 超时未确认收货
    */
    public function buyer_confirm_orders(){
        $do=M('orders_shop');
        $map['status']          =3;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['confirm_orders'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['confirm_orders']));
        //$map['pay_time']    =['lt',$eday];
        $map['pay_time']    =['between',[$sday,$eday]];
        $map['_string']     ='id not in (select DISTINCT s_id from '.C('DB_PREFIX').'refund where orders_status=3 and status not in (20,100))';

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            //dump($do->getLastSQL());
            $list=array_merge($list,$tmp);
            usleep(100);
        }


        //取消退款我同意退款
        /*
        $sql = 'orders_status=3 and (status=20 and cancel_time between "'.date('Y-m-d H:i:s',time() - $this->expire_time).'" and "'.date('Y-m-d H:i:s').'") or (status=100 and accept_time between "'.date('Y-m-d H:i:s',time() - $this->expire_time).'" and "'.date('Y-m-d H:i:s').'") and s_id in (select id from '.C('DB_PREFIX').'orders_shop where status=3 and pay_time between "'.$sday.'" and "'.$eday.'") and s_id not in (select DISTINCT s_id from '.C('DB_PREFIX').'refund where orders_status=3 and status not in (20,100))';

        //dump($sql);
        $tmp = M('refund')->distinct(true)->where(['_string' => $sql])->getField('s_no',true);
        */
        if($tmp) $list = array_unique(array_merge($tmp,$list));

        return $list;          
    }

    /**
    * 退款 - 已付款 卖家长时间未发货
    */
    public function seller_send_express(){
        $do=M('refund');
        $map['status']          =1;
        $map['orders_status']   =2;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_not_express'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_not_express']));
        //$map['atime']     =['lt',$eday];
        $map['atime']    =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('r_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;        
    }

    /**
    * 退款 - 已发货订单 申请退款，卖家长时间未响应
    */
    public function buyer_refund_add(){
        $do=M('refund');
        $map['status']          =1;
        $map['orders_status']   =3;
        $sday           =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express'] - $this->expire_time));
        $eday           =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express']));
        //$map['atime']   =['lt',$eday];
        $map['atime']    =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('r_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;          
    }

    /**
    * 退款被拒绝后，买家长时间没响应
    */
    public function seller_not_accept(){
        $do=M('refund');
        $map['status']          =2;
        $map['orders_status']   =3;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express']));
        //$map['dotime']      =['lt',$eday];
        $map['dotime']      =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('r_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }


    /**
    * 买家修改退款后卖家长时间未响应
    */
    public function buyer_refund_edit(){
        $do=M('refund');
        $map['status']          =3;
        $map['orders_status']   =3;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express']));
        //$map['dotime']      =['lt',$eday];
        $map['dotime']      =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('r_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }

    /**
    * 卖家同意退货，买长时间未发回退货
    */
    public function seller_accept(){
        $do=M('refund');
        $map['status']          =4;
        $map['orders_status']   =3;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['refund_express']));
        //$map['dotime']      =['lt',$eday];
        $map['dotime']      =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('r_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);            
        }
        return $list;
    }

    /**
    * 买家寄回退货，卖家长时间未确认
    */
    public function buyer_send_express(){
        $do=M('refund');
        $map['status']          =5;
        $map['orders_status']   =3;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['confirm_orders'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['confirm_orders']));
        //$map['dotime']      =['lt',$eday];
        $map['dotime']      =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('r_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }    


    /**
    * 超时未评价订单
    */
    public function buyer_rate(){
        $do=M('orders_shop');
        $map['status']      =4;
        $sday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['rate_add'] - $this->expire_time));
        $eday               =date('Y-m-d H:i:s',(time() - C('cfg.orders')['rate_add']));
        //$map['receipt_time']    =['lt',$eday];
        $map['receipt_time']    =['between',[$sday,$eday]];

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            //dump($do->getLastSQL());
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;         
    }

    /**
     * 超时同意售后，卖家
     * 7days
     */
    public function service_seller_accept() {
        $do     =   M('refund');
        $map    =   [
            'orders_status' =>  ['in', '4,5'],
            'status'        =>  1,
        ];
        $time               =   !empty(C('cfg.refund')['service_seller_accept_time']) ? C('cfg.refund')['service_seller_accept_time'] : 604800;
        $sday               =   date('Y-m-d H:i:s',(time() - $time - $this->expire_time));
        $eday               =   date('Y-m-d H:i:s',(time() - $time));
        //$map['dotime']      =   ['between',[$sday,$eday]];
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id,r_no,type,seller_id';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }
    
    /**
     * 售后超时确认收货，卖家
     * 15days
     */
    public function service_seller_confirm() {
        $do     =   M('refund');
        $map    =   [
            'orders_status' =>  ['in', '4,5'],
            'status'        =>  4,
        ];
        $time               =   !empty(C('cfg.refund')['service_seller_confirm_time']) ? C('cfg.refund')['service_seller_confirm_time'] : 1296000;
        $sday               =   date('Y-m-d H:i:s',(time() - $time - $this->expire_time));
        $eday               =   date('Y-m-d H:i:s',(time() - $time));
        $map['dotime']      =   ['between',[$sday,$eday]];
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id,r_no,type,seller_id';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }
    
    /**
     * 售后超时确认收货，买家
     * 15days
     */
    public function service_buyer_confirm() {
        $do     =   M('refund');
        $map    =   [
            'orders_status' =>  ['in', '4,5'],
            'status'        =>  6,
        ];
        $time               =   !empty(C('cfg.refund')['service_buyer_confirm_time']) ? C('cfg.refund')['service_buyer_confirm_time'] : 1296000;
        $sday               =   date('Y-m-d H:i:s',(time() - $time - $this->expire_time));
        $eday               =   date('Y-m-d H:i:s',(time() - $time));
        $map['dotime']      =   ['between',[$sday,$eday]];
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id,r_no,type,uid,s_no,orders_goods_id,num';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }
    
    /**
     * 卖家拒绝售后，买家7天内未操作则关闭售后
     */
    public function service_seller_refuse() {
        $do     =   M('refund');
        $map    =   [
            'orders_status' =>  ['in', '4,5'],
            'status'        =>  2,
        ];
        $time               =   !empty(C('cfg.refund')['service_buyer_express_time']) ? C('cfg.refund')['service_buyer_express_time'] : 604800;
        $sday               =   date('Y-m-d H:i:s',(time() - $time - $this->expire_time));
        $eday               =   date('Y-m-d H:i:s',(time() - $time));
        $map['dotime']      =   ['between',[$sday,$eday]];
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id,r_no,type,uid';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }
    
    /**
     * 卖家同意后，买家7天内未发货，则关闭售后
     * 7days
     */
    public function service_buyer_express() {
        $do     =   M('refund');
        $map    =   [
            'orders_status' =>  ['in', '4,5'],
            'status'        =>  3,
        ];
        $time               =   !empty(C('cfg.refund')['service_buyer_express_time']) ? C('cfg.refund')['service_buyer_express_time'] : 604800;
        $sday               =   date('Y-m-d H:i:s',(time() - $time - $this->expire_time));
        $eday               =   date('Y-m-d H:i:s',(time() - $time));
        $map['dotime']      =   ['between',[$sday,$eday]];
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id,r_no,type,uid';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }
    
    /**
    * 主图检测
    */
    public function goods_images_check(){
        $do = M('goods');

        $map['status']  =1;
        $sday               =date('Y-m-d H:i:s',(time() - $this->expire_time));
        $eday               =date('Y-m-d H:i:s');
        $map['etime']       =['between',[$sday,$eday]];

        //$map['_string']		='images not like "%clouddn.com%" and images not like "%tangmall.net%" and images not like "%qiniudns.com%"';

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('id',true);
            //dump($do->getLastSQL());
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        
        return $list;  
    }

    /**
    * 商品主图搬家
    */
    public function goods_images(){
        $do = M('goods');

        $map['status']  =1;
        $sday               =date('Y-m-d H:i:s',(time() - $this->expire_time));
        $eday               =date('Y-m-d H:i:s');
        //$map['etime']       =['between',[$sday,$eday]];

        $map['_string']		='images not like "%clouddn.com%" and images not like "%tangmall.net%" and images not like "%qiniudns.com%" and images not like "%trj.cc%"';

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('id',true);
            //dump($do->getLastSQL());
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        
        return $list;     	
    }

    /**
    * 商品库存主图搬家
    */
    public function goods_attr_list_images(){
        $do = M('goods_attr_list');

        $map['status']  =1;
        $sday               =date('Y-m-d H:i:s',(time() - $this->expire_time));
        $eday               =date('Y-m-d H:i:s');
        //$map['etime']       =['between',[$sday,$eday]];

        $map['_string']		='images!="" and images not like "%clouddn.com%" and images not like "%tangmall.net%" and images not like "%qiniudns.com%" and images not like "%trj.cc%"';

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('id',true);
            //dump($do->getLastSQL());
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        
        return $list;     	
    }


    /**
     * 商品库存属性图片搬家
     */
    public function goods_attr_value_images(){
        $do = M('goods_attr_value');

        $map['status']  =1;
        $sday               =date('Y-m-d H:i:s',(time() - $this->expire_time));
        $eday               =date('Y-m-d H:i:s');
        //$map['etime']       =['between',[$sday,$eday]];

        $map['_string']		='attr_images!="" and attr_images not like "%clouddn.com%" and attr_images not like "%tangmall.net%" and attr_images not like "%qiniudns.com%" and attr_images not like "%trj.cc%"';

        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();

        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('id',true);
            //dump($do->getLastSQL());
            $list=array_merge($list,$tmp);
            usleep(100);
        }

        return $list;
    }


    /**
     * 商品搜索索引 - 获取最近新增或更新商品的记录
     */
    public function goods_to_index(){
        $map['_string'] = 'status=1 and etime>"'.date('Y-m-d H:i:s',time() - $this->expire_time).'" and num>0 and shop_id in (select id from '.C('DB_PREFIX').'shop where status=1 and is_test=0)';

        $do = M('goods');
        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('id',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }

        return $list;
    }


    /**
     * 店铺搜索索引 - 获取最近新增或更新的店铺记录
     */
    public function shop_to_index(){
        $map['_string'] = 'status=1 and etime>"'.date('Y-m-d H:i:s',time() - $this->expire_time).'" and is_test=0 and goods_num>0';

        $do = M('shop');
        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('id',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }

        return $list;
    }

    /**
     * 广告统计 - 获取需要统计的店铺
     */
    public function shop_to_adtj(){
        $map['status']  = 1;

        $do=D('Common/ShopView');
        $count=$do->where($map)->count();
        $page=ceil($count/$this->limit);

        $list=array();
        for($i=1;$i<=$page;$i++){
            $tmp=$do->where($map)->page($i,$this->limit)->order('id desc')->getField('openid',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }

        return $list;
    }

    /**
     * 促销活动完成
     */
    public function activity_over() {
        $do  = M('activity');
        $map = [
            'status'    => 1,
            'end_time'  => ['elt', date('Y-m-d H:i:s', NOW_TIME)],
        ];

        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id';
        for ($i=1;$i<=$page;$i++) {
            $tmp    = $do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('id',true);
            $list   = array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }

    /**
     * 优惠券批次结束
     */
    public function coupon_batch_over() {
        $do  = M('coupon_batch');
        $map = [
            'status' => 1,
            'eday' => ['elt', date('Y-m-d H:i:s', NOW_TIME)],
        ];

        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('id',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }

    /**
     * 促销活动开始
     */
    public function activity_start() {
        $map  = [
            'start_time'    => ['elt', date('Y-m-d H:i:s')],
            'status'        => 0,
        ];
        $do = M('activity');
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt/$this->limit);
        $list   =   [];
        $fields =   'id';
        for ($i=1;$i<=$page;$i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('id',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }

    /**
     * 找出即将关闭还未付款的订单
     * @return array
     */
    public function orders_nopay_check() {
        $beforeTime =   24*3600;    //提前一天
        $checkTime  =   NOW_TIME - (C('cfg.orders')['add'] - $beforeTime);
        $map        =   [
            'status'=>  1,
            'atime' =>  ['elt', date('Y-m-d H:i:s', $checkTime)],
        ];
        $do     =   M('orders_shop');
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt / $this->limit);
        $list   =   [];
        $fields =   's_no';
        for ($i = 1; $i <= $page; $i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }

    /**
     * 找出即将自动收货还未收货的订单
     * @return array
     */
    public function orders_accept_check () {
        $beforeTime =   24*3600;    //提前一天
        $checkTime  =   NOW_TIME - (C('cfg.orders')['confirm_orders'] - $beforeTime);
        $map        =   [
            'status'    =>  3,
            'pay_time'  =>  ['elt', date('Y-m-d H:i:s', $checkTime)],
        ];
        $do     =   M('orders_shop');
        $cnt    =   $do->where($map)->count();
        $page   =   ceil($cnt / $this->limit);
        $list   =   [];
        $fields =   's_no';
        for ($i = 1; $i <= $page; $i++) {
            $tmp=$do->where($map)->field($fields)->page($i,$this->limit)->order('id desc')->getField('s_no',true);
            $list=array_merge($list,$tmp);
            usleep(100);
        }
        return $list;
    }
}