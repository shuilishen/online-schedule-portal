<?php
/**
 * Created by PhpStorm.
 * Users: Administrator
 * Date: 2019/6/14
 * Time: 17:36
 */

namespace app\common\controller;

use app\auth\model\Aus;
use app\auth\model\Groups;
use app\auth\model\Users;
use app\index\model\Members;
use think\Controller;
use think\view\driver\Think;

class myCommonController extends Controller
{

    private $accessList=array();

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function initialize()
    {

        $this->checkLogin();
        $this->initAccessList();
        if($this->needCheck() && !$this->checkAccess())
            $this->error('no access !');
        $this->assetMenu();
        if($this->request->isAjax()){
            $this->view->engine->layout(false);
        }
    }

    public function assetMenu(){
        $menu=array();
        foreach (config('menu.mymenu') as $m){
            $subMenu=[
                'title'=>$m['title'],
                'item'=>[]
            ];
            foreach ($m['item'] as $mi){
                $handel = strtolower(ltrim(str_replace('/','#',$mi['url']),'#'));
//                if(in_array($handel,$this->accessList)){
//                    $mi['active']=$handel==getActionHandle();
//                    $subMenu['item'][]=$mi;
//                }

                if(in_array($handel,$this->accessList) || session('auid')==config('app.admin_group')){
                    $mi['active']=$handel==getActionHandle();
                    $subMenu['item'][]=$mi;
                }

            }
            if(count($subMenu['item']) >0 )
                $menu[]=$subMenu;
        }

        $eid = session('uid');
        $mem = Users::where('eid', $eid)->find();
        $group = Groups::where('id', $mem['auid'])->find();

        if($mem['auid'] == 1)
        {
            $url = '/my/catPicture1.jpg';
        }else if($mem['auid'] == 1)
        {
            $url = '/my/catPicture2.jpg';
        }
        else
        {
            $url = '/my/catPicture2.jpg';
        }

        $this->assign('url', $url);
        $this->assign('userGroup', $group['title']);
        $this->assign('mainmenu',$menu);
    }


    /**
     * @param $un
     * @param $pw
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check($un,$pw){
        $user = Users::where('eid', $un)->find();

        if(false==$user){
            return false;
        }
        if($user['pwd']==$pw){
            session('uid',$user['eid']);
            session('auid',$user['auid']);
            return true;
        }
        return false;
    }

    public function isLogin(){
        if(session('?uid')){
            return true;
        }else{
            return false;
        }
    }

    public function checkLogin(){
        $isLoginPage =$this->request->module()=='index' && $this->request->controller()=='Index' && $this->request->action()=='login';
        if($this->isLogin() && $isLoginPage){
            $this->redirect('/');
        }
        if(!$this->isLogin() && !$isLoginPage){
            $this->redirect('/index/Index/login');
        }
    }

    private function checkAccess(){
        if(session('auid')==config('app.admin_group'))
            return true;
        $handle=getActionHandle();
        if(in_array($handle,$this->accessList)){
            return true;
        }
        return false;
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function initAccessList(){
        $auid=session('auid');
        $accessList = Aus::where('Aut_id',$auid)->select();

        foreach ($accessList as $al){
            $this->accessList[]=strtolower(sprintf('%s#%s#%s',$al['m'],$al['c'],$al['a']));
        }
    }

    private function needCheck(){
        $handle=getActionHandle();
        //halt($handle);
        if(in_array($handle,config('app.not_check_access'))){
            return false;
        }
        return true;
    }

}