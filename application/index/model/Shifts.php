<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 13:05
 */

namespace app\index\model;


use think\Model;

class Shifts extends Model
{
    protected $table = 'data_shifts';

    /*
    use SoftDelete;
    protected $deleteTime = 'isdeleted';
    protected $defaultSoftDelete = 0; */
}