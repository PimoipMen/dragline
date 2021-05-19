<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'third_party/taobao-sdk-PHP/TopSdk.php';

/**
 * 淘宝客商品
 * @author jacky
 */
class Taobaoke {

    private $_CI;
    private $cfg;
    private $_TopClient;

    public function __construct()
    {
        $this->_CI = &get_instance();
        $this->_TopClient = new TopClient;
        $this->cfg = $this->_CI->config->item('taobaoke');
        $this->_TopClient->appkey = $this->cfg['appkey'];
        $this->_TopClient->secretKey = $this->cfg['secret'];
    }

    /**
     * 淘宝客商品查询
     */
    public function tbk_item_get($postData = [])
    {
        $data = [
            'q' => isset($postData['keyword']) ? $postData['keyword'] : '', //查询词，例如：女包
            'cat' => isset($postData['cat']) ? $postData['cat'] : '16,18', //后台类目ID，用,分割，最大10个，该ID可以通过taobao.itemcats.get接口获取到
            'fields' => isset($postData['fields']) ? $postData['fields'] : 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick',
            'itemloc' => isset($postData['itemloc']) ? $postData['itemloc'] : '', //所在地,例如：深圳
            'sort' => isset($postData['sort']) ? $postData['sort'] : '', //排序_des（降序），排序_asc（升序），销量（total_sales），淘客佣金比率（tk_rate）， 累计推广量（tk_total_sales），总支出佣金（tk_total_commi）
            'is_tmall' => isset($postData['is_tmall']) ? $postData['is_tmall'] : false, //是否商城商品，设置为true表示该商品是属于淘宝商城商品，设置为false或不设置表示不判断这个属性
            'is_overseas' => isset($postData['is_overseas']) ? $postData['is_overseas'] : false, //是否海外商品，设置为true表示该商品是属于海外商品，设置为false或不设置表示不判断这个属性
            'start_price' => isset($postData['start_price']) ? $postData['start_price'] : 10, //折扣价范围下限，单位：元
            'end_price' => isset($postData['end_price']) ? $postData['end_price'] : 10, //折扣价范围上限，单位：元
            'start_tk_rate' => isset($postData['start_tk_rate']) ? $postData['start_tk_rate'] : 10.00, //淘客佣金比率上限，如：1234表示12.34%
            'end_tk_rate' => isset($postData['end_tk_rate']) ? $postData['end_tk_rate'] : 100.00, //淘客佣金比率下限，如：1234表示12.34%
            'platform' => isset($postData['platform']) ? $postData['platform'] : 1, //链接形式：1：PC，2：无线，默认：1
            'page_no' => isset($postData['page_no']) ? $postData['page_no'] : 1, //第几页，默认：1
            'page_size' => isset($postData['page_size']) ? $postData['page_size'] : 20, //页大小，默认20，1~100
        ];
        $req = new TbkItemGetRequest;
        $req->setFields($data['fields']);
        $req->setQ($data['q']);
        $req->setCat($data['cat']);
        $req->setItemloc($data['itemloc']);
        $req->setSort($data['sort']);
        $req->setIsTmall($data['is_tmall']);
        $req->setIsOverseas($data['is_overseas']);
        $req->setStartPrice($data['start_price']);
        $req->setEndPrice($data['end_price']);
        $req->setStartTkRate($data['start_tk_rate']);
        $req->setEndTkRate($data['end_tk_rate']);
        $req->setPlatform($data['platform']);
        $req->setPageNo($data['page_no']);
        $req->setPageSize($data['page_size']);
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

    /**
     * 淘宝客商品详情(简版)
     * @param type $num_iids  商品ID串，用,分割，从taobao.tbk.item.get接口获取num_iid字段，最大40个
     * @param type $fields    需返回的字段列表
     * @param type $platform  链接形式：1：PC，2：无线，默认：1
     * @return type
     */
    public function tbk_item_info_get($num_iids = '', $fields = '*', $platform = 1)
    {
        $fields = !empty($fields) ? $fields : 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url';
        $platform = isset($platform) ? $platform : 1;
        $num_iids = isset($num_iids) ? $num_iids : '';
        $req = new TbkItemInfoGetRequest;
        $req->setFields($fields);
        $req->setPlatform($platform);
        $req->setNumIids($num_iids);
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

    /**
     * 淘宝客商品链接转换
     * @param type $num_iids    商品ID串，用','分割，从taobao.tbk.item.get接口获取num_iid字段，最大40个 
     * @param type $adzone_id   广告位ID，区分效果位置 
     * @param type $fields      需返回的字段列表
     * @param type $platform    链接形式：1：PC，2：无线，默认：1
     */
    public function tbk_item_convert($num_iids = '', $adzone_id = '', $fields = '*', $platform = 1)
    {
        $unid = 'ykp'; //自定义输入串，英文和数字组成，长度不能大于12个字符，区分不同的推广渠道 
        $num_iids = !empty($num_iids) ? $num_iids : '';
        $adzone_id = !empty($adzone_id) ? $adzone_id : '';
        $platform = isset($platform) ? $platform : 1;
        $fields = !empty($fields) ? $fields : 'num_iid,click_url';
        $req = new TbkItemConvertRequest;
        $req->setFields($fields);
        $req->setNumIids($num_iids);
        $req->setAdzoneId($adzone_id);
        $req->setPlatform($platform);
        $req->setUnid($unid);
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

    /**
     * 淘宝客淘口令
     * @param type $user_id     生成口令的淘宝用户ID 
     * @param type $text        口令弹框内容
     * @param type $url         口令跳转目标页
     * @param type $logo        口令弹框logoURL 
     * @param type $ext         扩展字段JSON格式 
     */
    public function tbk_tpwd_create($user_id = '1', $text = '', $url = '', $logo = '', $ext = '{}')
    {
        $user_id = isset($user_id) ? $user_id : 1;
        $text = isset($text) ? $text : '';
        $url = isset($url) ? $url : '';
        $logo = isset($logo) ? $logo : '';
        $ext = isset($ext) ? $ext : '';
        $req = new TbkTpwdCreateRequest;
        $req->setUserId($user_id);
        $req->setText($text);
        $req->setUrl($url);
        $req->setLogo($logo);
        $req->setExt("{}");
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

    /**
     * 好券清单API【导购】
     * @param type $q
     * @param type $cat
     * @param type $page_no
     * @param type $page_size
     * @param type $platform    1：PC，2：无线，默认：1 
     * @param type $adzone_id   mm_xxx_xxx_xxx的第三位 
     */
    public function tbk_dg_item_coupon($q = '', $cat = '', $page_no = 1, $page_size = 2, $platform = 1, $adzone_id = '127126963')
    {
        $req = new TbkDgItemCouponGetRequest;
        $req->setAdzoneId($adzone_id);
        $req->setPlatform($platform);
        $req->setCat($cat);
        $req->setPageSize($page_size);
        $req->setQ($q);
        $req->setPageNo($page_no);
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

    /**
     * 阿里妈妈推广券信息查询
     * @param type $key  带券ID与商品ID的加密串 例如：
     */
    public function tbk_coupon_search($key)
    {
        $key = 'nfr%2BYTo2k1PX18gaNN%2BIPkIG2PadNYbBnwEsv6mRavWieOoOE3L9OdmbDSSyHbGxBAXjHpLKvZbL1320ML%2BCF5FRtW7N7yJ056Lgym4X01A%3D';
        $req = new TbkCouponGetRequest;
        $req->setMe($key);
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

    /**
     * 物料传播方式获取,生成推广链接
     * @param type $request
     */
    public function tbk_spread($requests = '', $url = 'http://temai.taobao.com')
    {
        $req = new TbkSpreadGetRequest;
        $requests = new TbkSpreadRequest;
        $requests->url = $url;
        $req->setRequests(json_encode($requests));
        $resp = $this->_TopClient->execute($req);
        return objectToArray($resp);
    }

}
