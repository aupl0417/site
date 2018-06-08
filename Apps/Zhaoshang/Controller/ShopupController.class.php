<?php
namespace Zhaoshang\Controller;

/**
 * libo 店铺升级 2016-11-16
 */

class ShopupController extends InitController
{

    protected $info;
    public function _initialize()
    {
        parent::_initialize();
        $user = session('user');
        # print_r($user);exit;
        
        if(empty($user)){
            redirect(C('sub_domain.user').'/login.html');
            exit();
        }elseif($user['shop_id'] > 0 && $user['shop_type'] != 1){
			
        }elseif($user['shop_id'] > 0){
            redirect(U('/Index/opened'));
        }else{
            redirect(U('/Index/index'));
        }

        $rs = M('zhaoshang_upgrade')->where(['uid' => session('user.id')])->field('atime,etime,ip',true)->order('etime DESC')->find();
        if($rs){
            $shop_type = $this->cache_table('shop_type');
            $rs['first_category']   = explode(',',$rs['first_category']);
            $rs['second_category']  = explode(',',$rs['second_category']);
            if($rs['brand']) $rs['brand'] = unserialize(html_entity_decode($rs['brand']));
            $rs['shop_type_name']   = $shop_type[$rs['shop_type_id']];
            $this->info = json_decode(json_encode($rs));
            $this->assign('info',$this->info);
        }

        C('seo',['title' => '店铺升级 - 招商频道']);

    }

    public function index(){
        $this->check_status();
        //是否已提交过资料
        if($this->info->status == 2 || ($this->info->status == 0 && $this->info->step > 0)){
            redirect(__CONTROLLER__.'/audit');
            exit;
        }
        $erp = $this->doApi('/User/userinfo',['openid' => session('user.openid')]);
        $this->assign('rs',$erp->data);
        if($this->checkup_auth($erp)) $status = 1;
        $this->assign('status',$status);

		$this->display();
    }


    /**
     * 阅读合同
     */
    public function agreement(){
        $res = $this->doApi('/Zhaoshangup/agreement');

        $this->assign('rs',$res->data);
        $this->display();
    }


    /**
     * 选择店铺类型
     */
    public function shop_type(){
        $this->check_status();
        
        # 企业用户的个人店铺才可以升级店铺
        $erp = $this->doApi('/User/userinfo',['openid' => session('user.openid')]);
        $this->checkup_auth($erp);
        
        $this->assign('rs',$erp->data);

        $res = $this->doApi('/Zhaoshangup/shop_type');
        $this->assign('shop_type',$res->data);
        
        //修改
        if($this->info){
        	$shop_type = $this->cache_table('shop_type');
        	$shop_type_id=M('shop')->where(['uid'=>session('user.id')])->getField('type_id');
        	$this->assign('shop_info',['shop_type_id'=>$shop_type_id,'shop_type_name'=>$shop_type[$shop_type_id]]);
            $this->display('shop_type_edit');
            exit;
        }else {
        	$shopinfo = $this->_info();
        	$shopinfo['sn']=count($shopinfo['brand']);
        	$shopinfo['shop_type_id']=6;
        	$this->assign('shop',$shopinfo);
        }

        $this->display();
    }
    
    /**
     * 申请记录详情
     */
    public function _info(){
    	$rs = M('zhaoshang_join')->cache(true)->where(['uid' => session('user.id')])->field('atime,etime,ip',true)->order('etime DESC')->find();
    	$shop_type = $this->cache_table('shop_type');
    	if($rs){
    		$rs['first_category_arr']   = explode(',',$rs['first_category']);
    		$rs['second_category_arr']  = explode(',',$rs['second_category']);
    		$list = M('goods_category a')->cache(true)->field('a.id,a.category_name,b.id as sid,b.category_name as scategory_name')->join('left join '.C('DB_PREFIX').'goods_category b on a.sid=b.id')->where(['a.status'=>1,'a.is_zhaoshang'=>1,'a.id'=>['in',$rs['second_category_arr']]])->select();
    		if($rs['brand']){
    			$rs['brand'] = unserialize(html_entity_decode($rs['brand']));
    			foreach ($rs['brand'] as $k=>$v){
    				$fs=explode(',', $v['second_category']);
    				foreach ($fs as $vv){
    					foreach ($list as $vvv){
    						if ($vv==$vvv['id']){
    							$rs['brand'][$k]['list'][]=$vvv;
    						}
    					}
    				}
    			}
    		}else {
    			$rs['brand'][0]['list'] = $list;
    		}
    		$rs['shop_type_name']   = $shop_type[$rs['shop_type_id']];
    		return $rs;
    	}else {
    		$shop_type_id=M('shop')->where(['uid'=>session('user.id')])->getField('type_id');
    		$rs['shop_type_id']=$shop_type_id;
    		$rs['shop_type_name']   = $shop_type[$shop_type_id];
    		return $rs;
    	}
    }

