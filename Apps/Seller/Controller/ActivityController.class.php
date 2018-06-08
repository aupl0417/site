<?php
namespace Seller\Controller;
use Common\Form\Form;

class ActivityController extends AuthController {
    public function _initialize() {
        parent::_initialize();
        if (session('user.shop_type') == 6) {   //如果为个人店的话则执行跳转
            redirect(DM('zhaoshang', '/shopup'));
        }
    }
    
    /**
     * 活动管理
     */
    public function index() {
        $sArr   =   [0,1,2,3];
        $map    =   [
            'shop_id'   =>  $this->_map['id'],
            'uid'       =>  getUid(),
        ];
        if (isset($_GET['sid']) && in_array(I('get.sid'), $sArr)) {
            $map['status']  =   I('get.sid');
        }
        $data   =   pagelist([
            'table'     =>  'ActivityView',
            'do'        =>  'D',
            'pagesize'  =>  15,
            'order'     =>  'id desc',
            'fields'    =>  'activity_name,icon,id,start_time,end_time,status,tyep_id,full_money,atime,type_id,full_value',
            'map'       =>  $map,
        ]);
        $this->assign('data', $data);
        C('seo', ['title' => '活动管理']);
        $this->display();
    }
    
    /**
     * 活动类型
     */
    public function type() {
        $this->assign('data', getActivityType());
        C('seo', ['title' => '能够使用的活动类型']);
		$this->seo(['title' => '能够使用的活动类型']);
        $this->display();
    }
    
