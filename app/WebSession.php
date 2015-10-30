<?php
class WebSession extends \Slim\Helper\Set {
    public function __construct($items = array())
    {
	@session_start();
	$this->data=&$_SESSION;
        parent::__construct($items);
    }
}

