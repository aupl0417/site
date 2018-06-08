<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| Work管理端 表单生成器，适用于各个模块
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Admin\Controller;
use Think\Controller;
class FormController extends CommonController {
    public function index(){
    	if(I('get.table')) $map['tables']=I('get.table');

    	$pagelist=pagelist(array(
    			'table'		=>'formtpl',
    			'pagesize'	=>40,
    			'map'		=>$map,
    			'order'		=>'id desc',
    			'fields'	=>'id,atime,tpl_name,tables,remark',
    		));
    	$this->assign('pagelist',$pagelist);

    	$this->display();
    }

    /**
    * 取所有数据表
    */
    public function tables(){
		$tables=$this->get_tables();
		$this->assign('tables',$tables);
		$this->display();
    }

    /**
    * 取某表字段
    */
    public function form_create(){
    	$table=I('get.table');
    	$fields=$this->get_fields($table);
    	$this->assign('fields',$fields);
    	//dump($fields);
    	$this->display();
    }

    /**
    * 保存模板
    */
    public function form_create_save(){

    	$table=I('post.table');
    	$fields=$this->get_fields($table);
    	$this->assign('fields',$fields);

    	$do=M();
    	$do->startTrans();
    	$data=array();
    	$data['tpl_name']=I('post.tpl_name');
    	$data['tables']=I('post.table');

    	if($sw1=D('Formtpl')->create($data)) $sw1=D('Formtpl')->add();
    	else $this->errorReturn('warning',D('Formtpl')->getError());
    	
    	
    	$sw2=D('FormtplGroup')->add(array('formtpl_id'=>$sw1,'group_name'=>'默认分组','tables'=>I('post.table')));
    	$group_id=D('FormtplGroup')->getLastInsID();

    	if(empty($_POST['field'])) $this->errorReturn('warning','请选择字段！');
    	$sw3=1;
    	foreach(I('post.field') as $key=>$val){
    		$data=array();
    		$data['formtpl_id']		=$sw1;
    		$data['group_id']		=$group_id;
    		$data['name']			=$val;
    		$data['label']			=$fields[$val]['comment'];
    		$data['formtype']		='text';
    		$data['tables']			=I('post.table');

            //var_dump($data);


    		if($sw3=D('FormtplFields')->create($data)) $sw3=D('FormtplFields')->add();    		
    		else {
    			$this->errorReturn('warning',D('FormtplFields')->getError());
    			$sw3=false;
    			break;
    		}

    	}

    	if($sw1 && $sw2 && $sw3){
    		$do->commit();
    		$result['status']='success';
    		$result['msg']='创建成功！';
    		$result['id']=$sw1;
    	}else{
    		$do->rollback();
    		$result['status']='warning';
    		$result['msg']='操作失败！'.$error;    		
    	}

    	$this->ajaxReturn($result);
    }

    /**
    * 模板详情
    */
    public function formtpl_view(){
    	$do=M('formtpl');
    	$rs=$do->where('id='.I('get.id'))->field('etime,ip',true)->find();
    	

        $table=substr($rs['tables'],strlen(C('DB_PREFIX')));   //当前操作数据表
        $rs['verify_model_file']    =ucfirst(str_replace('_','',$table)).$rs['id'];       //验证模型
        $rs['view_model_file']      =$rs['verify_model_file'].'ViewModel.class.php';       //视图模型
        $rs['relation_model_file']  =$rs['verify_model_file'].'RelationModel.class.php';       //关联模型

        $this->assign('rs',$rs);

        //列表字段
        if($rs['list_fields']) $list_fields=eval(html_entity_decode($rs['list_fields']));

        if(!empty($list_fields)){

        }else{
            $do=M('formtpl_fields');
            $list_fields=$do->where(array('formtpl_id'=>$rs['id']))->field('name,label,is_list')->order('sort asc')->select();            
        }
        $this->assign('list_fields',$list_fields);

    	//分组
    	$do=D('FormtplGroupRelation');
    	$list=$do->relation(true)->where(array('formtpl_id'=>$rs['id']))->order('sort asc')->select();
    	$this->assign('list',$list);

    	$this->display();
    }

