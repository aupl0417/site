<?php
/**
 *同步登录ERP系统-用户授权获取
 *此功能已作废，已使用新的控制器代替AppController
*/
namespace Oauth2\Controller;
use Wap\Controller\CommonController;
class AppauthController extends CommonController {
	//从APP请求并生成Ｍemcached
	/*
	+---------------------------------
	+	$data['appid']	='100020160207';
	+	$data['secret']	='58231ebf33fbf1542d5df785d80ffc2e';
	+	$data['type']	='IOS';
	+	$data['code']	='';　　　	//设备ID
	+	$data['userid']	='';		//用户u_id
	+---------------------------------
	*/

	public function token(){
		if(empty($_POST['type']) || empty($_POST['code']) || empty($_POST['userid'])) {
			$result['code']=2;
			$result['msg']='参数错误！';
			echo json_encode($result);
			exit;
		}

		$apps=array(
			'IOS'	=>array(
				'appid'	=>'100020160207',
				'secret'=>'58231ebf33fbf1542d5df785d80ffc2e',
				'type'	=>'IOS',
			),
			'ANDROID'=>array(
				'appid'	=>'200020160207',
				'secret'=>'c9d98e8309c044615c00b018c4a141c5',
				'type'	=>'ANDROID',				
			)
		);


		//if($rs){
		if($apps[I('post.type')]['appid']==I('post.appid') && $apps[I('post.type')]['secret']==I('post.secret')){
			$cache_name=md5(I('post.type').'_'.I('post.code'));
			$data['type']=I('post.type');
			$data['code']=I('post.code');
			$data['token']=$cache_name;
			$data['userid']=I('post.userid');
			S($cache_name,$data,86400);
			$result['code']=1;
			$result['url']=C('sub_domain.oauth2').'/Appauth/login/type/'.trim(I('post.type')).'/token/'.$cache_name;
			$result['msg']='已创建授权链接';
		}else{
			$result['code']=3;
			$result['msg']='非法登录！';
		}

		$logs = [
				'atime'	=>date('Y-m-d H:i:s'),
				'device'=>I('post.type'),
				'ip'	=>get_client_ip(),
				'post'	=>var_export(I('post.'),true),
				'res'	=>var_export($result,true)
		];
		log_add('app',$logs);		

		echo json_encode($result);
		
	}

	public function login(){
		$cache_name=trim(I('get.token'));
		$data=S($cache_name);
		if(empty($data)){
			//echo '授权失败！';
			redirect(C('sub_domain.wap'));
			exit;
		}
		if(trim(I('get.type'))!=$data['type']){
			//echo '错误的参数！';
			redirect(C('sub_domain.wap'));
			exit;
		}

		if(I('get.is_test')==1) dump($data);
		//C('DEBUG_API',true);
		//$data['userid']='5cce33bdb9d7ecc5ec2ee7325a91a55d';

		$res=$this->doApi('/Erp/user_info2',['erp_uid' => $data['userid']]);
		//dump($res);
		if($res->code==1){
			//判断用户是否已入库
		    $data    =   [
		        'erp_uid'         =>  $res->data->u_id,
		        'type'            =>  $res->data->u_type,
		        'nick'            =>  $res->data->u_nick,
		        'face'            =>  $res->data->u_logo?$res->data->u_logo:'https://img.trj.cc/FplovbCyAOdbztCfRqP9H02ec9hE',
		        'password'        =>  $res->data->u_loginPwd,
		        'name'            =>  $res->data->u_name,
		        'email'           =>  $res->data->u_email,
		        'mobile'          =>  $res->data->u_tel,
		        'group_id'        =>  $res->data->u_groupId,
		        'level_id'        =>  $res->data->u_level,
		        'status'          =>  $res->data->u_state,
		        'code'            =>  $res->data->u_code,
		        'up_uid'          =>  $res->data->u_fCode,
		        //'is_auth'         =>  $res->data->auth,
		        //'openid'          =>  $this->create_id(), //防止多出登陆
		    ];

		    

			$user=M('user')->where(['erp_uid' => $res->data->u_id ])->field('id,openid,loginum')->find();
			if($user){
				$data['last_login_time']=date('Y-m-d H:i:s');
				$data['ip']				=get_client_ip();
				$data['loginum']		=$user['loginum']+1;

				M('user')->where(['id' => $user['id']])->save($data);
				$data['level_name']=$res->data->u_level_text;
				$data=array_merge($data,$user);
				
				session('user',$data);
				S(md5(session_id()));

				redirect(C('sub_domain.wap'));

			}else{
				$user=M('user')->where(['nick' => $res->data->u_nick ])->field('id,nick')->find();
				if($user){
					//echo '错误！昵称“'.$res->data->u_nick.'”存在冲突，请联系客服处理！';
					//exit();
					M('user')->where('id='.$user['id'])->setField('nick',$user['nick'].'_'.$user['id'].'_copy');
				}

				$data['openid']		=$this->create_id();
				$data['ip']			=get_client_ip();
				if($data['id']=M('user')->add($data)){
					$data['level_name']=$res->data->u_level_text;
					session('user',$data);
					S(md5(session_id()));
					redirect(C('sub_domain.wap'));
				}else{
					//echo '授权失败！';
					redirect(C('sub_domain.wap'));
				}
			}
		}else{
			//登录失败
			//echo '获取不到用户资料，授权失败！';
			redirect(C('sub_domain.wap'));
		}
		
	}



}