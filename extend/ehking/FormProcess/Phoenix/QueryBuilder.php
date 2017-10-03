<?php
/**
 *
 *
 * PHP Version 5
 *
 * @category  Class
 * @file      QueryBuilder.php
 * @package Ehking\FormProcess\Phoenix
 * @author    chao.ma <chao.ma@ehking.com>
 */

namespace Ehking\FormProcess\Phoenix;

use Ehking\Configuration\ConfigurationUtils;
use Ehking\FormProcess\Process;
use Ehking\ResponseHandle\Phoenix\QueryHandle;
//$dir=dirname(__FILE__);
//require_once $dir .'/../../Configuration/ConfigurationUtils.php';
//require_once $dir .'/../../FormProcess/Process.php';
//require_once $dir .'/../../ResponseHandle/Phoenix/QueryHandle.php';


class QueryBuilder extends Process{


    public $merchantId;
    public $requestId;


    public function builder($params)
    {
        $this->merchantId = $params['merchantId'];
        $this->requestId = $params['requestId'];

        $handle = new QueryHandle();

        $str = $this->buildJson();
        $date = $this->creatdate($str);

        return $this->execute(
            ConfigurationUtils::getInstance()->getOnlinepayQueryUrl(),
            json_encode($date),
            $handle
        );
    }





}