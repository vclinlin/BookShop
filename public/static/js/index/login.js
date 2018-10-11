$(function () {
    setInterval(function () {
        if(--timeVal==0)
        {
            upCaptcha();
        }
        $("#times").html(timeVal);
    },1000);
});
var timeVal = 180;
function upCaptcha() {
    var ts = Date.parse(new Date())/1000;
    $('#captcha_img').attr("src", "/captcha?id="+ts);
    timeVal=180;
}
function login() {
    $(".error").remove();
    //用户账号
    var userid = $("#userid").val().trim();
    //用户密码
    var pass = $("#pass").val().trim();
    //验证码
    var captcha = $("#captcha").val().trim();
    /**
     * 验证规则
     * @type {RegExp}
     */
    var userids = /^[0-9]{6,10}$/;
    var passs = /^[0-9a-zA-Z\+\-\_]{6,16}$/;
    /**
     * 验证
     */
    if(!userids.test(userid))
    {
        $("#userid").after('<lable class="error" for="userid">账号为6-10位纯数字</lable>');
        return false;
    }
    if(!passs.test(pass))
    {
        $("#pass").after('<lable class="error" for="pass">密码只能包含6~16个字母、数字或下划线，加减号</lable>');
        return false;
    }
    if(captcha=="")
    {
        $("#captcha").after('<lable class="error" for="captcha">请输入验证码</lable>');
        return false;
    }
    $.ajax({
        url:'/index/index/onLogin',
        type:'post',
        dataType:'json',
        data:{
            'userId':userid,
            'pass':pass,
            captcha
        },success:function(res){
            try{
                if(res.stateCode == 200){
                    layer.msg(res.message,{icon:1,time:2000},function () {
                        window.location.reload();
                    });
                    return;
                }
                if(res.stateCode == 400){
                    layer.msg(res.message,{icon:2,time:2000});
                    upCaptcha();
                    return;
                }
            }catch (e) {
                layer.msg(
                    '网络错误,稍后再试',{icon:0,time:2500}
                )
                return;
            }
        },error:function () {
            layer.msg(
                '网络错误,稍后再试',{icon:0,time:2500}
            )
            return;
        }
    })
}