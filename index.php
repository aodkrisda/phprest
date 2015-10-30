<?php
/* fix input of CGI/FastCGI */

if(strpos($_SERVER["SERVER_SOFTWARE"],'nginx/')!==false){
	if(!isset($_SERVER['PATH_INFO'])){
		$a=explode('.php',$_SERVER["PHP_SELF"]);
		if((count($a)>1)){
			$_SERVER["SCRIPT_NAME"]=$a[0] .'.php';
			$_SERVER['PATH_INFO']=$a[1];
		}
		unset($a);
	}
}

/* start hack query */
$key='r';
$rewrite_r=false;
if(isset($_GET[$key])){
	if($_SERVER['PHP_SELF']==$_SERVER['SCRIPT_NAME']){
		$_SERVER['PATH_INFO']=$_GET[$key];
		$_SERVER['REQUEST_URI']=$_SERVER["SCRIPT_NAME"].$_SERVER["PATH_INFO"];
		//$_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
		unset($_GET[$key]);
		$rewrite_r=true;
	}
}
/* end hack query */


require_once 'venders/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
function __autoload($class){
    if (strpos($class, 'Controller')!==false) {
	    $class=strtolower(substr(preg_replace('/([A-Z])/','_$0', $class),1)) .'.php';
	    $fi=__DIR__ .'/app/controllers/'. $class;
	    if(file_exists($fi)){
	    	 require $fi;
	    }else{
	    	$fi=__DIR__ .'/app/controllers/admin/'. $class;
			if(file_exists($fi)){
			    require $fi;
			}
		}
	}
}
spl_autoload_register( '__autoload', true, false);

//require
require_once __DIR__.'/app/WebConfig.php';
require_once __DIR__.'/app/WebSession.php';
require_once __DIR__.'/app/WebUser.php';
require_once __DIR__.'/app/WebApp.php';

//create Web Appication
$app = new WebApp($CONFIGS);
$app->config('rewrite_r',$rewrite_r);
$app->config('view',new \Slim\Views\Twig());
$app->config('web_dir',__DIR__);

$app->notFound(function() use ($app){
	$app->render('not_found.twig');
});

$app->any('/auth/(:args+)', function ($args=null) use ($app) {
	$app->default_webapp_handler($app,$args);
});

$app->any('/admin/(:args+)', array($app,'Authenticate'), function ($args=null) use ($app) {
	$app->default_webapp_handler($app,$args,'admin');
});

$app->any('/(:args+)', function ($args=null) use ($app) {
	$app->default_webapp_handler($app,$args);
});

$app->run();
