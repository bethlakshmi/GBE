<?php

include ("intercon_schedule.inc");
include ("pcsg.inc");
include ("gbe_ticketing.inc");
include ("gbe_brownpaper.inc");
include ("gbe_users.inc");
include ("gbe_event.inc");
include ("gbe_run.inc");
include ("WhosWho.inc");
include ("gbe_schedule.inc");
include_once ("signup_controller.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = SCHEDULE_SHOW;

// echo "Action: $action\n";

switch ($action)
{
  case SCHEDULE_SHOW:
    $type = "";
    if (array_key_exists ('displayType', $_REQUEST))
      $type = $_REQUEST['displayType'];

    if (can_show_schedule ())
      show_away_schedule_form ($type);
    else
      display_access_error ();
    break;

  case SCHEDULE_SHOW_GAME:
    show_game ();
    break;

  case SCHEDULE_SIGNUP:
    switch (process_signup_request ())
    {
      case SIGNUP_FAIL:
	show_game();
	break;

      case SIGNUP_OK:
	show_away_schedule_form ();
	break;

      case SIGNUP_CONFIRM:
	break;
    }
    break;

  case PROCESS_ADD_GAME:
    if (! can_edit_game_info ())
    {
      display_access_error ();
      break;
    }

    if (! add_game ())
      display_game_form ();
    else
      show_game ();
    break;

  case EDIT_GAME:
    if (! can_edit_game_info ())
    {
      display_access_error ();
      break;
    }

    if (load_game_post_array ())
      display_game_form ();
    break;

  case LIST_GAMES:
    $type = "";
    if (array_key_exists ('type', $_REQUEST))
      $type = $_REQUEST['type'];

    list_games_alphabetically ($type);
    break;

  case SCHEDULE_SHOW_SIGNUPS:
    if (can_edit_game_info () || user_has_priv (PRIV_CON_COM) || user_has_priv(PRIV_SCHEDULING) )
      show_signups ();
    else
      display_access_error ();
    break;

  case SCHEDULE_SHOW_ALL_SIGNUPS:
    if (can_edit_game_info () || user_has_priv (PRIV_CON_COM) || user_has_priv(PRIV_SCHEDULING) )
      show_all_signups ();
    else
      display_access_error ();
    break;

  case SHOW_USER:
    if (can_edit_game_info ())
      display_user_information ();
    else
      display_access_error ();
    break;

  case SCHEDULE_UPDATE_SIGNUP:
    if (! can_edit_game_info ())
    {
      display_access_error ();
      break;
    }

    if (update_signup ())
      show_signups();
    else
      display_user_information ();
    break;

  case DISPLAY_GM_LIST:
    if (! can_edit_game_info ())
      display_access_error ();
    else
      display_gm_list ();
    break;

  case SCHEDULE_COMP_USER_FOR_EVENT:
    if (! can_edit_game_info())
      display_access_error();
    else
      comp_user_for_event();
    break;

  case ADD_GM:
    if (! can_edit_game_info ())
      display_access_error ();
    else
      select_user_as_gm ();
    break;

  case PROCESS_ADD_GM:
    if (! can_edit_game_info ())
      display_access_error ();
    else
    {
      if (! process_add_gm ())
	select_user_as_gm ();
      else
	display_gm_list ();
    }
    break;

  case EDIT_GM:
    if (! can_edit_game_info ())
      display_access_error ();
    else
      display_gm_information ();
    break;

  case SCHEDULE_UPDATE_GM:
    if (! can_edit_game_info ())
      display_access_error ();
    else
    {
      if (update_gm ())
	display_gm_list ();
      else
	display_gm_information ();
    }
    break;

  case WITHDRAW_FROM_GAME:
    if (! confirm_withdraw_from_game ())
      show_game();
    break;
      
  case WITHDRAW_FROM_GAME_CONFIRMED:
    if (! withdraw_from_game ())
      show_game();
    else
      show_away_schedule_form ();
    break;

  case SCHEDULE_AWAY_FORM:
    show_away_schedule_form ();
    break;

  case SCHEDULE_PROCESS_AWAY_FORM:
    process_away_form ();
    show_away_schedule_form ();
    break;

  default:
    display_error ("Unknown action code: $action");
}

// Add the postamble

html_end ();
/*
 * show_away_schedule_form
 *
 * Display the schedule with checkboxes to allow users to specify when they'll
 * be away
 */

function show_away_schedule_form ($type)
{  
  $signed_up_runs = array ();

  $logged_in = is_logged_in();

  if ($logged_in)
  {
    $result = get_signed_up_runs();
    if (! $result)
      return display_mysql_error ('Query for signup list failed');

    while ($row = mysql_fetch_object ($result))
    {
      // Note that the user is signed up for this run
      $signed_up_runs[$row->RunId] = $row->State;
    }
  }

  // Add the form boilerplate

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n",
	  SCHEDULE_PROCESS_AWAY_FORM);
  if ($type == "") {
    $type = "Events";
  }

  write_sched_selectors($type);
  schedule_day ('Fri', $signed_up_runs,  false, $type);
  schedule_day ('Sat', $signed_up_runs, false, $type);
  schedule_day ('Sun', $signed_up_runs,  false, $type);

  //

}


/*
 * Ask the database what runs the user is signed up for
 */

function get_signed_up_runs()
{
    $sql = 'SELECT Signup.RunId, Signup.State,';
    $sql .= ' Runs.Day, Runs.StartHour, Events.Hours';
    $sql .= ' FROM Signup, Runs, Events ';
    $sql .= ' WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= '  AND Runs.RunId=Signup.RunId';
    $sql .= '  AND Events.EventId=Runs.EventId';
    $sql .= '  AND Signup.State<>"Withdrawn"';
    return mysql_query ($sql);
}



	/**
	* Display an event.
	* $hour :
	* $row :
	* $dimensions :
	* $signed_up_runs : 
	* $signup_counts :
	*/
function display_event ($hour,	$row, $dimensions, $signed_up_runs, $signup_counts)
{
  $bgcolor = "#FFFFFF";
  $game_full = false;
  $males = $signup_counts["Male"];
  $females = $signup_counts["Female"];

  $game_max = $row->MaxPlayersNeutral;
  $game_full = $males >= $game_max;

  if (array_key_exists ($row->RunId, $signed_up_runs))
  {
    if ('Confirmed' == $signed_up_runs[$row->RunId])
      $bgcolor = get_bgcolor_hex ('Confirmed');
    elseif ('Waitlisted' == $signed_up_runs[$row->RunId])
      $bgcolor = get_bgcolor_hex ('Waitlisted');
  }
  elseif ($game_full)
    $bgcolor = get_bgcolor_hex ('Full');
  elseif ('Y' == $row->CanPlayConcurrently)
    $bgcolor = get_bgcolor_hex ('CanPlayConcurrently');
 

  // Add the game title (and run suffix) with a link to the game page

  $text = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d">',
		   SCHEDULE_SHOW_GAME,
		   $row->EventId,
		   $row->RunId);
  $text .= $row->Title;
  if ('' != $row->TitleSuffix)
    $text .= "<p>$row->TitleSuffix";
  $text .= '</a>';
  if ('' != $row->ScheduleNote)
    $text .= "<P>$row->ScheduleNote";
  if ('' != $row->Rooms)
    $text .= '<br>' . pretty_rooms($row->Rooms) . "\n";

  
  echo "<div class=\"class12\" style=\"".$dimensions->getCSS()."\">";
  write_centering_table($text, $bgcolor);
  echo "</div>\n";
}



function display_special_event($row, $dimensions, $bgcolor) {
  if ($row->DescLen > 0) {
	$text = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&' .
			 'RunId=%d">%s</a>',
			 SCHEDULE_SHOW_GAME,
			 $row->EventId,
			 $row->RunId,
			 $row->Title);
  } else {
    $text = $row->Title;
  }
  if ('' != $row->Rooms)
    $text .= '<br>' . pretty_rooms($row->Rooms) . "\n";
  

  echo "<div class=\"schedule_event\" style=\"".$dimensions->getCSS()."\">";
  write_centering_table($text, $bgcolor);
  echo "</div>\n";
}


/* 
 * get_signup_counts
 * Function specification here. 
 */

function get_signup_counts($run_ids) {
  $signup_counts = array();
  
  if (count($run_ids) == 0) {
	return $signup_counts;
  }
  
  foreach ($run_ids as $run_id) {
	$signup_counts[$run_id] = array(
		  "Male" => 0,
		  "Female" => 0,
		  "Uncounted" => 0,
		  "Waitlisted" => 0
		  );
  }
  
  $sql = 'SELECT RunId, State, Counted, Gender, COUNT(*) AS Count';
  $sql .= ' FROM Signup';
  $sql .= ' WHERE RunId IN ('.implode(",", $run_ids).')';
  $sql .= ' GROUP BY RunId, State, Gender, Counted';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for male signups failed');

  while ($row = mysql_fetch_object ($result)) {	
	if ($row->Counted == "Y") {
	  if ($row->State == "Waitlisted") {
		$signup_counts[$row->RunId]["Waitlisted"] += $row->Count;
	  } else if ($row->State == "Confirmed") {
		$signup_counts[$row->RunId][$row->Gender] += $row->Count;
	  }
	} else {
	  $signup_counts[$row->RunId]["Uncounted"] += $row->Count;
	}
  }
  
  return $signup_counts;
}


/**
 * Print the schedule for a given day, as a table
 */
function schedule_day ($day, $signed_up_runs, $show_counts, $type)
{

  $show_debug_info = user_has_priv (PRIV_SCHEDULING);
  if ($type == "") {
    $type = "Events";
  }

  if ($day == "Fri") {
     $today_start = FRI_MIN;
     $today_end = FRI_MAX;
  }

  if ($day == "Sat") {
     $today_start = SAT_MIN;
     $today_end = SAT_MAX;
  }
  if ($day == "Sun") {
     $today_start = SUN_MIN;
     $today_end = SUN_MAX;
  }

  // Get the day's events
  $bookings  = get_events_by_day($day);

  $runids = array();
  foreach ($bookings as $booking){
  	  $runids[]=$booking->RunId;
	  
  }
  $signup_counts = get_signup_counts($runids);


  $events_rooms = array("Theater"=>1,"Vendor Hall"=>1, "Crispus Attucks"=>1, 
		    "Haym Solomon"=>1, "Pool"=>1);		      

  $vol_rooms = array("Theater"=>1,"Vendor Hall"=>1, "Crispus Attucks"=>1, 
		    "Haym Solomon"=>1, "Registration"=>1);

  $conf_rooms = array("Thomas Paine A&B"=>1,"William Dawes A"=>1, "William Dawes B"=>1, 
		    "Molly Pitcher"=>1);

 
  $bookings = array();
  $rooms = array();
    get_general_bookings($bookings, $rooms, $day);
    set_status($bookings, $signup_counts, $signed_up_runs);

    $conf_array = build_events_table($day, $bookings, $events_rooms );
    write_events_table($conf_array, $events_rooms, "Events", $day, $today_start, $today_end, $type);

    get_volunteer_bookings($bookings, $rooms,  $day);
    set_status($bookings, $signup_counts, $signed_up_runs);
    $vol_array = build_events_table($day, $bookings, $vol_rooms);
   write_events_table($vol_array, $vol_rooms, "Volunteer", $day, $today_start,$today_end, $type);


    get_conference_bookings($bookings, $rooms, $day);
    set_status($bookings, $signup_counts, $signed_up_runs);
    $event_array = build_events_table($day, $bookings, $conf_rooms);
    write_events_table($event_array,$conf_rooms, "Conference", $day,  $today_start,$today_end, $type);
 // }

}

function write_sched_selectors($type) {
	 echo "<table class=\"sched_selectors\">\n";
	 echo "<tr class=\"day_selectors\">\n";
	 echo "<td id=\"Fri\" class=\"highlighted\">Friday</td><td id=\"Sat\">Saturday</td><td id=\"Sun\">Sunday</td></tr>\n";
	 echo "<tr class=\"event_type_selectors\">\n";
	 if ($type == "Events") {
	 echo "<td id=\"Events\" class=\"highlighted\">Events</td>\n";
	 } else {
	 echo "<td id=\"Events\">Events</td>\n";
	 }
	 if ($type == "Conference") {
	 echo "<td id=\"Conference\" class=\"highlighted\">Conference</td>\n";
	 }
	 else {echo "<td id=\"Conference\">Conference</td>\n";
	 }
	 if ($type== "Volunteer") {
	 echo "<td id=\"Volunteer\" class=\"highlighted\">Volunteer</td>\n";
//	 echo "<td id=\"Volunteer\" class=\"highlighted\"></td>\n";
	 }
	 else {
	 echo "<td id=\"Volunteer\">Volunteer</td>\n";
//	 echo "<td id=\"Volunteer\"></td>\n";
	 }
	 	 echo "</tr></table>\n\n";
}


function set_status($bookings, $signup_counts, $signed_up_runs){

  foreach ($bookings as $booking){
  
    if (array_key_exists ($booking->RunId, $signed_up_runs))
    {
      if ('Confirmed' == $signed_up_runs[$booking->RunId])
        $booking->Status = 'Confirmed';
      elseif ('Waitlisted' == $signed_up_runs[$booking->RunId])
        $booking->Status = 'Waitlisted';
    }
    elseif ($signup_counts[$booking->RunId]["Male"] >= $booking->Event->MaxPlayersNeutral )
    {	
  	 $booking->Status = "Full";
    }
    else $booking->Status = "Available";
    
  }
}


/*
 * display_players
 *
 * Helper function to display the player counts
 */

function display_players ($head, $min, $max, $preferred)
{
    echo "  <TR>\n";
    echo "    <TH>$head:</TH><TD>Min: $min / Max: $max</TD>\n";
    echo "  </TR>\n";
}



/*
 * players_will_fit
 *
 * Checks whether the players who are signedup will fit in the new maximums
 */

function players_will_fit ($male, $female,
			   $max_male, $max_female, $max_neutral)
{
  // If we're above total game max, then we're full

  if ($male + $female > $max_male + $max_female + $max_neutral)
    return false;

  else
    return true;
}

/*
 * get_user_status_for_run
 *
 */

