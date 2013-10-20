<?php

/* gbe_Signup.inc - defines the baselines for Signup management.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */
 
/* Class Signup 
 * 
 * This class represents all that is needed to describe an Signup.
 * 
 */
 
class Signup
{
   // These public properties represent the various columns in the table.
    public $SignupId;
    public $UserId;
    public $RunId;
    public $State;
    public $PrevState;
    public $Gender;
    public $Counted;
    public $UpdatedById;
    public $TimeStamp;	

	/* function load_from_Signupid
	 * 
	 * Used to load a single Signup from it's primary key 
	 *
	 * $ItemId:  the Signup Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_Signupid($SignupId)
	{
		$sql = sprintf("select * from Signup where SignupId = '%s'", 
			mysql_real_escape_string($SignupId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple Signup found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Signup not found:  $SignupId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}

	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the Signup table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
        $this->SignupId = (int)$row->SignupId;
        $this->UserId = (int)$row->UserId;
        $this->RunId = (int)$row->RunId;
        $this->State = (string)$row->State;
        $this->PrevState = (string)$row->PrevState;
        $this->Gender = (string)$row->Gender;
        $this->Counted = (string)$row->Counted;
        $this->UpdatedById = (int)$row->UpdatedById;
        $this->TimeStamp = (string)$row->TimeStamp;
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the Signup
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
        $this->SignupId = (int)$array['SignupId'];
        $this->UserId = (int)$array['UserId'];
        $this->RunId = (int)$array['RunId'];
        $this->State = (string)$array['State'];
        $this->PrevState = (string)$array['PrevState'];
        $this->Gender = (string)$array['Gender'];
        $this->Counted = (string)$array['Counted'];
		return true;
	}

	/* function save_to_db
	 * 
	 * Used to save the current status of the ticket item to the database.	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		// Sanitize database input.  Note, the build_sql_string uses 
		// mysql_real_escape_string() below.  
		
		if (strlen($this->State) > 20)
			return display_error("State is too long.  It must be one of an enumeration.");	
		if (strlen($this->PrevState) > 20)
			return display_error("PrevState is too long.  It must one of an enumeration.");	
		if (strlen($Gender->Gender) > 10)
			return display_error("Gender is too long.  It must be Male or Female.");	
		if (strlen($Gender->Counted) > 1)
			return display_error("Counted is too long.  It must be Y or N.");	

		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save Signup to DB:  not logged in.");	
				
				
		$this->TimeStamp = date("Y-m-d H:i:s", time());	
		$this->UpdatedById = $_SESSION[SESSION_LOGIN_USER_ID];

		
		// See if this is an insert or update.
		
		$this->SignupId = mysql_real_escape_string($this->SignupId);
		$sql = "select * from Signup where SignupId = '$this->SignupId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update Signup set ";
		}	
		else
		{
			$sql = "insert into Signup set ";
		}

        $sql .= build_sql_string('SignupId', $this->SignupId, false);
        $sql .= build_sql_string('UserId', $this->UserId);
        $sql .= build_sql_string('RunId', $this->RunId);
        $sql .= build_sql_string('State', $this->State);
        $sql .= build_sql_string('PrevState', $this->PrevState);
        $sql .= build_sql_string('Gender', $this->Gender);
        $sql .= build_sql_string('Counted', $this->Counted);
        $sql .= build_sql_string('UpdatedById', $this->UpdatedById);
        $sql .= build_sql_string('TimeStamp', $this->TimeStamp);
        			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where SignupId = '%s'", $this->SignupId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into Signup failed");
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		// delete Signup
		$sql = sprintf("delete from Signup where SignupId = '%s'", 
			mysql_real_escape_string($this->SignupId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql); 		
		
	}
}

/* function remove_run_signups
 * 
 * Function removes any signups that match a given run
 *
 * $RunId - unique ID number for the original Run 
 * Returns: nothing.
 */
function remove_run_signups($RunId)
{
  // delete Signup
  $sql = sprintf("delete from Signup where RunId = '%s'", 
    mysql_real_escape_string($RunId));
  $result = mysql_query($sql);
  if (!$result)
    return display_mysql_error ('Cannot execute query', $sql); 		
}
