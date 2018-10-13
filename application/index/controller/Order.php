<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/13
 * Time: 17:55
 */

namespace app\index\controller;


class Order extends Shopping
{
    protected $beforeActionList = [
        'checklogin'  =>  [
            'except'=>'addorder'
        ],
    ];
    public function addOrder(){
        //获取购物车数据
    }
}