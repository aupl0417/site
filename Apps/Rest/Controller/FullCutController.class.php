<?php
namespace Rest\Controller;

class FullCutController extends CommonController
{




	/**
	 * 获取所有满减活动列表
	 * @param int $device 平台
	 * @param int $category_id 分类表id，多个用逗号隔开
	 */
	public function lists(){

        $this->need_param = ['device','category_id'];

        $this->_need_param();
        $this->_check_sign();

        switch ($device = I('device','','int')) {
        	case 1:
        		# pc
        		break;
        	case 2:
        		# wap
        		break;
        	default:
        		$this->apiReturn(3);
        		break;
        }

        $where = array(
        	'status'	=> 1,
        );
        # $relationWhere = "device=$device and status=1";
        $category = trim(I('category_id'));
        if(strpos($category, ',')){
        	$where['id'] = array('in',explode(",", $category));
        }else{
        	$where['id'] = $category;
        }
        
        # $list = D('Common/FullCutCategoryRelation')->relation(true)->relationOrder('full_cut','sort asc')->relationWhere('full_cut',$relationWhere)->where($where)->field('atime,etime,ip', true)->select();
        $list = D('Common/FullCutCategoryRelation')->cache(true,1800)->relation(true)->where($where)->field('atime,etime,ip', true)->select();

        if($list){
        	$result = [];
        	foreach($list as $key => $value){
        		$value['remark'] = html_entity_decode($value['remark']);
        		$fullcut 	= [];
        		$sort 		= [];
        		foreach($value['fullcut'] as $k => $v){
        			# 手动relationWhere
        			if($v['device'] == $device && $v['status'] == 1){
        				# 手动sort 排序
        				$sort[$k] = $v['sort'];
        			}
        		}
        		# 手动sort 排序
        		asort($sort);
        		foreach ($sort as $k1 => $v1) {
        			# 生成pc的店铺链接
        			$shop_id = $value['fullcut'][$k1]['shop_id'];
        			$value['fullcut'][$k1]['pcUrl'] = shop_url($shop_id,M('shop')->cache(true,3600)->where(['id'=>$shop_id])->getField('domain'));

        			$fullcut[] = $value['fullcut'][$k1];
        		}
        		$value['fullcut'] = array_values($fullcut);
        		# 把id作为key
        		$result[$value['id']] = $value;
        	}
        	$this->apiReturn(1, ['data' => $result]);
        }else{
        	$this->apiReturn(3);
        }
	}

	



















}









