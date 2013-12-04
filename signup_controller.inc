<?php
include_once ("gbe_run.inc");

define ('SIGNUP_FAIL', 0);
define ('SIGNUP_OK', 1);
define ('SIGNUP_CONFIRM', 2);



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

  $UserId = $_SESSION[SESSION_LOGIN_USER_ID];
  
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (SIGNUP_FAIL);

  // Extract the EventId and RunId and make sure the user isn't already
  // signed up for this game

  $EventId = trim ($_REQUEST['EventId']);
  $RunId = trim ($_REQUEST['RunId']);
  $withdraw_from_conflicts = isset ($_REQUEST['Confirmed']);

  $status =  signup_user_for_run($EventId, $UserId, $withdraw_from_conflicts, $RunId);
}

/* signup_user_for_run
 *
 *  Performs the check of a given user's availability for a run of an event
 *    timeframe, and signs them up if all is well.  If this is a tech rehearsal, the user 
 *    is also signedup for the Call and Show.
 *  $UserId = the user being signed up
 *  $EventId = the event
 *  $RunId = the run, if set to zero, it's assumed that there will only be 1 run of 
 *     the given event.  The signup will fail otherwise.
 *  $withdraw_from_conflicts = the day of the game
 *  Returns:  Status
 *    SIGNUP_FAIL
 *    SIGNUP_OK
 */

