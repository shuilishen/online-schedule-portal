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
use app\index\model\Circles;
use app\index\model\Members;
use app\index\model\Orgs;
use app\index\model\Shifts;
use app\paiban\model\Paiban;
use think\Db;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
//        $EID = session('uid');
//        $user = Users::where('EID', $EID)->find();

        $members = Members::select();
        $memData = array();
        foreach ($members as $member) {
            $memData[$member['id']] = [
                'name' => $member['name']
            ];
        }
        $this->assign('members', json_encode($memData));

        $org1 = $this->getSelections(1);
        $this->assign('org1', json_encode($org1));

        return $this->fetch();
    }

    public function getSelections($key)
    {
        $orgs = Orgs::order('id', 'ASC')->where('parent_id', $key)->select();

        $selections = '<option value="">请选择</option>';
        foreach ($orgs as $org) {
            $selections .= '<option value='.$org['id'].'>'.$org['Org_N'].'</option>';
        }
        return $selections;
    }

    public function getCircleArray($circleId)
    {
        $circle = Circles::get($circleId);
        $check = false;
        if($circle['type'] == 1)
            $check = true;
        $shifts = Db::table('data_cdetail')->where('cid', $circleId)->order('order', 'ASC')->select();

        $arr = array();
        foreach ($shifts as $shift) {
            $arr[] = $shift['sid'];
        }

        $data = [
            'circle' => $arr,
            'special' => $check
        ];
        return json_encode($data);
    }


    public function getSelectionAndTables($key, $YM)
    {
        $orgs = Orgs::order('id', 'ASC')->where('parent_id', $key)->select();
        $selections = array();
        $selections[] = '<option value="">请选择</option>';
        foreach ($orgs as $org) {
            $selections[] = '<option value='.$org['id'].'>'.$org['Org_N'].'</option>';
        }

        $tableData = $this->getTables($key, $YM);

        $circles = Circles::where('Org_id', $key)->order('id', 'ASC')->select();

        $radios = array();
        $radios[] = '<input type="radio" name = "circle" value = 0 title="普通" checked>';
        foreach ($circles as $circle)
        {
            $radios[] = '<input type="radio" name = "circle" value='.$circle['id'].' title="'.$circle['title'].'">';
        }

        $data = [
            'circle' => $radios,
            'org' => $selections
        ];

        return json_encode(array_merge($data, $tableData));
    }

    public function getTables($key, $YM)
    {
        $colModel = array();
        $colModel[] = [
            'label' => '日期',
            'name' => 'date',
            'width' => 100,
            'align' => 'center',
            'frozen' => 'truth'
        ];
        $colModel[] = [
            'label' => '星期',
            'name' => 'weekDay',
            'width' => 70,
            'align' => 'center',
            'frozen' => 'truth'
        ];
        $shiftIds = Db::table('data_oshifts')->where('Org_id', $key)->order('order', 'ASC')->select();

        $firstC = null;
        foreach ($shiftIds as $i => $sid) {
            if($i == 0)
                $firstC = $sid['sid'];
            $shift = Shifts::get($sid['sid']);
            $colModel[] = [
                'label' => $shift['SN'],
                'name' => $sid['sid'],
                'align' => 'center',
                'formatter' => 'memberFormatter',
                'cellattr' => 'addMyClassToCell'
            ];
        }

        $org = Orgs::get($key);
        $groupHeaders = [
            'numberOfColumns' => count($shiftIds),
            'titleText' => $org['Org_N'],
            'startColumnName' => $firstC
        ];

        $workflowData = $this->getWorkflowData($key, $YM);
        $memListData = $this->getMemListData($key, $YM);

        $data = [
            'data' => $workflowData,
            'memListData' => $memListData,
            'colModel' => $colModel,
            'groupHeaders' => $groupHeaders
        ];

        return $data;
    }

    /**
     * @param $key
     * @param $YM
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemListData($key, $YM){
        $members = Members::where('oid', $key)->order('id', 'ASC')->select();

        $nextmonth = strtotime($YM);
        $startdate = $nextmonth;
        $enddate = strtotime('+1 month', $nextmonth);

        foreach ($members as $i => $member)
        {
            $workflowOfMem = Paiban::where('date', '>=', date("Y-m-d", $startdate))
                ->where('date', '<', date("Y-m-d", $enddate))
                ->where('Mem_id', $member['id'])->where('Shi_id','<>', '45')->order('date', 'ASC')->select();

            $members[$i]['days']=count($workflowOfMem);
        }

        return $members;
    }

    /**
     * @param $key
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWorkflowData($key, $YM)
    {
        $wday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];

        $nextmonth = strtotime($YM);
        $startdate = $nextmonth;
        $enddate = strtotime('+1 month', $nextmonth);

        $shiftIds = Db::table('data_oshifts')->where('Org_id', $key)->order('order', 'ASC')->select();

        $index = 0;
        $tableData = array();
        while ($startdate < $enddate)
        {
            $dateIndex = getdate($startdate);

            $tableData[$index] = [
                'date' => date("Y-m-d", $startdate),
                'weekDay' => $wday[$dateIndex['wday']]
            ];

            foreach ($shiftIds as $i => $sid) {     //problem here
                $memId = Db::table('app_paiban')
                    ->where('Org_id', $key)->where('date', date("Y-m-d", $startdate))
                    ->where('Shi_id', $sid['sid'])->field('Mem_id')->select();

                $arr = array_column($memId, 'Mem_id');

                $tableData[$index][$sid['sid']] = $arr;
            }
            $index++;
            $startdate = strtotime("+1 day", $startdate);
        }
        return $tableData;
    }

    public function targetTablePage()
    {
        return $this->fetch('targetTablePage');
    }

    /**
     * @param $YM
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTargetTableData($YM)
    {
        $wday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        $startdate = strtotime($YM);
        $enddate = strtotime('+1 month', $startdate);

        $colModel = array();
        $colNames = array();
        $groupHeaders = array();
        $shiftData = array();

        $colModel[] = [
            'label' => 'Org_C',
            'name' => 'Org_C',
            'align' => 'center',
            'formatter' => ''
        ];

        $colModel[] = [
            'label' => 'Org_N',
            'name' => 'Org_N',
            'align' => 'center',
            'formatter' => ''
        ];

        $colModel[] = [
            'label' => 'eid',
            'name' => 'eid',
            'align' => 'center',
            'formatter' => ''
        ];

        $colModel[] = [
            'label' => 'name',
            'name' => 'name',
            'align' => 'center',
            'formatter' => ''
        ];

        $colNames[] = '组织代码(Organization Code)';

        $colNames[] = '组织名称(Organization Name)';

        $colNames[] = '工号(Employee ID)';

        $colNames[] = '姓名(Name)';

        while ($startdate < $enddate)
        {
            $colModel[] = [
                'label' => 'date',
                'name' => date("Y-m-d", $startdate),
                'align' => 'center',
                'formatter' => ''
            ];

            $groupHeaders[] = [
                'numberOfColumns' => 1,
                'titleText' => date("Y-m-d ", $startdate).$wday[date('w', $startdate)],    //DATE()
                'startColumnName' => date("Y-m-d", $startdate)
            ];

            $colNames[] = '班别代码(Shift Code)';
            $startdate = strtotime("+1 day", $startdate);
        }


        $members = Members::order('oid', 'ASC')->select();
        $index = 0;
        foreach ($members as $member) {
            $startdate = strtotime($YM);

            $check = Paiban::where('date', '>=', date("Y-m-d", $startdate))
                ->where('date', '<', date("Y-m-d", $enddate))
                ->where('Mem_id', $member['id'])->order('date', 'ASC')->find();

            if($check)
            {
                $workflowOfMem = Paiban::where('date', '>=', date("Y-m-d", $startdate))
                    ->where('date', '<', date("Y-m-d", $enddate))
                    ->where('Mem_id', $member['id'])->order('date', 'ASC')->select();

                $wListOfMem = array();
                foreach ($workflowOfMem as $item) {
                    $shift = Shifts::get($item['Shi_id']);
                    $wListOfMem[$item['date']] = $shift['SC'];
                }

                $org = Orgs::get($member['oid']);

                $shiftData[$index]['Org_C'] = $org['Org_C'];
                $shiftData[$index]['Org_N'] = $org['Org_N'];
                $shiftData[$index]['eid'] = $member['eid'];
                $shiftData[$index]['name'] = $member['name'];

                $startdate = strtotime($YM);
                while ($startdate < $enddate)
                {
                    $dateString = date("Y-m-d", $startdate);

                    if(array_key_exists($dateString, $wListOfMem))
                        $shiftData[$index][$dateString] = $wListOfMem[$dateString];
                    else
                        $shiftData[$index][$dateString] = 'OFF';
                    $startdate = strtotime("+1 day", $startdate);
                }
                $index++;
            }
        }

        $data = [
            'data' => $shiftData,
            'colModel' => $colModel,
            'colNames' => $colNames,
            'groupHeaders' => $groupHeaders
        ];

        return json_encode($data);
    }

    /**
     * @param $YM
     * @param $org
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleteAll($YM, $org)
    {
        $startdate = strtotime($YM);
        $enddate = strtotime('+1 month', $startdate);

        Paiban::where('Org_id', $org)->where('date', '>=', date("Y-m-d", $startdate))
            ->where('date', '<', date("Y-m-d", $enddate))->delete();
    }

    /**
     * @param $data
     * @param $org
     * @throws \Exception
     */
    public function saveWorkflowData($data, $org)
    {
        $this->deleteAll($data[0]['date'], $org);

        $workflow = new Paiban;
        $saveList = array();
        foreach ($data as $rowData)
        {
            $date = $rowData['date'];
            foreach ($rowData as $key => $cellData)
            {
                if($key !== 'weekDay' && $key !== 'date')
                {
                    $sid = $key;
                    foreach ($cellData as $memId)
                    {
                        $saveList[] = [
                            'date' => $date,
                            'Org_id' => $org,
                            'Shi_id' => $sid,
                            'Mem_id' => $memId
                        ];
                    }
                }
            }
        }
        $workflow->saveAll($saveList);
        exit;
    }
}
