<?php


// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class used to create a connection to a external database and to perform subsequent queries needed to extract data
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package External connection
 * @version 2.0
 */

/**
* include the standard Adodb library
* if adodb is removed from moodle in the future, we might need
* to include it specially within ILP
*/
$adodb_dir = $CFG->dirroot . '/lib/adodb';
require_once( "$adodb_dir/adodb.inc.php" );
require_once( "$adodb_dir/adodb-exceptions.inc.php" );
require_once( "$adodb_dir/adodb-errorhandler.inc.php" );


class external_connection   {

    protected $db;
    public 	$errorlist;                   //collect a list of errors
    public	$prelimcalls;				  //calls to be executed before the sql is called

    /**
     * Constructor function
     * @param array $cparams arguments used to connect to a external db. array keys:
     *            type: the type of connection mssql, mysql etc
     *            host: host connection string
     *            user: the username used to connect to db
     *            pass: the password used to connect to the db
     *            dbname: the dbname
     *
     * @return \ulcc_external_connection true if not errors encountered false if otherwise
     */
    public function __construct( $cparams=array()){
        global $CFG;
        $this->db = false;
        $this->errorlist = array();
        $this->prelimcalls	=	array();

        $dbconnectiontype	=	(is_array($cparams) && !empty($cparams['type'])) 	? $cparams['type']	: 	"";

        //if the dbconnection is empty return false
       if (empty($dbconnectiontype)) return false;

        $host	=	(is_array($cparams) && !empty($cparams['host'])) 	? $cparams['host']	: 	"";
        $user	=	(is_array($cparams) && !empty($cparams['user'])) 	? $cparams['user']	: 	"";
        $pass	=	(is_array($cparams) && !empty($cparams['pass'])) 	? $cparams['pass']	: 	"";
        $dbname	=	(is_array($cparams) && !empty($cparams['dbname'])) 	? $cparams['dbname']	: 	"";
        
        //build the connection
        $connectioninfo = $this->get_external_connection($dbconnectiontype,$host,$user,$pass,$dbname);
        
        //return false if any errors have been found (we can display errors if wanted)
        $this->errorlist = $connectioninfo[ 'errorlist' ] ;
        if( !empty($this->errorlist))	return false;
        
        //give the connection to the db var
        $this->db = $connectioninfo[ 'db' ];
        return true;
    }

    /**
     * 
     * Creates a connection to a database using the values given in the arguments
     * @param string $type the type of connection to be used
     * @param string $host the hosts address
     * @param string $user the username that will be used to connect to db
     * @param string $pass the password used in conjunction with the username
     * @param string $dbname the name of the db that will be used
     * @return array|bool
     */
    public function get_external_connection( $type, $host, $user, $pass, $dbname ){
        $errorlist = array();
        $db = false;

        //trim any space chars (which seem to pass empty tests) and if empty return false
        $trimtype   =  trim($type);
        if (empty($trimtype))  return false;

        try{
            $db = ADONewConnection( $type );
        }
        catch( exception $e ){
            $errorlist[] = $e->getMessage();
        }
        if( $db ){
	        try{
	            $db->SetFetchMode(ADODB_FETCH_ASSOC);
	            $db->Connect( $host, $user, $pass, $dbname );
	        }
	        catch( exception $e ){
	            $errorlist[] = $e->getMessage();
	        }
        }
        return array(
            'errorlist' => $errorlist,
            'db' => $db
        );
    }

    /**
    * take a result array and return a list of the values in a single field
    * @param array of arrays $a
    * @param string $fieldname
    * @return array of scalars
    */
    protected function get_column_valuelist( $a, $fieldname ){
        $rtn = array();
        foreach( $a as $row ){
            $rtn[] = $row[ $fieldname ];
        }
        return $rtn;
    }

    /**
     * Takes an array in the format array($a=>array($b=> $c)) and returns 
     * a string in the format $a $b $c  
     * @param array $paramarray the params that need to be converted to 
     * a string
     * @return string
     */
    function arraytostring($paramarray)	{
    	$str	=	'';
    	$and	=	'';
    	if (!empty($paramarray) && is_array($paramarray)) 
    	foreach ($paramarray as $k => $v) {
    		$str	=	"$str $and ";
    		//$str	.=	(is_array($v)) ?	$k." ".$this->arraytostring($v) :	" $k $v";
			//remove all ~ from fieldname - this is so that when a field is used twice in a query, 
			//you can use the ~ to make a unique array key, but still generate sql with the simple fieldname
			//this will cause problems if the underlying database table has a fieldname with a ~ in it
    		$str	.=	(is_array($v)) ?	str_replace( '~' , '', $k ) ." ".$this->arraytostring($v) :	" $k $v";
    		$and	=	' AND ';
    	}
    	
    	return $str;
    }
    
    
    