    /**
    * 修改模板
    */
    public function formtpl_edit(){
    	$do=M('formtpl');
    	$rs=$do->where('id='.I('get.id'))->find();
    	$this->assign('rs',$rs);
    	$this->display();
    }

    /**
    * 保存修改
    */
    public function formtpl_edit_save(){
    	$do=D('Formtpl');
        //处理列表字段
        if($_POST['checked']){
            foreach($_POST['checked'] as $val){
                if($_POST['field'][$val] && $_POST['label'][$val]) $list_fields[]=array('is_list'=>1,'name'=>$_POST['field'][$val],'label'=>$_POST['label'][$val],'attr'=>$_POST['attr'][$val],'function'=>$_POST['function'][$val]);
            }

            $_POST['list_fields']='return '.var_export($list_fields,true).';';
        }else $_POST['list_fields']='';

    	if($do->create() && $do->save()){
    		$result['status']='success';
    		$result['msg']='修改成功！';

            //$this->create_model_file(I('post.id'));
            //$this->create_view_model_file(I('post.id'));
            //$this->create_relation_model_file(I('post.id'));            
    	}else{
    		$result['status']='warning';
    		$result['msg']='修改失败！'.$do->getError();
    	}

    	$this->ajaxReturn($result);
    }


    /**
    * 删除模板
    */
    public function delete_select(){
    	$do=M('formtpl');
    	if($do->where(array('id'=>array('in',I('post.id'))))->delete()){
    		$result['status']='success';
    		$result['msg']='删除成功！';
    	}else{
    		$result['status']='success';
    		$result['msg']='删除成功！';    		
    	}

    	$this->ajaxReturn($result);
    }

    /**
    * 添加分组
    */
    public function group_add(){
    	$this->display();
    }

    /**
    * 保存分组
    */
    public function group_add_save(){
    	$do=D('FormtplGroup');
    	if($do->create() && $do->add()){
    		$result['status']='success';
    		$result['msg']='添加成功！';
    	}else{
    		$result['status']='warning';
    		$result['msg']='操作失败！'.$do->getError();
    	}

    	$this->ajaxReturn($result);    	
    }

    /**
    * 修改分组
    */
    public function group_edit(){
    	$do=M('formtpl_group');
    	$rs=$do->where('id='.I('get.id'))->find();
    	$this->assign('rs',$rs);
    	$this->display();
    }

    /**
    * 保存修改
    */
    public function group_edit_save(){
    	$do=D('FormtplGroup');
    	if($do->create() && $do->save()){
    		$result['status']='success';
    		$result['msg']='修改成功！';
    	}else{
    		$result['status']='warning';
    		$result['msg']='操作失败！'.$do->getError();
    	}

    	$this->ajaxReturn($result);
    }

    /**
    * 删除分组
    */
    public function group_delete(){
    	$do=M('formtpl_group');
    	if($do->where('id='.I('get.id'))->delete()){
    		$result['status']='success';
    		$result['msg']='删除成功！';
    	}else{
    		$result['status']='warning';
    		$result['msg']='删除失败！';
    	}

    	$this->ajaxReturn($result);
    }

    /**
    * 删除选择的字段
    */
    public function delete_field_select(){
    	$do=M('formtpl_fields');
    	if($do->where(array('id'=>array('in',I('post.id'))))->delete()){
    		$result['status']='success';
    		$result['msg']='删除成功！';
    	}else{
    		$result['status']='warning';
    		$result['msg']='删除失败！';
    	}

    	$this->ajaxReturn($result);
    }


    /**
    * 批量分组
    */
    public function group_change_select(){
    	$do=M('formtpl_fields');
    	if($do->where(array('id'=>array('in',I('post.id'))))->save(array('group_id'=>I('get.tosid')))){
    		$result['status']='success';
    		$result['msg']='操作成功！';
    	}else{
    		$result['status']='warning';
    		$result['msg']='操作失败！';
    	}

    	$this->ajaxReturn($result);    	
    }

    /**
    * 添加表单项
    */
    public function field_add(){
        $this->display();
    }    

