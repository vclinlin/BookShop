<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:90:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\index\index.html";i:1538204011;s:75:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\application\index\view\layout.html";i:1539258751;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home</title>
    <script src="__JQ__/jquery-3.2.1.min.js"></script>
    <link rel="stylesheet" href="__BOOTSTRAP__/css/bootstrap.css">
    <link rel="stylesheet" href="__BOOTSTRAP__/css/bootstrap-grid.css">
    <link rel="stylesheet" href="__BOOTSTRAP__/css/bootstrap-reboot.css">
    <link rel="stylesheet" href="__STATIC__/font-awesome/css/font-awesome.css">
    <script src="__BOOTSTRAP__/js/bootstrap.bundle.js"></script>
    <script src="__BOOTSTRAP__/js/bootstrap.js"></script>
    <script src="__BOOTSTRAP__/popper.min.js"></script>
    <script src="__JS__/layer.js"></script>
    <script src="__JS__/common.js"></script>
    <!--<script src="__JQ__/validate/dist/jquery.validate.js"></script>-->
    <!--<script src="__JQ__/validate/dist/localization/messages_zh.js"></script>-->
    
    <link rel="stylesheet" href="__CSS__/index/index.css">
    
</head>
<body>

<!-- 头部 -->
<div class="container-fluid bg-light">
        <div class="col-12 bg-light">
            <!--顶部菜单-->
            <?php echo widget('widgets/head_nav'); ?>
        </div>
    <div class="row">
        <div class="col-12 bg-white">
            <!--搜索预留位-->
            <?php echo widget('widgets/retrieval'); ?>
        </div>
    </div>
</div>
<!--nav预留位-->
<?php echo widget('widgets/main_nav'); ?>
<div class="container-fluid p-0">
    <!--轮播预留位-->
    <?php echo widget('widgets/hot_images'); ?>
</div>
<div class="container-fluid p-0">
    <!--移动端的分类列表-->
    <?php echo widget('MobileWidgets/MobileClass'); ?>
</div>
<div class="container">
    <div class="col-12 d-inline-flex justify-content-center" style="height: 80px;">
        <h4 style="line-height: 80px;">最新上架</h4>
    </div>
    <?php echo widget('Widgets/newBook'); ?>
    <div class="col-12 d-inline-flex justify-content-center" style="height: 80px;">
        <h4 style="line-height: 80px;">销量排行</h4>
    </div>
    <?php echo widget('Widgets/Rankings'); ?>
</div>
<div class="col-12 bg-dark mt-2">
    <div class="container">
        <div class="row">
            <?php echo widget('Widgets/about'); ?>
        </div>
    </div>
</div>

</body>
</html>