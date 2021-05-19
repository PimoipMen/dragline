<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 
	$content = @file_get_contents('/Users/xt/Downloads/json.txt');
	var_dump(json_decode($content));
	var_dump(urlencode(mb_convert_encoding('阿里发票商家答疑', 'gb2312', 'utf-8')));
	echo "<br>";
	
	//模拟测试
	
	$appkey = '24585473';
	$secret = 'fa5477c0004b6cb93459eb56f523fa72';
	
	include("/top/TopClient.php");
	$c = new TopClient;
	$c->appkey = $appkey;
	$c->secretKey = $secret;
	$req = new TbkItemGetRequest;
	$req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
	$req->setQ("女装");
	/*$req->setCat("16,18");
	$req->setItemloc("杭州");
	$req->setSort("tk_rate_des");
	$req->setIsTmall("false");
	$req->setIsOverseas("false");
	$req->setStartPrice("10");
	$req->setEndPrice("10");
	$req->setStartTkRate("123");
	$req->setEndTkRate("123");
	$req->setPlatform("1");
	$req->setPageNo("123");
	$req->setPageSize("20");*/
	$resp = $c->execute($req);
	echo "<pre>";
	print_r($resp);	
	print_r($req);	
?>