    /**
     * builds an sql query using the given parameter and returns the results of the query 
     * 
     * @param string $table the name of the table or view that will be queried
     * @param array  $whereparams array holding params that should be used in the where statement
     * 				 format should be $k = field => array( $k= operand $v = field value) 
     * 				 e.g array('id'=>array('='=>'1')) produces id = 1  
     * @param mixed  $fields array or string of the fields that should be returned 
     * @param array  $addionalargs additional arguments that may be used the:
     * 				 'sort' the field that should be sorted by and DESC or ASC
     * 				 'group' the field that results should be grouped by
     * 				 'lowerlimit' lower limit of results 
     * 				 'upperlimit' should be used in conjunction with lowerlimt to limit results
     * @return bool
     */
    function return_table_values($table,$whereparams=null,$fields='*',$addionalargs=null) {
    	
    	//check if the fields param is an array if it is implode  
    	$fields 	=	(is_array($fields))		?	implode(', ',$fields)	:	$fields;		
    	   	
    	//create the select statement
    	$select		=	"SELECT		$fields ";
    	
    	//create the from 
    	$from		=	"FROM		$table ";
    	
    	//get the 
    	$wheresql		=	$this->arraytostring($whereparams);
    	
    	$where			=	(!empty($wheresql)) ? "WHERE $wheresql "	: 	"";
    	
    	$sort		=	'';
    	if (isset($addionalargs['sort']))	$sort		=	(!empty($addionalargs['sort']))	? "ORDER BY {$addionalargs['sort']} "	: "";

    	$group		=	'';
    	if (isset($addionalargs['group']))	$group		=	(!empty($addionalargs['group']))	? "GROUP BY {$addionalargs['group']} "	: "";
    	
    	$limit		=	'';
    	if (isset($addionalargs['lowerlimt']))	$limit		=	(!empty($addionalargs['lowerlimit']))	? "LIMIT {$addionalargs['lowerlimit']} "	: "";
    	
    	if (isset($addionalargs['upperlimt']))	{
    		if (empty($limit)) {
    			$limit		=	(!empty($addionalargs['upperlimt']))	? "LIMIT {$addionalargs['upperlimt']} "	: "";		
    		} else {
    			$limit		.=	(!empty($addionalargs['upperlimt']))	? ", {$addionalargs['upperlimt']} "	: "";
    		}
   		}
   	
    	$sql		=	$select.$from.$where.$sort.$group.$limit;
    	$result		= (!empty($this->db)) ? $this->execute($sql) : false;
    	return		(!empty($result->fields))	?	$result->getRows() :	false;
    }

    function arraytovar($val) {
    	if (is_array($val)) {
    		if (!is_array(current($val))) {
    			return current($val);
    		} else {
    			return $this->arraytovar(current($val));
    		}
    	}
    	
    	return $val;
    }


    /**
     *
     * builds a stored procedure query using the arguments given and returns the result
     * @param string $procedurename the name of the stored proceudre being called
     * @param string $procedureargs
     * @internal param array $mixed or string $procedureargs variables passed to stored procedure
     *
     * @return mixed
     */
    function return_stored_values($procedurename,$procedureargs='') {
    	
    	if (is_array($procedureargs)) {
			$temp	=	array();
    		foreach ($procedureargs as $p) {
				$val	=	$this->arraytovar($p);
			
    			if (!empty($val)) {
					$temp[]	=	$val;
				}
    		}
    		
    		$args	=	implode(', ',$temp);
    	} else {
    		$args	=	$procedureargs;
    	}
        if ($args) {
            $args = ',' . $args;
        }
		$sql	=	"EXECUTE $procedurename $args";
		
		$result		= (!empty($this->db)) ? $this->execute($sql) : false;
		return		(!empty($result->fields))	?	$result->getRows() :	false;
    }


    /**
     * step through an array of $key=>$value and assign them
     * to the class $params array
     * @param array $arrayvar the array that will hold the params
     * @param array $params the params that will be passed to $arrayvar
     * @return void
     */
    protected function set_params( &$arrayvar,$params ){
        foreach( $params as $key=>$value ){
            $arrayvar[ $key ] = $value;  
        }
    }

	/**
	 * This function makes any calls to the database that need to be made before the sql statement is run
	 * The function uses the $prelimcalls var 
	 */    
    private function make_prelimcall()	{
    	if (!empty($this->prelimcalls))	{
    		foreach ($this->prelimcalls as $pc)	{
	    		try {
		        	$res = $this->db->Execute( $pc );
				} catch (exception $e) {
					//we wont do anything if these calls fail
				}	
    		}
    	}
    }
    
    /**
    * executes the given sql query 
    * @param string $sql
    * @return array of arrays      
    */
    public function execute( $sql){
    	$this->make_prelimcall();
    	try {
        	$res = $this->db->Execute( $sql );
		} catch (exception $e) {
			return false;	
		}
        return $res;
    }

    /**
     * intended to return just the front item from an array of arrays (eg a recordset)
     * if just the array is sent, just the first row will be returned
     * if 2nd argument sent, then just the value of that field in the first row will be returned
     * @param array $a
     * @param bool|string $fieldname
     * @return mixed (array or single value)
     */
    public static function get_top_item( $a , $fieldname=false ){
        $toprow = array_shift( $a );
        if( $fieldname ){
            return $toprow[ $fieldname ];
        }
        return $toprow;
    }

        
}
