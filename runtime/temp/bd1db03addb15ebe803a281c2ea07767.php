<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:96:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\widget\hot_images.html";i:1538968565;}*/ ?>
<link rel="stylesheet" href="__CSS__/index/hot_images.css">
<div id="demo" class="carousel slide" data-ride="carousel">
    <!-- 轮播图片 -->
    <div class="carousel-inner">
        <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
            <div class="carousel-item <?php if($key == 0): ?>active<?php endif; ?> img-fluids">
                <img src="<?php echo $data['url']; ?>">
            </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>

    <!-- 左右切换按钮 -->
    <a class="carousel-control-prev" href="#demo" data-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#demo" data-slide="next">
        <span class="carousel-control-next-icon"></span>
    </a>

</div>