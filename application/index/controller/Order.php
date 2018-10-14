<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/13
 * Time: 17:55
 */

namespace app\index\controller;


use app\index\model\Books;
use app\index\model\Shopping_cart;
use think\Session;

class Order extends Shopping
{
    protected $beforeActionList = [
        'checklogin'  =>  [
            'except'=>''
        ],
    ];
    public function addOrder($data,$count=0){
        //获取购物车数据
        $cart_model = new Shopping_cart();
        $orderData = [];
        $error_Data = [];
        $sum = 0;
        if($count == 0)
        {   //购物车事件
            $data = explode(',',$data);
            foreach ($data as $item) {
                //查询该用户订单中库存足够的信息
                if (!$rel = $cart_model->alias('A')->
                join('books B', 'A.books_id = B.Id and B.count - B.sales >= A.sum')
                    ->where(['books_id' => $item, 'user_id' => Session::get('user')])
                    ->field('A.sum,B.bookname,B.price,B.press')
                    ->select()) {
                    $error_Data[] = $cart_model->alias('A')->
                    join('books B', 'A.books_id = B.Id ')
                        ->where(['books_id' => $item, 'user_id' => Session::get('user')])
                        ->field('A.books_id,A.sum,B.bookname,B.press,B.count,B.sales')
                        ->select()[0];
                    continue;
                }
                $orderData[] = $rel[0];
                $sum += $rel[0]['price'] * $rel[0]['sum'];
            }
        }else{
            //直接购买
            $book = new Books();
            if(!$rel = $book->where(['Id'=>$data])->where('count - sales','>=',$count)
            ->select())
            {
                return $this->error('库存不足');
            }
            $orderData[] =[
                'books_id'=>$rel[0]['Id'],
                'sum'=>$count,
                'bookname'=>$rel[0]['bookname'],
                'price'=>$rel[0]['price'],
                'press'=>$rel[0]['press'],
            ];
            $sum += $rel[0]['price'] * $count;

        }
        //满足条件的商品信息
        $this->assign('validData',$orderData);
        //库存不足的商品
        $this->assign('ErData',$error_Data);
        $this->assign('sum',$sum);
        return $this->fetch();
    }
}