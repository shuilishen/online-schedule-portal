<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 15:02
 */

namespace app\index\controller;


use app\common\controller\myCommonController;
use app\index\model\Orgs;
use app\index\model\Oshifts;
use app\index\model\Shifts;
use think\db;

class Manageorg extends myCommonController
{
    public function index()
    {
        $orgs = Orgs::select();
        $first_org = array();
        foreach ($orgs as $key => $value)
        {
            if($value['parent_id'] == 1)
            {
                $first_org[] = $value;
            }
        }

        $shifts = Shifts::order('id', 'ASC')->select();

        $shiftList = array();
        foreach ($shifts as $shift) {
            $shiftList[$shift['id']] = $shift['SN'];
        }

        $this->assign('shifts', json_encode($shiftList));
        $this->assign('first_org', $first_org);

        return $this->fetch();
    }

    /**
     * @param $key
     * @return string
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    public function getOrgs($key)
    {
        $orgs= Orgs::where('parent_id', $key)->select();

        $str='<option value="">请选择</option>';

        foreach ($orgs as $org)
        {
            if($org['parent_id'] == $key)
            {
                $str.= '<option value='.$org['id'].'>'.$org['Org_N'].'</option>';
            }
        }

        $data = [
            'html' => $str
        ];
        return json_encode($data);
    }

    /**
     * @param $key
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    public function getShiftsTable($key)
    {
        //return $key;
        if($key == null)
            $shifts = [];
        else
            $shifts = Db::table('data_oshifts')->where('Org_id', $key)
            ->order('order', 'ASC')->select();
        return $shifts;
    }

    /**
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    public function getAllShifts()
    {
        return Shifts::order('id', 'ASC')->select();
    }


    /**
     * @param $org
     * @param $shifts
     * @return string
     * @throws \Exception
     */
    public function addShiftsToOrg($org, $shifts)
    {
        $data = array();
        $oshifts = new Oshifts;

        $order = Oshifts::where('Org_id', $org)->max('order');
        $order++;
        foreach ($shifts as $shift) {
            if(Oshifts::where('Org_id', $org)->where('sid', $shift)->find())
                continue;
            $data[] = [
                'sid' => $shift,
                'Org_id' => $org,
                'order' => $order
            ];
            $order++;
        }
        $oshifts->saveAll($data);

        return json_encode($order);
    }

    public function shiftDel($key)
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '',
            'url' => '/index/Manageorg/shiftDel',
            'errorID' => null
        ];

        foreach ($key as $id)
        {
            if($id>0)
            {
                $data = Db::table('data_shifts')->delete($id);
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


    public function oshiftDel($key)
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '',
            'url' => '/index/Manageorg/oshiftDel',
            'errorID' => null
        ];

        foreach ($key as $id)
        {
            if($id>0)
            {
                $data = Db::table('data_oshifts')->delete($id);
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

    public function editOrder()
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '更新成功',
            'url' => '/index/Manageorg/editOrder',
            'errorID' => null
        ];

        $oshiftId=$this->request->param('id',0,'intval');

        $data = Oshifts::find($oshiftId);

        if($data)
        {
            $data->order=input('post.order');     //question here  difference between input('post.xxx') and $this->request->post('', '')
            $opt = $data->save();

            if(!$opt)
            {
                $state['code'] = 0;
                $state['msg'] = '更新失败';
            }
        }
        else
        {
            $state['code'] = 0;
            $state['msg'] = '更新失败';
        }
        return json_encode($state);
    }

    /**
     * @return string
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    public function editAndAdd()
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '更新成功',
            'url' => '/index/Member/index',
            'errorID' => null
        ];

        $shiftId=$this->request->param('id',0,'intval');

        $data = Shifts::find($shiftId);

        if($data)
        {
            $data->SC=input('post.SC');     //question here  difference between input('post.xxx') and $this->request->post('', '')
            $data->SN=input('post.SN');
            $data->Time=input('post.Time');
            $data->duration=input('post.duration');

            $opt = $data->save();

            if(!$opt)
            {
                $state['code'] = 0;
                $state['msg'] = '更新失败';
            }
        }
        else
        {
            $state['msg'] = '添加成功！';

            $SC=$this->request->post('SC','');
            $SN=$this->request->post('SN','');
            $Time=$this->request->post('Time','');
            $duration=$this->request->post('duration','');

            $check = Db::table('data_shifts')->where('SC', $SC)->find();

            if(!$check)
            {
                $shift = new Shifts;
                $shift->SC = $SC;
                $shift->SN = $SN;
                $shift->Time = $Time;
                $shift->duration = $duration;

                $opt=$shift->save();
                if(!$opt)
                {
                    $state['code'] = 0;
                    $state['msg'] = '添加失败！';
                }
            }
            else{
                $state['code'] = 0;
                $state['msg'] = '班别代码已存在！';
            }
        }
        return json_encode($state);
    }

    public function add()
    {
        $orgs = Orgs::where('parent_id', 1)->select();
        $this->assign('first_org', $orgs);

        if ($this->request->isPost()) {
            $state = [
                'code' => 0,        //1 success, 0 failure
                'msg' => 'posted',
                'errorID' => null,
                'url' => '/index/Manageorg/index'
            ];
            $SC=$this->request->post('SC','');
            $SN=$this->request->post('SN','');
            $org1=$this->request->post('org1','');
            $org2=$this->request->post('org2','');
            $org3=$this->request->post('org3','');

            $check=Shifts::where('SC',$SC)->find();

            if($check)
            {
                $state['code'] = 0;
                $state['msg'] = '班别代码已存在！';
                $state['errorID'] = 'SC';
            }
            else{
                $shifts = new Shifts;
                $shifts->SC=$SC;
                $shifts->SN=$SN;

                if($org3 !== '')
                    $shifts->Org_id=$org3;
                else if($org2 !== '')
                    $shifts->Org_id=$org2;
                else
                    $shifts->Org_id=$org1;

                $opt=$shifts->save();
                if($opt)
                {
                    $state['code'] = 1;
                    $state['msg'] = '新增成功！';
                    $state['url'] = '/index/Manageorg/index';
                }
                else
                {
                    $state['code'] = 0;
                    $state['msg'] = '新增失败！';
                }
            }

            return $state;
        }else
            return $this->fetch();
    }



}