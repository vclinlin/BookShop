<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/17
 * Time: 9:05
 */

namespace app\index\controller;


use app\index\model\Admin_url;
use app\index\model\Book_class;
use app\index\model\Books;
use app\index\model\Item_class;
use think\Controller;

class Commodity extends Controller
{
    //最新
    public function newBooks(){
        $model = new Books();
        $rel =$model->order('s_time','desc')->paginate('18');
        $this->assign('url',Admin_url::get(['Id'=>1]));
        $this->assign('data',$rel);
        $this->assign('page',$rel->render());
        return $this->fetch();
    }

    public function hotBooks()
    {
        $model = new Books();
        $rel =$model->order('sales','desc')->paginate('18');
        $this->assign('url',Admin_url::get(['Id'=>1]));
        $this->assign('data',$rel);
        $this->assign('page',$rel->render());
        return $this->fetch();
    }

    public function priceBooks()
    {
        $model = new Books();
        $rel =$model->order('price','asc')->paginate('18');
        $this->assign('url',Admin_url::get(['Id'=>1]));
        $this->assign('data',$rel);
        $this->assign('page',$rel->render());
        return $this->fetch();
    }

    public function allBooks($class_id=null,$item_id=null,$key=null)
    {
        $book_class = new Book_class();
        $class_data = $book_class->order('Id','asc')->select();
        //没有选择分类
        if($class_id==null)
        {
            $class_id = $class_data[0]['Id'];
        }
        $item_model = new Item_class();
        if(!$item_data = $item_model->where(['book_class_id'=>$class_id])
            ->select())
        {
            $item_id = 0;
        }else{
            if($item_id == null)
            {
                $item_id = $item_data[0]['Id'];
            }
        }
        //查询满足条件书籍
        $books = new Books();
        if($key == null)
        {
            //精确获取
            $book_data = $books->where(['item_class_id'=>$item_id])
                ->paginate('12');
        }else{
            //搜索,清除类别选择
            $class_id=0;
            $item_id=0;
            $book_data = $books
                ->where('bookname|press','like','%'.$key.'%')
                ->paginate('12');
        }
        //子类数据
        $this->assign('item_sum',count($item_data));
        $this->assign('item_data',$item_data);
        //选中子类
        $this->assign('item_id',$item_id);
        //主类数据
        $this->assign('class_sum',count($class_data));
        $this->assign('class_data',$class_data);
        $this->assign('class_id',$class_id);
        //书本数据
        $this->assign('page',$book_data->render());
        $this->assign('data',$book_data);
        return $this->fetch();
    }

    public function details($id)
    {
        $books = new Books();
        if(!$data = $books->get(['Id'=>$id]))
        {
            $this->error('该图书已下架','/');
            return;
        }
        $url = Admin_url::get(1)['url'];
        $this->assign('data',$data);
        return $this->fetch();
    }
}