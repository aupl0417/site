<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class MiaoshaController extends CommonModulesController {
    protected $name 			='官方活动';	//控制器名称
    protected $formtpl_id		=165;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
    protected $sw               =array();       //保存事务执行结果

    protected $activity_id      =250;   //秒杀活动ID
    protected $activity         = array();  //活动详情
    protected $time; //每天活动时间
    protected $days;    //未来7天
    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

        //活动详情
        $this->activity = M('officialactivity')->where(['id' => $this->activity_id])->field('atime,etime,ip',true)->find();
        if($this->activity['shop_map']) $this->activity['shop_map']     = unserialize(html_entity_decode($this->activity['shop_map']));
        if($this->activity['goods_map']) $this->activity['goods_map']   = unserialize(html_entity_decode($this->activity['goods_map']));
        if($this->activity['imgsize']) $this->activity['imgsize']       = unserialize(html_entity_decode($this->activity['imgsize']));
        if($this->activity['accept_category']) $this->activity['accept_category']   = explode(',',$this->activity['accept_category']);

        $imgsize = array();
        foreach($this->activity['imgsize']['width'] as $i => $val){
            if($val){
                $imgsize [] = [
                    'width'     =>  $val,
                    'height'    =>  $this->activity['imgsize']['height'][$i] ? $this->activity['imgsize']['height'][$i] : $val
                ];
            }
        }
        $this->activity['imgsize'] = $imgsize;
        $this->assign('activity',$this->activity);

        //dump($this->activity);

        //每天开抢时间
        $this->time = array('00:00','08:00','10:00','14:00','16:00','18:00','20:00','22:00');
        //$this->time = array('10:00','15:00');
        $this->assign('time',$this->time);

        //最近7天充许报名的时间,只能报两天之后的时间
        for($i=8;$i>0;$i--){
            $this->days[] = date('Y-m-d',time()+(86400*$i));
        }
        $this->assign('days',$this->days);
    }

    /**
     * 报名记录
     */
    /*
    public function goods_list(){
        $pagelist = pagelist(array(
            'table'         =>'OfficialactivityJoinRelation',
            'do'            =>'D',
            'relation'      =>true,
            'pagesize'      =>15,
            'order'         =>'day desc,id desc',
        ));

        foreach($pagelist['list'] as $i => $val){
            if($val['images']){
                $pagelist['list'][$i]['images'] = unserialize(html_entity_decode($val['images']));
            }
        }

        $this->assign('pagelist',$pagelist);

        //dump($pagelist);
        $this->display();
    }
    */

    /**
     * 活动排期
     */
    public function days(){
        $list = M('officialactivity_schedule')->where(['day' => ['elt',date('Y-m-d')]])->group('day')->order('day desc')->getField('day',true);
        if($list){
            if(!in_array(date('Y-m-d'),$list)) $this->days[] = date('Y-m-d');
            $this->days = array_merge($this->days,$list);
            $this->assign('days',$this->days);
        }
        $this->display();

    }

    public function day(){
        if(I('get.day') && I('get.time')){
            //排期
            $schedule = M('officialactivity_schedule')->where(['activity_id' => $this->activity_id,'day' => I('get.day'),'time' => I('get.time')])->field('atime,etime,ip',true)->find();

            if($schedule){
                $schedule['goods_num'] =0;
                //取楼层
                $floor  = M('officialactivity_floor')->where(['schedule_id' => $schedule['id']])->order('sort asc')->field('atime,etime,ip',true)->select();
                foreach($floor as $key => $val){
                    $schedule['num']    += $val['num'];
                    $floor_goods_id = M('officialactivity_floor_goods')->where(['floor_id' => $val['id']])->getField('sort,join_id',true);
                    //dump($floor_goods_id);
                    if($floor_goods_id) {
                        $tmp        = D('Officialactivityjoin168Relation')->relation(true)->where(['id' => ['in', $floor_goods_id]])->field('atime,etime,ip', true)->select();
                        //dump(D('Officialactivityjoin168Relation')->getLastSql());
                        $tmp2       =array();
                        foreach($tmp as $v){
                            $v['images']    = unserialize(html_entity_decode($v['images']));
                            $tmp2[$v['id']] = $v;
                        }
                        //dump($tmp2);
                        $schedule['goods_num']  += count($tmp2);
                    }
                    for($i=0;$i<$val['num'];$i++){
                        $floor[$key]['goods'][$i]   =   $tmp2[$floor_goods_id[$i]];
                    }
                }

                //dump($floor);

                $this->assign('schedule',$schedule);
                $this->assign('floor',$floor);
            }

            //距离活动开始时间
            $time_dif = strtotime(I('get.day').' '.I('get.time')) - time();
            //if($time_dif >0 && $time_dif < 3600 * 24) $this->assign('is_dectime',1);
            $this->assign('time_dif',$time_dif);

        }else{
            redirect(__CONTROLLER__.'/day/day/'.date('Y-m-d').'/time/'.$this->time[0].'#'.date('Y-m-d'));
            exit();
        }



        $this->display();
    }

    /**
     * 添加楼层
     */
    public function floor_add(){
        $this->display();
    }

    public function floor_add_save(){
        if(I('post.day').' '.I('post.time') < date('Y-m-d H:i')) $this->ajaxReturn(['status' => 'warning','msg' => '时间已过期，不能进行排期！']);
        $schedule = M('officialactivity_schedule')->where(['activity_id' => $this->activity_id,'day' => I('post.day'),'time' => I('post.time')])->field('id')->find();
        if(empty($schedule)){
            $schedule['id'] = M('officialactivity_schedule')->add(['day' => I('post.day'),'time' =>I('post.time'),'activity_id' =>$this->activity_id]);
        }
        $n=0;
        foreach($_POST['floor_name'] as $val){
            if($val){
                $data = [
                    'activity_id'   => $this->activity_id,
                    'schedule_id'   => $schedule['id'],
                    'day'           => I('post.day'),
                    'time'          => I('post.time'),
                    'floor_name'    => $val,
                    'num'           => 8,
                ];
                if(!M('officialactivity_floor')->where($data)->find()) {
                    M('officialactivity_floor')->add($data);
                    $n++;
                }
            }
        }
        if($n>0) $this->ajaxReturn(['status' =>'success','msg' =>'操作成功！']);
        else $this->ajaxReturn(['status' =>'warning','msg' =>'操作失败！请输入楼层名称或检查输入的楼层名称是否已存在！']);
    }

    /**
     * 楼层排序
     */
    public function floor_sort(){
        foreach(I('post.ids') as $key => $val){
            M('officialactivity_floor')->where(['id' => $val])->setField('sort',$key+1);
        }

        $this->ajaxReturn(['status' =>'success']);
    }

    /**
     * 楼层设置
     */
    public function floor_edit(){
        $rs = M('officialactivity_floor')->where(['id' => I('get.id')])->find();
        $this->assign('rs',$rs);
        $this->display();
    }

    public function floor_edit_save(){
        $do = D('Common/OfficialactivityfloorScheduleRelation');
        $rs = $do->relation(true)->where(['id' => I('post.id')])->find();
        if($rs['schedule']['status'] == 1 && $rs['num'] != I('post.num')) $this->ajaxReturn(['status' => 'warning','msg' => '活动已开始，不可修改楼层商品数量！']);

        $count = M('officialactivity_floor_goods')->where(['activity_id' => $rs['activity_id'],'floor_id' => I('post.id')])->count();
        if($count > I('post.num')) {
            $this->ajaxReturn(['status' => 'warning','msg' => '目前排期中的商品有'.$count.'，请删除掉部分方可更改楼层商品数量！']);
        }

        $do = M();
        $do->startTrans();
        if(!$this->sw[] = M('officialactivity_floor')->create()){
            goto error;
        }
        if(!$this->sw[] = M('officialactivity_floor')->save()){
            goto error;
        }

        //更改楼层商品数量后需要重新排序
        if($rs['num'] != I('post.num') && $count > 0){
            $floor_goods = M('officialactivity_floor_goods')->where(['activity_id' => $rs['activity_id'],'floor_id' => I('post.id')])->order('sort asc,id asc')->getField('id',true);
            foreach ($floor_goods as $key => $val){
                if($this->sw[] = false === M('officialactivity_floor_goods')->where(['id' => $val])->save(['sort' => $key])){
                    goto error;
                }
            }
        }

        $do->commit();
        $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'.@implode(',',$this->sw)]);
    }

    /**
     * 删除楼层
     */
    public function floor_delete(){
        $do = D('Common/OfficialactivityfloorScheduleRelation');
        $rs = $do->relation(true)->where(['id' => I('post.id')])->find();

        if($rs['schedule']['status'] == 1) $this->ajaxReturn(['status' => 'warning','msg' => '活动已开始，不可执行删除操作！']);

        if($rs['schedule']['day'].' '.$rs['schedule']['time'] < date('Y-m-d H:i')) $this->ajaxReturn(['status' => 'warning','msg' => '活动已过期，不可执行删除操作！']);

        $join_ids = M('officialactivity_floor_goods')->where(['activity_id' => $rs['activity_id'],'floor_id' => I('post.id')])->getField('join_id',true);
        $do = M();
        $do->startTrans();

        if($join_ids) {
            if (!$this->sw[] = M('officialactivity_join')->where(['id' => ['in',$join_ids]])->save(['time' => ''])) {
                goto error;
            }

            //清除商品中的活动信息
            /*
            $goods_ids = M('officialactivity_join')->where(['id' => ['in',$join_ids]])->getField('goods_id',true);
            if(!$this->sw[] = M('goods')->where(['id' => ['in',$goods_ids]])->save(['officialactivity_join_id' => 0,'officialactivity_price' => 0])) {
                goto error;
            }
            */

            if(!$this->sw[] = M('officialactivity_floor_goods')->where(['activity_id' => $rs['activity_id'],'floor_id' => I('post.id')])->delete()){
                goto error;
            }
        }

        if(!$this->sw[] = M('officialactivity_floor')->where(['id' => I('post.id')])->delete()){
            goto error;
        }

        $do->commit();
        $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'.@implode(',',$this->sw)]);
    }

    /**
     * 选择商品
     */
    public function select_goods(){
        //已排期的商品
        $ids = M('officialactivity_floor_goods')->where(['activity_id' => $this->activity_id,'day' => I('get.day')])->getField('join_id',true);
        if($ids) $map['id']          =   ['not in',$ids];

        $map['activity_id'] =   $this->activity_id;
        $map['day']         =   I('get.day');
        $map['status']      =   1;
        $pagelist = pagelist(array(
            'table'     => 'Officialactivityjoin168Relation',
            'do'        => 'D',
            'relation'  => true,
            'map'       => $map,
            'pagesize'  => 6,
            'ajax'      => 1,
            'page_js'   => 'page([p])',
        ));


        foreach($pagelist['list'] as $key => $val){
            if($val['images']) $pagelist['list'][$key]['images'] = unserialize(html_entity_decode($val['images']));
        }

        $this->assign('pagelist',$pagelist);
        //dump($pagelist);

        if(isset($_GET['p'])) unset($_GET['p']);
        $this->assign('query',http_build_query(I('get.')));

        $this->display();
    }

    /**
     * 对楼层添加活动商品
     */
    public function floor_goods_add(){
        $join = M('officialactivity_join')->where(['id' => I('post.join_id')])->field(['id,goods_id,price'])->find();

        $do = M('officialactivity_floor_goods');
        $_POST['activity_id']   = $this->activity_id;
        $_POST['goods_id']      = $join['goods_id'];

        if($do->where(['join_id' => I('post.join_id')])->find()){
            $this->ajaxReturn(['status' => 'warning','msg' =>'该商品已排期，不可重复添加！']);
        }

        //删除该位置旧数据
        if($rs = $do->where(['floor_id' => I('post.floor_id'),'sort' => I('post.sort')])->field('id,join_id')->find()){
            $this->ajaxReturn(['status' => 'warning','msg' =>'请先删除该位置旧数据才可更换商品！']);
        }

        $do->startTrans();
        if(!$this->sw[] = $do->create()){
            $msg = $do->getError();
            goto error;
        }
        if(!$this->sw[] = $do->add()){
            $msg = '插入数据失败！';
            goto error;
        }

        if(!$this->sw[] = M('officialactivity_join')->where(['id' => I('post.join_id')])->setField('time',I('post.time'))) {
            $msg = '更新报名商品失败！';
            goto error;
        }

        //设置商品活动状态
        /*
        if (!$this->sw[] = M('goods')->where(['id' => $join['goods_id']])->save(['officialactivity_join_id' => $join['id'], 'officialactivity_price' => $join['price']])) {
            $msg = '设置商品活动状态失败！';
            goto error;
        }
        */


        $do->commit();
        $this->assign('vo',I('post.'));
        $rs = D('Officialactivityjoin168Relation')->relation(true)->where(['id' => I('post.join_id')])->find();
        $rs['images']   = unserialize(html_entity_decode($rs['images']));
        $this->assign('gl',$rs);

        $html = $this->fetch('item_goods');

        $this->ajaxReturn(['status' => 'success','msg' => '操作成功！','html' => $html]);

        error:
        $do->rollback();
        $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'.$msg]);

    }

    /**
     * 删除活动商品
     */
    public function floor_goods_delete(){
        $do = M('officialactivity_floor_goods');
        $goods_id = M('officialactivity_join')->where(['id' => I('post.id')])->getField('goods_id');

        $do->startTrans();
        if(!$this->sw[] = $do->where(['floor_id' => I('post.floor_id'),'join_id' => I('post.id')])->delete()) goto error;
        if(!$this->sw[] = M('officialactivity_join')->where(['id' => I('post.id')])->setField('time','')) goto error;
        //if(!$this->sw[] = M('goods')->where(['id' => $goods_id])->save(['officialactivity_join_id' => 0,'officialactivity_price' => 0])) goto error;

        $do->commit();
        $this->assign('vo',I('post.'));
        $html = $this->fetch('no_goods');
        $this->ajaxReturn(['status' => 'success','msg' => '操作成功！','html' => $html]);

        error:
        $do->rollback();
        $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'.implode(',',$this->sw)]);

    }

    /**
     * 楼层商品排序
     */
    public function floor_goods_sort(){
        foreach(I('post.ids') as $key => $val){
            if($val){
                M('officialactivity_floor_goods')->where(['floor_id' => I('post.floor_id'),'join_id' => $val])->setField('sort',$key);
            }
        }
        $this->ajaxReturn(['status' => 'success']);
    }

    /**
     * 发送提醒短信
     */

    public function sms_send(){
        $this->display();
    }

    public function sms_send_save(){
        $join_ids = M('officialactivity_floor_goods')->where(['schedule_id' => I('post.id')])->getField('join_id',true);
        if(!$join_ids) $this->ajaxReturn(['status' => 'warning','msg' => '还未设置好活动商品！']);

        $shop_ids   = M('officialactivity_join')->where(['id' => ['in',$join_ids]])->group('shop_id')->getField('shop_id');
        $mobile     = M('officialactivity_contact')->where(['shop_id' => ['in' , $shop_ids]])->getField('mobile',true);
        $mobile     = implode(',',$mobile);
        //dump($mobile);
        //$mobile     = '13710356176';

        $res = sms_send(['mobile' => $mobile,'content' => I('post.content').'【乐兑】']);
        if($res) $this->ajaxReturn(['status' => 'success','msg' =>'发送成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' =>'发送失败！']);
    }

    /**
     * 设置活动倒计时
     */
    public function set_dectime(){
        $schedule = M('officialactivity_schedule')->where(['id' => I('post.schedule_id')])->field('atime,etime,ip',true)->find();
        if($schedule['status'] != 0) $this->ajaxReturn(['status' => 'warning','msg' => '该状态下不充许进行设置！']);

        //只能在活动开始的前24个小时内进行设置
        $time_dif = strtotime($schedule['day'].' '.$schedule['time']) - time();
        if($time_dif < 0 || $time_dif > 3600 * 24) $this->ajaxReturn(['status' => 'warning','msg' => '只能在活动开始的前6个小时内进行设置！']);
        //dump($time_dif);

        //取活动名额
        $ren_num = M('officialactivity_floor')->where(['schedule_id' => I('post.schedule_id')])->sum('num');

        //取活动商品
        $join_ids = M('officialactivity_floor_goods')->where(['schedule_id' => I('post.schedule_id')])->getField('join_id',true);
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

        if(!$this->sw[] = M('officialactivity_schedule')->where(['id' => I('post.schedule_id')])->save(['status' => 1])) goto error;

        $do->commit();
        $this->ajaxReturn(['status' => 'success','msg' => '设置成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '设置失败！'.implode(',',$this->sw)]);
    }

    /**
     * 结束秒杀活动，并恢复商品原始状态
     */
    public function set_recovery(){
        $schedule = M('officialactivity_schedule')->where(['id' => I('post.schedule_id')])->field('atime,etime,ip',true)->find();
        if($schedule['status'] != 1) $this->ajaxReturn(['status' => 'warning','msg' => '该状态下不充许进行设置！']);

        //活动时长为24小时，须24小时后方可结束活动
        $time_dif = strtotime($schedule['day'].' '.$schedule['time']) - time();
        if($time_dif > 3600 * 24 * -1) $this->ajaxReturn(['status' => 'warning','msg' => '活动还未结束！']);

        //取参与活动的商品
        $goods_ids = M('officialactivity_join')->where(['activity_id' => $this->activity_id,'day' => $schedule['day'],'time' => $schedule['time']])->getField('goods_id',true);

        $do = M();
        $do->startTrans();

        if($goods_ids) {
            if(!$this->sw[] = M('goods')->where(['id' => ['in',$goods_ids]])->save(['officialactivity_join_id' => 0,'officialactivity_price' => 0])) goto error;
        }
        if(!$this->sw[] = M('officialactivity_schedule')->where(['id' => I('post.schedule_id')])->save(['status' => 2])) goto error;

        $do->commit();
        $this->ajaxReturn(['status' => 'success','msg' => '设置成功！']);

        error:
        $do->rollback();
        $this->ajaxReturn(['status' => 'warning','msg' => '设置失败！']);
    }

    /**
     * 验证是否充许操作，通常活动过期以后不充许操作
     */
    public function check_activity(){

    }

    /**
     * 数据分析
     */
    public function analysis(){
        $this->display();
    }
}