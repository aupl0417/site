<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 秒杀
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class MiaoshaController extends CommonController {
    protected $day;     //活动时间
    protected $times;    //开场钟点
    protected $activity_id  = 250;  //秒杀活动ID
    protected $time;   //当前时间段
    protected $time_dif;    //倒计时间秒数
    public function _initialize() {
        parent::_initialize();

        //每天开抢时间
        $this->day  = date('Y-m-d');
        $time_arr = array('00:00','08:00','10:00','14:00','16:00','18:00','20:00','22:00');
        $status_name = array('已开抢','抢购进行中','即将开抢');

        foreach($time_arr as $i => $val){
            if(date('Y-m-d H:i') > $this->day.' '.$val && date('Y-m-d H:i') < $this->day.' '.substr($time_arr[$i+1],0,2)) {
                $status = 1;
                $this->time = $val;
            }
            elseif(date('Y-m-d H:i') > $this->day.' '.$val) $status = 0;
            elseif(date('Y-m-d H:i') < $this->day.' '.$val) $status = 2;

            $times[] = array(
                'time'         => $val,
                'status'        => $status,
                'status_name'   => $status_name[$status],
            );
        }

        $this->times = $times;


        //计算活动结束时间，由于用户较少，所以定义每期活动时长为24小时
        $this->time_dif = strtotime($this->day.' '.$this->time)+86400 - time();

        //抢购状态，小于1天的才出现倒计时
        $status = 1;    //抢购中
        if($this->time_dif < 0){
            $status = 0;   //已结束
        }elseif($this->time_dif < 86400){

        }elseif($this->time_dif > 86400){
            $status = 2;    //还款开始
        }

        $btn = ['抢购结束','立即抢购','即将开始'];

    }

    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 当天当期秒杀每个楼层的第一款商品
    */
    public function first(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $schedule = M('officialactivity_schedule')->cache(true)->where(['activity_id' => $this->activity_id,'day' => $this->day,'time' => $this->time])->field('id')->find();

        $goods = array();
        if($schedule){
            //取楼层
            $floor  = M('officialactivity_floor')->cache(true)->where(['schedule_id' => $schedule['id']])->order('sort asc')->field('atime,etime,ip',true)->select();
            foreach($floor as $key => $val){
                $floor_goods_id = M('officialactivity_floor_goods')->cache(true)->where(['floor_id' => $val['id']])->getField('sort,join_id',true);
                //dump($floor_goods_id);
                if($floor_goods_id) {
                    $tmp        = D('Common/OfficialactivityJoinRelation')->cache(true)->relation(true)->where(['id' => ['in', $floor_goods_id]])->field('atime,etime,ip', true)->find();
                    if($tmp){
                        $tmp['images']      = unserialize(html_entity_decode($tmp['images']));
                        $tmp['images'][0]   = myurl($tmp['images'][0],150);
                        $tmp['url']         = '/Goods/view/id/'.$tmp['goods_attr_list']['id'];
                        $goods[]            = $tmp;
                    }
                }
            }
        }


        $max    = 4;
        $count  = count($goods);
        
        if($count < $max){
            for($i=0;$i < ($max-$count);$i++){
                $goods[] = '';
            }
        }

        $this->apiReturn(1,['data' => $goods]);
    }

    /**
     * 获取某一期秒杀
     * @param date $_POST['date']   日期
     * @param string $_POST['time'] 秒杀时间段
     */
    public function miaosha(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        if(isset($_POST['day']) || isset($_POST['time'])) $this->need_param = array_merge($this->need_param,array('day','time'));
        $this->_need_param();
        $this->_check_sign();

        $this->day = I('post.day') ? I('post.day') : $this->day;
        $this->time = I('post.time') ? I('post.time') : $this->time;

        $schedule = M('officialactivity_schedule')->where(['activity_id' => $this->activity_id,'day' => $this->day,'time' => $this->time])->field('atime,etime,ip',true)->find();

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
                        $v['images']    = unserialize(html_entity_decode($v['images']));
                        $v['images'][0] = myurl($v['images'][0],150);
                        $v['url']       = '/Goods/view/id/'.$v['goods_attr_list']['id'];
                        $tmp2[] = $v;
                    }
                    //dump($tmp2);
                    $floor[$key]['goods'] = $tmp2;
                }

            }

            $this->apiReturn(1,['data' => $floor]);

        }else $this->apiReturn(3);


    }

}