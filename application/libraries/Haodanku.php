<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 好单库API
 * @author jacky
 * QPS限制：单个APIKEY并发量最高20，超过封锁10分钟，
 * 爬虫控制好接入速度（正常抓取数据入库一般也无需高并发，如非入库形式调用需提量，请联系管理单独定制）
 */
class Haodanku {

    private $_CI;
    private $cfg;
    private $_ApiKey;
    private $_ApiUrl;
    private $_UrlParam;

    public function __construct()
    {
        $this->_CI = &get_instance();
        $this->cfg = $this->_CI->config->item('haodanku');
        $this->_ApiKey = $this->cfg['api_key'];
        $this->_ApiUrl = $this->cfg['api_url'];
        $this->url_param = [
            'all_goods' => 'itemlist/', //全部商品API
        ];
    }

    /**
     * 商品列表页API
     * @param type $page
     * @param type $v
     * @param type $type
     * @return type
     */
    public function get_goods_list($page_size = 20, $nav = 1, $cid = 0)
    {
        $postdata = [
            'appkey' => $this->_ApiKey,
            'back' => $page_size,
            'nav' => $nav,
            'cid' => $cid,
            'mid_id' => 1,
//            'sort' => 0,
//            'price_min' => 100,
//            'price_max' => 200,
//            'sale_min' => 1000,
//            'sale_max' => 5000,
//            'coupon_min' => 10,
//            'coupon_max' => 20,
        ];
        $url = $this->_ApiUrl . $this->url_param['all_goods'] . preg_replace('/[&=]/', '/', http_build_query($postdata));
        $res = curl_postdata($url);
        $rs = json_decode($res['result'], true);
        if ($res['code'] != 200 || empty($rs)) {
            writelog(['msg' => '好单库获取【商品列表页API】失败,或返回空', 'param' => $res]);
            return false;
        }
        return $rs;
    }

}