function signup_user_for_run($EventId, $UserId, $withdraw_from_conflicts, $RunId=0)
{
  // Get the information about this run
  $RunClause = "";
  if ($RunId > 0)
    $RunClause = " Runs.RunId=" . $RunId." AND";
  else 
    $RunClause = " Events.EventId=" . $EventId." AND";

  $sql = 'SELECT Events.Title, Events.Hours, Events.IsOps, Events.IsConSuite,';
  $sql .= ' Events.MaxPlayersNeutral, Events.CanPlayConcurrently, Events.GameType,';
  $sql .= ' Runs.StartHour, Runs.Day, Runs.RunId, Runs.ShowId';
  $sql .= ' FROM Runs, Events';
  $sql .= ' WHERE'.$RunClause;
  $sql .= ' Events.EventId=Runs.EventId';

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
  else if (mysql_num_rows ($result) > 1)
  {
    display_error ("More than one run found for EventId $EventId");
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
  $RunId = $row->RunId;
  $GameType = $row->GameType;
  $ShowId = $row->ShowId;

  // I could differentiate this by sex, but not now

  //  $game_max = $row->MaxPlayersMale +
  //              $row->MaxPlayersFemale +
  //              $row->MaxPlayersNeutral;

  $game_start_hour = $row->StartHour;
  $game_end_hour = $row->StartHour + $row->Hours;
  $game_start_time = start_hour_to_am_pm ($game_start_hour);
  $game_end_time = start_hour_to_am_pm ($game_end_hour);

  // Is the user a GM for this game?

  $sql = 'SELECT GMs.GMId';
  $sql .= ' FROM GMs';
  $sql .= " WHERE GMs.EventId=$EventId";
  $sql .= '   AND GMs.UserId=' . $UserId;

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
  $Show = new Run();
  $Call = new Run();
 
  if ('N' == $can_play_game_concurrently)
  {
    $status = check_for_conflicts($UserId, $game_start_hour, $game_end_hour, $game_day, 
                        $waitlist_conflicts);
    if ($status != SIGNUP_OK)
      return $status;
    // when a user signs up for Tech Rehearsal, they must also be 
    // committed to Call time and Show time
    if ($GameType == "Tech Rehearsal")
    {
     
      $Show->load_from_ShowId($ShowId,"Show");
      $Call->load_from_ShowId($ShowId, "Call");

      if (strlen($Show->RunId) == 0 )
        return display_error("An error has occured please contact volunteer registration.");

      $status = check_for_conflicts($UserId, $Show->StartHour, $Show->StartHour + $Show->Event->Hours, 
                    $Show->Day, $waitlist_conflicts, $Show->RunId);
      if ($status != SIGNUP_OK)
      { 
         echo "There is a schedule conflict at the time of the show.  Please fix this conflict and try again."; 
         return $status;
      }
      if (strlen($Call->RunId) != 0)
        $status = check_for_conflicts($UserId, $Call->StartHour, $Call->StartHour + $Call->Event->Hours, 
                    $Call->Day, $waitlist_conflicts, $Call->RunId);
      if ($status != SIGNUP_OK)
      { 
         echo "There is a schedule conflict at the time of call for this show.  Please fix this conflict and try again."; 
         return $status;
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
  if ($GameType == "Tech Rehearsal")
  {
    $ShowRuns = array($Show, $Call);
    book_user($ShowRuns, $UserId, FALSE);
  }

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

/* check_for_conflicts
 *
 *  Performs the check of a given user's availability within a proposed 
 *    timeframe.  
 *  $UserId = the user to compare conflicts for
 *  $game_start_hour = the start time of the booking
 *  $game_end_hour = the end time of the booking
 *  $game_day = the day of the game
 *  $waitlist_conflicts - an array of any conflict with events the user is waitlisted in.
 *     pass in as an array.
 *  $RunId (optional) - the RunId of the run described by game_start_hour and game_end_hour
 *      if the run had previously been scheduled and is being updated.
 *  $keepGoing (optional, default is FALSE) - keep on checking for conflicts, don't 
 *      stop on the first one - useful for cases where this is a warning, not a stop
 *  Returns:
 *    SIGNUP_FAIL = 0
 *    SIGNUP_OK = 1
 */
 
function check_for_conflicts($UserId, $game_start_hour, $game_end_hour, $game_day, 
					&$waitlist_conflicts, $RunId=0, $keepGoing=FALSE)
{
  $status = SIGNUP_OK;
  
  // if a run has been provided - otherwise, assume this is a new booking 
  // this keeps the system from raising a conflict when a run is being rescheduled
  // or otherwise edited.
  $RunClause = "";
  if ($RunId > 0 )
    $RunClause = " AND Runs.RunId!=".$RunId;
    
  // Get the list of event runs the user is already registered for which may
  // conflict with this one
  $sql = 'SELECT Events.Title, Events.Hours,';
  $sql .= '      Runs.StartHour, Runs.EventId,';
  $sql .= '      Signup.SignupId, Signup.State, Runs.Day';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= ' WHERE Signup.UserId=' . $UserId;
  $sql .= '  AND Runs.RunId=Signup.RunId';
  $sql .= '  AND Events.EventId=Runs.EventId';
  $sql .= "  AND Events.CanPlayConcurrently='N'";
  $sql .= '  AND Signup.State!="Withdrawn"';
  $sql .= "  AND Runs.Day='$game_day'";
  $sql .= "  AND Runs.StartHour<$game_end_hour";
  $sql .= $RunClause;

 //    echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Cannot query database for conflicting events', $sql);
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
        $row_start_time = start_hour_to_am_pm ($row_start_hour);
        $row_end_time = start_hour_to_am_pm ($row_end_hour);
        $waitlist_conflicts[$row->SignupId] = $row->Title .
 	                                 " ($row_start_time - $row_end_time)";
      }
      else
      {
	    $error = sprintf ("There is an existing commitment for " .
			    '<A HREF=Schedule.php?action=%d&EventId=%d>' .
			    '<I>%s</I></A> at %s, %s which conflicts with this time slot',
			    SCHEDULE_SHOW_GAME, 
			    $row->EventId,
			    $row->Title, start_hour_to_am_pm ($row_start_hour), $row->Day);
	    display_error ($error);
	    $status = SIGNUP_FAIL;
	    if (!$keepGoing)
	      return $status;
	  }
    }
  }

  // Get the list of event runs the user is running as a teacher, panelist, coordinator
  // and so forth.  The only exception is true Ops events, where the "coordinator"
  // shouldn't have to be booked the whole time (for example, head of reg will not be 
  // working reg desk every single hour.

  $sql = 'SELECT Events.Title, Events.Hours,';
  $sql .= '      Runs.StartHour, Runs.EventId,';
  $sql .= '      GMs.GMId, Runs.Day';
  $sql .= ' FROM GMs, Runs, Events';
  $sql .= ' WHERE GMs.UserId=' . $UserId;
  $sql .= '  AND Events.EventId=GMs.EventId';
  $sql .= '  AND Events.EventId=Runs.EventId';
  $sql .= "  AND Events.CanPlayConcurrently='N'";
  $sql .= "  AND Runs.Day='$game_day'";
  $sql .= "  AND Runs.StartHour<$game_end_hour";
  $sql .= $RunClause;
  $sql .= " ORDER BY Runs.Day, Runs.StartHour";
  
  //    echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Cannot query database for conflicting events',
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
    echo " row_start_hour: $row_start_hour<br>\n";
    echo " row_end_hour: $row_end_hour<p>\n";
*/

    if (($row_end_hour > $game_start_hour) && ($row_start_hour < $game_end_hour))
    {
      $error = sprintf ("There is a conflict with presenting for the following event " .
			    '<A HREF=Schedule.php?action=%d&EventId=%d>' .
			    '<I>%s</I></A> at %s, %s which conflicts with this time slot',
			    SCHEDULE_SHOW_GAME,  
			    $row->EventId,
			    $row->Title,
			    start_hour_to_am_pm($row_start_hour), $row->Day);
      display_error ($error);
      $status = SIGNUP_FAIL;
	  if (!$keepGoing)
	      return $status;
    }
  }
  
  return $status;
}

/*
 * signup_user_for_game
 *
 * Signup the logged in user for the specified run or a game
 */

function signup_user_for_game ($RunId, $EventId, $Title,
			       $user_is_gm,
			       $max_male, $max_female, $max_neutral,
			       $waitlist_conflicts, $withdraw_from_conflicts,
			       &$signup_result)
{
  // Get the signup counts for the run
  $confirmed = array ();
  $waitlisted = array ();

  if (! get_counts_for_run ($RunId, $confirmed, $waitlisted))
    return SIGNUP_FAIL;

  if ($user_is_gm)
  {
    $counts_towards_total = 'N';
    $game_full = false;
  }
  else
  {
    $counts_towards_total = 'Y';
    $game_full = game_full ($full_msg, $_SESSION[SESSION_LOGIN_USER_GENDER],
			    $confirmed['Male'], $confirmed['Female'],
			    $max_male, $max_female, $max_neutral, $confirmed['']);
  }

  if ($game_full)
    $state = 'Waitlisted';
  else
  {
    $state = 'Confirmed';
  }
  
  // If the array of conflicting games the user is waitlisted on is not
  // empty, display a form asking the user to confirm the he wants us to
  // drop him from the waitlisted games

  //    echo 'Waitlist_conflicts: ' . count($waitlist_conflicts) . "<P>\n";

  if ((0 != count ($waitlist_conflicts)) && (! $withdraw_from_conflicts))
    return confirm_signup ($waitlist_conflicts, $Title, $EventId, $RunId);

  //  echo "State: $state<P>\n";

  $sql = 'INSERT INTO Signup SET UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= build_sql_string ('RunId', $RunId);
  $sql .= build_sql_string ('State', $state);
  $sql .= build_sql_string ('Counted', $counts_towards_total);
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);

  //  echo $sql . "<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Failed to signup for event');
    return SIGNUP_FAIL;
  }

  // If we've gotten this far, and there are conflicting games that the user
  // is waitlisted on, then he's confirmed that we're supposed to withdraw
  // him from them.  So do it.

  if (0 != count ($waitlist_conflicts))
  {
    foreach ($waitlist_conflicts as $k => $v)
      withdraw_from_game ($k);
  }

  if ($game_full)
    $signup_result = 'been wait listed for';
  else
    $signup_result = 'signed up for';

  return SIGNUP_OK;
}

