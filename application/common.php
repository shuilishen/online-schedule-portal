<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function getActionHandle(){
    $req=\think\facade\Request::instance();
    $handel=sprintf('%s#%s#%s',$req->module(),$req->controller(),$req->action());
    return strtolower($handel);
}

/**
 * 我的调试
 * @param $aug
 */
function myHalt($aug){
    echo '<pre>';
    print_r($aug);
    //dump($aug);
    exit;
}

/**
 * return the first level children array list of the directory
 * @return array
 */
function getModels(){
    $arr = [];
    $dir = app('app')->getAppPath();
    if (is_dir($dir))
    {//如果是目录，则进行下一步操作
        $d = opendir($dir);//打开目录
        if ($d)
        {//目录打开正常
            while (($file = readdir($d)) !== false)
            {//循环读出目录下的文件，直到读不到为止
                if(is_dir($dir.'/'.$file) && $file != '.' && $file != '..' && $file != 'common')
                {//排除一个点和两个点
                    $arr[] = $file;//记录文件名
                }
            }
        }
        closedir($d);//关闭句柄
    }
    return $arr;
}



