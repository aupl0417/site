<?php
namespace Admin\Model;
use Think\Model;
class AdminMenuModel extends Model {
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

	public function all($map=array()){
		
		$do=M('admin_menu');
		$list=$do->where(array_merge(array('sid'=>0),$map))->order('sort asc,id asc')->select();
		
		foreach($list as $key=>$val){
			$list[$key]['dlist']=$do->where(array_merge(array('sid'=>$val['id']),$map))->order('sort asc,id asc')->select();
			foreach($list[$key]['dlist'] as $vkey=>$v){
				$list[$key]['dlist'][$vkey]['dlist']=$do->where(array_merge(array('sid'=>$v['id']),$map))->order('sort asc,id asc')->select();
			}
		}
		
		return $list;
	}
}
?>