<?php

/*
 * Acl权限控制
 * @author:jacky Version 1.0.0 2016-9-19
 */

class Acl {

    private $dir;
    private $url_model;
    private $url_method;
    private $CI;
    private $http_method;
    // 需要登录后才能调用的接口
    private $need_login_token_method = [
        //用户信息
        'api_user/get_userinfo',
    ];

    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('session');
        $this->dir = $this->CI->uri->segment(1);
        $this->url_model = $this->CI->uri->segment(2);
        $this->url_method = $this->CI->uri->segment(3, 'index');
        $this->http_method = $this->CI->input->method();
        if (ENVIRONMENT != 'production' /* and isset($_SERVER['HTTP_USER_AGENT']) and $_SERVER['HTTP_USER_AGENT'] == 'ykp' */) {
            $this->need_login_token_method = [];
        }
    }

    function auth()
    {
        if ('api' !== $this->dir) {//api目录下面的才需要acl
            return FALSE;
        }

        if (!in_array($this->url_model . '/' . $this->url_method, $this->need_login_token_method)) {
            return FALSE;
        }

        $login_token = $this->CI->input->server('HTTP_LOGIN_TOKEN');
        if (empty($login_token)) {
            $login_token = $this->CI->input->get_post('login_token');
        }

        $uid = $this->CI->input->get_post('uid');

        if (empty($uid)) {//没传递这个参数 直接可以不验证
            return FALSE;
        }

        if (!empty($uid) and empty($login_token)) {//uid不为空的时候
            echo json_encode(['code' => '403', 'msg' => 'LOGIN_TOKEN参数不能为空', 'data' => []]);
            exit;
        }

        $this->CI->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $cache_uid = $this->CI->cache->get($login_token);
        if (!$cache_uid) {
            echo json_encode(['code' => '403', 'msg' => '未登录', 'data' => []]);
            exit;
        }

        if ($uid != $cache_uid) {
            echo json_encode(['code' => '403', 'msg' => '您无权读取此用户的信息', 'data' => []]);
            exit;
        }
        $date = date("ymd");
        $this->CI->cache->save($login_token, $cache_uid, 600); //刷新模拟session
    }

}
