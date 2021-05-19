<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'third_party/wxapppay/lib/WxPay.Api.php';
/*
 * 微信支付封装类
 * @author:william Version 1.0.0 2016-5-30
 */

/**
 * 微信支付封装类
 *
 * @author william
 */
class Wxapppay {

    //put your code here
    protected $config;

    public function __construct()
    {
        $this->config = [
            'order_body' => '极联开发科技',
            'attach' => '极联开发科技',
            'goods_tag' => '极联开发科技',
            'notify_url' => base_url() . 'pay/index/wxapp_return',
            'trade_type' => 'APP',
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
        $input = new WxPayUnifiedOrder();
        $input->SetBody($this->config['order_body']);
//        $input->SetAttach($this->config['attach']);
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($order_price * 100);
//        $input->SetTime_start(date("YmdHis"));
//        $input->SetTime_expire(date("YmdHis", time() + 600));
//        $input->SetGoods_tag($this->config['goods_tag']);
        $input->SetNotify_url($this->config['notify_url']);
        $input->SetTrade_type($this->config['trade_type']);
//        $input->SetDevice_info('WEB');
        $order = WxPayApi::unifiedOrder($input);
        if (isset($order['result_code']) and isset($order['return_code']) and $order['return_code'] == $order['result_code'] and $order['result_code'] == 'SUCCESS') {
            return $order;
        }
        writelog([
            'debug_info' => __LINE__ . " " . __FUNCTION__ . "" . __FILE__,
            'order' => $order,
            'info' => '创建支付订单失败',
        ]);
        return FALSE;
    }

}
