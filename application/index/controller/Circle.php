<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 10:45
 */

namespace app\index\controller;


use app\common\controller\myCommonController;
use app\index\model\Calendars;
use app\index\model\Circles;
use app\index\model\Cshifts;
use app\index\model\Orgs;
use app\index\model\Shifts;
use think\Db;

class Circle extends myCommonController
{
    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $circles = Circles::order('id', 'ASC')->select();

        $cals = array();
        $calendars = Calendars::select();
        foreach ($calendars as $calendar) {
            $cals[$calendar['id']] = $calendar['title'];
        }

        $circleType = array();
        $ctypes = Db::table('data_ctypes')->select();
        foreach ($ctypes as $ctype) {
            $circleType[$ctype['id']] = $ctype['title'];
        }

        $organization = array();
        $orgs = Orgs::select();
        foreach ($orgs as $org) {
            $organization[$org['id']] = $org['Org_N'];
        }
        $this->assign('calendars', json_encode($cals));
        $this->assign('ctypes', json_encode($circleType));
        $this->assign('orgs', json_encode($organization));

        $this->assign('circles', $circles);

        return $this->fetch();
    }

    public function getTableData()
    {
        $circles = Circles::order('id', 'ASC')->select();
        return $circles;
    }

    public function editCirShift()
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '更新成功',
            'url' => '/index/Circle/editCirShift',
            'errorID' => null
        ];

        $cshiftId=$this->request->param('id',0,'intval');

        $data = Cshifts::find($cshiftId);
        if($data)
        {
            $data->order=input('post.order');
            $opt = $data->save();
            if(!$opt)
            {
                $state['msg'] = '更新失败';
                $state['code'] = 1;
            }
        }
        else
        {
            $state['msg'] = 'id不存在';
            $state['code'] = 1;
        }

        return json_encode($state);
    }

    /**
     * @param $key
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editAndAdd()
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '更新成功',
            'url' => '/index/Circle/index',
            'errorID' => null
        ];

        $circleId=$this->request->param('id',0,'intval');

        $data = Circles::find($circleId);

        if($data)
        {
            $data->Cal_id=input('post.Cal_id');     //question here  difference between input('post.xxx') and $this->request->post('', '')
            $data->Org_id=input('post.Org_id');
            $data->type=input('post.type');
            $data->title=input('post.title');

            $opt = $data->save();

            if(!$opt)
            {
                $state['msg'] = '更新失败';
                $state['code'] = 1;
            }
        }
        else
        {
            $state['msg'] = '添加成功！';

            $title=$this->request->post('title','');
            $Cal_id=$this->request->post('Cal_id','');
            $Org_id=$this->request->post('Org_id','');
            $type=$this->request->post('type','');

            $check = Db::table('data_ctypes')->where('id', $type)->find();

            if(Calendars::where('id', $Cal_id)->find() && Orgs::where('id', $Org_id)->find() && $check)
            {
                $circle = new Circles;
                $circle->title = $title;
                $circle->Cal_id = $Cal_id;
                $circle->Org_id = $Org_id;
                $circle->type = $type;

                $opt=$circle->save();
                if(!$opt)
                {
                    $state['code'] = 0;
                    $state['msg'] = '添加失败！';
                }
            }
            else{
                $state['code'] = 0;
                $state['msg'] = '添加失败！';
            }
        }
        return json_encode($state);
    }

    public function cirDel($key)
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '',
            'url' => '/index/Circle/cirShiftsDel',
            'errorID' => null
        ];

        foreach ($key as $id)
        {
            if($id>0)
            {
                $data = Db::table('data_circles')->delete($id);
                if (!$data) {
                    $state['code'] = 0;
                    $state['errorID'][] = $id;
                }
            }else{
                $state['code'] = 0;
                $state['errorID'][] = $id;
            }
        }

        if($state['code'] == 0)
            $state['msg'] = '部分规则删除失败！';
        else
        {
            $state['msg'] = '删除成功！';
        }
        return json_encode($state);
    }


    public function manageCirShifts()
    {
        return $this->fetch('manageCirShifts');
    }


    public function getCirShiftsData($key)
    {
        $cshifts = Db::table('data_cdetail')->where('cid', $key)->order('order', 'ASC')->select();
        $shiftData = array();
        foreach ($cshifts as $k => $cshift) {
            $shiftData[$k] = Shifts::get($cshift['sid']);
            $shiftData[$k]['id'] =  $cshift['id'];
            $shiftData[$k]['sid'] =  $cshift['sid'];
            $shiftData[$k]['order'] = $cshift['order'];
        }

        return $shiftData;
    }


    public function getLayerData($key)
    {
        $shifts = Shifts::order('id', 'ASC')->select();
        $cshifts = Db::table('data_cdetail')->where('cid', $key)->order('order', 'ASC')->select();

        $shiftData = array();
        foreach ($cshifts as $k => $cshift) {
            $shiftData[$k] = Shifts::get($cshift['sid']);
            $shiftData[$k]['order'] = $cshift['order'];
        }

        $shiftList = array();
        foreach ($shifts as $shift) {
            $shiftList[$shift['id']] = [
                'SN' => $shift['SN'],
                'SC' => $shift['SC'],
                'Time' => $shift['Time'],
                'duration' => $shift['duration']
            ];
        }

        $data = [
            'allShifts' => $shifts,
            'cshifts' => $shiftData,
            'shiftList' => $shiftList
        ];

        return json_encode($data);
    }

    public function addShiftsToCircle($circle, $shiftIds)
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '新增成功',
            'url' => '/index/Circle/addShiftsToCircle',
            'errorID' => null
        ];

        $shifts = Db::table('data_cdetail')->where('cid', $circle)->order('order', 'ASC')->select();

        $index = 0;
        $data = array();
        if($shifts == [])
        {
            foreach ($shiftIds as $key => $shiftId) {
                $data[$key]['sid'] = $shiftId;
                $data[$key]['cid'] = $circle;
                $data[$key]['order'] = $index;
                $index++;
            }
        }
        else
        {
            $index = Db::table('data_cdetail')->where('cid', $circle)->max('order') + 1;
            foreach ($shiftIds as $key => $shiftId) {
                $data[$key]['sid'] = $shiftId;
                $data[$key]['cid'] = $circle;
                $data[$key]['order'] = $index;
                $index++;
            }
        }

        $check = Db::table('data_cdetail')->insertAll($data);
        if($check == false)
        {
            $state['code'] = 0;
            $state['msg'] = '新增失败';
        }

        return json_encode($state);
    }

    public function cirShiftsDel($key)
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '',
            'url' => '/index/Circle/cirShiftsDel',
            'errorID' => null
        ];

        foreach ($key as $id)
        {
            if($id>0)
            {
                $data = Db::table('data_cdetail')->delete($id);
                if (!$data) {
                    $state['code'] = 0;
                    $state['errorID'][] = $id;
                }
            }else{
                $state['code'] = 0;
                $state['errorID'][] = $id;
            }
        }

        if($state['code'] == 0)
            $state['msg'] = '部分班别删除失败！';
        else
        {
            $state['msg'] = '删除成功！';
        }
        return json_encode($state);
    }
}
