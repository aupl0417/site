<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/22
 * Time: 19:28
 */

namespace Home\Controller;


use Mobile\Controller\CommonController;
use Think\Controller;
use Think\Exception;

class HongbaoController extends CommonController
{
    static $maxNumByUser = 10;    //单次最大领取数量
    static $maxNum       = 80000;//单场最多可以领取数量
    static $cacheNameAll = 'trj_activity_hongbaoyu_winning_count';
    static $timeOver     = 1800; //可抢30分钟
    static $EffectiveClickTime          = 5; //有效点击时间3秒后
    static $EffectiveClickTimeCacheName = 'effective_click_time_cache_name';    //有效点击时间记录
    static $areaTime     = [
        '10:00:00',
        '16:00:00',
        '21:00:00'
    ];
    /*static $prize = [
        1 => [
            'coupon'=>  188,  //优惠券ID
            'num'   =>  10, //中奖概率  百分比
        ],
        2 => [
            'coupon'=>  189,
            'num'   =>  7,
        ],
        5 => [
            'coupon'=>  192,
            'num'   =>  2,
        ],
        's' => [
            'coupon'=>  197,
            'num'   =>  1,
        ],
        0 => [
            'coupon'=>  0,
            'num'   =>  80,
        ],
    ];*/
    static $prize = [
        1 => [
            'coupon'=>  902,  //优惠券ID
            'num'   =>  10, //中奖概率  百分比
        ],
        2 => [
            'coupon'=>  905,
            'num'   =>  7,
        ],
        5 => [
            'coupon'=>  907,
            'num'   =>  2,
        ],
        's' => [
            'coupon'=>  908,
            'num'   =>  1,
        ],
        0 => [
            'coupon'=>  0,
            'num'   =>  80,
        ],
    ];
    //protected $cacheNameUser;
    public function _initialize()
    {
        parent::_initialize();
        //dump(session());
    }

    /**
     * subject: 红包页面
     * api: index
     * author: Mercury
     * day: 2017-04-22 19:29
     * [字段名,类型,是否必传,说明]
     */
    public function index()
    {
        //取出商品
        $data = [
            //'openid'    => 'b3afac450126aea7e45edb9588ad5323',
            'openid'    => 'aa99f8ecd77cf9b778b37efe39c8b2ea',
            'pagesize'  => 24,
            'nosign'    => 'pagesize',
        ];
        if (in_array(date('H') . ':00:00', self::$areaTime) && date('H:i:s') <= date('H') . ':30:00') { //当前活动正在进行 则3s之后直接下红包雨
            $nextTime = 3;
        } else {    //  否则找出下一波
//            $nowTime  = date('Y/m/d H:i:s');
//            $toDay    = date('Y/m/d ');
//            $keys     = 0;
//            if (date('H:i:s') <= '21:30:00' && date('H:i:s') >= '10:00:00') {
//                foreach (self::$areaTime as $key => $item) {
//                    //如果在半个小时内则把nextTime设为5秒，如果超过半个小时则等待下一场次
//                    if ($toDay . $item > $nowTime) {
//                        $keys = $key;
//                        break;
//                    }
//                }
//                $firstTime = strtotime('2017/04/24 10:00:00');
//            } else {    //如果当前时间为晚上9点半之后或者10点之前则取出第二天第一波
//                $firstTime = strtotime(date('Y-m-d', strtotime("+1 day")) . ' 10:00:00');
//            }
//            $nextTime  = $firstTime < strtotime($nowTime) ? strtotime(self::$areaTime[$keys]) - strtotime($nowTime) : $firstTime - strtotime($nowTime);
            $nextTime = self::nextTime();
        }

        $this->assign('nextTime', $nextTime);
        //正在进行。。。
        $list = $this->doApi2('/Shop/goods',['shop_id' => 3864,'is_best' => 1, 'pagesize' => 24]);
        //$list = $this->curl('/sellerGoods/goods_online', $data, 1);
        $this->assign('data', $list['data']['list']);
        $this->display();
    }

