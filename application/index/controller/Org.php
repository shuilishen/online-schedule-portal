<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 14:06
 */

namespace app\index\controller;

use app\common\controller\myCommonController;
use app\index\model\Orgs;
use think\Db;
use think\View;
use think\db\Connection;

/**
 * @name    组织
 * Class Org
 * @package app\index\controller
 */
class Org extends myCommonController
{
    /**
     * @name    主页
     * @return mixed
     */
    public function index(){

        return $this->fetch();
    }

    /**
     * @name    列表
     * @return array
     */
    public function listdata(){
        $Orgs=Orgs::select();
        if($Orgs && count($Orgs)>0)
            return ['code'=>0,'msg'=>'','count'=>count($Orgs),'data'=>$Orgs];
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
    public function add()
    {
        if($this->request->isPost()){
            $Org_C=$this->request->post('Org_C','');
            $Org_N=$this->request->post('Org_N','');

            $check1=Orgs::where('Org_C',$Org_C)->find();
            $check2=Orgs::where('Org_N',$Org_N)->find();
            if($check1)
                $this->error('组织代码已存在');
            else if($check2) {
                $this->error('组织名称已存在');
            }else{
                $org = new Orgs;

                $org->Org_C=$Org_C;
                $org->Org_N=$Org_N;

                $opt=$org->save();
                if($opt)
                    $this->success('新增成功！','/index/Org/index');
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
     */
    public function edit()
    {
        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Orgs::find($id);
            if ($data) {
                if($this->request->isPost()){

                    $data->Org_C=input('post.Org_C');
                    $data->Org_N=input('post.Org_N');

                    $opt = $data->save();
                    if($opt)
                        $this->success('更新成功！','/index/Org/index');
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
            $data = Orgs::find($id);
            if ($data) {
                $opt=$data->delete();
                if($opt)
                    $this->success('删除成功！','/index/Org/index');
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