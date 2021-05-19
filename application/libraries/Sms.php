<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'third_party/taobao-sdk-PHP/TopSdk.php';

/**
 * 
  {
  "alibaba_aliqin_fc_sms_num_send_response":{
  "result":{
  "err_code":"0",
  "model":"134523^4351232",
  "success":false,
  "msg":"成功"
  }
  }
  }
  {
  "error_response":{
  "code":50,
  "msg":"Remote service error",
  "sub_code":"isv.invalid-parameter",
  "sub_msg":"非法参数"
  }
  }
 */

/**
 * Description of Sms
 *
 * @author liang
 */
class Sms {

    /**
     * CodeIgniter instance
     *
     * @var object
     */
    private $_CI;
    public $config;
    private $topclient;
    private $req;

    public function __construct($config)
    {
        $this->_CI = &get_instance();
        $this->config = $config;
        $this->topclient = new TopClient;
        $this->topclient->appkey = $this->config['appkey'];
        $this->topclient->secretKey = $this->config['secretKey'];
        $this->topclient->format = 'json';
        $this->req = new AlibabaAliqinFcSmsNumSendRequest;
//        $this->req->setExtend("123456");//暂时不需要这个参数
        $this->req->setSmsType($this->config['sms_type']);
        $this->req->setSmsFreeSignName($this->config['sms_free_sign_name']);
    }

    /**
     * 发送短信验证码
     * @param type $vcode
     * @param type $mobile
     * @return boolean
     */
    public function send_vcode($vcode, $mobile)
    {
        $params = [
            'number' => strval($vcode),
        ];
        $params = json_encode($params);
        $this->req->setSmsParam($params);
        $this->req->setRecNum($mobile);
        $this->req->setSmsTemplateCode($this->config['sms_template_code_vcode']);
        $resp = $this->topclient->execute($this->req);
        $resp_arr = objectToArray($resp);
        if (isset($resp_arr['result']['success']) and $resp_arr['result']['success']) {
            return TRUE;
        }
        writelog(['resp' => $resp]); //记录错误日志
        return FALSE;
    }

    /**
     * 企业信使--短信发送 （有量）
     * @param type $vcode
     * @param type $mobile
     * @return boolean
     */
    public function send_vcode_qyxs($content, $mobile, $send_time_type = '')
    {
        $qyxs_msg = $this->_CI->config->item('qyxs_msg');
        $timestamp = date('YmdHis');
        $url = $qyxs_msg['url'];
        $postdata = [
            'action' => $qyxs_msg['action'],
            'userid' => $qyxs_msg['userid'],
            'timestamp' => $timestamp,
            'sign' => md5($qyxs_msg['account'] . $qyxs_msg['pwd'] . $timestamp),
            'mobile' => $mobile,
            'content' => $qyxs_msg['sign_name'] . $content, // "您的验证码是{$vcode}，快去完成验证吧~",            
            'sendTime' => $send_time_type, //空是立即发送,  定时格式2016-09-22 22:08:10
            'extno' => $qyxs_msg['extno'],
        ];
        $res = curl_postdata($url, $postdata);
        $rs = xmlToArray($res['result']);
        if ($rs['returnstatus'] == 'Success' && $rs['message'] == 'ok') {
            return TRUE;
        }
        writelog(['msg' => '企业信使短信发送失败', 'postdata' => $postdata, 'res' => $res]);
        return FAlSE;
    }

}
