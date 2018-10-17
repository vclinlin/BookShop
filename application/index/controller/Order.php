<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/13
 * Time: 17:55
 */

namespace app\index\controller;


use app\index\model\Books;
use app\index\model\Flow_sheet;
use app\index\model\Freight;
use app\index\model\Order_book;
use app\index\model\Ordinary_users;
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
        'paypwd'=>[
            'except'=>'setpaypwd,ispaypwd,setpaypass'
        ]
    ];
    protected function ispayPwd(){
        $model = new Ordinary_users();
        $rel = $model->field('pay_pass')->where(['userid'=>Session::get('user'),
            'pass'=>Session::get('pass')])->select();
        if($rel[0]['pay_pass']==""||!$rel[0])
        {
            return false;
        }else{
            return true;
        }
    }
    protected function payPwd(){
        if(!$this->ispayPwd())
        {
            return $this->redirect('index/Order/setPayPwd');
        }
    }
    public function setPayPwd(){
        if($this->ispayPwd())
        {
            //修改支付密码
            return $this->fetch('editpaypwd');
        }else{
            return $this->fetch();
        }
    }
    //设置密码
    public function setPayPass($pay_pass,$user_pass=null)
    {
        $model = new Ordinary_users();
        if(!$pay_pass||$pay_pass=="")
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'设置失败'
            ]);
            return;
        }
        //加密
        $pay_pass = md5(md5(Session::get('user')).md5($pay_pass).md5('!@#$%^&*()_+'));
        //未设置
        if(!$this->ispayPwd()) {
            if (!$model->where(['userid' => Session::get('user'),
                'pass' => Session::get('pass')])->update(['pay_pass' => $pay_pass])) {
                echo json_encode([
                    'state' => 400,
                    'msg' => '设置失败'
                ]);
                return;
            }
            echo json_encode([
                'state' => 200,
                'msg' => '设置成功'
            ]);
            return;
        }
        //修改密码
        if($this->ispayPwd()&&$user_pass!=null)
        {
            $pass = md5(md5(Session::get('user')).md5($user_pass).md5('!@#$%^&*()_+'));
            if (!$model->where(['userid' => Session::get('user'),
                'pass' =>$pass])
                ->update(['pay_pass' => $pay_pass])) {
                echo json_encode([
                    'state' => 400,
                    'msg' => '设置失败'
                ]);
                return;
            }
            echo json_encode([
                'state' => 200,
                'msg' => '设置成功'
            ]);
            return;
        }
    }
    protected function checkOrder(){
        //清理过期订单
        $model = new Order_book();
        $model->where('create_time','<',time()-600)
            ->where(['pay_state'=>0,'order_state'=>0])->update(['order_state'=>2]);
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
    //支付
    public function payment($Id,$money=null,$payPwd=null)
    {
        $model = new Order_book();
        if($money==null||$payPwd==null)
        {
            //返回价格
            if(!$data = $model->get(['user_id'=>Session::get('user'),'Id'=>$Id,'order_state'=>0,'pay_state'=>0]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'待支付订单不存在,或已失效'
                ]);
                return;
            }
            if(!$rel = Freight::get(['pay_id'=>$data['pay']]))
            {
                $freight = 0;
            }else{
                $freight = $rel['money'];
            }
            echo json_encode([
                'state'=>200,
                'money'=>$data['money'],
                'freight'=>$freight
            ]);
            return;
        }else{
            //商品验证
            if(!$order = $model->get(['user_id'=>Session::get('user'),
                'Id'=>$Id,'order_state'=>0,'pay_state'=>0]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'待支付订单不存在,或已失效'
                ]);
                return;
            }
            //支付密码验证
            $pay_pass = md5(md5(Session::get('user')).md5($payPwd).md5('!@#$%^&*()_+'));
            if(!$user = Ordinary_users::get(['userid'=>Session::get('user'),
                "pay_pass"=>$pay_pass]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'密码错误'
                ]);
                return;
            }
            //物流价格
            if(!$rel = Freight::get(['pay_id'=>$order['pay']]))
            {
                $freight = 0;
            }else{
                $freight = $rel['money'];
            }
            if($user['user_money']<=0||$user['user_money']<$order['money']+$freight)
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'余额不足'
                ]);
                return;
            }
            //扣除商品数量
            $bookIdAry = explode(',',$order['books_id']);
            $bookSumAry = explode(',',$order['books_sum']);
            $books_model = new Books();
            $i = 0;
            //验证数量
            foreach ($bookIdAry as $item)
            {
                if(!$rel = $books_model->get(['Id'=>$item]))
                {
                    echo json_encode([
                        'state'=>400,
                        'msg'=>'订单存在已下架商品,不能结算'
                    ]);
                    return;
                    break;
                }
                if($rel['count']-$rel['sales']<=0||$rel['count']-$rel['sales']<$bookSumAry[$i])
                {
                    echo json_encode([
                        'state'=>400,
                        'msg'=>'商品《'.$rel['bookname'].'》库存不足'
                    ]);
                    return;
                    break;
                }
                $i++;
            }
            $i = 0;
            foreach ($bookIdAry as $item)
            {
                $rel->where(['Id'=>$item])->setInc('sales',$bookSumAry[$i]);
                $i++;
            }
            //账户余额足够,扣除相应金额,更新订单状态
            $user_model = new Ordinary_users();
            if(!$user_model->where(['userid'=>Session::get('user')])
            ->setDec('user_money',$order['money']+$freight))
            {
                echo json_encode([
                    'state'=>400,
                    '支付失败,稍后再试'
                ]);
                return;
            }
            if(!$model->where(['Id'=>$Id])->update(['pay_state'=>1]))
            {
                //金额还原
                $user_model->where(['userid'=>Session::get('user')])
                    ->setInc('user_money',$order['money']+$freight);
                echo json_encode([
                    'state'=>400,
                    '支付失败,稍后再试'
                ]);
                return;
            }
            //产生流水号
            $str = "";
            while (strlen($str)<8)
            {
                $str .= rand(1,2)==1?chr(rand(65,90)):chr(rand(97,122));
            }
            $flow_number = str_shuffle($str.time());
            //生成流水信息
            $flow = [
                //订单编号
                'order_number'=>$order['order_number'],
                //流水号
                'flow_number'=>$flow_number,
                //流水金额
                'flow_money'=>'-'.($order['money']+$freight),
                //流水信息
                'msg'=>'购买商品',
                //用户id
                'user_id'=>Session::get('user'),
                //时间
                'create_time'=>time()
            ];
            $flow_sheet = new Flow_sheet();
            if(!$flow_sheet->insert($flow))
            {
                //订单与金额还原
                $model->where(['Id'=>$Id])->update(['pay_state'=>0]);
                $user_model->where(['userid'=>Session::get('user')])
                    ->setInc('user_money',$order['money']+$freight);
                echo json_encode([
                    'state'=>400,
                    '支付失败,稍后再试'
                ]);
                return;
            }

            echo json_encode([
                'state'=>200,
                'msg'=>'支付成功'
            ]);
            return;
        }
    }

    public function cancel($Id)
    {
        $model = new Order_book();
        if(!$model->where([
            'user_id'=>Session::get('user'),
            'pay_state'=>0,
            'Id'=>$Id,
            'order_state'=>0
        ])->update(['order_state'=>1]))
        {
            echo json_encode([
                'state'=>400,
                '取消失败,稍后再试'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'订单已取消'
        ]);
        return;
    }
}