function get_user_status_for_run ($RunId, &$SignupId, &$is_confirmed)
{
  $SignupId = -1;
  $is_confirmed = false;

  $sql = 'SELECT SignupId, State FROM Signup';
  $sql .= " WHERE State<>'Withdrawn'";
  $sql .= "  AND RunId=$RunId";
  $sql .= '  AND UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];

  $result = mysql_query ($sql);
  if (! $result)
  {
    echo '<!-- Attempt to check whether user signed up failed: ' .
      mysql_error ();
    return false;
  }

  $row = mysql_fetch_object ($result);
  if (! $row)
    return false;

  $SignupId = $row->SignupId;
  $is_confirmed = ('Confirmed' == $row->State);

  mysql_free_result ($result);

  return true;
}


/*
 * is_user_gm_for_game
 *
 * Check whether the user is a GM for a game
 */

function is_user_gm_for_game ($UserId, $EventId)
{
  $sql = 'SELECT GMId FROM GMs';
  $sql .= " WHERE UserId=$UserId";
  $sql .= "   AND EventId=$EventId AND GMs.Role != 'performer'";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query GM status', $sql);

  if (mysql_num_rows ($result) > 1)
    return display_mysql_error ('Matched more than 1 GM entry', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return false;
  else
    return true;
}


function display_comp_info($EventId)
{
  $sql = 'SELECT FirstName, LastName FROM Users';
  $sql .= "  WHERE CompEventId=$EventId";
  $sql .= "  ORDER BY LastName, FirstName";

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error("Query for list of comp'd users failed", $sql);

  $comp_count = mysql_num_rows($result);
  switch ($comp_count)
  {
    case 0:
      echo "<li class=\"alert\">";
      printf ('<a href="Schedule.php?action=%d&EventId=%d">2 comps available!</a>',
	      DISPLAY_GM_LIST, $EventId);
      echo "</li>\n";
      break;
      
    case 1:
      $row = mysql_fetch_object($result);
//      $name = trim("$row->FirstName $row->LastName");
      echo "<li class=\"alert\">";
      printf ('<a href="Schedule.php?action=%d&EventId=%d">1 comp available!</a>',
	      DISPLAY_GM_LIST, $EventId);
      echo "</li>\n";
      break;

    default:
/*      $row =  mysql_fetch_object($result);
      $name = trim("$row->FirstName $row->LastName");
      echo "<p>$name";
      $i = 1;
      while ($row = mysql_fetch_object($result))
      {
	$i++;
	if ($i < $comp_count)
	  echo ', ';
	else
	  echo ' and ';

	$name = trim("$row->FirstName $row->LastName");
	echo $name;
      }
      echo " are comped for this game.</p>\n"; */
      break;
  }
}

/*
 * show_game
 *
 * Show information about a game and allow users to signup
 */

function show_game ()
{
  $highlight='';

  // Extract the EventId and build the query

  $EventId = intval (trim ($_REQUEST['EventId']));
  if (array_key_exists ('RunId', $_REQUEST))
    $RunId = intval (trim ($_REQUEST['RunId']));
  else
    $RunId = 0;

  // checks convention wide setting.
  $signups_allowed = con_signups_allowed();

  // If this is a GM, or a privileged user, they can edit the game.

  $can_edit_game = false;

  if (user_has_priv (PRIV_SCHEDULING) || user_has_priv (PRIV_GM_LIAISON)
       || user_is_moderator ($_SESSION[SESSION_LOGIN_USER_ID], $EventId)
       || user_is_teacher ($_SESSION[SESSION_LOGIN_USER_ID], $EventId) )
    $can_edit_game = true;

  
  $can_signup = can_signup();
  $show_part = user_is_gm_for_game ($_SESSION[SESSION_LOGIN_USER_ID], $EventId);

  // Create an Event object

  $event = new Event;
  $event->load_from_eventid($EventId);



  global $GM_TYPES;
  $gms = $GM_TYPES[$event->GameType];

  // Note if this is a volunteer event (ConSuite or Ops)

  $volunteer_event = ($event->IsOps=='Y') || ($event->IsConSuite=='Y');

  // Note if there are 0 players.  We'll use this later

  $max_signups = $event->MaxPlayersNeutral;

  // Save the game title in the session information, since we'll need
  // it a bunch

  $_SESSION['GameTitle'] = $event->Title;

  if ($can_edit_game)
  {
    echo '<ul id="game_admin" class="menu priv">';
    echo '<li class="title">Event Admin</li>';
    printf ('<li><a href="Schedule.php?action=%d&EventId=%d">Edit Event</a></li>',
	    EDIT_GAME, $EventId);
	
	// editing the presenters of the event is limited to scheduling people 
	if (user_has_priv (PRIV_SCHEDULING) )
      printf ('<li><a href="Schedule.php?action=%d&EventId=%d">Edit %s</a></li>',
	    DISPLAY_GM_LIST, $EventId, $gms);

	if ($event->GameType == "Show")
	{
	  printf ('<li><A HREF=DisplayActTechInfo.php?ShowId=%d>%s</A></li>',
		  $EventId,
		  'Show tech info');
	  echo "    </TD>\n";
	  echo "  </TR>\n";
	}
    //display_comp_info($EventId);
    
    $updater_name = '<Unknown>';

    $sql = 'SELECT DisplayName ';
    $sql .= ' FROM Users';
    $sql .= " WHERE UserId=$event->UpdatedById";

    $updated_result = mysql_query ($sql);
    if (! $updated_result)
      display_mysql_error ('Failed to fetch last updater\'s name');
    else
    {
      if ($updated_row = mysql_fetch_object ($updated_result))
	      $name = trim ("$updated_row->DisplayName");
      mysql_free_result ($updated_result);
    }
    echo "<li class=\"info\"><b>Last updated</b><br/>$event->Timestamp<br/>by $name</li>";
    echo '</ul>';
  }

  // Display the title

  echo "<h2><i>$event->Title</i></h2>\n";

  $num_gms = 0;

  echo "<table>\n";

    if ($volunteer_event)
      display_one_col ('Head of Area', $event->Author);
    //else
    //  display_one_col ('Author(s)', $event->Author);

    // only expose emails if this is a privileged person.
    if ('' != $event->GameEMail && $can_edit_game)
    {
      $email = mailto_or_obfuscated_email_address ($event->GameEMail);
      if ($volunteer_event)
        display_one_col ('Email', $email);
      else  
        display_one_col ('Head of '.$event->GameType, $email);
    }

    // Fetch the list of associated event runners

    $sql = 'SELECT DISTINCT Users.DisplayName, GMs.Role,';
    $sql .= ' Users.EMail, GMs.DisplayEMail, Users.CompEventId';
    $sql .= ' FROM GMs, Users';
    $sql .= " WHERE GMs.EventId=$EventId";
    $sql .= "   AND GMs.DisplayAsGM='Y'";
    $sql .= "   AND Users.UserId=GMs.UserId";
    $sql .= "   AND GMs.Role != 'performer' ";
    $sql .= ' ORDER BY GMs.Role DESC, Users.DisplayName';

    //  echo "$sql<P>";

    $gm_result = mysql_query ($sql);
    if (! $gm_result)
      display_mysql_error ('Failed to fetch list of GMs');

    $num_gms = mysql_num_rows ($gm_result);

    if ($num_gms != 0)
    {
      echo "  <TR>\n";
      echo "    <TH VALIGN=TOP ALIGN=RIGHT>".$gms.":</TH>\n";
      echo "    <TD>\n";
      echo "      <TABLE CELLSPACING=0 CELLPADDING=0>\n";
      while ($gm_row = mysql_fetch_object ($gm_result))
      {
	    echo "        <TR VALIGN=TOP>\n";
	    $name = $gm_row->DisplayName.'&nbsp;';
	    if ($event->GameType == "Panel")
	     $name .= '-&nbsp;'.$gm_row->Role."&nbsp;";
	
	    if ('Y' != $gm_row->DisplayEMail)
	      $EMail = '';

   	    echo "          <TD>$name</TD>\n";
	    echo "          <TD>$EMail</TD>\n";
	    if ($can_edit_game) {
          echo "<TD>";
          if ($gm_row->CompEventId == $EventId) {
            echo "&nbsp;&nbsp;&nbsp;(Comp for this game)";
          }
          echo "</TD>";
	    }
	    echo "        </TR>\n";
      }
    echo "      </TABLE>\n    </TD>\n  </TR>\n";
  
    if (""  != $game_row->Organization)
      display_one_col ('Organization', $game_row->Organization);
  }


  // Make sure the homepage URL has a scheme ('http://')
  if ('' != $event->Homepage)
  {
    $parts = parse_url ($game_row->Homepage);

    if (array_key_exists ('scheme', $parts))
      $homepage = $event->Homepage;
    else
      $homepage = 'http://' . $event->Homepage;

    display_one_col ('Home Page',
		     "<a href=\"$homepage\" target=\"_blank\">$homepage</a>");
  }

  if ($max_signups > 0)
  {
    echo "  <tr>\n";
    if ($volunteer_event)
      echo "    <th>Volunteers Needed:</th>\n";
    else if ($event->GameType == "Show")
      echo "    <th>Total Crew:</th>\n";
    if ($volunteer_event || $event->GameType == "Show")
        printf ("    <td>Min: %d / Max: %d</td>\n",
	    $event->MinPlayersNeutral,
	    $event->MaxPlayersNeutral);
    echo "  </tr>\n";
  }

  if (user_has_priv(PRIV_SCHEDULING))
  {

    if ('Y' == $event->IsOps)
    {
      echo "  <tr>\n";
      echo "    <td colspan=\"2\">This event <b>is</b> Ops</td>\n";
      echo "  </tr>\n";
    }

    if ('Y' == $event->IsConSuite)
    {
      echo "  <tr>\n";
      echo "    <td colspan=\"2\">This event <b>is</b> ConSuite</td>\n";
      echo "  </tr>\n";
    }

  }
  echo "</table>\n";

  // must allow people to join, and must be something you can sign up for
  // Classes and Panels are flexible attendance and have no need for signups
  if (can_show_schedule () )
  {
    $logged_in = is_logged_in ();

    // Extract information for the runs of this game

    $sql = "SELECT RunId, Day, StartHour, Rooms, ShowId FROM Runs";
    $sql .= ' WHERE EventId=' . $event->EventId;
    $sql .= ' ORDER BY Day, StartHour';
    $runs_result = mysql_query ($sql);
    if (! $runs_result)
      return display_mysql_error ("Cannot query runs for Event $event->EventId");
    $run_count = mysql_num_rows ($runs_result);
    $run_col = -1;
    
    // If this is a GM or a privileged user, AND there's only one run AND
    // there are neutral players, offer the user the ability to freeze the
    // gender balance

    /*
    if ($can_edit_game &&
	(1 == $run_count) &&
	(0 != $event->MaxPlayersNeutral))
    {
      printf ('<a href=Schedule.php?action=%d&EventId=%d>Freeze Gender Balance</a>',
	      SCHEDULE_FREEZE_GENDER_BALANCE,
	      $EventId);
    }
    */
    // If we can show them the schedule, show them *something*

    echo "<CENTER>\n";

    // If the user isn't logged in, suggest that he should be

    if (! $logged_in && is_signup_event($event->GameType) && $max_signups > 0)
    {
	    echo "<table border=1>\n";
	    echo "  <tr>\n";
	    echo "    <td>&nbsp;You must be <a href=\"index.php\">logged in</a> to signup for this event&nbsp;</td>\n";
	    echo "  </tr>\n";
	    echo "</table>\n";
	}
    
    // OK, show the user what he can (potentially) do
  	if (0 == $run_count)
	    $colspan = 1;
	else
	    $colspan = min ($run_count, 4);

    // if signing up is an option create the info.
    if ($max_signups > 0 && is_signup_event($event->GameType) 
    		&& $logged_in && $can_signup)
	    $table_title = 'Click on the run day/time to signup';
	else
	    $table_title = 'Schedule Details';
    
    if ( $run_count > 0)
    {    
  	  echo "<TABLE BORDER=1>\n";
      echo "  <TR>\n";
	  echo "    <TH COLSPAN=$colspan>$table_title</TH>";
	  echo "  </TR>\n";
    }
    
 	if (($run_count > 1) && $can_edit_game)
	{
	  $cols = min (4, $run_count);
	  echo "  <TR>\n";
	  echo "    <TD COLSPAN=$cols ALIGN=CENTER>\n";
	  printf ('<A HREF=Schedule.php?action=%d&EventId=%d&FirstTime=1>%s</A>',
		  SCHEDULE_SHOW_ALL_SIGNUPS,
		  $EventId,
		  'Show all signups');
	  echo "    </TD>\n";
	  echo "  </TR>\n";
	}
	

	
	if ( $run_count > 0)
	{

	  // The sequence number must be the same for all runs

	  $seq = increment_sequence_number ();

	  // Display the runs

	  while ($run_row = mysql_fetch_object ($runs_result))
	  {
	    // Make sure that there aren't more than 4 runs on a row

	    if (4 == ++$run_col)
	    {
	      $run_col = 0;
	      echo " <TR ALIGN=CENTER VALIGN=TOP>\n";
	    }
        $ShowId = $run_row->ShowId;
	    $game_start = start_hour_to_am_pm ($run_row->StartHour);
	    $game_end = start_hour_to_am_pm ($run_row->StartHour +
					       $event->Hours);
	    $run_text = "$run_row->Day. $game_start - $game_end\n";
		
	    if ('' != $run_row->Rooms)
	      $run_text .= '<br>' . pretty_rooms($run_row->Rooms) . "\n";
		$text = $run_text;

	    $bgcolor = '';

        // if signing up is an option create the info related to availability
        // of both slot and user
        if ( ($max_signups > 0 || $can_edit_game || $show_part) && is_signup_event($event->GameType) && $logged_in)
        {
  		  $confirmed = array ();
		  $waitlisted = array ();


	      // Check whether the user is already signed up for this run

	      get_user_status_for_run ($run_row->RunId, $SignupId, $is_signedup);

	      // Get the signup counts for the run
	      get_counts_for_run ($run_row->RunId, $confirmed, $waitlisted);

	      //	$date = day_to_date ($run_row->Day);

	      $game_full = game_full ($full_msg, $_SESSION[SESSION_LOGIN_USER_GENDER],
				    $confirmed['Male'], $confirmed['Female'],
				    0,
				    0,
				    $event->MaxPlayersNeutral,$confirmed['']);
	      $count_text = sprintf ('Signed Up: %d<BR>Waitlist: %d',
		 		   $confirmed['Total'],
				   $waitlisted['Total']);

	      // If the user can edit the GM (he/she is a GM) or if they
	      // have Outreach privilege, let them view the signups

  	      if ($can_edit_game || user_has_priv (PRIV_OUTREACH) || $show_part)
	      {
	        $count_text = sprintf ('<A HREF=Schedule.php?action=%d&RunId=%d' .
				     '&EventId=%d&FirstTime=1>%s</A>',
				     SCHEDULE_SHOW_SIGNUPS,
				     $run_row->RunId,
				     $EventId,
				     $count_text);
	      }


	      if (-1 != $SignupId)
	      {
	        $text = $run_text . '<P>' . $count_text;
	        if ($is_signedup)
		      $text .= '<P><I>You are signed up</I>';
	        else
	        {
		      $wait = get_waitlist_number ($run_row->RunId, $SignupId);

		      if (0 == $wait)
		         $text .= '<P><I>You are waitlisted</I>';
		      else
		         $text .= "<P><I>You are waitlisted #$wait</I>";
	        }  

	        if ($event->GameType != "Show" && $event->GameType != "Call" && 
	             ($can_signup) && (0 != con_signups_allowed()))
	        {
	            if ($event->GameType=="Tech Rehearsal")
	              $SignupId .= "&ShowId=".$ShowId;
	              
		        $link = sprintf ('<A HREF=Schedule.php?action=%d&SignupId=%s' .
				 '&Seq=%d>',
				 WITHDRAW_FROM_GAME,
				 $SignupId,
				 $seq);
		         $text .= "<P>${link}Withdraw</A>";
	         }
	      }
	      else
	      {
	        // If we're logged in we can (attempt) to signup for this game
	        // If the game's full, the user will be put on the waitlist

	        $text = '<P>';


	        
				if ($can_signup && $max_signups > 0)
				{
				  $link = sprintf ('<A HREF=Schedule.php?action=%d&RunId=%d&' .
					   'EventId=%d&Seq=%d>',
					   SCHEDULE_SIGNUP,
					   $run_row->RunId,
					   $EventId,
					   $seq);
		  		  $text .= "${link}$run_text</A><P>";
				}
				else
				  $text .= $run_text;
	      	

	        $text .= '<P>' . $count_text;

	        if ($game_full)
	        {
				$bgcolor = get_bgcolor ('Full');
				$text .= "<P><I>$full_msg</I>";
	      	}
	      }

	      if (($run_count > 1) && ($RunId == $run_row->RunId))
	        $highlight = 'style="border: medium solid"';
	      else
	        $highlight = '';

	    } // end of if this is a signupable event
	    
	    echo "    <TD $highlight $bgcolor>$text</TD>";

	  } // while there are more runs

	  echo "</TABLE>\n";

	  if ($can_edit_game && is_signup_event($event->GameType) && $show_part)
	    echo "    <BR>Click on the counts to see signup list\n";

	  if ('Y' == $event->CanPlayConcurrently)
	    echo "<BR><B>Note:</B> Classes and Panels require a Whole Shebang pass or a Conference pass for the day of attendance.\n";
      
      echo "</CENTER>\n";

	} // if it's scheduled

  }

  display_tickets_for_event($event->EventId);
	
  if ($event->SpecialEvent)
  {
    echo "<br>";
	echo $event->Description;
    echo "<p>\n";
	
  }
  else
  {
    echo "<P>\n";
    echo "<HR>\n";

    echo $event->Description;    

    echo "<p>\n<hr>\n";
  }
  

if ($event->GameType != "Show")
{
  if (0 == $num_gms)
    return;


  // Fetch the list of GMs again, so we can display their bios

  $sql = 'SELECT DISTINCT Users.DisplayName, Users.UserId';
  $sql .= ' FROM GMs, Users';
  $sql .= " WHERE GMs.EventId=$EventId";
  $sql .= "   AND GMs.DisplayAsGM='Y'";
  $sql .= "   AND Users.UserId=GMs.UserId";
  $sql .= "   AND GMs.Role != 'performer' ";
  $sql .= ' ORDER BY Users.DisplayName';

  //  echo "$sql<P>";

  $gm_result = mysql_query ($sql);
  if (! $gm_result)
    display_mysql_error ('Failed to fetch list of GMs');

echo "<table border=0 width=\"800\">";

  while ($gm_row = mysql_fetch_object ($gm_result))
  {
  echo "<TR><TD >";
    display_header ("$gm_row->DisplayName");

    $sql = "SELECT BioText, Website, PhotoSource FROM Bios WHERE UserId=$gm_row->UserId";

    $bio_result = mysql_query ($sql);
    if (! $bio_result)
      display_mysql_error ('Failed to fetch bio');

    $bio_text = '';

    $bio_row = mysql_fetch_object ($bio_result);
    if ($bio_row)
      show_user_homepage_bio_info ($bio_row->Website, $bio_row->BioText, $bio_row->PhotoSource);
    else
      echo "<BR><i>No Bio available.</i><br><br>\n";

  echo "</TD></TR>";
  }
echo "</table>";
}
else
{
  get_who_is_who_for_show($EventId);
}
}

/*
 * display_tickets_for_event
 *
 * For special event, this displays and allows the user to purchase tickets to the event.
 *
 * $EventId - the ID for the event in question.
 * Returns:  nothing.
 */
function display_tickets_for_event($EventId)
{
	get_valid_tickets_for_event($EventId, $TicketItems);
	if (sizeof($TicketItems) <= 0)
		return;
		
	display_header("The following tickets allow admission to this event:<br>");	
	
	foreach ($TicketItems as $item)
	{
		printf("%s &nbsp &nbsp ", $item->Title);
		$link = create_ticket_refer_link($item->ItemId);
		printf("<a href=\"%s\" target=\"_blank\">", $link);
		printf("Purchase Here</a>");

		echo "<br>\n";
	}
}





/*
 * validate_new_counts
 *
 * Verify that if the max counts are changing for this game, that the number
 * of players already signedup will fit
 */

function validate_new_counts ($EventId,
			      $old_max_male, $old_max_female, $old_max_neutral,
			      $new_max_male, $new_max_female, $new_max_neutral)
{
/*
  echo "validate_new_counts:<BR>\n";
  echo "Old Max's: Male: $old_max_male, Female: $old_max_female, Neutral: $old_max_neutral<BR>\n";
  echo "New Max's: Male: $new_max_male, Female: $new_max_female, Neutral: $new_max_neutral<P>\n";
*/

  // If the max counts haven't changed, we don't need to validate any more

  if (($old_max_male == $new_max_male) &&
      ($old_max_female == $new_max_female) &&
      ($old_max_neutral == $new_max_neutral))
    return true;

  // Start by assuming that it will OK

  $ok = true;

  // Get each run scheduled for this game and check the number of players
  // who have signed up

  $sql = 'SELECT RunId, Day, StartHour, TitleSuffix';
  $sql .= '  FROM Runs';
  $sql .= "  WHERE EventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ("Query failed for runs for $EventId");

  $confirmed = array ();
  $waitlisted = array ();

  while ($row = mysql_fetch_object ($result))
  {
    get_counts_for_run ($row->RunId, $confirmed, $waitlisted);
    if (! players_will_fit ($confirmed['Male'], $confirmed['Female'],
			    $new_max_male, $new_max_female, $new_max_neutral))
    {
      //      echo "New Max's: Male: $new_max_male, Female: $new_max_female, Neutral: $new_max_neutral<P>\n";
      $error = sprintf ('There are %d male and %d female confirmed participants. ' .
			'You cannot lower the number of players to the point '.
			'where they will not fit in the game.',
			$confirmed['Male'], $confirmed['Female']);
      display_error ($error);
      $ok = false;
    }
  }

  // Return whether we found any problems

  return $ok;
}

/*
 * accept_players_from_waitlist
 *
 * Iterate over the waitlist, if there is one, for each run and accept users
 * into the game in order.  This function expects to run with the following
 * tables locked:
 *     Signup - Write locked
 *     Users, Runs, Events, GMs, - Read locked
 *
 * The locking prevents another user from slipping in while we're scanning
 * the waitlist for this game for a player who's been waiting.  The locks
 * will be released after the function completes
 */

function accept_players_from_waitlist ($EventId,
				       $Title,
				       $Hours,
				       $CanPlayConcurrently,
				       $max_male,
				       $max_female,
				       $max_neutral)
{
  // Get each run scheduled for this game and see if we can accept anyone
  // from the waitlist

  $sql = 'SELECT RunId, Day, StartHour, TitleSuffix';
  $sql .= '  FROM Runs';
  $sql .= "  WHERE EventId=$EventId";

  $run_result = mysql_query ($sql);
  if (! $run_result)
    display_mysql_error ("Query failed for runs for $EventId");

  $confirmed = array ();
  $waitlisted = array ();

  while ($run_row = mysql_fetch_object ($run_result))
  {
    $run_title = stripslashes (trim ("$Title $run_row->TitleSuffix"));

    //    echo "Processing run $run_row->RunId, $run_title<br>\n";

    get_counts_for_run ($run_row->RunId, $confirmed, $waitlisted);

    //    echo count($waitlisted) . " Players on the waitlist<br>\n";

    if (count ($waitlisted) > 0)
    {
      /*
      echo "Confirmed males: " . $confirmed['Male'] .
	", Confirmed females: " . $confirmed['Female'] . "<br>\n";
      echo "max males: $max_male, max females: $max_female, max neutral: $max_neutral<br>\n";
      */
      calculate_available_slots ($confirmed['Male'], $confirmed['Female'],
				 $max_male, $max_female, $max_neutral,
				 $avail_male, $avail_female, $avail_neutral);

      //  echo "Available slots: male - $avail_male, female - $avail_female, neutral - $avail_neutral<br>\n";

      accept_players_from_waitlist_for_run ($EventId,
					    $run_row->RunId,
					    $run_row->RunId,
					    $run_title,
					    $run_row->Day,
					    $run_row->StartHour,
					    $Hours,
					    $CanPlayConcurrently,
					    $avail_male,
					    $avail_female,
					    $avail_neutral);
    }
  }
}
/*
 * is_signup_event
 *
 * Check that the event is a type that allows signup
 *   Conference items do not - they are open to conference attendees.
 *   Ops volunteer slots always do.
 *
 */
 function is_signup_event ($GameType)
 {
   return ($GameType != "Class" && $GameType != "Panel");
 }

/*
 * notify_about_event_changes
 *
 * Check for changes in player counts or hours and notify the Bid Chair
 * and GM Coordinator if there are any
 */

function notify_about_event_changes ($EventId, $row)
{
  // Check for changes in the fields we care about

  $a = array ('MinPlayersMale', 'PrefPlayersMale', 'MaxPlayersMale',
	      'MinPlayersFemale', 'PrefPlayersFemale', 'MaxPlayersFemale',
	      'MinPlayersNeutral', 'PrefPlayersNeutral', 'MaxPlayersNeutral',
	      'Hours');

  $changes = '';
  foreach ($a as $key)
  {
    if ($_REQUEST[$key] != $row[$key])
    {
      $changes .= sprintf ("%s changed from %d to %d\n",
			   $key,
			   $row[$key],
			   $_REQUEST[$key]);
    }
  }

  // If nothing important (to the con) changed, we're done

  if ('' == $changes)
    return;

  // Tell the Bid Chair and the GM Coordinator

  $subj = sprintf ('[%s] Changes were made to "%s"',
		   CON_NAME,
		   $_REQUEST['Title']);

  $sql = 'Select FirstName, LastName FROM Users';
  $sql .= ' WHERE UserId=' .$_SESSION[SESSION_LOGIN_USER_ID];

  $result = mysql_query ($sql);
  if ($result)
  {
    $user_row = mysql_fetch_object($result);
    if ($user_row)
      $changes .= "\nMade by $user_row->FirstName $user_row->LastName\n";
  }

  //  echo "Subject: $subj<br>\n";
  //  echo "Body: $changes<p>\n";
  if (! intercon_mail (EMAIL_BID_CHAIR, $subj, $changes))
    return display_error ('Attempt to send changes to Bid Chair failed');

  if (! intercon_mail (EMAIL_GM_COORDINATOR, $subj, $changes))
    return display_error ('Attempt to send changes to GM Coordinator failed');
}

/*
 * add_game
 *
 * Process a game form.  Allows the user to both add a new game and
 * update an existing game
 */

function add_game ()
{
  //  dump_array ('POST', $_POST);

  // If we're out of sequence, don't do anything

  if (out_of_sequence ())
    return display_sequence_error (true);

  // Are updating or adding?

  $update = isset ($_REQUEST['EventId']);
  $EventId = trim ($_REQUEST['EventId']);
  $Title = trim ($_POST['Title']);

  if (! $update)
  {
      // Check that the title isn't already in the Events table

      if (! title_not_in_events_table ($Title))
        return false;
  }

  // Check the numeric arguments

  if (! validate_players ('Neutral'))
    return FALSE;

  if (! validate_int ('Hours', 1, 12, 'Hours'))
      return FALSE;

  // If this is an update, make sure that any players signed up still fit
  // for each of the runs

  $new_max_male = intval (trim ($_REQUEST['MaxPlayersMale']));
  $new_max_female = intval (trim ($_REQUEST['MaxPlayersFemale']));
  $new_max_neutral = intval (trim ($_REQUEST['MaxPlayersNeutral']));

  if ($update)
  {
    // Start by fetching the counts currently in place

    $sql = 'SELECT PrefPlayersNeutral,';
    $sql .= '  Hours';
    $sql .= '  FROM Events';
    $sql .= "  WHERE EventId=$EventId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query failed for current event counts');

    $row = mysql_fetch_array ($result);
    if (! $row)
      return display_error ("Query for event counts failed for $EventId");

    $old_max_male = 0;
    $old_max_female = 0;
    $old_max_neutral = $row['MaxPlayersNeutral'];

    if (! validate_new_counts ($EventId,
			       $old_max_male, $old_max_female, $old_max_neutral,
			       $new_max_male, $new_max_female, $new_max_neutral))
    {
      echo "validate_new_counts failed!\n";
      return false;
    }

    // Check for changes in player counts, hours and notify the Bid Chair
    // and GM Coordinator if there are any

   // notify_about_event_changes ($EventId, $row);
  }

  // If we don't have a game EMail address, we can't send mail there, can we?
  if ('' == trim ($_POST['GameEMail']))
    $_POST['ConMailDest'] = 'GMs';

  if (array_key_exists ('CheckIsOps', $_POST))
    $IsOps = 'Y';
  else
    $IsOps = 'N';

  if (array_key_exists ('CheckIsConSuite', $_POST))
    $IsConSuite = 'Y';
  else
    $IsConSuite = 'N';

  if (array_key_exists ('CheckIsIronGm', $_POST))
    $IsIronGm = 'Y';
  else
    $IsIronGm = 'N';

  if (array_key_exists ('CheckIsSmallGameContestEntry', $_POST))
    $IsSmallGameContestEntry = 'Y';
  else
    $IsSmallGameContestEntry = 'N';


  if ($update)
    $sql = 'UPDATE Events SET ';
  else
    $sql = 'INSERT Events SET ';

  $sql .= build_sql_string ('Title', $Title, false);
  $sql .= build_sql_string ('Author');
  $sql .= build_sql_string ('GameEMail');
  $sql .= build_sql_string ('Organization');
  $sql .= build_sql_string ('Homepage');
  $sql .= build_sql_string ('ConMailDest');

  if (user_has_priv (PRIV_SCHEDULING))
  {
    $sql .= build_sql_string ('IsOps', $IsOps);
    $sql .= build_sql_string ('IsConSuite', $IsConSuite);
    $sql .= build_sql_string ('IsIronGm', $IsIronGm);
    $sql .= build_sql_string ('IsSmallGameContestEntry',
			      $IsSmallGameContestEntry);
    $sql .= build_sql_string ('Hours');
  }

  $sql .= build_sql_string ('MinPlayersNeutral');
  $sql .= build_sql_string ('MaxPlayersNeutral', $new_max_neutral);
  $sql .= build_sql_string ('PrefPlayersNeutral');

  $sql .= build_sql_string ('Description', '', true, true);
  $sql .= build_sql_string ('ShortBlurb', '', true, true);

  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);

  if ($update)
    $sql .= " WHERE EventId=$EventId";

  //  echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Insert into Events failed");

  // If the update succeeded, see if we need to pull any users off the
  // waitlist for any runs

  if (! $update)
    return true;

  // If the counts haven't increased, we don't have to pull anyone off
  // the waitlists

  if (($old_max_male >= $new_max_male) &&
      ($old_max_female >= $new_max_female) &&
      ($old_max_neutral >= $new_max_neutral))
    return true;

  // We lock the Signup table to make sure that if there are two users trying
  // to get the last slot in a game, then only one will succeed.  A READ lock
  // allows clients that only read the table to continue, but will block
  // clients that attempt to write to the table

  $result = mysql_query ('LOCK TABLE Signup WRITE, Users READ, Runs READ, Events READ, GMs READ');
  if (! $result)
  {
    display_mysql_error ('Failed to lock the Signup table');
    return SIGNUP_FAIL;
  }

  accept_players_from_waitlist ($EventId, $Title,
				intval (trim ($_REQUEST['Hours'])),
				'Y' == trim ($_REQUEST['CanPlayConcurrently']),
				$new_max_male,
				$new_max_female,
				$new_max_neutral);

  // Unlock the Signup table so that other queries can access it

  $result = mysql_query ('UNLOCK TABLES');
  if (! $result)
  {
    display_mysql_error ('Failed to unlock the Signup table');
    return SIGNUP_FAIL;
  }
  return true;
}

