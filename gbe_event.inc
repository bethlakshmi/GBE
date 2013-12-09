<?php

/* gbe_event.inc - defines the baselines for event management.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */
 
/* Class Event 
 * 
 * This class represents all that is needed to describe an event.
 * 
 */
 
class Event
{
   // These public properties represent the various columns in the table.
	public $EventId;
	public $Title;
    public $Author;
    public $GameType;
    public $GameEMail;
    public $Organization;
    public $Homepage;
    public $MinPlayersNeutral;
    public $MaxPlayersNeutral;
    public $PrefPlayersNeutral;
    public $Hours;
    public $SpecialEvent;
    public $CanPlayConcurrently;
    public $IsOps;
    public $IsConSuite;
    public $ConMailDest;
    public $Description;
    public $ShortBlurb;
    public $LastUpdated;	
    public $UpdatedById;

	

	/* function load_from_eventid
	 * 
	 * Used to load a single Event from it's primary key 
	 *
	 * $ItemId:  the Event Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_eventid($EventId)
	{
		$sql = sprintf("select * from Events where EventId = '%s'", 
			mysql_real_escape_string($EventId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple events found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Event not found:  $EventId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}

	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the Events table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
		$this->EventId = (int)$row->EventId;
		$this->Title = (string)$row->Title;
		$this->Author = (string)$row->Author;
		$this->GameType = (string)$row->GameType;
		$this->GameEMail = (string)$row->GameEMail;
		$this->Organization = (string)$row->Organization;
		$this->Homepage = (string)$row->Homepage;
		$this->MinPlayersNeutral = (int)$row->MinPlayersNeutral;
		$this->MaxPlayersNeutral = (int)$row->MaxPlayersNeutral;
		$this->PrefPlayersNeutral = (int)$row->PrefPlayersNeutral;
		$this->Hours = (int)$row->Hours;
		$this->SpecialEvent = (int)$row->SpecialEvent;
		$this->CanPlayConcurrently = (string)$row->CanPlayConcurrently;
		$this->IsOps = (string)$row->IsOps;
		$this->ConMailDest = (string)$row->ConMailDest;
		$this->Description = (string)$row->Description;
		$this->ShortBlurb = (string)$row->ShortBlurb;
		$this->LastUpdated = (string)$row->LastUpdated;
		$this->UpdatedById = (int)$row->UpdatedById;
		$this->IsConSuite = (string)$row->IsConSuite;
				
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the Event
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
		$this->EventId = (int)$array['EventId'];
		$this->Title = (string)$array['Title'];
		$this->Author = (string)$array['Author'];
		$this->GameType = (string)$array['GameType'];
		$this->GameEMail = (string)$array['GameEMail'];
		$this->Organization = (string)$array['Organization'];
		$this->Homepage = (string)$array['Homepage'];
		$this->MinPlayersNeutral = (int)$array['MinPlayersNeutral'];
		$this->MaxPlayersNeutral = (int)$array['MaxPlayersNeutral'];
		$this->PrefPlayersNeutral = (int)$array['PrefPlayersNeutral'];
		$this->Hours = (int)$array['Hours'];
		$this->SpecialEvent = (bool)$array['SpecialEvent'];
		$this->CanPlayConcurrently = (string)$array['CanPlayConcurrently'];
		$this->IsOps = (string)$array['IsOps'];
		$this->ConMailDest = (string)$array['ConMailDest'];
		$this->Description = (string)$array['Description'];
		$this->ShortBlurb = (string)$array['ShortBlurb'];
		$this->IsConSuite = (string)$array['IsConSuite'];
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
		
		if (!is_bool($this->SpecialEvent))
			return display_error("Act isGroup state is not in a valid format.");	

		if (strlen($this->Title) > 128)
			return display_error("Title is too long.  It must be 128 characters or less.");	
		if (strlen($this->Author) > 128)
			return display_error("Author is too long.  It must be 128 characters or less.");	
		if (strlen($this->GameType) > 30)
			return display_error("Game Type is too long.  It must be 30 characters or less.");	
		if (strlen($this->GameEMail) > 64)
			return display_error("Game EMail is too long.  It must be 64 characters or less.");	
		if (strlen($this->Organization) > 64)
			return display_error("Organization is too long.  It must be 64 characters or less.");	
		if (strlen($this->Homepage) > 500)
			return display_error("Homepage is too long.  It must be 500 characters or less.");	
		if (strlen($this->CanPlayConcurrently) > 128)
			return display_error("Can Play Concurrently is too long.  It must be 128 characters or less.");	
		if (strlen($this->IsOps) > 1)
			return display_error("Is Ops is too long.  It must be Y or N.");	
		if (strlen($this->ConMailDest) > 1)
			return display_error("Con Mail Dest is too long.  It must be Y or N.");	
		if (strlen($this->Description) > 500)
			return display_error("Description is too long.  It must be 500 characters or less.");	
		if (strlen($this->ShortBlurb) > 500)
			return display_error("ShortBlurb is too long.  It must be 500 characters or less.");	

		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save Event to DB:  not logged in.");	
				
				
		$this->LastUpdated = date("Y-m-d H:i:s", time());	
		$this->UpdatedById = $_SESSION[SESSION_LOGIN_USER_ID];

		
		// See if this is an insert or update.
		
		$this->EventId = mysql_real_escape_string($this->EventId);
		$sql = "select * from Events where EventId = '$this->EventId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple event rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update Events set ";
		}	
		else
		{
			$sql = "insert into Events set ";
		}

		$sql .= build_sql_string('EventId', $this->EventId, false);
		$sql .= build_sql_string('Title', $this->Title);
		$sql .= build_sql_string('Author', $this->Author);
		$sql .= build_sql_string('GameType', $this->GameType);
		$sql .= build_sql_string('GameEMail', $this->GameEMail);
		$sql .= build_sql_string('Organization', $this->Organization);
		$sql .= build_sql_string('Homepage', $this->Homepage);
		$sql .= build_sql_string('MinPlayersNeutral', $this->MinPlayersNeutral);
		$sql .= build_sql_string('MaxPlayersNeutral', $this->MaxPlayersNeutral);
		$sql .= build_sql_string('PrefPlayersNeutral', $this->PrefPlayersNeutral);
		$sql .= build_sql_string('Hours', $this->Hours);
		$sql .= build_sql_string('SpecialEvent', $this->SpecialEvent);
		$sql .= build_sql_string('CanPlayConcurrently', $this->CanPlayConcurrently);
		$sql .= build_sql_string('IsOps', $this->IsOps);
		$sql .= build_sql_string('ConMailDest', $this->ConMailDest);
		$sql .= build_sql_string('Description', $this->Description);
		$sql .= build_sql_string('ShortBlurb', $this->ShortBlurb);
		$sql .= build_sql_string('LastUpdated', $this->LastUpdated);
		$sql .= build_sql_string('UpdatedById', $this->UpdatedById);

			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where EventId = '%s'", $this->EventId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into Events failed");
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		// delete event
		$sql = sprintf("delete from Events where EventId = '%s'", 
			mysql_real_escape_string($this->EventId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);

 		// delete runs
 		remove_event_runs($this->EventId);
 		
 		// delete GMs
		$sql = sprintf("delete from GMs where EventId = '%s' and Role != \"performer\"", 
			mysql_real_escape_string($this->EventId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);

		// clear from Bids
		$sql = sprintf("update Bids set EventId=0 where EventId = '%s'", 
			mysql_real_escape_string($this->EventId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		
	}
}

