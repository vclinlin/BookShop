<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:90:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\index\index.html";i:1538204011;s:94:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\widget\head_nav.html";i:1538968771;}*/ ?>
<nav class="navbar navbar-light navbar-expand-sm bg-light">
    <div class="container">
        <a class="navbar-brand" href="/">
            <?php if($logo_text['logo'] == ''): ?>
            ThinkPHP5网上书店
            <?php else: ?>
            <?php echo $logo_text['logo']; endif; ?>
        </a>


        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".collapsibleNavbarA">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse justify-content-end collapsibleNavbarA ">
            <ul class="navbar-nav">
                <?php if($data['state'] == 0): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/index/index/login">登录</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/index/index/reglogin">注册</a>
                </li>
                <?php endif; if($data['state'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#"><?php echo $data['name']; ?></a>
                </li>
                <?php endif; ?>
                <!-- Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="javascript:" id="navbardrop"
                       data-toggle="dropdown">个人中心
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">我的订单</a>
                        <a class="dropdown-item" href="#">我的关注</a>
                        <a class="dropdown-item" href="#">我的消息</a>
                        <a class="dropdown-item" href="#">商品评价</a>
                        <a class="dropdown-item" href="#">商品咨询</a>
                        <a class="dropdown-item" href="#">收货地址</a>
                        <a class="dropdown-item" href="#">账户金额</a>
                        <a class="dropdown-item" href="#">账户安全</a>
                        <?php if($data['state'] == 1): ?>
                        <a class="dropdown-item" href="<?php echo url('index/index/exitUser'); ?>">退出登录</a>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>