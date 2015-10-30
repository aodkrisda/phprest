<?php
class Form	 {
	protected $_model=array();
	protected $_action='';
	protected $_method='POST';
	protected $_name='';
	protected $_children=array();
	protected $_keys=array();

	public $controlwidth='col-md-8';
	public $labelwidth='col-md-4';
	public $labelOffsetwidth='col-md-offset-4';

		
	function constructor(){
		
	}
	
	public function  model(){
		$param=func_get_args();
		if($param){
			$this->_model=$param[0];
			return $this;
		}
		return $this->_model;
	}
	
	public function  action(){
		$param=func_get_args();
		if($param){
			$this->_action=$param[0];
			return $this;
		}
		return $this->_action;
	}
	public function  method(){
		$param=func_get_args();
		if($param){
			$this->_method=$param[0];
			return $this;
		}
		return $this->_method;
	}	
	public function  name(){
		$param=func_get_args();
		if($param){
			$this->_name=$param[0];
			return $this;
		}
		return $this->_name;
	}	
	public function html(){
		$str=array();
		$str[]=sprintf('<form class="form form-horizontal" name="%s" action="%s" method="%s">', $this->name(), $this->action(), $this->method());
		foreach($this->_keys as $key=>$idx){
			$str[]=$this->element($key);
		}
		$str[]='</form>';
		return implode("\r\n", $str);
	}
	public function removeAll(){
		$this->_keys=[];
		$this->_children=[];
	}
	public function create($type='text', $name='', $options=null){
		if($type && $name){
			$type=strtolower($type);
			if(isset($this->_keys[$name])){
				$this->_children[$this->_keys[$name]]=array($type, $name, $options);
			}else{
				$this->_keys[$name]=count($this->_children);
				$this->_children[]=array($type, $name, $options);
			}
		}
	}
	public function element($name){
		$html='';
		if(isset($this->_keys[$name])){
			$ars=$this->_children[$this->_keys[$name]];
			switch($ars[0]){
				case 'checkbox':
					$html=$this->create_checkbox($ars[1],$ars[2]);
					break;
				case 'checkbox-inline':
					$html=$this->create_checkbox_inline($ars[1],$ars[2]);
					break;
				case 'radio':
					$html=$this->create_radio($ars[1],$ars[2]);
					break;
				case 'radio-inline':
					$html=$this->create_radio_inline($ars[1],$ars[2]);
					break;						
				case 'textarea':
					$html=$this->create_textarea($ars[1],$ars[2]);
					break;						
				default:
					$html=$this->create_input($ars[0],$ars[1],$ars[2]);
					break;									
			}
			
		}
		return $html;
	}
	
	protected function value($name, $def=''){
		return 'xxx value xxx';
		if($name && isset($this->_model[$name])){
			return $this->_model[$name];
		}
		return $def;
	}
	
