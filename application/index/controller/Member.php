<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 13:54
 */

namespace app\index\controller;


use app\common\controller\myCommonController;
use app\index\model\Members;
use app\index\model\Orgs;

/**
 * @name    数据
 * Class Members
 * @package app\index\controller
*/
class Member extends myCommonController
{
    /**
     * @name    主页
     * @return mixed
     */
    public function index()
    {
        $orgs=Orgs::select();
        //myHalt(json_encode($orgs, JSON_FORCE_OBJECT));

        $arr_c=array();
        foreach ($orgs as $o){
            $arr_c[]=sprintf('%s:"%s"',$o['id'],$o['Org_C']);
        }
        $this->assign('ocstr',implode(',',$arr_c));

        $arr_n=array();
        foreach ($orgs as $o){
            $arr_n[]=sprintf('%s:"%s"',$o['id'],$o['Org_N']);
        }
        $this->assign('onstr',implode(',',$arr_n));

        return $this->fetch();
    }

    /**
     * @name    列表
     * @return array
     */
    public function listdata(){
        $datas=Members::select();
        if($datas && count($datas)>0)
            return ['code'=>0,'msg'=>'','count'=>count($datas),'data'=>$datas];
        else
            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
    }

    /**
     * @name    添加
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(){
        $orgs=Orgs::select();
        $this->assign('orgs',$orgs);


        if($this->request->isPost()){

            $state = [
                'code' => 0,        //1 success, 0 failure
                'msg' => '',
                'errorID' => null,
                'url' => '/index/Member/index'
            ];

            $EID=$this->request->post('Emp_id','');
            $name=$this->request->post('name','');
            $Org_id=$this->request->post('Org_id','');

            if(strlen($EID)!=6)
            {
                $state['code'] = 0;
                $state['msg'] = '工号长度错误！';
                $state['errorID'] = 'EID';

                return $state;
                //$this->error('工号长度错误！', '', $errorData);
            }
            $check=Members::where('Emp_id',$EID)->find();

            if($check)
            {
                $state['code'] = 0;
                $state['msg'] = '工号已存在！';
                $state['errorID'] = 'EID';

                return $state;
            }

            else{
                $mem = new Members;
                $mem->Emp_id=$EID;
                $mem->name=$name;
                $mem->Org_id=$Org_id;

                $opt=$mem->save();
                if($opt)
                {
                    $state['code'] = 1;
                    $state['msg'] = '新增成功！';
                    $state['url'] = '/index/Member/index';

                    return $state;
                }
                else
                {
                    $state['code'] = 0;
                    $state['msg'] = '新增失败！';

                    return $state;
                }
            }

        }else{
            return response($this->fetch());
        }
    }

    /**
     * @name    编辑
     * @return mixed
     */
    public function edit(){
        $orgs=Orgs::select();
        $this->assign('orgs',$orgs);

        $id=$this->request->param('id',0,'intval');

        if($id>0){
            $data = Members::find($id);
            if ($data) {
                if($this->request->isPost()){

                    $data->Emp_id=input('post.Emp_id');     //question here  difference between input('post.xxx') and $this->request->post('', '')
                    $data->name=input('post.name');
                    $data->Org_id=input('post.Org_id');

                    $opt = $data->save();
                    if($opt)
                        $this->success('更新成功！','/index/Member/index');
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

    /**
     * @name    删除
     */
    public function del(){
        $state = [
            'code' => 0,        //1 success, 0 failure
            'msg' => '',
            'errorID' => null,
            'url' => '/index/Member/index'
        ];

        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Members::find($id);
            if ($data) {
                $opt=$data->delete();
                if($opt)

                {
                    $state['code'] = 1;
                    $state['msg'] = '删除成功！';
                    $state['url'] = '/index/Member/index';

                    return $state;
                    //$this->success('删除成功！','/index/Member/index');
                }
                else
                {
                    $state['code'] = 0;
                    $state['msg'] = '删除失败！';

                    return $state;
                    //$this->error('删除失败！');
                }
            }
            else
            {
                $state['code'] = 0;
                $state['msg'] = 'id 不存在！';

                return $state;
                //$this->error('id 不存在！');
            }
        }else{
            $state['code'] = 0;
            $state['msg'] = 'id 错误！';
            $this->error('id 错误！');
        }
    }

}