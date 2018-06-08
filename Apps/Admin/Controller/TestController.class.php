<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TestController extends CommonModulesController {
	public function test(){
		set_time_limit(0);
		$do=M('goods_category');
		$list=$do->select();

		foreach($list as $val){
			if($do->where(array('sid'=>$val['id']))->count()==0){
				dump($val);
				/*
				M('goods_param_group')->add(array(
						'ip'			=>get_client_ip(),
						'status'		=>1,
						'group_name'	=>'商品参数',
						'category_id'		=>$val['id']
					));

				$insid=M('goods_param_group')->getLastInsID();

				M('goods_param_group_option')->add(array(
						'ip'			=>get_client_ip(),
						'param_name'	=>'商品编号',
						'type'			=>3,
						'group_id'		=>$insid
					));

				*/

				M('goods_attr')->add(array(
						'ip'	=>get_client_ip(),
						'attr_name'=>'属性',
						'category_id'=>$val['id']
					));

				$insid=M('goods_attr')->getLastInsID();

				for($i=1;$i<11;$i++){
					M('goods_attr')->add(array(
							'ip'	=>get_client_ip(),
							'attr_name'=>'属性'.$i,
							'sid'=>$insid
						));					
				}
			}
		}

	}
	
	//将goods_attr_value更新到goods_attr_list
	public function update_attr_list() {
		$list = M('goods_attr_list')->field('id,goods_id,attr,attr_name,attr_id')->select();
		
		foreach($list as $k=>$v){
			$attr_name = '';
			$attr_id = '';
			$attr = '';
			
			$attrs = explode(',',$v['attr_id']);
			foreach($attrs as $ke=>$va){
				$ids = explode(':',$va);
				$map['goods_id'] = $v['goods_id'];
				$map['attr_id'] = $ids[0];
				$map['option_id'] = $ids[1];
				$res = M('goods_attr_value')->field('attr_value,attr_id,option_id')->where($map)->find();
				$attr_name .= $res['attr_value'].',';
				$attr_id .= $res['attr_id'].':'.$res['option_id'].',';
				$attr .= $res['attr_id'].':'.$res['option_id'].':'.$res['attr_value'].',';
			}
			
			$data['attr'] = substr($attr,0,-1);
			$data['attr_name'] = substr($attr_name,0,-1);
			$data['attr_id'] = substr($attr_id,0,-1);
			
			if($data['attr'] != $v['attr']){
				M('goods_attr_list')->where('id = '.$v['id'])->data($data)->save();
				dump($v);
				dump($data);
				//exit();
			}
		}

    }

    public function add_field(){
        set_time_limit(0);
        $do=\Think\Db::getInstance();
        $list = $do->getTables();

        foreach($list as $val){
            $sql = "ALTER TABLE `".$val."` ADD COLUMN `appid` INT NOT NULL COMMENT '应用ID';";
            echo $sql.'<br>';
            $sql = "ALTER TABLE `".$val."` ADD INDEX `appid` (`appid`);";
            echo $sql.'<br><br>';
            //$do->execute($sql);
        }


    }

    /**
     * 签到，检测用户名
     */
    public function luck_cd(){
        $count = M('luckdraw_chance')->group('uid')->count();
        $page = ceil($count/1000);


        $p=I('get.p') ? I('get.p') : 1;
        $list =M('luckdraw_chance')->where($map)->page($p)->limit(1000)->order('id asc')->group('uid')->getField('uid',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        foreach($list as $val){
            $id = M('user')->where(['id' => $val])->getField('id');
            if($id){
                //dump($id);
            }else{
                echo $val.'-error<br>';

                M('luckdraw_chance')->where(['uid' => $val])->delete();
            }
        }

        usleep(1000);
        //gourl('/Test/luck_cd/p/'.($p+1));
    }
}