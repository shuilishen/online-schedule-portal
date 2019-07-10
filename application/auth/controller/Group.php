<?php
/**
 * Created by PhpStorm.
 * Users: Administrator
 * Date: 2019/6/18
 * Time: 13:46
 */

namespace app\auth\controller;


use app\common\controller\myCommonController;
use app\auth\model\Groups;
use think\db;

/**
 * @name    用户组
 * Class Group
 * @package app\index\controller
 */
class Group extends myCommonController
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
        $groups=Groups::select();
        if($groups && count($groups)>0)
            return ['code'=>0,'msg'=>'','count'=>count($groups),'data'=>$groups];
        else
            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
    }

    /**
     * @name    添加
     * @return mixed
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    public function add(){
        if($this->request->isPost()){
            $title=$this->request->post('title','');

            if(strlen($title)<3)
                $this->error('名称太短！');
            $check=Groups::where('title',$title)->find();
            if($check)
                $this->error('名称已存在');
            else{
                $group = new Groups;
                $group->title=$title;
                $opt=$group->save();
                if($opt)
                    $this->success('新增成功！','/index/Group/index');
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
    public function edit(){
        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Groups::find($id);
            if ($data) {
                if($this->request->isPost()){
                    $data->title=input('post.title');
                    $opt = $data->save();
                    if($opt)
                        $this->success('更新成功！','/index/Group/index');
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
            $data = Groups::find($id);
            if ($data) {
                $opt=$data->delete();
                if($opt)
                    $this->success('删除成功！','/index/Group/index');
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