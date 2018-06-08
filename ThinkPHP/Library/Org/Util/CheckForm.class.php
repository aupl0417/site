<?php
namespace Org\Util;
/**
 * 数据验证类
 */
class CheckForm extends CheckData {
	private $check_type=1; //0为逐一检验，1为全部检验
	private $data=array();	//要进行验证的数据，Array类型


	/**
	 *数据验证
	 * @param array $this->data
	 */
	public function check_data(){
		foreach($this->data as $val){
			$res=$this->check_item($val);
			if(!is_null($res)) {
				$error[]=$res;
				if($this->check_type==1){
					$msg[]=$res['msg'];
				}else{
					break;
				}
			}
		}

		$result['error']=$error;
		$result['count']=count($error);
		$result['msg']=@implode(',',$msg);
		return $result;
	}

	/**
	 * 单项验证
	 * @param array $item
	 * $item['data']	要验证的数据
	 * $item['check']	array 验证选项，
	 * 如：$item['check']=array(array('function'=>'notempty','msg'=>'必填项！'),array(……))
	 * @return array
	 */

	protected function check_item($item){
		$this->str=$item['data'];

		foreach($item['check'] as $val){
			if($this->$val['function']()==false){
				$result['msg']=$val['msg'];
				break;
			}
		}

		return $result;
	}

	//设置属性
	public function __set($name,$v){
		return $this->$name=$v;
	}

	//获取属性
	public function __get($name){
		return $this->$name;
	}

	//追加验证选项
	public function set_item($data,$option){

		return $this->data[]=array('data'=>$data,'check'=>$option);
	}



}
