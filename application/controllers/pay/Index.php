<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'third_party/wxapppay/lib/WxPay.Api.php';
require APPPATH . 'third_party/wxapppay/lib/WxPay.Notify.php';
/*
 * 支付回调处理
 * @author:william Version 1.0.0 2016-5-30
 */

/**
 * 支付回调处理
 *
 * @author william
 */
class Index extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('message/message');
    }

    /**
     * 微信app支付回调
     */
    public function wxapp_return() {
        writelog(['info' => 'begin notify', 'debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__]);
        $xml = $GLOBALS['HTTP_RAW_POST_DATA']; //获取请求参数
        $obj = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $out_trade_no = $obj["out_trade_no"];
        writelog(['info' => "out_trade_no|{$out_trade_no} obj " . var_export($obj, TRUE), 'debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__]);
        //判断签名
        if (empty($out_trade_no)) {
            echo "请求异常";
            exit;
        }

        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
    }

}

class PayNotifyCallBack extends WxPayNotify {

    private $_CI;

    public function __construct() {
        $this->_CI = &get_instance();
    }

    //查询订单
    public function Queryorder($transaction_id) {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        writelog(['info' => "query_order:" . json_encode($result), 'debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__]);
        if (array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg) {
        writelog(['debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__, 'data' => $data]);
        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            writelog(['debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__, 'info' => $msg]);
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            writelog(['debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__, 'info' => $msg]);
            return false;
        }

        //验证成功 修改数据库
        $out_trade_no = $data['out_trade_no'];
        //------------------------------
        //处理业务开始
        //------------------------------
        //处理数据库逻辑
        //注意交易单不要重复处理
        //注意判断返回金额		

        $total_fee_t = $data['total_fee'] / 100;

        /**
         * 根据$out_trade_no查询对应的订单
         * 增加账户余额操作
         * echo success告诉接口处理成功
         */
        $this->_CI->load->model('um/um_user');
        $this->_CI->load->model('nm/nm_notice');
        $this->_CI->db->trans_begin(); //开始事务
        $cz_order_info = $this->_CI->um_user->recharge_callback($out_trade_no, $total_fee_t, $data["transaction_id"]);
        if (!is_array($cz_order_info)) {
            $this->_CI->db->trans_rollback(); //回滚
            writelog(['debug_info' => __LINE__ . " " . __FILE__ . " " . __FUNCTION__, 'info' => "充值回调失败" . $cz_order_info]);
            echo "fail";
            exit;
        }
        $this->_CI->db->trans_commit(); //提交事务
        //推送消息
        $desc = ($cz_order_info['buy_type'] == 1 ? '积分' : '有量币') . '充值成功';
        $this->_CI->nm_notice->push_notice($cz_order_info['uid'], 1, '系统消息', $desc, '');

        echo "success";
        exit;


        //------------------------------
        //处理业务完毕
        //------------------------------
        return true;
    }

}