/*
 * confirm_signup
 *
 * Ask the user to confirm that he wants to signup for a game when he's
 * got conflicted waitlists
 */

function confirm_signup ($conflict_array, $Title, $EventId, $RunId)
{
  echo "You are currently waitlisted for the following games which conflict\n";
  echo "with <I>$Title</I>:\n";

  echo "<UL>\n";
  foreach ($conflict_array as $k=>$v)
    echo "<LI>$v\n";
  echo "</UL>\n";

  echo "You cannot be waitlisted for any game which conflicts with a game\n";
  echo "you are signed up for.<P>\n";

  echo "Click the button to confirm that you want to withdraw from the\n";
  echo "waitlist for these games, or choose a different game to signup for.\n";

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", SCHEDULE_SIGNUP);
  echo "<INPUT TYPE=HIDDEN NAME=RunId VALUE=$RunId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=EventId VALUE=$EventId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=Confirmed VALUE=1>\n";
  echo "<CENTER>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=\"Withdraw from conflicts and signup for game\">\n";
  echo "</CENTER>\n";
  echo "</FORM>\n";

  return SIGNUP_CONFIRM;
}

/*
 * get_counts_for_run
 */

function get_counts_for_run ($RunId, &$confirmed, &$waitlisted)
{
  // Initialize the starting contents
  $confirmed[''] = 0;
  $waitlisted[''] = 0;

  // Start by getting the count of confirmed users

  $sql = 'SELECT COUNT(UserId) AS Count';
  $sql .= ' FROM Signup';
  $sql .= " WHERE RunId=$RunId";
  $sql .= "   AND State='Confirmed'";
  $sql .= "   AND Counted='Y'";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Attempt to count confirmed signups for RunId $RunId Failed', $sql);

  while ($row = mysql_fetch_object ($result))
    $confirmed[''] = $row->Count;

  // Now count the number of waitlisted users

  $sql = 'SELECT COUNT(UserId) AS Count';
  $sql .= ' FROM Signup';
  $sql .= " WHERE RunId=$RunId";
  $sql .= "   AND State='Waitlisted'";
  $sql .= "   AND Signup.Counted='Y'";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Attempt to count waitlisted signups for RunId $RunId Failed', $sql);

  while ($row = mysql_fetch_object ($result))
    $waitlisted[''] = $row->Count;

  mysql_free_result ($result);

  $confirmed['Total'] = $confirmed[''];
  $waitlisted['Total'] = $waitlisted[''];

  return true;
}

/*
 * game_full
 *
 * Checks whether the event is full. 
 * This call can be simplified since we're no longer tracking slots by gender.
 */

function game_full (&$msg, $gender, $male, $female,
		    $max_male, $max_female, $max_neutral, $neutralcount=0)
{
  // If we're above total game max, then we're full

  if ($neutralcount >= $max_neutral)
  {
    //    echo "<!-- Above game total -->\n";
    $msg = 'This opportunity is full';
    return TRUE;
  }
  else 
       return FALSE;
}

/*
 * act_rehearsal_full
 *
 * Checks whether the rehearsal slot is full. 
 * This call is different than a regular event in that we're counting the number of acts,
 *  not the number of participant signups.
 */

function act_rehearsal_full ($RunId, $max)
{
  // How many acts are signed up?
  $sql = 'SELECT COUNT(ActId) AS Count';
  $sql .= ' FROM Acts';
  $sql .= " WHERE RehearsalId=$RunId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Attempt to count confirmed signups for RunId $RunId Failed', $sql);

  while ($row = mysql_fetch_object ($result))
    $act_count = $row->Count;
  
  
  if ($act_count >= $max)
    return TRUE;
  else 
    return FALSE;
}