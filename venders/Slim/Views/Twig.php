<?php

namespace Slim\Views;

class Twig extends \Slim\View
{
	static private $_instance=null;
    static public function getInstance(){
    	if(!self::$_instance){
	        $dir=__DIR__.'/';
	
		    $loader = new \Twig_Loader_Filesystem($dir.'../../../app/views/');
		    $loader->addPath($dir.'../../../app/views/admin/','admin');
		    self::$_instance = new \Twig_Environment($loader, array(
		        'xxxcache' => $dir.'/cache/',
		    ));
		    require(__DIR__ . '/TwigExtension.php');
		    self::$_instance->addExtension(new TwigExtension());

	    }
	    return self::$_instance;
    }
    protected function render($template, $data = null)
    {
    	$data = array_merge($this->data->all(), (array) $data);
		return self::getInstance()->render($template, $data);
    }
}
