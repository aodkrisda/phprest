<?php
class HomeController extends BaseController{
	protected function initialize(){
		$this->roles=array('','user','admin');
	}

	public function indexAction(){
		$this->render('home.twig');
	}

	public function testHellowAction(){
		echo "TEST Helow World". time() ;
		var_dump(func_get_args());
	}	
}