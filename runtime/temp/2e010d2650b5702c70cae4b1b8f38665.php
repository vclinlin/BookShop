<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:94:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\widget\main_nav.html";i:1538056349;}*/ ?>
<link rel="stylesheet" href="__CSS__/index/main_nav.css">

<nav class="navbar navbar-expand-sm bg-dark navbar-dark hidden-sm">
    <!-- Links -->
    <div class="container">
        <ul class="navbar-nav">
            <!-- Dropdown -->
            <li class="nav-item dropdown mr-2" style="background: #CC3333;">
                <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
                    书籍分类
                </a>
                <div class="dropdown-menu">
                    <?php if(count($data) == 0): ?>
                    <a class="dropdown-item" href="javascript:">空空如也</a>
                    <?php else: if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
                        <a class="dropdown-item" href="#"><?php echo $data['name']; ?></a>
                        <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                    <a class="dropdown-item" href="#    ">更多...</a>
                </div>
            </li>
            <li class="nav-item ml-2 mr-2">
                <a class="nav-link" href="/index">首页</a>
            </li>
            <li class="nav-item ml-2 mr-2">
                <a class="nav-link" href="#">热销书籍</a>
            </li>
            <li class="nav-item ml-2 mr-2">
                <a class="nav-link" href="#">限时折扣</a>
            </li>
            <li class="nav-item ml-2 mr-2">
                <a class="nav-link" href="#">最新上架</a>
            </li>
        </ul>
    </div>
</nav>

<nav class="bg-secondary fixed-bottom hidden-lg">
    <div style="height: 60px;" class="text-white container d-inline-flex justify-content-between justify-content-center mt-2 ">
        <div class="flex-btn text-center">
            <a href="/"><i class="fa fa-home" style="font-size:30px;color:white"></i></a>
            <p>首页</p>
        </div>
        <div class="flex-btn">
            <a href="/"><i class="fa fa-thumbs-up" style="font-size:30px;color:white"></i></a>
            <p>热销</p>
        </div>
        <div class="flex-btn">
            <a href="/"><i class="fa fa-heartbeat" style="font-size:30px;color:white"></i></a>
            <p>最新</p>
        </div>
        <div class="flex-btn">
            <a href="/"><i class="fa fa-file-text" style="font-size:30px;color:white"></i></a>
            <p>订单</p>
        </div>
        <div class="flex-btn">
            <a href="/"><i class="fa fa-user-circle-o" style="font-size:30px;color:white"></i></a>
            <p>我的</p>
        </div>
    </div>
</nav>

