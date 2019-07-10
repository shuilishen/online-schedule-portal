<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 16:20
 */

namespace app\auth\controller;


use app\common\controller\myCommonController;
use app\auth\model\Aus;
use app\auth\model\Groups;

/**
 * @name    用户组权限
 * Class Au
 * @package app\auth\controller
 */
class Au extends myCommonController
{

    /**
     * @name    权限管理页面
     * @return mixed
     */
    public function index(){

        $this->assign('treedata', json_encode($this->getAllNode()));
        $auid=$this->request->param('auid',0,'intval');
        $this->assign('Aut_id',$auid);

        $group=Groups::where('id', $auid)->value('title');
        $this->assign('group', $group);

        if($this->request->isPost()){
            //myHalt(input('post.'));
            $checked = input('post.treedata');
            if($checked !== ''){
                $che_arr = explode(',', $checked);
                $this->resetAcessData($che_arr);
            }
        }

        return $this->fetch();
    }

    private function resetAcessData($che_arr)
    {
        $auid=$this->request->param('auid',0,'intval');

        //back up previous setting
        $pre_s = $this->listdata();

        //delete all previous setting
        $opt = Aus::where('Aut_id', $auid)->delete();
        if($pre_s['count'] !== $opt)
            $this->error('更新失败！ 请重试'); //question here

        //store new setting
        foreach ($che_arr as $key => $s){
            $arr[] = explode('#', $s);
            $au = new Aus;
            $au->m = $arr[$key][0];
            $au->c = $arr[$key][1];
            $au->a = $arr[$key][2];
            $au->Aut_id = $auid;

            $opt=$au->save();
            if($opt);
            else{
                $this->error('更新失败！ 请重试'); //question here
            }
        }
        $this->success('新增成功！','/auth/Au/index/auid/'.$auid);
    }

    private function getAllNode(){
        $models = getModels();
        $tree=array();
        foreach ($models as $key => $model){

            if($key === 0)
                $s = true;
            else
                $s = false;

            $tree[]=[
                'title'=>'目录'.$key,
                'id'=>$model,
                'children'=>$this->getControllers($model),
                'spread'=>$s
            ];

        }
        //myHalt($tree);
        return $tree;
    }

    private function getControllers($model){
        $arr = [];
        $parent = app('app')->getAppPath();
        $dir = $parent.$model.DIRECTORY_SEPARATOR.'controller';
        if (is_dir($dir))
        {//如果是目录，则进行下一步操作
            $d = opendir($dir);//打开目录
            if ($d)
            {//目录打开正常
                while (($file = readdir($d)) !== false)
                {//循环读出目录下的文件，直到读不到为止
                    if(is_file($dir.'/'.$file))
                    {//排除一个点和两个点
                        //$arr[] = $file;//记录文件名

                        $controller=substr($file,0,strpos($file,'.'));

                        $nArr=['app',$model,'controller',$controller];
                        $namespace=implode('\\',$nArr);

                        $class = new \ReflectionClass($namespace);

                        $name = $this->getName($class->getDocComment());

                        $arr[]=[
                            'id'=>$model.'#'.$controller,
                            'title'=>$name,  //change this to meaningful names
                            'children'=>$this->getPublicMethods($model,$controller)
                        ];
                    }
                }
            }
            closedir($d);//关闭句柄
        }
        return $arr;
    }

    private function getPublicMethods($model,$controller){
        $nArr=['app',$model,'controller',$controller];
        $namespace=implode('\\',$nArr);
        $class = new \ReflectionClass($namespace);

        $arr = array();
        $all = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($all as $al){
            $action=$al->name;
            if(!(strpos($action,'__')===0) && $action!='initialize' && $al->class == $namespace){
                //$arr[]=$al->name;
                //$method = $class->getMethod($action);
                $name = $this->getName($al->getDocComment());
                //myHalt($name);

                $arr[]=[
                    'id'=>implode('#',[$model,$controller,$action]),
                    'title'=>$name,
                ];
            }

        }

        return $arr;
    }

