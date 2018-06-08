<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * App相关
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-03-05
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class AppController extends ApiController {
    protected $action_logs = array('token','auth');

    /**
     * subject: 服务端登出
     * api: /App/logout
     * author: Lazycat
     * day: 2017-03-04
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     */
    public function logout(){
        $this->check('openid',false);
        if (M('user_device')->where(['uid' => $this->user['id']])->save(['status' => 0]) === false) $this->apiReturn(['code' => 0, 'msg' => '更改device状态失败']);
        $res = $this->_logout();
        $this->apiReturn($res);
    }

    public function _logout(){
        $cache_name = 'app_logined_'.$this->user['openid'];
        S($cache_name,null);

        return ['code' => 1];
    }


    /**
     * subject: 判断用户是否在服务端保持登录状态
     * api: /App/logined
     * author: Lazycat
     * day: 2017-03-04
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     */
    public function logined(){
        $this->check('',false);

        $res = $this->_logined();
        $this->apiReturn($res);
    }

    public function _logined(){
        $cache_name = 'app_logined_'.$this->user['openid'];
        $data       = S($cache_name);

        if(empty($data)) return ['code' => 2,'msg' => '服务端并无保存登录状态！'];

        if($data['device_id'] == $this->token['data']['device_id']) return ['code' => 1,'msg' => '服务端已保存登录状态！','data' => $data];

        return ['code' => 0,'msg' =>'已在其它设备登录！','data' => $data];
    }

    /**
     * subject: APP请求生成授权连接
     * api: /App/token
     * author: Lazycat
     * day: 2017-04-05
     * content: 用于dttx APP
     *
     * [字段名,类型,是否必传,说明]
     * param: erp_uid,string,1,ERP中的用户UID
     * param: redirect_url,string,0,跳转连接，默认跳到wap首页
     */
    public function token(){
        $this->check($this->_field('redirect_url','erp_uid'),false);

        $res = $this->_token($this->post);
        $this->apiReturn($res);
    }
    public function _token($param){
        $cache_name = md5('app_'.$this->token.'_'.$param['erp_uid']);
        S($cache_name,['code' => 1,'erp_uid' => $param['erp_uid'],'atime' => time()],300);

        $url = C('sub_domain.oauth2').'/AppV2/login?token='.$cache_name . ($param['redirect_url'] ? '&redirect_url=' . urlencode($param['redirect_url']) : '');
        return ['code' =>1, 'data' => ['url' => $url],'msg' => '创建授权链接成功！'];
    }

    /**
     * subject: APP授权登录
     * api: /App/auth
     * author: Lazycat
     * day: 2017-04-05
     * content: 用于dttx APP
     *
     * [字段名,类型,是否必传,说明]
     * param: erp_uid,string,1,ERP中的用户UID
     */
    public function auth(){
        $this->check('erp_uid',false);

        $res = $this->_auth($this->post);
        $this->apiReturn($res);
    }

    public function _auth($param){
        $cache_name = 'app_auth_' . $param['erp_uid'];
        $res = A('Rest2/Erp')->_user_info(['userID' => $param['erp_uid']]);
        log_add('api_debug', ['param' => $param, 'route' => 'app/auth', 'res' => $res, 'time' => date('Y-m-d H:i:s')]);
        if($res['code'] == 1){
            //判断用户是否已入库
            $data    =   [
                'erp_uid'         =>  $res['data']['u_id'],
                'type'            =>  $res['data']['u_type'],
                'nick'            =>  $res['data']['u_nick'],
                'face'            =>  $res['data']['u_logo']?$res['data']['u_logo']:'https://img.trj.cc/FplovbCyAOdbztCfRqP9H02ec9hE',
                //'password'        =>  $res['data']['u_loginPwd'],
                'name'            =>  $res['data']['u_name'],
                'email'           =>  $res['data']['u_email'],
                'mobile'          =>  $res['data']['u_tel'],
                'group_id'        =>  $res['data']['u_groupId'],
                'level_id'        =>  $res['data']['u_level'],
                'status'          =>  $res['data']['u_state'],
                'code'            =>  $res['data']['u_code'],
                'is_auth'         =>  $res['data']['u_auth'] ? : '0000',
                //'openid'          =>  $this->create_id(), //防止多出登陆
                'is_un'           =>  $res['data']['u_isUn'] ? : 0,
                'is_bm'           =>  $res['data']['u_isBm'] ? : 0,
                'is_soc'          =>  $res['data']['u_isSoc'] ? : 0,
                'is_bc'           =>  $res['data']['u_isBc'] ? : 0,
                'is_ledt'         =>  $res['data']['u_isLedt'] ? : 0,
                'is_rest_username'=>  $res['data']['u_resetUsername'] ? : 0,
                'is_quit'         =>  $res['data']['u_isQuit'] ? : 0,
                'is_virtual'      =>  $res['data']['u_isVirtual'] ? : 0,
                'un_time'         =>  $res['data']['u_unTime'] ? : '1970-01-01',
                'bm_time'         =>  $res['data']['u_bmTime'] ? : '1970-01-01',
                'soc_time'        =>  $res['data']['u_socTime'] ? : '1970-01-01',
                'ledt_time'       =>  $res['data']['u_ledtTime'] ? : '1970-01-01',
                'rest_username_time' => $res['data']['u_resetUsernameTime'] ? : '1970-01-01',
                'quit_time'       =>  $res['data']['u_quitTime'] ? : '1970-01-01',
            ];
            if($res['info']['u_fax']) $data['fax'] = $res['info']['u_fax'];
            $user=M('user')->where(['erp_uid' => $res['data']['u_id']])->field('id,openid,loginum,shop_type,shop_id')->find();
            if($user){
                $data['last_login_time']= date('Y-m-d H:i:s');
                $data['ip']				= get_client_ip();
                $data['loginum']		= $user['loginum']+1;

                M('user')->where(['id' => $user['id']])->save($data);
                $data['level_name']     = $res['data']['u_level_text'];
                $data=array_merge($data,$user);

                S($cache_name,['code' => 1,'data' => $data,'atime' => date('Y-m-d H:i:s')]);
                return ['code' => 1,'data' => $data,'msg' => '授权登录成功！'];
            }else{
                $data['openid']		= $this->create_id();
                $data['ip']			= get_client_ip();
                if($data['id']=M('user')->add($data)){
                    $data['level_name'] = $res['data']['u_level_text'];

                    S($cache_name,['code' => 1,'data' => $data,'atime' => date('Y-m-d H:i:s')]);
                    return ['code' => 1,'data' => $data,'msg' => '授权登录成功！'];
                }else{
                    return ['code' => 0,'msg' => '授权登录失败！'];
                }
            }

        }

        return ['code' => 0,'msg' => '授权登录失败！'];
    }


    /**
     * subject: dttx App广告
     * api: /App/ad
     * author: Lazycat
     * day: 2017-04-05
     * content: 用于dttx APP
     *
     * [字段名,类型,是否必传,说明]
     * param: erp_uid,string,1,ERP中的用户UID
     */
    public function ad(){
        $this->check('erp_uid',false);

        $res = $this->_ad($this->post);
        $this->apiReturn($res);
    }

    public function _ad($param){
        $data['ad1']    = A('Rest2/Ad')->_ad(196)['data']['ads'];
//        $data['ad2']    = A('Rest2/Ad')->_ad(176)['data']['ads'];
//		$data['ad3']    = A('Rest2/Ad')->_ad(188)['data']['ads'];
//		$data['ad4']    = A('Rest2/Ad')->_ad(189)['data']['ads'];
		
		foreach($data as $k=>$val){
			foreach($val as $key=>$v){
                $data[$k][$key]['linkurl']  = $v['url'];
				$tmp        = $this->_token(['erp_uid' => $param['erp_uid'],'redirect_url' => $v['url']]);
				$data[$k][$key]['url'] = $tmp['data']['url'];
			} 
		}
/* 		
		foreach($data as &$val){
            foreach($val['data']['ads'] as &$v){
                $tmp        = $this->_token(['erp_uid' => $param['erp_uid'],'redirect_url' => $v['url']]);
                $v['url']   = $tmp['data']['url'];
            }
        } */

        return ['code' => 1,'data' => $data];
    }


    /**
     * subject: 版本检测
     * api: /App/version
     * author: Lazycat
     * day: 2017-04-05
     *
     * [字段名,类型,是否必传,说明]
     * param: type,int,0,类型，1=乐兑买家版，2=乐兑卖家版，默认为1
     * param: terminal,int,0,终端，1=安卓，2=IOS，默认为1
     */
    public function version(){
        $this->check($this->_field('type,terminal'),false);

        $res = $this->_version($this->post);
        $this->apiReturn($res);
    }

    public function _version($param){
        $param['type']      = $param['type'] ? $param['type'] : 1;
        $param['terminal']  = $param['terminal'] ? $param['terminal'] : 1;
        $do=M('app_upgrade');
        $rs = $do->cache(true,C('CACHE_LEVEL.XXS'))->where(['type' => $param['type'],'terminal' => $param['terminal']])->field('atime,etime,ip,type',true)->order('version desc')->find();
        if($rs){
            $rs['content'] = html_entity_decode($rs['content']);
            return ['code' => 1,'data' => $rs];
        }else{
            return ['code' => 0,'msg' => '暂无版本升级记录！'];
        }
    }


    /**
     * subject: IOS配置
     * api: /App/ios_config
     * author: Lazycat
     * day: 2017-04-13
     *
     * [字段名,类型,是否必传,说明]
     */
    public function ios_config(){
        $res = $this->_ios_config();
        $this->apiReturn($res);
    }

    public function _ios_config(){
        $rs = M('ios_config')->cache(true,60)->where(['status' => 1])->field('atime,etime,ip',true)->order('id desc')->find();

        if($rs){
            $rs['content'] = html_entity_decode($rs['content']);
            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 0,'msg' => '找不到配置内容！'];
    }

}