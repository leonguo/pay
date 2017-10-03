<?php
/**
 * 
 *  
 * PHP Version 5
 *
 * @category  Class
 * @file      EhkingServiceProvider.php
 * @package Ehking
 * @author    chao.ma <chao.ma@ehking.com>

 */

//namespace Ehking;

use Ehking\Controller\JointPayController;
use Ehking\Controller\OnlinePayController;
$dir=dirname(__FILE__);
require_once $dir.'/../Controller/JointPayController.php';
require_once $dir.'/../Controller/OnlinePayController.php';
//use Ehking\Controller\ForeignExchangeController;
//use Ehking\Controller\TransferController;

//下面几个文件还未引入、因为文件未找到
//use Silex\Application;
//use Silex\ControllerCollection;
//use Silex\ControllerProviderInterface;
//use Silex\ServiceProviderInterface;

class EhkingServiceProvider implements ServiceProviderInterface, ControllerProviderInterface{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $app['ehking.foreignexchange_controller'] = $app->share(function(){
            return  new ForeignExchangeController();
        });

        $app['ehking.onlinepay_controller'] = $app->share(function(){
            return  new OnlinePayController();
        });

        $app['ehking.transfer_controller'] = $app->share(function(){
            return  new TransferController();
        });

        $app['ehking.jointpay_controller'] = $app->share(function(){
            return  new JointPayController();
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $app->post('/jointpay/order','ehking.jointpay_controller:orderAction')
            ->bind('jointpay_order');
        $app->post('/jointpay/query','ehking.jointpay_controller:queryAction')
            ->bind('jointpay_query');

        $app->post('/foreignexchange/order', 'ehking.foreignexchange_controller:orderAction')
            ->bind('foreignexchange_order');
        $app->post('/foreignexchange/query', 'ehking.foreignexchange_controller:queryAction')
            ->bind('foreignexchange_query');
        $app->post('/foreignexchange/refund', 'ehking.foreignexchange_controller:refundAction')
            ->bind('foreignexchange_refund');
        $app->post('/foreignexchange/refundQuery', 'ehking.foreignexchange_controller:refundQueryAction')
            ->bind('foreignexchange_refund_query');
        $app->post('/foreignexchange/listpriceLock','ehking.foreignexchange_controller:listpriceLockAction')
            ->bind('foreignexchange_listprice_lock');

        $app->post('/phoenix/order','ehking.onlinepay_controller:orderAction')
            ->bind('onlinepay_order');
        $app->post('/phoenix/query','ehking.onlinepay_controller:queryAction')
            ->bind('onlinepay_query');
        $app->post('/phoenix/refund','ehking.onlinepay_controller:refundAction')
            ->bind('onlinepay_refund');
        $app->post('/phoenix/refundQuery','ehking.onlinepay_controller:refundQueryAction')
            ->bind('onlinepay_refund_query');

        $app->post('/transfer/order','ehking.transfer_controller:orderAction')
            ->bind('transfer_order');
        $app->post('/transfer/query','ehking.transfer_controller:queryAction')
            ->bind('transfer_query');
        $app->post('/transfer/listpriceLock','ehking.transfer_controller:listpriceLockAction')
            ->bind('transfer_listprice_lock');

        $app->get('/transfer/notify','ehking.transfer_controller:notifyAction')
            ->bind('transfer_notify');

        $app->get('/transfer/callback','ehking.transfer_controller:callbackAction')
            ->bind('transfer_callback');

        return $controllers;
    }


} 