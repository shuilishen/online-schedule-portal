<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/24
 * Time: 15:29
 */

namespace app\paiban\controller;

use app\auth\model\Users;
use app\common\controller\myCommonController;
use app\index\model\Members;
use app\index\model\Orgs;
use app\index\model\Positions;
use app\index\model\Shifts;
use app\paiban\model\Paiban;
use think\Db;

class Index extends myCommonController
{
    /**
     * @name    主页
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     *
     */
    public function index()
    {
        $EID = session('uid');
        $user = Users::where('EID', $EID)->find();

        $positions = Positions::where('Org_id', $user['Man_Org_id'])->select();
        $defaultPosition = Positions::where('Org_id', $user['Man_Org_id'])->find();
        $this->assign('default_PID', $defaultPosition['id']);
        $this->assign('positions', $positions);
        $this->getTable($defaultPosition['id']);

        return $this->fetch();
    }

    public function getTable($Pos_id)
    {
        //$position = Positions::where('id', $Pos_id)->select();
        $position = Positions::get($Pos_id);
        $Org = Orgs::get($position['Org_id']);

        $shifts = Shifts::where('Pos_id', $Pos_id)->select();

        $thismonth = strtotime('first day of this month');
        $nextmonth = strtotime('first day of next month');
        //myHalt(date("Y-m-d", $nextmonth));

        $firstDayNM = date("Y-m-d", $nextmonth);
        $check = Paiban::where('date', $firstDayNM)->find();

        $wday = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];

        $paibanData = array();
        $preData = array();
        $newData = array();
        if(!$check)
        {
            // no data, load empty table
            $startdate = $nextmonth;
            $enddate = strtotime('+1 month', $nextmonth);


            while ($startdate < $enddate) {
                $temp = getdate($startdate);

                $paibanData[] = [
                    'date' => date("Y-m-d", $startdate),
                    'Org_id' => $Org['id'],
                    'Pos_id' => $Pos_id,
                    'Shi_id' => '',
                    'Mem_id' => ''
                ];

                $newData[] = [
                    'date' => date("Y-m-d", $startdate),
                    'weekDay' => $wday[$temp['wday']],
                    'Org_N' => $Org['Org_N']
                ];

                $startdate = strtotime("+1 day", $startdate);
            }
            //save
//            $paiban = new Paiban;
//            $paiban->saveAll($pdata);

            $startdate = strtotime('+20 days', $thismonth);
            $enddate = strtotime($nextmonth);


            //check if previous data exist
            if(false)
            {
                //exist
                while ($startdate < $enddate) {
                    $preData[] = [
                        'date' => date("Y-m-d", $startdate),
                        'weekDay' => $wday[$temp['wday']],
                        'Org_N' => $Org['Org_N']
                    ];

                    $startdate = strtotime("+1 day", $startdate);
                }
            }
        }
        else
        {

        }

        $this->assign('displayData', $newData);
        return json_encode($Pos_id);
    }

//
//        $thismonth = strtotime('first day of this month');
//        $nextmonth = strtotime('first day of next month');
//        $startdate = strtotime('+20 days', $thismonth);
//        $enddate = strtotime('+2 month', $thismonth);
//
//        $firstDayNM = date("Y-m-d", $nextmonth);
//        $check = Paiban::where('date', $firstDayNM)->find();
//
//
//        $dates = array();
//
//
//        while ($startdate < $enddate) {
//            $displayDates[] = date("Y-m-d", $startdate);
//            $dates[] = date("Y-m-d", $startdate);
//
//            $temp = getdate($startdate);
//            $weekDay[] = $wday[$temp['wday']];
//
//            $startdate = strtotime("+1 day", $startdate);
//        }
//
//        if(!$check)
//        {
//            //create data for this month
//
//
//            $newlist = array();
//            $startdate = strtotime($nextmonth);
//            $enddate = strtotime('+1 month', $nextmonth);
//            while ($startdate < $enddate) {
//                $newlist[]['date'] = date("Y-m-d", $startdate);
//                $newlist[]['Org_id']
//            }
//
//
//
//
//        }
//        else
//        {
//            //load data
//            myHalt($check);
//        }
//        // assign variables
//
//
//
//
//
//        $org = Orgs::where('id', $user['Org_id'])->find();
//
//        $org_members = Members::where('Org_id', $user['Org_id'])->select();
//
//        $positions = Positions::where('Org_id', $user['Org_id'])->select();
//
//        $shifts = array();
//        foreach ($positions as $key=>$position) {
//            $shifts[$position['PN']] = Shifts::where('Pos_id', $position['id'])->select();
//            $positions[$key]['shiftCount'] = sizeof($shifts[$position['PN']]);
//
//        }
//
//        $thismonth = strtotime('first day of this month');
//        $startdate = strtotime('+20 days', $thismonth);
//        $enddate = strtotime('+2 month', $thismonth);
//
//        $dates = array();
//        $week = array();
//        $wday = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];
//        while ($startdate < $enddate) {
//            $dates[] = date("Y-m-d", $startdate);
//            $temp = getdate($startdate);
//            $week[] = $wday[$temp['wday']];
//            $startdate = strtotime("+1 day", $startdate);
//        }
//
//        $fixedInfo = array();
//        $draggableInfo = array();
//        foreach ($dates as $key=>$date) {
//            $fixedInfo[$key]['date'] = $date;
//            $fixedInfo[$key]['week'] = $week[$key];
//            $fixedInfo[$key]['Org_N'] = $org['Org_N'];
//
//            foreach ($shifts as $key2=>$shift)
//            {
//                foreach ($shift as $key3=>$s){
//                    $draggableInfo[$key][] = '';
//                }
//
//            }
//        }
//
//
//
//        $this->assign('shifts', $shifts);
//        $this->assign('shifts2', str_replace("\\r\\n","",json_encode($shifts)));
//        $this->assign('fixedInfo', $fixedInfo);
//        $this->assign('draggableInfo',$draggableInfo);
//
//        return $this->fetch();


}