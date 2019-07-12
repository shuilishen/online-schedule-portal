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

    /**
     * @name    表格
     * @param $Pos_id
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function getTable($Pos_id)
    {
        $position = Positions::get($Pos_id);
        $Org = Orgs::get($position['Org_id']);

        $shifts = Shifts::where('Pos_id', $Pos_id)->select();

        $nextmonth = strtotime('first day of next month');

        $firstDayNM = date("Y-m-d", $nextmonth);
        $check = Paiban::where('date', $firstDayNM)->find();

        $paibanData = array();
        if(!$check)
        {
            // no data, initialize
            $startdate = $nextmonth;
            $enddate = strtotime('+1 month', $nextmonth);

            while ($startdate < $enddate) {
                foreach($shifts as $shift)
                {
                    $paibanData[] = [
                        'date' => date("Y-m-d", $startdate),
                        'Org_id' => $Org['id'],
                        'Pos_id' => $Pos_id,
                        'Shi_id' => $shift['id'],
                        'Mem_id' => ''
                    ];
                }
                $startdate = strtotime("+1 day", $startdate);
            }
            $paiban = new Paiban;
            $paiban->saveAll($paibanData);
        }

        $this->readPData($Pos_id);
    }

    private function readPData($Pos_id)
    {
        $paibanData = Paiban::where('Pos_id', $Pos_id)->select();
        $Shifts = Shifts::where('Pos_id', $Pos_id)->select();

        $Position = Positions::get($Pos_id);
        $Org = Orgs::get($Position['Org_id']);

        $tableHead = array();
        $tableData = array();

        $wday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        $nextmonth = strtotime('first day of next month');

        $startdate = $nextmonth;
        $enddate = strtotime('+1 month', $nextmonth);

        $cols = 0;
        $i = 0;
        $NumEOfShifts = 0;

        $tableHead[0][0] = "{field:'date', title:'日期', rowspan: 2, width:50, align: 'center'}";
        $tableHead[0][1] = "{field:'weekDay', title:'星期', rowspan: 2, width:50, align: 'center'}";
        $tableHead[0][2] = "{field:'org', title:'组织', rowspan: 2, width:50, align: 'center'}";


        while ($startdate < $enddate) {

            $dateIndex = getdate($startdate);
            $tableData[$i][] = "date: '".date("Y-m-d", $startdate)."'";
            $tableData[$i][] = "weekDay: '".$wday[$dateIndex['wday']]."'";
            $tableData[$i][] = "org: '".$Org['Org_N']."'";


            foreach ($Shifts as $key=>$Shift) {

                $EmployeeOfShift = Paiban::where('date', date("Y-m-d", $startdate))->where('Shi_id', $Shift['id'])->select();
                $NumEOfShifts += count($EmployeeOfShift);
                foreach ($EmployeeOfShift as $key2=>$value)
                {
                    $tableHead[1][$key+$key2] = "{field:'EID".($key+$key2)."', title:'".$Shift['SN']."<a href=\'javascript:;\' class=\'l-btn l-btn-small l-btn-plain\' group=\'\' id=\'\'><span class=\'l-btn-left l-btn-icon-left\'><span class=\'l-btn-text\'>Add</span><span class=\'l-btn-icon icon-add\'>&nbsp;</span></span></a>', align: 'center', width: 100}";


                    $Member = Members::get($value['Mem_id']);

                    $tableData[$i][] = "EID".($key+$key2).": '".$Member['name']."'";
                }
            }
            if($NumEOfShifts > $cols)
                $cols = $NumEOfShifts;
            $NumEOfShifts = 0;


            $tableData[$i] = '{'.implode(',',$tableData[$i]).'}';
            $startdate = strtotime("+1 day", $startdate);
            $i++;
        }


        $tableHead[0][3] = "{title:'".$Position['PN']."', colspan: ".$cols.", width:100}";

        $tableHead[0] = '['.implode(',', $tableHead[0]).']';
        $tableHead[1] = '['.implode(',', $tableHead[1]).']';

        $tableData = '['.implode(',', $tableData).']';
        $tableHead = '['.implode(',', $tableHead).']';


        $this->assign('tableHead', $tableHead);
        $this->assign('tableData', $tableData);
    }


//                $temp = getdate($startdate);


//                $newData[] = [
//                    'date' => date("Y-m-d", $startdate),
//                    'weekDay' => $wday[$temp['wday']],
//                    'Org_N' => $Org['Org_N']
//                ];

//            $startdate = strtotime('+20 days', $thismonth);
//            $enddate = $nextmonth;
//
//
//            //check if previous data exist
//            if(false)
//            {
//                //exist
//                while ($startdate < $enddate) {
//                    $temp = getdate($startdate);
//                    $preData[] = [
//                        'date' => date("Y-m-d", $startdate),
//                        'weekDay' => $wday[$temp['wday']],
//                        'Org_N' => $Org['Org_N']
//                    ];
//
//                    $startdate = strtotime("+1 day", $startdate);
//                }
//            }

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