<?php
/**
 * 
 *  
 * PHP Version 5
 *
 * @category  Class
 * @file      Process.php
 * @package Ehking\FormProcess
 * @author    chao.ma <chao.ma@ehking.com>

 */

//namespace Ehking\FormProcess;


//use Ehking\Configuration\ConfigurationUtils;
//use Ehking\Entity\AbstractModel;
//use Ehking\Excation\HmacVerifyException;
//use Ehking\Excation\InvalidRequestException;
//use Ehking\Excation\InvalidResponseException;
//use Ehking\ResponseHandle\ResponseTypeHandle;
$dir=dirname(__FILE__);

require_once $dir.'/../Configuration/ConfigurationUtils.php';
require_once $dir.'/../Entity/AbstractModel.php';
require_once $dir.'/../Excation/HmacVerifyException.php';
require_once $dir.'/../Excation/InvalidRequestException.php';
require_once $dir.'/../Excation/InvalidResponseException.php';
require_once $dir.'/../ResponseHandle/ResponseTypeHandle.php';


abstract class Process {
    public $merchantId;
    const STATUS = 'status';
    const SUCCESS = 'SUCCESS';
    const FAILED = 'FAILED';
    const CANCEL = 'CANCEL';
    const INIT = 'INIT';
    const ERROR = 'ERROR';
    const REDIRECT = 'REDIRECT';

    private $response_hmac_fields = array();

    public abstract function builder($params);

    public function setHmacFields($fields)
    {
        $this->response_hmac_fields = $fields;
    }

