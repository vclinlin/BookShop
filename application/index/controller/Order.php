<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/13
 * Time: 17:55
 */

namespace app\index\controller;


use app\index\model\Books;
use app\index\model\Order_book;
use app\index\model\Shopping_cart;
use think\Exception;
use think\Session;

class Order extends Shopping
{
    protected $beforeActionList = [
        'checkorder',
        'checklogin'  =>  [
            'except'=>''
        ],
    ];
    protected function checkOrder(){
        //清理过期订单
        $model = new Order_book();
        $model->where('create_time','<',time()-600)
            ->where(['pay_state'=>0])->update(['order_state'=>2,'expiry_time'=>time()]);
    }
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
                    ->field('A.books_id,A.sum,B.bookname,B.price,B.press')
                    ->select()) {
                    try {
                        $error_Data[] = $cart_model->alias('A')->
                        join('books B', 'A.books_id = B.Id ')
                            ->where(['books_id' => $item, 'user_id' => Session::get('user')])
                            ->field('A.books_id,A.sum,B.bookname,B.press,B.count,B.sales')
                            ->select()[0];
                    }catch (Exception $e){
                        //不做处理
                    }
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

    public function placeOrder()
    {
        $datas = $this->request->post();
        //整理格式
        $data = $datas['data'];
        if(count($data)<=0)
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'订单信息错误'
            ]);
            return;
        }
        /**
         * 处理订单
         * -订单信息
         * |-订单编号   $order_number
         * |-商品编号   $books_id
         * |-商品数量   $books_sum
         * |-总价       $money
         * |-支付状态   $pay_state
         * |-创建时间   $create_time
         * |-订单状态   $order_state
         */
        $book = new Books();
        //生成订单信息
        $books_id = null;
        $books_sum = null;
        $money = 0;
        foreach ($data as $item){
            if(!$rel = $book->get(['Id'=>$item['books_id']])){
                //商品信息有误
                echo json_encode([
                    'state'=>400,
                    'msg'=>'订单信息错误'
                ]);
                return;
                break;
            }
            //组合成字符串,便于存取
            $books_id  .= $item['books_id'].',';
            $books_sum .= $item['sum'].',';
            //计算总价
            $money += $rel['price']*$item['sum'];
        }
        //产生订单编号所需要的字母
        $order_number = '';
        while (true){
            $order_number.=rand(1,2)==1?chr(rand(65,90)):chr(rand(97,122));
            if(strlen($order_number) == 5)
            {
                break;
            }
        }
        //生成订单号
        $order_number = str_shuffle($order_number.time());
        //预留做邮费处理
        //获取附加信息
        $msg = $datas['msgData'];
        $orderAry = [
            //产生随机字符串
            'order_number'=>$order_number,
            'books_id'=>substr($books_id, 0, -1),
            'books_sum'=>substr($books_sum,0,-1),
            'money'=>round($money,2),
            'pay_state'=>0,
            'order_state'=>0,
            'create_time'=>time(),
            'user_id'=>Session::get('user'),
            'msg'=>htmlentities($msg['msg']),
            'pay'=>$msg['pay']==0?0:1,
            'address'=>htmlentities($msg['address'])
        ];
        $model = new Order_book();
        if(!$model->insert($orderAry))
        {
            //存入失败
            echo json_encode([
                'state'=>400,
                'msg'=>'订单信息错误'
            ]);
            return;
        }
        //清理购物车
        $cart_model = new Shopping_cart();
        foreach ($data as $item){
            $cart_model->where(['books_id'=>$item['books_id'],
                'user_id'=>Session::get('user'),
                'sum'=>$item['sum']])->delete();
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'已生成订单',
            'order_number'=>$order_number
        ]);
        return;
    }
    public function payOrder($order_number=null)
    {
        //展示所有订单
        $order_model = new  Order_book();
        if($order_number == null)
        {
            //按订单时间排序
            $order_data= $order_model->where(['user_id'=>Session::get('user')])
                ->order('create_time','desc')
                ->paginate('5');
            $this->assign('order_data',$order_data);
            $this->assign('page',$order_data->render());
        }else{
            //指定订单
            $order_data= $order_model->where(['user_id'=>Session::get('user'),
                'order_number'=>$order_number])
                ->paginate('10');
            $this->assign('order_data',$order_data);
            $this->assign('page',$order_data->render());
        }
        return $this->fetch('orderlist');
    }
}