function display_players_entry ($gender)
{
  $min = 'MinPlayers' . $gender;
  $max = 'MaxPlayers' . $gender;
  $pref = 'PrefPlayers' . $gender;

  $min_value = $_POST[$min];
  if (empty ($min_value))
    $min_value = '0';

  $max_value = $_POST[$max];
  if (empty ($max_value))
    $max_value = '0';

  $pref_value = $_POST[$pref];
  if (empty ($pref_value))
    $pref_value = '0';

  if ($gender == "Neutral")
    $gender = "";

  print ("  <TR>\n");
  print ("    <TD ALIGN=RIGHT>$gender Participants:</TD>\n");
  print ("    <TD ALIGN=LEFT>\n");
  printf ("      Min:<INPUT TYPE=TEXT NAME=%s SIZE=3 MAXLENGTH=3 VALUE=\"%s\">&nbsp;&nbsp;&nbsp;\n",
	  $min,
	  $min_value);
  printf ("      Max:<INPUT TYPE=TEXT NAME=%s SIZE=3 MAXLENGTH=3 VALUE=\"%s\">&nbsp;&nbsp;&nbsp;\n",
	  $max,
	  $max_value);
  printf ("      Preferred:<INPUT TYPE=TEXT NAME=%s SIZE=3 MAXLENGTH=3 VALUE=\"%s\">\n",
	 $pref,
	 $pref_value);
  print ("    </TD>\n");
  print ("  </TR>\n");
}

