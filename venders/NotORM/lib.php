<?php
/** NotORM - simple reading data from the database
* @link http://www.notorm.com/
* @author Jakub Vrana, http://www.vrana.cz/ 
* @copyright 2010 Jakub Vrana
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

if (!interface_exists('JsonSerializable')) {
	interface JsonSerializable {
		function jsonSerialize();
	}
}


include_once ("db.config.php");
include_once ("Structure.php");
include_once ("Cache.php");
include_once ("Literal.php");
include_once ("Result.php");
include_once ("MultiResult.php");
include_once ("Row.php");

// friend visibility emulation
abstract class NotORM_Abstract {
	protected $connection, $driver, $structure, $cache;
	protected $notORM, $table, $primary, $rows, $referenced = array();
	
	protected $debug = false;
	protected $debugTimer;
	protected $freeze = false;
	protected $rowClass = 'NotORM_Row';
	protected $jsonAsArray = false;
	
	protected function access($key, $delete = false) {
	}
	
}


class NotORM_Structure_Convention2 extends NotORM_Structure_Convention{
 function getReferencedTable($name, $table) {
 		/*
        if ($name == "author_id") {
            return "user";
        }
        */
        return parent::getReferencedTable($name, $table);
    }
}



/** Database representation
* @property-write mixed $debug = false Enable debugging queries, true for error_log($query), callback($query, $parameters) otherwise
* @property-write bool $freeze = false Disable persistence
* @property-write string $rowClass = 'NotORM_Row' Class used for created objects
* @property-write bool $jsonAsArray = false Use array instead of object in Result JSON serialization
* @property-write string $transaction Assign 'BEGIN', 'COMMIT' or 'ROLLBACK' to start or stop transaction
*/
class NotORM extends NotORM_Abstract {
	static private $_instance=null;
	static public function &getInstance(){
		if(!NotORM::$_instance){
			if(defined('PDO_CONNECTION') && PDO_CONNECTION){
				$pdo = new PDO(PDO_CONNECTION);
			}else{
				$pdo = new PDO("mysql:host=". MYSQL_HOST . ";dbname=". MYSQL_DB_NAME . ";charset=utf8", MYSQL_DB_USER, MYSQL_DB_PASW);
			}
			
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			NotORM::$_instance= new NotORM($pdo);
		}
		return NotORM::$_instance;
	}
	
	/** Create database representation
	* @param PDO
	* @param NotORM_Structure or null for new NotORM_Structure_Convention
	* @param NotORM_Cache or null for no cache
	*/
  
  //private $table_prefix='info_';
  private $table_prefix=''; 
  
  
	function __construct(PDO $connection, NotORM_Structure $structure = null, NotORM_Cache $cache = null) {
		$this->connection = $connection;
		$this->driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
		if (!isset($structure)) {
			//primary, foreign, table, tabl_prefix
			$structure = new NotORM_Structure_Convention2('%s_id', '%s_id', '%s', $this->table_prefix);
     		 //$structure = new NotORM_Structure_Convention2('id', '%s_id', '%s', $this->table_prefix);
		}
		$this->structure = $structure;
		$this->cache = $cache;
	}
	function date_info($datestr){
		  $old=strtotime($datestr);
		  $datestr=date('Y-m-1',$old);
		  $tm=strtotime($datestr);
		  $tm2=strtotime('+1 months',$tm);
		  $year=intval(date('Y',$tm));
	    $month=intval(date('m',$tm));
	    $date=intval(date('d',$old));
		  return array('year'=>$year, 'month'=>$month, 'day'=>$date, 'begin'=>date('Y-m-d',$tm) ,'date'=>date('Y-m-d',$old), 'end'=>date('Y-m-d',$tm2));
	 } 
	/** Get table data to use as $db->table[1]
	* @param string
	* @return NotORM_Result
	*/
	function __get($table) {
		return new NotORM_Result($this->structure->getReferencingTable($table, ''), $this, true);
	}
	
	/** Set write-only properties
	* @return null
	*/
	var $__transBegin=false;
	function __set($name, $value) {
		if ($name == "debug" || $name == "debugTimer" || $name == "freeze" || $name == "rowClass" || $name == "jsonAsArray") {
			$this->$name = $value;
		}
		if ($name == "transaction") {
			switch (strtoupper($value)) {
				case "BEGIN": 
					if($this->__transBegin!==true){
						$this->__transBegin=true;
						return $this->connection->beginTransaction();
					}
				case "COMMIT": 
					if($this->__transBegin===true){
						$this->__transBegin=false;
						return $this->connection->commit();
					}
				case "ROLLBACK": 
					if($this->__transBegin===true){
						$this->__transBegin=false;
						return $this->connection->rollback();
					}
			}
		}
    
	}
	