    public function creatdate($strdata){

        $public_key = ConfigurationUtils::getInstance()->getRsaKey($strdata['merchantId']);
        /*
          * 生成16位随机数（AES秘钥）
          */
        $str1='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str1);//打乱字符串
        $rands= substr($randStr,0,16);

        /**
         * AES加密方法
         * @param string $str
         * @return string
         */
        $str = json_encode($strdata);
        $screct_key = $rands;
        $str = trim($str);
        $str = $this->addPKCS7Padding($str);
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
//        $encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
        $ivsize = openssl_cipher_iv_length('AES-128-ECB');
        $iv = openssl_random_pseudo_bytes($ivsize);
        $encrypt_str = openssl_encrypt($str, 'AES-128-ECB', $screct_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,$iv);

        $date = base64_encode($encrypt_str);

        /*
         * RSA加密
         */
        $pem = chunk_split($public_key,64,"\n");//转换为pem格式的公钥
        $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
        $publicKey = openssl_pkey_get_public($pem);//获取公钥内容
        openssl_public_encrypt($rands,$encryptKey,$publicKey,OPENSSL_PKCS1_PADDING);
        $encryptKey = base64_encode($encryptKey);



        $json = array("data" =>$date,"encryptKey"=>$encryptKey,"merchantId"=>$strdata['merchantId'],"requestId"=>$strdata['requestId']);


        return $json;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    function addPKCS7Padding($source){
        $source = trim($source);
        $block = mcrypt_get_block_size('rijndael-128', 'ecb');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }



    /**
     * hmac 验证
     * @return mixed
     */
    public function checkHmac($data)
    {
        $public_key = ConfigurationUtils::getInstance()->getRsaKey($data['merchantId']);
        /*
         * RSA公钥解密
         *
         */
        $encryptKey =$data['encryptKey'];
        $pem1 = chunk_split($public_key,64,"\n");
        $pem1 = "-----BEGIN PUBLIC KEY-----\n".$pem1."-----END PUBLIC KEY-----\n";
        $pi_key =  openssl_pkey_get_public($pem1);
        openssl_public_decrypt(base64_decode($encryptKey),$decrypted,$pi_key);


        /*
         * AES解密
         *
         */

        $date = base64_decode($data['data']);
        $screct_key = $decrypted;
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
//        $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $date, MCRYPT_MODE_ECB, $iv);
        $ivsize = openssl_cipher_iv_length('AES-128-ECB');
        $iv = openssl_random_pseudo_bytes($ivsize);
        $encrypt_str = openssl_decrypt($date, 'AES-128-ECB', $screct_key,OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,$iv);

        $encrypt_str = preg_replace('/[\x00-\x1F]/','', $encrypt_str);
        $encrypt_str = json_decode($encrypt_str,true);


        /*
         * 去除空值的元素
         */

        function clearBlank($arr)
        {
            function odd($var)
            {
                return($var<>'');//return true or false
            }
            return (array_filter($arr, "odd"));
        }

        function array_remove_empty(& $arr, $trim = true){
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    array_remove_empty($arr[$key]);
                } else {
                    $value = trim($value);
                    if ($value == '') {
                        unset($arr[$key]);
                    } elseif ($trim) {
                        $arr[$key] = $value;
                    }
                }
            }
        }
        $encrypt_str = clearBlank($encrypt_str);
        /*
         * hamc签名验证
         *
         *
         */
        if (empty($encrypt_str['hmac'])){
            throw new HmacVerifyException(array(
                'error_description'=>'hmac validation error',
                'responseData' => $encrypt_str
            ));
        }
        $hmac = $encrypt_str['hmac'];
        unset($encrypt_str['hmac']);
        ksort($encrypt_str);
        $hmacSource = '';
        foreach($encrypt_str as $key => $value){
                if (is_array($value)) {
                    ksort($value);
                    foreach ($value as $key2 => $value2) {
                        if (is_object($value2)) {
                            $value2 = array_filter((array)$value2);
                            ksort($value2);
                            foreach ($value2 as $oKey => $oValue) {
                                $oValue .= '#';
                                $hmacSource .= trim($oValue);
                            }
                        } else {
                            $value2 .= '#';
                            $hmacSource .= trim($value2);
                        }
                    }
                } else {
                    $value .= '#';
                    $hmacSource .= trim($value);
                }
        }
        $sourceHmac = hash_hmac('md5', $hmacSource, ConfigurationUtils::getInstance()->getHmacKey(isset($encrypt_str['merchantId'])?$encrypt_str['merchantId']:''));
        if ($sourceHmac !== $hmac){
            throw new HmacVerifyException(array(
                'error_description'=>'hmac validation error'
            ));
        }
        return $encrypt_str;
    }

    public function execute($url, $param, ResponseTypeHandle $handle=null)
    {
        $data = $this->httpRequestPost($url, $param);
        if($handle !== null && $handle instanceof ResponseTypeHandle){
            $handle->handle($data);
        }
        return $data;
    }
    public function httpRequestPost($url, $param)
    {
        $theArray = json_decode($param,true);
        $abb =$theArray['data'];
        $curl = curl_init($this->absoluteUrl($url));
        curl_setopt($curl,CURLOPT_HEADER, 1 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_HTTPHEADER,array(
            'Content-Type: application/vnd.ehking-v2.0+json',
            'encryptKey: '.$theArray['encryptKey'],
            'merchantId: '.$theArray['merchantId'],
            'requestId: '.$theArray['requestId']
        ));
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$abb);// post传输数据
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证

        $responseText = curl_exec($curl);
        if (curl_errno($curl) || $responseText === false) {
            curl_close($curl);
            throw new InvalidRequestException(array(
                'error_description'=> 'Request Error'
            ));
        }
        curl_close($curl);
        preg_match_all('/(encryptKey|merchantId|data"):(\s+|")([^"\s]+)/s',$responseText,$m);
        list($encryptKey, $merchantId, $data) = $m[3];
        $responsedata = array("data" =>$data,"encryptKey"=>$encryptKey,"merchantId"=>$merchantId);
        if ($responsedata['merchantId'] == null){
            throw new InvalidRequestException(array(
                'error_description'=>'Request error',
                'responseData'=>$responseText
            ));
        }
        $date = $this->checkHmac($responsedata);
        return $date;
    }


    /**
     *
     * @return string
     */
    protected function buildJson($para=null)
    {
        $vars = $para?'':get_object_vars($this);
        unset($vars['response_hmac_fields']);
        $data = array();
        foreach($vars as $k=>$var){
            if(is_scalar($var) && $var !== '' && $var !== null){
                $data[$k] = $var;
            }else if(is_object($var) && $var instanceof AbstractModel){
                $data[$k] =array_filter((array) $var);
            }else if(is_array($var)){
                $data[$k] =array_filter($var);

            }
            if(empty($data[$k])){
                unset($data[$k]);
            }
        }
        ksort($data);
        $hmacSource = '';
        foreach($data as $key => $value){
            if (is_array($value)){
                ksort($value);
                foreach ($value as $key2 => $value2) {
                    if(is_object($value2)) {
                        $value2 = array_filter((array)$value2);
                        ksort($value2);
                        foreach ($value2 as $oKey => $oValue) {
                            $oValue.='#';
                            $hmacSource .= trim($oValue);
                        }
                    }else{
                        $value2.='#';
                        $hmacSource .= trim($value2);
                    }
                }
            } else {
                $value.='#';
                $hmacSource .= trim($value);
            }
        }
        $data['hmac'] = hash_hmac("md5", $hmacSource, ConfigurationUtils::getInstance()->getHmacKey($this->merchantId));
        return $data;
    }
    private function absoluteUrl($url, $param=null)
    {
        if ($param !== null) {
            $parse = parse_url($url);

            $port = '';
            if ( ($parse['scheme'] == 'http') && ( empty($parse['port']) || $parse['port'] == 80) ){
                $port = '';
            }else{
                $port = $parse['port'];
            }
            $url = $parse['scheme'].'//'.$parse['host'].$port. $parse['path'];

            if(!empty($parse['query'])){
                parse_str($parse['query'], $output);
                $param = array_merge($output, $param);
            }
            $url .= '?'. http_build_query($param);
        }

        return $url;
    }


    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    function stripPKSC7Padding($source){
        $source = trim($source);
        $char = substr($source, -1);
        $num = ord($char);
        if($num==62)return $source;
        $source = substr($source,0,-$num);
        return $source;
    }
} 