<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/24
 * Time: 15:29
 */

namespace app\paiban\controller;

use app\common\controller\myCommonController;
use app\index\model\Members;
use app\index\model\Orgs;
use app\index\model\Postions;
use app\index\model\Shifts;
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
    public function index(){
//        $firstDayNMonth = date("Y-m-d", strtotime('first day of next month'));
//        $check = Db::table('paiban')->where('date', $firstDayNMonth)->find();

//        $thismonth = strtotime('first day of this month');
//        $startdate = strtotime('+20 days', $thismonth);
//        $enddate = strtotime('+2 month', $thismonth);
//
//
//        $dates = array();
//        $weekDay = array();
//        $wday = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];
//        while ($startdate < $enddate) {
//            $dates[]['date'] = date("Y-m-d", $startdate);
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
//            $paiban = new Paiban;
//            Db::name('paiban')->insertAll($dates);
//
//        }
//        else
//        {
//            //load data
//            myHalt($check);
//        }
        // assign variables



        $EID=session('uid');
        $user = Members::where('Emp_id', $EID)->find();
        $org = Orgs::where('id', $user['Org_id'])->find();

        $org_members = Members::where('Org_id', $user['Org_id'])->select();

        $posts = Postions::where('Org_id', $user['Org_id'])->select();

        $shifts = array();
        foreach ($posts as $key=>$post) {
            $shifts[$post['PN']] = Shifts::where('Pos_id', $post['id'])->select();
            $posts[$key]['shiftCount'] = sizeof($shifts[$post['PN']]);

        }

        $thismonth = strtotime('first day of this month');
        $startdate = strtotime('+20 days', $thismonth);
        $enddate = strtotime('+2 month', $thismonth);

        $dates = array();
        $week = array();
        $wday = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];
        while ($startdate < $enddate) {
            $dates[] = date("Y-m-d", $startdate);
            $temp = getdate($startdate);
            $week[] = $wday[$temp['wday']];
            $startdate = strtotime("+1 day", $startdate);
        }

        $fixedInfo = array();
        $draggableInfo = array();
        foreach ($dates as $key=>$date) {
            $fixedInfo[$key]['date'] = $date;
            $fixedInfo[$key]['week'] = $week[$key];
            $fixedInfo[$key]['Org_N'] = $org['Org_N'];

            foreach ($shifts as $key2=>$shift)
            {
                foreach ($shift as $key3=>$s){
                    $draggableInfo[$key][] = '';
                }

            }
        }

        $this->assign('posts',$posts);

        $this->assign('shifts', $shifts);
        $this->assign('shifts2', str_replace("\\r\\n","",json_encode($shifts)));
        $this->assign('fixedInfo', $fixedInfo);
        $this->assign('draggableInfo',$draggableInfo);

        return $this->fetch();
    }

    /**
     * @name 表格
     */
    public function getTable()
    {
        $id=$this->request->param('id',0,'intval');
    }
}