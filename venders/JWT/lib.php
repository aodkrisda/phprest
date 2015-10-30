<?php
require(__DIR__ . '/JWT.php');

class JWT_Token{

	private $life=(60 * 15); //15 sec
	private $key='H9#*3AzHelowThaiLAND';
	private $alg='HS512';
	private $_data=null;
	private $_token=null;
	
	
	static function Authenticate($route){
		if($route){
			$app=\Slim\Slim::getInstance();
			
			$user=$app->request->headers('PHP_AUTH_USER');
			if(!$user){
				$pwd=$app->request->headers('PHP_AUTH_PW');
				//echo $app->environment['PHP_AUTH_USER'];
				$b=base64_encode('111:'. time());
				
				$app->response->headers->set('Authorization', 'Bearer ' . $b);
				$app->response->headers->set('XBBB', 'Bearer ' . $b);
				echo "<form><input name='ss'><input type='submit'></form>";
		
				$app->stop();
			}
			

		}
	}
	
	
	function __construct($data=null){
		if($data){
			if(is_string($data)){
				$this->parse($data);
			}else{
				$this->create($data);
			}
		}
	}
	
	function create($data){
		if($data){
			$now=time();
			$exp=$now + $this->life;
			$tm=array('iss'=>$data, 'iat'=>$now, 'exp'=>$exp,'aud'=>$_SERVER['HTTP_HOST']);
			$this->parse($tm);
		}
	}
	
	function parse($data){
		$this->_data=null;
		$this->_token=null;
		try{
			if(is_string($data)){
				$tm=explode('.',$data);
				if(count($tm)!=3){
					$data=$this->find($data);
				}
				unset($tm);
				$this->_data=JWT::decode($data, $this->key,$this->alg);
				$this->_token=$data;
			}else{
				$this->_token=JWT::encode($data, $this->key,$this->alg);
				$this->_data=$data;			
			}
		}catch(Exception $e){}
	}
	
	function isValid(){
		return is_array($this->_data);
	}
	
	function isExpired(){
		if($this->isValid()){
			if(isset($this->_data['exp'])){
				return ($this->_data['exp'] < time());
			}
			return false;
		}
		return true;
	}
	
	function token(){
		return $this->_token;
	}
	
	function short_token(){
		if($this->_token){
			return md5($this->_token);
		}
		return $this->_token;
	}

	function data(){
		if($this->isValid()){
			if(isset($this->_data['iss'])){
				return $this->_data['iss'];
			}
		}
		return null;
	}
	
	function raw(){
		return $this->_data;
	}
	
	function find($key){
		return $key;
	}
}




