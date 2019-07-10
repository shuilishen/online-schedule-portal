<?php
/**
 * Created by PhpStorm.
 * Users: Administrator
 * Date: 2019/6/18
 * Time: 14:03
 */

namespace app\auth\model;


use think\Model;
use think\model\concern\SoftDelete;

class Groups extends Model
{
    protected $table = 'auth_groups';
//    use SoftDelete;
//    protected $deleteTime = 'isdeleted';
//    protected $defaultSoftDelete = 0;
}