<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/10
 * Time: 10:45
 */

namespace app\index\controller;


use app\common\controller\myCommonController;

class Circle extends myCommonController
{
    public function index()
    {
        return $this->fetch();
    }
}