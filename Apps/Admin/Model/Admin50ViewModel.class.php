<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Admin50ViewModel extends ViewModel {
    public $viewFields = array(
        'admin'=>array('*'),
        'admin_sort'=>array('group_name','menuid','action', '_on'=>'admin.sid=admin_sort.id'),
    );
}
?>