<?php
// 图表类
namespace Admin\Widget;
use Think\Controller;
use Common\Builder\Activity;
class EchartWidget extends Controller {
/*
		//参数格式
        $test['options'] = 'xxx',
 		$test['title'] = "测试题目";//标题
		$test['top'] = "12%";		//表格距离上部百分比,可以不填写
		$test['left'] = "5%";       //表格距离左部百分比,可以不填写
		$test['right'] = "5%";      //表格距离右部百分比,可以不填写
		$test['bottom'] = "5%";     //表格距离下部百分比,可以不填写
		$test['xAxis'] = array(     //xAxis:基于横向坐标的矩形图，yAxis基于纵向坐标的矩形图
			0 =>'x轴1',
			1 =>'x轴2',
			2 =>'x轴3',
			3 =>'x轴4',
			4 =>'x轴5',
		);
		$test['x_title'] =array(  //要统计的数据标题
			0 =>'标题1',
			1 =>'标题2',
			2 =>'标题3',
		);
		$test['data'] = array(   //图表中的数据
			array(
				'name' => "标题1",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'data' => array(
							0=> 0,
							1=> 1,
							2=> 2,
							3=> 3,
							4=> 4,
						),
			),
			array(
				'name' => "标题2",
				'type' => "bar", //line:折线图，bar:柱状图
				'data' => array(
							0=> 1,
							1=> 2,
							2=> 3,
							3=> 4,
							4=> 5,
						),
			),
			array(
				'name' => "标题3",
				'type' => "bar", //line:折线图，bar:柱状图
				'data' => array(
							0=> 2,
							1=> 3,
							2=> 4,
							3=> 5,
							4=> 6,
						),
			),
		); */	
	//柱状和折线图图表
	public function bar_echart($param=null){
		$option['title']=array('text'=>$param['title']);
		$option['tooltip']=array('trigger'=>'axis');
		$option['legend']=array('data'=>$param['x_title']);
		$option['toolbox']=array(
				'show'			=>true,
				'feature'		=>array(
						'mark'			=>array('show'=>true),
						'dataView'		=>array('show'=>true,'readOnly'=>false),
						'magicType'		=>array('show'=>true,'type'=>array('line','bar')),
						'restore'		=>array('show'=>true),
						'saveAsImage'	=>array('show'=>true)
					),
			);
		$option['grid']=array('top'=>$param['top'],'left'=>$param['left'],'right'=>$param['right'],'bottom'=>$param['bottom'],'containLabel'=>true);
		$option['calculable']=true;
		if($param['xAxis']){
			$option['yAxis']=array(array('type'=>'value'));
			$option['xAxis']=array(array(
				'type'	=>'category',
				'data'	=>$param['xAxis'],
			));
		}else{
			$option['xAxis']=array(array('type'=>'value'));
			$option['yAxis']=array(array(
				'type'	=>'category',
				'data'	=>$param['yAxis'],
			));
		}

		$option['series']=$param['data'];
		echo 'var '.($param['option'] ? : 'option').'='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	}
		
/* 		
		//参数格式
		$test['title'] = array(
			'text'    => "测试主标题",   //主标题
			'subtext' => "测试副标题",   //副标题,可不要此参数
			'x'       => "center",       //标题位置(left,right,center)
		);

		$test['legend'] =array(
			0 =>'标题1',
			1 =>'标题2',
			2 =>'标题3',
		);
		$test["x_title"] = "left";      //legend 位置(left,right,center)
		$test["name"]    = "测试来源";  //图表用途名称
		$test['data'] = array(//数据
			array(
				'value' => 1,
				'name'  => "标题1",
			),
			array(
				'value' => 2,
				'name'  => "标题2",
			),
			array(
				'value' => 3,
				'name'  => "标题3",
			),
		); */

	//饼状图表
	public function pie_echart($param=null){
		$option['title'] = $param['title'];
		$option['tooltip'] = array(
			'trigger'   => "item",
			'formatter' => "{a} <br/>{b} : {c} ({d}%)",
		);
		$option['legend'] = array(
			'orient' => "vertical",
			'left' => $param["x_title"],
			'data' => $param['legend'],
		);
		$option['series'] = array(
			'name' => $param['name'],
			'type' => 'pie',
			'data' => $param['data']
		);
		echo 'var '.($param['option'] ? : 'option').'='.json_encode($option,JSON_UNESCAPED_UNICODE).';';

	}
}