/*
 * load_game_post_array
 *
 * Fill the $_POST array with information about the selected game
 */

function load_game_post_array ()
{
  $EventId = intval (trim ($_REQUEST['EventId']));
  if (0 == $EventId)
    return display_error ("Invalid EventId");

  $sql = "SELECT * FROM Events WHERE EventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for game list failed');

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find any game with EventId $EventId");

  // Fetch the row from the database as an associative array, and fill the
  // $_POST array with it's contents.  Since we cleverly used the same names
  // for the database columns and the form elements, we can just copy the
  // data right in

 $row = mysql_fetch_array ($result, MYSQL_ASSOC);

  foreach ($row as $k => $v)
    $_POST[$k] = $v;

  mysql_free_result ($result);

  if ('Y' == $_POST['IsOps'])
    $_POST['CheckIsOps'] = 1;

  if ('Y' == $_POST['IsConSuite'])
    $_POST['CheckIsConSuite'] = 1;

  if ('Y' == $_POST['IsIronGm'])
    $_POST['CheckIsIronGm'] = 1;

  if ('Y' == $_POST['IsSmallGameContestEntry'])
    $_POST['CheckIsSmallGameContestEntry'] = 1;

  return true;
}

/*
 * can_edit_game_info
 *
 * Check whether the logged in user can edit the game information.  Either
 * if it's the Bid Chair (or staff member) or a GM for the game.
 */

function can_edit_game_info ()
{
  // If the user isn't logged in then they can't edit anything!!!

  if (empty ($_SESSION[SESSION_LOGIN_USER_ID]))
    return false;

  // If the user is the Bid Chair (or Staff member) they can edit the game
  // information

  if (user_has_priv (PRIV_BID_CHAIR))
    return true;

  // If the user is the GM Liaison, they can edit the game information

  if (user_has_priv (PRIV_GM_LIAISON))
    return true;

  $EventId = intval (trim ($_REQUEST['EventId']));
  if (0 == $EventId)
    return display_error ('Invalid EventId');

  // See if the logged in user is a GM for this game

  $sql = 'SELECT GMId FROM GMs';
  $sql .= "  WHERE EventId=$EventId AND GMs.Role != 'performer'";
  $sql .= "    AND UserId=" . $_SESSION[SESSION_LOGIN_USER_ID];

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Check for GMs failed');

  return (mysql_num_rows ($result) > 0);
}

function scheduling_priv_option ($name, $field, $check_key)
{
  // If the user has scheduling priv, display a checkbox

  if (user_has_priv (PRIV_SCHEDULING))
  {
    if (array_key_exists ($check_key, $_POST))
      $checked = 'checked';
    else
      $checked = '';

    echo "  <tr>\n";
    echo "    <td colspan=2>\n";
    echo "      <input type=\"checkbox\" $checked name=\"$check_key\">This event is $name\n";
    echo "    </td>\n";
    echo "  </tr>\n";

    return true;
  }

  // Not privileged.  If this isn't a special event, just return false

  if ('N' == $_POST[$field])
    return false;

  // Return true to indicate that this is an event, not a game

  return true;
}

/*
 * display_game_form
 *
 * Show the game information and let the user edit it
 */

function display_game_form ()
{
  $EventId = intval (trim ($_REQUEST['EventId']));

  echo "<H2>Update <I>" . $_POST['Title'] . "</I></H2>\n";

  print ("<FORM METHOD=POST ACTION=" . $_SERVER['PHP_SELF'] . ">\n");
  form_add_sequence ();
  form_hidden_value('action', PROCESS_ADD_GAME);
  form_hidden_value('EventId', $EventId);
  form_hidden_value('CanPlayConcurrently',
		    trim ($_POST['CanPlayConcurrently']));

  print ("<TABLE BORDER=0>\n");
  form_text (64, 'Title', '', 128);
  //form_text (64, 'Author(s)', 'Author', 128);
  form_text (64, 'Contact Email', 'GameEMail');
  form_text (64, 'Homepage', '', 128);
  form_text (64, 'Organization');

  if ($is_event)
    $event_type = 'event';
  else
    $event_type = 'game';


  if (user_has_priv (PRIV_SCHEDULING))
  {
    display_players_entry ("Neutral");

    $is_event = scheduling_priv_option ('Ops', 'IsOps', 'CheckIsOps');


    form_text (2, 'Hours');
  }
  else
  {
    if (1 == $_POST['Hours'])
      $period = 'hour';
    else
      $period = 'hours';

    echo "  <tr valign=\"top\">\n";
    printf ('    <td colspan="2">This %s lasts %d %s - Contact the <a href="mailto:%s">' .
	    "Conference Coordinator</a> to modify the length of this %s.</td>\n",
	    $event_type,
	    $_POST['Hours'],
	    $period,
	    EMAIL_GM_COORDINATOR,
	    $event_type);
    echo "  </tr>\n";
    printf ("<input type=\"hidden\" name=\"Hours\" value=\"%d\">\n",
	  intval (trim ($_POST['Hours'])));

  }

  form_textarea ('Short paragraph (50 words or less) displayed in all lists', 'ShortBlurb', 4, TRUE, TRUE);
  form_textarea ('Description', 'Description', 20, TRUE, TRUE);
  form_submit ('Update Info');

  print ("</TABLE>\n");
  print ("</FORM>\n");

  $sql = "SELECT FirstName, LastName FROM Users";
  $sql .= "  WHERE CompEventId=$EventId";
  $sql .= "  ORDER BY LastName, FirstName";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for list of comp'd users failed");

/*  if (0 != mysql_num_rows ($result))
  {
    echo "<P>The following users are comped for this game:\n";

    while ($row = mysql_fetch_object ($result))
    {
      echo "<BR>&nbsp;&nbsp;&nbsp;&nbsp;$row->LastName, $row->FirstName\n";
    }
  }
*/
  echo "<P>\n";
}

/*
 * list_games_alphabetically
 *
 * Let the user select a game to view.
 */

function list_games_alphabetically ($GameType="")
{
  if (file_exists(TEXT_DIR.'/'.$GameType.'intro.html'))
	include(TEXT_DIR.'/'.$GameType.'intro.html');	
  
  
  $whereclause ="";
  if ($GameType == "Conference")
    $whereclause .= " WHERE GameType='Class' or GameType='Panel'";
  else if ($GameType == "Ops")
    $whereclause .= " WHERE GameType='Ops' or GameType='Tech Rehearsal'";  
  else if ($GameType == "Events")
    $whereclause .= " WHERE GameType='Show' or GameType='MasterClass' or GameType='Drop-In' or GameType='Special'";
  else if ($GameType != "")
    $whereclause .= " WHERE GameType='".$GameType."'";

  $sql = 'SELECT EventId, Title, ShortBlurb, SpecialEvent,';
  $sql .= ' IsSmallGameContestEntry, GameType, Fee,';
  $sql .= ' LENGTH(Description) AS DescLen';
  $sql .= ' FROM Events'.$whereclause;
  $sql .= ' ORDER BY Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for game list failed');

  $n = mysql_num_rows ($result);

  if ($n > 0)
  {
      if (file_exists(TEXT_DIR.'/betweenclasses.html'))
         include(TEXT_DIR.'/betweenclasses.html');	     

 
    while ($row = mysql_fetch_object ($result))
    {
       list_this_game($row, $GameType);
       if (file_exists(TEXT_DIR.'/betweenclasses.html'))
         include(TEXT_DIR.'/betweenclasses.html');	  

    }
  }
  mysql_free_result ($result);
}

function list_this_game($row, $GameType)
{
      // If this is a special event, and there's no text, skip it
  if ((0 != $row->SpecialEvent) && 
              ('' == $row->ShortBlurb))
    return;

      // If there's no long description, don't offer a link
  $title = $row->Title;
  if ($row->GameType == "Panel")
    $title = "PANEL:  ".$row->Title;
  echo "<p>\n";
  if ($row->DescLen > 0 && SELECTEVENTS_ENABLED)
    printf ("<a href=\"Schedule.php?action=%d&EventId=%d\">%s</a> \n",
            SCHEDULE_SHOW_GAME,
	    $row->EventId,
	    $title);
  else
    echo "<b>$row->Title</b> \n";

//      if ('Other' != $row->GameType)
//	echo "($row->GameType)";


	// get the teachers or panelists 

     
     // Show event leaders for all event types - 
     // CODE REVIEW: Do we want to restrict to certain types?  
    
  if ($GameType != "Show" )
  {
    show_gms($row);
  }
  if ('' != $row->ShortBlurb)
    echo "<br>\n$row->ShortBlurb\n";
  if ('' != $row->Fee)
    echo "<br>\n<i><font color=red>This event has a fee:  $row->Fee</font></i>\n";
 // echo "</p>\n";
}


function show_gms($row){
    $sql = 'SELECT DISTINCT Users.DisplayName';
    $sql .= ' FROM GMs, Users';
    $sql .= " WHERE GMs.EventId=$row->EventId";
    $sql .= "   AND GMs.DisplayAsGM='Y'";
    $sql .= "   AND Users.UserId=GMs.UserId AND GMs.Role != 'performer'";
    $sql .= ' ORDER BY Users.DisplayName';

    $gm_result = mysql_query ($sql);
    if (! $gm_result)
      display_mysql_error ('Failed to fetch list of GMs');

    $gm_row = mysql_fetch_object ($gm_result);
    echo "<br><i>$gm_row->DisplayName</i>";

    while ($gm_row = mysql_fetch_object ($gm_result))
    {
      echo ", <i>$gm_row->DisplayName</i>";
    }
}

/*
 * show_signups_state
 *
 * Show the users that are confirmed or waitlisted for this game
 */

