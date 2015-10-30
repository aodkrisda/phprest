<?php
class BaseController{
	protected $app=null;
	protected $roles=array('user');
	protected $template='';
	
	public function __construct($app, $template=''){
		$this->app=$app;	
		$this->template=$template;
		$this->initialize();
		$this->checkPermission();
	}
	
	protected function initialize(){
		/* code for initialize extened controller */
	}
	
	protected function render($template, $data=array()){
		$this->app->render($template , $data);
	}
	
	private function checkPermission(){
		if($this->roles){
			$user=false;
			if($this->app->user){
				$obj_fun=array($this->app->user, 'getUser');
				if(is_callable ($obj_fun)){
					$user=call_user_func_array($obj_fun, array());
				}
			}
			if(!$user){
				$this->app->errorRequiredLogin();
				return;			
			}

			$role='';
			if(methode_exists($this->app->user,'getRole')){
				$role=$this->app->user->getRole();
			}
			if(!in_array($role,$this->roles)){
				$this->app->errorForbidden();
				return;	
			}
		}
	}
}