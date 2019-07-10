<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 13:05
 */

namespace app\index\controller;


use app\common\controller\myCommonController;
use app\index\model\Calendars;
use app\index\model\Circles;
use app\index\model\Orgs;
use app\index\model\Positions;
use app\index\model\Shifts;
use app\index\model\Members;
use think\Db;

/**
 * @name    班别
 * Class Shift
 * @package app\index\controller
 */
class Shift extends myCommonController
{

    /**
     * @name    主页
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $org = Orgs::order('Org_C ASC')->select();
        //$org=Db::table('data_organizations')->order('Org_C ASC')->select();
        $this->assign('org',$org);

        return $this->fetch();
    }

    /**
     * @name    列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getmemdata()
    {
        $members = Members::field('Emp_id, name, Org_C, Org_N')->select();
        if($members && count($members)>0)
            return ['code'=>0,'msg'=>'','count'=>count($members),'data'=>$members];
        else
            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
    }

    /**
     * @name    选择
     * @param $key
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSelect_T($key){
        $str = '<option value="">请选择</option>';

        $m = Members::where('Org_id', $key)->select();
        $position = Positions::where('Org_id', $key)->select();
        foreach ($position as $item) {
            $str .= sprintf('<option value="%s">%s</option>',$item['id'], $item['PN']);
        }
        $data = [
            'PID' => $str,
            'mtable' => $m
        ];
        return json_encode($data);
    }

    /**
     * @name    Member
     * @param $key
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSelect($key){
        $str='<option value="">请选择</option>';
        $shift = Shifts::where('Pos_id', $key)->select();
        $std = Shifts::where('SC', '001')->find();

        $str.= sprintf('<option value="%s">%s</option>',$std['id'], $std['SN']);
        foreach ($shift as $item) {
            $str.=sprintf('<option value="%s">%s</option>',$item['id'], $item['SN']);
        }
        $data = ['shift' => $str];
        return json_encode($data);
    }

    /**
     * @name    Member
     * @param $key
     * @return string
     */
    public function getData($key){

        $shifts = Shifts::get($key);
        $ca = Calendars::get($shifts['Cal_id']);
        $re = Circles::get($shifts['Cir_id']);

        $round = $re['value'][0] + $re['value'][1];
        $str = '<tbody>';
        for($i = 1; $i <= $round; $i++)
        {
            if($i == 1)
                $str .= '<tr>';
            $str .= '<td>DAY'.$i.'</td>';

            if($i == $round)
                $str .= '</tr>';
        }
        for($i = 1; $i <= $round; $i++)
        {
            if($i == 1)
                $str .= '<tr>';

            if($i <=  $re['value'][0])
                $str .= '<td>'.'上班'.'</td>';
            else
                $str .= '<td>'.'休息'.'</td>';

            if($i == $round)
                $str .= '</tr>';
        }
        $str .= '</tbody>';

        $time = explode('-', $shifts['Time']);
        $data = [
            'CA' => $ca['title'],
            'SC' => $shifts['SC'],
            'BT' => $time[0],
            'ET' => $time[1],
            'wtable' => $str
        ];
        return json_encode($data);
    }

    public function add()
    {
        $positions=Positions::select();
        $this->assign('positions', $positions);
        $calendars=Calendars::select();
        $this->assign('calendars', $calendars);
        $circles = Circles::select();
        $this->assign('circles', $circles);

        if($this->request->isPost()){
            $SC = $this->request->post('SC','');
            $SN = $this->request->post('SN','');
            $Time = $this->request->post('Time','');
            $Pos_id = $this->request->post('Pos_id','');
            $Cir_id = $this->request->post('Cir_id','');
            $Cal_id = $this->request->post('Cal_id','');
            $check=Shifts::where('SC',$SC)->find();

            if($check)
                $this->error('班别代码已存在');
            else{
                $shift = new Shifts;

                $shift->SC=$SC;
                $shift->SN=$SN;
                $shift->Time=$Time;
                $shift->Pos_id=$Pos_id;
                $shift->Cir_id=$Cir_id;
                $shift->Cal_id=$Cal_id;

                $opt=$shift->save();
                if($opt)
                    $this->success('新增成功！','/index/Shift/index');
                else
                    $this->error('新增失败！');
            }
        }else{
            return $this->fetch();
        }
    }

    public function edit(){
        $posts=Positions::select();
        $this->assign('posts', $posts);
        $calendars=Calendars::select();
        $this->assign('calendars', $calendars);
        $circles=Circles::select();
        $this->assign('circles', $circles);

        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Positions::find($id);
            if ($data) {
                if($this->request->isPost()){
                    $data->SC=input('post.SC');
                    $data->SN=input('post.SN');
                    $data->Time=input('post.Time');
                    $data->Pos_id=input('post.Pos_id');
                    $data->Cir_id=input('post.Cir_id');
                    $data->Cal_id=input('post.Cal_id');

                    $opt = $data->save();
                    if($opt)
                        $this->success('更新成功！','/index/Shift/index');
                    else
                        $this->error('更新失败！');
                }else {
                    $this->assign('form_data', $data);
                    return $this->fetch();
                }
            } else {
                $this->error('id 不存在！');
            }
        }else{
            $this->error('id 错误！');
        }
    }

}