function show_signups_state ($bConfirmed, $EventId, $RunId, $order_text,
			     $order_by, $result, &$status, &$gms, $can_edit,
			     $include_number_checked, $include_name_checked,
			     $include_email_checked, $include_gm_flag_checked,
			     $include_gms_checked,
			     $include_confirmed_checked,
			     $include_waitlisted_checked, $showrun=FALSE)
{
  if ($bConfirmed)
  {
    $state = 'Confirmed';
    $abbrev = 'Conf';
  }
  else
  {
    $state = 'Waitlisted';
    $abbrev = 'Wait';
  }

  $checked_state='';
  if ('' != $include_confirmed_checked)
    $checked_state .= '&IncludeConfirmed=on';
  if ('' != $include_waitlisted_checked)
    $checked_state .= '&IncludeWaitlisted=on';
  if ('' != $include_number_checked)
    $checked_state .= '&IncludeNumber=on';
  if ('' != $include_name_checked)
    $checked_state .= '&IncludeName=on';
  if ('' != $include_email_checked)
    $checked_state .= '&IncludeEmail=on';
  if ('' != $include_gm_flag_checked)
    $checked_state .= '&IncludeGMFlag=on';
  if ('' != $include_gms_checked)
    $checked_state .= '&IncludeGMs=on';

  echo "<P><FONT SIZE=\"+1\"><B>$state Participants</B></FONT> - by $order_text\n";
  echo "<TABLE BORDER=1 CELLPADDING=5>\n";
  echo "  <TR ALIGN=LEFT VALIGN=BOTTOM>\n";

  if (0 == $RunId)
  {
    $action = SCHEDULE_SHOW_ALL_SIGNUPS;
    $run_id = '';
  }
  else
  {
    $action = SCHEDULE_SHOW_SIGNUPS;
    $run_id = "&RunId=$RunId";
  }

  $has_registrar_priv = user_has_priv (PRIV_REGISTRAR);

  if ($has_registrar_priv)
    printf ("<th>Seq</th>\n");

  if ('' != $include_number_checked)
    printf ('<TH><A HREF=Schedule.php?action=%d&EventId=%d&OrderBy=%d%s%s>' .
	    "%s</A></TH>\n",
	    $action,
	    $EventId,
	    ORDER_BY_SEQ,
	    $checked_state,
	    $run_id,
	    $abbrev);

  if ('' != $include_name_checked)
    printf ('<TH><A HREF=Schedule.php?action=%d&EventId=%d&OrderBy=%d%s%s>' .
	    "%s</A></TH>\n",
	    $action,
	    $EventId,
	    ORDER_BY_NAME,
	    $checked_state,
	    $run_id,
	    'Name');


  if ('' != $include_email_checked)
    echo "    <TH ALIGN=LEFT>EMail</TH>\n";

  if ('' != $include_gm_flag_checked)
    echo "    <TH>Teacher/Panelist/Perfomer</TH>\n";
    
  if ($showrun)
    echo "    <TH>When Booked</TH>\n";

  echo "  </TR>\n";


  while ($row = mysql_fetch_object ($result))
  {
    if (empty ($gms[$row->UserId]))
      $is_gm = '&nbsp;';
    else
    {
      $is_gm = $gms[$row->UserId];
      if (strlen($is_gm) == 0) 
        $is_gm = "coordinator";
      //unset ($gms[$row->UserId]);
      if ('' == $include_gms_checked)
	continue;
    }

    $name = "$row->DisplayName";

    echo "  <TR VALIGN=TOP>\n";

    if ($has_registrar_priv)
      printf ("    <td align=center>%d</td>\n",
	      $row->SignupId);

    if ('' != $include_number_checked)
      echo '    <TD ALIGN=CENTER>' . $status[$row->SignupId] . "</TD>\n";

    if ('' != $include_name_checked)
    {
      if ($can_edit && (0 != $RunId))
	printf ("    <TD><A HREF=Schedule.php?action=%d&SignupId=%d$run_id&EventId=%d>%s</A></TD>\n",
		SHOW_USER,
		$row->SignupId,
		$EventId,
		$name);
      else
	echo "    <TD>$name</TD>\n";
    }


    if ('' != $include_email_checked)
      echo "    <TD><A HREF=MAILTO:$row->EMail>$row->EMail</A></TD>\n";

    if ('' != $include_gm_flag_checked)
      echo "    <TD ALIGN=CENTER>$is_gm</TD>\n";
      
    if ($showrun)
      echo "    <TD ALIGN=CENTER>$row->Day, ".start_hour_to_am_pm($row->StartHour)."</TD>\n";

    echo "  </TR>\n";
  }
  echo "</TABLE>\n";

  mysql_free_result ($result);
}

/*
 * show_signups
 *
 * Show the users that are signed up for this game
 */

function show_signups ()
{
  $RunId = intval (trim ($_REQUEST['RunId']));
  $EventId = intval (trim ($_REQUEST['EventId']));

  if (! isset ($_REQUEST['OrderBy']))
    $OrderBy = ORDER_BY_SEQ;
  else
    $OrderBy = intval (trim ($_REQUEST['OrderBy']));

  if (isset ($_REQUEST['CSV']))
    $csv = intval (trim ($_REQUEST['CSV']));
  else
    $csv = 0;

  if (array_key_exists ('FirstTime', $_REQUEST))
  {
    $include_number_checked = 'CHECKED';
    $include_name_checked = 'CHECKED';
    $include_email_checked = 'CHECKED';
    $include_gm_flag_checked = 'CHECKED';
    $include_gms_checked = 'CHECKED';
    $include_confirmed_checked = 'CHECKED';
    $include_waitlisted_checked = 'CHECKED';
    $include_nl_checked = 'CHECKED';
    $comma_checked = 'CHECKED';
    $semicolon_checked = '';
    $separator = ', ';
  }
  else
  {
    if (array_key_exists ('IncludeNumber', $_REQUEST))
      $include_number_checked = 'CHECKED';
    else
      $include_number_checked = '';
    
    if (array_key_exists ('IncludeName', $_REQUEST))
      $include_name_checked = 'CHECKED';
    else
      $include_name_checked = '';

    if (array_key_exists ('IncludeEmail', $_REQUEST))
      $include_email_checked = 'CHECKED';
    else
      $include_email_checked = '';

    if (array_key_exists ('IncludeGMFlag', $_REQUEST))
      $include_gm_flag_checked = 'CHECKED';
    else
      $include_gm_flag_checked = '';

    if (array_key_exists ('IncludeGMs', $_REQUEST))
      $include_gms_checked = 'CHECKED';
    else
      $include_gms_checked = '';

    if (array_key_exists ('IncludeNL', $_REQUEST))
      $include_nl_checked = 'CHECKED';
    else
      $include_nl_checked = '';

    $separator = ', ';
    if (array_key_exists ('separator', $_REQUEST))
    {
      if ('semicolon' == $_REQUEST['separator'])
	$separator = '; ';
    }

    if (', ' == $separator)
    {
      $comma_checked = 'CHECKED';
      $semicolon_checked = '';
    }
    else
    {
      $comma_checked = '';
      $semicolon_checked = 'CHECKED';
    }

    if (array_key_exists ('IncludeConfirmed', $_REQUEST))
      $include_confirmed_checked = 'CHECKED';
    else
      $include_confirmed_checked = '';

    if (array_key_exists ('IncludeWaitlisted', $_REQUEST))
      $include_waitlisted_checked = 'CHECKED';
    else
      $include_waitlisted_checked = '';
  }

  // Fetch the game title and suffix (if any)

  $sql = 'SELECT Events.Title, Events.Hours, Events.GameType, ';
  $sql .= ' Events.MinPlayersNeutral, Events.MaxPlayersNeutral, Events.PrefPlayersNeutral,';
  $sql .= ' Runs.TitleSuffix, Runs.StartHour, Runs.Day, Runs.ShowId';
  $sql .= ' FROM Runs, Events';
  $sql .= " WHERE Runs.RunId=$RunId AND Events.EventId=Runs.EventId";

  //  echo "$sql<P>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for game title and suffix failed for RunID $RunId");

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find game title and suffix for RunId $RunId");

  if (1 != mysql_num_rows ($result))
    return display_error ("RunId $RunId matched multiple games!");

  $row = mysql_fetch_object ($result);

  $Title = $row->Title;
  if ('' != $row->TitleSuffix)
    $Title .= " - $row->TitleSuffix";

  // if this is associated with a show, figure out the show id
  $ShowId = NULL;
  //echo "GameType: ".$row->GameType."<br>";
  if ($row->GameType == "Show")
    $ShowId = $EventId;
  else 
    $ShowId = $row->ShowId;


  $start_time = start_hour_to_am_pm ($row->StartHour);
  $end_time = start_hour_to_am_pm ($row->StartHour + $row->Hours);

  $Day = $row->Day;
  $Date = day_to_date ($Day);

  $max_male = 0;
  $max_female = 0;
  $max_neutral = $row->MaxPlayersNeutral;
  $total = $row->MaxPlayersNeutral;

  echo "<I><B><FONT SIZE='+2'>$Title</FONT></B></I><BR>\n";
  echo "<B><FONT SIZE='+1'>$Date&nbsp;&nbsp;&nbsp;$start_time - $end_time</FONT></B><P>\n";

  echo "Max: $total&nbsp;&nbsp;&nbsp;";

  switch ($OrderBy)
  {
    default:
    case ORDER_BY_SEQ:
      $order_by_text = 'Signup';
      $order_by_sql = 'Signup.SignupId';
      break;

    case ORDER_BY_NAME:
      $order_by_text = 'Player Name';
      $order_by_sql = 'Users.DisplayName';
      break;

  }

  // Get the list of GMs.  We'll want to know who they are.
  // if this is a show, it's more important to have performers, who are hooked in differently
  if ($ShowId == NULL)
  {
    $sql = 'SELECT GMs.UserId, GMs.Role ';
    $sql .= '  FROM GMs';
    $sql .= "  WHERE GMs.EventId=$EventId AND GMs.Role != 'performer'";
  }
  else 
  {
    $sql = 'SELECT GMs.UserId, GMs.Role ';
    $sql .= '  FROM Acts, GMs';
    $sql .= "  WHERE GMs.EventId=Acts.ActId AND Acts.ShowId=$ShowId";
    $sql .= '    AND GMs.Role="performer"';
  }
  //echo $sql;

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for Coordinator list for Event $EventId",
				$sql);

  $gms = array();

  while ($row = mysql_fetch_object ($result))
  {
    $gms[$row->UserId] = "$row->Role";
    //echo "Made GM: ".$row->UserId." Role: ".$row->Role."<br>";
  }
  // Fetch the list of confirmed and waitlisted users

  $status = array ();
  $conf_counter = 0;
  $wait_counter = 0;

  $sql = 'SELECT SignupId, State, Counted';
  $sql .= ' FROM Signup';
  $sql .= " WHERE Signup.RunId=$RunId";
  $sql .= '   AND Signup.State<>"Withdrawn"';
  $sql .= ' ORDER BY SignupId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query failed for signup list', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if ('N' == $row->Counted)
      $status[$row->SignupId] = "N/C";
    else
    {
      if ('Confirmed' == $row->State)
	$status[$row->SignupId] = sprintf ('%03d', ++$conf_counter);
      else
	$status[$row->SignupId] = sprintf ('%03d', ++$wait_counter);
    }
  }

  // Fetch the list of players signed up

  $sql = 'SELECT Users.UserId, Users.DisplayName, ';
  $sql .= ' Users.EMail,';
  $sql .= ' Signup.SignupId, Signup.Counted, Signup.State';
  $sql .= ' FROM Signup, Users';
  $sql .= " WHERE Signup.RunId=$RunId";
  $sql .= '   AND Users.UserId=Signup.UserId';
  $sql .= '   AND Signup.State<>"Withdrawn"';

  if ($csv)
  {
    $csv_checked = 'CHECKED';
    $html_checked = '';
  }
  else
  {
    $csv_checked = '';
    $html_checked = 'CHECKED';
  }

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", SCHEDULE_SHOW_SIGNUPS);
  echo "<INPUT TYPE=HIDDEN NAME=EventId VALUE=$EventId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=RunId VALUE=$RunId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=OrderBy VALUE=$OrderBy>\n";

  echo "<TABLE>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Display as</TD>\n";
  echo "    <TD>\n";
  echo "      <B>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=0 $html_checked>HTML Table</BR>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=1 $csv_checked>Comma Separated Values\n";
  echo "      </B>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Include fields:</TD>\n";
  echo "    <TD>\n";
  echo "      <B>\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeNumber $include_number_checked>&nbsp;Number\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeName $include_name_checked>&nbsp;Name\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeEmail $include_email_checked>&nbsp;EMail\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeGMFlag $include_gm_flag_checked>&nbsp;Running Flag\n<BR>";
  echo "      </B>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Include Participants:</TD>\n";
  echo "    <TD>\n";
  echo "      <B>\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeConfirmed $include_confirmed_checked>&nbsp;Confirmed\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeWaitlisted $include_waitlisted_checked>&nbsp;Waitlisted\n";
  echo "      <input type=checkbox name=IncludeGMs $include_gms_checked>&nbsp;Teachers/Panelists/Performers\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "  <tr valign=top>\n";
  echo "    <td>CSV Options</td>\n";
  echo "    <td><b>\n";
  echo "      <input type=checkbox name=IncludeNL $include_nl_checked>&nbsp;Line Break<br>\n";
  echo "      <input type=radio name=separator value=\"comma\" $comma_checked>&nbsp;Comma separated<br>\n";
  echo "      <input type=radio name=separator value=\"semicolon\" $semicolon_checked>&nbsp;Semicolon separated<br\n";
  echo "      <BR><INPUT TYPE=SUBMIT VALUE=\"Update\">\n";
  echo "    </b></TD>\n";
  echo "  </TR>\n";
  echo "</TABLE>\n";
  echo "<P>\n";

  if ($csv)
  {

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query failed for CSV', $sql);


    echo "<DIV class=\"sched_day0\" NOWRAP>\n";
    while ($row = mysql_fetch_object ($result))
    {
      if (empty ($gms[$row->UserId]))
	$gm = '&nbsp;';
      else
      {
	unset ($gms[$row->UserId]);
	if ('' == $include_gms_checked)
	  continue;
	$gm = 'GM';
      }

      $name = stripslashes (trim ("$row->DisplayName"));

      $s = substr ($row->State, 0, 1);

      // Skip to the next record if we're not displaying either confirmed
      // players (and this is a confirmed player) or waitlisted players
      // (and this is a waitlisted player)

      if (('' == $include_confirmed_checked) && ('C' == $s))
	continue;

      if (('' == $include_waitlisted_checked) && ('W' == $s))
	continue;

      // Display the appropriate information (as Comma Separated Values)

      if ($include_number_checked != '')
	printf ('%s%s%s', $row->State, $status[$row->SignupId], $separator);

      if ($include_name_checked != '')
	echo "\"$name\"$separator";

      if ($include_email_checked != '')
	echo "$row->EMail$separator";

      if ($include_gm_flag_checked != '')
	echo "$gm$separator";

      if ($include_nl_checked != '')
	echo "<BR>\n";
    }
    echo "</DIV>\n";
  } // if csv
  else
  {
    $conf_sql = $sql . "   AND Signup.State='Confirmed' ORDER BY $order_by_sql";
    $wait_sql = $sql . "   AND Signup.State='Waitlisted' ORDER BY $order_by_sql";

    //  echo "$sql<P>\n";

    $result = mysql_query ($conf_sql);
    if (! $result)
      return display_mysql_error ("Query for list of participants for run $RunId failed", $conf_sql);

    if (0 == mysql_num_rows ($result))
    {
      echo "No one is signed up.\n";
    }
    else
    {
      $can_edit = can_edit_game_info ();

      if ('' != $include_confirmed_checked)
	      show_signups_state (true, $EventId, $RunId, $order_by_text,
			    $OrderBy, $result, $status, $gms, $can_edit,
			    $include_number_checked, $include_name_checked,
			    $include_email_checked, $include_gm_flag_checked,
			    $include_gms_checked,
			    $include_confirmed_checked,
			    $include_waitlisted_checked);

      if ('' != $include_waitlisted_checked)
      {
	$result = mysql_query ($wait_sql);
	if (! $result)
	  return display_mysql_error ("Query for list of participants for run $RunId failed", $wait_sql);

	if (0 != mysql_num_rows ($result))
	  show_signups_state (false, $EventId, $RunId, $order_by_text,
			      $OrderBy, $result, $status, $gms, $can_edit,
			      $include_number_checked, $include_name_checked,
			      $include_email_checked, $include_gm_flag_checked,
			      $include_gms_checked,
			      $include_confirmed_checked,
			      $include_waitlisted_checked);
      }
    }
  }

  if ((sizeof ($gms) > 0) && ('' != $include_confirmed_checked))
  {
    echo "<P><B>Warning: The following Presenters are not signed up for this run:</B><BR>\n";
    foreach ($gms as $gmid => $name)
      echo "&nbsp;&nbsp;&nbsp;&nbsp;$name<BR>\n";
  }

  echo "<P>\n";
  printf ("Return to <A HREF=Schedule.php?action=%d&EventId=%d><I>%s</I></A>\n",
	  SCHEDULE_SHOW_GAME,
	  $EventId,
	  $Title);
  echo "<P>\n";
}