    private function getName($comment)
    {
        //$pattern = '/\/\*\*@name\s([a-zA-Z]+)/';
        $pattern = '/@name\s.*\s/';

        $m = [];
        preg_match($pattern, $comment, $m);
        $m2 = implode('', $m);
        $m3 = sscanf($m2, '%s %s');

        //myHalt($m3[1]);
        return $m3[1];
    }

    /**
     * @name    浏览列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     *
     * handle exception:
     * try
     * catch
     * finally
     *
     *
     */
    public function listdata(){
        $auid=$this->request->param('auid',0,'intval');
        $aus=Aus::where('Aut_id',$auid)->select();

        if($aus && count($aus)>0)
            return ['code'=>0,'msg'=>'','count'=>count($aus),'data'=>$aus];
        else
            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
    }

    /**
     * @name    添加
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     *
     * handle exception:
     * try
     * catch
     * finally
     *
     *
     */
    public function add(){
        $auid=$this->request->param('auid',0,'intval');
        if($this->request->isPost())
        {
            $model=$this->request->post('model','');
            $controller=$this->request->post('controller','');
            $action=$this->request->post('action','');

            $check1=Aus::where('m', $model)->find();
            $check2=Aus::where('c', $controller)->find();
            $check3=Aus::where('a', $action)->find();
            $check4=Aus::where('Aut_id', $auid)->find();

            if($check1 && $check2 && $check3 && $check4)
                $this->error('权限已存在');
            else{
                $au = new Aus;
                $au->m = $model;
                $au->c = $controller;
                $au->a = $action;
                $au->Aut_id = $auid;

                $opt=$au->save();

                if($opt)
                    $this->success('新增成功！','/auth/Au/index/auid/${auid}');
                else
                    $this->error('新增失败！');
            }
        }
        else
        {
            return $this->fetch();
        }
    }




    /**
     * @name    编辑
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     *
     * handle exception:
     * try
     * catch
     * finally
     *
     *
     */
    public function edit()
    {
        $auid=$this->request->param('auid',0,'intval');
        $id=$this->request->param('id',0,'intval');

        if($id>0){
            $data = Aus::where('Aut_id', $auid)->find($id);
            //halt($data);
            if ($data) {
                if($this->request->isPost()){
                    $data->m = input('post.model');
                    $data->c = input('post.controller');
                    $data->a = input('post.action');
                    //$data->auid = input('post.title');
                    //$data-> = input('post.title');
                    $opt = $data->save();

                    if($opt)
                        $this->success('更新成功！','/index/Au/index');
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
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */
    public function del(){
        $auid=$this->request->param('auid',0,'intval');
        $id=$this->request->param('id',0,'intval');
        if($id>0){
            $data = Aus::where('Aut_id', $auid)->find($id);
            if ($data) {
                $opt=$data->delete();
                if($opt)
                    $this->success('删除成功！','/index/Au/index');
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


//    private function listDirFiles($dir)
//    {
//        $arr = [];
//        if (is_dir($dir))
//        {//如果是目录，则进行下一步操作
//            $d = opendir($dir);//打开目录
//
//            if ($d)
//            {//目录打开正常
//                while (($file = readdir($d)) !== false)
//                {//循环读出目录下的文件，直到读不到为止
//                    if($file != '.' && $file != '..' && $file != 'common' && $file != 'view')
//                    {//排除一个点和两个点
//                        if (is_dir($dir.'/'.$file))
//                        {//如果当前是目录并且不是common
//
//
//                            $arr[$file] = $this->listDirFiles($dir.'/'.$file);//进一步获取该目录里的文件
//                        }
//                        else if($dir != app('app')->getAppPath())
//                        {
//                            $arr[] = $file;//记录文件名
//                        }
//                    }
//                }
//            }
//            closedir($d);//关闭句柄
//        }
//        return $arr;
//    }
//