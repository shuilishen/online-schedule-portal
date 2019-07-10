<?php
/**
 * Created by PhpStorm.
 * Users: Administrator
 * Date: 2019/6/18
 * Time: 15:15
 */

namespace app\auth\controller;


use app\common\controller\myCommonController;
use app\auth\model\Groups;
use app\auth\model\Users;

/**
 * @name    用户
 * Class User
 * @package app\auth\controller
 */
class User extends myCommonController
{
    /**
     * @name    主页
     * @return mixed
     */
    public function index(){

        $groups=Groups::select();
        $arr=array();
        foreach ($groups as $g){
            $arr[]=sprintf('%s:"%s"',$g['id'],$g['title']);
        }
        $this->assign('gpstr',implode(',',$arr));

        return $this->fetch();
    }

    /**
     * @name    列表
     * @return array
     *
     */
    public function listdata(){
        $users=Users::select();
        if($users && count($users)>0)
            return ['code'=>0,'msg'=>'','count'=>count($users),'data'=>$users];
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
        $org1 = Groups::order('id ASC')->select();
        $this->assign('org1',$org1);

        if($this->request->isPost()){
            $EID=$this->request->post('Emp_id','');
            $email=$this->request->post('email','');
            $password=$this->request->post('password','');

            $auid=$this->request->post('Aut_id','');

            if(strlen($EID)!=6)
                $this->error('工号长度错误！');
            $check=Users::where('Emp_id',$EID)->find();
            if($check)
                $this->error('工号已存在');
            else{
                $user = new Users;

                $user->Emp_id=$EID;
                $user->email=$email;
                $user->password=$password;

                $user->Aut_id=$auid;

                $opt=$user->save();
                if($opt)
                    $this->success('新增成功！','/auth/User/index');
                else
                    $this->error('新增失败！');
            }

        }else{
            return $this->fetch();
        }
    }

    /**
     * @name    编辑
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit(){
        $org1 = Groups::order('id ASC')->select();
        $this->assign('org1',$org1);
        $id=$this->request->param('id',0,'intval');

        if($id>0){
            $data = Users::find($id);
            if ($data) {
                if($this->request->isPost()){

                    $data->Emp_id=input('post.Emp_id');
                    $data->email=input('post.email');
                    $data->password=input('post.password');
                    $data->Aut_id=input('post.Aut_id');

                    $opt = $data->save();
                    if($opt)
                        $this->success('更新成功！','/auth/User/index');
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
        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Users::find($id);
            if ($data) {
                $opt=$data->delete();
                if($opt)
                    $this->success('删除成功！','/auth/User/index');
                else
                    $this->error('删除失败！');

            } else {
                $this->error('id 不存在！');
            }
        }else{
            $this->error('id 错误！');
        }
    }

}