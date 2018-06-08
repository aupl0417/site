<?php
namespace Common\Model;
use Think\Model\ViewModel;
class ActivityParticipateViewModel extends ViewModel {
    protected $tableName    =   'activity_participate';
    public $viewFields      =   [
        'activity_participate'  =>  ['atime','uid','id','activity_id','status', 's_no', 'calc_before_money', 'calc_after_money', 'full_value', 'full_money', 'remark'],
        'user'                  =>  ['nick', '_on' => 'activity_participate.uid = user.id'],
    ];
}