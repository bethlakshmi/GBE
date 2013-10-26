<?php

/* gbe_acts.inc - defines the baselines for act management, post acceptance.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */
 
/* Class Act 
 * 
 * This class represents all that is needed to describe 1 act.  Currently, 
 * it defines a post-bid, post-acceptance act, which assumes the act is:
 *   - assigned to a show
 *   - has at least one performer (the submitter)
 *  In the GBE context, acts are performed at shows (a special type of event)
 *  acts are also part of exactly 1 act rehearsal, and perfomers are granted
 *  one comp ticket each and expected to be at call.
 * 
 */
 
class Act
{
	// These public properties represent the various columns in the table.
	
	public $ActId;
	public $BidId;
	public $ShowId;
	public $RehearsalId;
	public $ActInfoId;
	public $GroupBio;
	public $GroupName;
	public $isGroup;
	
	/* function get_Title
	 * 
	 * Get the title the performer used in bidding this act.
	 *
	 * Returns: the title as a string or a null value if not found.
	 */
	function get_Title()
	{
	  $Title = NULL;
	  
	  // Check for user
	  $sql = sprintf("select Title from Bids where BidId = '%s'", 
			$this->BidId);
		 
	  $result = mysql_query($sql);
		
	  if (!$result)
		display_mysql_error ('Cannot execute query', $sql);
	  else if (mysql_num_rows($result) > 1)
	    display_error("Error:  multiple Bids found with same ID");	
	  else if (1 != mysql_num_rows($result))
	    display_error("Bid not found:  $UserId");
	  else {
	    $row = mysql_fetch_object($result);

	    $Title = $row->Title;
      }
      
      return $Title;
      
    }

	/* function add_performer
	 * 
	 * Used to add a perfomer link to this act.  Performer must already
	 * be a user and have a user id. 
	 *
	 * $UserId:  the user Id to be loaded.
	 * Returns: nothing.
	 */
	function add_performer($UserId)
	{
	    // Check for user
		$sql = sprintf("select * from Users where UserId = '%s'", 
			mysql_real_escape_string($UserId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple users  found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("User not found:  $UserId");
		
		// check if this is an add or an update
		$sql = sprintf("select * from GMs where UserId = '%s' and EventId = %s and Role=\"performer\"", 
			mysql_real_escape_string($UserId), $this->ActId);
		 
		$result = mysql_query($sql);
		
		$action = "INSERT INTO ";
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple users  found with same ID in show assignment list");	
		if (1 == mysql_num_rows($result))
			$action = "UPDATE ";

		// Add the lead GM as a GM for the game

        $sql = $action."GMs SET EventId=$this->ActId, UserId=$UserId,";
        $sql .= '  Submitter="Y", ReceiveConEMail="Y", role="performer",';
        $sql .= '  UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where EventId = '%s' and Role=\"performer\"", $this->ActId);

        //echo $sql;

        $result = mysql_query ($sql);
        if (! $result)
          return display_mysql_error ("Perfomer insertion failed");

	}
	
	/* function remove_performer
	 * 
	 * Used to remove a perfomer link from this act.  Performer must already
	 * be a user and have a user id. 
	 *
	 * $UserId:  the user Id to be removed.  If left blank, it implies ALL perfomers 
	 * Returns: nothing.
	 */
	function remove_performer($UserId = NULL)
	{

      $sql = "DELETE FROM GMs WHERE EventId=$this->ActId and role=\"performer\"";
      if ($UserId != NULL)
        $sql .= " and UserId = $UserId ";
        
      $result = mysql_query ($sql);
      if (! $result)
         return display_mysql_error ("Deletion from GMs failed for Event $EventId");
    }


	/* function load_from_actid
	 * 
	 * Used to load a particular row by primary key from the database. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_actid($ActId)
	{
		$sql = sprintf("select * from Acts where ActId = '%s'", 
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

	/* function load_from_bidid
	 * 
	 * Used to load a particular row by BidId from the database. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_bidid($BidId)
	{
		$sql = sprintf("select * from Acts where BidId = '%s'", 
			mysql_real_escape_string($BidId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Act not found:  $BidId");
		
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
		$this->GroupName = (string)$row->GroupName;	
		$this->GroupBio = (int)$row->GroupBio;	
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
		if (array_key_exists('isGroup', $array))
		{
		  $this->isGroup = (bool)$array['isGroup'];
  		  $this->GroupName = (string)$array['GroupName'];
		  $this->GroupBio = (int)$array['GroupBio'];
		}
		else 
		  $this->isGroup = false;
		
		return true;
	}
	/* function get_show_name
	 * 
	 * Retrieve the title of the show in which this act has been cast	 
	 *
	 * Returns: a string for the show's title or NULL if not found
	 */
	function get_show_name()
	{	
	    $title = NULL;
	  	$sql = "select Title from Events where EventId = '$this->ShowId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		$row = mysql_fetch_object($result);
		return (string)$row->Title;
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
		if (strlen($this->GroupName) > 50)
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
		if ( $this->isGroup )
		{
  		  $sql .= build_sql_string('GroupName', $this->GroupName);
		  $sql .= build_sql_string('GroupBio', $this->GroupBio);
		}
		$sql .= build_sql_string('isGroup', $this->isGroup);
			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where ActId = '%s'", $this->ActId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into Acts failed");

		if ($this->ActId < 1)
          $this->ActId = mysql_insert_id();
        echo "Just saved or made ActId: ".$this->ActId."<br>\n";
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

/* function get_show_list
 *
 * Used to obtain an array of the current shows
 *
 * $shows - an array of Events in the DB which are of type "Show". The array key is the 
 * event ID, and the value is another array containing the event title.
 * Returns: nothing.
 */
function get_show_list(&$shows)
{
	$sql = sprintf("SELECT * FROM Events WHERE GameType = 'Show' ");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no Events to list at this time. Please add.");

	$shows = array();
	while ($row = mysql_fetch_object($result))
	{
		$show = array();
		$show['Title'] = $row->Title;
		$show['ShortBlurb'] = $row->ShortBlurb;
		$show['Description'] = $row->Description;
		$shows[(int)$row->EventId] = $show;
	}
}

		



/* function set_act_in_show
 * 
 * Function creates a new act in a given show.  The act is based upon the Bid
 * that a user has submitted to the show committee
 *
 * $BidId - unique ID number for the original Bid for this act
 * $ShowId - unique ID number for the Event that represents the show
 * $isGroup - marker for whether this is a group or solo - defaults to solo
 * Returns: nothing.
 */
function set_act_in_show($BidId, $ShowId, $isGroup = false)
{
	$act = new Act();
	$act->BidId = $BidId;
	$act->ShowId = $ShowId;
		
	$act->save_to_db();
}


/* function get_preferred_shows
 * 
 * Function retrieves the preferred shows from the bid information
 *
 * $BidId - unique ID number for the original Bid for this act
 * $bid_pref_slots - storage container for preferred slots
 *   key is DaySlot, values are boolean preferred or not
 * Returns: bid_pref_slots as reference.
 */
function get_preferred_shows($BidId, &$bid_pref_slots)
{

  //Get the Bid Preferred Slot Info
  $sql = 'SELECT * FROM BidTimes WHERE BidId=' . $BidId;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for BidId $BidId failed");

  while ($row = mysql_fetch_assoc($result)) {
    $bid_pref_slots[$row['Day'].$row['Slot']] = $row['Pref'];
  }
}
?>

