
var $main = $('.main');
var $confirm = $('.confirm');
// var $success = $('.success');
// var $error = $('.error');
$('body').css({
  minHeight: window.innerHeight +  "px"
})
var activity = '';
var cost =  60;
var address =  '';
var time = '';
var openid = ""
$(document).ready(function(){
    $.ajax({
        url: 'http://wxpay.nicefilm.com/pay/activityinfo',
        type: 'GET',
        async: false,
        success: function(dataAll) {
            activity = dataAll.data.name
            time = dataAll.data.date
            address = dataAll.data.address
            cost = dataAll.data.price
            openid = dataAll.data.openid
            $("#activityTitle").text(activity);
            $("#activityTitle1").text(activity);
            $("#activityTime").text(time);
            $("#activityTime1").text(time);
            $("#activityAddress").text(address);
            $("#activityAddress1").text(address);
            $("#activityPrice").text(cost);
            $("#cost").text(cost);
            $("#openid").text(openid);
        },
        error: function () {
            alert('获取活动信息失败');
        }
    })

    if(openid == "" || typeof(openid) == "undefined") {
            $.ajax({
                url: 'http://wxpay.nicefilm.com/pay/getopeninfo',
                type: 'GET',
                async: false,
                success: function (res) {
                    var url = res.data.uri;
                         if (url) window.location.href = url;
                },
                error: function () {
                    alert("请用微信开启页面或刷新页面")
                }
            })
    }
});
//请求接口获取微信公众号授权
// $(function(){
//   var open_id = $.cookie('open_id');
//   var open_id_stat = $.cookie('open_id_stat');
//   if(!open_id && !open_id_stat ) {
//     $.ajax({
//       url: 'http://wxpay.nicefilm.com/view/order/payinfo.php',
//       type: 'GET',
//       success: function(res) {
//         var url = res.data.url;
//         window.location.href = url;
//       },
//       error: function() {
//         alert("请用微信开启页面")
//       }
//     })
//   }
// });
function main() {
  
  var count = parseInt($('.main .count span').text());
  var $nodeCount = $('.main .count span');
  var $total = $('.main .total');
  var $minus = $('.main .minus');
  var $name = $('.main input[name="name"]');
  var $tel = $('.main input[name="tel"]');
  var $submit = $('.main .submit');
  var $openid = $('#openid');
  var btnClassHandle = function() {
    if (count > 1) {
      $minus.removeClass('default');
    } else {
      $minus.addClass('default');
    }
  }
  
  
  $('.main .add').on('click', function() {
    $nodeCount.text(++count);
    $total.text( cost * count);
    btnClassHandle()
  })
  $minus.on('click', function() {
    if(!(count <=1)) {
      $nodeCount.text(--count);
      $total.text( cost * count);
      btnClassHandle()
    }
  })
  
  $tel.on('input', function() {
    if($tel.val() && $name.val()) {
      $submit.addClass('checked');
    } else {
      $submit.removeClass('checked');
    }
  })
  $name.on('input', function() {
    if($tel.val() && $name.val()) {
      $submit.addClass('checked');
    } else {
      $submit.removeClass('checked');
    }
  })
  //验证个人信息
  $submit.on('click', function(){
    var reg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
    if( $name.val() && reg.test($tel.val())) {
      $('.confirm .name span').text($name.val());
      $('.confirm .tel span').text($tel.val());
      $('.confirm .count span').text( cost + ' * ' + count);
      $('.confirm .total').text( cost  * count);
      $main.hide();
      $confirm.show();
    } else {
      alert('请填写正确手机号');
    }
  })


  $('.confirm .submit').on('click', function(){
    var expired_time = 0,
        order_id = 0,
        appId,
        timeStamp,
        nonceStr,
        package,
        signType,
        paySign;
    //下单
    $.ajax({
      url: 'http://wxpay.nicefilm.com/pay/genorder',
      type: 'POST',
      async:'false',
      data: JSON.stringify({
              product_id: 1, 
              quantity: count, 
              name: $name.val(), 
              phone: $tel.val(),
              openid: $openid.text()
            }),
      success: function(res) {
        
        //获取 appId, timeStamp, nonceStr, package,signType, paySign;
        order_id = res.data.order_id;
        appId = res.data.appId;
        timeStamp = res.data.timeStamp;
        nonceStr = res.data.nonceStr;
        package = res.data.package;
        signType = res.data.signType;
        paySign = res.data.paySign;
        //保存订单号  供成功页面读取
        window.localStorage.setItem('order_id', order_id);
        //支付
        pay();
      },
      error: function() {
        alert('获取付款信息失败');
      }
    })

    var pay = function() {
      var onBridgeReady = function (){
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', {
                "appId": appId ,//"wx2421b1c4370ec43b",     //公众号名称，由商户传入     
                "timeStamp":timeStamp,  //"1395712654",         //时间戳，自1970年以来的秒数     
                "nonceStr":nonceStr, //"e61463f8efa94090b1f366cccfbbb444", //随机串     
                "package":package, //"prepay_id=u802345jgfjsdfgsdg888",     
                "signType":signType ,//"MD5",         //微信签名方式：     
                "paySign":paySign //"70EA570631E4BB79628FBCA90534C63FF7FADD89" //微信签名 
            },
            function(res){     
                if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                    window.location.href = "http://wxpay.nicefilm.com/activity/success.html";
                }     // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。 
            }
        ); 
      }
      if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
      }else{
        onBridgeReady();
      }
    }
  })
};
main();