    /**
    * 保存表单字段
    */
    public function field_add_save(){
        $do=D('FormtplFields');

        if($do->create() && $do->add()){
            $result['status']='success';
            $result['msg']='修改成功！';
        }else{
            $result['status']='warning';
            $result['msg']='操作失败！'.$do->getError();
        }

        $this->ajaxReturn($result);  
        
    }
    /**
    * 修改表单项
    */
    public function field_edit(){
    	$do=M('formtpl_fields');
    	$rs=$do->where('id='.I('get.id'))->find();
    	$this->assign('rs',$rs);
    	$this->display();
    }

    /**
    * 保存表单字段
    */
    public function field_edit_save(){
    	$do=D('FormtplFields');

        if($do->create() && $do->save()){
            $result['status']='success';
            $result['msg']='修改成功！';
        }else{
            $result['status']='warning';
            $result['msg']='操作失败！'.$do->getError();
        }

        $this->ajaxReturn($result);  
    	
    }

    /**
    * 根据数据表字段新增表单项
    */
    public function field_add_from_table(){
        $do=M('formtpl');
        $rs=$do->where('id='.I('get.formtpl_id'))->field('tables')->find();
        $table=$rs['tables'];
        $fields=$this->get_fields($table);
        $this->assign('fields',$fields);

        //已被添加字段
        $do=M('formtpl_fields');
        $list=$do->where(array('formtpl_id'=>I('get.formtpl_id')))->getField('name',true);
        $this->assign('list',$list);
        //dump($list);
        //dump($fields);
        $this->display();
    }

    /**
    * 保存表单项
    */
    public function field_add_from_table_save(){
        if(empty($_POST['field'])) $this->errorReturn('warning','请选择字段！');

        $do=M('formtpl');
        $rs=$do->where('id='.I('post.formtpl_id'))->field('tables')->find();
        $table=$rs['tables'];
        $fields=$this->get_fields($table);
        $this->assign('fields',$fields);

        $n=0;
        foreach(I('post.field') as $key=>$val){
            $data=array();
            $data['formtpl_id']     =I('post.formtpl_id');
            $data['group_id']       =I('post.group_id');
            $data['name']           =$val;
            $data['label']          =$fields[$val]['comment'];
            $data['formtype']       ='text';
            $data['tables']         =$rs['tables'];


            if($sw3=D('FormtplFields')->create($data)) {
                $sw3=D('FormtplFields')->add();          
                $n++;
            }else {
                $error[]=D('FormtplFields')->getError();
            }

        }

        if($n>0){
            $result['status']='success';
            $result['msg']='创建成功！';
        }else{

            $result['status']='warning';
            $result['msg']='操作失败！';          
        }

        $this->ajaxReturn($result);        
    }  

    /**
    * 批量设置状态
    */
    public function status_change_select(){
        $do=M('formtpl_fields');
        $data[I('get.status_field')]=I('get.tostatus');
        if($do->where(array('id'=>array('in',I('post.id'))))->save($data)){
            $result['status']='success';
            $result['msg']='设置成功！';
        }else{

            $result['status']='warning';
            $result['msg']='设置失败！'.$do->getLastSQL();          
        }

        $this->ajaxReturn($result);  
    }

    /**
    * 分组排序
    */
    public function setsort(){
        $do=M('formtpl_group');
        foreach(I('post.ids') as $key=>$val){
            $do->where('id='.$val)->setField('sort',$key);
        }
        $this->ajaxReturn(array('status'=>'success'));
    }

    /**
    * 字段排序
    */
    public function setsort_field(){
        $do=M('formtpl_fields');
        foreach(I('post.ids') as $key=>$val){
            $do->where('id='.$val)->setField('sort',$key);
        }
        $this->ajaxReturn(array('status'=>'success'));
    }      

    /**
    * 创建模型文件
    */
    public function create_model_file(){
        switch (I('post.type')) {
            case 1:
                    $this->create_verify_model_file(I('post.tplid'));
                break;
            case 2:
                    $this->create_view_model_file(I('post.tplid'));
                break;
            case 3:
                    $this->create_relation_model_file(I('post.tplid'));
                break;          

        }
        $this->ajaxReturn(array('status'=>'success','msg'=>'创建成功！'));
    }


