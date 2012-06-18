<?php class dbI_Field { 
	 
function __construct( $name, $length, $key, $extra, $DB){
		$this->name = $name;
		$this->length = $length;
		$this->key = $key;
		$this->extra = $extra;
		$this->DB = $DB;
	}//
	

public function prepare( $value ){
		// Prepare as a string
		$value = $this->DB->real_escape_string($value);
		return "'$value'";
	}//
	


};// end class dbI_Field 





class dbI_Field_INT extends dbI_Field {

function __construct( $name, $length, $key, $extra, $DB){
		$this->type = "INT";
		parent :: __construct($name, $length, $key, $extra, $DB);
	}//
	
public function prepare( $value ){
		return (int) $value;
	}//
	
};// end class dbI_Field_INT

//=====================================================================================================================================================
//=====================================================================================================================================================

class dbI_Field_STR extends dbI_Field {

function __construct( $name, $length, $key, $extra, $DB){
		$this->type = "INT";
		parent :: __construct($name, $length, $key, $extra, $DB);
	}//
	
};// end class dbI_Field_STR ?>