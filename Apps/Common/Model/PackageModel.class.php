<?php
namespace Common\Model;
use Think\Model;
class PackageModel extends Model {
    protected $tableName   =   'goods_package';
    
    protected $_validate    =   [
        ['package_name', 'require', '包装模板名称不能为空', 1, 'regex', 3],
        ['content', 'require', '包装模板内容不能为空', 1],
    ];
    /*
    protected $_auto        =   [
        ['ip', 'get_client_ip', 1, 'function'],
        ['uid', 'getUid', 1, 'function'],
    ];
	*/
	protected $_auto        =   [
        ['ip', 'get_client_ip', 1, 'function'],
        ['uid', 'getSellerId', 1, 'callback'],
    ];
	
	
	/**
     * 获取买家ID
     *
     * @return mixed
     */
    public function getSellerId($data) {
		if($data){
			return $data;
		}else{
			return getUid();
		}
        
    }
}