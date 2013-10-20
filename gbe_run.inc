<?php
include ("gbe_signup.inc");

/* gbe_Run.inc - defines the baselines for Run management.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */
 
/* Class Run 
 * 
 * This class represents all that is needed to describe an Run.
 * 
 */
 
class Run
{
   // These public properties represent the various columns in the table.
    public $RunId;
    public $EventId;
    public $ShowId;
    public $Track;
    public $Span;
    public $Day;
    public $StartHour;
    public $TitleSuffix;
    public $ScheduleNote;
    public $Rooms;
    public $Viewable;
    public $UpdatedById;
    public $LastUpdated;
	

	/* function load_from_Runid
	 * 
	 * Used to load a single Run from it's primary key 
	 *
	 * $ItemId:  the Run Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_Runid($RunId)
	{
		$sql = sprintf("select * from Runs where RunId = '%s'", 
			mysql_real_escape_string($RunId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple Runs found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Run not found:  $RunId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}

	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the Runs table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
        $this->RunId = (int)$row->RunId;
        $this->EventId = (int)$row->EventId;
        $this->ShowId = (int)$row->ShowId;
        $this->Track = (int)$row->Track;
        $this->Span = (int)$row->Span;
        $this->Day = (string)$row->Day;
        $this->StartHour = (int)$row->StartHour;
        $this->TitleSuffix = (string)$row->TitleSuffix;
        $this->ScheduleNote = (string)$row->ScheduleNote;
        $this->Rooms = (string)$row->Rooms;
        $this->Viewable = (string)$row->Viewable;
        $this->UpdatedById = (int)$row->UpdatedById;
        $this->LastUpdated = (string)$row->LastUpdated;				
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the Run
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
        $this->RunId = (int)$array['RunId'];
        $this->EventId = (int)$array['EventId'];
        $this->ShowId = (int)$array['ShowId'];
        $this->Track = (int)$array['Track'];
        $this->Span = (int)$array['Span'];
        $this->Day = (string)$array['Day'];
        $this->StartHour = (int)$array['StartHour'];
        $this->TitleSuffix = (string)$array['TitleSuffix'];
        $this->ScheduleNote = (string)$array['ScheduleNote'];
        $this->Rooms = (string)$array['Rooms'];
        $this->Viewable = (string)$array['Viewable'];
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
		
		if (strlen($this->Day) > 3)
			return display_error("Day is too long.  It must be 3 characters or less.");	
		if (strlen($this->TitleSuffix) > 32)
			return display_error("TitleSuffix is too long.  It must be 32 characters or less.");	
		if (strlen($this->ScheduleNote) > 32)
			return display_error("ScheduleNote is too long.  It must be 32 characters or less.");	

		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save Run to DB:  not logged in.");	
				
				
		$this->LastUpdated = date("Y-m-d H:i:s", time());	
		$this->UpdatedById = $_SESSION[SESSION_LOGIN_USER_ID];

		
		// See if this is an insert or update.
		
		$this->RunId = mysql_real_escape_string($this->RunId);
		$sql = "select * from Runs where RunId = '$this->RunId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update Runs set ";
		}	
		else
		{
			$sql = "insert into Runs set ";
		}

        $sql .= build_sql_string('RunId', $this->RunId, false);
        $sql .= build_sql_string('EventId', $this->EventId);
        $sql .= build_sql_string('ShowId', $this->ShowId);
        $sql .= build_sql_string('Track', $this->Track);
        $sql .= build_sql_string('Span', $this->Span);
        $sql .= build_sql_string('Day', $this->Day);
        $sql .= build_sql_string('StartHour', $this->StartHour);
        $sql .= build_sql_string('TitleSuffix', $this->TitleSuffix);
        $sql .= build_sql_string('ScheduleNote', $this->ScheduleNote);
        $sql .= build_sql_string('Rooms', $this->Rooms);
        $sql .= build_sql_string('Viewable', $this->Rooms);
        $sql .= build_sql_string('UpdatedById', $this->UpdatedById);
        $sql .= build_sql_string('LastUpdated', $this->LastUpdated);
			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where RunId = '%s'", $this->RunId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into Runs failed");
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		// delete Run
		$sql = sprintf("delete from Runs where RunId = '%s'", 
			mysql_real_escape_string($this->RunId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);

 		// delete signups
 		remove_run_signups($this->RunId)
		
	}
}

/* function remove_event_runs
 * 
 * Function removes runs for a given event, and any subsequent signups for
 *    that run.
 *
 * $EventId - unique ID number for the original Event 
 * Returns: nothing.
 */
function remove_event_runs($EventId)
{
  $sql = sprintf("select RunId from Runs where EventId = '%s'", 
    mysql_real_escape_string($EventId));
		 
  $result = mysql_query($sql);
		
  if (!$result)
    return display_mysql_error ('Cannot execute query', $sql);
		
  while ($row = mysql_fetch_object($result))
  {
    remove_run_signups($row->RunId);
  }
  
  // delete Runs
  $sql = sprintf("delete from Runs where EventId = '%s'", 
    mysql_real_escape_string($EventId));
  $result = mysql_query($sql);
  if (!$result)
    return display_mysql_error ('Cannot execute query', $sql); 		
}
