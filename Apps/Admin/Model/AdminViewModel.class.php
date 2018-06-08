<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class AdminViewModel extends ViewModel {
    public $viewFields = array(
        'admin'=>array('*'),
        'admin_sort'=>array('group_name','menuid','action', '_on'=>'admin.sid=admin_sort.id'),
    );
}

?>