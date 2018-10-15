<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/11
 * Time: 18:29
 */

namespace app\index\controller;


use app\index\model\Admin_url;
use app\index\model\Books;
use app\index\model\Ordinary_users;
use app\index\model\Shopping_cart;
use think\Controller;
use think\Cookie;
use think\Session;

class Shopping extends Controller
{
    protected $beforeActionList = [
        'checklogin'  =>  [
            'except'=>'addshoppingcart,shoppingcart,upcart,delcart,clearcart'
        ],
    ];
    public function checkLogin()  //权限控制(必须登录)
    {
        //未登录
        if(!$this->isLogin())
        {
            $this->redirect('index/index/login');
            return;
        }
        //判断账号是否被冻结

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

    public function ShoppingCart()
    {
        $model = new Books();
        $this->assign('url',Admin_url::get(1));
        //判断是否登陆
        if(!$this->isLogin())
        {
            //未登录检测cookie购物车
            if(!Cookie::get('cartAry'))
            {
                //不存在cookie购物车信息
                $this->assign('cartData',[]);
                return $this->fetch();
            }
            //存在cookie购物车信息
            $data = Cookie::get('cartAry');
            $cartData = [];
            foreach ($data as $item)
            {
                if(!$model->get(['Id'=>$item['books_id']]))
                {
                    //商品已失效
                    continue;
                }
                //有效商品
                $rel = $model->get(['Id'=>$item['books_id']]);
                $rel['sum'] = $item['sum'];
                $cartData[]= $rel;
            }
            $this->assign('cartData',$cartData);
            return $this->fetch();
        }
        $cart = new Shopping_cart();
        $data = $cart->where(['user_id'=>Session::get('user')])->select();
        $cartData = [];
        foreach ($data as $item)
        {
            if(!$model->get(['Id'=>$item['books_id']]))
            {
                //商品已失效
                continue;
            }
            //有效商品
            $rel = $model->get(['Id'=>$item['books_id']]);
            $rel['sum'] = $item['sum'];
            $cartData[]= $rel;
        }
        $this->assign('cartData',$cartData);
        return $this->fetch();
    }

    public function upCart($books_id,$sum)
    {
        if($sum>10||$sum<=0)
        {
            echo json_encode([
                'state'=>'400',
                'msg'=>'数据不对呢'
            ]);
            return;
        }
        //判断修改离线或者在线购物车
        if(!$this->isLogin())  //cookie购物车
        {
            if(!Cookie::get('cartAry'))
            {
                echo json_encode([
                    'state'=>'400',
                    'msg'=>'购物车空空如也呢,兄弟?'
                ]);
                return;
            }
            $data = Cookie::get('cartAry');
            $i = 0;
            foreach ($data as $item)
            {
                if($books_id == $item['books_id'])
                {
                    $data[$i]['sum'] = $sum;
                }
                $i++;
            }
            //有效期七天
            Cookie::set('cartAry',$data,604800);
            echo json_encode([
                'state'=>'200',
            ]);
            return;
        }
        //在线购物车更新
        $model = new Shopping_cart();
        //更新数量
        if(!$model->where(['user_id'=>Session::get('user'),'books_id'=>$books_id])
        ->update(['sum'=>$sum]))
        {
            echo json_encode([
                'state'=>'400',
                'msg'=>'您的购物车里,没有该商品存在'
            ]);
            return;
        }
        echo json_encode([
            'state'=>'200'
        ]);
        return;
    }

    public function delCart($books_id)  //购物车单件删除
    {
        //判断修改离线或者在线购物车
        if(!$this->isLogin())  //cookie购物车
        {
            if(!Cookie::get('cartAry'))
            {
                echo json_encode([
                    'state'=>'400',
                    'msg'=>'购物车空空如也呢,兄弟?'
                ]);
                return;
            }
            $data = Cookie::get('cartAry');
            $i = 0;
            foreach ($data as $item)
            {
                if($books_id == $item['books_id'])
                {
                    array_splice($data,$i,1);
                }
                $i++;
            }
            //有效期七天
            Cookie::set('cartAry',$data,604800);
            echo json_encode([
                'state'=>'200',
                'msg'=>'已删除'
            ]);
            return;
        }
        //删除在线购物车数据
        $model = new Shopping_cart();
        if(!$model->where(['user_id'=>Session::get('user'),'books_id'=>$books_id])
            ->delete())
        {
            echo json_encode([
                'state'=>'400',
                'msg'=>'您的购物车里,没有该商品存在'
            ]);
            return;
        }
        echo json_encode([
            'state'=>'200',
            'msg'=>'已删除'
        ]);
        return;
    }
    public function clearCart(){
        //判断修改离线或者在线购物车
        if(!$this->isLogin())  //cookie购物车
        {
            if(!Cookie::get('cartAry'))
            {
                echo json_encode([
                    'state'=>'400',
                    'msg'=>'购物车空空如也呢,兄弟?'
                ]);
                return;
            }
            //删除cookie购物车
            Cookie::delete('cartAry');
            echo json_encode([
                'state'=>'200',
                'msg'=>'已删除'
            ]);
            return;
        }
        //删除该用户的在线购物车所有数据
        $model = new Shopping_cart();
        if(!$model->where(['user_id'=>Session::get('user')])
            ->delete())
        {
            echo json_encode([
                'state'=>'400',
                'msg'=>'您的购物车里,没有商品存在'
            ]);
            return;
        }
        echo json_encode([
            'state'=>'200',
            'msg'=>'已删除'
        ]);
        return;
    }
}