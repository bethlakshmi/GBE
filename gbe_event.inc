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

/*
 * process_signup_request
 *
 * The user has requested to signup for this game.  Make sure there are no
 * conflicts, and that the game isn't full, then add a record to the Signup
 * table.
 */

function process_signup_request ()
{
  // Make sure the user is logged in

  if (! is_logged_in ())
  {
    display_error ('You must be <a href="index.php">logged in</a> to signup for any event');
    return SIGNUP_FAIL;
  }

  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (SIGNUP_FAIL);

  // Extract the EventId and RunId and make sure the user isn't already
  // signed up for this game

  $EventId = trim ($_REQUEST['EventId']);
  $RunId = trim ($_REQUEST['RunId']);
  $withdraw_from_conflicts = isset ($_REQUEST['Confirmed']);

  // Get the information about this run

  $sql = 'SELECT Events.Title, Events.Hours, Events.IsOps, Events.IsConSuite,';
  $sql .= ' Events.MaxPlayersNeutral, Events.CanPlayConcurrently,';
  $sql .= ' Runs.StartHour, Runs.Day';
  $sql .= ' FROM Runs, Events';
  $sql .= ' WHERE Runs.RunId=' . $RunId;
  $sql .= '  AND Events.EventId=Runs.EventId';

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Cannot query database for run information');
    return SIGNUP_FAIL;
  }

  // This should have matched exactly one row

  if (0 == mysql_num_rows ($result))
  {
    display_error ("Failed to find event information for RunId $RunId");
    return SIGNUP_FAIL;
  }

  // Extract the game information

  $row = mysql_fetch_object ($result);

  $game_title = $row->Title;
  $game_hours = $row->Hours;
  $game_day = $row->Day;
  $can_play_game_concurrently = $row->CanPlayConcurrently;
  $is_ops = $row->IsOps == 'Y';
  $is_consuite = $row->IsConSuite == 'Y';
  $max_neutral = $row->MaxPlayersNeutral;

  // I could differentiate this by sex, but not now

  //  $game_max = $row->MaxPlayersMale +
  //              $row->MaxPlayersFemale +
  //              $row->MaxPlayersNeutral;

  $game_start_hour = $row->StartHour;
  $game_end_hour = $row->StartHour + $row->Hours;
  $game_start_time = start_hour_to_12_hour ($game_start_hour);
  $game_end_time = start_hour_to_12_hour ($game_end_hour);

  // Is the user a GM for this game?

  $sql = 'SELECT GMs.GMId';
  $sql .= ' FROM GMs';
  $sql .= " WHERE GMs.EventId=$EventId";
  $sql .= '   AND GMs.UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Cannot query database for GM status', $sql);
    return SIGNUP_FAIL;
  }

  $user_is_gm = mysql_num_rows ($result) > 0;

  // Make sure that the user isn't trying to violate the signup limits

  if (! $user_is_gm)
  {
    $signups_allowed = con_signups_allowed ();
    switch ($signups_allowed)
    {
      case 0:
	    display_error ('Signups are not allowed at this time');
	    return SIGNUP_FAIL;

      case UNLIMITED_SIGNUPS:  // No limits
	    break;

      default:

    }
  }

  $waitlist_conflicts = array ();

  if ('N' == $can_play_game_concurrently)
  {
    // Get the list of event runs the user is already registered for which may
    // conflict with this one

    $sql = 'SELECT Events.Title, Events.Hours,';
    $sql .= '      Runs.StartHour, Runs.EventId,';
    $sql .= '      Signup.SignupId, Signup.State';
    $sql .= ' FROM Signup, Runs, Events';
    $sql .= ' WHERE Signup.UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= '  AND Runs.RunId=Signup.RunId';
    $sql .= '  AND Events.EventId=Runs.EventId';
    $sql .= "  AND Events.CanPlayConcurrently='N'";
    $sql .= '  AND Signup.State!="Withdrawn"';
    $sql .= "  AND Runs.Day='$game_day'";
    $sql .= "  AND Runs.StartHour<$game_end_hour";

    //    echo "$sql<p>\n";

    $result = mysql_query ($sql);
    if (! $result)
    {
      display_mysql_error ('Cannot query database for conflicting games',
			   $sql);
      return SIGNUP_FAIL;
    }

    // Scan through the returned list looking for a conflict

/*
    echo "$game_title<br>\n";
    echo "   Start hour: $game_start_hour ($game_start_time)<br>\n";
    echo "   End hour: $game_end_hour ($game_end_time)<p>\n";

    echo 'Rows: ' . mysql_num_rows ($result) . "<p>\n";
*/

    while ($row = mysql_fetch_object ($result))
    {
      $row_start_hour = $row->StartHour;
      $row_end_hour = $row_start_hour + $row->Hours;

      // If the game the user is already registered for runs into this one,
      // or if the game the user is already registered for starts during this
      // on, there's a conflict and the user cannot register for this game
/*
      echo "Checking <i>$row->Title</I><br>\n";
      echo "State: $row->State<br>\n";
      echo " row_start_hour: $row_start_hour<br>\n";
      echo " row_end_hour: $row_end_hour<p>\n";
*/

      if (($row_end_hour > $game_start_hour) &&
	  ($row_start_hour < $game_end_hour))
      {
	// If this is a confirmed game, tell the user that he has to withdraw
	// from the game before he can signup for this one

	if ('Waitlisted' == $row->State)
	{
	  $row_start_time = start_hour_to_12_hour ($row_start_hour);
	  $row_end_time = start_hour_to_12_hour ($row_end_hour);
	  $waitlist_conflicts[$row->SignupId] = $row->Title .
 	                                 " ($row_start_time - $row_end_time)";
	}
	else
	{
	  $error = sprintf ("You're already registered for " .
			    '<A HREF=Schedule.php?action=%d&EventId=%d>' .
			    '<I>%s</I></A> which conflicts with this time slot',
			    SCHEDULE_SHOW_GAME,
			    $row->EventId,
			    $row->Title);
	  display_error ($error);
	  return SIGNUP_FAIL;
	}
      }
    }
  }

  // We lock the Signup table to make sure that if there are two users trying
  // to get the last slot in a game, then only one will succeed.  A READ lock
  // allows clients that only read the table to continue, but will block
  // clients that attempt to write to the table

  $result = mysql_query ('LOCK TABLE Signup WRITE, Users READ, Runs Read, Events Read, GMs Read');
  if (! $result)
  {
    display_mysql_error ('Failed to lock the Signup table');
    return SIGNUP_FAIL;
  }

  // Make sure there's room in the game, and add a signup record if there is

  $signup_ok = signup_user_for_game ($RunId, $EventId, $game_title,
				     $user_is_gm,
				     $max_male, $max_female, $max_neutral,
				     $waitlist_conflicts,
				     $withdraw_from_conflicts,
				     $signup_result);

  // Unlock the Signup table so that other queries can access it

  $result = mysql_query ('UNLOCK TABLES');
  if (! $result)
  {
    display_mysql_error ('Failed to unlock the Signup table');
    return SIGNUP_FAIL;
  }

  if (SIGNUP_OK != $signup_ok)
    return $signup_ok;

  echo "You have $signup_result <I>$game_title</I> on $game_day, ";
  echo start_hour_to_12_hour ($game_start_hour) . ' - ';
  echo start_hour_to_12_hour ($game_end_hour);
  echo "<P>\n";

  // Notify any GMs who have requested notification of signups

  if ('signed up for' == $signup_result)
    $type = 'Signup';
  else
  {
    $type = 'Waitlist';

    echo "If you are at the head of the waitlist and a player withdraws\n";
    echo "from the game, you will automatically be signed up for this\n";
    echo "game<P>\n";
  }

  notify_gms ($EventId, $game_title, $game_day, $game_start_hour,
	      $signup_result, $type);

  return SIGNUP_OK;
}
