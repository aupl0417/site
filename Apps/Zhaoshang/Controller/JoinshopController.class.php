<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class JoinshopController extends InitController {
    protected $info;
    public function _initialize()
    {
        parent::_initialize();
        $this->check_login();

        //dump(session());
        /*  可能问题，location.href跳转时读不到数据
        $res = $this->doApi('/Zhaoshang/info',['openid' => session('user.openid')]);
        if($res->code == 1) $this->info = $res->data;
        */

        //代替上面接口
        $rs = M('zhaoshang_join')->where(['uid' => session('user.id')])->field('atime,etime,ip',true)->find();
        if($rs){
            $shop_type = $this->cache_table('shop_type');
            $rs['first_category']   = explode(',',$rs['first_category']);
            $rs['second_category']  = explode(',',$rs['second_category']);
            if($rs['brand']) $rs['brand'] = unserialize(html_entity_decode($rs['brand']));
            $rs['shop_type_name']   = $shop_type[$rs['shop_type_id']];
            $this->info = json_decode(json_encode($rs));
            $this->assign('info',$this->info);

        }


        C('seo',['title' => '入驻申请 - 招商频道']);
    }

    public function index(){
        $this->check_status();
        //是否已提交过资料
        if($this->info->status == 2 || ($this->info->status == 0 && $this->info->step > 0)){
            redirect(__CONTROLLER__.'/audit');
            exit();
        }

        $res = $this->doApi('/Erp/user_info',['openid' => session('user.openid')]);
        $this->assign('rs',$res->data);

        if($this->check_auth($res)) $status = 1;
        $this->assign('status',$status);

		$this->display();
    }

    /**
     * 阅读合同
     */
    public function agreement(){
        $res = $this->doApi('/Zhaoshang/agreement');

        $this->assign('rs',$res->data);
        $this->display();
    }

    /**
     * 选择店铺类型
     */

    public function shop_type(){
        $this->check_status();
        $res = $this->doApi('/Erp/user_info',['openid' => session('user.openid')]);
        $this->check_auth($res);
        $this->assign('rs',$res->data);

        $res = $this->doApi('/Zhaoshang/shop_type');
        $this->assign('shop_type',$res->data);

        //修改
        if($this->info){
            $this->display('shop_type_edit');
            exit;
        }

        $this->display();
    }

    /**
     * 保存店铺类型、品牌、经营类目
     * step 1
     */
    public function shop_type_set(){
        //C('DEBUG_API',true);
        $res = $this->doApi('/Zhaoshang/shop_type_set',['openid' => session('user.openid'),'data' => serialize(I('post.')),'is_edit' => I('post.is_edit')],'is_edit');

        $this->ajaxReturn($res);
    }

    /**
     * 填写品牌资料
     */
    public function brand(){
        $this->check_status();
        //根据类目获取品牌资质
        //C('DEBUG_API',true);
        if($this->info->step < 1) redirect(__CONTROLLER__.'/shop_type');

        if($this->info->brand){
            $res = $this->doApi('/Zhaoshang/get_brand_cred',['shop_type_id' => $this->info->shop_type_id,'data' => serialize(objectToArray($this->info->brand))]);
            if($res->code == 1){
                foreach($res->data as $key => $val){
                    if($val->cred){
                        $res->data[$key]->cred = json_decode(json_encode($val->cred), true);
                    }
                }
                $this->info->brand = $res->data;
                $this->assign('info',$this->info);
            }
        }else{ //无品牌时直接跳至下一流程
        	if (I('get.ps')==1){
        		redirect(__CONTROLLER__.'/shop_type');
        	}else {
        		redirect(__CONTROLLER__.'/industry');
        	}
        }

        //修改操作
        if($this->info->brand_cred){
            if($this->info->brand_cred) {
                $tmp = unserialize(html_entity_decode($this->info->brand_cred));
                foreach ($tmp as $i => $val){
                    foreach($val as $key => $v){
                        if($key == 'cred'){
                            $brand[$i][$key] = $v;
                        }else $brand[$i][$key.'_'.$i] = $v;
                    }
                }
                $this->assign('brand',$brand);
            }
            $this->display('brand_edit');
            exit;
        }

        $this->display();
    }

    /**
     * 保存品牌资料
     */
    public function brand_save(){
        if($this->info->step < 1) $this->ajaxReturn(['code' => 0,'msg' => '请按流程步骤提交资料！']);
        //数据格式化
        $data = array();
        foreach(I('post.') as $key => $val){
            if($key != 'is_edit') {
                $key = explode('_', $key);
                if ($key[0] == 'cred') {
                    $data[end($key)][$key[0]][$key[1]] = $val;
                } elseif ($key[0] == 'is') {
                    $data[end($key)][$key[0] . '_' . $key[1]] = $val;
                } else {
                    $data[end($key)][$key[0]] = $val;
                }
            }
        }

        $res = $this->doApi('/Zhaoshang/brand_save',['openid' => session('user.openid'),'data' => serialize($data),'is_edit' => I('post.is_edit')],'is_edit');

        $this->ajaxReturn($res);

    }

    /**
     * 行业资质
     */
    public function industry(){
        $this->check_status();
        if($this->info->step < 2 && $this->info->shop_type_id != 6 && $this->info->shop_type_id != 7) redirect(__CONTROLLER__.'/brand');
        //取行业资质条件
        //C('DEBUG_API',true);
        $res = $this->doApi('/Zhaoshang/get_industry_cred',['shop_type_id' => $this->info->shop_type_id,'second_category' => implode(',',$this->info->second_category)]);
        if($res->code == 3){    //当无行业资质时直接跳到店铺设置
            M('zhaoshang_join')->where(['id' => $this->info->id])->save(['step' => 3]);
            redirect(__CONTROLLER__.'/shop_info');
            exit();
        }
        
        $this->assign('cred',json_decode(json_encode($res->data), true));

        //修改操作
        if($this->info->industry_cred){
            if($this->info->industry_cred) {
                $industry = unserialize(html_entity_decode($this->info->industry_cred));

                $this->assign('industry',$industry);
                //dump($industry);
            }
            $this->display('industry_edit');
            exit;
        }

        $this->display();
    }

    /**
     * 保存行业资质
     */
    public function industry_save(){
    	if($this->info->step < 2 && $this->info->shop_type_id != 6 && $this->info->shop_type_id != 7) $this->ajaxReturn(['code' => 0,'msg' => '请按流程步骤提交资料！']);
        //数据格式化
        $data = array();
        foreach(I('post.') as $key => $val){
            if($key != 'is_edit') {
                $key = explode('_', $key);
                $data[$key[1]] = $val;
            }
        }

        $res = $this->doApi('/Zhaoshang/industry_save',['openid' => session('user.openid'),'data' => serialize($data),'is_edit' => I('post.is_edit')],'is_edit');

        $this->ajaxReturn($res);
    }

    public function city(){
        $res = $this->doApi('/Tools/city',['sid' => I('get.sid')],'sid');
        $this->assign('city',$res->data);
        $this->display();
    }

    public function get_city(){
        $res = $this->doApi('/Tools/city',['sid' => I('get.sid')],'sid');
        $this->ajaxReturn($res);
    }

    public function shop_info(){
        $this->check_status();
        if($this->info->step < 3 && $this->info->shop_type_id != 6) redirect(__CONTROLLER__.'/industry');

        //修改操作
        if($this->info->shop_name){
            $rs = objectToArray($this->info);

            $area = $this->cache_table('area');
            $rs['select_city'] = $area[$rs['province']].' > '.$area[$rs['city']].' > '.$area[$rs['district']].' > '.$area[$rs['town']];

            $this->assign('rs',$rs);
            $this->display('shop_info_edit');
            exit;
        }

        $this->display();
    }

    /**
     * 保存店铺信息
     */
    public function shop_info_save(){
        if($this->info->step < 3 && $this->info->shop_type_id != 6) $this->ajaxReturn(['code' => 0,'msg' => '请按流程步骤提交资料！']);

        $field = 'shop_name,shop_logo,about,inventory_type,province,city,district,street,town,linkname,mobile,tel,qq,email,is_edit';
        $field = explode(',',$field);

        $data['openid']  = session('user.openid');
        foreach($field as $val){
            $data[$val] = I('post.'.$val);
        }

        $res = $this->doApi('/Zhaoshang/shop_info_save',$data,'tel,shop_logo,town,is_edit');

        $this->ajaxReturn($res);

    }

    /**
     * 审核状态
     */
    public function audit(){
        if($this->info->status == 5){
            $res = $this->doApi('/ShopSetting/shop_info',['openid' => session('user.openid')]);
            $this->assign('rs',$res->data);
        }elseif(in_array($this->info->status,array(2,4))){
            $res = $this->doApi('/Zhaoshang/last_logs',['openid' => session('user.openid')],'',1);

            $this->assign('logs',$res['data']);
        }
        $this->display();
    }


    /**
     * 编辑权限
     */
    public function check_status(){
        if(in_array($this->info->status,array(1,3,4,5))){
            redirect(__CONTROLLER__.'/audit');
            exit();
        }
    }
}