    /**
    * 生成验证模型
    * @param int $formtpl_id 表单模板ID
    */
    public function create_verify_model_file($formtpl_id=null){
        if(is_null($formtpl_id)) $formtpl_id=I('get.formtpl_id');

        //取表单模板
        if(!$formtpl=M('formtpl')->where(array('id'=>$formtpl_id))->field('id,tables')->find()){
            E('获取不到表单模板信息！');
        }

        $table=substr($formtpl['tables'],strlen(C('DB_PREFIX')));   //当前操作数据表
        $verify_model=ucfirst(str_replace('_','',$table)).$formtpl['id'];       //验证模型

        $filename=C('MODEL_PATH').'/'.$verify_model.'Model.class.php';
        $do=M('formtpl_fields');
        $fields=$do->where(array('formtpl_id'=>$formtpl_id,'is_verify'=>1,'active'=>1))->field('atime,etime,ip',true)->order('sort asc')->select();

        if($fields){
            $code='';
            foreach($fields as $val){
                foreach($val as $vkey=>$v){
                    if($v) $val[$vkey]=html_entity_decode($v);
                }               
                $code.="        array('".$val['name']."','".($val['verify_regex']?$val['verify_regex']:'require')."','".($val['verify_tips']?$val['verify_tips']:$val['label'].'不能为空!')."',".($val['verify_map']!==''?$val['verify_map']:0).",'".$val['verify_regextype']."',".$val['verify_time'].($val['verify_regex']=='checkform'?','.$val['verify_custom']:'')."), ".chr(13).chr(10);
            }

            $file=file_get_contents(C('MODULES_PATH').'/Model/Model.class.php');
            $file=str_replace(array('{modelname}','{tablename}','{code}'),array($verify_model,$table,$code),$file);

            file_put_contents($filename, $file);
        }else{
            $file=file_get_contents(C('MODULES_PATH').'/Model/Model.class.php');
            $file=str_replace(array('{modelname}','{tablename}','{code}'),array($verify_model,$table,''),$file);
            file_put_contents($filename, $file);
        }

    }

    /**
    * 生成视图模型
    * @param int $formtpl_id 表单模板ID
    */
    public function create_view_model_file($formtpl_id=null){
        if(is_null($formtpl_id)) $formtpl_id=I('get.formtpl_id');

        //取表单模板
        if(!$formtpl=M('formtpl')->where(array('id'=>$formtpl_id))->field('id,tables,view_model')->find()){
            E('获取不到表单模板信息！');
        }

        $table=substr($formtpl['tables'],strlen(C('DB_PREFIX')));   //当前操作数据表
        $view_model=ucfirst(str_replace('_','',$table)).$formtpl['id'].'View';      //视图模型
        $filename=C('MODEL_PATH').'/'.$view_model.'Model.class.php';

        if($formtpl['view_model']){
            $code=html_entity_decode($formtpl['view_model']);
            $file=file_get_contents(C('MODULES_PATH').'/Model/ViewModel.class.php');
            $file=str_replace(array('{modelname}','{code}'),array($view_model,$code),$file);
            file_put_contents($filename, $file);
        }else{
            @unlink($filename);
        }
    }   

    /**
    * 生成关联模型
    * @param int $formtpl_id 表单模板ID
    */
    public function create_relation_model_file($formtpl_id=null){
        if(is_null($formtpl_id)) $formtpl_id=I('get.formtpl_id');

        //取表单模板
        if(!$formtpl=M('formtpl')->where(array('id'=>$formtpl_id))->field('id,tables,relation_model')->find()){
            E('获取不到表单模板信息！');
        }

        $table=substr($formtpl['tables'],strlen(C('DB_PREFIX')));   //当前操作数据表
        $relation_model=ucfirst(str_replace('_','',$table)).$formtpl['id'].'Relation';      //视图模型      
        $filename=C('MODEL_PATH').'/'.$relation_model.'Model.class.php';

        if($formtpl['relation_model']){
            $code=html_entity_decode($formtpl['relation_model']);           
            $file=file_get_contents(C('MODULES_PATH').'/Model/RelationModel.class.php');
            $file=str_replace(array('{modelname}','{tablename}','{code}'),array($relation_model,$table,$code),$file);
            file_put_contents($filename, $file);
        }else{
            @unlink($filename);
        }
    }

