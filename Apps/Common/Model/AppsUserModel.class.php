<?php
namespace Common\Model;
use Think\Model;
class AppsUserModel extends Model {
	protected $tableName='apps_user';
	/**
	* 用户授权检查
	* @param integet $param['appid']	应用ID
	* @param string $param['access_key'] AccessKey
	* @param string $param['secret_key']	SecretKey
	* @param string $param['sign_code']	签名串
	*/
	public function user_check($param){
		//必传参数
		/*
		$need_param=array('appid','access_key','secret_key','sign_code');
		$res=need_param($need_param,$param);
		if($res['code']!=1){
			//缺少必传参数！
			$result['code']=13;
			$result['msg']=C('error_code')[$result['code']].@implode(',',$res['nokey']);
			return $result;
		}
		*/

		if($rs=$this->where(array('id'=>$param['appid']))->field('id,status,access_key,secret_key,sign_code')->find()){
			if($rs['status']!=1){
				//该应用接口已被停用！
				$result['code']=14;
				$result['msg']=C('error_code')[$result['code']];
			}elseif($param['access_key']!=$rs['access_key'] || $param['secret_key']!=$rs['secret_key'] || $param['sign_code']!=$rs['sign_code']){
				//参数错误！
				$result['code']=12;
				$result['msg']=C('error_code')[$result['code']];
			}else{
				//授权成功！
				$result['code']=10;
				$result['msg']=C('error_code')[$result['code']];
				$this->where(array('id'=>$rs['id']))->setInc('num',1,60);
			}
		}else{
			//应用ID不存在！
			$result['code']=11;
			$result['msg']=C('error_code')[$result['code']];
		}

		$result['data']=$param;

		return $result;

	}

}
?>