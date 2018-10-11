<?php
namespace app\index\controller;

use app\index\model\Books;
use app\index\model\Ordinary_users;
use app\index\model\Shopping_cart;
use think\Controller;
use think\Cookie;
use think\Exception;
use think\Request;
use think\Session;

class Index extends Controller
{
    public function index()   //home
    {
        return $this->fetch('index');
    }
    public function reglogin()  //注册表单
    {
        try{
            $data =[
                'userid'=>Session::get('user'),
                'pass'=>Session::get('pass')
            ];
            $model = new Ordinary_users();
            if($model->get($data)){
                return $this->redirect('/index');
            }
        }catch (Exception $e){
            return $this->fetch('reglogin');
        }
        return $this->fetch('reglogin');
    }
    public function goRegLogin(Request $request)  //注册事件
    {
        $Ary = $request->post();
        if(!captcha_check($Ary['captcha']))
        {
            echo json_encode($data=[
                "id"=>"captcha",
                "messages"=>"验证码错误"
            ]);
            return;
        }
        unset($Ary['captcha']);   //验证通过不再需要
        $model = new Ordinary_users();
        if($model->where("email","=",$Ary['email'])->select())  //邮箱限制
        {
            echo json_encode($data=[
                "id"=>"email",
                "messages"=>"该邮箱已注册"
            ]);
            return;
        }
        /**
         * 账号分配
         */
        $min = 100000;//最小账号范围
        $max = 999999;//最大账号范围
        $count = 0;
        do{
            $userid = rand($min,$max);  //随机账号
            ++$count;
            if($count==$max-$min+1)
            {
                echo json_encode($data=[
                    "id"=>"captcha",
                    "messages"=>"可注册账户已达上限！"
                ]);
                return;
            }
        }while($model->where("userid","=",$userid)->select());
        $Ary['userid'] = $userid;
        /**
         * 密码签名
         */
        $Ary["pass"] = md5(md5($userid).md5($Ary["pass"]).md5("!@#$%^&*()_+"));
        $rel = $model->save($Ary);
        if(!$rel)
        {
            echo json_encode($data=[
                "id"=>"captcha",
                "messages"=>"注册失败，稍后再试！"
            ]);
            return;
        }
        /**
         * 注册完成
         * end
         */
        echo json_encode($data=[
            "state"=>true,
            "messages"=>$userid
        ]);
        return;
    }
    public function login()   //登录
    {
        try{
            $data =[
                'userid'=>Session::get('user'),
                'pass'=>Session::get('pass')
            ];
            $model = new Ordinary_users();
            if($model->get($data)){
                return $this->redirect('/index');
            }
        }catch (Exception $e){
            return $this->fetch('login');
        }
        return $this->fetch('login');
    }
    public function onLogin()  //登陆实现
    {
        $Ary = $this->request->post();
        if(!captcha_check($Ary['captcha'])){
            echo json_encode([
                "stateCode"=>400,
                "message"=>"验证码错误"
            ]);
            return;
        }
        $data = [
            "userid"=>$Ary['userId'],
            "pass"=>md5(md5($Ary['userId']).md5($Ary['pass']).md5("!@#$%^&*()_+")),
            "state"=>0
        ];
        $model = new Ordinary_users();
        $rel = $model->get($data);
        if(!$rel)
        {
            echo json_encode([
                "stateCode"=>400,
                "message"=>"账号或密码错误"
            ]);
            return;
        }
        Session::set('user',$data['userid']);
        Session::set('pass',$data['pass']);
        try{  //处理不存在cookie购物车数据的异常
            //扫描cookie购物车,并添加至数据库
            $cartAry = Cookie::get('cartAry');
            $cartModel = new Shopping_cart();
            $book = new Books();
            foreach ($cartAry as $item)
            {
                //商品已不存在
                if(!$book->get(['Id'=>$item['books_id']]))
                {
                    continue;
                }
                if(!$rel = $cartModel->get(['user_id'=>Session::get('user'),
                    'books_id'=>$item['books_id']]))  //如果该商品没有在该用户的购物车中
                {
                    if(!$cartModel->insert(['user_id'=>Session::get('user'),
                        'books_id'=>$item['books_id'],'create_time'=>$item['create_time']
                        ,'sum'=>$item['sum']]))
                    {
                        continue;
                    }
                    continue;
                }
                //如果该商品已存在该用户的购物车中
                $cartModel->where(['user_id'=>Session::get('user'),
                    'books_id'=>$item['books_id']])->setInc('sum',$item['sum']);
            }
            //删除cookie购物车内容
            Cookie::delete('cartAry');
        }catch (Exception $e){

        }
        echo json_encode([
            "stateCode"=>200,
            "message"=>"登录成功"
        ]);
        return;
    }

    public function exitUser()//用户退出登录
    {
        try{
            Session::delete('user');
            Session::delete('pass');
            $this->success('已注销','/index');
            return;
        }catch (Exception $e){
            return;
        }
        return;
    }

    public function checkCookie()
    {
        $data = Cookie::get('cartAry');
        echo '<pre>';
        print_r($data);
    }
}
