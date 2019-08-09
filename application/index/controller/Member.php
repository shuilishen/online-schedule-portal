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
use think\Db;
use think\facade\Cache;

/**
 * @name    数据
 * Class Members
 * @package app\index\controller
*/
class Member extends myCommonController
{
    /**
     * @throws \Exception
     *
     */
    public function updateMemAndOrgData(){
        Db::query('truncate table data_members');

        $members = $this->getMemberData();
        $mem = new Members;
        $list = array();
        foreach ($members as $key=>$member) {
            if($member['o3']!== null)
                $oid = $member['o3'];
            else if($member['o2'] != null)
                $oid = $member['o2'];
            else
                $oid = $member['o1'];

            $list[] = [
                'id' => $key,
                'eid' => $member['eid'],
                'name' => $member['name'],
                'type' => $member['type'],
                'Org_id' => $member['o1'],
                'Org_id2' => $member['o2'],
                'Org_id3' => $member['o3'],
                'oid' => $oid
            ];
        }
        $mem->saveAll($list, false);

//        $oldData = Orgs::select();
//        $oldData->delete(true);

        Db::query('truncate table data_organizations');

        $list = array();
        $orgs = $this->getOrgsData();
        $organizaton = new Orgs;
        foreach ($orgs as $key => $org){
            $list[] = [
                'id' => $key,
                'parent_id' => $org['Parent_ID'],
                'Org_N' => $org['CONTENT']
            ];
        }

        $organizaton->saveAll($list, false);
    }

    /**
     * @param $tableName
     * @param bool $force
     * @return array|mixed
     * @throws \think\Exception
     *
     */
    private function getHrData($tableName, $force = false){
        if(!$force && Cache::has($tableName)) {
            return Cache::get($tableName);
        }else {
            $data = array();

            if($tableName == 'dbo.A01')
            {
                $data = Db::connect('hr_readonly')->table('dbo.A01')->where('A0191', '<', '06')
                    ->where('DELETED', 0)
                    ->field('A0188, A0191, A0190, A0101')->select();
            }
            else if($tableName == 'dbo.J01')
            {
                $data = Db::connect('hr_readonly')->table('dbo.J01')
                    ->field('A0188, DEPT_ID, DEPT_IDEX, DEPT_IDEX2, DEPT_IDEX3, DEPT_IDEX4') ->select();
            }
            else if($tableName == 'dbo.B01')
            {
                $data = Db::connect('hr_readonly')->table('dbo.B01')->where('B01Deleted', 0)
                    ->field('Dept_ID, CONTENT, Parent_ID')->order('Dept_ID', 'ASC')->select();
            }
            Cache::set($tableName, $data,0);
            return $data;
        }
    }

    /**
     * @return array
     * @throws \think\Exception
     */
    public function getOrgsData(){
        $orgsTable = $this->getHrData('dbo.B01', true);

        $orgs = array();
        foreach ($orgsTable as $org){
            $orgs[$org['Dept_ID']] = [
                'CONTENT' => $org['CONTENT'],
                'Parent_ID' => $org['Parent_ID']
            ];
        }
        ksort($orgs);
        return $orgs;
    }

    /**
     * @return array
     * @throws \think\Exception
     *
     */
    public function getMemberData(){
        $membersTable = $this->getHrData('dbo.A01', true);
        $memOrgsTable = $this->getHrData('dbo.J01', true);
        //$orgs = $this->getOrgsData();

        $members = array();
        foreach ($membersTable as $mem){
            $members[$mem['A0188']] = [
                'name' => $mem['A0101'],
                'eid' => $mem['A0190'],
                'type' =>  $mem['A0191']
            ];
        }
        ksort($members);

        foreach ($memOrgsTable as $memOrg){

            if(array_key_exists($memOrg['A0188'], $members))
            {

                $members[$memOrg['A0188']]['o1'] = $memOrg['DEPT_IDEX'];
                $members[$memOrg['A0188']]['o2'] = $memOrg['DEPT_IDEX2'];
                $members[$memOrg['A0188']]['o3'] = $memOrg['DEPT_IDEX3'];


//                $o1 = $memOrg['DEPT_IDEX'];
//                if($o1 !== null)
//                    $members[$memOrg['A0188']]['o1'] = $orgs[$memOrg['DEPT_IDEX']]['CONTENT'];
//                else
//                    $members[$memOrg['A0188']]['o1'] = $o1;
//
//                $o2 = $memOrg['DEPT_IDEX2'];
//                if($o2 !== null)
//                    $members[$memOrg['A0188']]['o2'] = $orgs[$o2]['CONTENT'];
//                else
//                    $members[$memOrg['A0188']]['o2'] = $o2;
//
//                $o3 = $memOrg['DEPT_IDEX3'];
//                if($o3)
//                    $members[$memOrg['A0188']]['o3'] = $orgs[$o3]['CONTENT'];
//                else
//                    $members[$memOrg['A0188']]['o3'] = $o3;
            }
        }
        return $members;
    }