	/** Get table data
	* @param string
	* @param array (["condition"[, array("value")]]) passed to NotORM_Result::where()
	* @return NotORM_Result
	*/
	function __call($table, array $where) {
		$return = new NotORM_Result($this->structure->getReferencingTable($table, ''), $this);
		if ($where) {
			call_user_func_array(array($return, 'where'), $where);
		}
		return $return;
	}
  
  public function &pdo(){
    return $this->connection;
  }  
  
  public function &execute($sql,$param=null,$native=false){
    $ret=false;
    $stm = $this->pdo()->prepare($sql);
    $stm->setFetchMode(PDO::FETCH_ASSOC);
    $b=false;
    if(is_array($param)){
      $b=$stm->execute($param);
    }else{
      $b=$stm->execute();
    }
    if($b){
      if($native===true){
        $ret=&$stm;
      }else{
        $ret=$stm->fetchAll();
      }
    }
    return $ret;
  } 
  
  private $_tablesExists=false;
  public function table_exists($table,$add_prefix=true){
    if(!$this->_tablesExists){
      $this->get_tables();
    }
    if(is_array($this->_tablesExists)){
      $table=$this->get_table_name($table,$add_prefix);
      return in_array($table,$this->_tablesExists);
    }
    return false;
  }
  public function get_table_name($table, $add_prefix=true){
    if($add_prefix) $table=$this->table_prefix . $table;
    return $table;
  }
  
  public function get_tables(){
    if(!$this->_tablesExists){
      $name=$this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
      $q=null;
      if($name=="mysql"){
      	$q = $this->connection->query("SHOW TABLES");
      }else if($name=="sqlite"){
      	$q = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name!='sqlite_sequence'");
      }
      if($q){
      	$q->execute();
     	$this->_tablesExists = $q->fetchAll(PDO::FETCH_COLUMN);
      }
    }
    if(is_array($this->_tablesExists)){
      return $this->_tablesExists;
    }
    return false;  
  }
  
  public function load_definition(){
  	$f=__DIR__ .'/_definition_';
  	$def=null;
  	if(file_exists($f)){
  		try{
  		$def=unserialize(file_get_contents($f));
  		}catch(Exception $e){}
  		if(is_array($def) && $def){
  			$this->_tablesExists=array();
  			$this->_tableFields=array();
  			foreach($def as $tb=>$fs){
  				$this->_tablesExists[]=$tb;
  				$this->_tableFields[$tb]=$fs;
  			}
  		}else{
  			$def=null;
  		}
  	}
  	if(!is_array($def)){
 
  		$tbs=$this->get_tables();
  		$def=array();
  		foreach($tbs as $tb){
  			$def[$tb]=$this->get_fields($tb,false);
  		}
  		if(is_writable(__DIR__)){
  			@file_put_contents($f,serialize($def));
  		}
  	}
  	return $def;
  }
  
  public function get_structure(){
    return $this->structure;
  }
  
  public function get_primary_key($table){
     return $this->structure->getPrimary($table);
  }
  
  public function get_table_prefix(){
    return $this->table_prefix;
  }
  
  public function escape_like($str,$begin=true, $end=true){
    $str=preg_replace('/%/','\%',$str);
    if($begin) $str='%' . $str;
    if($end) $str=$str . '%';
  	return $str;
  }
  
  public function escape_name($str, $startchr='`', $endchr=null){
  	if($startchr && (strpos($str,$startchr)!==0)){
  		if($endchr===null){
  			if($startchr=='['){
  				$endchr=']';
  			}else if($startchr=='{'){
  				$endchr='}';
  			}else if($startchr=='('){
  				$endchr=')';  				
  			}else{
  				$endchr=$startchr;
  			}
  		}
  		$str="{$startchr}{$str}{$endchr}";
  	}
  	return $str;
  }
  
