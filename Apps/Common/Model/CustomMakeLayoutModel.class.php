<?php
namespace Common\Model;
use Think\Model;

class CustomMakeLayoutModel extends Model
{

	protected $tableName = 'custom_make_layout';
	protected $_validate = array(
        array('layout_name','require','布局名称不能为空!',1,'regex',3),
        array('layout_type','require','布局类型不能为空!',1,'regex',3),
        array('col','require','列数不能为空!',1,'regex',3),
        array('page','require','页面不能为空！!',1,'regex',3),
        array('domain','require','域名前缀不能为空！!',1,'regex',3),
        array('layout_id','require','布局ID不能为空!',1,'regex',3),
        array('col_0','require','单元格第一列参数不能为空！!',1,'regex',3),
        array('col_1','require','单元格第二列参数不能为空！!',1,'regex',3),
        array('col_2','require','单元格第三列参数不能为空！',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}