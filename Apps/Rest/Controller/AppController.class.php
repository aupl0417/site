<?php
/*
+----------------------------
+ APP版本升级检测
+-----------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class AppController extends CommonController {
    protected $action_logs = array('auth','token');

    public function _initialize() {
        parent::_initialize();

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
            '_check_version'            => 'type',  //APP版本升级检测
            '_ad'                       => array('require_check' => false),  //广告
            '_auth'                     => 'erp_uid',   //用户授权登录
            '_token'                    => 'erp_uid',   //生成授权链接
        ];

        $result=$sign_field[$method];
        return $result;
    }


    public function index(){
        redirect(C('sub_domain.www'));
    }


	/**
     * APP版本升级检测
     * @param string $type 类型；IOS|ANDROID
     */
	public function _check_version(){
		$do=M('app_upgrade');
		$rs=$do->cache(true,C('CACHE_LEVEL.XXS'))->where(array('type'=>I('post.type')))->field('atime,etime,ip',true)->order('version desc')->find();
		if($rs){
			$rs['down_url']=C('sub_domain.rest').'/Download/android/id/'.$rs['id'];
			$this->apiReturn(1,array('data'=>$rs));
		}else{
			$this->apiReturn(0);
		}
	}

	/**
     * 首页广告
     */
    public function _ad(){
        $ad1 = $this->_ad_item(175);
        $ad2 = $this->_ad_item(176);

        $data['ad1'] = $ad1['data'];
        $data['ad2'] = $ad2['data'];

        return ['code' => 1,'data' => $data];
    }

    /**
     * @param int $position_id 广告位
     */
    public function _ad_item($position_id){
        $do=D('Common/PositionRelation');
        $prs=$do->relation(true)->relationWhere('ad','status=1 and (is_default=1 or FIND_IN_SET("'.date('Y-m-d').'",days))')->relationField('ad','id,name,sort,images,url,is_default')->where(array('id'=>$position_id))->field('id,position_name,type,num,default_images,url,width,height,is_seat')->find();

        //dump($prs);
        //默认广告 $default
        foreach($prs['ads'] as $key=>$val){
            if($val['is_default']==1) $default[$val['sort']]=$val;  //默认广告
            else $adlist[$val['sort']]=$val;    //用户投放广告
        }

        //当没有默认广告和用户广告是使用广告位设置的点位图
        $tmp = [
            'url'       => $prs['url'],
            'images'    => $prs['default_images'],
            'name'      => $prs['content'],
            'background_images' => $prs['background_images'],
        ];
        for($i=0;$i<$prs['num'];$i++){
            $ads[$i] = isset($adlist[$i]) ? $adlist[$i] : ($default[$i] ? $default[$i] : $tmp);
        }

        if($ads) return ['code' => 1,'data' => $ads];

        return ['code' => 3];

    }

    /**
     * App授权登录
     */
    public function _auth(){
        $cache_name = 'app_auth_' . I('post.erp_uid');

        if($status == 1) return ['code' => 1];
        else {  //执行登录操作
            $res = A('Erp')->_user_info(I('post.erp_uid'));
            if($res['code'] == 1){
                //判断用户是否已入库
                $data    =   [
                    'erp_uid'         =>  $res['data']->u_id,
                    'type'            =>  $res['data']->u_type,
                    'nick'            =>  $res['data']->u_nick,
                    'face'            =>  $res['data']->u_logo?$res['data']->u_logo:'https://img.trj.cc/FplovbCyAOdbztCfRqP9H02ec9hE',
                    'password'        =>  $res['data']->u_loginPwd,
                    'name'            =>  $res['data']->u_name,
                    'email'           =>  $res['data']->u_email,
                    'mobile'          =>  $res['data']->u_tel,
                    'group_id'        =>  $res['data']->u_groupId,
                    'level_id'        =>  $res['data']->u_level,
                    'status'          =>  $res['data']->u_state,
                    'code'            =>  $res['data']->u_code,
                    'up_uid'          =>  $res['data']->u_fCode,
                    'is_auth'         =>  $res['data']->auth,
                    //'openid'          =>  $this->create_id(), //防止多出登陆
                ];
                //
                $user=M('user')->where(['erp_uid' => $res['data']->u_id ])->field('id,openid,loginum,shop_type,shop_id')->find();
                if($user){
                    $data['last_login_time']=date('Y-m-d H:i:s');
                    $data['ip']				=get_client_ip();
                    $data['loginum']		=$user['loginum']+1;

                    M('user')->where(['id' => $user['id']])->save($data);
                    $data['level_name']=$res['data']->u_level_text;
                    $data=array_merge($data,$user);

                    S($cache_name,['code' => 1,'atime' => date('Y-m-d H:i:s')]);
                    return ['code' => 1,'data' => $data];
                }else{
                    $data['openid']		=$this->create_id();
                    $data['ip']			=get_client_ip();
                    if($data['id']=M('user')->add($data)){
                        $data['level_name']=$res['data']->u_level_text;

                        S($cache_name,['code' => 1,'atime' => date('Y-m-d H:i:s')]);
                        return ['code' => 1,'data' => $data];
                    }else{
                        return ['code' =>0];
                    }
                }
            }else{
                //登录失败
                return ['code' =>0];
            }

        }
    }


    /**
     * 生成授权链接
     */
    public function _token(){
        $cache_name = md5('app_token_'.I('post.appid').'_'.I('post.erp_uid'));
        S($cache_name,['code' => 1,'erp_uid' => I('post.erp_uid'),'atime' => time()],300);

        $url = C('sub_domain.oauth2').'/App/login?token='.$cache_name . (I('post.redirect_url') ? '&redirect_url=' . urlencode(I('post.redirect_url')) : '');
        return ['code' =>1,'data' => ['url' => $url],'msg' => '创建授权链接成功！'];
    }



}