    /**
    * 创建控制器
    */
    public function create_controller(){
        $path=C('MODULES_PATH').'/'.I('post.type');
        $controller=ucfirst(strtolower(I('post.controller')));
        $name=trim(I('post.name'));
        $formtpl_id=I('post.formtpl_id');


        //创建模型文件
        $this->create_verify_model_file($formtpl_id);
        $this->create_view_model_file($formtpl_id);
        $this->create_relation_model_file($formtpl_id);


        //生成控制器
        $file=file_get_contents($path.'/Controller/Controller.class.php');
        $file=str_replace(array('{controller}','{name}','{formtpl_id}') , array($controller,$name,$formtpl_id), $file);
        file_put_contents('./Apps/Admin/Controller/'.$controller.'Controller.class.php', $file);

        //模板文件
        $view_path='./Apps/Admin/View/default/'.$controller;
        @mkdir($view_path);

        copy($path.'/View/add.html',$view_path.'/add.html');
        copy($path.'/View/edit.html',$view_path.'/edit.html');
        copy($path.'/View/index.html',$view_path.'/index.html');
        copy($path.'/View/search_box.html',$view_path.'/search_box.html');
        copy($path.'/View/widget_pagelist.html',$view_path.'/widget_pagelist.html');

        $file=file_get_contents($path.'/View/nav.html');
        $file=str_replace('{name}', $name, $file);
        file_put_contents($view_path.'/nav.html', $file);

        $do=D('Controller');
        $data['type']=I('post.type');
        $data['controller_name']=$name;
        $data['controller']=$controller;
        $data['formtpl_id']=$formtpl_id;

        $action_set=include($path.'/action_set.php');
        $data['action']='return '.var_export($action_set,true).';';

        if(!$rs=$do->where(array('controller'=>$controller))->find()){
            $do->add($data);
        }else{
            $do->where('id='.$rs['id'])->save($data);
        }        

        $this->ajaxReturn(array('status'=>'success','msg'=>'创建成功！'));
    }

    /**
    * 搜索字段列表
    */
    public function formtpl_search_fields(){
        $do=M('formtpl_search_fields');
        $list=$do->where(array('formtpl_id'=>I('get.formtpl_id')))->field('id,active,name,label,formtype,search_type,map_field')->order('sort asc')->select();
        $this->assign('list',$list);
        $this->display();
    }


    /**
    * 搜索字段排序
    */
    public function setsort_search_field(){
        $do=M('formtpl_search_fields');
        foreach(I('post.ids') as $key=>$val){
            $do->where('id='.$val)->setField('sort',$key);
        }
        $this->ajaxReturn(array('status'=>'success'));
    }  

    /**
    * 从表单模板中添加搜索字段
    */
    public function search_field_add_from_fields(){
        $do=M('formtpl_fields');
        //$tpl=$do->where(array('id'=>I('get.formtpl_id')))->field('id,tables')->find();
        $list=$do->where(array('formtpl_id'=>I('get.formtpl_id')))->field('id,name,label,formtype')->order('sort asc')->select();
        $this->assign('list',$list);

        $do=M('formtpl_search_fields');
        $selected=$do->where(array('formtpl_id'=>I('get.formtpl_id')))->getField('name',true);
        $this->assign('selected',@implode(',',$selected));


        $this->display();

    }

    /**
    * 保存搜索字段
    */
    public function search_field_add_from_fields_save(){
        if(empty($_POST['id'])) goto error;

        $do=M('formtpl_fields');
        $list=$do->where(array('id'=>array('in',I('post.id'))))->field('id',true)->order('sort asc')->select();
        
        $do=D('formtpl_search_fields');
        $selected=$do->where(array('formtpl_id'=>I('get.formtpl_id')))->getField('name',true);

        if($list){
            foreach($list as $val){
                if(!in_array($val['name'],$selected)){
                    $do->create($val);
                    $do->add();
                }
            }
        }

        $this->ajaxReturn(array('status'=>'success','msg'=>'添加成功！'));
            
        error:
        $this->ajaxReturn(array('status'=>'warning','msg'=>'添加失败！'));
        
    }

