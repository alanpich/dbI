<?php class dbI_Table {
	
public $name;
public $primary;
public $fields = array();
private $DB;
private $typemap = array(
				'int'		=> "INT",
				'tinyint' 	=> "INT",
				'bigint' 	=> "INT",
				'varchar' 	=> "STR",
				'text' 		=> "STR"
			);

function __construct( $DB, $tableName ){
		$this->name = $tableName;
		$this->DB = $DB;
		
		$this->map_table();
	}//
	
	
// Query
public function query($sql){
		$this->DB->query($sql);
		return $this->DB->error;
	}//

	
private function map_table(){
		// Grab field data
		$res = $this->DB->query('DESCRIBE '.$this->name);
		while($row = $res->fetch_assoc()){
			$this->fields[ $row['Field'] ] = $this->getFieldObject($row);
			if($row['Key'] == 'PRI'){
				$this->primary = $row['Field'];
			};
		};
	}//
	
	
private function getFieldObject( $data ){
		$name = $data['Field'];
		$key = $data['Key'];
		$extra = $data['Extra'];
		
		$bits = explode('(',$data['Type']);
		$type = $this->typemap[$bits[0]];
		$length = (int) str_replace(')','',$bits[1]);
		
		$class = "dbI_Field_$type";
	
		return new $class($name, $length, $key, $extra, $this->DB);
	}//


public function get( $filters = false ){
	
		if( is_array($filters)){
			// Set WHERE operator
			
			$operator = isset($filters["__OPERATOR__"])? " ".$filters["__OPERATOR__"]." " : ' AND ';
			
			// Get WHERE clause
			$wheres = array();
			foreach($filters as $key => $val){
				if( in_array($key,array_keys($this->fields))){
					$wheres[] = "`$key`=". $this->fields[$key]->prepare($val);
				};
			};
			$WHERE = (count($wheres)>0) ? ' WHERE '.implode($operator,$wheres) : '';
			
			// Build & run query
			$sql = 'SELECT * FROM `'.$this->name.'`'.$WHERE;
		} else 
		if($filters !== false){
			// search on primary key
			$sql = 'SELECT * FROM `'.$this->name.'` WHERE `'.$this->primary.'`='.$this->fields[$this->primary]->prepare($filters);
		} else {
			$sql = 'SELECT * FROM `'.$this->name;
		};
		
		$res = $this->DB->query($sql);
		
		// Return useful object
		$R = new dbI_Result($res,$sql,$this);
		// Return response object
		return $R;	
	}//





// Insert
//-----------------------------------------------------------------------------------------------------------------------------------------
public function insert($values){
		 if(!is_array($values)){ return; };
		 
		 $keys = array();
		 $vals = array();
		 
		 foreach($values as $key => $val){
		 	$keys[] = '`'.$key.'`';
		 	$vals[] = $this->fields[$key]->prepare($val);
		 };
		 
		 $keyStr = implode(',',$keys);
		 $valStr = implode(',',$vals);
		 
		 $sql = 'INSERT INTO `'.$this->name.'` ('.$keyStr.') VALUES ('.$valStr.')';
		 $q = $this->query($sql);
		 $ID = $this->DB->insert_id;
		 if(is_numeric($ID) && $ID > 0){
			return $ID;
		};
		return false;
	}//
	


// Update
//-----------------------------------------------------------------------------------------------------------------------------------------
public function update($values = array(), $where = array()){
		if(!is_array($values)){return;};

		$sets = array();

		foreach($values as $key => $val){
			$sets[] = '`'.$key.'`'.'='. $this->fields[$key]->prepare($val);
		};


		$wheres = array();
		if(is_array($where)){
			foreach($where as $key => $val){
				$wheres[] = '`'.$key.'`'.'='. $this->fields[$key]->prepare($val);
			};
		} else {
			$wheres[] = $this->primary.'='.$this->fields[$this->primary]->prepare($where);
		};
		$whereClause = count($wheres)<1? '' : 'WHERE '.implode(' AND ',$wheres);

		$sql = 'UPDATE `'.$this->name.'` '.$whereClause;

		if($q = $this->query($sql)){
			return true;
		} else {
			return $q;
		};
		

		
	}//


	
	
// Delete
//-----------------------------------------------------------------------------------------------------------------------------------------
public function delete($filters){
		$sql = 'DELETE FROM `'.$this->name.'`'.$this->where($filters);
		die($sql);
	}//





// Build a WHERE statement
//-----------------------------------------------------------------------------------------------------------------------------------------
public function where($filters, $operator = 'AND'){
		
		$wheres = array();
		
		foreach($filters as $key => $val){			
			// Allow operator override
			if($key === OPERATOR){	$operator = $val;
			} else {
				
				// Is array or key=>val?
				if(is_numeric($key)){
					// Array -> OR group
				} else {
					// Is key=>val straight up, or OR array?
					if(is_array($val)){
						// OR for values of same key
						$clauses = array();
						foreach($val as $_val){
							$clauses[] = "`$key`=".$this->fields[$key]->prepare($_val);
						};
						$clause = '('.implode(' OR ',$clauses).')';
					};
				};
				
				$wheres[] = $clause;
			};
		};
		
		$operator = " ".$operator." ";
		return implode($operator,$wheres);
		
	}//


private function whereClause($mxd){
		
	}//




};// end class dbI_Table ?>
