<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class Luckdraw1Controller extends CommonModulesController {
	protected $name 			='抽奖游戏';	//控制器名称
    protected $formtpl_id		=230;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

    }

    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" data-id="[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0 btn-view">修改</a><div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">绑定奖品</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

    /**
     * subject: 添加奖品
     * api: view
     * author: Mercury
     * day: 2017-04-22 14:00
     * [字段名,类型,是否必传,说明]
     */
    public function view()
    {
        $id     = I('get.id');
        $model  = D('Luckdraw1230View');
        $rs     = $model->where(['id' => $id])->find();
        if ($rs) {
            $rs['rule']     = html_entity_decode($rs['rule']);
            $rs['coupons']  = join(',', M('luckdraw1_coupon_condition')->where(['id' => ['in', $rs['coupon_condition']]])->getField('price', true));
            $prize          = M('luckdraw1_prize')->where(['luckdraw_id' => $rs['id']])->field('probability,max_winning,sort,luckdraw_id,id,type_id,value')->select();   //奖品
            if ($prize) {
                $prize = array_reduce($prize, function (&$prize, $val) {
                    switch ($val['type_id']) {
                        case 1: //代金券
                        case 2: //优惠券
                            if (!empty($val['value'])) {
                                $tmp = M('coupon_batch')->order('id asc')->where(['id' => ['in', trim($val['value'], ',')]])->field('id,min_price,price')->select();
                                if ($tmp) {
                                    foreach ($tmp as $k => $v) {
                                        $val['coupons'] .= '<span data-id="'.$v['id'].'" class="mr10 btn btn-success btn-trans">满 ' . $v['min_price'] . ' 减 ' . $v['price'] . '</span>';
                                    }
                                }
                            }
                            break;
                    }
                    $prize[$val['sort']] = $val;
                    return $prize;
                });
                $this->assign('prize', $prize);
            }
            $prizeCate = M('luckdraw1_prize_category')->cache(true)->where(['status' => 1])->getField('id,name', true);
            $eChart['title'] = '奖品类型';
            $eChart['option']= 'prize';
            $eChart['xAxis'] = array(     //xAxis:基于横向坐标的矩形图，yAxis基于纵向坐标的矩形图
                0 =>'所有',
                1 =>'今天',
                2 =>'昨天',
            );
            $i=0;
            $winning = [];
            foreach ($prizeCate as $k => $v) {
                $eChart['x_title'][$i] = $v;
                $eChart['data'][$i]['name']    = $v;
                $eChart['data'][$i]['type']    = 'bar';
                //所有
                $rs['winning']['all'][$k]['name']           = $v;
                $rs['winning']['all'][$k]['value']          = M('luckdraw1_winning')->where(['luck_id' => $id, 'type_id' => $k])->count();
                //今天
                $rs['winning']['today'][$k]['name']         = $v;
                $rs['winning']['today'][$k]['value']        = M('luckdraw1_winning')->where(['luck_id' => $id, 'type_id' => $k, 'atime' => ['gt', date('Y-m-d')]])->count();
                //昨天
                $rs['winning']['yesterday'][$k]['name']     = $v;
                $rs['winning']['yesterday'][$k]['value']    = M('luckdraw1_winning')->where(['luck_id' => $id, 'type_id' => $k, 'atime' => ['between', [date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d', NOW_TIME)]]])->count();
                $winning['all']                     += $rs['winning']['all'][$k]['value'];
                $winning['today']                   += $rs['winning']['today'][$k]['value'];
                $winning['yesterday']               += $rs['winning']['yesterday'][$k]['value'];
                $eChart['data'][$i]['data']    = [
                    $rs['winning']['all'][$k]['value'],
                    $rs['winning']['today'][$k]['value'],
                    $rs['winning']['yesterday'][$k]['value']
                ];
                $i++;
            }


            $statusArr = ['唐宝','免费','订单'];
            $feChart['title'] = '机会类型';
            $feChart['option']= 'isFree';
            $feChart['xAxis'] = array(     //xAxis:基于横向坐标的矩形图，yAxis基于纵向坐标的矩形图
                0 =>'所有',
                1 =>'今天',
                2 =>'昨天',
            );
            $isFree = [];
            foreach ($statusArr as $k => $v) {
                $feChart['x_title'][$k]         = $v;
                $feChart['data'][$k]['name']    = $v;
                $feChart['data'][$k]['type']    = 'bar';
                $rs['isFree']['all'][$k]['name']        = $v;
                $rs['isFree']['all'][$k]['value']       = M('luckdraw1_winning')->where(['luck_id' => $id, 'is_free' => $k])->count();
                //$rs['isFree']['all'][$k]['ratio']       = '';
                $rs['isFree']['today'][$k]['name']      = $v;
                $rs['isFree']['today'][$k]['value']     = M('luckdraw1_winning')->where(['luck_id' => $id, 'is_free' => $k, 'atime' => ['gt', date('Y-m-d')]])->count();
                $rs['isFree']['yesterday'][$k]['name']  = $v;
                $rs['isFree']['yesterday'][$k]['value'] = M('luckdraw1_winning')->where(['luck_id' => $id, 'is_free' => $k, 'atime' => ['between', [date('Y-m-d', strtotime('-1 day')), date('Y-m-d', NOW_TIME)]]])->count();
                $isFree['all']                  += $rs['isFree']['all'][$k]['value'];
                $isFree['today']                += $rs['isFree']['today'][$k]['value'];
                $isFree['yesterday']            += $rs['isFree']['yesterday'][$k]['value'];
                $feChart['data'][$k]['data']    = [
                    $rs['isFree']['all'][$k]['value'],
                    $rs['isFree']['today'][$k]['value'],
                    $rs['isFree']['yesterday'][$k]['value']
                ];
            }
        }
        $this->assign('isFree', $isFree);
        $this->assign('winning', $winning);
        $this->assign('fechart', $feChart);
        $this->assign('echart', $eChart);
        $this->assign('rs', $rs);
        $this->display();
	}

    private static function getNum($num)
    {
        $r = 200;   //半径
        $c = 2 * $r * pi(); //周长
        return number_formats($c / $num, 1);
	}

    public function createPic()
    {
        header("Content-type: image/png");
        $num= I('get.num', 8, 'int');
        $im = imagecreatetruecolor(400, 400);
        $background_color = imagecolorallocate($im, 255, 255, 255);
        $b = imagecolorallocate($im, 0, 0, 0);
        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagearc($im, 200, 200, 400, 400, 400, 400,$background_color);
        //imagestring($im, 2, 5, 5,  "A Simple Text String", $text_color);
        $font = THINK_PATH.'Library/Think/Verify/ttfs/5.ttf';
        $startDeg = 90;
        $next = 360 / $num;
        for ($i=1;$i<=$num;$i++) {
            //$text = iconv("GB2312", "UTF-8", '第'. 1);
            $text = 1;
            imagettftext($im, 14, 90, 200 + ($i * 25), 100 + ($i * 25), $text_color, $font, $text);
        }
        imagepng($im);
        imagedestroy($im);
	}

    /**
     * subject: 添加或操作奖品
     * api: post
     * author: Mercury
     * day: 2017-04-27 22:04
     * [字段名,类型,是否必传,说明]
     */
    public function post()
    {
        if (IS_POST) {
            $data = I('post.');
            $model= D('Luckdraw1Prize');
            if ($model->create($data) == false) $this->ajaxReturn(['status' => 'warning', 'msg' => $model->getError()]);
            if ($data['id'] > 0 && !empty($data['id'])) {


                //如果没有未中奖奖品则不允许修改
                if ($model->where(['luckdraw_id' => $data['luckdraw_id'], 'type_id' => 4])->find() == false && $data['type_id'] != 4) {
                    $this->ajaxReturn(['status' =>  'warning', 'msg' => '请先设置未中奖奖品']);
                }
                /**
                 * 如果有更改奖品类型 则判断更改后的奖品是否与更改前的奖品是一样，如果是一样的话则不可提交
                 */
                //if ($tmp['type_id'] != $data['type_id'] && $data['value'] == $tmp['value'] && !empty($tmp['value']) && $data['type_id'] != 4) $this->ajaxReturn(['status' => 'warning', 'msg' => '更改奖品类型后奖品不能为空']);
                $flag = $model->save($data);
            } else {
                $flag = $model->add($data);
            }
            if ($flag) $this->ajaxReturn(['status' => 'success', 'msg' => '操作成功']);
            $this->ajaxReturn(['status' =>  'warning', 'msg' => '操作失败']);
        }
	}

    /**
     * @return string
     */
    public function getPrize()
    {
        $sort = I('get.sort');
        $field= 'type_id,value,images,express_price,name,id';
        $list = D('Luckdraw1prizelist235View')->where(['status' => 1])->field($field)->select();
        $this->assign('rs', $list);
        $this->assign('sort', $sort);
        $this->display();
    }

    /**
     * subject: 获取代金券
     * api: getCoupons
     * author: Mercury
     * day: 2017-05-15 9:20
     * [字段名,类型,是否必传,说明]
     */
    public function getCoupons()
    {
        $sort = I('get.sort');
        $map = [
            'face_type' =>  1,
            'channel'   =>  2,
            'type'      =>  2,
            //'use_type'  =>  1,
            'eday'      =>  ['gt', date('Y-m-d')],  //过期时间当前当前时间
        ];
        $map['_string'] = 'num>get_num';
        $list = M('coupon_batch')->where($map)->order('id desc')->select();
        $this->assign('sort', $sort);
        $this->assign('rs', $list);
        $this->display();
    }

    /**
     * subject: 获取优惠券
     * api: getCoupon
     * author: Mercury
     * day: 2017-05-15 10:53
     * [字段名,类型,是否必传,说明]
     */
    public function getCoupon()
    {
        $sort = I('get.sort');

        $map = [
            'luckdraw_id'   =>  I('get.id'),
            'status'        =>  2,
        ];

        $coupons = M('luckdraw1_apply')->where($map)->order('id desc')->getField('id,coupons', true);
        $ids     = '';
        if ($coupons) {
            foreach ($coupons as $v) {
                $v = unserialize($v);
                foreach ($v as $val) {
                    $ids .= $val['cid'] . ',';
                }
            }
        }
        if ($ids) {
            $cMap = [
                'id'        =>  ['in', trim($ids, ',')],
                'get_num'   =>  ['lt', C('CFG.luckdraw')['luckdraw_coupon_num']],
            ];
            $list = M('coupon_batch')->where($cMap)->order('id desc')->select();
            foreach ($list as &$v) {
                if ($v['shop_id']) $v['shop_name'] = M('shop')->cache(true)->where(['id' => $v['shop_id']])->getField('shop_name');
            }
        }
        $this->assign('sort', $sort);
        $this->assign('rs', $list);
        $this->display();
    }
}