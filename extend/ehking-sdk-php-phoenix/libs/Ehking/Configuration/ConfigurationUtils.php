<?php
/**
 * 
 *  
 * PHP Version 5
 *
 * @category  Class
 * @file      ConfigurationUtils.php
 * @package ${NAMESPACE}
 * @author    chao.ma <chao.ma@ehking.com>

 */
//namespace Ehking\Configuration;

class ConfigurationUtils{

    private static $that;
    private static $configuration;

    private function __construct($config)
    {
        if(self::$configuration == null){
            if(is_file($config))
                self::$configuration = include $config;
            else if (is_array($config)){
                self::$configuration = $config;
            }
        }
    }

    public static function getInstance($config=null)
    {
        if(self::$that)
            return self::$that;

        if($config==null)
            $dir=dirname(__FILE__);
            $config = $dir.'/../Resources/config/parameters.php';
        self::$that = new ConfigurationUtils($config);

        return self::$that;
    }

    public function getHmacKey($merchantId)
    {
        if (isset(self::$configuration['merchant'][$merchantId]))
            return self::$configuration['merchant'][$merchantId];

        return null;
    }

    public function getRsaKey($merchantId)
    {
        if (isset(self::$configuration['grantPublicKey'][$merchantId]))
            return self::$configuration['grantPublicKey'][$merchantId];

        return null;
    }

    public function getOnlinepayOrderUrl()
    {
        if(isset(self::$configuration['phoenix.order.url'])){
            return self::$configuration['phoenix.order.url'];
        }
        return null;
    }

    public function getOnlinepayQueryUrl()
    {
        if(isset(self::$configuration['phoenix.query.url'])){
            return self::$configuration['phoenix.query.url'];
        }
        return null;
    }

    public function getOnlinepayRefundUrl()
    {
        if(isset(self::$configuration['phoenix.refund.url'])){
            return self::$configuration['phoenix.refund.url'];
        }
        return null;
    }

    public function getOnlinepayRefundQueryUrl()
    {
        if(isset(self::$configuration['phoenix.refund.query.url'])){
            return self::$configuration['phoenix.refund.query.url'];
        }
        return null;
    }



    
}