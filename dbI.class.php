<?php 

require_once dirname(__FILE__).'/dbI_Table.class.php';
require_once dirname(__FILE__).'/dbI_Field.class.php';
require_once dirname(__FILE__).'/dbI_Result.class.php';

define('STRING','string');
define('INTEGER',3);
define('OPERATOR','dbI_WhereOperator');

class dbI {

private $debug = false;
private $dbUser,$dbPass,$dbHost,$dbName;
private $error  = array();
private $DB;

private $_tables = array();
private $_loadedTables = array();
public $tables = array();


// Constructor 
//--------------------------------------------------------------------------------------------------------------
function __construct( $opts ){
		// Update options ---------------------------
		foreach($opts as $key => $val){
			$this->$key = $val;
		};
		
		// Make sure connection is OK
		if(!$this->check_connection()){ return $this->fail(); };
		
		// Grab a list of all tables
		$this->get_table_list();
		
		
	}//
	
	
// Check DB connection works
//--------------------------------------------------------------------------------------------------------------
private function check_connection(){
		@ $this->DB = new mysqli($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
		if($this->DB->connect_error != ''){
			$this->error[] = $this->DB->connect_error;
			return false;
		};	
		return true;
	}//


// Get a list of all tables
//--------------------------------------------------------------------------------------------------------------
private function get_table_list(){
		$query = $this->DB->query('SHOW TABLES');
		while($row = $query->fetch_array()){
			$this->_tables[] = $row[0];
		};
	}//


// Return a table object
//--------------------------------------------------------------------------------------------------------------
public function table( $table ){
		// Check if table has been loaded
		if(!in_array($table,$this->_loadedTables) ){
			// Check table exists
			if(!in_array($table,$this->_tables)){
				$this->error[] = "Table [$table] does not exist";
				$this->fail();
				return null;
			};
			
			// Load the table
			$this->load_table($table);
		};
		
		return $this->tables[$table];
	}//


// Load a table object map
//--------------------------------------------------------------------------------------------------------------
public function load_table( $table ){
		$this->tables[$table] = new dbI_Table($this->DB,$table);
	}//


// Straight-up mySQL query on the db
//--------------------------------------------------------------------------------------------------------------
public function query( $sql ){
		return $this->DB->query($sql);
	}//

	
// Die Gracefully
//--------------------------------------------------------------------------------------------------------------
private function fail(){
		if($this->debug != true){ return; };
		echo count($this->error)." errors\n"
			   .implode("\n",$this->error)."\n\n";
		return false;
	}//
	
};// end class dbI ?>
