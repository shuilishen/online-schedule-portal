<?php

namespace app\index\controller;

use app\auth\model\Users;
use app\common\controller\myCommonController;
use think\Db;
use think\View;
use think\db\Connection;

/**
 * @name    登录页123
 * Class Index
 * @package app\index\controller
 */
class Index extends myCommonController
{
    /**
     * @name    主页
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $username = session('uid');
        $user = Users::where('Emp_id', $username)->find();

        if($user['Aut_id'] == config('admin_group')){
            $this->redirect('/auth/Group/index');
        }else{
            $this->redirect('/paiban/Index/index');
        }
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(){
        if($this->request->isPost()){
            $username=input('post.username','');
            if(strlen($username)!=6)
                $this->error( '用户名长度错误!');
            $password=input('post.password','');
            if($this->check($username, $password)){
                $this->success('登录成功','/');
            }else{
                $this->error('用户名或者密码错误！');
            }
        }else{
            return $this->fetch('login');
        }
    }

    public function logout()
    {
        if($this->isLogin()) {
            session('uid', null);
            $this->success('退出成功');
        }
        $this->checkLogin();
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * @name    排版
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function scheduling()
    {
        $org1=Db::table('data_members')->order('Org_C ASC')->select();
        $this->assign('org1',$org1);

        return $this->fetch('schedule');
    }

    //---------------------------------------------------------------------------------------

}