    /**
     * 保存店铺类型、品牌、经营类目
     * step 1
     */
    public function shop_type_set(){
    	$shop_type=M('shop')->where(['uid'=>session('user.id')])->getField('type_id');
    	if ($shop_type!=6 && $shop_type!=I('post.shop_type_id')){
    		$this->ajaxReturn(['code'=>0, 'msg'=>'您不能调整店铺类型']);
    	}
		
        $res = $this->doApi('/Zhaoshangup/shop_type_set',['openid' => session('user.openid'),'data' => serialize(I('post.')),'is_edit' => I('post.is_edit')],'is_edit');

        $this->ajaxReturn($res);
    }

    /**
     * 填写品牌资料
     */
    public function brand(){
        $this->check_status();
        //根据类目获取品牌资质
        if($this->info->step < 1) redirect(__CONTROLLER__.'/shop_type');

        if($this->info->brand){
            $res = $this->doApi('/Zhaoshangup/get_brand_cred',['shop_type_id' => $this->info->shop_type_id,'data' => serialize(objectToArray($this->info->brand))]);
            if($res->code == 1){
                foreach($res->data as $key => $val){
                    if($val->cred){
                    	$res->data[$key]->cred = (array)json_decode(json_encode($val->cred));
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

        $res = $this->doApi('/Zhaoshangup/brand_save',['openid' => session('user.openid'),'data' => serialize($data),'is_edit' => I('post.is_edit')],'is_edit');

        $this->ajaxReturn($res);

    }

    /**
     * 行业资质
     */
    public function industry(){
        $this->check_status();
        if($this->info->step < 2 && $this->info->shop_type_id != 6 && $this->info->shop_type_id != 7) redirect(__CONTROLLER__.'/brand');
        //取行业资质条件
        $res = $this->doApi('/Zhaoshangup/get_industry_cred',['shop_type_id' => $this->info->shop_type_id,'second_category' => implode(',',$this->info->second_category)]);
        if($res->code == 3){    //当无行业资质时直接跳到店铺设置
            M('zhaoshang_upgrade')->where(['id' => $this->info->id])->save(['step' => 3]);
            redirect(__CONTROLLER__.'/shop_info');
            exit();
        }
        $this->assign('cred',(array)$res->data);

        //修改操作
        if($this->info->industry_cred){
            if($this->info->industry_cred) {
                $industry = unserialize(html_entity_decode($this->info->industry_cred));

                $this->assign('industry',$industry);
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

        $res = $this->doApi('/Zhaoshangup/industry_save',['openid' => session('user.openid'),'data' => serialize($data),'is_edit' => I('post.is_edit')],'is_edit');

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
        if($this->info->step < 2 && !$this->info->industry_cred) redirect(__CONTROLLER__.'/industry');

        //修改操作
        if($this->info->shop_name){
            $rs = objectToArray($this->info);

            $area = $this->cache_table('area');
            $rs['select_city'] = $area[$rs['province']].' > '.$area[$rs['city']].' > '.$area[$rs['district']].' > '.$area[$rs['town']];

            $this->assign('rs',$rs);
            $this->display('shop_info_edit');
            exit;
        }else {
        	$area 	=	$this->cache_table('area');
        	$do=D('Common/ShopRelation');
        	$rs=$do->relation(false)->cache(true)->where(['id'=>session('user.shop_id')])->field('etime,ip',true)->find();
        	$rs['province_name']    =$area[$rs['province']];
        	$rs['city_name']        =$area[$rs['city']];
        	$rs['district_name']    =$area[$rs['district']];
        	$rs['town_name']        =$area[$rs['town']];
        	$size=strlen($rs['shop_name'])-strlen($this->info->shop_type_name);
        	$rs['shop_name']        =substr($rs['shop_name'],0,$size);
        	$this->assign('rsu',$rs);
        }
        
        $this->display();
    }

    /**
     * 保存店铺信息
     */
    public function shop_info_save(){
    	if($this->info->step < 3) $this->ajaxReturn(['code' => 0,'msg' => '请按流程步骤提交资料！']);

        $field = 'shop_name,shop_logo,about,inventory_type,province,city,district,street,town,linkname,mobile,tel,qq,email,is_edit';
        $field = explode(',',$field);

        $data['openid']  = session('user.openid');
        foreach($field as $val){
            $data[$val] = I('post.'.$val);
        }

        $res = $this->doApi('/Zhaoshangup/shop_info_save',$data,'tel,shop_logo,town,is_edit');

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
            $res = $this->doApi('/Zhaoshangup/last_logs',['openid' => session('user.openid')],'',1);
            $this->assign('logs',$res['data']);
        }
        $this->display();
    }


    /**
     * 编辑权限
     */
    public function check_status(){
//     	$dotime = strtotime($this->info->dotime);
//     	if ($dotime && in_array($this->info->status,array(1,3,4,5))){
//     		if ((time()-$dotime) < 2592000){
//     			redirect(__CONTROLLER__.'/audit');
//     			exit();
//     		}
//     	}else {
    		if(in_array($this->info->status,array(1,3,4,5))){
    			redirect(__CONTROLLER__.'/audit');
    			exit();
    		}
//     	}
    }












}