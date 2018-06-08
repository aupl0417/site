<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| Work管理端各模块管理功能共用方法（核心）
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Admin\Controller;
use Think\Controller;
class CommonModulesController extends CommonController {
    protected $_data    =   [];
    /**
    * 列表
    */
    public function _index($param=null){
    	$map=is_array($this->map)?$this->map:array();
    	if($param['map']) $map=array_merge($map,$param['map']);

    	if(in_array(CONTROLLER_NAME,['Goods','Help']) && I('get.category_id')){
    		$map['category_id'] = ['in',sortid(['table' =>strtolower(CONTROLLER_NAME).'_category','sid' => I('get.category_id')])];
    	}

		$pagelist=pagelist(array(
				'do'		=>$this->fcfg['do'],
				'table'		=>$this->fcfg['modelname'],
				'pagesize'	=>$this->fcfg['pagesize'],
				'order'		=>$this->fcfg['order'],
				'fields'	=>$this->fcfg['fields'],
				'relation'	=>$this->fcfg['action_type']==2?true:'',
				'map'		=>$map,
			));

 		//导出excel
		if($param['is_out_excel'] == 1){
			$fields = $this->plist();
			
			$out_option_orders = 'A';//excel横列排序
			foreach($fields as $v){
				if(isset($v['field'])){
					$out_excel_option[$out_option_orders]['descript'] = $v['title'];
					$out_excel_option[$out_option_orders]['field'] = $v['field'];
					$out_option_orders++;
				}
			}
			
			foreach($pagelist['list'] as $k=>$v){
				//处理数据
				foreach($v as $ke=>$va){
					//需要转换的数据
					if(!empty($param['data_trans'][$ke])){
						//查询数据类型
						if($param['data_trans'][$ke]['type'] == 'sql'){
							$res = M($param['data_trans'][$ke]['table'])->field($param['data_trans'][$ke]['field'])->where($param['data_trans'][$ke]['where'].$va)->find();
							$pagelist['list'][$k][$ke] = ' '.$res[$param['data_trans'][$ke]['field']];
						//状态类型
						}else if($param['data_trans'][$ke]['type'] == 'type'){
							$pagelist['list'][$k][$ke] = ' '.$param['data_trans'][$ke]['data'][$va];
						//数组类型
						}else if($param['data_trans'][$ke]['type'] == 'array'){
							$pagelist['list'][$k][$ke] = ' '.$pagelist['list'][$k][$param['data_trans'][$ke]['first']][$param['data_trans'][$ke]['second']];
						}
					//直接输出的数据
					}else{
						//加空格避免长数据在excel使用科学计数法
						$pagelist['list'][$k][$ke] = ' '.$va;
					}
				}
			}

			D('Admin/Excel')->outExcel($pagelist['list'],$out_excel_option,$param['out_excel_title']);
		}else{
		    $this->_data  =   $pagelist;
			$this->assign('pagelist',$pagelist);
			//列表字段
			$this->assign('fields',$this->plist());
		}

		//$this->display();
    }

    /**
    * 添加记录
    */
    public function _add($param=null){
    	//$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function _add_save($param=null){
		$do=D($this->fcfg['verify_model']);
		$this->post_cmp();

        if(isset($_POST['is_bat']) && I('post.is_bat') != 1){   //批量添加,逗号隔开
            $n = 0;
            $bat = I('post.'.I('post.is_bat'));
            $bat = explode(',',$bat);
            foreach($bat as $val){
                $_POST[I('post.is_bat')] = $val;
                if ($do->create() && $do->add()) {
                    $n++;
                }
            }

            if($n > 0) {
                $result['status']   = 'success';
                $result['msg']      = '批量添加了'.$n.'个';
            }
            else {
                $result['status']   = 'warning';
                $result['msg']      = '添加失败！';

            }

        }else {
            if ($do->create() && $do->add()) {
                $result['status'] = 'success';
                $result['msg'] = '添加成功！';
                $result['id'] = $do->getLastInsID();
            } else {
                $result['status'] = 'warning';
                $result['msg'] = '操作失败！' . $do->getError();
            }
        }

		return $result;
	}

	/**
	* 修改记录
	*/
	public function _edit($param=null){
		$do=M($this->fcfg['table']);
		$rs=$do->where('id='.I('get.id'))->find();

		if($param['data']) $rs=array_merge($rs,$param['data']);
		$this->assign('rs',$rs);
		//$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function _edit_save($param=null){
		$do=D($this->fcfg['verify_model']);
		$this->post_cmp();

		if($do->create() && $do->save() !== false){
			$result['status']='success';
			$result['msg']='添加成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！'.$do->getError();
		}

		return $result;
	}

