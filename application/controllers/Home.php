<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/controllers/Base.php';

class Home extends Base {

    public function index()
    {
        $this->data['key'] = '龙采头条';
        $this->twig->render('front/index/index.html', $this->data);
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
	
	

}
