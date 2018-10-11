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
        }catch (e) {
        }
    },error:function (res) {
    }
})
 */
