<?php
namespace app\index\controller;

use app\index\model\OrderPayment;
use app\index\model\Orders;
use app\index\model\Product;
use Ehking\FormProcess\Phoenix\OrderBuilder;
use Ehking\ResponseHandle\Phoenix\NotifyHandle;
use think\Log;
use think\Request;
use think\Session;
use think\Validate;

class Pay
{
    //活动信息
    public function activityInfo()
    {
        //活动信息
        $product_id = 1;
        $result = array(
            "date" => "2017年09月24日 14:00~17:00",
            "address" => "朝阳公园西里北区5号华西国际公寓南大堂一楼",
            "name" => "踢踏你的节奏"
        );
        $product = new Product();
        $data = $product::get($product_id);
        $result['price'] = $data->price / 100;
        $open_id = Session::get("open_id");
        $result["openid"] = $open_id ?: "";

        return json_ok($result);
    }

    // 获取微信公众号openID
    public function getOpenInfo()
    {
        $code = Request::instance()->param("code");
        $openid = \wxpay\JsapiPay::getOpenId($code);
        if (!empty($openid)) {
            Session::set("open_id", $openid);
            return redirect('/activity');
        }
        return json_fail([], 400);
    }

    // 生产订单
    public function genOrder()
    {
        // 公众号支付
        $payment_mode = 3;
        //商户ID
        $merchant_id = config("merchantId");
        $openid = Request::instance()->param("openid");
        $product_id = Request::instance()->param("product_id");
        $name = Request::instance()->param("name");
        $phone = Request::instance()->param("phone");
        $quantity = Request::instance()->param("quantity");
        $name = mb_substr($name, 0, 45, 'utf-8');
        $phone = mb_substr($phone, 0, 45, 'utf-8');
        $validate = new Validate([
            'openid' => ['require'],
            'product_id' => ['require'],
            'name' => ['require'],
            'phone' => ['number', 'max' => 20],
            'quantity' => ['number', 'between' => '1,200']
        ]);
        $data = [
            "openid" => $openid,
            "product_id" => $product_id,
            "name" => $name,
            "phone" => $phone,
        ];
        if (!$validate->check($data)) {
            return json_fail("param error", 400);
        }
        if (empty($openid)) {
            return json_fail("openid error", 400);
        }
        $product = Product::get($product_id);
        $price = $product->price;
        $total_price = intval($product->price) * intval($quantity);
        $product_detail = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price,
            'amount' => $total_price,
            'name' => $product->name,
        ];
        $customer_info = array("user_id" => 1, "user_name" => $name, "phone" => $phone);
        $customer_id = isset($customer_info['user_id']) ? $customer_info['user_id'] : '1';
        //构造订单参数
        $order_data = [
            'currency' => $product->currency,
            'order_id' => createOrderId(),
            'price' => $price,
            'amount' => $total_price,
            'quantity' => $quantity,
            'product_id' => $product_id,
            'product_detail' => json_encode($product_detail),
            'payment_mode' => $payment_mode,
            'customer_info' => json_encode($customer_info),
            'customer_id' => $customer_id,
            "remark" => ""
        ];
        // 生产订单
        $order = Orders::create($order_data);
        $orderid = $order->id;
        //第三方下单
        $order_product_detail = [
            [
                'amount' => $total_price,
                'name' => $product->name,
                'quantity' => intval($quantity),
                'description' => $product->description ?: "",
                'receiver' => "深圳市耐飞科技有限公司"
            ]
        ];
        $builder = new OrderBuilder();
        $codeArr = config("paymentModeCode");
        $params = [
            'merchantId' => $merchant_id,
            'requestId' => $order_data['order_id'],
            'orderAmount' => $order_data['amount'],
            'orderCurrency' => $order_data['currency'],
            'notifyUrl' => Config("notifyUrl"),
            'callbackUrl' => Config("callbackUrl"),
            'paymentModeCode' => $codeArr[$payment_mode],
            'details' => json_encode($order_product_detail, JSON_UNESCAPED_UNICODE),
            'remark' => "",
            'openId' => $openid,
        ];
        try {
            Log::error($params);
            $resp = $builder->builder($params);
            Log::error($resp);
        } catch (Exception $e) {
            return json_fail([], 400);
        }
        //第三方下单成功
        Orders::update(["status" => 1], ["id" => $orderid]);
        return json_ok($resp);

    }

    public function notify()
    {
        $post = Request::instance()->param();
        $post['encryptKey'] = Request::instance()->header('Encryptkey');
        $post['merchantId'] = Request::instance()->header('Merchantid');
        $handle = new NotifyHandle();
        try {
            $data = $handle->checkHmac($post);
        } catch (Exception $e) {
            return "FAIL";
        }
        if (!isset($data['status'])) {
            return "FAIL";
        }
        Log::info($data);
        if ($data['status'] == 'SUCCESS') {
            //成功时相关处理代码
            Orders::update(["status" => 2], function ($query) use ($data) {
                $query->where('order_id', $data["requestId"])->where('status', 1);
            });
            OrderPayment::create([
                "order_id" => $data["requestId"],
                "serial_id" => $data["serialNumber"],
                "currency" => $data["orderCurrency"],
                "amount" => $data["orderAmount"],
                "status" => 2,
                "remark" => $data["remark"] ?: "",
                "created_time" => time(),
            ]);
            return "SUCCESS"; //打印出 SUCCESS 表示收到通知
        } elseif ($data['status'] == 'FAILED' || $data['status'] == 'CANCEL') {
            //失败时相关处理代码
            //订单取消
            Orders::update(["status" => -1], function ($query) use ($data) {
                $query->where('order_id', $data["requestId"])->where('status', '<>', 2);
            });
            OrderPayment::create([
                "order_id" => $data["requestId"],
                "serial_id" => $data["serialNumber"],
                "currency" => $data["orderCurrency"],
                "amount" => $data["orderAmount"],
                "status" => -1,
                "remark" => isset($data["remark"]) ? $data["remark"] : "",
                "created_time" => time(),
            ]);
            return "SUCCESS";
        } elseif ($data['status'] == 'INIT') {
            //待处理状态下相关处理代码
        }
        return "FAIL";
    }
}