  public function join_table(){
  	// join_table('table1','left join table2 on table1.field1=table2.field2', ...);
  	$joins=func_get_args();
  	$table=array_shift($joins);
	$table=preg_replace('/`/','',$table);
  	$tbs=array();
  	foreach($joins as $exp){
  		$exp=preg_replace('/`/','',$table .' '. $exp);
  		preg_match_all('/\S{1,}\s+((left|right|inner|outer)\s+join)\s+(\S{1,})\s+(on)\s+(\S{1,})\s{0,}=\s{0,}(\S{1,})/i', $exp, $ms);
  		if($ms && isset($ms[6]) && $ms[6]){
  			$ptn3=$this->structure->getReferencingTable(implode($ms[3],''),'');
  			$tn3=implode($ms[3],'');
  			if($ptn3!=$tn3){
  				$ptn3="`{$ptn3}` AS `{$tn3}`";
  			}else{
  				$ptn3="`{$ptn3}`";
  			}
  			$tbs[]=sprintf('%s %s %s %s = %s',strtoupper(implode($ms[1],'')),$ptn3, strtoupper(implode($ms[4],'')), implode($ms[5],''), implode($ms[6],''));
  		}
  	}
  	if($tbs){
  		$tn3=$this->structure->getReferencingTable($table,'');
  		if($table!=$tn3){
  			array_unshift($tbs, "`{$tn3}`  AS `{$table}`");
  		}else{
  			array_unshift($tbs,"`{$table}`");
  		}
  		$table=implode($tbs,' ');
  	}
  	return new NotORM_Result($this->structure->getReferencingTable($table, ''), $this, true);
  }

  private $_tableFields=array();
  public function get_fields($table,$add_prefix=true){
    if($this->table_exists($table,$add_prefix)){
      $table=$this->get_table_name($table,$add_prefix);
      $name=$this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
      if($name=="mysql"){
      	$q = $this->connection->prepare(sprintf("DESCRIBE `%s`",$table) );
     	$q->execute();
      	$this->_tableFields[$table] = $q->fetchAll(PDO::FETCH_COLUMN); 
      }else if($name="sqlite"){
		$q = $this->connection->query("PRAGMA table_info(`{$table}`)");
		$columns=false;
		while($r=$q->fetch()){
			$columns[]=$r['name'];
		}
		$this->_tableFields[$table]=$columns;   
	  }
      if(is_array($this->_tableFields[$table])){
        return $this->_tableFields[$table];
      }
    }
    return false;
  }
  
  public function &toArray($rs){
     if(is_a($rs, 'NotORM_Row')){
        $r=array();
        foreach($rs as $k=>$v) $r[$k]=$v;
        return $r;          
     }else if(is_a($rs, 'NotORM_Result')){
        $re=array();
        foreach($rs  as $row){
          $re[]=$this->toArray($row);
        }
        return $re;
     }
     return $rs;
  }
  
  public function &groupBy($rs, $fd){
    $result=false;
     if(is_a($rs, 'NotORM_Result')){
        foreach($rs  as $row){
          $tm=$this->toArray($row);
          if(isset($tm[$fd])){
            $result[$tm[$fd]][]=$tm;
          }
        }
     }
     return $result;
  }
  
  public function &countBy($rs, $fd){
    $result=false;
     if(is_a($rs, 'NotORM_Result')){
        foreach($rs  as $row){
          $tm=$this->toArray($row);
          if(isset($tm[$fd])){
            if(!isset($result[$tm[$fd]])) $result[$tm[$fd]]=0;
            $result[$tm[$fd]]=$result[$tm[$fd]] + 1;
          }
        }
     }
     return $result;
  }
  
  public function get_extend($fields, $table, $add_prefix=true){
    $ret=array();
    if(is_array($fields)){
      $fds=$this->get_fields($table,$add_prefix);
      if($fds){
        foreach($fds as $fd){
          if(isset($fields[$fd])){
            $ret[$fd]=$fields[$fd];
          }
        }
      }
    }
    return $ret;
  }
  
  private $_time=null;
  public function now($tm=null){
    if($tm && is_string($tm)){
      $tm=strtotime($tm);
    }

    if(!$tm){
      if(!$this->_time) $this->_time=time();
      $tm=$this->_time;
    }

    return date("Y-m-d H:i:s",$tm);
  }
  
  public function today($tm=null){
    if($tm && is_string($tm)){
      $tm=strtotime($tm);
    }
    if(!$tm){
      if(!$this->_time) $this->_time=time();
      $tm=$this->_time;
    }
    return date("Y-m-d",$tm);  
  }
}



?>