#ehking-sdk-php

1 libs/Ehking/*

依赖composer自动加载, 创建 composer.json，内容如下：

	"autoload": {
        "psr-0": {
            "": "libs/",
        }
    }
    
    执行：
    composer install
    
    参看：https://getcomposer.org/
    
2 配置一个bootstrap.php 

		<?php 
		$loader = require __DIR__.'/../vendor/autoload.php';
		return $loader;
		?>
		
3 网站根index.php 引入bootstrap.php




4 如 接口类型:跨境汇款查询:
	
	<form name="yeepay" action="query.php" method='post'>
		<input type="text" name="merchantId">
		<input type="text" name="requestId">
	</form>
	
	file: query.php 
	<?php
		include "bootstrap.php";
		$transfer = new Ehking\Controller\TransferController();
		echo $transfer->queryAction(); //返回json串 
	?>
5 通知机制
     支付成功后：
              交易完成后结果数据通过服务器以主动通知的方式通知给商户系统，商户系统接收到结果数据之后，处理完自己的业务逻辑，处理成功后返回给交易平台一个处理结果SUCCESS。
	          如果商户系统未给交易平台返回处理成功的结果，交易平台会在一定时间以后，以递增的时间间隔再次重发4次；时间间隔分别为20/30/40/50秒，总时长140秒，以确保订单通知成功。
	 订单超时：
	        如商户系统提交至易汇金系统超过24小时候未支付，易汇金系统发送通知自动取消该订单。
	
6 上线前请在 Resources/config/parameters.php 将商编、密钥、正式地址替换

    正式环境地址
    #网关购付汇
    foreignexchange.order.url=https://api.ehking.com/foreignExchange/order
    foreignexchange.query.url=https://api.ehking.com/foreignExchange/query
    foreignexchange.refund.url=https://api.ehking.com/foreignExchange/refund
    foreignexchange.refund.query.url=https://api.ehking.com/foreignExchange/refundQuery
    foreignexchange.listpricelock.url=https://api.ehking.com/foreignExchange/listpriceLock
    #国内人民币
    onlinepay.order.url=https://api.ehking.com/onlinePay/order
    onlinepay.query.url=https://api.ehking.com/onlinePay/query
    onlinepay.refund.url=https://api.ehking.com/onlinePay/refund
    onlinepay.refund.query.url=https://api.ehking.com/onlinePay/refundQuery
    #跨境汇款
    transfer.order.url=https://api.ehking.com/gateway/transfer/order
    transfer.query.url=https://api.ehking.com/gateway/transfer/query
    transfer.listpricelock.url=https://api.ehking.com/gateway/foreignExchange/listpriceLock
    #联名账户
    jointpay.order.url=https://api.ehking.com/hg/order
    jointpay.query.url=https://api.ehking.com/hg/query
 
