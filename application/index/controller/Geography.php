<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/15
 * Time: 9:09
 */

namespace app\index\controller;


use app\index\model\J_position;
use app\index\model\J_position_city;
use app\index\model\J_position_county;
use app\index\model\J_position_provice;
use app\index\model\J_position_town;
use app\index\model\J_position_village;
use app\index\model\Receiving_address;
use think\Session;

class Geography extends Shopping
{
    //省级数据
    public function getProvince()
    {
        $model = new J_position_provice();
        $data = $model->order('provice_id','asc')->select();
        echo json_encode($data);
        return;
    }
    public function getCity($province_id)
    {
        $model = new J_position_city();
        $data = $model->where(['province_id'=>$province_id])
            ->order('city_id','asc')->select();
        echo json_encode($data);
        return;
    }

    public function getCounty($city_id)
    {
        $model = new J_position_county();
        $data = $model->where(['city_id'=>$city_id])
            ->order('county_id','asc')->select();
        echo json_encode($data);
        return;
    }

    public function getTown($county_id)
    {
        $model = new J_position_town();
        $data = $model->where(['county_id'=>$county_id])
            ->order('town_id','asc')->select();
        echo json_encode($data);
        return;
    }

    public function getVillage($town_id)
    {
        $model = new J_position_village();
        $data = $model->where(['town_id'=>$town_id])
            ->order('village_id','asc')->select();
        echo json_encode($data);
        return;
    }

    public function setReceivingAddress()
    {
        $datas = $this->request->post();
        //处理数据
        $j_position = new J_position();
        $data = $datas['data'];
        //存在城镇与乡村信息,保存详细地址信息
        if(isset($data['town_id'])||isset($data['village_id']))
        {
            if(!$rel = $j_position->get([
                'province_id'=>$data['province_id'],
                'city_id'=>$data['city_id'],
                'county_id'=>$data['county_id'],
                'town_id'=>$data['town_id'],
                'village_id'=>$data['village_id']
                ]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'地理信息错误,请联系商家'
                ]);
                return;
            }
            $save = [
                'user_name'=>htmlentities($data['user_name']),
                'user_id'=>Session::get('user'),
                'telephone'=>$data['telephone'],
                'j_position_id'=>$rel['id'],
                'detailed'=>htmlentities($data['detailed'])
            ];
        }else{
            $county_model = new J_position_county();
            if(!$rel = $county_model->get([
                'city_id'=>$data['city_id'],
                'county_id'=>$data['county_id']
            ]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'地理信息错误,请联系商家'
                ]);
                return;
            }
            $save = [
                'user_name'=>htmlentities($data['user_name']),
                'user_id'=>Session::get('user'),
                'telephone'=>$data['telephone'],
                'county_id'=>$rel['county_id'],
                'detailed'=>htmlentities($data['detailed'])
            ];
        }
        $receiving_address = new Receiving_address();
        if(!$receiving_address->insert($save))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'添加失败,稍后再试'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'已添加新地址'
        ]);
        return;
    }
    protected function getAddressData()
    {
        //按类获取地址
        $county = new J_position_county();
        $position = new J_position();
        $receiving_address = new Receiving_address();
        $data = $receiving_address->
        where(['user_id'=>Session::get('user')])->select();
        $addressData = [];
        foreach ($data as $item){
            //存在详细信息
            if($item['j_position_id']>0&&$item['county_id']==0)
            {
                if(!$rel = $position->get(['id'=>$item['j_position_id']]))
                {
                    continue;
                }
                $addressData[]=[
                    'state'=>1,
                    'item'=>$item,
                    'region'=>$rel
                ];
                continue;
            }
            if($item['county_id']>0&&$item['j_position_id']==0){
                if(!$rel=$county->alias('A')
                ->join('j_position_city B','A.city_id = B.city_id')
                ->join('j_position_provice C','B.province_id = C.provice_id')
                ->where(['county_id'=>$item['county_id']])
                ->select()
                ){
                    continue;
                }
                $addressData[]=[
                    'state'=>2,
                    'item'=>$item,
                    'region'=>$rel
                ];
                continue;
            }
        }
        return ($addressData);
    }
    public function getAddress()
    {
        echo json_encode($this->getAddressData());
        return;
    }
    //地址管理
    public function myAddress()
    {
        $this->assign('data',$this->getAddressData());
        return $this->fetch();
    }

    public function delAddress($Id)
    {
        $model = new Receiving_address();
        if(!$model->where(['Id'=>$Id,'user_id'=>Session::get('user')])
        ->delete()){
            echo json_encode([
                'state'=>400,
                'msg'=>'删除失败'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'已删除'
        ]);
        return;
    }
}