/*
 * show_all_signups
 *
 * Show the users that are signed up for any run of this event
 */

function show_all_signups ()
{
  $EventId = intval (trim ($_REQUEST['EventId']));

  if (! isset ($_REQUEST['OrderBy']))
    $OrderBy = ORDER_BY_NAME;
  else
    $OrderBy = intval (trim ($_REQUEST['OrderBy']));

  if (isset ($_REQUEST['CSV']))
    $csv = intval (trim ($_REQUEST['CSV']));
  else
    $csv = 0;

  if (array_key_exists ('FirstTime', $_REQUEST))
  {
    $include_name_checked = 'CHECKED';
    $include_email_checked = 'CHECKED';
    $include_confirmed_checked = 'CHECKED';
    $include_waitlisted_checked = 'CHECKED';
  }
  else
  {
    if (array_key_exists ('IncludeName', $_REQUEST))
      $include_name_checked = 'CHECKED';
    else
      $include_name_checked = '';


    if (array_key_exists ('IncludeEmail', $_REQUEST))
      $include_email_checked = 'CHECKED';
    else
      $include_email_checked = '';

    if (array_key_exists ('IncludeConfirmed', $_REQUEST))
      $include_confirmed_checked = 'CHECKED';
    else
      $include_confirmed_checked = '';

    if (array_key_exists ('IncludeWaitlisted', $_REQUEST))
      $include_waitlisted_checked = 'CHECKED';
    else
      $include_waitlisted_checked = '';
  }

  // Fetch the game title

  $Title = $_SESSION['GameTitle'];

  echo "<I><B><FONT SIZE='+2'>$Title</FONT></B></I><P>\n";

  switch ($OrderBy)
  {
    default:
    case ORDER_BY_SEQ:
      $order_by_text = 'Signup';
      $order_by_sql = 'Signup.SignupId';
      break;

    case ORDER_BY_NAME:
      $order_by_text = 'Player Name';
      $order_by_sql = 'Users.LastName, Users.FirstName';
      break;

  }

  // Get the list of GMs.  We'll want to know if any aren't signed up

  $sql = 'SELECT GMs.UserId, Users.DisplayName ';
  $sql .= '  FROM Users, GMs';
  $sql .= "  WHERE GMs.EventId=$EventId";
  $sql .= '    AND Users.UserId=GMs.UserId AND GMs.Role != \'performer\'';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for GM list for Event $EventId",
				$sql);

  $gms = array();

  while ($row = mysql_fetch_object ($result))
    $gms[$row->UserId] = "$row->DisplayName";

  // Fetch the list of confirmed and waitlisted users

  $status = array ();
  $conf_counter = 0;
  $wait_counter = 0;

  $sql = 'SELECT Signup.SignupId, Signup.State, Signup.Counted';
  $sql .= ' FROM Signup, Runs';
  $sql .= " WHERE Runs.EventId=$EventId";
  $sql .= '   AND Signup.RunId=Runs.RunId';
  $sql .= '   AND Signup.State<>"Withdrawn"';
  $sql .= ' ORDER BY SignupId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query failed for signup list', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if ('N' == $row->Counted)
      $status[$row->SignupId] = "N/C";
    else
    {
      if ('Confirmed' == $row->State)
	$status[$row->SignupId] = sprintf ('%03d', ++$conf_counter);
      else
	$status[$row->SignupId] = sprintf ('%03d', ++$wait_counter);
    }
  }

  // Fetch the list of players signed up

  $sql = 'SELECT DISTINCT Users.UserId, Users.DisplayName,';
  $sql .= ' Users.EMail, Runs.Day, Runs.StartHour, ';
  $sql .= ' Signup.SignupId';
  $sql .= ' FROM Signup, Runs, Users';
  $sql .= " WHERE Runs.EventId=$EventId";
  $sql .= '   AND Signup.RunId=Runs.RunId';
  $sql .= '   AND Users.UserId=Signup.UserId';

  if ($csv)
  {
    $csv_checked = 'CHECKED';
    $html_checked = '';
  }
  else
  {
    $csv_checked = '';
    $html_checked = 'CHECKED';
  }

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n",
	  SCHEDULE_SHOW_ALL_SIGNUPS);
  echo "<INPUT TYPE=HIDDEN NAME=EventId VALUE=$EventId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=OrderBy VALUE=$OrderBy>\n";

  echo "<TABLE>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Display as</TD>\n";
  echo "    <TD>\n";
  echo "      <B>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=0 $html_checked>HTML Table</BR>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=1 $csv_checked>Comma Separated Values\n";
  echo "      </B>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Include fields:</TD>\n";
  echo "    <TD>\n";
  echo "      <B>\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeName $include_name_checked>&nbsp;Name\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeEmail $include_email_checked>&nbsp;EMail<BR>\n";
  echo "      </B>\n";
  echo "      <INPUT TYPE=SUBMIT VALUE=\"Update\">\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "</TABLE>\n";
  echo "<P>\n";

  if ($csv)
  {
    $sql .= '   AND Signup.State<>"Withdrawn"';

    //    echo "$sql<p>\n";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query failed for CSV players', $sql);


    echo "<DIV class=\"class11\" NOWRAP>\n";
    while ($row = mysql_fetch_object ($result))
    {
      if (empty ($gms[$row->UserId]))
	$gm = '&nbsp;';
      else
      {
	$gm = 'GM';
	unset ($gms[$row->UserId]);
      }

      $name = stripslashes (trim ("$row->DisplayName"));

      if (0 == $row->BirthYear)
	$age = '?';
      else
	$age = birth_year_to_age ($row->BirthYear);

      if ($include_name_checked != '')
	echo "\"$name\",";

      if ($include_email_checked != '')
	echo "$row->EMail,";

      echo " ".$row->Day.", ".start_hour_to_am_pm($row->StartHour);
      echo "<BR>\n";
    }
    echo "</DIV>\n";
  }
  else
  {
    $conf_sql = $sql . "   AND Signup.State='Confirmed' ORDER BY $order_by_sql";
    $wait_sql = $sql . "   AND Signup.State='Waitlisted' ORDER BY $order_by_sql";

    //    echo "$conf_sql<P>\n";

    $result = mysql_query ($conf_sql);
    if (! $result)
      return display_mysql_error ("Query for list of confirmed participants for event $EventId failed",
				  $conf_sql);

    if (0 == mysql_num_rows ($result))
    {
      echo "No players are signed up for this event\n";
    }
    else
    {
      $can_edit = can_edit_game_info ();

      show_signups_state (true, $EventId, 0, $order_by_text,
			  $OrderBy, $result, $status, $gms, $can_edit,
			  '', $include_name_checked,
			  $include_email_checked, '',
			  '',
			  $include_confirmed_checked,
			  $include_waitlisted_checked, TRUE);

      $result = mysql_query ($wait_sql);
      if (! $result)
	return display_mysql_error ("Query for list of waitlisted players for event $EventId failed",
				  $wait_sql);

      if (0 != mysql_num_rows ($result))
	show_signups_state (false, $EventId, 0, $order_by_text,
			    $OrderBy, $result, $status, $gms, $can_edit,
			    '', $include_name_checked,
			    $include_email_checked, '',
			    '',
			    $include_confirmed_checked,
			    $include_waitlisted_checked, TRUE);
    }
  }

  if (sizeof ($gms) > 0)
  {
    echo "<P><B>The following Presenters are not signed up for this event:</B><BR>\n";
    foreach ($gms as $gmid => $name)
      echo "&nbsp;&nbsp;&nbsp;&nbsp;$name<BR>\n";
  }

  echo "<P>\n";
  printf ("Return to <A HREF=Schedule.php?action=%d&EventId=%d><I>%s</I></A>\n",
	  SCHEDULE_SHOW_GAME,
	  $EventId,
	  $Title);
  echo "<P>\n";
}

/*
 * display_user_information
 *
 * Display information about a user signed up for the game
 */

function display_user_information ()
{
  $SignupId = intval (trim ($_REQUEST['SignupId']));
  $EventId = intval (trim ($_REQUEST['EventId']));
  $RunId = intval (trim ($_REQUEST['RunId']));

  // Fetch the information about the user

  $sql = 'SELECT Users.*';
  $sql .= ' FROM Signup, Users';
  $sql .= " WHERE Signup.SignupId=$SignupId";
  $sql .= '   AND Users.UserId=Signup.UserId';
echo 
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');

  // We should match precisely 1 user

  if (1 != mysql_num_rows ($result))
    return display_error ('Failed to find entry for user ' . $user_id);

  $row = mysql_fetch_object ($result);

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", SCHEDULE_UPDATE_SIGNUP);
  echo "<INPUT TYPE=HIDDEN NAME=SignupId VALUE=$SignupId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=EventId VALUE=$EventId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=RunId VALUE=$RunId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=FirstTime VALUE=1>\n";

  if ('' != $row->EMail)
    $EMail = "<A HREF=mailto:$row->EMail>$row->EMail</A>";
  else
    $EMail = '';


  print ("<TABLE BORDER=0>\n");
  echo "  <TR>\n";
  echo "    <TD COLSPAN=2 BGCOLOR=\"CCFFFF\">\n";
  echo "      &nbsp;<BR>\n";
  echo "      <B>$row->DisplayName</B>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
  display_text_info ('First Name', $row->FirstName);
  display_text_info ('Last Name', $row->LastName);
  display_text_info ('Stage Name', $row->StageName);

  $address = $row->Address1;
  if ('' != $row->Address2)
    $address .= ', ' . $row->Address2;
  display_text_info ('Address', $address);
  display_text_info ('City', $row->City);
  display_text_info ('State / Province', $row->State);
  display_text_info ('Zipcode', $row->Zipcode);
  display_text_info ('Country', $row->Country);
  display_text_info ('EMail', $EMail);
  display_text_info ('Daytime Phone', $row->DayPhone);
  display_text_info ('Evening Phone', $row->EvePhone);
  display_text_info ('Best Time to Call', $row->BestTime);
  display_text_info ('Preferred Contact', $row->PreferredContact);

  // Save the UserId for a moment

  $UserId = $row->UserId;

  // Select the information about this run

  // Fetch the game title and suffix (if any)

  $sql = 'SELECT Events.Title, Events.EventId, Events.Hours, Events.GameType,';
  $sql .= ' Runs.TitleSuffix, Runs.StartHour, Runs.Day, Runs.ShowId,';
  $sql .= ' Signup.State, Signup.Counted';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= " WHERE Signup.SignupId=$SignupId";
  $sql .= '  AND Runs.RunId=Signup.RunId';
  $sql .= '  AND Events.EventId=Runs.EventId';

  //    echo "$sql<P>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for event title and suffix failed for SignupID $SignId");

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find event title and suffix for SignupId $SignupId");

  if (1 != mysql_num_rows ($result))
    return display_error ("SignupId $SignupId matched multiple events or runs!");

  $row = mysql_fetch_object ($result);

  // if this is associated with a show, figure out the show id
  $ShowId = NULL;
  if ($row->GameType == "Show")
    $ShowId = $row->EventId;
  else 
    $ShowId = $row->ShowId;

  $Title = $row->Title;
  if ('' != $row->TitleSuffix)
    $Title .= " - $row->TitleSuffix";

  $start_time = start_hour_to_am_pm ($row->StartHour);
  $end_time = start_hour_to_am_pm ($row->StartHour + $row->Hours);

  $Day = $row->Day;
  $Date = day_to_date ($Day);

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2 BGCOLOR=\"CCFFFF\">\n";
  echo "      &nbsp;<BR>\n";
  echo "      <B>$Title<BR>\n";
  echo "      $Date&nbsp;&nbsp;&nbsp;$start_time - $end_time</B>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";

  if (user_is_gm_for_game ($UserId, $row->EventId))
    $gm_state = 'is';
  else
    $gm_state = 'is not';

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>\n";
  echo "      The attendee is <B>$row->State</B> for this run.<BR>\n";
  if ( $ShowId != NULL && user_is_performer_for_show($UserId, $ShowId))
    echo "     The attendee is a performer in this show";
  else
    echo "      The attendee <B>$gm_state</B> a teacher, panelist, coordinator, etc. for this event.\n";

  echo "    </TD>\n";
  echo "  </TR>\n";

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>\n";

 if ('Y' == $row->Counted)
    $checked = 'CHECKED';
  else
    $checked = '';
  echo "      <INPUT TYPE=CHECKBOX NAME=Counted $checked> Count this attendee towards totals for this run\n";
  echo "    </TD>\n";
  echo "  </TR>\n";

  if (user_has_priv (PRIV_STAFF))
    form_submit2 ('Update User for this Run', 'Force user into event',
		  'ForceUser');
  else
    form_submit ('Update User for this Run');


  echo "</TABLE>\n";
  echo "</FORM>\n";

  return false;
}

