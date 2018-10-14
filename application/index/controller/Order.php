<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/13
 * Time: 17:55
 */

namespace app\index\controller;


use app\index\model\Shopping_cart;
use think\Session;

class Order extends Shopping
{
    protected $beforeActionList = [
        'checklogin'  =>  [
            'except'=>''
        ],
    ];
    public function addOrder($data){
        //获取购物车数据
        $data = explode(',',$data);
        $cart_model = new Shopping_cart();
        $orderData = [];
        foreach ($data as $item)
        {
            //查询该用户订单中库存足够的信息
            if(!$rel = $cart_model->alias('A')->
                join('books B','A.books_id = B.Id and B.count - B.sales >= A.sum')
                ->where(['books_id'=>$item,'user_id'=>Session::get('user')])
                ->field('A.sum,B.bookname,B.price,B.press')
                ->select())
            {
                continue;
            }
            $orderData[] = $rel[0];
        }
        //满足条件的商品信息
        $this->assign('validData',$orderData);
        return $this->fetch();
    }
}