$(function () {
    setInterval(function () {
        if(--timeVal==0)
        {
            reCaptcha();
        }
        $("#times").html(timeVal);
    },1000);
});
var timeVal = 180;
function reCaptcha() {//验证码手动刷新
    var ts = Date.parse(new Date())/1000;
    $('#captcha_img').attr("src", "/captcha?id="+ts);
    timeVal=180;
}
function relogin() {
    $(".error").remove();
    /**
     * 数据字段
     * @type {*|string|jQuery}
     */
    var email = $("#email").val().trim();
    var username = $("#username").val().trim();
    var pass = $("#pass").val().trim();
    var repass = $("#repass").val().trim();
    var captcha = $("#captcha").val().trim();
    // alert(email+username+pass+repass+captcha);//调试
    /**
     * 验证规则
     * @type {RegExp}
     */
    var usernames = /^[0-9a-zA-Z\+\-\丶\u4e00-\u9fa5]{1,16}$/;
    var emails =/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    var passs = /^[0-9a-zA-Z\+\-\_]{6,16}$/;
    if(!emails.test(email))
    {
        $("#email").after('<lable class="error" for="email">请输入正确的邮箱</lable>');
        return false;
    }
    if(!usernames.test(username))
    {
        $("#username").after('<lable class="error" for="username">用户名由1-16个中英文字符或+-符号组成</lable>');
        return false;
    }
    if(!passs.test(pass))
    {
        $("#pass").after('<lable class="error" for="pass">密码只能包含6~16个字母、数字或下划线，加减号</lable>');
        return false;
    }
    if(repass!==pass)
    {
        $("#repass").after('<lable class="error" for="repass">两次密码不同</lable>');
        return false;
    }
    if(captcha=="")
    {
        $("#captcha").after('<lable class="error" for="captcha">请输入验证码</lable>');
        return false;
    }
    /**
     * ajax 提交
     */
    $.ajax({
        url:"/index/index/goreglogin",
        dataType:"json",
        type:"post",
        data:{
            email,
            username,
            pass,
            captcha
        },
        success:function (data) {

            if(data["id"])  //错误返回
            {
                $("#"+data['id']+"").after('<lable class="error" for="'+data['id']+'">'+
                    data['messages']+'</lable>');
                reCaptcha();
                return;
            }
            if(data['state'])  //成功注册
            {
                $(":input").val("");   //表单清空
                $("#myModal").find(".modal-body").empty().append('<dd>请妥善保管你的账号和密码</dd>' +
                    '<dd>账号：<span class="text-danger">'+data['messages']+'</span></dd>');
                reCaptcha();
                $("#myModal").modal();
                return;
            }
        },
        error:function (data) {
        }
    })
}