/*
 * update_signup_accept_player_from_waitlist
 *
 * If we're updating a player fron Counted to Not Counted, and the player's
 * signup record currently says he or she is Counted, and the player is
 * confirmed for the game, scan the wait list for another player to
 * move into the slot
 */

function update_signup_accept_player_from_waitlist ($SignupId)
{
  // See whether this user is being changed from Counted to Not Counted

  $sql = 'SELECT RunId, Counted, Gender, State';
  $sql .= " FROM Signup WHERE SignupId=$SignupId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for Counted State");

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ("Failed to find signup record for $SignupId");

  // If there's no change, we're done

  if ('N' == $row->Counted)
    return true;

  // If the user is not confirmed for the game, we're done

  if ('Confirmed' != $row->State)
    return true;

  // Accept a player from the waitlist for this game, if there are any

  $Gender = $row->Gender;
  $RunId = $row->RunId;

  //  echo "RunID: $RunId, Gender: $Gender<p>\n";

  $sql = 'SELECT Runs.EventId, Runs.Day, Runs.StartHour,';
  $sql .= ' Events.Title, Events.Hours, Events.CanPlayConcurrently,';
  $sql .= ' Events.MaxPlayersNeutral';
  $sql .= ' FROM Runs, Events';
  $sql .= " WHERE Runs.RunId=$RunId";
  $sql .= '   AND Events.EventId=Runs.EventId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for run $RunId", $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ("Failed to find information for run $RunId");

  $male_slots = 0;
  $female_slots = 0;
  $neutral_slots = 0;

  if (0 != $row->MaxPlayersNeutral)
    $neutral_slots = 1;
  else
  {
    if ('Male' == $Gender)
      $male_slots = 1;
    else
      $female_slots = 1;
  }

  //  echo "male: $male_slots, female: $female_slots, neutral: $neutral_slots<P>\n";

  //  echo "Title: $row->Title\n";
  //  echo "Day: $row->Day\n";
  
  accept_players_from_waitlist_for_run ($row->EventId, $RunId, $RunId,
					$row->Title, $row->Day,
					$row->StartHour, $row->Hours,
					$row->CanPlayConcurrently,
					$male_slots, $female_slots, $neutral_slots);
}

/*
 * calculate_available_slots
 *
 * Calculate how many slots are available
 */

function calculate_available_slots ($cur_male, $cur_female,
				    $max_male, $max_female, $max_neutral,
				    &$avail_male, &$avail_female,
				    &$avail_neutral)
{
  // Start with all neutral slots available.  We'll subtract from that
  // if all the gender-specific slots are taken
  
  $avail_neutral = $max_neutral- $cur_male;
  return $avail_neutral > 0;

}



/*
 * update_signup_locked
 *
 * Portion of update_signup that must occur with the tables locked
 */

function update_signup_locked ($SignupId, $Counted, $ForceUser)
{
  // If someone is being changed from Counted to Not Counted, we may need
  // to scan the waitlist for the next available player (if any)

  if ('N' == $Counted)
    update_signup_accept_player_from_waitlist ($SignupId);

  // Update this record

  $sql = "UPDATE Signup SET Counted='$Counted',";
  if ($ForceUser)
    $sql .= ' PrevState=State, State="Confirmed",';
  $sql .= ' UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= " WHERE SignupId=$SignupId";

  //    echo $sql;

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Failed to update signup table for ID $SignupId");

}

/*
 * update_signup
 *
 * Update the Counted field of a signup
 */

function update_signup ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (true);

  $SignupId = intval (trim ($_POST['SignupId']));
  $RunId = intval (trim ($_POST['RunId']));
  $EventId = intval (trim ($_POST['EventId']));

  $Counted = 'N';
  if (array_key_exists ('Counted', $_REQUEST))
  {
    if ('on' == $_REQUEST['Counted'])
      $Counted = 'Y';
  }

  $ForceUser = isset ($_POST['ForceUser']);

  // Lock the Signup table to make sure that if there are two users trying
  // to get the last slot in a game, then only one will succeed.  A READ lock
  // allows clients that only read the table to continue, but will block
  // clients that attempt to write to the table

  $result = mysql_query ('LOCK TABLE Signup WRITE, Users READ, Runs READ, Events READ, GMs READ');
  if (! $result)
    return display_mysql_error ('Failed to lock the Signup table');

  if (isset ($_POST['SwapGender']))
    swap_gender_locked ($SignupId,
			$RunId,
			$EventId,
			$_POST['SwappedGender']);
  else
    update_signup_locked ($SignupId, $Counted, $ForceUser);

  // Unlock the Signup table so that other queries can access it

  $result = mysql_query ('UNLOCK TABLES');
  if (! $result)
    return display_mysql_error ('Failed to unlock the Signup table');

  return TRUE;
}

/*
 * display_gm_list
 *
 * Show the list of GMs for an event and let the user add or remove GMs from
 * it
 */

function display_gm_list ()
{
  $EventId = intval (trim ($_REQUEST ['EventId']));
  $Title = $_SESSION['GameTitle'];
  
  $sql = 'SELECT * FROM Events';
  $sql .= "  WHERE EventId=$EventId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for comped people failed", $sql);
  $event_row = mysql_fetch_object($result);

  global $GM_TYPES;
  global $PANELIST_TYPE;
  $gms = $GM_TYPES[$event_row->GameType];

  display_header ("$gms for <I>$Title</I>");

  if ($event_row->GameType == "Panel")
      printf ('<p><b>Add</b></a> <a href="Schedule.php?action=%d&EventId=%d&Role=%s">moderator</a>'.
        ' or <a href="Schedule.php?action=%d&EventId=%d&Role=%s">panelist</a>. ' .
	    "from registered users<p>\n",
	    ADD_GM, $EventId, $PANELIST_TYPE[2],
	    ADD_GM, $EventId, $PANELIST_TYPE[1]);

  else
    printf ('<p><a href="Schedule.php?action=%d&EventId=%d"><b>Add</b></a> '.$gms.
	  " from registered users<p>\n",
	  ADD_GM,
	  $EventId);

  $sql = 'SELECT COUNT(*) AS CompCount FROM Users';
  $sql .= "  WHERE Users.CompEventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for comped people failed", $sql);
  $row = mysql_fetch_object($result);

  $comped_count = $row->CompCount;

  $sql = 'SELECT Users.DisplayName, Users.CompEventId,';
  $sql .= '  Users.CanSignup, Users.UserId, GMs.Role,';
  $sql .= '  GMs.GMId, GMs.Submitter, GMs.DisplayAsGM, GMs.DisplayEMail,';
  $sql .= '  GMs.ReceiveConEMail, GMs.ReceiveSignupEMail';
  $sql .= '  FROM GMs, Users';
  $sql .= "  WHERE GMs.EventId=$EventId";
  $sql .= '    AND Users.UserId=GMs.UserId AND GMs.Role != \'performer\'';
  $sql .= '  ORDER BY Users.DisplayName';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for ".$gms." failed", $sql);

  if (0 != mysql_num_rows ($result))
  {
    $text = "Submitted Bid";
    if ($event_row->GameType == "Panel")
      $text = "Current Role";
    echo "<TABLE BORDER=1 CELLPADDING=5>\n";
    echo "  <TR VALIGN=BOTTOM>\n";
    echo "    <TH>#</TH>\n";
    echo "    <TH>Name</TH>\n";
    echo "    <TH>$text</TH>\n";
    echo "    <TH>Display with Event</TH>\n";
    echo "    <TH>Display EMail Address</TH>\n";
    echo "    <TH>Receive EMail From Con</TH>\n";
    echo "    <TH>Receive EMail on Signup or Withdraw</TH>\n";
    // echo "    <TH>Comp'd For This Game</TH>\n";
    echo "  </TR>\n";

    $i = 1;

    while ($row = mysql_fetch_object ($result))
    {
      if ($EventId == $row->CompEventId)
	$comped = 'X';
      else
      {
	if (($comped_count < COMPS_PER_GAME) && ('Alumni' == $row->CanSignup))
	{
	  $comped = sprintf ('<a href="Schedule.php?action=%d&UserId=%d&' .
			     'EventId=%d">Comp this '.$gms.'</a>',
			     SCHEDULE_COMP_USER_FOR_EVENT,
			     $row->UserId,
			     $EventId);
	}
	else
	  $comped = '&nbsp;';
      }

      echo "  <TR ALIGN=CENTER>\n";
      echo "    <TD>$i</TD>\n";
      $href = 'Schedule.php?action=' . EDIT_GM . "&GMId=$row->GMId&EventId=$EventId";
      echo "    <TD ALIGN=LEFT><A HREF=$href>$row->DisplayName</A></TD>\n";
      if ($event_row->GameType == "Panel")
        echo "   <TD ALIGN=CENTER>$row->Role&nbsp;</TD>";
      else
        yn_to_x_column ($row->Submitter);
      yn_to_x_column ($row->DisplayAsGM);
      yn_to_x_column ($row->DisplayEMail);
      yn_to_x_column ($row->ReceiveConEMail);
      yn_to_x_column ($row->ReceiveSignupEMail);
      //echo "    <TD>$comped</TD>\n";
      echo "  </TR>\n";

      $i++;
    }
    echo "</TABLE><P>\n";
  }

  //    It appears this code is mostly old checks for comped or paid entires.  Since we're
  //	overhauling all of this for Ticketing, going to remove the feature for now.  -MDB
  

  $sql = 'SELECT Events.Author';
  $sql .= '  FROM Events';
  $sql .= "  WHERE EventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for GMs failed", $sql);

  $row = mysql_fetch_object ($result);

  /*
  if (strlen($row->Author) > 0 )
  {
    echo "NOTE:  The teacher(s) from the original Bid were <b>".$row->Author."</b><br><br>";
  }
  */
  
  // we only need a panelist setting form if this is a panel,
  // teachers are already booked
  if ( $event_row->GameType == "Panel" ) {
    echo "<h2>Select from Panelist Bids</h2>";
    echo "<FORM METHOD=POST ACTION=Panels.php?action=".ADD_GM.">\n";
    form_hidden_value("EventId",$EventId);
    $sql = "SELECT BidId FROM Bids WHERE EventId=$EventId";
    $result = mysql_query ($sql);
    if (! $result)
      return display_error ("Cannot query title for EventId $EventId: " . mysql_error ());

    if (0 == mysql_num_rows ($result))
      return display_error ("Cannot find EventId $EventId in the database!");

    if (1 != mysql_num_rows ($result))
      return display_error ("EventId $EventId matched more than 1 row!");

    $bidrow = mysql_fetch_object ($result);

    display_schedule_pref($bidrow->BidId, $event_row->GameType == "Panel", TRUE );
    echo "  <br><br>";
    echo "      <INPUT TYPE=SUBMIT VALUE=\"Add Panelists\">\n";
    echo "</FORM>";
  }


  printf ("<A HREF=Schedule.php?action=%d&EventId=%d>Return</A>",
	  SCHEDULE_SHOW_GAME,
	  $EventId);
  echo " to <I>$Title</I> page<P>\n";
}

/*
 * comp_user_for_event_locked
 *
 * This function assumes that the Users table is write locked.
 *
 * Count the number of registrations comped for the event.  If
 * it's less than COMPS_PER_GAME, mark the user comped.
 */

function comp_user_for_event_locked ($EventId, $UserId)
{
  $sql = 'SELECT COUNT(*) AS CompCount FROM Users';
  $sql .= "  WHERE Users.CompEventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for comped GMs failed", $sql);
  $row = mysql_fetch_object($result);

  if ($row->CompCount >= COMPS_PER_GAME)
    return display_error ("$row->CompCount registrations are already comped " .
			  "for this event");

  $sql = 'SELECT LastName, FirstName, CanSignup';
  $sql .= " FROM Users WHERE UserId=$UserId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for status of user $UserId failed",
				$sql);
  $row = mysql_fetch_object ($result);

  if ('Alumni' != $row->CanSignup)
  {
    $name = trim ("$row->FirstName $row->LastName");
    return display_error ("$name is not eligible to be comped");
  }

  $sql = "UPDATE Users SET CanSignup='Comp', CompEventId=$EventId,";
  $sql .= "  CanSignupModified=NULL, Modified=NULL,";
  $sql .= "  CanSignupModifiedId=" . $_SESSION[SESSION_LOGIN_USER_ID] . ',';
  $sql .= "  ModifiedBy=" . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= "  WHERE UserId=$UserId";

  //  echo "Comp: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ("Comp update failed for user $UserId");
}

/*
 * comp_user_for_event
 *
 * Lock the Users table, the comp the user
 */

function comp_user_for_event()
{
  $EventId = intval (trim ($_REQUEST ['EventId']));
  $UserId = intval (trim ($_REQUEST ['UserId']));

  // We lock the Users table to make sure that too many folks don't get
  // comped for this event

  $result = mysql_query ('LOCK TABLE Users Write');
  if (! $result)
  {
    display_mysql_error ('Failed to lock the User table');
  }
  else
  {
    comp_user_for_event_locked ($EventId, $UserId);
    $result = mysql_query ('UNLOCK TABLES');
    if (! $result)
      display_mysql_error ('Failed to unlock the User table');
  }


  display_gm_list();
}

function yn_to_x_column ($value)
{
  if ('Y' == $value)
    $disp = 'X';
  else
    $disp = '&nbsp;';

  echo "    <TD>$disp</TD>\n";
}

/*
 * select_user_as_gm
 *
 * Display list of users at the Con, highlighting those who are GMs for this
 * game.  They won't be able to be selected.
 */

