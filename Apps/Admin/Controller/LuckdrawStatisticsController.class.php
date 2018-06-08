<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class LuckdrawStatisticsController extends CommonModulesController {
	

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();
        //初始化表单模板

    }

    /**
    * 列表
    */
    public function index($param=null){
    	$start7 = date('Y-m-d',time()-86400*7);
		$start1 = date('Y-m-d',time()-86400);
		$end = date('Y-m-d',time());

        $yesterday = date('Y-m-d',time()-86400);
        $where['_string'] = 'date_format(`day`,"%Y-%m-%d") = "'.$yesterday.'"';

        //读取基本数据
        $res = M('luckdraw_statistics')->where($where)->find();
        $this->assign('yes_data',$res);
		
        //中奖奖品比例
        $res = M('luckdraw_prize_statistics')->field('prize_name,winning_num')->where($where)->order('id desc')->select();
		$winning_list = $res;
		$this->assign('winning_list',$winning_list);
		
		//领奖奖品比例
		$where['prize_name'] = ['notlike','%积分%'];
        $res = M('luckdraw_prize_statistics')->field('prize_name,receive_num')->where($where)->order('id desc')->select();
		$receive_list = $res;
		$this->assign('receive_list',$receive_list);
		

        //统计总数据
        $res = M('luckdraw_statistics')->field('sum(`luck_num`) as luck_num,sum(`use_tangbao`) as use_tangbao,sum(`winning_num`) as winning_num,sum(`winning_score`) as winning_score,sum(`entity_winning_num`) as entity_winning_num,sum(`entity_receive_num`) as entity_receive_num,sum(`pay_express_price`) as pay_express_price')->find();
        $res['entity_receive_percen'] = round(($res['entity_receive_num']/$res['entity_winning_num']*100),2);

        $this->assign('total_data',$res);
        $this->display();
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}
}