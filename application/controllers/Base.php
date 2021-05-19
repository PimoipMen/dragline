<?php

/**
 * 所有业务逻辑的父类
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Base extends CI_Controller {

    public $data = array();

    public function __construct()
    {
        //echo ENVIRONMENT;
        parent::__construct();
        $this->init_data();
        //$this->session->set_userdata('user_id', $user_info['id']);
        $this->data = array_merge($this->data, $this->session->get_userdata());
    }

    public function init_data()
    {
        $this->data['version'] = $this->config->item('version'); // BASEPATH; //站点物理根目录
        $this->data['webinfo'] = $this->config->item('webinfo');
        $this->data['base_url'] = base_url();       // $this->config->item('base_url');
        $this->data['upload_path'] = base_url() . 'resource/front/uploads/';
        $this->data['template_path'] = '/resource/front/css/mobile/';
    }

    /**
     * 统一API参数检验方法
     * 调用示例 check_param(array('money' => array('required', 'integer', 'greater_than_equal_to[1]', 'less_than_equal_to[200]')));
     * @param   array       $arr
     * @return  boolean
     */
    public function check_param($arr, $data = array(), $method = 'get')
    {
        /**
         * 设置要验证的请求数据
         */
        if (!empty($arr)) {
            $key_arr = array();
            $rule_arr = array();
            $tmp_arr = [];
            foreach ($arr as $key => $value) {
                $tmp_arr[$key] = array_shift($value);
                $temp_arr = explode(",", $key);
                if (!is_array($value)) {
                    $value = explode("|", $value);
                }
                $key_arr = array_merge($key_arr, $temp_arr);
                if (!empty($temp_arr)) {
                    foreach ($temp_arr as $temp_value) {
                        if (!empty($rule_arr[$temp_value])) {
                            $rule_arr[$temp_value] = array_merge($rule_arr[$temp_value], $value);
                        } else {
                            $rule_arr[$temp_value] = $value;
                        }
                    }
                }
            }
            $key_arr = array_unique($key_arr);
            if (!empty($rule_arr)) {
                foreach ($rule_arr as $rule_key => $rule_value) {
                    $rule_arr[$rule_key] = array_unique($rule_value);
                }
            }
        }
        if ($method === 'post' || $method === 'POST') {
            $request_data = $this->input->post($key_arr, TRUE);
        } else {
            $request_data = $this->input->get($key_arr, TRUE);
        }

        if ('get_post' == $method) {
            $request_data = [];
            foreach ($key_arr as $one_key) {
                $request_data[$one_key] = $this->input->get_post($one_key, TRUE);
            }
        }

        $this->form_validation->set_data($request_data);
        /**
         * 设置验证规则
         */
        if (!empty($rule_arr)) {
            foreach ($rule_arr as $rule_key => $rule_value) {
                $this->form_validation->set_rules($rule_key, '', $rule_value, array('required' => '%s 不能为空;'
                    , 'integer' => '%s 必须是数字;'
                    , 'regex_match' => '%s 格式有误;'
                    , 'greater_than' => '%s 有误;'
                    , 'max_length' => '%s 超过长度;'
                    , 'min_length' => '%s 长度不够;'
                ));
            }
        }
        /**
         * 开始验证
         */
        if (!$this->form_validation->run()) {
            //验证失败处理逻辑
            $errmsg = validation_errors(' ', ' ');
            if (!empty($tmp_arr)) {
                foreach ($tmp_arr as $arr_key => $arr_value) {
                    if ($arr_value) {
                        $errmsg = str_replace($arr_key, $tmp_arr[$arr_key], $errmsg);
                    }
                }
            }
            $this->returnError($errmsg . "请检查是否正确", '400');
            return FALSE;
        }
        return $request_data;
    }

    /**
     * 跳至错误页面
     * @param string $type
     * @param array $postData
     * @param string $postDasta['msg']
     * @return false
     */
    public function gotoPage($type, $postData)
    {
        $data = $postData ? $postData : array();
        switch ($type) {
            case 'error_page':
                $data['title'] = @$data['title'] ? $data['title'] : '参数非法';
                $this->twig->render('front/error_page', $data);
                break;
            case '404':
                $data['title'] = @$data['title'] ? $data['title'] : '页面不存在';
                $this->twig->render('front/404', $data);
                break;
            case 'nodata':   // 结果为空 
                $data['title'] = @$data['title'] ? $data['title'] : '暂无数据';
                $this->twig->render('front/nodata', $data);
                break;
            default:    //默认404
                $data['title'] = @$data['title'] ? $data['title'] : '页面不存在';
                $this->twig->render('front/404', $data);
                break;
        }
        exit();
    }

    /**
     *  api返回数据格式
     * @param array $data
     * @param int $code
     * @param string $msg
     */
    public function returnData($data = [], $msg = '', $code = 0)
    {
        $ret_data = array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        );
        echo json_encode($ret_data);
        exit;
    }

    /**
     * 获取数据失败提示
     * @param number $code
     * @param string $msg
     */
    public function returnError($msg, $code = -1)
    {
        $this->returnData(array(), $msg, $code);
    }

    /**
     * 获取验证码
     */
    public function get_captcha()
    {
        $this->load->library('captcha');
        $this->captcha->set_param([ 'img_width' => 150]);
        $res = $this->captcha->build_captcha();
        $this->session->set_userdata('captcha', $res['word']);
        $data = ['image_url' => $res['image_url']];
        $this->returnData($data);
    }

}
