<?php
namespace Wap\Controller;

class MiaoshaController extends CommonController{
    protected $day;     //活动时间
    protected $time;    //开场钟点
    protected $activity_id  = 250;  //秒杀活动ID
    protected $activity     = array();  //活动详情
    protected $shop_id;     //店铺ID
    protected $shop_info;   //店铺信息
    protected $this_time;   //当前时间段
    public function _initialize() {
        parent::_initialize();

        //每天开抢时间
        $this->day  = date('Y-m-d');
        //$this->time = array('08:00','10:00','14:00','16:00','18:00','20:00','22:00','24:00');
        $this->time = array('10:00','15:00');
        $status_name = array('已开抢','抢购进行中','即将开抢');

        foreach($this->time as $i => $val){
            if($i == 0 && date('Y-m-d H:i') < $this->day.' '.$val){
                $status = 2;
                $this->this_time = $val;
            }elseif(date('Y-m-d H:i') > $this->day.' '.$val) {
                $status = 1;
                $this->this_time = $val;
            }
            elseif(date('Y-m-d H:i') > $this->day.' '.$val) $status = 0;
            elseif(date('Y-m-d H:i') < $this->day.' '.$val) $status = 2;

            $times[] = array(
                'time'         => $val,
                'status'        => $status,
                'status_name'   => $status_name[$status],
            );
        }


        $this->assign('times',$times);


        //当前活动时间
        if(I('get.day'))    $this->day = I('get.day');
        if(I('get.time'))   $this->this_time = I('get.time');

        $this->assign('this_time',$this->this_time);
        $this->assign('this_day',$this->day);

        //计算活动结束时间，由于用户较少，所以定义每期活动时长为24小时
        $day_str    = $this->day.' '.$this->this_time;
        $str2time   = strtotime($day_str)+86400;
        $time_dif   = $str2time - time();

        //抢购状态，小于1天的才出现倒计时
        $status = 1;    //抢购中
        if($time_dif < 0){
            $status = 0;   //已结束
        }elseif($time_dif < 86400){
            $this->assign('time_dif',$time_dif);
        }elseif($time_dif > 86400){
            $status = 2;    //还款开始
        }

        $btn = ['抢购结束','立即抢购','即将开始'];
        $this->assign('btn',$btn);
        $this->assign('status',$status);
        //dump($btn[$status]);
        //dump($time_dif);
    }
    public function index(){

        $this->display();
    }

    /**
     * 当前期数
     */
    public function current_num(){
        $res = ['day' => $this->day,'time' => $this->this_time];
        return $res;
    }

    /**
     * 获取秒杀商品
     */
    public function item($param=null){
        if(!is_null($param['day'])) $this->day   = $param['day'];
        if(!is_null($param['time'])) $this->this_time = $param['time'];

        $schedule = M('officialactivity_schedule')->where(['activity_id' => $this->activity_id,'day' => $this->day,'time' => $this->this_time])->field('atime,etime,ip',true)->find();
        if($schedule){
            //取楼层
            $floor  = M('officialactivity_floor')->where(['schedule_id' => $schedule['id']])->order('sort asc')->field('atime,etime,ip',true)->select();
            foreach($floor as $key => $val){
                $floor_goods_id = M('officialactivity_floor_goods')->where(['floor_id' => $val['id']])->getField('sort,join_id',true);
                //dump($floor_goods_id);
                if($floor_goods_id) {
                    $tmp        = D('Common/OfficialactivityJoinRelation')->relation(true)->where(['id' => ['in', $floor_goods_id]])->field('atime,etime,ip', true)->select();
                    //dump(D('OfficialactivityJoinRelation')->getLastSql());
                    $tmp2       =array();
                    foreach($tmp as $v){
                        $v['sale_num']	= M('orders_goods')->where(['officialactivity_join_id' => $v['id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in ("2,3,4,5,6,11"))'])->sum('num');
                        $v['sale_num']	= $v['sale_num'] ? $v['sale_num'] : 0;
                        $v['images']    = unserialize(html_entity_decode($v['images']));
                        $tmp2[$v['id']] = $v;
                    }
                    //dump($tmp2);
                }
                for($i=0;$i<$val['num'];$i++){
                    $floor[$key]['goods'][$i]   =   $tmp2[$floor_goods_id[$i]];
                }
            }
            //dump($floor);
            
        }else{
            $floor = [];
        }
        # return $floor;
        $this->assign('floor',$floor);
        $this->assign('thisDay',$this->day);
        $html = $this->fetch('Miaosha:item');

        return $html;
    }
}