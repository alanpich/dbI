<?php class dbI_Result {

function __construct( $R, $query ){
		$this->num_rows = $R->num_rows;
		$this->query = $query;
		$this->rows = array();
		
		while($row = $R->fetch_assoc()){
			$this->rows[] = $row;
		};
	}//
	
	
	
public function toArray(){
		return $this->rows;
	}//
	
public function dump(){
		echo print_r($this->rows,true);
	}//
	
	
public function sort( $field, $DIR = "ASC" ){
		$keys = array();
		foreach($this->rows as $row){
			$keys[] = $row[$field];
		};
		array_multisort($keys,$this->rows);
		
		// Reverse sort order if desc
		if($DIR=='DESC'){ $this->rows = array_reverse($this->rows);	};
		
		return $this;
	}//
	
};// end class dbI_Result ?>
