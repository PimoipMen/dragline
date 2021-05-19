<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . "/third_party/GETUI_PHP_SDK/IGt.Push.php");
require_once(APPPATH . "/third_party/GETUI_PHP_SDK/igetui/IGt.AppMessage.php");
require_once(APPPATH . "/third_party/GETUI_PHP_SDK/igetui/IGt.APNPayload.php");
require_once(APPPATH . "/third_party/GETUI_PHP_SDK/igetui/template/IGt.BaseTemplate.php");
require_once(APPPATH . "/third_party/GETUI_PHP_SDK/IGt.Batch.php");

/*
 * 封装个推推送类
 * @author:william Version 1.0.0 2016-8-8
 */

/**
 * 封装个推推送类
 *
 * @author william
 */
class Push {

    protected $appkey;
    protected $appid;
    protected $mastersecret;
    protected $host;
    protected $CI;
    protected $config;

    public function __construct($config)
    {
        $this->CI = & get_instance();
        $this->config = $config;
        $this->appkey = $config['appkey'];
        $this->appid = $config['appid'];
        $this->mastersecret = $config['mastersecret'];
        $this->host = $config['host'];
    }

    /**
     * 重新设置keys
     * @param type $platform
     */
    public function reset_config($platform)
    {
        $other_keys = $this->config['other_keys'];
        if (array_key_exists($platform, $other_keys)) {
            $this->appkey = $other_keys[$platform]['appkey'];
            $this->appid = $other_keys[$platform]['appid'];
            $this->mastersecret = $other_keys[$platform]['mastersecret'];
        } else {
            if ($this->config['appkey'] != $this->appkey) {//需要重置到默认的key
                $this->appkey = $this->config['appkey'];
                $this->appid = $this->config['appid'];
                $this->mastersecret = $this->config['mastersecret'];
            }
        }
    }

    public function debug()
    {
        var_dump($this->appkey, $this->appid, $this->mastersecret);
    }

    /**
     * 推送单用户
     *
     * @param $template
     * @param $clientid  2fb5fabb512752c6d8e5629480bf6024 我的设备id
     */
    public function pushMessageToSingle($template, $clientid)
    {
        $igt = new IGeTui($this->host, $this->appkey, $this->mastersecret);
        //个推信息体
        $message = new IGtSingleMessage();
        $message->set_isOffline(true); //是否离线
        $message->set_offlineExpireTime(172800000); //离线时间
        $message->set_data($template); //设置推送消息类型
        //接收方
        $target = new IGtTarget();
        $target->set_appId($this->appid);
        $target->set_clientId($clientid);
        try {
            $rep = $igt->pushMessageToSingle($message, $target);
        } catch (RequestException $e) {
            $requstId = $e->getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target, $requstId);
        }

