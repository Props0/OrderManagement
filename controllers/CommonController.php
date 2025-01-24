<?php

class CommonController {
private $servername = "localhost";
	private $username = "root";
	private $dbname="mocsa";
	private $pass ="";
	public $table="";
	private $conn;
	private $previousaction=''; 
    private $previouscontroller='';
	private $formatter='';

	function __construct() {
		
	}
	public function formatdata($datestring){
		$data = new DateTime($datestring);
		date_default_timezone_set('Europe/Lisbon');
		$this->formatter= new IntlDateFormatter(
			'pt_PT'
			,IntlDateFormatter::FULL
			,IntlDateFormatter::NONE
			,'Europe/Lisbon'       
			,IntlDateFormatter::GREGORIAN
		);
		return $this->formatter->format($data);
	}
	public function getConnection(){	
		$this->conn = new mysqli(	$this->servername, $this->username,$this->pass, $this->dbname);			
		if ($this->conn->connect_error) {
			die("Connection failed: " . $this->conn->connect_error);
		}
	}
	function setPrevious(){
		$this->previousaction=$_POST['previousaction']; 
		$this->previouscontroller=$_POST['previouscontroller'];
	}
	
	function generateGuid(){
		return bin2hex(openssl_random_pseudo_bytes(16));
	}
	function generateRandomString($length = 10, $characters='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
	public function deconstructObject($object){
		$array=array();
		foreach ($object as $key => $value)
		{
			if($value!=null){
				$type="";
				if(is_string($value)) {				
					$type="s";
				}
				if(is_int($value)) {				
					$type="i";
				}
				if(is_double($value)) {				
					$type="d";
				}
				$array[$key]["type"]=$type;
				$array[$key]["val"]=$value;
			}
		}
		
		return  $array;
	}
	public function buildObject($array, $object){
		$class  = get_class($object);
		$obj = new $class;	
		foreach ($array as $key => $value) 
		{		
			if(method_exists($obj, 'set'.$key)){
				call_user_func_array(array($obj, 'set'.$key), array($value));
			}
		}

		return $obj;
	}
	public function createObject($array, $object){

		$arrayofobjects=array();
		foreach ($array as $key => $objectinarray) 
		{			
			$class  = get_class($object);
			$obj = new $class;			
			foreach ($objectinarray as $key => $value)
			{
				if(method_exists($obj, 'set'.$key)){
					call_user_func_array(array($obj, 'set'.$key), array($value));
				}
			}
			array_push($arrayofobjects, $obj);
		}
		return  $arrayofobjects;
	}
	private function get_result( $Statement ) {
	    $RESULT = array();
	    $Statement->store_result();
	    for ( $i = 0; $i < $Statement->num_rows; $i++ ) {
	        $Metadata = $Statement->result_metadata();
	        $PARAMS = array();
	        while ( $Field = $Metadata->fetch_field() ) {
	            $PARAMS[] = &$RESULT[ $i ][ $Field->name ];
	        }
	        if(method_exists($Statement, 'bind_result')){
		        call_user_func_array( array( $Statement, 'bind_result' ), $PARAMS );
		    }
	        $Statement->fetch();
	    }
	    return $RESULT;
	}
	public function getquery($values=null, $alternative_table=null,  $extra=null, $extraPRE=null, $debug=null){
		$conditions=null;
		$getitems=null; 
		$unions=null;
		$orderby=null; 
		$object=null;
		$sqlinjectionprotection_variable=false;
		$sqltable=$alternative_table!=null ? $alternative_table:$this->table;
		if(isset($values["conditions"]) && $values["conditions"]!=null){
			$conditions=$values["conditions"];
		}
		if(isset($values["getitems"]) && $values["getitems"]!=null){
			$getitems=$values["getitems"];
		}
		if(isset($values["unions"]) && $values["unions"]!=null){
			$unions=$values["unions"];
		}
		if(isset($values["orderby"]) && $values["orderby"]!=null){
			$orderby=$values["orderby"];
		}
		if(isset($values["object"]) && $values["object"]!=null){
			$object=$values["object"];
		}		
		$param_type="";
		$conditions_text="";
		$union_text="";
		$sql="";
		if($getitems!=null){
			$sql='SELECT '.$getitems.' FROM '.$sqltable;
		}else{
			$sql='SELECT * FROM '.$sqltable;
		}		
		if(isset($unions)>0){			
			foreach ($unions as $key => $union) {
				if (strpos($key, 'ptable_acronym')  !== false){
					$union_text.=" as ". $union;
				}
				if (strpos($key, 'table_extra')  !== false){
					foreach ($union as $key_union => $table) {
						switch ($key_union) {
							case 'table_name':
								$union_text.=" , ". $table;
							break;
							case 'table_acronym':
								$union_text.=" as ". $table;
							break;
						}
					}
				}								
			}		
		}
		$sql=$sql . $union_text;
		if(isset($conditions)>0){
			$conditions_text.=" where ";			
			$conditionsvalues= array();
			foreach ($conditions as $key => $condition) {
				
				if(strpos($key, 'group') !== false){
					$conditions_text=" ".$conditions_text.$condition." ";
				}else if(strpos($key, 'logicgate') !== false){
					$conditions_text=" ".$conditions_text.$condition." ";
				}else{
					$conditions_text=$conditions_text.$key." ";
				}
				if(is_array($condition)){ 
					foreach($condition as $key => $val) {
						switch ($key) {
							case 'type':
							$param_type=$param_type.$val;
							break;
							case 'signal':
							$sqlinjectionprotection_variable=true;
							$conditions_text.=$val." ? ";
							break;
							case 'signalvariable':
							$conditions_text.=$val." ";
							break;
							case 'variable':
							$conditions_text.=$val." ";
							break;
							case 'val':
							array_push($conditionsvalues, $val);
							break;	
							default:
							break;				
						}							
					}
				}
			}
			$conditions_text.=" and 1=1 ";
			$a_params[] = & $param_type;
			foreach ($conditionsvalues as $key => $conditionvalue) {
				$a_params[]=& $conditionsvalues[$key];
			}
			$sql=$sql . $conditions_text;
		}

		if($orderby!=null){
			$sql=$sql." order by ".$orderby;
		}
		if($extra!=null){
			$sql.=$extra;
		}
		if($extraPRE!=null){
			$sql=$extraPRE.$sql;
		}
		return $sql;
	}
	public function get($values=null, $alternative_table=null,  $extra=null, $extraPRE=null, $debug=null){
		$conditions=null;
		$getitems=null; 
		$unions=null;
		$orderby=null; 
		$object=null;
		$sqlinjectionprotection_variable=false;
		$sqltable=$alternative_table!=null ? $alternative_table:$this->table;
		if(isset($values["conditions"]) && $values["conditions"]!=null){
			$conditions=$values["conditions"];
		}
		if(isset($values["getitems"]) && $values["getitems"]!=null){
			$getitems=$values["getitems"];
		}
		if(isset($values["unions"]) && $values["unions"]!=null){
			$unions=$values["unions"];
		}
		if(isset($values["orderby"]) && $values["orderby"]!=null){
			$orderby=$values["orderby"];
		}
		if(isset($values["object"]) && $values["object"]!=null){
			$object=$values["object"];
		}		
		$param_type="";
		$conditions_text="";
		$union_text="";
		$sql="";
		if($getitems!=null){
			$sql='SELECT '.$getitems.' FROM '.$sqltable;
		}else{
			$sql='SELECT * FROM '.$sqltable;
		}		
		if(isset($unions)>0){			
			foreach ($unions as $key => $union) {
				if (strpos($key, 'ptable_acronym')  !== false){
					$union_text.=" as ". $union;
				}
				if (strpos($key, 'table_extra')  !== false){
					foreach ($union as $key_union => $table) {
						switch ($key_union) {
							case 'table_name':
								$union_text.=" , ". $table;
							break;
							case 'table_acronym':
								$union_text.=" as ". $table;
							break;
						}
					}
				}								
			}		
		}
		$sql=$sql . $union_text;
		if(isset($conditions)>0){
			$conditions_text.=" where ";			
			$conditionsvalues= array();
			foreach ($conditions as $key => $condition) {
				
				if(strpos($key, 'group') !== false){
					$conditions_text=" ".$conditions_text.$condition." ";
				}else if(strpos($key, 'logicgate') !== false){
					$conditions_text=" ".$conditions_text.$condition." ";
				}else{
					$conditions_text=$conditions_text.$key." ";
				}
				if(is_array($condition)){ 
					foreach($condition as $key => $val) {
						switch ($key) {
							case 'type':
							$param_type=$param_type.$val;
							break;
							case 'signal':
							$sqlinjectionprotection_variable=true;
							$conditions_text.=$val." ? ";
							break;
							case 'signalvariable':
							$conditions_text.=$val." ";
							break;
							case 'variable':
							$conditions_text.=$val." ";
							break;
							case 'val':
							array_push($conditionsvalues, $val);
							break;	
							default:
							break;				
						}							
					}
				}
			}
			$conditions_text.=" and 1=1 ";
			$a_params[] = & $param_type;
			foreach ($conditionsvalues as $key => $conditionvalue) {
				$a_params[]=& $conditionsvalues[$key];
			}
			$sql=$sql . $conditions_text;
		}

		if($orderby!=null){
			$sql=$sql." order by ".$orderby;
		}
		if($extra!=null){
			$sql.=$extra;
		}
		if($extraPRE!=null){
			$sql=$extraPRE.$sql;
		}
		$this->getConnection();
		$stmt = $this->conn->prepare($sql);
		if($stmt == false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $this->conn->errno . ' ' . $this->conn->error, E_USER_ERROR);
		}		
		if($sqlinjectionprotection_variable){
			call_user_func_array(array($stmt, 'bind_param'), $a_params);
		}
		$stmt->execute();
		$a_data= array();
		$res = $this->get_result($stmt);
		
		while($row = array_shift( $res )) {
			array_push($a_data, $row);
		}
		$result=array();
		if($debug!=null){
			if($object!=null){
				$result["obj"]=$this->createObject($a_data, $object);
			}else{
				$result["obj"]=$a_data;
			}
			$result["sql"]= $sql;
		}else{
			if($object!=null){
				$result=$this->createObject($a_data, $object);
			}else{
				$result=$a_data;
			}
		}
		return $result;
	}
	public function insert($values=null, $alternative_table=null){
		if(is_array($values) ){
			return $this->insertValues($values, $alternative_table);
		}else{
			return $this->insertValues($this->deconstructObject($values), $alternative_table);
		}
	}
	private function insertValues($values=null, $alternative_table=null){
		$param_type="";
		$values_text="";
		$sqltable=$alternative_table!=null ? $alternative_table:$this->table;
		$sql='Insert into '.$sqltable;
		if(isset($values)>0){
			$values_text=" ( ";

			$values_= array();
			foreach ($values as $key => $value) {
				$values_text=$values_text.$key.",";
				foreach ($value as $key => $val) {
					switch ($key) {
						case 'type':
						$param_type=$param_type.$val;
						break;						
						case 'val':
						array_push($values_, $val);
						break;	
						default:
						break;				
					}							
				}
			}
			$values_text=substr($values_text, 0, -1);
			$values_text=$values_text.") values(";

			$a_params[] = & $param_type;
			foreach ($values_ as $key => $valueprop) {

				$a_params[]=&$values_[$key];

				$values_text=$values_text."?,";
			}

			$values_text=substr($values_text, 0, -1);
			$values_text=$values_text.")";
			$sql=$sql . $values_text;
		}

		$this->getConnection();
		$stmt = $this->conn->prepare($sql);
		if($stmt == false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $this->conn->errno . ' ' . $this->conn->error, E_USER_ERROR);
		}

