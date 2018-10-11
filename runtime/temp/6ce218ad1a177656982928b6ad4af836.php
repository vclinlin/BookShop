<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:94:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\widget\main_nav.html";i:1538056349;s:96:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\widget\hot_images.html";i:1538968565;s:104:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\mobilewidgets\mobileclass.html";i:1537940050;s:93:"D:\Vc_PHP\Apache24\htdocs\Bookstore\shop\public/../application/index\view\widget\newbook.html";i:1539260821;}*/ ?>
<div class="d-flex flex-wrap justify-content-start">
    <?php if(count($data) == 0): ?>
    <div class="col-12 text-center" style="height: 200px;border: black 1px dashed">
        <h3 style="line-height: 200px">未找到任何书籍</h3>
    </div>
    <?php else: if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
    <div class="col-md-2 col-12 p-3 mt-2">
        <div class="row border-lg">
            <div class="col-md-12 col-4" style="overflow: hidden;">
                <div class="m-lg-3">
                    <img    class="img-fluid img-thumbnail"
                            src="<?php echo $data['cover']==''?'./default_cover/default_cover.png':$url['url'].$data['cover']; ?>">
                </div>
            </div>
            <div class="col-md-12 col-8">
                <div class="ml-lg-3">
                    <dl>
                        <dt style="white-space: nowrap;"><?php echo $data['bookname']; ?></dt>
                        <dl>
                            <kbd class="bg-danger small">¥<?php echo $data['price']; ?></kbd>
                        </dl>
                    </dl>
                    <div class="btn-group btn-group-sm mb-2">
                        <input type="button" class="btn btn-success"
                               onclick="window.location.href='<?php echo url('index/Shopping/addOrder',['id'=>$data['Id']]); ?>'" value="立即购买">
                        <button type="button" class="btn btn-danger"
                                onclick="addShoppingCart(<?php echo $data['Id']; ?>)" >
                            <i class="fa fa-cart-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
</div>