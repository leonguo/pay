<?php
/**
 * Created by PhpStorm.
 * User: xingjun
 * Date: 2017/10/3
 * Time: 20:17
 */

return [

    // ehking第三方支付参数
    "merchantId" => "900000695",
    "notifyUrl" => "http://wxpay.nicefilm.com/pay/notify",
    "callbackUrl" => "http://wxpay.nicefilm.com/activity/success.html",
    "paymentModeCode" => [
        3 => "WEIXIN_PUBLIC"
    ]
];