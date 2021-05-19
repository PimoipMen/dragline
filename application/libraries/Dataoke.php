<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大淘客API
 * @author jacky
 */
class DaTaoke {

    private $_CI;
    private $cfg;
    private $_ApiKey;
    private $_ApiUrl;
    private $_UrlParam;
    private $_CharSet;

    public function __construct()
    {
        $this->_CI = &get_instance();
        $this->cfg = $this->_CI->config->item('dataoke');
        $this->_ApiKey = $this->cfg['api_key'];
        $this->_ApiUrl = $this->cfg['api_url'];
        $this->_CharSet = 2; //2: utf-8编码的json格式数据1(默认值): 返回gbk编码json格式数据（此参数主要为了兼容之前的数据,后续将逐步取消,建议使用2）
        $this->url_param = [
            'web_api' => 'r=goodsLink/www&', //网站专用API接口
            'top_100' => 'r=Port/index&', //TOP100人气榜API接口
            'get_gift' => 'r=Port/index&', //全站领券商品API接口     
            'goods_trunk' => 'r=Port/index&', //全站领券商品API接口 
            'goods_info' => 'r=Port/index&', //全站领券商品API接口 
        ];
    }

    /**
     * 网站专用API接口
     * @param type $page
     * @param type $v
     * @param type $type
     * @return type
     */
    public function get_web_goods($page_size = 20, $type = 'www_quan')
    {
        $postdata = [
            'appkey' => $this->_ApiKey,
            'v' => $this->_CharSet,
            'type' => $type,
            'page' => $page_size
        ];
        $url = $this->_ApiUrl . $this->url_param['web_api'] . http_build_query($postdata);
        $res = curl_postdata($url);
        $rs = json_decode($res['result'], true);
        if ($res['code'] != 200 || empty($rs)) {
            writelog(['msg' => '大淘客获取【网站专用API接口】失败,或返回空', 'param' => $res]);
            return false;
        }
        return $rs;
    }

    /**
     * TOP100人气榜API接口
     * @param type $type
     * @return boolean
     */
    public function get_top100_goods($type = 'top100')
    {
        $postdata = [
            'appkey' => $this->_ApiKey,
            'v' => $this->_CharSet,
            'type' => $type,
        ];
        $url = $this->_ApiUrl . $this->url_param['top_100'] . http_build_query($postdata);
        $res = curl_postdata($url);
        $rs = json_decode($res['result'], true);
        if ($res['code'] != 200 || empty($rs)) {
            writelog(['msg' => '大淘客获取【TOP100人气榜API接口】失败,或返回空', 'param' => $res]);
            return false;
        }
        return $rs;
    }

    /**
     * 全站领券商品API接口
     * @param type $page_size
     * @param type $type
     * @return boolean
     */
    public function get_hasgift_goods($page_size = 20, $type = 'total')
    {
        $postdata = [
            'appkey' => $this->_ApiKey,
            'v' => $this->_CharSet,
            'type' => $type,
            'page' => $page_size
        ];
        $url = $this->_ApiUrl . $this->url_param['get_gift'] . http_build_query($postdata);
        $res = curl_postdata($url);
        $rs = json_decode($res['result'], true);
        if ($res['code'] != 200 || empty($rs)) {
            writelog(['msg' => '大淘客获取【全站领券商品API接口】失败,或返回空', 'param' => $res]);
            return false;
        }
        return $rs;
    }

    /**
     * 实时跑量榜API接口
     * @param type $type
     * @return boolean
     */
    public function get_goods_trunk($type = 'paoliang')
    {
        $postdata = [
            'appkey' => $this->_ApiKey,
            'v' => $this->_CharSet,
            'type' => $type,
        ];
        $url = $this->_ApiUrl . $this->url_param['goods_trunk'] . http_build_query($postdata);
        $res = curl_postdata($url);
        $rs = json_decode($res['result'], true);
        if ($res['code'] != 200 || empty($rs)) {
            writelog(['msg' => '大淘客获取【实时跑量榜API接口】失败,或返回空', 'param' => $res]);
            return false;
        }
        return $rs;
    }

    /**
     * 单品详情API接口
     * @param type $goods_id
     * @return boolean
     */
    public function get_goods_info($goods_id = 0)
    {
        $postdata = [
            'appkey' => $this->_ApiKey,
            'v' => $this->_CharSet,
            'id' => $goods_id,
        ];
        $url = $this->_ApiUrl . $this->url_param['goods_info'] . http_build_query($postdata);
        $res = curl_postdata($url);
        $rs = json_decode($res['result'], true);
        if ($res['code'] != 200 || empty($rs)) {
            writelog(['msg' => '大淘客获取【单品详情API接口】失败,或返回空', 'param' => $res]);
            return false;
        }
        return $rs;
    }

}
