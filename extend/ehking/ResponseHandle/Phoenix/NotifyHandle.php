<?php
/**
 *
 *
 * PHP Version 5
 *
 * @category  Class
 * @file      NotifyHandle.php
 * @package Ehking\ResponseHandle\Phoenix
 * @author    chao.ma <chao.ma@ehking.com>
 */

namespace Ehking\ResponseHandle\Phoenix;


use Ehking\Excation\HmacVerifyException;
use Ehking\Excation\InvalidResponseException;
use Ehking\ResponseHandle\ResponseTypeHandle;
use Ehking\Configuration\ConfigurationUtils;

//$dir=dirname(__FILE__);
//require_once $dir.'/../../Excation/InvalidResponseException.php';
//require_once $dir.'/../../ResponseHandle/ResponseTypeHandle.php';

class NotifyHandle extends ResponseTypeHandle
{

    public function handle($data = array())
    {
        if ($data['status'] == 'SUCCESS') {
            //成功时相关处理代码
            echo "SUCCESS"; //打印出 SUCCESS 表示收到通知
        } elseif ($data['status'] == 'FAILED' || $data['status'] == 'CANCEL') {
            //失败时相关处理代码
            //订单取消
        } elseif ($data['status'] == 'INIT') {
            //待处理状态下相关处理代码
        } else {
            throw new InvalidResponseException(array(
                'error_description' => 'notify response error',
                'responseData' => $data
            ));
        }
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

        $encryptKey = $data['encryptKey'];
        $pem1 = chunk_split($public_key, 64, "\n");
        $pem1 = "-----BEGIN PUBLIC KEY-----\n" . $pem1 . "-----END PUBLIC KEY-----\n";
        $pi_key = openssl_pkey_get_public($pem1);
        openssl_public_decrypt(base64_decode($encryptKey), $decrypted, $pi_key);

        /*
         * AES解密
         *
         */
        $date = base64_decode($data['data']);
        $screct_key = $decrypted;
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
//        $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $date, MCRYPT_MODE_ECB, $iv);
//        $encrypt_str = preg_replace('/[\x00-\x1F]/','', $encrypt_str);
        $ivsize = openssl_cipher_iv_length('AES-128-ECB');
        $iv = openssl_random_pseudo_bytes($ivsize);
        $encrypt_str = openssl_decrypt($date, 'AES-128-ECB', $screct_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        $encrypt_str = preg_replace('/[\x00-\x1F]/', '', $encrypt_str);
        $encrypt_str = json_decode($encrypt_str, true);


        /*
         * 去除空值的元素
         */

        function clearBlank($arr)
        {
            return (array_filter($arr, function ($var) {
                return ($var <> '');//return true or false
            }));
        }

        function array_remove_empty(& $arr, $trim = true)
        {
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
        $hmac = $encrypt_str['hmac'];
        unset($encrypt_str['hmac']);
        ksort($encrypt_str);
        $hmacSource = '';
        foreach ($encrypt_str as $key => $value) {
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
                        foreach ($value2 as $oKey => $oValue) {
                            $oValue .= '#';
                            $hmacSource .= trim($oValue);
                        }
                    }
                }
            } else {
                $value .= '#';
                $hmacSource .= trim($value);
            }
        }
        $sourceHmac = hash_hmac('md5', $hmacSource, ConfigurationUtils::getInstance()->getHmacKey(isset($encrypt_str['merchantId']) ? $encrypt_str['merchantId'] : ''));
        if ($sourceHmac !== $hmac) {
            throw new HmacVerifyException(array(
                'error_description' => 'hmac validation error',
                'responseData' => $encrypt_str
            ));
        }
        return $encrypt_str;
    }

}