    /**
     * 创建活动html
     */
    public function create() {
        $data['type_id']    =   intval(I('get.type'));
        $pageTitle = '创建促销';
        if (isset($_GET['id']) && !empty(I('get.id'))) {
            $data   =   M('activity')->where(['id' => I('get.id'), 'shop_id' => $this->_map['id']])->find();
            if ($data['sku_num'] == 0) {
                unset($data['sku_num']);
            }
            if ($data['max_num'] == 0) {
                unset($data['max_num']);
            }
            if (!$data) {
                $this->display(T('Home@Empty:404'));
                exit;
            }
            if ($data['highest'] < 0.01) unset($data['highest']);
            $pageTitle = '修改促销';
            $this->assign('pageTitle', $pageTitle);
            $this->assign('data', $data);
        }
        $type   =   getActivityType($data['type_id']);
        if ($type) {
            switch ($data['type_id']) {
                case 1:
                    $title  =   '满包邮';
                    $fullMoneyTitle = '最低消费';
                    $fullMoneyValidate = ['required', 'min' => 0.1, 'number'];
                    break;
                case 2:
                    $title  =   '选礼品';
                    $fullMoneyTitle = '最低消费';
                    $fullMoneyValidate = ['required', 'min' => 0.1, 'number'];
                    $fullValueValidate = ['required', 'min' => 0.1, 'number'];
                    break;
                case 3:
                    $title  =   '优惠金额';
                    $fullMoneyTitle = '最低消费';
                    $fullMoneyValidate = ['required', 'min' => 0.1, 'number'];
                    $fullValueValidate = ['required', 'min' => 0.1, 'number'];
                    $highestValidate   = ['number', 'min' => 0.1];
                    break;
                case 4:
                    $title  =   '优惠折扣';
                    $fullValueValidate = ['required', 'min' => 0.1, 'number', 'max' => 9.9];
                    break;
                case 5:
                    $title  =   '0元购';
                    $skuNumValidate = ['digits', 'min' => 1];
                    $maxNumValidate = ['digits', 'min' => 1];
                    break;
                case 6:
                    $title  =   '商品秒杀价';
                    $fullMoneyTitle = '秒杀金额';
                    $fullMoneyValidate = ['required', 'min' => 0.1, 'number'];
                    $skuNumValidate = ['digits', 'min' => 1];
                    $maxNumValidate = ['digits', 'min' => 1];
                    break;
                case 7:
                    $title  =   '消费金额可升级';
                    break;
            }
            //取出商品
            if (!empty($data['goods'])) {
                $goods['goods']    =   M('goods')->cache(true)->where(['id' => ['in', $data['goods'], 'shop_id' => $this->_map['id']]])->order('id asc')->field('id,images')->select();
                $goods['goodsIds'] =   $data['goods'];
                $goods['aid']      =   $data['id'];
                $data['goods']     =   $goods;
            }
            //取出赠品、0元购、秒杀等活动商品
            if (in_array($data['type_id'], [2,5,6])) {
                if (!empty($data['full_value'])) {
                    $full_value['goods']    =   M('goods')->cache(true)->where(['id' => ['in', $data['full_value'], 'shop_id' => $this->_map['id']]])->order('id asc')->field('id,images')->select();
                    $full_value['goodsIds'] =   $data['full_value'];
                    $full_value['aid']      =   $data['id'];
                    $data['full_value']     =   $full_value;
                }
            }
            $this->seo(['title' => isset($_GET['id']) ? '编辑活动' : '添加活动']);
            $max    =   $type['id'] == 2 ? C('cfg.activity')['activity_get_max_num'] : C('cfg.activity')['activity_spike_max_num'];
            $this->assign('maxnum', $max);
            if (C('DEFAULT_THEME') == 'default') {
                $code   =   '$this->builderForm()->keyHtmltext(\'活动类型\', $type[\'activity_name\'])';
                $code   .=  '->keyId()->keyId(\'type_id\')';
                $code   .=  '->keyDatetime(\'start_time\', \'活动开始时间\', 1)->keyDatetime(\'end_time\', \'活动结束时间\', 1, \'结束时间不能大于开始时间90天\')';
                $minPrice = 0;
                switch ($data['type_id']) {
                    case 1://包邮
                        $code   .=  '->keyText(\'full_money\', \'需消费金额\', \'\', \'不填则不限\')';
                        break;
                    case 2://赠送礼品
                        $code   .=  '->keyText(\'full_money\', \'需消费金额\', 1, \'不填则不限\')';
                        $code   .=  '->keyGoods(\'full_value\', $title, 1)';
                        break;
                    case 3://满减
                        $code   .=  '->keyText(\'full_money\', \'需消费金额\', 1, \'需消费金额不能为空\')';
                        $code   .=  '->keyText(\'full_value\', $title, 1, $title.\'不能为空\')';
                        $code   .=  '->keyText(\'highest\', \'最高可减\', \'\', \'不填则不限，此项同累积成对使用\')';
                        $code   .=  '->keyCheckBox(\'is_accumulation\', \'是否累加\', [1=>\'累积\'], \'\', \'比如满100减10满200则减20以此类推\')';
                        break;
                    case 4://优惠折扣
                        $code   .=  '->keyText(\'full_value\', $title, 1, $title.\'介于0.1-9.9之间\')';
                        break;
                    case 5://0元购
                        $code   .=  '->keyText(\'sku_num\', \'预售数量\', \'\', \'不填则不限\')';
                        $code   .=  '->keyText(\'max_num\', \'最多可买\', \'\', \'不填则不限\')';
                        $code   .=  '->keySelect(\'is_single\', \'参与类型\', [0=>\'多件参与\',1=>\'单件参与\'])';
                        $code   .=  '->keyGoods(\'full_value\', \'参与0元购的商品\', 1, \'<span class="text_yellow">不包邮商品才能够参与此活动！</span>\')';
                        break;
                    case 6://秒杀
                        $code   .=  '->keyText(\'full_money\', \'商品秒杀价\', \'1\', \'商品秒杀价\')';
                        $code   .=  '->keyText(\'sku_num\', \'预售数量\', \'\', \'不填则不限\')';
                        $code   .=  '->keyText(\'max_num\', \'最多可买\', \'\', \'不填则不限\')';
                        $code   .=  '->keySelect(\'is_single\', \'参与类型\', [0=>\'多件参与\',1=>\'单件参与\'])';
                        $code   .=  '->keyGoods(\'full_value\', \'参与秒杀的商品\', 1)';
                        $minPrice = 0.1;
                        break;
                    case 7://可升级
                        break;
                }
                $code   .=  '->data($data)->view();';
                eval($code);
            } else {
                $typeId = $data['type_id'];
                $config = [
                    'action' => U('/activity/createSave'),
                    'gourl'  => '"' . U('/activity') . '"',
                ];
                $form = Form::getInstance($config)
                    ->hidden(['name' => 'id', 'value' => $data['id']])
                    ->hidden(['name' => 'type_id', 'value' => $typeId])
                    ->datetime(['name' => 'start_time', 'title' => '活动开始时间', 'value' => $data['start_time'], 'options' => ['format' => 'yyyy-mm-dd hh:ii'], 'require' => 1, 'validate' => ['required']])
                    ->datetime(['name' => 'end_time', 'title' => '活动结束时间', 'value' => $data['end_time'], 'options' => ['format' => 'yyyy-mm-dd hh:ii'], 'tips' => '结束时间不能大于开始时间90天', 'require' => 1, 'validate' => ['required']])
                    ->callback((in_array($typeId, [1,2,3,6]) ? true : false), ['name' => 'full_money', 'value' => $data['full_money'], 'title' => $fullMoneyTitle, 'require' => 1, 'validate' => $fullMoneyValidate])
                    ->callback((in_array($typeId, [3,4]) ? true : false), ['name' => 'full_value', 'value' => $data['full_value'], 'title' => $title, 'require' => 1, 'validate' => $fullValueValidate])
                    ->callback(($typeId == 3 ? true : false), ['name' => 'highest', 'title' => '最高可减', 'value' => $data['highest'], 'tips' => '不填则不限，此项同累积成对使用', 'validate' => $highestValidate])
                    ->callback(($typeId == 3 ? true : false), ['name' => 'is_accumulation', 'title' => '是否累加', 'value' => $data['is_accumulation'], 'callback' => 'checkbox', 'options' => [1 => '累加'], 'tips' => '比如满100减10满200则减20以此类推'])
                    ->callback((in_array($typeId, [5,6]) ? true : false), ['name' => 'sku_num', 'title' => '预售数量', 'value' => $data['sku_num'], 'tips' => '不填则不限', 'validate' => $skuNumValidate])
                    ->callback((in_array($typeId, [5,6]) ? true : false), ['name' => 'max_num', 'title' => '最多购买', 'value' => $data['max_num'], 'tips' => '不填则不限', 'validate' => $maxNumValidate])
                    ->callback((in_array($typeId, [2,5,6]) ? true : false), ['name' => 'full_value', 'value' => $data['full_value'], 'callback' => 'goods', 'require' => 1, 'title' => '选商品', 'url' => U('/goods/choose', ['maxNum' => $max, 'inputName' => 'full_value'])])
                    ->callback((in_array($typeId, [5,6]) ? true : false), ['name' => 'is_single', 'title' => '参与类型', 'value' => $data['is_single'], 'callback' => 'radio', 'options' => ['多件参与', '单件参与']])
                    ->submit(['title' => $pageTitle])
                    ->create();
                $this->assign('form', $form);
            }

            $this->assign('pageTitle', $pageTitle);
            $this->assign('title', $title);
            C('seo', ['title' => isset($_GET['id']) ? '编辑活动' : '添加活动']);
            $this->assign('minPrice', $minPrice);
            $this->display();
        } else {
            $this->redirect('/activity/type');
        }
    }
    