        return $rep;
    }

    /**
     * 给所有用户推送
     *
     * @param $phoneType
     * @param $template
     */
    public function pushMessageToApp($phoneType, $template)
    {
        //此方式可通过获取服务端地址列表判断最快域名后进行消息推送，每10分钟检查一次最快域名
        $igt = new IGeTui('', $this->appkey, $this->mastersecret);
        //个推信息体
        //基于应用消息体
        $message = new IGtAppMessage();
        $message->set_isOffline(true);
        //离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_offlineExpireTime(172800000);
        $message->set_data($template);
        //设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $message->set_PushNetWorkType(0);
        //设置群推接口的推送速度，单位为条/秒，例如填写100，则为100条/秒。仅对指定应用群推接口有效。
        $message->set_speed(1000);
        $message->set_appIdList(array($this->appid));
        if ($phoneType == 1) {
            $message->set_phoneTypeList(array('ANDROID'));
        } else {
            $message->set_phoneTypeList(array('IOS'));
        }
        $rep = $igt->pushMessageToApp($message);
//        var_dump($rep);
        return $rep;
    }

    /**
     *  设置用户标签
     *
     * @param type string $clientid
     * @param type array $tagList
     * */
    public function setUserTags($clientid, $tagList)
    {
        $igt = new IGeTui($this->host, $this->appkey, $this->mastersecret);
        return $igt->setClientTag($this->appid, $clientid, $tagList);
    }

    /**
     * 获取用户标签
     *
     * @param type string $clientid
     * */
    public function getUserTags($clientid)
    {
        $igt = new IGeTui($this->host, $this->appkey, $this->mastersecret);
        return $igt->getUserTags($this->appid, $clientid);
    }

    /**
     * 通知透传模版
     * 使用这个通知模版的时候的透传消息要点击了通知栏才能收到
     * @param $data
     * @return IGtNotificationTemplate
     */
    public function NotificationTemplate($data = [], $phoneType = 1)
    {
        $data = array_merge($this->config['NotificationTemplate'], $data);
        $template = new IGtNotificationTemplate();
        //应用appid
        $template->set_appId($this->appid);
        //应用appkey
        $template->set_appkey($this->appkey);
        //收到消息是否立即启动应用：1为立即启动，2则广播等待客户端自启动
        $template->set_transmissionType($data['transmissionType']);
        //透传内容
        $template->set_transmissionContent($data['transmissionContent']);
        if (1 === $phoneType) {//安卓手机通知透传模版
            //通知栏标题
            $template->set_title($data['title']);
            //通知栏内容
            $template->set_text($data['text']);
            //通知栏logo
            $template->set_logo($data['logo']);
            //通知栏logoURL
            $template->set_logoURL($data['logoURL']);
            //是否响铃
            $template->set_isRing($data['isRing']);
            //是否震动
            $template->set_isVibrate($data['isVibrate']);
            //通知栏是否可清除
            $template->set_isClearable($data['isClearable']);
            //        $BEGINTIME = date("Y-m-d H:i:s");
            //        $ENDTIME = date("Y-m-d H:i:s", time() + 300);
            //        //设置ANDROID客户端在此时间区间内展示消息
            //        $template->set_duration($BEGINTIME, $ENDTIME);

            return $template;
        }

        //TODO:IOS待添加
        // 如下有两个推送模版，一个简单一个高级，可以互相切换使用。此处以高级为例，所以把简单模版注释掉。
//        APN简单推送
        $apn = new IGtAPNPayload();
        $alertmsg = new SimpleAlertMsg();
        $alertmsg->alertMsg = $data['title'];
        $alertmsg->body = $data['text'];
        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $apn->sound = "";
        $apn->add_customMsg("payload", "payload");
//        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        //APN高级推送
//        $apn = new IGtAPNPayload();
//        $alertmsg = new DictionaryAlertMsg();
//        $alertmsg->body = $data['text'];
//        $alertmsg->actionLocKey = "ActionLockey";
//        $alertmsg->locKey = $data['text'];
//        $alertmsg->locArgs = array("locargs");
//        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
//        $alertmsg->title = $data['title'];
//        $alertmsg->titleLocKey = "TitleLocKey";
//        $alertmsg->titleLocArgs = array("TitleLocArg");
//        $apn->alertMsg = $alertmsg;
//        $apn->badge = 7;
//        $apn->sound = "";
//        $apn->add_customMsg("payload", "payload");
//        $apn->contentAvailable = 1;
//        $apn->category = "ACTIONABLE";
//        $template->set_apnInfo($apn);
        return $template;
    }

    /**
     * 点击打开网页模版
     *
     * @param $data
     * @return IGtLinkTemplate
     */
    public function LinkTemplate($data)
    {
        $data = array_merge($this->config['LinkTemplate'], $data);
        $template = new IGtLinkTemplate();
        //应用appid
        $template->set_appId($this->appid);
        //应用appkey
        $template->set_appkey($this->appkey);
        //通知栏标题
        $template->set_title($data['title']);
        //通知栏内容
        $template->set_text($data['text']);
        //通知栏logo
        $template->set_logo($data['logo']);
        //通知栏logo链接
        $template->set_logoURL($data['logoURL']);
        //是否响铃
        $template->set_isRing($data['isRing']);
        //是否震动
        $template->set_isVibrate($data['isVibrate']);
        //通知栏是否可清除
        $template->set_isClearable($data['isClearable']);
        //打开连接地址
        $template->set_url($data['url']);
        //TODO:IOS待添加
        return $template;
    }

    /**
     * 通知栏弹框下载 IOS 不支持
     *
     * @param $data
     * @return IGtNotyPopLoadTemplate
     */
    public function NotyPopLoadTemplate($data)
    {
        $data = array_merge($this->config['NotyPopLoadTemplate'], $data);
        $template = new IGtNotyPopLoadTemplate();
        $template->set_appId($this->appid);   //应用appid
        $template->set_appkey($this->appkey); //应用appkey
        //通知栏
        //通知栏标题
        $template->set_notyTitle($data['notyTitle']);
        //通知栏内容
        $template->set_notyContent($data['notyContent']);
        //通知栏logo
        $template->set_notyIcon($data['notyIcon']);
        //是否响铃
        $template->set_isBelled($data['isBelled']);
        //是否震动
        $template->set_isVibrationed($data['isVibrationed']);
        //通知栏是否可清除
        $template->set_isCleared($data['isCleared']);
        //弹框
        //弹框标题
        $template->set_popTitle($data['popTitle']);
        //弹框内容
        $template->set_popContent($data['popContent']);
        //弹框图片
        $template->set_popImage($data['popImage']);
        //左键
        $template->set_popButton1($data['popButton1']);
        //右键
        $template->set_popButton2($data['popButton2']);
        //下载
        //弹框图片
        $template->set_loadIcon($data['loadIcon']);
        //弹框标题
        $template->set_loadTitle($data['loadTitle']);
        //下载地址
        $template->set_loadUrl($data['loadUrl']);
        //是否自动安装
        $template->set_isAutoInstall($data['isAutoInstall']);
        //安装完成后是否自动启动
        $template->set_isActived($data['isActived']);
        return $template;
    }

    /**
     * 透传模版
     * 单独用这个模版用户可以在不点击通知栏的情况下收到透传消息
     * @param $data
     * @return IGtTransmissionTemplate
     */
    public function TransmissionTemplate($data, $phoneType = 1)
    {
        $data = array_merge($this->config['TransmissionTemplate'], $data);
        $template = new IGtTransmissionTemplate();
        //应用appid
        $template->set_appId($this->appid);
        //应用appkey
        $template->set_appkey($this->appkey);
        //收到消息是否立即启动应用，1为立即启动，2则广播等待客户端自启动
        $template->set_transmissionType($data['transmissionType']);
        //透传内容
        $template->set_transmissionContent($data['transmissionContent']);
        if (1 == $phoneType) {
            return $template;
        }
        //TODO:IOS待添加
        //APN高级推送
        $apn = new IGtAPNPayload();
        $alertmsg = new DictionaryAlertMsg();
        $alertmsg->body = $data['text'];
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = $data['text'];
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
        $alertmsg->title = $data['title'];
        $alertmsg->titleLocKey = $data['title'];
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $apn->alertMsg = $alertmsg;
        $apn->badge = $data['badge'];
        $apn->sound = "";
        $apn->add_customMsg("payload", "payload");
//        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        return $template;
    }

}
