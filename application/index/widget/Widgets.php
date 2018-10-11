<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/6/3
 * Time: 16:38
 */

namespace app\index\widget;


use app\index\model\Admin_url;
use app\index\model\Book_class;
use app\index\model\Books;
use app\index\model\Broadcast_msg;
use app\index\model\Logo_img;
use app\index\model\Logo_text;
use app\index\model\Shopping_cart;
use think\Controller;
use think\Session;
use app\index\model\Ordinary_users;

class Widgets extends Controller
{
    public function main_nav()   //主菜单
    {
        $model = new Book_class();
        $rel = $model->limit(10)->order('createtime','desc')->select();
        $this->assign('data',$rel);
        return $this->fetch('widget/main_nav');
    }
    public function head_nav()  //顶部菜单
    {
        try{  //异常处理
            $data =[
                'userid'=>Session::get('user'),
                'pass'=>Session::get('pass'),
                'state'=>0
            ];
            $model = new Ordinary_users();
            $rel = $model->get($data);
            if($rel){
                //登陆过    状态为1,传递用户名
                $this->assign('data',['state'=>1,'name'=>$rel['username']]);
            }else{
                $this->assign('data',['state'=>0]);
            }
        }catch (Exception $e){
            //未登录,或异常
            $this->assign('data',['state'=>0]);
            return $this->fetch('widget/head_nav');
        }
        $model = new Logo_text();
        $this->assign('logo_text',$model->get(['Id'=>1]));
        return $this->fetch('widget/head_nav');
    }
    public function hot_images()  //轮播图
    {
        $model = new Broadcast_msg();
        $this->assign('data',$model->order('sort','asc')->select());
        return $this->fetch('widget/hot_images');
    }
    public function newBook()//最新书籍展示
    {
        $model = new Books();
        $this->assign('url',Admin_url::get(['Id'=>1]));
        $rel =$model->limit(6)->order('s_time','desc')->select();
        $this->assign('data',$rel);
        return $this->fetch('widget/newbook');
    }

    public function Rankings()  //销售排行
    {
        $model = new Books();
        $this->assign('url',Admin_url::get(['Id'=>1]));
        $rel =$model->limit(6)->order('sales','desc')->select();
        $this->assign('data',$rel);
        return $this->fetch('widget/rankings');
    }

    public function about()  //关于
    {
        return $this->fetch('widget/about');
    }
    public function retrieval()  //搜索框
    {
        $model = new Logo_img();
        $cart = new Shopping_cart();
        $this->assign('sum',[
            'num'=>count($cart->where(['user_id'=>Session::get('user')])->select())
        ]);
        $this->assign('img',$model->get(['Id'=>1]));
        return $this->fetch('widget/retrieval');
    }
}