		if(isset($values)>0){
			call_user_func_array(array($stmt, 'bind_param'), $a_params);
		}
		$stmt->execute();
		$a_data= array();
		$res = $this->get_result($stmt);

		return $this->conn->insert_id;
	}
	public function update($values, $conditions=null, $alternative_table=null, $extra=null){
		$param_type="";
		$values_text="";
		$sqltable=$alternative_table!=null ? $alternative_table:$this->table;
		$sql='Update '.$sqltable." set ";
		$values_text="";
		$conditions_text="";
		$values_= array();
		foreach ($values as $key => $value) {
			$values_text=$values_text.$key." = ?,";
			foreach ($value as $key => $val) {
				switch ($key) {
					case 'type':
					$param_type=$param_type.$val;
					break;						
					case 'val':
					array_push($values_, $val);
					break;	
					default:
					break;				
				}							
			}
		}
		if(isset($conditions)>0){
			$conditions_text.=" where ";
			
			$conditionsvalues= array();
			foreach ($conditions as $key => $condition) {
				if(strpos($key, 'logicgate') !== false){
					$conditions_text=" ".$conditions_text.$condition." ";
				}else{
					$conditions_text=$conditions_text.$key." ";
				}
				if(is_array($condition)){ 
					foreach ($condition as $key => $val) {
						switch ($key) {
							case 'type':
							$param_type=$param_type.$val;
							break;
							case 'signal':
							$conditions_text=$conditions_text.$val." ? ";
							break;
							case 'val':
							array_push($conditionsvalues, $val);
							break;	
							default:
							break;				
						}							
					}
				}
			}
			$conditions_text.=" and 1=1 ";

		}
		$values_text=substr($values_text, 0, -1);

		$a_params[] = & $param_type;
		foreach ($values_ as $key => $valueprop) {			
			$a_params[]=&$values_[$key];
		}
		
		$sql=$sql . $values_text;

		foreach ($conditionsvalues as $key => $conditionvalue) {
			$a_params[]=& $conditionsvalues[$key];
		}
		$sql=$sql . $conditions_text;

		if($extra!=null){
			$sql.=$extra;
		}
		$this->getConnection();
		$stmt = $this->conn->prepare($sql);
		if($stmt == false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $this->conn->errno . ' ' . $this->conn->error, E_USER_ERROR);
		}
		if(isset($values)>0){
			call_user_func_array(array($stmt, 'bind_param'), $a_params);
		}
		$stmt->execute();
		$a_data= array();
		$res = $this->get_result($stmt);

		return true;
	}
	public function delete($conditions=null, $alternative_table=null){
		$param_type="";
		$conditions_text="";
		$sqltable=$alternative_table!=null ? $alternative_table:$this->table;
		$sql='delete FROM '.$sqltable;
		if(isset($conditions)>0){
			$conditions_text.=" where ";			
			$conditionsvalues= array();
			foreach ($conditions as $key => $condition) {
				if(strpos($key, 'logicgate') !== false){
					$conditions_text.=" ".$condition." ";
				}else{
					$conditions_text.=$key." ";
				}
				if(is_array($condition)){ 
					foreach($condition as $key => $val) {
						switch ($key) {
							case 'type':
							$param_type=$param_type.$val;
							break;
							case 'signal':
							$conditions_text=$conditions_text.$val." ? ";
							break;
							case 'val':
							array_push($conditionsvalues, $val);
							break;	
							default:
							break;				
						}							
					}
				}
			}
			$conditions_text.=" and 1=1 ";
			$a_params[] = & $param_type;
			foreach ($conditionsvalues as $key => $conditionvalue) {
				$a_params[]=& $conditionsvalues[$key];
			}
			$sql=$sql . $conditions_text;
		}

		$this->getConnection();
		$stmt = $this->conn->prepare($sql);
		if($stmt == false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $this->conn->errno . ' ' . $this->conn->error, E_USER_ERROR);
		}
		if(isset($conditions)>0){
			call_user_func_array(array($stmt, 'bind_param'), $a_params);
		}
		$stmt->execute();
		$a_data= array();
		$res = $this->get_result($stmt);
		
		return true;
	}
	public function getemptyView($model=null){
		echo $this->getPartialView("emptyview.php", $model);
	}
	//View Controller
	public function createView($model=null, $path="../view/base.php"){
		$_POST["Model"]=$model;
		$_POST["previousaction"]=$this->previousaction;
		$_POST["previouscontroller"]=$this->previouscontroller;
		include_once($path);
		$html=ob_get_contents();
		if (ob_get_contents()) ob_end_clean();
		if (ob_get_contents()) ob_end_flush();
		echo $html;
	}
	public function getPartialView($path, $model=null, $fullpath=null){
		$_POST["Model"]=$model;
		$_POST["previousaction"]=$this->previousaction;
		$_POST["previouscontroller"]=$this->previouscontroller;
		if($fullpath!=null){
			$path =$fullpath;
		}else{
			$path="../view/".$path;
		}
		ob_start();
		include($path);

		$html = ob_get_contents();

		if (ob_get_contents()) ob_end_clean();
		if (ob_get_contents()) ob_end_flush();
		return $html;
	}
	public function createPartialView($path, $model=null, $fullpath=null){
		$_POST["Model"]=$model;
		$_POST["previousaction"]=$this->previousaction;
		$_POST["previouscontroller"]=$this->previouscontroller;
		if($fullpath!=null){
			$path =$fullpath;
		}else{
			$path="../view/".$path;
		}
		include_once($path);
		$html=ob_get_contents();
		if (ob_get_contents()) ob_end_clean();
		if (ob_get_contents()) ob_end_flush();
		echo $html;
	}
	public function getActionUrl($controller, $action, $params=null){
		$params_text="";
		if($params!=null){
			$params_text.="&params=";
			foreach ($params as $key => $param) {
				$params_text.=$param.",";
			}
			$params_text=substr($params_text, 0, -1);		
		}
		$url="callercontroller.php?controller_name=".$controller."&action_name=".$action;
		if($params_text!=""){
			$url.=$params_text;
		}
		return $url;
	}
	public function printActionUrl($controller, $action, $params=null){
		$params_text="";
		if($params!=null){
			$params_text.="&params=";
			foreach ($params as $key => $param) {
				$params_text.=$param.",";
			}
			$params_text=substr($params_text, 0, -1);		
		}
		$url="callercontroller.php?controller_name=".$controller."&action_name=".$action;
		if($params_text!=""){
			$url.=$params_text;
		}
		echo $url;
	}
	public function createErrorMessage($msg){
		$message=new toast();
		$message->setmessage($msg);
		$message->settype("error");
		$message->setduration(3000);
		$model["obj"]=$message;
		$response=new response();
		$response->sethtml($this->getPartialView("toast.php", $model));
		$response->setstatuscode(200);
		echo json_encode($response);
	}
	public function createSuccessMessage($msg){
		$message=new toast();
		$message->setmessage($msg);
		$message->settype("success");
		$message->setduration(3000);
		$model["obj"]=$message;
		$response=new response();
		$response->sethtml($this->getPartialView("toast.php", $model));
		$response->setstatuscode(200);
		echo json_encode($response);
	}
	public function createCustomMessage($msg, $type, $duration){
		$message=new toast();
		$message->setmessage($msg);
		$message->settype($type);
		$message->setduration($duration);
		$model["obj"]=$message;
		$response=new response();
		$response->sethtml($this->getPartialView("toast.php", $model));
		$response->setstatuscode(200);
		echo json_encode($response);

	}
	public function getErrorMessage($msg){
		$message=new toast();
		$message->setmessage($msg);
		$message->settype("error");
		$message->setduration(3000);
		$model["obj"]=$message;
		
		return $this->getPartialView("toast.php", $model);
	}
	public function getSuccessMessage($msg){
		$message=new toast();
		$message->setmessage($msg);
		$message->settype("success");
		$message->setduration(3000);
		$model["obj"]=$message;
		return $this->getPartialView("toast.php", $model);
	}
	public function getCustomMessage($msg, $type, $duration){
		$message=new toast();
		$message->setmessage($msg);
		$message->settype($type);
		$message->setduration($duration);
		$model["obj"]=$message;
		return $this->getPartialView("toast.php", $model);

	}
	public function get_page_titles($controller, $action){
		switch ($controller) {
		    case "ProductController":
		        switch ($action) {
					case "Add":
				        echo "Adicionar tipo de produto";
				        break;
				    case "Index" || "getPartialIndex":
				        echo "Listagem de tipos de produtos";
				        break;
				}
		        break;
			case "PropertyController":
					switch ($action) {
						case "Add":
							echo "Adicionar propriedades";
							break;
						case "Index" || "getPartialIndex":
							echo "Listagem de propriedades";
							break;
					}
					break;
			case "InvoiceController":
				switch ($action) {
					case "Add":
						echo "Adicionar registo de Compra";
						break;
					case "Index" || "getPartialIndex":
						echo "Listagem de registos de Compra";
						break;
				}
				break;
			case "AssistantController":
				switch ($action) {
					case "Index" || "getPartialIndex":
						echo "Listagem de Assistentes";
						break;
				}
				break;
		}
		
	}
	function uploadImage() {
		$filesNum = count($_FILES['file']['name']);
		// Looping all files
		for ($i = 0; $i < $filesNum; $i++) {
			// same the file
			$newfilename=$this->generateGuid().".".explode(".", $_FILES['file']['name'][$i])[1];
			move_uploaded_file($_FILES['file']['tmp_name'][$i], "../Userfiles/".$newfilename);
			header('Content-Type: application/json');
    		echo json_encode($newfilename);
			exit();
		}
	  }

	public function getvalidationdatatags($class, $property){
                    
        $property = new ReflectionProperty(get_class($class), $property);
		echo "	data-validation='true'	";
        foreach ($property ->getAttributes() as $attribute) {
			$name = $attribute->getName();
			$args = $attribute->getArguments();
			echo "data-".$name.'="'.implode(', ', $args).'" ';
		}
                    
	}
	public function session(){
		if (session_status() != PHP_SESSION_ACTIVE && empty(session_id()) && !headers_sent()) {
				session_start();
		}
	}

}
