<?php
namespace Common\Model;
use Think\Model\AdvModel;

class ShopMakeTemplates2Model extends AdvModel
{

	protected $tableName = 'shop_make_templates';

	protected $_validate = array(
		array('status','require','审核状态不能为空!',1,'regex',3), 
		array('tpl_name','require','模板名称不能为空!',1,'regex',3), 
		array('tpl_url','require','模板目录不能为空!',1,'regex',3), 
		array('cfg','require','全局配置不能为空!',1,'regex',3), 
		array('cfg_box','require','单元配置不能为空!',1,'regex',3), 
		array('images','require','缩略图不能为空!',1,'regex',3), 
		array('styles','require','风格不能为空!',1,'regex',3), 
		array('uid','require','用户ID不能为空!',1,'regex',3), 
		array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
		array('templates_id','require','模板ID不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}