<?php
/**
 * 
 *  
 * PHP Version 5
 *
 * @category  Class
 * @file      RefundBuilder.php
 * @package Ehking\FormProcess\Phoenix
 * @author    chao.ma <chao.ma@ehking.com>
 */

namespace Ehking\FormProcess\Phoenix;


use Ehking\Configuration\ConfigurationUtils;
use Ehking\FormProcess\Process;
use Ehking\ResponseHandle\Phoenix\RefundHandle;
//$dir=dirname(__FILE__);
//require_once $dir.'/../../Configuration\ConfigurationUtils.php';
//require_once $dir.'/../../FormProcess\Process.php';
//require_once $dir . '/../../ResponseHandle\Phoenix\RefundHandle.php';

class RefundBuilder extends Process {

    /**
     * 商户ID
     * @var
     */
    public $merchantId;

    /**
     * 订单号
     * @var
     */
    public $requestId;

    /**
     * 金额
     * @var
     */
    public $amount;

    /**
     * 原订单流水号
     * @var
     */
    public $orderId;

    /**
     * 通知地址
     * @var
     */
    public $notifyUrl;

    /**
     * 备注
     * @var
     */
    public $remark;

    public function builder($params)
    {


        $this->merchantId = $params['merchantId'];

		$this->requestId = $params["requestId"];
		$this->amount = $params["amount"];
		$this->orderId = $params["orderId"];
		$this->remark = $params["remark"];
		$this->notifyUrl = $params["notifyUrl"];
        $handle = new RefundHandle();

        $str = $this->buildJson();
        $date = $this->creatdate($str);

        return $this->execute(
            ConfigurationUtils::getInstance()->getOnlinepayRefundUrl(),
            json_encode($date),
            $handle
        );
    }

} 