	/**
	* 删除选中记录
	*/
	public function _delete_select($param=null){
		if($param['map']) $map = $param['map'];
	    $map['id']=array('in',I('post.id'));
		$do=M($this->fcfg['table']);
		if($do->where($map)->delete()){
			$result['status']='success';
			$result['msg']='删除成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='删除失败！';
		}

		return $result;
	}

	/**
	* 批量更改状态
	*/
	public function _active_change_select($param=null){
		if($param['map']) $map=$param['map'];
		$map['id']=array('in',I('post.id'));
		$do=M($this->fcfg['table']);
		if($do->where($map)->save(array('status'=>I('get.toactive')))){
			$result['status']='success';
			$result['msg']='操作成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！';
		}

		return $result;
	}
	

	/**
	* 分类列表，目前只取3级分类
	*/
	public function _category($level=3,$param=null){
		$tp=I('get.p')?I('get.p'):1;
		$limit=(($tp-1)*$this->fcfg['pagesize']).','.$this->fcfg['pagesize'];

		if(!is_null($this->map)) $map=$this->map;

		$list=get_category(array('table'=>$this->fcfg['table'],'level'=>$level,'field'=>$this->fcfg['fields'],'cache_name'=>$param['cache_name'],'limit_level'=>array($limit),'map'=>array($map)));
		$this->assign('list',$list);

		//计算分页
		$do=M($this->fcfg['table']);
		
		$map['sid']=0;
		$count=$do->where($map)->count();
		$p = new \Think\Page($count, $this->fcfg['pagesize']);

		$page=$p->show_btn();
		$page['allnum']=$count;
		$page['allpage']=$p->allpage();
		$this->assign('page',$page);

		//dump($do->getLastSQL());

		//列表字段
		$this->assign('fields',$this->plist());		
	}
	
	/**
	* 排序
	*/
	public function _setsort($param=null){
		$do=M($this->fcfg['table']);
		foreach(I('post.ids') as $key=>$val){
			$do->where('id='.$val)->setField('sort',$key);
		}

		return array('status'=>'success');
	}

	/**
	* 类目转移
	*/
	public function _sid_change_select($param=null){
		$do=M($this->fcfg['table']);
		if($param['map']) $map=$param['map'];
		$map['id']=array('in',I('post.id'));
		if($do->where($map)->save(array('sid'=>I('get.tosid')))){
			$result['status']='success';
			$result['msg']='操作成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！';
		}

		return $result;
	}
	
	/**
	* 分类列表，适应多级且数据较多的分类
	*/
	public function _category_list(){
		$do=$this->fcfg['do']($this->fcfg['modelname']);
		$map=$this->map;
		$map['sid']=I('get.sid')!=''?I('get.sid'):0;
		$list=$do->where($map)->order('sort asc')->select();

		$this->assign('list',$list);
		
		//列表字段
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <a href="'.__CONTROLLER__.'/index/sid/[id]" class="btn btn-sm btn-default btn-rad btn-trans btn-block m0">进入子级</a>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn)); 
		
		//dump($list);
		return $list;
	}

	/**
	* 获取选中记录数据并导出
	*/
	public function _excel(){
		$do=$this->fcfg['do']($this->fcfg['modelname']);
		$list=$do->where(array('id'=>array('in',I('post.id'))))->field($this->fcfg['fields'])->select();
		foreach ($list as $key => $value) {
			# code...
		}
	}

    /**
     * 文件上传
     */
    public function _upload($field,$width=0,$height=0){
        if (empty($_FILES)) {
            $result['code']=53;
            $result['status']=0;
            $result['msg']=C('error_code')[$result['code']];
            return $result;
        }

        //充许上传格式
        $ext_arr    =array('gif','jpg','jpeg','png');
        $file_ext   =strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if(!in_array($file_ext,$ext_arr)){
            $result['code']=52;
            $result['status']=0;
            $result['msg']=C('error_code')[$result['code']];
            return $result;
        }
        //充许上传文件大小，限制3M
        $maxsize=1024*1024*3;
        $filesize=filesize($_FILES[$field]['tmp_name']);
        if($filesize>$maxsize){
            $result['code']=51;
            $result['status']=0;
            $result['msg']=C('error_code')[$result['code']];
            return $result;
        }

        //尺寸要求
        $imginfo=getimagesize($_FILES[$field]['tmp_name']);
        if($width>0 && $height>0){
            if($imginfo[0]!=$width || $imginfo[1]!=$height) {
                $result['code'] 	= 550;	//图片尺寸不符合要求
                $result['status']	= 0;
                $result['msg']		= C('error_code')[$result['code']];
                return $result;
            }


        }

        $res=$this->doApi('/Upload/upload2',array('content'=>file_get_contents($_FILES[$field]['tmp_name'])),'content,openid');

        $result['code']		=$res->code;
        $result['status']	=1;
        $result['msg']		=$res->msg;
        $result['url']		=$res->data->url;
        $result['imginfo']	=$imginfo;

        return $result;

    }
}