    /**
     * subject: 优惠券分配
     * api: winning
     * author: Mercury
     * day: 2017-04-22 19:29
     * [字段名,类型,是否必传,说明]
     */
    public function winning()
    {
        if (IS_POST) {
            $this->ajaxReturn(['code' => 0, 'msg' => 'over']);
            if (getUid() <= 0) $this->ajaxReturn(['code' => 401, 'msg' => '请登录']);  //未登录的用户则需要重新登录
            $clickTime = S(self::$EffectiveClickTimeCacheName.getUid());
            if ($clickTime && $clickTime >= NOW_TIME) $this->ajaxReturn(['code' => 2, 'msg' => '无效点击']);
            S(self::$EffectiveClickTimeCacheName.getUid(), NOW_TIME+self::$EffectiveClickTime);//记录点击时间
            //有效时间判断  10分钟内领取有效
            $nowTime = date('H', NOW_TIME);
            if (!in_array($nowTime . ':00:00', self::$areaTime)) $this->ajaxReturn(['code' => 0, 'msg' => 'over', 'data' => self::nextTime()]);
            if (date('H:i:s') > date('H') . ':30:00') $this->ajaxReturn(['code' => 0, 'msg' => 'over', 'data' => self::nextTime()]);
            //当前用户是否已经点击10次
            $cacheName = 'trj_activity_hongbaoyu_'.getUid();
            $userClick = S($cacheName);
            if ($userClick >= self::$maxNumByUser) $this->ajaxReturn(['code' => 0, 'msg' => 'over', 'data' => self::nextTime()]);
            S($cacheName, $userClick?$userClick+1:1, 3600);//记录当前用户点击次数
            //记录所有用户点击次数
            $cnt       = S(self::$cacheNameAll);
            if ($cnt >= self::$maxNum) $this->ajaxReturn(['code' => 0, 'msg' => 'over', 'data' => self::nextTime()]);
            S(self::$cacheNameAll, $cnt?$cnt+1:1, 3600);

            $prize = self::getWinning();  //取得抽奖信息
            if ($prize['coupon'] == 0) $this->ajaxReturn(['code' => 0, 'msg' => 'nothing']);    //未抽中

            try {
                $model     = M();
                $model->startTrans();
                //取出优惠券信息
                $coupon    = M('coupon_batch')->where(['id' => $prize['coupon']])->cache(true)->find();
                if ($coupon == false) throw new Exception('nothing');
                $iData = [
                    'uid'   =>  getUid(),
                    'b_id'  =>  $prize['coupon'],
                    'price' =>  $coupon['price'],
                    'code'  =>  md5(session('user.openid') . NOW_TIME . session('id')),
                    'sday'  =>  $coupon['sday'],
                    'eday'  =>  $coupon['eday'],
                    'shop_id'   =>  $coupon['shop_id'],
                    'min_price' =>  $coupon['min_price'],
                    'is_use'    =>  0,  //是否已使用
                    'status'    =>  1,  //当前状态
                    //'use_time'  =>  date('Y-m-d H:i:s', NOW_TIME),  //领取时间
                    'channel'   =>  2,//抽奖
                    'type'      =>  1,//类型 店铺优惠券
                    //'use_type'  =>  2,  //指定店铺
                    'ip'        =>  get_client_ip(),
                ];
                $iData['short_code'] =  shortUrl($iData['code']);
                if (M('coupon')->add($iData) == false) throw new Exception('nothing');
                if (M('coupon_batch')->where(['id' => $prize['coupon']])->setInc('get_num', 1, 30) == false) throw new Exception('nothing');
                $model->commit();
                $this->ajaxReturn(['code' => 1, 'msg' => '已中奖', 'data' => intval($coupon['price'])]);
            } catch (Exception $e) {
                $model->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }


//            try {
//                $model     = M();
//                $model->startTrans();
//                $msg       = self::$msg[rand(0,5)]; //提示语
//
//
//                $coupons   = S($cacheName);
//                $key       = self::$coupons[rand(0, 12)];  //随机取出一张优惠券
//                $coupon    = M('coupon_batch')->where(['id' => $key])->cache(true)->find();
//                //数据操作
//                $iData = [
//                    'uid'   =>  getUid(),
//                    'b_id'  =>  $key,
//                    'price' =>  $coupon['price'],
//                    'code'  =>  md5(session('user.openid') . NOW_TIME . session('id')),
//                    'sday'  =>  $coupon['sday'],
//                    'eday'  =>  $coupon['eday'],
//                    'shop_id'   =>  $coupon['shop_id'],
//                    'min_price' =>  $coupon['min_price'],
//                    'is_use'    =>  0,  //是否已使用
//                    'status'    =>  1,  //当前状态
//                    'use_time'  =>  date('Y-m-d H:i:s', NOW_TIME),  //领取时间
//                    'channel'   =>  2,//抽奖
//                    'type'      =>  1,//类型 店铺优惠券
//                    'use_type'  =>  2,  //指定店铺
//                ];
//                if (false == $coupons) { //一个都还没领取
//                    $data= [
//                        $key => NOW_TIME,
//                    ];
//                    if (M('coupon')->add($iData) == false) throw new Exception($msg);
//                    S($cacheName, serialize($data));    //存到缓存
//                    S(self::$cacheNameAll, $count + 1); //总数+1
//                } else {    //查看已领取了那些
//                    $coupons = unserialize($coupons);
//                    if (count($coupons) >= 10) throw new Exception($msg);   //单次不能领取10张
//                    if (array_key_exists($key, $coupons)) throw new Exception($msg);    //如果已经了领取了这一张则返回不可领取或者再rand的一次
//                    //取出后领取的时间
//                    $lastTime = array_search(max($coupons), $coupons);  //找出最后领取的时间
//                    if (NOW_TIME - $coupons[$lastTime] < 60) throw new Exception($msg); //一分钟之内不可以领取两次
//                    //如果按次数领取则在时间后面加个数量在用字符串截取取出来
//                    $data = [
//                        $key => NOW_TIME, //领取优惠券
//                    ];
//                    $coupons = array_merge($coupons, $data);    //合并到已经领取的数组中
//                    //数据操作
//                    if (M('coupon')->add($iData) == false) throw new Exception($msg);
//                    S($cacheName, serialize($coupons)); //  存到缓存
//                    S(self::$cacheNameAll, $count+1);   //统计所有
//                }
//                //为当前优惠券更新领取+1
//                if (M('coupon_batch')->where(['id' => $key])->setInc('get_num', 1) == false) throw new Exception($msg);
//                //val = 124|256|785|677|3654|
//                //存储为序列化数据
//                $model->commit();
//                $this->ajaxReturn(['code' => 0, 'msg' => '领取成功', 'data' => []]);
//            } catch (Exception $e) {
//                $model->rollback();
//                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
//            }

        }
    }

