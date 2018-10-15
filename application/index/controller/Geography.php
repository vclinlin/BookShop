<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/15
 * Time: 9:09
 */

namespace app\index\controller;


use app\index\model\J_position_city;
use app\index\model\J_position_county;
use app\index\model\J_position_provice;
use app\index\model\J_position_town;
use app\index\model\J_position_village;

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
}