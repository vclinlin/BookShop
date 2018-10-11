<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/26
 * Time: 10:51
 */

namespace app\index\widget;


use think\Controller;

class MobileWidgets extends Controller
{
    public function MobileClass(){  //移动端分类展示
        return $this->fetch('mobilewidgets/mobileclass');
    }

}