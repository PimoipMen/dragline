<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/controllers/Base.php';

class Home extends Base {

    public function index()
    {
        $this->data['key'] = '龙采头条';
        $this->twig->render('front/index/index.html', $this->data);
    }
    
    public function login(){
    	$this->data['key'] = '登录';
        $this->twig->render('front/user/login.html', $this->data);
    }
    
    public function register(){
    	$this->data['key'] = '注册';
        $this->twig->render('front/user/register.html', $this->data);
    }
    
    public function invite(){
    	$this->data['key'] = '邀请好友';
        $this->twig->render('front/index/invite.html', $this->data);
    }
    
    public function invite_f(){
    	$this->data['key'] = '面对面邀请好友';
        $this->twig->render('front/index/invite_f.html', $this->data);
    }
    
    public function download(){
    	$this->data['key'] = '下载';
        $this->twig->render('front/index/download.html', $this->data);
    }
    
    public function video()
    {
        $this->data['key'] = '龙采头条视频';
        $this->twig->render('front/index/video.html', $this->data);
    }
	
	public function canvas()
    {
        $this->data['key'] = '龙采头条';
        $this->twig->render('front/index/canvas.html', $this->data);
    }
    
    public function forwardTwo(){
		$this->data['key'] = '我要提现';
        $this->twig->render('front/index/forwardTwo.html', $this->data);
	}
	
	 public function forwardThree(){
		$this->data['key'] = '我要提现第二步';
        $this->twig->render('front/index/forwardThree.html', $this->data);
	}
    
    public function accountBankCard()
    {
        $this->data['key'] = '到账银行卡列表';
        $this->twig->render('front/index/accountBankCard.html', $this->data);
    }
	
	public function addCard()
    {
        $this->data['key'] = '添加银行卡';
        $this->twig->render('front/index/addCard.html', $this->data);
    }
    public function addphone()
    {
        $this->data['key'] = '添加银行卡第二部';
        $this->twig->render('front/index/addphone.html', $this->data);
    }
	public function forgetpwd()
    {
        $this->data['key'] = '修改密码';
        $this->twig->render('front/index/forget.html', $this->data);
    }
    public function forgetpwdOne()
    {
        $this->data['key'] = '修改密码第二部';
        $this->twig->render('front/index/forgetOne.html', $this->data);
    }
}
