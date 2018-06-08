<?php
namespace Common\Model;
use Think\Model\ViewModel;
class RefundActivityViewModel extends ViewModel {
    protected $tableName    =   'activity_participate';
    
    public $viewFields      =   [
        'activity_participate'  =>  ['*'],
        'activity'              =>  ['full_value' => 'fullValue', '_no' => 'activity_participate.activity_id = activity.id AND activity.full_money > 0'],
    ];
}