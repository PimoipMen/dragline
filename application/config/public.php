<?php

/*
 * 公共配置
 */

defined('BASEPATH') OR exit('No direct script access allowed');


//配置
$config['sex_list'] = [ '1' => '男', '2' => '女'];
$config['taobaoke'] = [
    'appkey' => '24585473',
    'secret' => 'fa5477c0004b6cb93459eb56f523fa72',
    'pid' => 'mm_125610100_35526269_127126963',
];
//大淘客api
$config['dataoke'] = [
    'api_url' => 'http://api.dataoke.com/index.php?',
    'api_key' => 'f45675bd9f',
];

//好单库api
$config['haodanku'] = [
    'api_url' => 'http://v2.api.haodanku.com/',
    'api_key' => 'hongzhukuaigou',
];


//用户注册后默认头像
$config['default_avatar'] = '/resource/front/images/avatar/avatar.png';
$config['version'] = '1.0.1';

//oauth2验证
$config['oauth2_tables'] = array(
    'client_table' => 'oauth_clients',
    'access_token_table' => 'oauth_access_tokens',
    'refresh_token_table' => 'oauth_refresh_tokens',
    'code_table' => 'oauth_authorization_codes',
    'user_table' => 'oauth_users',
    'jwt_table' => 'oauth_jwt',
    'jti_table' => 'oauth_jti',
    'scope_table' => 'oauth_scopes',
    'public_key_table' => 'oauth_public_keys',
);
