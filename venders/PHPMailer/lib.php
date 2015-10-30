<?php
require_once(__DIR__.'/class.phpmailer.php');
require_once(__DIR__.'/class.smtp.php');
require_once(__DIR__.'/config.php');

class Mail extends PHPMailer{
	function __construct($replyto=null, $name='Mailer') {
		parent::__construct();
		//$this->SMTPDebug  = 2;
		$email=null;
		$pass=null;
		$type=false;
		if(defined(SMTP_EMAIL_NAME) && defined(SMTP_EMAIL_PASS) && SMTP_EMAIL_NAME){
			$type='SMTP';
			$email=SMTP_EMAIL_NAME;
			$pass=SMTP_EMAIL_PASS;			
		}
		
		$this->Username = $email;
		$this->Password = $pass;
		
		if($email && $pass && $type=='STMP'){
			$this->SMTPAuth = true;
			if(preg_match('/@hotmail.com/i', $email)){
				$this->SMTPSecure = 'tls';	
				$this->Host = 'smtp.live.com';
				$this->Port = 25;			
			}else if(preg_match('/@yahoo.com/i', $email)){
				$this->Host = 'smtp.mail.yahoo.com';
				$this->Port = 25;					
			}else{
				$this->SMTPSecure = 'tls';
				$this->Host = 'smtp.gmail.com';	
				$this->Port = 587;
			}
			$this->isSMTP();
		}else{
			echo "Please config mailer before using....";
		}
		
		//$this->addReplyTo(SMTP_EMAIL_NAME);
		//$this->addCC('cc@example.com');
		//$this->addBCC('bcc@example.com');	
		//$this->addAttachment('/tmp/image.jpg');	
		//$this->addAttachment('/tmp/image.jpg', 'new.jpg');	
		
		$this->From = $email;
		$this->FromName = (is_string($name) && $name)?$name:'Mailer';
		if(is_string($replyto) && $replyto){
			$b=false;
			if(function_exists('filter_var')){
				$b=filter_var($replyto, FILTER_VALIDATE_EMAIL);
			}
			if($b) $this->addReplyTo($replyto);
		}
	}
	
	function send($to=null, $subject=null, $body=null, $ishtml=false){

		$this->isHTML(($ishtml===true));
		if($to) $this->AddAddress($to);
		if(is_string($subject)) $this->Subject=$subject;
		if(is_string($body)) $this->Body=$body;
		//$this->AltBody='text plain';
		return parent::send();
	}
}

//Test Config
$path=str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["REQUEST_URI"]);
if (($path=='/test') && (basename($_SERVER['SCRIPT_FILENAME'])=='lib.php') && realpath($_SERVER['SCRIPT_FILENAME'])===realpath(__FILE__)){
	if(file_exists(__DIR__ .'/test.php')){
		include_once(__DIR__ .'/test.php');
	}
}

