<?php
class WebApp extends \Slim\Slim{
	const DEFAULT_CONTROLLER='home';
	const DEFAULT_ACTION='index';
	
	public function camelName($str,$action=false){
		if(strpos($str, '_')!==false){
			$ars=split('_',strtolower($str));
			foreach($ars as $a=>$b){
				$ars[$a]=ucwords($b);
			}
			if($action===true){
				$ars[0]=strtolower($ars[0]);
			}
			return join('',$ars);
		}
		return $str;
	}

	public function normalName($str){
		$str=strtolower(preg_replace('/[A-Z]/','_$0',$str));
		$str=preg_replace('/^_{1,}/','',$str);
		return $str;
	}

	public function getAuthorization(){
		$ukey='HTTP_AUTHORIZATION';
		$lkey='Authorization';
		$str='';
		$tmp=$this->request->headers($lkey);
		if($tmp){
			$str=$tmp;
		}else if(isset($_SERVER[$ukey]) && $_SERVER[$ukey]){
			$str=$_SERVER[$ukey];
		}else{
			$tmp=apache_request_headers();
			if($tmp && isset($tmp[$lkey]) && $tmp[$lkey]){
				$str=$tmp[$lkey];
			}
		}
		return $str;
	}

	public function default_webapp_handler($app, $args, $role=''){
		$success=false;
		$result=false;
		$ctrl_dir=__DIR__. '/controllers/';
		if($role) $ctrl_dir .=$role .'/';

		if(empty($args)) $args[]=self::DEFAULT_CONTROLLER;
		if(count($args)==1) $args[]=self::DEFAULT_ACTION;
		
		$cname=strtolower(array_shift($args));
		$fname=$ctrl_dir . $cname . '_controller.php';

		if($fname && file_exists($fname)){
			@require_once ($fname);
			$cname=$app->camelName($cname .'_controller');
			if(class_exists($cname)){
				$func='';
				if($args){
					$func=array_shift($args);
				}
				if(empty($func)) $func=self::DEFAULT_ACTION;
				$func=$app->camelName($func.'_action',true);

									
				$tpl=$this->normalName(str_replace('Controller','',$cname) . '_' . str_replace('Action','',$func));
				$app->role=$role;
			
				$app->controler=new $cname($app,$tpl);
						
				$app->action=$func;
				$app->arguments=$args;
				$obj_fun=array($app->controler, $func);

				if(is_callable($obj_fun)){
					$result=call_user_func_array($obj_fun, $args);
					$success=true;
				}	
			}
		}
		if($success===false){
			$app->notFound();
		}	
	}	
	
	public function Authenticate($route){
		$app=$this; //\Slim\Slim::getInstance();
		if($app->user->getUser()){
			return;
		}
		$access_token=$app->getAuthorization();
		$user=$app->session->get('_user_');
		if ($access_token && (!$user)) {
		    $rs=$app->orm->user->limit(1);
		    if(count($rs)){  
		      $user=$app->orm->toArray($rs)[0];
		      if($user){
		        unset($user['password']);
		        $app->user->setUser($user);
		        $app->session->set('_user_',$app->user->getUser());
		        return;
		      }
		    }
		}else{
			if($user){
				$app->user->setUser($user);
				return;
			}
		}

		$app->response->setStatus(401);
		$app->response->setBody('Unauthorized');
	}

	public function errorRequiredLogin(){
		$this->response->setStatus(401);
		$this->render('login.twig');
		$this->stop();
	}
	
	public function errorForbidden(){
		$this->response->setStatus(403);
		$this->render('forbidden.twig');
		$this->stop();			
	}
	
	public function render($template, $data = array(), $status = null){
		if($this->role){
			if(strpos($template,'@')===false){
				$template='@'. $this->role . '/'. $template;
			}
		}
		parent::render($template, $data,$status);
	}
	
	public function __construct(array $userSettings = array()){
		parent::__construct($userSettings);
		$this->container->singleton('user', function ($c) {
			return new WebUser();
		});
		$this->container->singleton('orm', function ($c) {
			require_once __DIR__ . '/../venders/NotORM/lib.php';
			return NotORM::getInstance();
		});
		$this->container->singleton('session', function ($c) {
			return new WebSession();
		});		
	}
}