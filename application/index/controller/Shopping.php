<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/11
 * Time: 18:29
 */

namespace app\index\controller;


use app\index\model\Books;
use app\index\model\Ordinary_users;
use app\index\model\Shopping_cart;
use think\Controller;
use think\Cookie;
use think\Session;

class Shopping extends Controller
{
    protected $beforeActionList = [
        'checklogin'  =>  ['except'=>'addshoppingcart'],
    ];
    public function checkLogin()  //权限控制(必须登录)
    {
        //未登录
        if(!$this->isLogin())
        {
            $this->redirect('index/index/login');
            return;
        }

    }
    public function isLogin() //登陆检测
    {
        $model = new Ordinary_users();
        //未登录
        if(!$model->get(['userid'=>Session::get('user'),
            'pass'=>Session::get('pass')]))
        {
            return false;
        }
        return true;
    }
    public function addShoppingCart($id)  //添加购物车
    {
        //未登录时
        if(!$this->isLogin())
        {
            //cookie购物车,保存七天
            if(!Cookie::get('cartAry')) //没有添加过
            {
                $data=[
                    [
                        'books_id'=>$id,
                        'create_time'=>time(),
                        'sum'=>1
                    ],
                ];
                //设置cookie保存7天
                Cookie::set('cartAry',$data,604800);
                echo json_encode([
                    'state'=>200,
                    'count'=>count(Cookie::get('cartAry')),
                    'msg'=>'已添加至购物车'
                ]);
                return;
            }
            //获取原有内容
            $data = Cookie::get('cartAry');
            $i = 0;
            foreach ($data as $item)
            {
                if($item['books_id']==$id)  //如果该商品已在购物车中
                {
                    $data[$i]['sum'] += 1;
                    Cookie::set('cartAry',$data,604800);
                    echo json_encode([
                        'state'=>200,
                        'count'=>count(Cookie::get('cartAry')),
                        'msg'=>'已存在购物车,数量加1'
                    ]);
                    return;
                    break;
                }
                $i++;
            }
            //不存在则新增
            $data[] = [
                'books_id'=>$id,
                'create_time'=>time(),
                'sum'=>1
            ];
            //设置cookie,保存七天
            Cookie::set('cartAry',$data,604800);
            echo json_encode([
                'state'=>200,
                'count'=>count(Cookie::get('cartAry')),
                'msg'=>'已添加至购物车'
            ]);
            return;
        }
        //已登录,添加到购物车表单
        $model = new Shopping_cart();
        $book = new Books();
        if(!$book->get(["Id"=>$id]))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'商品不存在'
            ]);
            return;
        }
        if(!$rel = $model->get(['user_id'=>Session::get('user'),
            'books_id'=>$id]))  //如果该商品没有在该用户的购物车中
        {
            if(!$model->insert(['user_id'=>Session::get('user'),
                'books_id'=>$id,'create_time'=>time(),'sum'=>1]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'添加购物车失败,稍后再试'
                ]);
                return;
            }
            echo json_encode([
                'state'=>200,
                'count'=>count($model->where(['user_id'=>Session::get('user')])->select()),
                'msg'=>'已添加至购物车'
            ]);
            return;
        }
        if(!$model->where(['user_id'=>Session::get('user'),
            'books_id'=>$id])->setInc('sum'))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'添加购物车失败,稍后再试'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'count'=>count($model->where(['user_id'=>Session::get('user')])->select()),
            'msg'=>'已存在购物车,数量加1'
        ]);
        return;
    }
    public function addOrder($id)  //添加订单,立即购买页面
    {
    }
}