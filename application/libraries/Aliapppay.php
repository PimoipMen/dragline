<?php

error_reporting(-1);
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'third_party/alipay-sdk-PHP/AopSdk.php';
/*
 * 支付宝app支付
 * @author:william Version 1.0.0 2016-10-9
 */

/**
 * 支付宝app支付
 *
 * @author william
 */
class Aliapppay {

    //put your code here
    protected $config;

    public function __construct()
    {
        $this->config = [
            'order_body' => '极联开发科技',
            'subject' => '极联开发科技',
            'notify_url' => base_url() . 'pay/index/aliapp_return',
        ];
    }

    /**
     * 创建待支付订单
     * @param type $out_trade_no 交易订单号
     * @param type $order_price 订单金额
     * @return mix 正常返回一个数组
     */
    public function create_unified_order($out_trade_no, $order_price)
    {
        $c = new AopClient;
        $c->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $c->appId = "2016100802058511";
//        $c->privateKey = "UqRGdHb2ExT0ORYMaCtozg==";
        $c->encryptKey = "UqRGdHb2ExT0ORYMaCtozg==";
        $c->format = "json";
        $c->rsaPrivateKeyFilePath = APPPATH . 'third_party/alipay-sdk-PHP/rsa_private_key.pem';
        $c->debugInfo = TRUE;
//        $c->alipayPublicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB";
        $c->alipayPublicKey = APPPATH . 'third_party/alipay-sdk-PHP/rsa_public_key.pem';
        $biz_content = [
            'body' => $this->config['order_body'],
            'subject' => $this->config['subject'],
            'out_trade_no' => $out_trade_no,
            'total_amount' => $order_price,
            'product_code' => 'QUICK_APP_PAY',
        ];
        //实例化具体API对应的request类,类名称和接口名称对应，当前调用接口名称：alipay.offline.material.image.upload 
        $request = new AlipayTradeAppPayRequest();
        $request->setBizContent(json_encode($biz_content));
        $request->setNotifyUrl($this->config['notify_url']);
        $request->setNeedEncrypt(TRUE);
        $response = $c->execute($request);
        //获取图片地址
        var_dump($response, $request, $c);
    }

}
