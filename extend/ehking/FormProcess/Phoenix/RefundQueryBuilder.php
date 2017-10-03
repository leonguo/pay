<?php
/**
 * 
 *  
 * PHP Version 5
 *
 * @category  Class
 * @file      RefundBuilder.php
 * @package Ehking\FormProcess\ForeignExchange
 * @author    chao.ma <chao.ma@ehking.com>
 */

namespace Ehking\FormProcess\Phoenix;


use Ehking\Configuration\ConfigurationUtils;
use Ehking\FormProcess\Process;
use Ehking\ResponseHandle\Phoenix\RefundQueryHandle;
//$dir=dirname(__FILE__);
//require_once $dir.'/../../Configuration\ConfigurationUtils.php';
//require_once $dir.'/../../FormProcess\Process.php';
//require_once $dir . '/../../ResponseHandle\Phoenix\RefundQueryHandle.php';

class RefundQueryBuilder extends Process{


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



    public function builder($params)
    {

        $this->merchantId = $params['merchantId'];
        $this->requestId = $params['requestId'];

        $handle = new RefundQueryHandle();


        $str = $this->buildJson();
        $date = $this->creatdate($str);

        return $this->execute(
            ConfigurationUtils::getInstance()->getOnlinepayRefundQueryUrl(),
            json_encode($date),
            $handle
        );
    }




} 