    /**
     * 创建活动PHP
     */
    public function createSave() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data               =   I('post.');
            $data['shop_id']    =   $this->_map['id'];
            $id                 =   !empty(intval($data['id'])) ? intval($data['id']) : 0;
            $goodsId            =   trim(I('post.goods'), ',');
            $model              =   D('Activity');
            if (empty($data['is_accumulation']) && !isset($data['is_accumulation'])) {      //累加
                $data['is_accumulation'] = 0;
            }

            if ($id > 0 && $data['start_time'] >= date('Y-m-d H:i:s', NOW_TIME)) {   //如果卖家修改促销并且促销开始时间大于当前时间则把促销改为未开始状态
                $data['status'] = 0;
            }

            if (!$data = $model->create($data)) {
                $msg    =   null;
                if (function_exists($model->getError())) {
                    $msg    =   call_user_func_array($model->getError(), [I('post.type_id')]);
                }
                $this->ajaxReturn(['code' => 0, 'msg' => !is_null($msg) ? $msg : $model->getError()]);
            }
            $model->startTrans();
            if ($id > 0) {
                $map    =   [
                    'id'        =>  $id,
                    'shop_id'   =>  $this->_map['id'],
                    //'status'    =>  0,  //未开始的活动才能够编辑
                ];
                $flag   =   $model->where($map)->save();
                if (!$flag) goto error;
            } else {
                $flag   =   $model->add();
                if (!$flag) goto error;
            }
            //取出参与活动的商品
            /*$goodsMap    =   ['shop_id' => $this->_map['id'], 'status' => 1];
            
            $clearAc    =   M('goods')->where(array_merge($goodsMap,['id' => ['notin', $goodsId]]))->where('FIND_IN_SET("'.$id.'", activity_id)')->field('id,activity_id')->select();
            if ($clearAc) {
                $tmps   =   [];
                foreach ($clearAc as $key => $val) {
                    $tmps[$key] =   explode(',', $val['activity_id']);
                    $cnt        =   count($tmps[$key]);
                    for ($i = 0; $i < $cnt; $i++) {
                        if ($id == $tmps[$key][$i]) {
                            unset($tmps[$key][$i]);
                        }
                    }
                    $ids    =   implode(',', $tmps[$key]);
                    //只有未开始的活动才能够编辑
                    if (false == M('goods')->where(['id' => $val['id']])->save(['activity_id' => $ids])) {
                        goto error;
                        break;
                    }
                }
                unset($val,$cnt,$tmps,$i,$clearAc);
            }
            
            if (!empty($goodsId)) {//未选择则为所有商品
                $goodsMap['id']  =   ['in', $goodsId];
            }
            $goodsInfo   =   M('goods')->where($goodsMap)->getField('id,activity_id');
            if (empty($goodsInfo)) goto error;
            $submit      =  true;
            if ($id > 0) {
                $map    =   [
                    'id'        =>  $id,
                    'shop_id'   =>  $this->_map['id'],
                    'status'    =>  0,  //未开始的活动才能够编辑
                ];
                $flag   =   $model->where($map)->save();
                if (!$flag) goto error;
                $acv[]       =  $id;
                
                foreach ($goodsInfo as $key => $val) {
                    $tmpVal[$key]   =   explode(',', $val);
                    $tmp            =   implode(',', array_unique(array_merge($tmpVal[$key], $acv))); //合并并除去相同值
                    $uData['activity_id']  =   $tmp;
                    if (false ===   M('goods')->where(['id' => $key, 'shop_id' => $this->_map['id'], 'status' => 1])->save($uData)) {
                        $submit =   false;
                        break;
                    }
                }
                if ($submit == false) goto error;
            } else {
                $flag   =   $model->add();
                if (!$flag) goto error;
                foreach ($goodsInfo as $key => $val) {
                    $uData['activity_id']  =   !empty($val) ? $val.','.$flag : $flag;
                    if (!$flags =   M('goods')->where(['id' => $key, 'shop_id' => $this->_map['id'], 'status' => 1])->save($uData)) {
                        $submit =   false;
                        break;
                    }
                }
                if ($submit == false) goto error;
            }*/
            $model->commit();
            $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            error:
            $model->rollback();
            $this->ajaxReturn(['code' => 0, 'msg' => '数据未更新']);
        }
    }
    
    /**
     * 活动详情
     */
    public function detail() {
        $id =   intval(I('get.id'));
        if ($id > 0) {
            $model  =   D('ActivityView');
            $data   =   $model->where(['shop_id' => $this->_map['id'], 'id' => $id])->find();
            if ($data) {
                $data['participate']    =   pagelist([
                    'table'     =>  'ActivityParticipateView',
                    'do'        =>  'D',
                    'pagesize'  =>  10,
                    'order'     =>  'id desc',
                    'map'       =>  ['activity_id' => $data['id'], 'status' => 1],
                ]);
                $inArr  =   [2,5,6];    //赠送的商品、秒杀、0元购
                if (in_array($data['type_id'], $inArr)) {    //赠送的商品、秒杀、0元购
                    foreach ($data['participate']['list'] as $k => $val) {
                        $data['participate']['list'][$k]['goods']   =   getActivityFullvalueGoods($val['full_value'], $this->_map['id']);
                    }
                    unset($val,$k);
                    $data['goods']  =   getActivityFullvalueGoods($data['full_value'], $this->_map['id']);
                }
                $this->assign('data', $data);
                C('seo', ['title' => '促销活动详情']);
				$this->seo(['title' => '促销活动详情']);
                $this->display();
            } else {
                $this->display(T('Home@Empty:404'));exit;
            }
        } else {
            $this->display(T('Home@Empty:404'));exit;
        }
    }
    
    /**
     * 删除活动
     */
    public function del() {
        if (IS_POST) {
            if (M('activity')->where(['shop_id' => $this->_map['id'], 'id' => I('post.id'), 'uid' => getUid()])->delete()) {
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '操作失败']);
        }
    }
    
    /**
     * 结束活动
     */
    public function cancel() {
        if (IS_POST) {
            $id =   I('post.id');
            $model      =   M('activity');
            $model->startTrans();
            //取出参与活动的商品
            /*$goodsMap   =   ['shop_id' => $this->_map['id']];
            $clearAc    =   M('goods')->where($goodsMap)->where('FIND_IN_SET("'.$id.'", activity_id)')->field('id,activity_id')->select();
            
            
            if ($clearAc) {
                $tmps   =   [];
                foreach ($clearAc as $key => $val) {
                    $tmps[$key] =   explode(',', $val['activity_id']);
                    $cnt        =   count($tmps[$key]);
                    for ($i = 0; $i < $cnt; $i++) {
                        if ($id == $tmps[$key][$i]) {
                            unset($tmps[$key][$i]);
                        }
                    }
                    $ids    =   implode(',', $tmps[$key]);
                    //只有未开始的活动才能够编辑
                    if (false == M('goods')->where(['id' => $val['id']])->save(['activity_id' => $ids])) {
                        goto error;
                        break;
                    }
                }
                unset($val,$cnt,$tmps,$i,$clearAc);
            }*/
            $flag   =   $model->where(['id' => $id, 'shop_id' => $this->_map['id'], 'status' => 1])->save(['status' => 3, 'cancel_time' => date('Y-m-d H:i:s', NOW_TIME)]);
            if (false == $flag) goto error;
            
            $model->commit();
            $this->ajaxReturn(['code' => 1, 'msg' => '取消成功']);
            error:
                $model->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => '取消失败']);
        }
    }
}