	/**
	options : {label, true_fvalue, false_value}
	*/
	protected function create_checkbox($name='', $options=null){
		$str=array();
		if(!is_array($options)) $options=array();
		$key='name';
		if(isset($options[$key])) $name=$options[$key];			
		$key='true_value';
		$true_value=(isset($options[$key])) ? $options[$key] : 'true';
		$key='false_value';
		$value=$this->value($name);
		$label='';
		$key='label';
		if(isset($options[$key])) $label=$options[$key];		
		$false_value=(isset($options[$key])) ? $options[$key] : 'false';
		$str[]='<div class="form-group">';
		$str[]=sprintf('<div class="%s %s">', $this->controlwidth, $this->labelOffsetwidth);		
		$str[]=sprintf('<div class="checkbox"><label><input type="checkbox" name="%s"  data-false-value="%s" value="%s" %s/> %s</label></div>',$name, $false_value, $true_value, ($value==$true_value)?'checked':'', $label);
		$str[]='</div>';
		$str[]='</div>';
		return implode('', $str);
	}
	/**
	items : [{label, true_fvalue, false_value}, ...]
	*/
	protected function create_checkbox_inline($name, $items){
		$str=array();
		if(is_array($items)){
			$str[]='<div class="form-group">';
			$str[]=sprintf('<div class="%s %s">', $this->controlwidth, $this->labelOffsetwidth);				
			foreach($items as $it){
				$options=$it;
				$key='name';
				if(isset($options[$key])) $name=$options[$key];			
				$key='true_value';
				$true_value=(isset($options[$key])) ? $options[$key] : 'true';
				$key='false_value';
				$false_value=(isset($options[$key])) ? $options[$key] : 'false';
				$value=$this->value($name);
				$label='www';
				$key='label';
				if(isset($options[$key])) $label=$options[$key];		

				$str[]=sprintf('<label class="checkbox-inline"><input type="checkbox" name="%s"  data-false-value="%s" value="%s" %s/> %s</label>',$name, $false_value, $true_value, ($value==$true_value)?'checked':'', $label);
				
			}
			$str[]='</div>';
			$str[]='</div>';
		}
		return implode('', $str);
	}
	/**
	options : {label, true_fvalue, false_value}
	*/
	protected function create_radio($name='', $options=null){
		$str=array();
		if(!is_array($options)) $options=array();
		$key='true_value';
		$true_value='';
		$key='name';
		if(isset($options[$key])) $name=$options[$key];		
		$key='value';
		if(isset($options[$key])) $true_value=$options[$key];
		$value=$this->value($name);
		$label='';
		$key='label';
		if(isset($options[$key])) $label=$options[$key];		
		$str[]='<div class="form-group">';	
		$str[]=sprintf('<div class="%s %s">', $this->controlwidth, $this->labelOffsetwidth);	
		$str[]=sprintf('<div class="radio"><label><input type="radio" name="%s"  value="%s" %s/> %s</label></div>',$name,  $true_value, ($value==$true_value)?'selected':'', $label);
		$str[]='</div>';
		$str[]='</div>';
		return implode('', $str);
	}
	/**
	items : [{label, true_fvalue, false_value}, ...]
	*/
	protected function create_radio_inline($name, $items){
		$str=array();
		if(is_array($items)){
			$str[]='<div class="form-group">';
			$str[]=sprintf('<div class="%s %s">', $this->controlwidth, $this->labelOffsetwidth);
			foreach($items as $it){
				$options=$it;
				$key='name';
				if(isset($options[$key])) $name=$options[$key];			
				$key='value';
				$true_value=(isset($options[$key])) ? $options[$key] : '';
				$value=$this->value($name);
				$label='';
				$key='label';
				if(isset($options[$key])) $label=$options[$key];		
				
				$str[]=sprintf('<label class="radio-inline"><input type="radio" name="%s"  value="%s" %s/> %s</label>',$name,  $true_value, ($value==$true_value)?'selected':'', $label);

			}
			$str[]='</div>';
			$str[]='</div>';
		}
		return implode('', $str);
	}
	protected function create_textarea( $name='', $options=null){
		if(!is_array($options)) $options=array();
		$error='';
		$warning='';		
		$helpblock='';
		$label='';
		$key='label';
		if(isset($options[$key])) $label=$options[$key];
		$placeholder='';
		$key='placeholder';
		if(isset($options[$key])) $placeholder=$options[$key];			
		$rows=5;
		if(isset($options[$key])) $rows=intval($options[$key]);		
		$value=$this->value($name);		
		$str=array();
		$str[]='<div class="form-group">';
		$str[]=sprintf('<label class="control-label %s">%s</label>', $this->labelwidth, $label);
		$str[]=sprintf('<div class="%s">', $this->controlwidth);
		$str[]=sprintf('<textarea name="%s" placeholder="%s" class="form-control" rows="%d">%s</textarea>',$name, $placeholder, $rows, $value);

		if($error || $warning){
			$str[]='<p class="help-block %s">%s</p>';
		}else if($helpblock){
			$str[]='<p class="help-block">%s</p>';
		}
		$str[]='</div>';
		$str[]='</div>';
		return implode('',$str);
		
	}
	protected function create_input($type='text', $name='', $options=null){
		if(!is_array($options)) $options=array();
		$type=strtolower($type);
		$error='';
		$warning='wwwww';		
		$helpblock='';
		$label='';
		$key='label';
		if(isset($options[$key])) $label=$options[$key];
		$placeholder='';
		$key='placeholder';
		if(isset($options[$key])) $placeholder=$options[$key];		
		$value=$this->value($name);
		$str=array();
		$cls='';
		if($error || $warning){
			$cls=($error)?'has-error':'has-warning';
		}
		$str[]=sprintf('<div class="form-group has-feedback %s">', $cls);
		$str[]=sprintf('<label class="control-label %s">%s</label>',  $this->labelwidth, $label);
		$str[]=sprintf('<div class="%s">', $this->controlwidth);
		if($type=='static'){
			$str[]=sprintf('<p class="form-control-static">%s</p>', $value);
		}else{
			if($warning) $str[]='<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>';
			if($error) $str[]='<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>';		
			$str[]=sprintf('<input type="%s" name="%s"  placeholder="%s" class="form-control" value="%s"/>', $type, $name,$placeholder, $value);

		}

		if($error){
			$str[]=sprintf('<p class="help-block">%s</p>', $error);
		}else if($warning){
			$str[]=sprintf('<p class="help-block">%s</p>', $warning);
		}else if($helpblock){
			$str[]=sprintf('<p class="help-block">%s</p>', $helpblock);
		}
		$str[]='</div>';
		$str[]='</div>';
		return implode('',$str);
	}
}

echo <<< EOT
<!DOCTYPE html>
<html>
	<head>
					<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">        
			<link rel="stylesheet" href="style.css" />
				<title> - My Webpage</title>
		<link rel="stylesheet" type="text/css" href="app/assets/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="app/assets/bootstrap/css/bootstrap-theme.css"> 
		<script src="app/assets/bootstrap/js/jquery.min.js"></script>
		<script src="app/assets/bootstrap/js/bootstrap.min.js"></script>  
	</head>
	<body>
	<div class="container">
EOT;

$f=new Form();
$f->name('test_form');
$f->action('test/index.php');
$f->method('PUT');

$f->create('static','id', array('label'=>'PK'));
$f->create('text','first_name' ,array('label'=>'First Name'));
$f->create('text','last_name' ,array('label'=>'Last Name'));
$f->create('password','lpass' ,array('label'=>'Your Password'));
$f->create('textarea','comment' ,array('label'=>'Enter Your Comment'));
$f->create('file','file1', array('label'=>'Select a File to upload'));
$f->create('checkbox','check1' ,array('label'=>'Rememer'));
$f->create('checkbox','check2' ,array('label'=>'Clear'));
$f->create('radio','r1' ,array('label'=>'IE9','name'=>'g1'));
$f->create('radio','r2' ,array('label'=>'IE10 ...','name'=>'g1'));

$f->create('checkbox-inline','line1' ,array(array('label'=>'Rememer1'), array('label'=>'Rememer2'), array('label'=>'Rememer3')));
$f->create('radio-inline','lr1' ,array(array('label'=>'Rememer1','value'=>'1'), array('label'=>'Rememer2','value'=>'2'), array('label'=>'Rememer3','value'=>'3')));
echo $f->html();


echo <<< EOT
</div>
</body>
</html>
EOT;