    /**
    * 新增搜索字段
    */
    public function search_field_add(){
        $this->display();
    }

    /**
    * 保存搜索表单字段
    */
    public function search_field_add_save(){
        $do=D('FormtplSearchFields');

        if($do->where(array('name'=>I('post.name'),'formtpl_id'=>I('post.formtpl_id')))->find()) $this->ajaxReturn(array('status'=>'warning','msg'=>'该搜索字段已存在！'));

        if($do->create() && $do->add()){
            $result['status']='success';
            $result['msg']='添加成功！';
        }else{
            $result['status']='warning';
            $result['msg']='添加失败！'.$do->getError();
        }

        $this->ajaxReturn($result);  
        
    }

    /**
    * 修改搜索表单项
    */
    public function search_field_edit(){
        $do=M('formtpl_search_fields');
        $rs=$do->where('id='.I('get.id'))->find();
        $this->assign('rs',$rs);
        $this->display();
    }

    /**
    * 保存搜索表单字段
    */
    public function search_field_edit_save(){
        $do=D('FormtplSearchFields');

        if($do->create() && $do->save()){
            $result['status']='success';
            $result['msg']='修改成功！';
        }else{
            $result['status']='warning';
            $result['msg']='操作失败！'.$do->getError();
        }

        $this->ajaxReturn($result);  
        
    }

    /**
    * 删除搜索表单字段
    */

    public function search_field_delete(){
        $do=M('formtpl_search_fields');

        if($do->where(array('id'=>array('in',I('post.id'))))->delete()){
            $result['status']='success';
            $result['msg']='删除成功！';
        }else{
            $result['status']='warning';
            $result['msg']='删除失败！'.$do->getError();
        }

        $this->ajaxReturn($result);
    }

    /**
    * 导出模板
    */
    public function export(){
        $do=M('formtpl');
        $rs=$do->where(['id'=>I('get.id')])->field('id',true)->find();
        //var_dump($rs);
        //$rs['tables']=str_replace('ylh_', 'dterp_', $rs['tables']);

        $file='<?php'.PHP_EOL;
        $file.='$tpl='.var_export($rs,true).';'.PHP_EOL;
        $file.='$do=M("formtpl");'.PHP_EOL;
        $file.='$insid=M("formtpl")->add($tpl);'.PHP_EOL;

        
        $group=M('formtpl_group')->where(['formtpl_id' => I('get.id')])->select();

        foreach($group as $val){
            //$val['tables']=str_replace('ylh_', 'dterp_', $val['tables']);
            $fields=M('formtpl_fields')->where(['group_id' => $val['id']])->field('id',true)->select();

            unset($val['id']);
            $file.='$group='.var_export($val,treu).';'.PHP_EOL;
            $file.='$group["formtpl_id"]=$insid;'.PHP_EOL;
            $file.='$gid=M("formtpl_group")->add($group);'.PHP_EOL;            

            foreach($fields as $v){
                //$v['tables']=str_replace('ylh_', 'dterp_', $v['tables']);
                $file.='$field='.var_export($v,true).';'.PHP_EOL;
                $file.='$field["group_id"]=$gid;'.PHP_EOL;
                $file.='$field["formtpl_id"]=$insid;'.PHP_EOL;
                $file.='M("formtpl_fields")->add($field);'.PHP_EOL;
            }
        }

        $search=M('formtpl_search_fields')->where(['formtpl_id' => I('get.id')])->field('id',true)->select();
        foreach($search as $val){
            //$val['tables']=str_replace('ylh_', 'dterp_', $val['tables']);
            $file.='$search='.var_export($val,true).';'.PHP_EOL;
            $file.='$search["formtpl_id"]=$insid;'.PHP_EOL;
            $file.='M("formtpl_search_fields")->add($search);'.PHP_EOL;
        }

        $file.='?>';

        file_put_contents('tpl_'.I('get.id').'.php', $file);
        echo 'tpl_'.I('get.id').'.php';
    }
}