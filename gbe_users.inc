<?php
/* gbe_users.inc - defines classes and functions relevant to users and their bios.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */


/* Class Bio 
 * 
 * This class is a single bio for a single user or group.
 * There is an underlying assumption in this system that a user may 
 * do many things - teach, sit on panels, perform, be on staff, volunteer.
 * However, a group is expected to be only performing and only performing once
 */
 
class Bio
{

   // These public properties represent the various columns in the table.
    public $BioId;
    public $UserId;
    public $BioText;
    public $Title;
    public $ShowNickname;
    public $LastUpdated;
    public $Website;
    public $PhotoSource;


	/* function load_from_bioid
	 * 
	 * Used to load a single Bio from its primary key 
	 *
	 * $ItemId:  the Bio Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_bioid($BioId)
	{
		$sql = sprintf("select * from Bios where BioId = '%s'", 
			mysql_real_escape_string($BioId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple bios found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Bio not found:  $BioId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}

	/* function load_from_userid
	 * 
	 * Used to load a single Bio from its user id 
	 *
	 * $ItemId:  the User Id to be loaded.
	 * Returns: true if it worked, false if no user found.
	 */
	function load_from_userid($UserId)
	{
		$sql = sprintf("select * from Bios where UserId = '%s'", 
			mysql_real_escape_string($UserId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple events found with same ID");	
		if (1 != mysql_num_rows($result))
			return false;
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
		
		return true;
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
        $this->BioId = (int)$row->BioId;
        $this->UserId = (int)$row->UserId;
        $this->BioText = (string)$row->BioText;
        $this->Title = (string)$row->Title;
        $this->ShowNickname = (int)$row->ShowNickname;
        $this->LastUpdated = (string)$row->LastUpdated;
        $this->Website = (string)$row->Website;
        $this->PhotoSource = (string)$row->PhotoSource;
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *  NOTE:  Using StaffTitle instead of Title to avoid overload during bid approval.
	 *
	 * $array: an array with entries for each of the properties of the Event
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
        $this->BioId = (int)$array['BioId'];
		if (array_key_exists('UserId', $array))
          $this->UserId = (int)$array['UserId'];
        $this->BioText = (string)$array['BioText'];
		if (array_key_exists('StaffTitle', $array))
          $this->Title = (string)$array['StaffTitle'];
		if (array_key_exists('ShowNickname', $array))
          $this->ShowNickname = (int)$array['ShowNickname'];
		if (array_key_exists('Website', $array))
          $this->Website = (string)$array['Website'];
		if (array_key_exists('PhotoSource', $array))
          $this->PhotoSource = (string)$array['PhotoSource'];		
          
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
		

		if (strlen($this->Website) > 128)
			return display_error("Website is too long.  It must be 128 characters or less.");	
		if (strlen($this->PhotoSource) > 128)
			return display_error("PhotoSource is too long.  It must be 128 characters or less.");	

		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save Bio to DB:  not logged in.");	
				
				
		$this->LastUpdated = date("Y-m-d H:i:s", time());	

		
		// See if this is an insert or update.
		
		$this->BioId = mysql_real_escape_string($this->BioId);
		$sql = "select * from Bios where BioId = '$this->BioId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple Bio rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update Bios set ";
		}	
		else
		{
			$sql = "insert into Bios set ";
		}

		$sql .= build_sql_string('BioId', $this->BioId, false);
		$sql .= build_sql_string('UserId', $this->UserId);
		$sql .= build_sql_string('BioText', $this->BioText);
		if (strlen($this->Title) > 0)
		  $sql .= build_sql_string('Title', $this->Title);
		$sql .= build_sql_string('ShowNickname', $this->ShowNickname);
		$sql .= build_sql_string('LastUpdated', $this->LastUpdated);
		$sql .= build_sql_string('Website', $this->Website);
		$sql .= build_sql_string('PhotoSource', $this->PhotoSource);
			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where BioId = '%s'", $this->BioId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into Bios failed");

		if ($this->BioId < 1)
			$this->BioId = mysql_insert_id();
		echo "Just saved or made BioId: ".$this->BioId."<br>\n";
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
		$sql = sprintf("delete from Bios where BioId = '%s'", 
			mysql_real_escape_string($this->BioId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		
	}
}

/* function get_acts_for_user
 * 
 * Function retrieves the accepted acts for a given user.  Act must
 * be cast in a show to show up here.
 *
 * $User - unique ID number for the user, primary key of user table
 * $act_list - the list of accepted acts that this user is in.
 * Returns: act_list as reference.
 */
function get_acts_for_user($UserId, &$act_list)
{

	//Get the Act Ids from GM table, join with act info on Acts table
	$sql = 'SELECT GMs.EventId, Acts.ActId, Acts.BidId, Acts.ShowId, Acts.RehearsalId,';
	$sql .=' Acts.ActInfoId, Acts.GroupBio, Acts.isGroup FROM GMs, Acts WHERE UserId=' . $UserId;
	$sql .= ' and Role = "performer" and GMs.EventId = Acts.ActId';
	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ("Query for UserId $UserId failed");

	$n=0;
	while ($row = mysql_fetch_object($result)) {
		$act = new Act();
		$act->convert_from_sql_row($row);
		$act_list[$n] = $act;
		$n++;

	}
}

/* function get_conf_items_for_user
 * 
 * Function retrieves the accepted conference items for a given user.  
 * Conference item must be accepted and user must be listed as teacher or
 * moderator.
 *
 * $User - unique ID number for the user, primary key of user table
 * $event_list - the list of accepted items that this user can edit.
 * Returns: event_list as reference.
 */
function get_conf_items_for_user($UserId, &$event_list)
{
	//Get the Event Ids from GM table
	$sql = 'SELECT EventId FROM GMs WHERE UserId=' . $UserId;
	$sql .= ' and (Role = "moderator" or Role = "teacher")';
	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ("Query for UserId $UserId failed");

	$n=0;
	while ($row = mysql_fetch_assoc($result)) {
		$event_list[$n] = $row['EventId'];
		$n++;
	}
}

/*
 * user_is_gm_for_game
 *
 * Returns true if the user is a GM for the specified game.
 */

function user_is_gm_for_game ($UserId, $EventId)
{
	// If the user isn't logged in, then they're not a GM, are they?

	if (0 == $UserId)
		return false;

	// Query the database to see if the user is GM

	$sql = "SELECT GMId FROM GMs WHERE UserId=$UserId AND EventId=$EventId";
	$sql .= '  LIMIT 1';

	//	echo "Query: $sql<p>\n";

	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ('Query for GMs failed');

	$num = mysql_num_rows ($result);
	mysql_free_result ($result);

	return $num != 0;
}

/*
 * user_is_moderator
 *
 * Returns true if the user is a moderator for the specified panel.
 */

function user_is_moderator ($UserId, $EventId)
{
	// If the user isn't logged in, then they're not a GM, are they?

	if (0 == $UserId)
		return false;

	// Query the database to see if the user is GM

	$sql = "SELECT GMId FROM GMs WHERE UserId=$UserId AND EventId=$EventId";
	$sql .= ' and Role="moderator" LIMIT 1';

	//	echo "Query: $sql<p>\n";

	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ('Query for moderator failed');

	$num = mysql_num_rows ($result);
	mysql_free_result ($result);

	return $num != 0;
}

/*
 * user_is_teacher
 *
 * Returns true if the user is a teacher for the specified class.
 */

function user_is_teacher ($UserId, $EventId)
{
	// If the user isn't logged in, then they're not a GM, are they?

	if (0 == $UserId)
		return false;

	// Query the database to see if the user is GM

	$sql = "SELECT GMId FROM GMs WHERE UserId=$UserId AND EventId=$EventId";
	$sql .= ' and Role="teacher" LIMIT 1';

	//	echo "Query: $sql<p>\n";

	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ('Query for teacher failed');

	$num = mysql_num_rows ($result);
	mysql_free_result ($result);

	return $num != 0;
}

/*
 * user_is_performer_for_show
 *
 * Returns true if the user is a performer for the specified show.
 */

function user_is_performer_for_show ($UserId, $ShowId)
{

	if (0 == $UserId)
		return false;

	// Query the database to see if the user is GM

	$sql = "SELECT GMs.GMId FROM GMs, Acts ";
	$sql .= "WHERE GMs.UserId=$UserId AND Acts.ShowId=$ShowId AND Acts.ActId=GMs.EventId";
	$sql .= ' and GMs.Role="performer" LIMIT 1';

	//	echo "Query: $sql<p>\n";

	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ('Query for performer failed');

	$num = mysql_num_rows ($result);
	mysql_free_result ($result);

	return $num != 0;
}

?>
