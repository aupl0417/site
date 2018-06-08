<?php
namespace Admin\Controller;
use Think\Controller;
class RadioTableController extends CommonModulesController {
	public function index(){
		$table=I('get.table');
		if(empty($table)) {
			echo '参数错误！';
			exit;
		}

		$tcfg=D('TablesConfig')->config(array('table'=>$table));
		$this->assign('tcfg',$tcfg);

		if($tcfg['do']=='D') $table=ucfirst(str_replace('_','',$tcfg['table_name'])).$tcfg['id'].'View';

		$ac=$tcfg['do']?$tcfg['do']:'M';
		



		$pagesize=$tcfg['pagesize']?$tcfg['pagesize']:24;
		$orderby=$tcfg['orderby']?$tcfg['orderby']:'atime desc';
		$orderby=I('post.orderby')?str_replace('-',' ',I('post.orderby')):$orderby;

		if(I('post.sid')) $map['sid']=array('in',sortid(array('table'=>$tcfg['table_name'].'_sort','sid'=>I('post.sid'))));
		if(trim(I('post.q')) && I('post.field')) $map[I('post.field')]=array('like','%'.trim(I('post.q')).'%');

		if(I('get.sellerid')) $map['sellerid'] = I('get.sellerid');
		$pagelist=pagelist(array(
			'table'		=>$table,
			'do'		=>$ac,
			'map'		=>$map,
			'field'		=>$tcfg['field'],
			'pagesize'	=>$pagesize,
			'order'		=>$orderby,
		));

		$this->assign('pagelist',$pagelist);
		
		//dump($pagelist);

		$tpl=I('get.tpl')?I('get.tpl'):'pagelist';
		//$this->assign('body',$this->fetch($tpl));
		

		$this->display($tpl);
	}

	public function sel_goods_form(){
	$table=I('get.table');
	if(empty($table)) {
		echo '参数错误！';
		exit;
	}

	$tcfg=D('TablesConfig')->config(array('table'=>$table));
	$this->assign('tcfg',$tcfg);

	if($tcfg['do']=='D') $table=ucfirst($tcfg['table_name']).$tcfg['id'].'View';

	$ac=$tcfg['do']?$tcfg['do']:'M';


	$pagesize=$tcfg['pagesize']?$tcfg['pagesize']:24;
	$orderby=$tcfg['orderby']?$tcfg['orderby']:'atime desc';
	$orderby=I('post.orderby')?str_replace('-',' ',I('post.orderby')):$orderby;

	if(I('post.sid')) $map['sid']=array('in',sortid(array('table'=>$tcfg['table_name'].'_sort','sid'=>I('post.sid'))));
	if(trim(I('post.q')) && I('post.field')) $map[I('post.field')]=array('like','%'.trim(I('post.q')).'%');

	if(I('get.sellerid')) $map['sellerid'] = I('get.sellerid');
	$pagelist=pagelist(array(
		'table'		=>$table,
		'do'		=>$ac,
		'map'		=>$map,
		'field'		=>$tcfg['field'],
		'pagesize'	=>$pagesize,
		'order'		=>$orderby,
	));

	$this->assign('pagelist',$pagelist);

	$tpl=I('get.tpl')?I('get.tpl'):'pagelist';
	$this->assign('body',$this->fetch($tpl));

	$this->display();
}
	
}