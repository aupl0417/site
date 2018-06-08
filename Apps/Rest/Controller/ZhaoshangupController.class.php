<?php
/**
+----------------------------------------------------------------------
| RestFull API
+----------------------------------------------------------------------
| 店铺升级
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class ZhaoshangupController extends CommonController {
    protected $info;    //申请资料
    public function _initialize() {
        parent::_initialize();

        if($this->uid) {
            $this->info = M('zhaoshang_upgrade')->where(['uid' => $this->uid, 'status' => ['neq', 5]])->field('atime,etime,ip',true)->order('etime DESC')->find();
            if(!empty($this->info['brand'])) $this->info['brand'] = unserialize(html_entity_decode($this->info['brand']));
            if(!empty($this->info['brand_cred'])) $this->info['brand_cred'] = unserialize(html_entity_decode($this->info['brand_cred']));
            if(!empty($this->info['industry_cred'])) $this->info['industry_cred'] = unserialize(html_entity_decode($this->info['industry_cred']));
        }

        $action = ACTION_NAME;
        if(!in_array('_'.$action,get_class_methods($this))) $this->apiReturn(1501);  //请求的方法不存在

        $this->_api(['method' => $action]);
    }


    /**
     * 各方法的必签字段
     * @param string $method     方法
     */
    public function _sign_field($method){
        $sign_field = [
            '_about'                => array('require_check' => false),    //招商介绍
            '_category'             => array('require_check' => false),    //招商类目
            '_brand'                => array('require_check' => false,'not_sign' => 'category_id'),    //入驻品牌
            '_brand_lib'            => array('require_check' => false,'not_sign' => 'category_id'),    //招商品牌
            '_shop_type'            => array('require_check' => false),    //店铺类型
            '_cred_view'            => 'category_id',    //类目资质要求
            '_shop_type'            => '',   //店铺类型
            '_shop_type_set'        => 'openid,data', //保存店铺类型、品牌、类目
            '_info'                 => 'openid', //申请资料
            '_get_brand_cred'       => 'shop_type_id,data',  //品牌资质条件
            '_brand_save'           => 'openid,data',   //保存品牌信息
            '_get_industry_cred'    => 'shop_type_id,second_category',  //行业资质条件
            '_industry_save'        => 'openid,data',   //保存行业资质信息
            '_shop_info_save'       => 'openid,shop_name,about,inventory_type,province,city,district,street,linkname,mobile,qq,email',  //保存店铺信息
            '_last_logs'            => 'openid',  //取最新一条拒绝的审核记录
        ];

        $result=$sign_field[$method];
        return $result;
    }


    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
     * 招商介绍
     */
    public function _about(){
        $rs = M('zhaoshang')->cache(true)->field('atime,etime,ip',true)->find();
        if($rs) return array('code' => 1,'data' => $rs);

        return array('code' => 3);
    }



    /**
     * 招商类目
     */
    public function _category(){
        $param = [
            'table'     => 'goods_category',
            'sql'       => 'status=1',
            'level'     => 2,
            'field'     => 'id,sid,category_name',
            'cache_name'=> 'goods_category_level2',
            'noid'      => '100845890,100845542',
            'map'       => [1 => ['is_zhaoshang' => 1]],
        ];
        $list = get_category($param);

        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 已入驻品牌
     * @param int $_POST['category_id'] 招商类目ID，选填
     */
    public function _brand(){
        $map['status']  = 1;
        if(I('post.category_id')) $map['category_id']   = I('post.category_id');

        $list = M('brand')->distinct(true)->where($map)->field('id,b_name,b_logo,shop_id')->limit(1000)->select();

        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 招商品牌库
     * @param int $_POST['category_id'] 招商类目ID，选填
     */
    public function _brand_lib(){
        $map['status']  = 1;
        if(I('post.category_id')) $map['_string']   = 'find_in_set ('.I('post.category_id').',category_id)';

        $list = M('zhaoshang_brand')->where($map)->field('atime,etime,ip',true)->select();

        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 店铺类型
     */
    public function _shop_type(){
        $list = M('shop_type')->cache(false)->where(['status' => 1])->field('atime,etime',true)->order('id asc')->select();
        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 类目资质详情
     */
    public function _cred_view(){
        //店铺类别
    	$shop_type = ['cred_flagship','cred_franchised','cred_exclusive','cred_personal','cred_individual'];

        //资质类别
        $category['cred'] = array(
            //array('id' => 1,'name' => '公司资质'),
            array('id' => 4,'name' => '会员资质'),
            array('id' => 2,'name' => '品牌资质'),
            array('id' => 3,'name' => '行业资质'),
        );

        $rs = M('goods_category')->cache(true)->where(['id' => I('post.category_id')])->field('atime,etime,ip',true)->find();
        if($rs){
            $rs['nav'] = nav_sort(array('table' => 'goods_category','field' => 'id,sid,category_name','id' => I('post.category_id'),'key' => 'category_name','cache_name' => 'goods_category_nav_'.I('post.category_id')));

            foreach($shop_type as $val){
                if($rs[$val]){
                    $tmp = unserialize(html_entity_decode($rs[$val]));
                    $item = $category;
                    $item['options']    = $tmp;
                    foreach ($category['cred'] as $k => $v){
                        switch($v['id']){
                            case 4:
                                $item['cred'][$k]['dlist'] = M('zhaoshang_cred')->where(['status' => 1,'type' => 4,'id' => ['in',$tmp['is_select']]])->field('atime,etime,ip',true)->select();
                                break;
                            case 2:
                                $item['cred'][$k]['dlist'] = M('zhaoshang_cred')->where(['status' => 1,'type' => 2,'id' => ['in',$tmp['is_select']]])->field('atime,etime,ip',true)->select();
                                break;
                            case 3:
                                $item['cred'][$k]['dlist'] = M('zhaoshang_cred')->where(['status' => 1,'type' => 3,'id' => ['in',$tmp['is_select']]])->field('atime,etime,ip',true)->select();
                                break;
                        }
                    }

                    $rs[$val]   = $item;
                }
            }

            //$this->apiReturn(1,['data' => $rs]);
            return ['code' => 1,'data' => $rs];
        }else return['code' => 3];
    }

    /**
     * 店铺类型
     */
    public function shop_type(){
        //暂停个人店铺及自营店
        $list=M('shop_type')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => ['not in','1'], 'status' => 1])->field('atime,etime',true)->select();

        if($list) return ['code' => 1,'data' => $list];
        return ['code' => 3];

    }

    /**
     * 申请记录详情
     */
    public function _info(){
        $rs = M('zhaoshang_upgrade')->where(['uid' => $this->uid])->field('atime,etime,ip',true)->find();
        if($rs){
            $shop_type = $this->cache_table('shop_type');
            $rs['first_category']   = explode(',',$rs['first_category']);
            $rs['second_category']  = explode(',',$rs['second_category']);
            if($rs['brand']) $rs['brand'] = unserialize(html_entity_decode($rs['brand']));
            $rs['shop_type_name']   = $shop_type[$rs['shop_type_id']];
            return ['code' => 1,'data' => $rs];
        }else return ['code' => 3];
    }

    /**
     * 取品品牌资质条件
     * @param string $_POST['data'] 序列化后的数据
     * @param int   $_POST['shop_type_id'] 店铺类型ID
     */
    public function _get_brand_cred(){
        $data = unserialize(html_entity_decode(I('post.data')));

        $fields[2]  = 'cred_flagship';
        $fields[3]  = 'cred_exclusive';
        $fields[4]  = 'cred_franchised';
        $fields[6]  = 'cred_personal';
        $fields[7]  = 'cred_individual';
        $field      = $fields[I('post.shop_type_id')];
        $field_list = 'id,category_name,'.$field;

        foreach($data as $kt => $item){
            $list = M('goods_category')->cache(true)->where(['id' => ['in',$item['second_category']]])->getField($field_list,true);
            $is_select  = array();
            $is_need    = array();
            foreach($list as $key => $val){
                if($val[$field]) {
                    $tmp = unserialize(html_entity_decode($val[$field]));
                    if($tmp['is_select']) $is_select = array_merge($is_select,$tmp['is_select']);
                    if($tmp['is_need']) $is_need = array_merge($is_need,$tmp['is_need']);
                    $list[$key][$field] = $tmp;
                }
            }

            $is_select  = array_unique($is_select);
            $is_need    = array_unique($is_need);

            //dump($list);
            if($is_select) {
                $cred = M('zhaoshang_cred')->cache(true)->where(['type' => 2, 'status' => 1, 'id' => ['in', $is_select]])->getField('id,category_id,cred_name,images,about', true);

                foreach ($cred as $key => $val) {
                    foreach ($list as $v) {
                        if (in_array($key, $v[$field]['is_select'])) {
                            $cred[$key]['category_name'][] = $v['category_name'];
                        }
                        if (in_array($key, $v[$field]['is_need'])) {
                            $cred[$key]['is_need']++;
                        }
                    }
                }

                $data[$kt]['cred'] = $cred;
            }

            //dump($cred);

        }

        //dump($data);
        //$ids = array_unique($ids);
        //dump($ids);

        if($data) return ['code' => 1,'data' => $data];
        else return ['code' =>3];

    }

    /**
     * 保存店铺类型、品牌、经营类目
     * step 1
     * @param string $_POST['data'] 序列化后的数据
     */
    public function _shop_type_set(){
        $data = unserialize(html_entity_decode(I('post.data')));
        if(empty($data['shop_type_id'])) return ['code' => 1900];   //未选择店铺类型！
        
        $first_category     = array();  //一级类目
        $second_category    = array();  //二级类目
        $brand = array();
        
        foreach($data['first_category'] as $key => $val){
            if($val) $first_category = array_merge($first_category,explode(',',$val));
            if($data['second_category'][$key]) $second_category = array_merge($second_category,explode(',',$data['second_category'][$key]));

            if($data['brand_name'][$key]){
                $brand[] = [
                    'name'              => $data['brand_name'][$key],
                    'first_category'    => $val,
                    'second_category'   => $data['second_category'][$key],
                ];

                //当为旗舰店是，检测该品牌是否已有用户申请了旗舰店
                if($data['shop_type_id'] == 2 && M('brand')->where(['b_name' => $data['brand_name'][$key],'_string' => 'uid in (select uid from '.C('DB_PREFIX').'shop where type_id=2)'])->getField('id')){
                    return ['code' => 1908];
                }
            }
        }
        //dump($data);
        $first_category     = array_unique($first_category);
        $second_category    = array_unique($second_category);

        if(empty($second_category)) return ['code' => 1901]; //未选择经营类目！

        //验证类目数量
        $shop_type = M('shop_type')->cache(true)->where(['id' => $data['shop_type_id']])->field('id,max_brand,max_category,max_second_category')->find();


        if(count($first_category) > $shop_type['max_category']) { //一级类目不可超过{n}个！
            return ['code' => 1902,'msg' => str_replace('{n}',$shop_type['max_category'],C('error_code.1902'))];
        }
        if(count($second_category) > $shop_type['max_second_category']) { //二级类目不可超过{n}个！
            return ['code' => 1903,'msg' => str_replace('{n}',$shop_type['max_second_category'],C('error_code.1903'))];
        }
        //非个人店铺
        if($data['shop_type_id'] != 6 && $data['shop_type_id'] != 7){
            if(empty($brand)) return ['code' => 1905]; //未输入品牌！
            if(count($brand) > $shop_type['max_brand']) { //品牌数量不可超过{n}个！
                return ['code' => 1904,'msg' => str_replace('{n}',$shop_type['max_brand'],C('error_code.1904'))];
            }
        }

        //dump($this->info['brand_cred']);
        if(empty($brand)) $vdata['brand_cred']  = '';
        elseif(!empty($this->info['brand_cred'])){  //检查品牌是否有变更
            $i=0;
            foreach($this->info['brand_cred'] as $key => $val){
                if(empty($brand[$i])){
                    unset($this->info['brand_cred'][$key]);
                }else {
                    $this->info['brand_cred'][$key]['name']   = $brand[$i]['name'];
                }
                $i++;
            }

            $vdata['brand_cred'] = empty($this->info['brand_cred']) ? '' : serialize($this->info['brand_cred']);
        }


        $vdata['uid']               = $this->uid;
        $vdata['shop_type_id']      = $data['shop_type_id'];
        $vdata['first_category']    = implode(',',$first_category);
        $vdata['second_category']   = implode(',',$second_category);
        $vdata['brand']             = empty($brand) ? '' : serialize($brand);
        if($this->info['step'] < 1){
        	$vdata['step']   = 1;
        }

        //print_r($vdata);
        if($id = M('zhaoshang_upgrade')->where(['uid' => $this->uid, 'status' => ['neq', 5]])->order('etime DESC')->getField('id')){
        	if (false !== M('zhaoshang_upgrade')->where(['id' => $id])->save($vdata)){
        		return ['code' => 1,'data' => ['brand_num' => count($brand)]];
        	}else {
        		return ['code' => 0];
        	}
        }elseif(M('zhaoshang_upgrade')->add($vdata)){
        	return ['code' => 1,'data' => ['brand_num' => count($brand)]];
        }else return ['code' => 0];

    }

    /**
     * 保存品牌信息
     * @param string $_POST['data'] 序列化后的品牌信息
     */
    public function _brand_save(){
        if($this->info['step'] < 1) return ['code' => 1906]; //请按流程提交申请资料！

        $data['brand_cred'] = I('post.data');
        if($this->info['step'] < 2) $data['step']   = 2;

        if(false !== M('zhaoshang_upgrade')->where(['uid' => $this->uid, 'id'=>$this->info['id']])->save($data)){
            return ['code' => 1];
        }else return ['code' => 0];
    }


    /**
     * 行业资质
     */
    public function _get_industry_cred(){
        $fields[2]  = 'cred_flagship';
        $fields[3]  = 'cred_exclusive';
        $fields[4]  = 'cred_franchised';
        $fields[6]  = 'cred_personal';
        $fields[7]  = 'cred_individual';
        $field      = $fields[I('post.shop_type_id')];
        $field_list = 'id,category_name,'.$field;

        $list = M('goods_category')->cache(true)->where(['id' => ['in',I('post.second_category')]])->getField($field_list,true);
        $is_select  = array();
        $is_need    = array();
        foreach($list as $key => $val){
            if($val[$field]) {
                $tmp = unserialize(html_entity_decode($val[$field]));
                if($tmp['is_select']) $is_select = array_merge($is_select,$tmp['is_select']);
                if($tmp['is_need']) $is_need = array_merge($is_need,$tmp['is_need']);
                $list[$key][$field] = $tmp;
            }
        }

        $is_select  = array_unique($is_select);
        $is_need    = array_unique($is_need);

        //dump($list);
        if($is_select) {
            $cred = M('zhaoshang_cred')->cache(true)->where(['type' => 3, 'status' => 1, 'id' => ['in', $is_select]])->getField('id,category_id,cred_name,images,about', true);
            foreach ($cred as $key => $val) {
                foreach ($list as $v) {
                    if (in_array($key, $v[$field]['is_select'])) {
                        $cred[$key]['category_name'][] = $v['category_name'];
                    }
                    if (in_array($key, $v[$field]['is_need'])) {
                        $cred[$key]['is_need']++;
                    }
                }
            }
        }
        //dump($cred);

        if($cred) return ['code' => 1,'data' => $cred];
        else return ['code' => 3];
    }

    /**
     * 保存品牌信息
     * @param string $_POST['data'] 序列化后的品牌信息
     */
    public function _industry_save(){
    	if($this->info['step'] < 2 && $this->info['shop_type_id'] != 6 && $this->info['shop_type_id'] != 7) return ['code' => 1906]; //请按流程提交申请资料！

        $data['industry_cred'] = I('post.data');
        if($this->info['step'] < 3) $data['step']   = 3;

        if(false !== M('zhaoshang_upgrade')->where(['uid' => $this->uid, 'id'=>$this->info['id']])->save($data)){
            return ['code' => 1];
        }else return ['code' => 0];
    }

    /**
     * 保存店铺信息
     * openid,shop_name,about,inventory_type,province,city,district,street,linkname,mobile,qq,email
     * @param string $_POST['openid']       用户openid
     * @param string $_POST['shop_name']    店铺名称
     * @param string $_POST['shop_logo']    店铺logo
     * @param string $_POST['about']        店铺介绍
     * @param int   $_POST['inventory_type'] 分账模式，0=扣货款,1=库存积分分发
     * @param int   $_POST['province']      省份ID
     * @param int   $_POST['city']          城市ID
     * @param int   $_POST['district']      区县ID
     * @param int   $_POST['town']          镇、街道ID
     * @param string $_POST['street']       详细地址
     * @param string $_POST['linkname']     店铺负责人
     * @parma string $_POST['mobile']       手机
     * @param string $_POST['qq']           qq
     * @param string $_POST['email']        邮箱
     */

    public function _shop_info_save(){
    	if($this->info['step'] < 3 && $this->info['shop_type_id'] != 6 && $this->info['shop_type_id'] != 7) return ['code' => 1906]; //请按流程提交申请资料！

        $_POST['shop_name'] = trimall(I('post.shop_name'));

        if(in_array($this->info['shop_type_id'],[2,3]) && !strstr($_POST['shop_name'],trimall($this->info['brand'][0]['name']))){ //旗舰店/专卖店命名规则验证
            return ['code' => 1907];
        }

        //验证店铺名称
        $res = A('OpenShop')->_check_shop_name($_POST['shop_name'],$this->uid);
        if($res['code'] != 1) return $res;

        $do = D('Common/ZhaoshangUpdateShopInfo');
        $data = I('post.');
        $data['status'] = 1;
        $data['inventory_type'] = $data['inventory_type'] ? $data['inventory_type'] : 0;
        if($this->info['step'] < 4) $data['step']   = 4;

        if(!$do->create($data)) return ['code' => 4,'msg' => $do->getError()];

        if(false !== $do->where(['uid' => $this->uid, 'id'=>$this->info['id']])->save($data)){
            return ['code' => 1];
        }else return ['code' => 0];

    }

    /**
     * 最新的一条拒绝的审核记录
     */
    public function _last_logs(){
        $rs = M('zhaoshang_upgrade_logs')->where(['status' => ['in','2,4'],'zhaoshang_upgrade_id' => $this->info['id']])->field('id,content,remark')->order('id desc')->find();
        if($rs){
            if($rs['content']) $rs['content'] = unserialize(html_entity_decode($rs['content']));
            return ['code' => 1,'data' => $rs];
        }else return ['code' => 3];
    }
}
