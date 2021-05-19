<?php

/*
 * 个推配置文件
 * @author:william Version 1.0.0 2016-8-10
 */

//个推配置
$config = [
    'appkey' => '2DdkJ2P7sT8UmEBfrjYar9',
    'appid' => 'QDrYM4j9X69SMSjPDX0hD5',
    'mastersecret' => 'gxvc203r1l8TEGAt4MkDK2',
    'host' => 'http://sdk.open.api.igexin.com/apiex.htm',
    'other_keys' => [],
//通知透传模版
    'NotificationTemplate' => [
        'title' => '测试标题', //通知栏标题
        'text' => '测试内容', //通知栏内容
        'logo' => '', //通知栏logo
        'logoURL' => 'http://120.24.168.104:8080/resource/front/images/youliang.logo.jpg', //通知栏logoURL
        'transmissionType' => 2, //1为立即启动，2则广播等待客户端自启动
        'transmissionContent' => '透传测试数据', //透传消息的内容
        'isRing' => TRUE, //是否响铃
        'isVibrate' => TRUE, //是否震动
        'isClearable' => TRUE, //通知栏是否可清除
    ],
    //点击打开网页模版
    'LinkTemplate' => [
        'title' => '测试标题', //通知栏标题
        'text' => '测试内容', //通知栏内容
        'logo' => '', //通知栏logo
        'logoURL' => 'http://120.24.168.104:8080/resource/front/images/youliang.logo.jpg', //通知栏logoURL
        'isRing' => TRUE, //是否响铃
        'isVibrate' => TRUE, //是否震动
        'isClearable' => TRUE, //通知栏是否可清除
        'url' => '', //打开连接地址
    ],
    'NotyPopLoadTemplate' => [
//通知栏
        'notyTitle' => '测试标题', //通知栏标题
        'notyContent' => '测试内容', //通知栏内容
        'notyIcon' => 'http://120.24.168.104:8080/resource/front/images/youliang.logo.jpg', //通知栏logo
        'isBelled' => TRUE, //是否响铃
        'isVibrationed' => TRUE, //是否震动
        'isCleared' => TRUE, //通知栏是否可清除
//弹框
        'popTitle' => '', //弹框标题
        'popContent' => '', //弹框内容
        'popImage' => 'http://120.24.168.104:8080/resource/front/images/youliang.logo.jpg', //弹框图片
        'popButton1' => '', //左键
        'popButton2' => '', //右键
//下载
        'loadIcon' => 'http://120.24.168.104:8080/resource/front/images/youliang.logo.jpg', //下载图片
        'loadTitle' => '测试标题', //下载标题
        'loadUrl' => '', //下载地址
        'isAutoInstall' => TRUE, //是否自动安装
        'isActived' => TRUE, //安装完成后是否自动启动
    ],
    'TransmissionTemplate' => [
        'title' => '测试标题透传', //通知栏标题
        'text' => '测试内容透传', //通知栏内容
        'transmissionType' => 2, //1为立即启动，2则广播等待客户端自启动
        'transmissionContent' => '透传内容', //透传消息的内容
        'badge' => 1,
    ],
];

/* End of file push.php */
/* Location: ./application/config/push.php */