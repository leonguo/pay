<?php
/**
 *
 *
 * PHP Version 5
 *
 * @category  Class
 * @file      OrderBuilder.php
 * @package Ehking\FormProcess\Phoenix
 * @author    chao.ma <chao.ma@ehking.com>
 */

namespace Ehking\FormProcess\Phoenix;

use Ehking\FormProcess\Process;
use Ehking\Entity\ProductDetail;
use Ehking\Configuration\ConfigurationUtils;
use Ehking\ResponseHandle\Phoenix\OrderHandle;

class OrderBuilder extends Process
{

    public $merchantId;
    public $requestId;
    public $orderAmount;
    public $orderCurrency;
    public $notifyUrl;
    public $callbackUrl;
    public $remark;
    public $paymentModeCode;
    public $productDetails;
    public $openId;

    public function builder($params)
    {
        $this->merchantId = $params['merchantId'];
        $this->requestId = $params['requestId'];
        $this->orderAmount = $params['orderAmount'];
        $this->orderCurrency = $params['orderCurrency'];
        $this->notifyUrl = $params['notifyUrl'];
        $this->callbackUrl = $params['callbackUrl'];
        $this->remark = $params['remark'];
        $this->paymentModeCode = $params['paymentModeCode'];
        $this->openId = $params['openId'];
        //商品信息
        if (!empty($params['product'])) {
            $postProduct = $params['product'];
            $products = array();
            foreach ($postProduct as $val) {
                $product = new ProductDetail();
                $product->setDescription($val['description'])
                    ->setAmount($val['productAmount'])
                    ->setName($val['productName'])
                    ->setQuantity($val['quantity'])
                    ->setReceiver($val['receiver']);

                array_push($products, $product);
            }
            $this->productDetails = $products;
        }

        if (!empty($params['details'])) {
            $arr = str_replace("'", '"', $params['details']);
            $arr = stripslashes($arr);
            $details = json_decode($arr, true);
            $products = array();
            foreach ($details as $val) {
                $product = new ProductDetail();
                $product->setDescription($val['description'])
                    ->setAmount($val['amount'])
                    ->setName($val['name'])
                    ->setQuantity($val['quantity']);
                $product->setReceiver($val['receiver']);

                array_push($products, $product);
            }
            $this->productDetails = $products;
        }


        $handle = new OrderHandle();

        $str = $this->buildJson();
        $date = $this->creatdate($str);
        return $this->execute(
            ConfigurationUtils::getInstance()->getOnlinepayOrderUrl(),
            json_encode($date),
            $handle
        );
    }
}