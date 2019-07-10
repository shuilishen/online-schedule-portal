<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/26
 * Time: 10:01
 */

namespace app\index\controller;


use app\common\controller\myCommonController;
use app\index\model\Calendars;
use app\index\model\Vacations;
use app\index\model\VacationTypes;

/**
 * Class Calendar
 * @package app\index\controller
 */
class Calendar extends myCommonController
{
    /**
     * @name    zhuye
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function index(){
        $calendars = Calendars::select();
        $vtypes = VacationTypes::select();

        $ovs = Vacations::where(['vtype' => [1, 2]])->select();
        $cvs = Vacations::where(['vtype' => [3, 4]])->select();

        $calendarOV=array();
        foreach ($ovs as $v){
            $ovdates=explode(',',$v['dates']);
            foreach ($ovdates as $d){
                $calendarOV[$d]=['title'=>$v['title'],'type'=>$v['vtype']];
            }
        }

        $calendarCV=array();
        foreach ($cvs as $v){
            $cvdates=explode(',',$v['dates']);
            foreach ($cvdates as $d){
                $calendarCV[$d]=['title'=>$v['title'],'type'=>$v['vtype']];
            }
        }

        $arr = array();
        foreach ($vtypes as $vtype){
            $arr[]=sprintf('%s:"%s"',$vtype['id'],$vtype['title']);
        }

        foreach ($calendars as $key=>$c){
            if($key == 0)
                $calendars[$key]['active'] = true;
            else
                $calendars[$key]['active'] = false;
        }

        $this->assign('vtypes',implode(',',$arr));

        $this->assign('calendarOV',json_encode($calendarOV));
        $this->assign('calendarCV',json_encode($calendarCV));

        $this->assign('tableOV', $ovs);
        $this->assign('tableCV', $cvs);

        $this->assign('calendars', $calendars);
        return $this->fetch();
    }

    public function add(){
        $vtypes = VacationTypes::select();
        $this->assign('vtypes', $vtypes);


        return $this->fetch();
    }


}