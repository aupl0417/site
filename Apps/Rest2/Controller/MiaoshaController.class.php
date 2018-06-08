<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 秒杀
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-03-31
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class MiaoshaController extends ApiController {
    protected $day;                 //活动时间
    protected $times;               //开场钟点
    protected $activity_id  = 250;  //秒杀活动ID
    protected $active_time;         //当前时间段
    protected $active;              //当前期数据
    protected $time_dif;            //倒计时间秒数

    public function _initialize() {
        parent::_initialize();

        //每天开抢时间
        $this->day      = date('Y-m-d');
        $status_name    = array('已开抢','抢购进行中','即将开抢');


        $sday   = date('Y-m-d',time() - 86400).' 08:00';
        $time_arr[] = $sday;
        for($i=0;$i<8;$i++){    //昨天
            $sday       = date('Y-m-d H:i',strtotime($sday) + 7200);
            $time_arr[] = $sday;
        }

        $sday       = date('Y-m-d').' 08:00';
        $time_arr[] = $sday;
        for($i=0;$i<8;$i++){    //今天
            $sday       = date('Y-m-d H:i',strtotime($sday) + 7200);
            $time_arr[] = $sday;
        }

        $sday   = date('Y-m-d',time() + 86400).' 08:00';
        $time_arr[] = $sday;
        for($i=0;$i<8;$i++){    //明天
            $sday       = date('Y-m-d H:i',strtotime($sday) + 7200);
            $time_arr[] = $sday;
        }

        $this_time      = date('Y-m-d H:i');
        $n = 0;
        foreach($time_arr as $key => $val){
            if($this_time < $val) {
                $n = $key-1;
                break;
            }
        }

        for($i=$n;$i<$n+8;$i++){
            $val = explode(' ',$time_arr[$i]);
            $tmp = [
                'time'          => $val[1],
                'day'           => $val[0],
                'daytime'       => $time_arr[$i],
                'status_name'   => $i == $n ? '开抢中':'即将开抢',
                'active'        => $i == $n ? 1 : 0,
            ];
            $tmp['time_dif']    = strtotime($time_arr[$i]) - time();
            if($i == $n) {
                $tmp['time_dif']    = (strtotime($time_arr[$i]) + 86400) - time();
                $this->active       = $tmp;
            }
            $this->times[] = $tmp;
        }


    }

    /**
     * subject: 秒杀时间轴
     * api: /Miaosha/times
     * author: Lazycat
     * day: 2017-03-31
     * content: day和time,不传默认为当前期，如果传值的话两个参数都必传
     *
     * [字段名,类型,是否必传,说明]
     * param: day,date,1,日期
     * param: time,time,1,时间，格式：12:00
     */
    public function times(){
        if(!empty($this->post['day']) || !empty($this->post['time'])) $this->check('day,time',false);

        $res = $this->_times($this->post);
        $this->apiReturn($res);
    }
    public function _times($param=null){
        $res['times']       = $this->times;
        $res['active']      = $this->active;

        $daytime = $param['day'].' '.$param['time'];

        foreach($this->times as $val){
            if($val['daytime'] == $daytime){
                $res['active'] = $val;
                break;
            }
        }


        return ['code' => 1,'data' => $res];
    }

    /**
     * subject: 秒杀商品
     * api: /Miaosha/goods_list
     * author: Lazycat
     * day: 2017-03-31
     * content: day和time,不传默认为当前期，如果传值的话两个参数都必传
     *
     * [字段名,类型,是否必传,说明]
     * param: day,date,1,日期
     * param: time,time,1,时间，格式：12:00
     */
    //无法取小于当前时间的数据
    public function goods_list(){
        if(!empty($this->post['day']) || !empty($this->post['time'])) $this->check('day,time',false);

        $res = $this->_goods_list($this->post);
        $this->apiReturn($res);
    }

    public function _goods_list($param){
        $times      = $this->_times($param);
        $this_cfg   = $times['data']['active'];
        $schedule = M('officialactivity_schedule')->where(['activity_id' => $this->activity_id,'day' => $this_cfg['day'],'time' => $this_cfg['time']])->field('atime,etime,ip',true)->find();
        if($schedule){
            //取楼层
            $floor  = M('officialactivity_floor')->where(['schedule_id' => $schedule['id']])->order('sort asc')->field('atime,etime,ip',true)->select();
            foreach($floor as $key => $val){
                $floor_goods_id = M('officialactivity_floor_goods')->where(['floor_id' => $val['id']])->getField('sort,join_id',true);
                //dump($floor_goods_id);
                if($floor_goods_id) {
                    $tmp        = D('Common/OfficialactivityJoinRelation')->relation(true)->where(['id' => ['in', $floor_goods_id]])->field('atime,etime,ip', true)->select();
                    //dump(D('Officialactivityjoin168Relation')->getLastSql());
                    $tmp2       =array();
                    foreach($tmp as $v){

                        $v['sale_num']	= M('orders_goods')->where(['officialactivity_join_id' => $v['id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in ("2,3,4,5,6,11"))'])->sum('num');
                        $v['sale_num']	= $v['sale_num'] ? $v['sale_num'] : 0;
                        $v['images_album']    = unserialize(html_entity_decode($v['images']));
                        $v['images']    = $v['images_album'][0];
                        $v['score']     = $v['price'] * $v['goods']['score_ratio'] * 100;
                        $tmp2[] = $v;
                    }
                    //dump($tmp2);
                    $floor[$key]['goods'] = $tmp2;
                }

            }

            $data['goods_list'] = $floor;
        }

        $data['times']      = $times['data'];
        return ['code' => 1,'data' => $data];
    }

    /**
     * subject: 获取top条秒杀商品记录
     * api: /Miaosha/top_goods
     * author: Lazycat
     * day: 2017-04-01
     * content: day和time,不传默认为当前期，如果传值的话两个参数都必传
     *
     * [字段名,类型,是否必传,说明]
     * param: day,date,1,日期
     * param: time,time,1,时间，格式：12:00
     * param: num,int,0,获取记录数量，默认为8
     */
    //无法取小于当前时间的数据
    public function top_goods(){
        if(!empty($this->post['day']) || !empty($this->post['time'])) $this->check($this->_field('num','day,time'),false);
        else $this->check($this->_field('num'),false);

        $res = $this->_top_goods($this->post);
        $this->apiReturn($res);
    }

    public function _top_goods($param){
        $times      = $this->_times($param);
        $this_cfg   = $times['data']['active'];
        $limit      = $param['num'] > 0 ? $param['num'] : 8;

        $schedule   = M('officialactivity_schedule')->where(['activity_id' => $this->activity_id,'day' => $this_cfg['day'],'time' => $this_cfg['time']])->field('atime,etime,ip',true)->find();
        if($schedule){
            $floor_goods_id = M('officialactivity_floor_goods')->where(['schedule_id' => $schedule['id']])->limit($limit)->getField('join_id',true);
            if($floor_goods_id) {
                $tmp        = D('Common/OfficialactivityJoinRelation')->relation(true)->where(['id' => ['in', $floor_goods_id]])->field('atime,etime,ip', true)->select();
                $tmp2       =array();
                foreach($tmp as $v){
                    $v['sale_num']	= M('orders_goods')->where(['officialactivity_join_id' => $v['id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in ("2,3,4,5,6,11"))'])->sum('num');
                    $v['sale_num']	= $v['sale_num'] ? $v['sale_num'] : 0;
                    $v['images_album']    = unserialize(html_entity_decode($v['images']));
                    $v['images']    = $v['images_album'][0];
                    $v['score']     = $v['price'] * $v['goods']['score_ratio'] * 100;
                    $data['goods_list'][] = $v;
                }
            }

        }

        $data['times']      = $times['data'];
        return ['code' => 1,'data' => $data];
    }

    /**
     * 由于起初秒杀没什么用户报名，所以采用系统自动报名的方式
     * Create by Lazycat
     * 2017-04-01
     */
    public function auto_activity(){
        $this->check($this->_field('day,time'));

        $res = $this->_auto_activity($this->post);
        $this->apiReturn($res);
    }

    public function _auto_activity($param){
        $day = date('Y-m-d',time() + (86400 * 2));  //默认为创建2天后的活动
        if($param['day'] && $param['time']){
            $res = $this->_auto_activity_daytime($param['day'],$param['time']);
            return $res;
        }elseif($param['day'] && empty($param['time'])){
            $day = $param['day'];
        }

        $sday = $day.' 08:00';
        $res    = $this->_auto_activity_daytime($day,'08:00');
        for($i=0;$i<8;$i++){
            $sday   = date('Y-m-d H:i',strtotime($sday) + 7200);
            $tmp    = explode(' ',$sday);
            $res    = $this->_auto_activity_daytime($tmp[0],$tmp[1]);
        }
        return ['code' => 1];
    }

    //自动创建某一期活动，默认创建4个楼层
    public function _auto_activity_daytime($day,$time){
        $floor_name_arr = [
            '100845562'     => '手机数码',
            //'100841624'     => '皮具箱包',
            //'100841621'     => '男女服饰',
            '100841633'     => '食品保健',
            '100841645'     => '母婴玩具',
            //'100841648'     => '家居家纺',
        ];

        $shop_ids = [3864];
        //$shop_ids = [160,243];

        //创建排期
        $schedule_id = M('officialactivity_schedule')->where(['day' => $day,'time' => $time])->getField('id');
        if(!$schedule_id){
            $data = [
                'status'        => 0,
                'activity_id'   => $this->activity_id,
                'day'           => $day,
                'time'          => $time,
            ];

            if(!$schedule_id = M('officialactivity_schedule')->add($data)){
                return ['code' => 0,'msg' => '创建排期失败！'];
            }
        }


        //创建楼层
        foreach($floor_name_arr as $key => $val){
            $floor_id = M('officialactivity_floor')->where(['schedule_id' => $schedule_id,'floor_name' => $val])->getField('id');
            if(!$floor_id){
                $data = [
                    'status'        => 1,
                    'activity_id'   => $this->activity_id,
                    'schedule_id'   => $schedule_id,
                    'floor_name'    => $val,
                    'num'           => 8,
                ];

                if(!$floor_id = M('officialactivity_floor')->add($data)){
                    continue;
                }

                //商品报名，挑选出没有价格区间的商品，昨天报名过的商品不能再次报名
                $n      = 0;
                //昨天至今已报名的商品
                $ids    = M('officialactivity_join')->where(['status' => 1,'_string' => 'day="'.$day.'" or day="'.date('Y-m-d',strtotime($day) - 86400).'"'])->getField('goods_id',true);
                $cids   = sortid(['table' => 'goods_category','sid' => $key,'cache_name' => md5('_auto_activity_daytime_'.$key)]);

                $map = ['_string' =>'price = price_max','shop_id' => ['in',$shop_ids],'category_id' => ['in',$cids],'status' => 1,'price' => ['between','10,10000']];
                if($ids) $map['id'] = ['not in',$ids];
                $count  = M('goods')->where($map)->order('sale_num desc,pr desc')->count();

                $floor_goods_count = M('officialactivity_floor_goods')->where(['floor_id' => $floor_id])->count();
                if($floor_goods_count < 1) {
                    $max = $count <= 8 ? ($count-1) : 8;

                    while ($n < $max) {
                        if($ids) $map['id'] = ['not in',$ids];
                        $goods = M('goods')->where($map)->field('id,seller_id,shop_id,goods_name,price,num,images')->limit(rand(0, $count) . ',1')->select();
                        if (empty($goods)) continue;
                        $goods = $goods[0];
                        //if (M('goods_attr_list')->where(['goods_id' => $goods['id'], 'price' => ['neq', $goods['price']]])->count() > 0) continue;  //含有价格区间

                        $data = [
                            'status' => 1,
                            'shop_id' => $goods['shop_id'],
                            'uid' => $goods['seller_id'],
                            'activity_id' => $this->activity_id,
                            'day' => $day,
                            'time' => $time,
                            'goods_id' => $goods['id'],
                            'price' => $goods['price'],
                            'num' => $goods['num'],
                            'subject' => $goods['goods_name'],
                            'images' => serialize(array($goods['images'])),
                        ];

                        //创建报名记录
                        if ($join_id = M('officialactivity_join')->add($data)) {
                            //创建排期商品
                            $data = [
                                'status' => 1,
                                'activity_id' => $this->activity_id,
                                'schedule_id' => $schedule_id,
                                'floor_id' => $floor_id,
                                'join_id' => $join_id,
                                'goods_id' => $goods['id'],
                                'day' => $day,
                                'time' => $time,
                                'sort' => $n,
                            ];
                            M('officialactivity_floor_goods')->add($data);
                        }

                        $ids[] = $goods['id'];
                        $n++;
                    }
                }

            }
        }

        return ['code' => 1];
    }
}