<?php
namespace Common\Model;
use Think\Model\AdvModel;

class ShopMakeTemplatesModel extends AdvModel
{

	protected $tableName = 'shop_make_templates';

	protected $serializeField = array(
		'cfg' 		=> array('bgcolor','fixed','bgimages','style'),
		'cfg_box'	=> array('cell_is_border','cell_border_color','cell_bgcolor','cell_bgimages','cell_text_color','cell_text_size','cell_title_bgcolor','cell_title_bgimages','cell_title_color','cell_margin_top','cell_margin_bottom','cell_style','img_b','img_m','img_s','img_xs'),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}