function select_user_as_gm ()
{
  $EventId = intval (trim ($_REQUEST ['EventId']));
  $Title = $_SESSION['GameTitle'];

  // Get a list of GMs for the game.  They'll be highlighted

  $sql = 'SELECT DISTINCT GMs.UserId';
  $sql .= '  FROM GMs';
  $sql .= "  WHERE GMs.EventId=$EventId AND GMs.Role != 'performer'";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of GMs');

  $highlight = array ();

  while ($row = mysql_fetch_object ($result))
  {
    $highlight[$row->UserId] = 'BGCOLOR="#FFCCCC"';
  }

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", ADD_GM);
  printf ("<INPUT TYPE=HIDDEN NAME=EventId VALUE=%d>\n", $EventId);
  $alumni = include_alumni ();
  echo "<INPUT TYPE=SUBMIT VALUE=\"Update\"><BR>\n";
  echo "</FORM>\n";

  $link = sprintf ('Schedule.php?action=%d&EventId=%d&Seq=%d',
		   PROCESS_ADD_GM,
		   $EventId,
		   increment_sequence_number ());

  if (isset($_REQUEST ['Role']))
    $link .= "&Role=".$_REQUEST ['Role'];

  select_user ("Select User to run <I>$Title</I>",
	       $link,
	       FALSE,
	       FALSE,
	       $highlight,
	       0 == $alumni);
}

/*
 * process_add_gm
 *
 * Called when the user has selected a user to be added as a GM for a game
 */

function process_add_gm ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return true;

  $EventId = intval (trim ($_REQUEST ['EventId']));
  $UserId = intval (trim ($_REQUEST ['UserId']));
  $Role = trim ($_REQUEST ['Role']);

  // Get the list of runs and check for conflicts

  $sql = 'SELECT Events.Title, Events.Hours, Events.CanPlayConcurrently,';
  $sql .= ' Runs.StartHour, Runs.Day';
  $sql .= ' FROM Runs, Events';
  $sql .= ' WHERE Events.EventId=' . $EventId;
  $sql .= '  AND Events.EventId=Runs.EventId';

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Cannot query database for run information');
    return SIGNUP_FAIL;
  }
  $waitlist_conflicts = array ();

  // Extract the event information
  while ($row = mysql_fetch_object ($result))
  { 
  //echo "Checking conflict for ".$row->Title;
    $game_title = $row->Title;
    $game_hours = $row->Hours;
    $game_day = $row->Day;
    $game_start_hour = $row->StartHour;
    $game_end_hour = $row->StartHour + $row->Hours;

    $can_play_game_concurrently = $row->CanPlayConcurrently;
    if ($can_play_game_concurrently == "N")
    {
      $status = check_for_conflicts($UserId, $game_start_hour, $game_end_hour, $game_day, 
                        $waitlist_conflicts, 0, TRUE);
                        
      if ($status == SIGNUP_FAIL)
        display_error("The run with a conflict for ".$game_title." is at ".
            start_hour_to_am_pm ($game_start_hour)." for ".$game_hours." blocks.<hr>");
    }
	if (sizeof($waitlist_conflicts) > 1)
	{
      echo "The presenter is currently waitlisted for the following games which conflict\n";
      echo "with <I>$Title</I>:\n";

      echo "<UL>\n";
      foreach ($waitlist_conflicts as $k=>$v)
        echo "<LI>$v\n";
      echo "</UL>\n";
    }

  }
  
  // Make him a GM

  $sql = "INSERT INTO GMs SET EventId=$EventId,";
  $sql .= " UserId=$UserId,";
  $sql .= ' UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= ', Role=\'' . $Role.'\'';

  $insert_result = mysql_query ($sql);
  if (! $insert_result)
    return display_mysql_error ("GM Insertion failed", $sql);

  return TRUE;
}

/*
 * display_gm_information
 *
 */

function display_gm_information ()
{
  if (empty ($_REQUEST['GMId']))
    return display_error ('GMId not specified');

  $GMId = intval (trim ($_REQUEST['GMId']));
  $EventId = intval (trim ($_REQUEST['EventId']));

  // Fetch the information about the user

  $sql = "SELECT Users.*,";
  $sql .= "GMs.DisplayAsGM, GMs.DisplayEMail, GMs.ReceiveConEMail,";
  $sql .= "  GMs.ReceiveSignupEMail";
  $sql .= "  FROM Users, GMs";
  $sql .= "  WHERE GMs.GMId=$GMId";
  $sql .= "    AND Users.UserId=GMs.UserId AND GMs.Role != 'performer'";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');

  // We should match precisely 1 user

  if (1 != mysql_num_rows ($result))
    return display_error ('Failed to find entry for GM ' . $GMId);

  $row = mysql_fetch_object ($result);

  echo "<FORM METHOD=POST ACTION=Schedule.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", SCHEDULE_UPDATE_GM);
  echo "<INPUT TYPE=HIDDEN NAME=GMId VALUE=$GMId>\n";
  echo "<INPUT TYPE=HIDDEN NAME=EventId VALUE=$EventId>\n";

  if ('' != $row->EMail)
    $EMail = "<A HREF=mailto:$row->EMail>$row->EMail</A>";
  else
    $EMail = '';

  print ("<TABLE BORDER=0>\n");
  echo "  <TR>\n";
  echo "    <TD COLSPAN=2 BGCOLOR=\"CCFFFF\">\n";
  echo "      &nbsp;<BR>\n";
  echo "      <B>$row->DisplayName</B>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";

  display_text_info ('Name',"$row->FirstName $row->LastName");

  //display_text_info ('Nickname', $row->Nickname);
  //display_text_info ('Age', birth_year_to_age ($row->BirthYear));
  //display_text_info ('Gender', $row->Gender);
  $address = $row->Address1;
  if ('' != $row->Address2)
    $address .= ', ' . $row->Address2;
  display_text_info ('Address', $address);
  display_text_info ('City', $row->City);
  display_text_info ('State / Province', $row->State);
  display_text_info ('Zipcode', $row->Zipcode);
  display_text_info ('Country', $row->Country);
  display_text_info ('EMail', $EMail);
  display_text_info ('Daytime Phone', $row->DayPhone);
  display_text_info ('Evening Phone', $row->EvePhone);
  display_text_info ('Best Time to Call', $row->BestTime);
  display_text_info ('Preferred Contact', $row->PreferredContact);

  echo "  <tr>\n";
  echo "    <td colspan=\"2\">\n";	// 

  form_checkbox ('DisplayAsGM', 'Y' == $row->DisplayAsGM);
  echo " Display as Presenter<br>\n";
  form_checkbox ('DisplayEMail', 'Y' == $row->DisplayEMail);
  echo " Display EMail Address<br>\n";
  form_checkbox ('ReceiveConEMail', 'Y' == $row->ReceiveConEMail);
  echo " Receive mail from Con<br>\n"; 
  form_checkbox ('ReceiveSignupEMail', 'Y' == $row->ReceiveSignupEMail);
  echo " Receive mail on Signup or Withdrawal<br>\n";
		 
  echo "    </td>\n";
  echo "  </tr>\n";

  form_submit2 ('Update Settings', 'Remove Presenter', 'Remove');

  echo "</table>\n";
  echo "</form>\n";

  return false;
}

/*
 * checkbox_to_yn
 *
 * Translate a checkbox setting to a value for SQL
 */

function checkbox_to_yn ($key)
{
  if ('on' == $_POST[$key])
    return 'Y';
  else
    return 'N';
}

function build_sql_yn_from_checkbox ($key, $prefix_comma=TRUE)
{
  if ($prefix_comma)
    $res = ',';
  else
    $res = '';

  $res .= $key . '=';

  if ('on' == $_POST[$key])
    $res .= '"Y"';
  else
    $res .= '"N"';

  //  echo "KEY: $key, RESULT: $res<BR>";

  return $res;
}

/*
 * update_gm
 *
 * Update the GM settings
 */

function update_gm ()
{
  // If we're out of sequence, don't do anything

  if (out_of_sequence ())
    return display_sequence_error (true);

  if (empty ($_REQUEST['GMId']))
    return display_error ('GMId not specified');

  $GMId = intval (trim ($_REQUEST['GMId']));

  // See if we've been asked to remove this GM

  if (isset ($_POST['Remove']))
  {
    $sql = "DELETE FROM GMs WHERE GMId=$GMId";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to delete GM $GMId");

    return TRUE;
  }

  $sql = 'UPDATE GMs SET ';
  $sql .= build_sql_yn_from_checkbox ('DisplayAsGM', FALSE);
  $sql .= build_sql_yn_from_checkbox ('DisplayEMail');
  $sql .= build_sql_yn_from_checkbox ('ReceiveConEMail');
  $sql .= build_sql_yn_from_checkbox ('ReceiveSignupEMail');
  $sql .= " WHERE GMId=$GMId";

  //  echo $sql;

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Failed to update GM $GMId");

  return TRUE;
}

/*
 * check_if_away
 *
 * Check to see if the user will be away during the a run of an event
 */

function check_if_away ($day, $start_hour, $hours)
{
  // Get the user's away record

  $sql = 'SELECT * FROM Away WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for away record failed');

  // If there's no record of away hours, there's no conflict

  if (0 == mysql_num_rows ($result))
      return false;

  // Fetch the away record as an array for easy access

  $row = mysql_fetch_array ($result);

  // Start by checking if they've said they'll be away all day

  if (1 == $row[$day])
    return true;

  // Check each hour of the event

  for ($h = $start_hour; $h < $start_hour + $hours; $h++)
  {
    $k = sprintf ('%s%02d', $day, $h);
    if (1 == $row[$k])
      return true;
  }

  // I guess they'll be there

  return false;
}
/*
 * freeze_gender_balance
 *
 * Allow GMs to freeze the gender balance of their games at the current
 * levels of signed up players.
 */

function freeze_gender_balance ()
{
  // Extract the EventId and build the query

  $EventId = intval (trim ($_REQUEST['EventId']));
  if (0 == $EventId)
    return false;

  // Start by getting the maximum values for males, females and neutrals

  $sql = 'SELECT Title, MaxPlayersMale, MaxPlayersFemale, MaxPlayersNeutral';
  $sql .= ' FROM Events';
  $sql .= " WHERE EventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query database');

  // We should have matched exactly one game

  if (1 != mysql_num_rows ($result))
    return display_error ("Failed to find entry for EventId $EventId");

  $row = mysql_fetch_object ($result);

  $Title = $row->Title;
  $MaxMale = $row->MaxPlayersMale;
  $MaxFemale = $row->MaxPlayersFemale;
  $MaxNeutral = $row->MaxPlayersNeutral;

  $max_signups = $MaxMale + $MaxFemale + $MaxNeutral;

  // Get the currnet count of males and females. There should only be
  // one run for this event...

  $sql = 'SELECT Signup.Gender, COUNT(Signup.Gender) AS Count';
  $sql .= ' FROM Signup, Runs';
  $sql .= " WHERE Runs.EventId=$EventId";
  $sql .= '   AND Signup.RunId=Runs.RunId';
  $sql .= '   AND Signup.State="Confirmed"';
  $sql .= '   AND Signup.Counted="Y"';
  $sql .= ' GROUP BY Gender';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Attempt to count confirmed signups for EventId $EventId Failed', $sql);

  $confirmed = array ();
  $confirmed['Male'] = 0;
  $confirmed['Female'] = 0;

  while ($row = mysql_fetch_object ($result))
    $confirmed[$row->Gender] = $row->Count;

  display_header ('Freeze Gender Balance');
  printf ("<p>\nThere are currently %d Male, %d Female and %d Neutral\n",
	  $MaxMale, $MaxFemale, $MaxNeutral);
  echo "roles for <i>$Title</i>\n";
  echo "<p>\nIf you freeze the gender balance, the neutral roles will be\n";
  echo "changed to match the current balance of players signed up.  If a\n";
  echo "player withdraws from the game, this will force the website to\n";
  echo "select the first player of the same gender on the waitlist instead\n";
  echo "of simply the first player in line.  You should consider doing this\n";
  echo "after you've cast the game.\n";
  echo "<p>\nBased on the current list of players who have signed up for\n";
  echo "<i>$Title</i>, there would be\n";
  printf ("%d Male and %d Female roles, and no Neutral roles.\n",
	  $confirmed['Male'],
	  $confirmed['Female']);

  echo "<p>\n";
  printf ("Yes. <a href=Schedule.php?action=%d&EventId=%d&Male=%d&Female=%d>" .
	  "Make the change</a>\n",
	  SCHEDULE_CONFIRM_FREEZE_GENDER_BALANCE,
	  $EventId,
	  $confirmed['Male'],
	  $confirmed['Female']);

  echo "<p>\n";
  printf ("No.  <a href=Schedule.php?action=%d&EventId=%d>Return to ".
	  "<i>$Title</i></a>\n",
	  SCHEDULE_SHOW_GAME,
	  $EventId);
}

/*
 * confirm_freeze_gender_balance
 *
 * The GM has confirmed that he wants to freeze the gender balance,
 * so do it
 */

function confirm_freeze_gender_balance ()
{
  // Extract the EventId and build the query

  $EventId = intval (trim ($_REQUEST['EventId']));
  $Male = intval (trim ($_REQUEST['Male']));
  $Female = intval (trim ($_REQUEST['Female']));
  if (0 == $EventId)
    return false;

  $sql = 'UPDATE Events SET ';
  $sql .= build_sql_string ('MaxPlayersMale', $Male, false);
  $sql .= build_sql_string ('MaxPlayersFemale', $Female);
  $sql .= build_sql_string ('MinPlayersNeutral', '0');
  $sql .= build_sql_string ('MaxPlayersNeutral', '0');
  $sql .= build_sql_string ('PrefPlayersNeutral', '0');
  $sql .= " WHERE EventId=$EventId";

  //  echo "$sql\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Update failed for event $EventId");
  else
    return true;
}


/**
 * parse_event_time
 * @time_blocks: Time as a number of blocks since 00:00 AM 
 * return: Start time as DateTime object. Date portion defaults to 1 January 2000
 * usage: for start time, submit blocks (stored in Run->$StartHour). For end time, use
 *        Run->$StartHour + Event->$Hours
 */
function parse_start_time ($time_blocks, $date = "NULL") 
{
	if ($date == "NULL") {
	   $date = new DateTime("1/1/2000");
	}
	$minutes = $start_time * EVENT_BLOCK;  // get time in minutes
	$hours = $minutes / 60;  	       
	$minutes = $minutes % 60;
	$date->setTime($hours, $minutes);
	return $date;
}

/*
 * display_one_col
 *
 * Helper function to display a single column row
 */

function display_one_col ($head, $subject)
{
  if ('' != $subject)
  {
    echo "  <tr>\n";
    echo "    <th align=right valign=top>$head:</th><td>$subject</td>\n";
    echo "  </tr>\n";
  }
}
?>
