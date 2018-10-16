/**
 * 基础公共事件
 */
$(function () {
    //表格 强制不换行
    $(".table-nowrap th").attr('nowrap', 'nowrap');
    $(".table-nowrap td").attr('nowrap', 'nowrap');
    //美化所有分页
    $(".pager").attr('class','pagination');
    $(".pagination").find('span,a').attr('class','page-link');
    $(".pagination").find('li').attr('class','page-item');
    //优化底部菜单
    try{
        $(window).resize(function () {
            if($(window).width()<720)
            {
                $("#mobileNav").addClass('d-inline-flex');
            }else {
                $("#mobileNav").removeClass('d-inline-flex');
            }
        });
    }catch (e) {
    }
})
/**
 * 添加购物车,公共事件
 * 商品id
 */
function addShoppingCart(id){
    $.ajax({
        url:'/index/Shopping/addShoppingCart',
        type:'post',
        dataType:'json',
        data:{
            id
        },success:function (res) {
            try {
                if(res.state == 200)
                {
                    $('.sumCart').html(res.count);
                    layer.msg(res.msg,{icon:1,time:2000});
                    return;
                }
                if(res.state == 400)
                {
                    layer.msg(res.msg,{icon:0,time:2000});
                    return;
                }
            }catch (e) {
                layer.msg('网络错误,稍后再试',{icon:0,time:2000});
                return;
            }
        },error:function (res) {
            layer.msg('网络错误,稍后再试',{icon:0,time:2000});
            return;
        }
    })
}
/**
 * 备用ajax基础代码
 $.ajax({
        url:'',
        type:'post',
        dataType:'json',
        data:{
        },success:function (res) {
            try {
                if(res.state == 200)
                {
                    return;
                }
                if(res.state == 400)
                {
                    layer.msg(res.msg,{icon:0,time:2000});
                    return;
                }
            }catch (e) {
                layer.msg('网络错误,稍后再试',{icon:0,time:2000});
                return;
            }
        },error:function (res) {
            layer.msg('网络错误,稍后再试',{icon:0,time:2000});
            return;
        }
    })
 */
