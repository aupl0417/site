<?php
namespace Home\Model;
use Think\Model;
class MemberModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $_validate = array(
		array('username','require','昵称不能为空！'),
		array('password','require','密码不能为空！'),
	);
	
	protected $_auto = array (
		array('atime','time',1,'function'),
		array('etime','time',2,'function'),
		array('ip','get_client_ip',3,'function'),	
	);
	
	/*
	+--------------------------------
	+$result['code']  1:登录成功  2:账号被锁定 3:找不到记录
	+--------------------------------
	*/
	
	public function getrs($param){
		$username=strtolower(trim($param['username']));
		$password=md5(trim($param['password']));
		
		$map['_string']='(username="'.$username.'" or mobile="'.$username.'" or email="'.$username.'") and password="'.$password.'"';
		
		if($rs=$this->where($map)->field('id,active,username,password')->find()){
			if($rs['active']==0) $result['code']=2;
			else{
				$result['code']=1;
				$result['user']=$rs;
			}
		}else{
			$result['code']=0;
		}
		
		return $result;
	}
	
	
	//判断某字段的值是否存在
	public function check_field($param){
		$map[$param['field']]=$param['value'];
		if($this->where($map)->count()) return true;
		else return false;
	}


}
?>