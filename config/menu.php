<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/25
 * Time: 13:57
 */
return [
    'mymenu' => [
        ['title'=> '权限管理', 'item' => [
            ['title' => '用户组', 'url' => '/auth/Group/index'],
            ['title' => '用户', 'url' => '/auth/User/index'],
        ]],

        ['title'=> '数据管理', 'item' => [
            ['title' => '员工', 'url' => '/index/Member/index'],
            ['title' => '组织', 'url' => '/index/Org/index'],
            ['title' => '上班规则', 'url' => '/index/Circle/index'],
            ['title' => '岗位', 'url' => '/index/Position/index'],
            ['title' => '班别', 'url' => '/index/Shift/index'],
            ['title' => '日历', 'url' => '/index/Calendar/index'],
        ]],

        ['title'=> '排班系统', 'item' => [
            ['title' => '排班', 'url' => '/paiban/Index/index'],
        ]],
    ],


];