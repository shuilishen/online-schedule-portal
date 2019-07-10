<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/26
 * Time: 9:39
 */

namespace app\index\controller;


use app\common\controller\myCommonController;
use app\index\model\Positions;
use app\index\model\Orgs;

/**
 * @name    岗位管理
 * Class Position
 * @package app\index\controller
 */
class Position extends myCommonController
{
    /**
     * @name    主页
     * @return mixed
     */
    public function index(){
        $orgs=Orgs::select();
        $arr_n=array();
        foreach ($orgs as $o){
            $arr_n[]=sprintf('%s:"%s"',$o['id'],$o['Org_N']);
        }
        $this->assign('onstr',implode(',',$arr_n));
        return $this->fetch();
    }

    /**
     * @name    表单
     * @return array
     */
    public function listdata(){
        $posts=Positions::select();
        if($posts && count($posts)>0)
            return ['code'=>0,'msg'=>'','count'=>count($posts),'data'=>$posts];
        else
            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
    }

    public function add(){
        $orgs=Orgs::select();
        $this->assign('orgs',$orgs);

        if($this->request->isPost()){
            $Org_id=$this->request->post('Org_id','');
            $PN=$this->request->post('PN','');

            $post = new Positions;
            $post->PN=$PN;
            $post->Org_id=$Org_id;

            $opt=$post->save();
            if($opt)
                $this->success('新增成功！','/index/Position/index');
            else
                $this->error('新增失败！');
        }else{
            return $this->fetch();
        }
    }

    public function edit(){
        $orgs=Orgs::select();
        $this->assign('orgs',$orgs);

        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Positions::find($id);
            if ($data) {
                if($this->request->isPost()){
                    $data->Org_id=input('post.Org_id');
                    $data->PN=input('post.PN');
                    $opt = $data->save();
                    if($opt)
                        $this->success('更新成功！','/index/Position/index');
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

    public function del(){
        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Positions::find($id);
            if ($data) {
                $opt=$data->delete();
                if($opt)
                    $this->success('删除成功！','/index/Position/index');
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