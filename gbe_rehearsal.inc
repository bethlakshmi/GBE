<?php

/* gbe_rehearsal.inc - defines the baselines for rehearsal management.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */
 
/* Class Rehearsal 
 * 
 * This class represents all that is needed to describe a set of rehearsals.
 * Every show has a Tech Rehearsal that comprises
 *   - Several hours (flexible) of tech rehearsal
 *   - Hour-long slots for a subgroup of acts to rehearse
 *   - Spaces for the skills of the tech crew, # of spaces defined by
 *      Stage Manager or Tech Director.
 * 
 */
 
class Rehearsal
{
	// These public properties represent the various columns in the table.
	
	public $TechRehearsal;
	public $ActRehearsals;
	public $ShowId;
	

	/* function load_from_showid
	 * 
	 * Used to load a set of rehearsals based on the Show they support. 
	 *
	 * $ItemId:  the Show Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_showid($ShowId)
	{
		$sql = sprintf("select * from Runs where Show = '%s'", 
			mysql_real_escape_string($ActId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Act not found:  $ActId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}

	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the Acts table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
		$this->ActId = (int)$row->ActId;
		$this->BidId = (int)$row->BidId;
		$this->ShowId = (int)$row->ShowId;
		$this->RehearsalId = (int)$row->RehearsalId;	
		$this->ActInfoId = (int)$row->ActInfoId;
		$this->GroupBio = (string)$row->GroupBio;	
		$this->isGroup = (bool)$row->isGroup;
		
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the TickeItem
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
		$this->ActId = (int)$array['ActId'];
		$this->BidId = (int)$array['BidId'];
		$this->ShowId = (int)$array['ShowId'];
		$this->RehearsalId = (int)$array['RehearsalId'];
		$this->ActInfoId = (int)$array['ActInfoId'];
		$this->GroupBio = (string)$array['ItemId'];
		if (array_key_exists('isGroup', $array))
		  $this->isGroup = (bool)$array['isGroup'];
		else 
		  $this->isGroup = false;
		
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
		
		if (!is_bool($this->isGroup))
			return display_error("Act isGroup state is not in a valid format.");	
		if (strlen($this->GroupBio) > 500)
			return display_error("Group Bio is too long.  It must be 500 characters or less.");	
		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save Act to DB:  not logged in.");	
				
		
		// See if this is an insert or update.
		
		$this->ActId = mysql_real_escape_string($this->ActId);
		$sql = "select * from Acts where ActId = '$this->ActId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update Acts set ";
		}	
		else
		{
			$sql = "insert into Acts set ";
		}

		$sql .= build_sql_string('BidId', $this->BidId, false);
		$sql .= build_sql_string('ShowId', $this->ShowId);
		$sql .= build_sql_string('RehearsalId', $this->RehearsalId);
		$sql .= build_sql_string('ActInfoId', $this->ActInfoId);
		$sql .= build_sql_string('GroupBio', $this->GroupBio);
		$sql .= build_sql_string('isGroup', $this->isGroup);
			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where ActId = '%s'", $this->ActId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into Acts failed");
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this ticket item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		$sql = sprintf("delete from Acts where ActId = '%s'", 
			mysql_real_escape_string($this->ActId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		$this->remove_performer();
	}
}

