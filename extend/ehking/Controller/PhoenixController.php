<?php
/**
 * 
 *  
 * PHP Version 5
 *
 * @category  Class
 * @file      PhoenixController.php
 * @package Ehking\Controller
 * @author    chao.ma <chao.ma@ehking.com>

 */

namespace Ehking\Controller;
use Ehking\Excation\ExceptionInterface;
use Ehking\FormProcess\Phoenix\OrderBuilder;
use Ehking\FormProcess\Phoenix\QueryBuilder;
use Ehking\FormProcess\Phoenix\RefundBuilder;
use Ehking\FormProcess\Phoenix\RefundQueryBuilder;
use Ehking\ResponseHandle\Phoenix\NotifyHandle;
//$dir=dirname(__FILE__);
//require_once $dir.'/../Excation\ExceptionInterface.php';
//require_once $dir . '/../FormProcess\Phoenix\OrderBuilder.php';
//require_once $dir . '/../FormProcess\Phoenix\QueryBuilder.php';
//require_once $dir.'/../../FormProcess\Process.php';
//require_once $dir.'/../../FormProcess\Phoenix\RefundBuilder.php';
//require_once $dir.'/../../FormProcess\Phoenix\RefundQueryBuilder.php';
//require_once $dir.'/../../ResponseHandle\Phoenix\NotifyHandle.php';
/**
 * 人民币收单
 * Class OnlinePayController
 * @package Ehking\Controller
 */
class PhoenixController {

    /**
     * 下单
     */
    public function orderAction()
    {
        $builder = new OrderBuilder();
        try{
            return json_encode($builder->builder($_POST));
        }catch (ExceptionInterface $e)
        {
            return json_encode(unserialize($e->getMessage()));
        }
    }

    /**
     * 查询
     */
    public function queryAction()
    {
        $builder = new QueryBuilder();
        try{
            return json_encode($builder->builder($_POST));
        }catch (ExceptionInterface $e)
        {
            return json_encode(unserialize($e->getMessage()));
        }
    }

    /**
     * 退款
     */
    public function refundAction()
    {
        $builder = new RefundBuilder();
        try{
            return json_encode($builder->builder($_POST));
        }catch (ExceptionInterface $e)
        {
            return json_encode(unserialize($e->getMessage()));
        }
    }

    /**
     * 退款查询
     */
    public function refundQueryAction()
    {
        $builder = new RefundQueryBuilder();
        try{
            return json_encode($builder->builder($_POST));
        }catch (ExceptionInterface $e)
        {
            return json_encode(unserialize($e->getMessage()));
        }
    }


    /**
     * 通知处理
     */
    public function notifyAction()
    {
        $raw = '';
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', '-', $key);
                $raw .= $key.': '.$value."\r\n";
            }
        }
        $raw_post_data = file_get_contents('php://input', 'r');
        $post = json_decode($raw_post_data, true);
        preg_match_all('/(ENCRYPTKEY|MERCHANTID|HOST"):(\s+|")([^"\s]+)/s',$raw,$m);
        list($encryptKey, $merchantId) = $m[3];
        $post['encryptKey'] = $encryptKey;
        $post['merchantId'] = $merchantId;
        /*
         * 接收参数写入日志
         */
//        $file='log.txt';
//        $content='日志记录\r\n';
//        $content.= json_encode($post);
//        if($f =file_put_contents($file,$content,FILE_APPEND)) {
//            echo "写入成功。<br/>";
//        }
        //exit;
        $handle = new NotifyHandle();
        $post1 = $handle->checkHmac($post);
        $handle->handle($post1);
    }

    /**
     * 回调处理
     */
    public function callbackAction()
    {
        $this->notifyAction();
    }
} 