    public function index()
    {
        $orgs=Orgs::select();

        $arr=array();
        foreach ($orgs as $org){

            $arr[$org['id']] = [
                'orgName' => $org['Org_N'],
                'orgCode' => $org['Org_C']
            ];
        }

        $this->assign('orgs', json_encode($arr));
        return $this->fetch();
    }

    /**
     * @name    列表
     * @return array
     */
    public function listdata(){
        $members=Members::select();
        return json_decode($members);

//        data format for layui table:

//        if($members && count($members)>0)
//            return ['code'=>0,'msg'=>'','count'=>count($members),'data'=>$members];
//        else
//            return ['code'=>301,'msg'=>'没有数据','count'=>0,'data'=>[]];
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
                'msg' => 'posted',
                'errorID' => null,
                'url' => '/index/Member/index'
            ];

            $EID=$this->request->post('eid','');
            $name=$this->request->post('name','');
            $Org_id=$this->request->post('Org_id','');

            if(strlen($EID)!=6)
            {
                $state['code'] = 0;
                $state['msg'] = '工号长度错误！';
                $state['errorID'] = 'EID';

                return $state;
            }

            $check=Members::where('eid',$EID)->find();

            if($check)
            {
                $state['code'] = 0;
                $state['msg'] = '工号已存在！';
                $state['errorID'] = 'EID';

                return $state;
            }
            else{
                $mem = new Members;
                $mem->eid=$EID;
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

                    $data->Emp_id=input('post.eid');     //question here  difference between input('post.xxx') and $this->request->post('', '')
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

    public function del($key){
        $state = [
            'code' => 1,        //1 success, 0 failure
            'msg' => '',
            'url' => '/index/Member/index',
            'errorID' => null
        ];

        foreach ($key as $id)
        {
            if($id>0){
                $data = Members::find($id);
                if ($data){
                    $opt=$data->delete();
                    if(!$opt){
                        $state['code'] = 0;
                        $state['errorID'][] = $id;
                    }
                }
                else {
                    $state['code'] = 0;
                    $state['errorID'][] = $id;
                }
            }else {
                $state['code'] = 0;
                $state['errorID'][] = $id;
            }
        }

        if($state['code'] == 0)
            $state['msg'] = '部分人员删除失败！';
        else
        {
            $state['msg'] = '删除成功！';
        }
        return $state;
    }

}

// ID type eid name 所属部门 所属岗位
//        $members = Db::connect('hr_readonly')->table('dbo.VIEW_A01')->where('A0191', '<', '06')
//            ->where('DELETED', 0)->where('J01_DELETED', 0)
//            ->field('A0188, A0191, A0190, A0101, B01_DEPT_CODE, J01_DEPT_ID, J01_E0101')->select();

//        $members = $this->getMemberData('dbo.VIEW_A01');
//
//
//        $myMemberData = array();
//
//        foreach ($members as $member) {
//            $DEPT_ID_ARR = Db::connect('hr_readonly')->table('dbo.J01')->where('A0188', $member['A0188'])
//                ->field('DEPT_ID, DEPT_IDEX, DEPT_IDEX2, DEPT_IDEX3, DEPT_IDEX4') ->select();
//
//            //$DEPT_ID_ARR =  $this->getMemberData($member['A0188']);
//
//            $temp = Db::connect('hr_readonly')->table('dbo.B01')->where('DEPT_ID', $DEPT_ID_ARR[0]['DEPT_IDEX4'])
//                ->field('CONTENT') ->select();
//
//            $arr[] = [
//                'name' => $member['A0101'],
//                'type' => $member['A0191'],
////
////                'w1' => Db::connect('hr_readonly')->table('dbo.B01')->where('DEPT_CODE', $member['B01_DEPT_CODE'])
////                            ->field('')->select(),
////                'w2' => Db::connect('hr_readonly')->table('dbo.B01')->where('DEPT_ID', $member['J01_DEPT_ID'])
////                            ->field('CONTENT')->select(),
////                'w3' => Db::connect('hr_readonly')->table('dbo.E01')->where('E0101', $member['J01_E0101'])
////                            ->field('MC0000') ->select(),
//                'o1' => Db::connect('hr_readonly')->table('dbo.B01')->where('DEPT_ID', $DEPT_ID_ARR[0]['DEPT_IDEX'])
//                            ->field('CONTENT') ->select(),
//
//                'o2' => Db::connect('hr_readonly')->table('dbo.B01')->where('DEPT_ID', $DEPT_ID_ARR[0]['DEPT_IDEX2'])
//                            ->field('CONTENT') ->select(),
//
//                'o3' => Db::connect('hr_readonly')->table('dbo.B01')->where('DEPT_ID', $DEPT_ID_ARR[0]['DEPT_IDEX3'])
//                            ->field('CONTENT') ->select(),
//
//                'o4' => $temp
//            ];
//        }
//        myHalt($arr);
//        myHalt($members);