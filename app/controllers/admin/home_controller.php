<?php
class HomeController extends BaseController{
	protected function initialize(){
		//$this->loginRequired=true;
	}
	public function indexAction(){
	echo __CLASS__ . __FUNCTION__ . "   ";
		$d=$this->app->orm->user()->toArray();
		$x='xxx';
		$this->render('home.twig',array('title'=>'Admin Home','users'=>$d,'xurl'=>$x));
	}

}