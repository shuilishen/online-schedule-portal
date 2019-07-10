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
use app\index\model\Postions;
use app\paiban\model\Regulations;
use app\index\model\Shifts;
use app\index\model\Members;
use think\Db;
use think\View;
use think\db\Connection;

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
        $org=Db::table('data_organizations')->order('Org_C ASC')->select();
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
    public function getmemdata(){
        $members=Db::table('data_members')->field('EID, name, Org_C, Org_N')->select();
        if($members && count($members)>0)
            return ['code'=>0,'msg'=>'','count'=>count($members),'data'=>$members];
        else
            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
    }

    /**
     * @name    选择
     * @param $s
     * @param $key
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function getSelect_T($key){
        $str = '<option value="">请选择</option>';

        $m = Members::where('Org_id', $key)->select();
        $post = Postions::where('Org_id', $key)->select();
        foreach ($post as $item) {
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
        $shift=Db::table('data_shifts')->where('PID', $key)->select();
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
        $ca = Calendars::get($shifts['CID']);
        $re = Regulations::get($shifts['REID']);

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
        $posts=Postions::select();
        $this->assign('posts', $posts);
        $calendars=Calendars::select();
        $this->assign('calendars', $calendars);
        $regulations=Regulations::select();
        $this->assign('regulations', $regulations);

        if($this->request->isPost()){
            $SC = $this->request->post('SC','');
            $SN = $this->request->post('SN','');
            $Time = $this->request->post('Time','');
            $PID = $this->request->post('PID','');
            $REID = $this->request->post('REID','');
            $CID = $this->request->post('CID','');
            $check=Shifts::where('SC',$SC)->find();

            if($check)
                $this->error('班别代码已存在');
            else{
                $shift = new Shifts;

                $shift->SC=$SC;
                $shift->SN=$SN;
                $shift->Time=$Time;
                $shift->PID=$PID;
                $shift->REID=$REID;
                $shift->CID=$CID;

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
        $posts=Postions::select();
        $this->assign('posts', $posts);
        $calendars=Calendars::select();
        $this->assign('calendars', $calendars);
        $regulations=Regulations::select();
        $this->assign('regulations', $regulations);

        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Postions::find($id);
            if ($data) {
                if($this->request->isPost()){
                    $data->SC=input('post.SC');
                    $data->SN=input('post.SN');
                    $data->Time=input('post.Time');
                    $data->PID=input('post.PID');
                    $data->REID=input('post.REID');
                    $data->CID=input('post.CIE');

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