    /**
     * subject: 获取中奖信息
     * api: getWinning
     * author: Mercury
     * day: 2017-04-24 14:21
     * [字段名,类型,是否必传,说明]
     * @return mixed
     */
    private static function getWinning()
    {
        $cacheByAllClick = 'trj_hongbao_coupon_cache_by_all_click';
        $cacheByCouponsNum = 'trj_hongbao_coupon_cache_by_coupon_num';
        //保存点击次数
        $num = S($cacheByAllClick);
        if ($num >= 100) {
            S($cacheByAllClick, null);//满100清空
            S($cacheByCouponsNum, self::shuStr());  //满100生成随机字符串
        }
        S($cacheByAllClick, $num>=100||$num==false?1:$num+1, 3600);
        if (S($cacheByCouponsNum) == false) S($cacheByCouponsNum, self::shuStr());
        $str   = S($cacheByCouponsNum);
        return self::$prize[$str[$num-1]];
    }

    /**
     * subject: 打乱字符串
     * api: shuStr
     * author: Mercury
     * day: 2017-04-24 14:20
     * [字段名,类型,是否必传,说明]
     * @return string
     */
    private static function shuStr()
    {
        $str = '';
        foreach (self::$prize as $k=>$v) {
            for ($i=0;$i<$v['num'];$i++) {
                $str .= $k;
            }
        }
        return str_shuffle($str);
    }

    /**
     * subject: 下一波时间
     * api: nextTime
     * author: Mercury
     * day: 2017-04-24 19:46
     * [字段名,类型,是否必传,说明]
     * @return false|int
     */
    private static function nextTime()
    {
        $nowTime  = date('Y/m/d H:i:s');
        $toDay    = date('Y/m/d ');
        $keys     = 0;
        $cacheName= 'trj_activity_hongbaoyu_'.getUid();
        $userClick= S($cacheName);
        //if (date('H:i:s') <= '21:30:00' && date('H:i:s') >= '10:00:00' && $userClick < self::$maxNumByUser) {
            foreach (self::$areaTime as $key => $item) {
                //如果在半个小时内则把nextTime设为5秒，如果超过半个小时则等待下一场次
                if ($toDay . $item > $nowTime) {
                    $keys = $key;
                    break;
                }
            }
            $firstTime = strtotime(date('Y/m/d') . ' 10:00:00');
//        } else {    //如果当前时间为晚上9点半之后或者10点之前则取出第二天第一波
//            $firstTime = strtotime(date('Y-m-d', strtotime("+1 day")) . ' 10:00:00');
//        }
        if (date('H:i:s') >= '21:30:00') {
            $firstTime = strtotime(date('Y-m-d', strtotime("+1 day")) . ' 10:00:00');
        }
        $nextTime  = $firstTime < strtotime($nowTime) ? strtotime(self::$areaTime[$keys]) - strtotime($nowTime) : $firstTime - strtotime($nowTime);
        return $nextTime;
    }
}