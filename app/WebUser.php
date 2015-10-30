<?php
class WebUser{
	const FIELD_ROLES=array('1'=>'admin', '2'=>'user');
	const FIELD_ROLE='role_id';
	const FIELD_ID='user_id';

	protected $user=null;

	public function setUser($user){
		if($user && isset($user[self::FIELD_ID])){
			$this->user=$user;
		}else{
			$this->user=null;
		}
	}

	public function getUser(){
		return $this->user;
	}

	public function getUserId(){
		if($this->user){
			return $this->user[self::FIELD_ID];
		}
		return null;
	}

	public function getRoleId(){
		$str=null;
		if($this->user){
			if(isset($this->user[self::FIELD_ROLE])){
				$str=$this->user[self::FIELD_ROLE];
			}
		}
		return $str;
	}	

	public function getRole(){
		$str=null;
		if($this->user){
			if(isset($this->user[self::FIELD_ROLE])){
				if((self::FIELD_ROLES[$this->user[self::FIELD_ROLE]])!==null){
					$str=self::FIELD_ROLES[$this->user[self::FIELD_ROLE]];
				}
			}else{
				$str='';
			}
		}
		return $str;
	}
}
