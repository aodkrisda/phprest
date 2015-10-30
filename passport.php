<?php

//create application
class Passport{
	const FIELD_ROLE = 'role_id';
	const FIELD_ID= 'user_id';

	protected $user=null;
	public function __construct($app){
		$this->app=$app;	
	}
	public function serializeUser($data){
		$this->user=$data;
		return 'xxx';
	}

	public function deserializeUser($hash){
		$this->user=null;
	}

	public function getUser(){
		return $this->user;
	}

	public function getUserId(){
		if($this->user && isset($this->user[self::FIELD_ID])){
			return $this->user[self::FIELD_ID];
		}
		return null;
	}
	public function getRoleId(){
		$str=null;
		if($this->user && isset($this->user[self::FIELD_ROLE])){
			$str=$this->user[self::FIELD_ROLE];
		}
		return $str;
	}	
	public function getRole(){
		$str=null;
		if($this->user && isset($this->user[self::FIELD_ROLE])){
			switch($this->user[self::FIELD_ROLE]){
				case '1':
					$str='admin';
					break;
				defalt:
					$str='';
			}
		}
		return $str;
	}
}