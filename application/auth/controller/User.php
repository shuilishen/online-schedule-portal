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

        $list = array();
        $groups=Groups::select();
        foreach ($groups as $group)
        {
            $list[$group['id']] = $group['title'];
        }
        $this->assign('groups', json_encode($list));
        return $this->fetch();
    }

    public function getUserData()
    {
        return Users::order('id', 'ASC')->select();
    }

    public function editAndAdd()
    {
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '更新成功',
            'url' => '/index/User/editAndAdd',
            'errorID' => null
        ];

        $userId=$this->request->param('id',0,'intval');

        $data = Users::find($userId);

        if($data)
        {
            $data->eid=input('post.eid');     //question here  difference between input('post.xxx') and $this->request->post('', '')
            $data->email=input('post.email');
            $data->name=input('post.name');
            $data->pwd=input('post.pwd');
            $data->auid=input('post.auid');
            $data->mid=input('post.mid');

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

            $eid=$this->request->post('eid','');
            $email=$this->request->post('email','');
            $name=$this->request->post('name','');
            $pwd=$this->request->post('pwd','');
            $auid=$this->request->post('auid','');
            $mid=$this->request->post('mid','');

            $user = new Users;
            $user->eid = $eid;
            $user->email = $email;
            $user->name = $name;
            $user->pwd = $pwd;
            $user->auid = $auid;
            $user->mid = $mid;

            $opt=$user->save();
            if(!$opt)
            {
                $state['code'] = 0;
                $state['msg'] = '添加失败！';
            }
        }
        return json_encode($state);
    }


//    /**
//     * @name    添加
//     * @return mixed
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\ModelNotFoundException
//     * @throws \think\exception\DbException
//     */
//    public function add(){
//        $org1 = Groups::order('id ASC')->select();
//        $this->assign('org1',$org1);
//
//        if($this->request->isPost()){
//            $EID=$this->request->post('eid','');
//            $email=$this->request->post('email','');
//            $password=$this->request->post('pwd','');
//
//            $auid=$this->request->post('auid','');
//
//            if(strlen($EID)!=6)
//                $this->error('工号长度错误！');
//            $check=Users::where('eid', $EID)->find();
//            if($check)
//                $this->error('工号已存在');
//            else{
//                $user = new Users;
//
//                $user->Emp_id=$EID;
//                $user->email=$email;
//                $user->password=$password;
//
//                $user->Aut_id=$auid;
//
//                $opt=$user->save();
//                if($opt)
//                    $this->success('新增成功！','/auth/User/index');
//                else
//                    $this->error('新增失败！');
//            }
//
//        }else{
//            return $this->fetch();
//        }
//    }

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