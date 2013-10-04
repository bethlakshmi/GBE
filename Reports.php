<?php

// Note:  This report functionality basically doesn't work at all, so leave it alone for now.  -MDB

include ("intercon_db.inc");

// If the user's not logged in, send him to the entrypoint

if (! array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
{
  header ('Location: index.php');
  exit ();
}

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to connect to ' . DB_NAME);
  exit ();
}

// Only privileged users may access these pages

if (! user_has_priv (PRIV_CON_COM))
{
  html_begin ();
  display_access_error ();
  html_end ();
  exit ();
}

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = REPORT_PER_GAME;

$bShowCopyright = true;

switch ($action)
{
  case REPORT_PER_USER:
    html_begin (false);
    if (report_per_user ())
      $bShowCopyright = false;
    break;

  case REPORT_PER_GAME:
    html_begin (false);
    if (report_per_game ())
      $bShowCopyright = false;
    break;


  case REPORT_PER_ROOM:
    html_begin (false);
    if (report_per_room ())
      $bShowCopyright = false;
    break;

  case REPORT_WHOS_NOT_PLAYING_FORM:
    html_begin (true);
    whos_not_playing_when_form ();
    break;

  case REPORT_WHOS_NOT_PLAYING:
    html_begin (true);
    whos_not_playing_when ();
    break;

  case REPORT_OPS_TRACK:
    html_begin (true);
    report_volunteer_track (true);
    break;

  case REPORT_CONSUITE_TRACK:
    html_begin (true);
    report_volunteer_track (false);
    break;

  case REPORT_GAMES_BY_TIME:
    html_begin (false);
    report_games_by_time ('Fri');
    report_games_by_time ('Sat');
    report_games_by_time ('Sun');
    break;

  case REPORT_USERS_CSV:
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=\"".CON_NAME." Users.csv\"");
    report_users_csv ();
    exit();

  case REPORT_REGISTRATION:
    html_begin (true);
    registration_report ();
    break;

  case REPORT_BY_AGE:
    html_begin (true);
    report_by_age ();
    break;

  case REPORT_HOW_HEARD:
    html_begin (true);
    report_how_heard();
    break;

  default:
    html_begin (true);
    display_error ("Unknown action code: $action");
}

// Standard postamble

html_end ($bShowCopyright);

/*
 * report_how_heard
 *
 * Report on all users who've filled in the 'How you heard of Intercon'
 * question
 */

function report_how_heard()
{
  $sql = 'SELECT DisplayName, HowHeard,Created,';
  $sql .= 'DATE_FORMAT(Created, "%d-%b-%Y") AS CreDate';
  $sql .= ' FROM Users';
  $sql .= ' WHERE HowHeard != ""';
  $sql .= ' ORDER BY Created DESC';
  //  $sql .= ' ORDER BY LastName, FirstName';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for list of users failed', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo 'No users have answered the question about how they heard about ';
    echo CON_NAME . "\n";
    return;
  }

  echo "<table border=\"1\">\n";
  echo "  <tr align=\"left\">\n";
  echo "    <th>&nbsp;Created</th>\n";
  echo "    <th>&nbsp;User</th><th>&nbsp;How They Heard&nbsp;</th>\n";
  echo "  </tr>\n";

  while ($row = mysql_fetch_object ($result))
  {
    echo "  <tr valign=\"top\">\n";
    echo "    <td nowrap>&nbsp;$row->CreDate&nbsp;</td>\n";
    printf ("    <td>&nbsp;%s&nbsp;</td>\n",
	    	    trim ("$row->DisplayName"));
    echo "    <td>&nbsp;$row->HowHeard&nbsp;</td>\n";
    echo "  </tr>\n";
  }

  echo "</table>\n";
}

/*
 * report_per_user
 *
 * Dump a per-user report for all the attendees at the Con
 */

function report_per_user ()
{
  // Gather the list of all users who are able to signup

  $sql = 'SELECT UserId, FirstName, LastName';
  $sql .= ' FROM Users';
  $sql .= ' WHERE CanSignup!="Alumni"';
  $sql .= ' ORDER BY LastName, FirstName';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for list of users failed', $sql);

  $y = date ('Y');

  while ($row = mysql_fetch_object ($result))
  {
    if ('Admin' == $row->LastName)
      continue;

    echo "<div class=print_logo_break_before><img src=PageBanner.png></div>\n";

    write_user_report (trim ("$row->LastName, $row->FirstName"),
		       $row->UserId);

    echo "\n<div class=print_copyright>\n";
    echo "<HR WIDTH=\"50%\" ALIGN=CENTER>\n";
    echo "Copyright &copy; $y, New England Interactive Literature<BR>\n";
    echo "All Rights Reserved\n";
    echo "</div> <!-- copyright-->\n";
  }
}

function ampm_time($h)
{
  $hour = $h % 12;
  $suffix = 'PM';

  if (1 == ($h / 12))
  {
    $suffix = 'PM';
    if (0 == $hour)
      $suffix = 'AM';
  }
  else
  {
    $suffix = 'AM';
    if (0 == $hour)
      $suffix = 'PM';
  }

  if (0 == $hour)
    $hour = 12;

  return "$hour $suffix";
}

/*
 * write_room_report
 *
 * Write per-room entry for the specified day
 */

function write_room_report($room, $day, $day_title)
{
  $sql = 'SELECT Runs.StartHour, Runs.Rooms, Events.Title, Events.Hours';
  $sql .= ' FROM Runs, Events';
  $sql .= " WHERE Runs.Day='$day'";
  $sql .= '   AND Runs.EventId = Events.EventId';
  $sql .= "   AND FIND_IN_SET('$room', Runs.Rooms) > 0";
  $sql .= ' ORDER BY Runs.StartHour';
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error('Query for room report failed', $sql);

  echo "  <tr>\n";
  echo "    <th colspan=\"2\" align=\"left\">$day_title</th>\n";
  echo "  </tr>\n";

  while ($row = mysql_fetch_object($result))
  {
    echo "  <tr valign=\"top\">\n";
    /*
    printf ("    <td align=\"right\">&nbsp;%s - %s&nbsp;</th>\n",
	    ampm_time($row->StartHour),
	    ampm_time($row->StartHour + $row->Hours));
    */
    printf ("    <td>&nbsp;%02d:00 - %02d:00&nbsp;</th>\n",
	    $row->StartHour, $row->StartHour + $row->Hours);
    echo "    <td>$row->Title";
    $rooms_array = explode(',', $row->Rooms);
    if (count($rooms_array) > 1)
    {
      $a = array();
      foreach($rooms_array as $r)
      {
	if ($r != $room)
	  array_push($a, $r);
      }

      $other_rooms = pretty_rooms(implode(',', $a));
      echo "<br><small>Also in: $other_rooms</small>";
    }
    echo "</td>\n";
    echo "  </tr>\n";
  }

  echo "  <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
}

/*
 * report_per_room
 *
 * Dump a per-room report for all the rooms at the Con
 */

function report_per_room ()
{
  // We'll need the year
  $y = date ('Y');

  // Fetch the set of rooms
  $sql = 'SELECT COLUMN_TYPE FROM Information_Schema.Columns';
  $sql .= ' WHERE Table_Name="Runs" AND Column_Name="Rooms"';
  $sql .= ' AND Table_Schema="'.DB_NAME.'"';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for list of Con rooms failed', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('No rooms fetched for Con runs');

  // We're expecting a string of the form "set('room a', 'room b')"
  // Start by trimming off the leading 'set(' and trailing ')'
  $rooms = trim($row->COLUMN_TYPE, 'set()');

  // Now break the rooms into an array
  $rooms_array = explode(',', $rooms);

  foreach($rooms_array as $r)
  {
    $room_name = trim($r, "'");

    $sql = 'SELECT RunId FROM Runs';
    $sql .= " WHERE FIND_IN_SET('$room_name', Rooms) > 0";

    $room_result = mysql_query($sql);
    if (! $room_result)
    {
      display_mysql_error ('Query for room rows failed', $sql);
      continue;
    }

    //    printf ("<p>Room: %s - %d rows</p>\n", $room_name, mysql_num_rows($room_result));

    if (0 == mysql_num_rows($room_result))
      continue;

    echo "<div class=print_logo_break_before><img src=PageBanner.png></div>\n";

    echo "<font size=\"+3\"><b>$room_name</b></font><p>\n";

    echo "<table>\n";
    write_room_report ($room_name, 'Fri', FRI_TEXT);
    write_room_report ($room_name, 'Sat', SAT_TEXT);
    write_room_report ($room_name, 'Sun', SUN_TEXT);
    echo "</table>\n";
  }

  // Suppress the copyright display
  return true;
}

/*
 * build_order_string
 *
 * Build a string with shirt order information
 */

function build_order_string ($n, $size, &$s, &$count, $type)
{
  if (0 == $n)
    return;

  if ('' != $s)
    $s .= ', ';
  $s .= "$n $size $type";
  $count += $n;
}

/*
 * write_user_report
 *
 * Display the per-user information for both attendees and for Ops.
 */

function write_user_report ($name, $user_id)
{
  echo "<font size=\"+3\"><b>$name</b></font><p>\n";
  $gms = array();

  // See if this user has ordered any shirts

  $sql = "SELECT * FROM TShirts WHERE UserId=$user_id";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for shirts failed', $sql);

  $row = mysql_fetch_object ($result);
  if ($row)
  {
    $order = '';
    $count = 0;

    build_order_string ($row->Small,   'Small',   $order, $count, SHIRT_NAME);
    build_order_string ($row->Medium,  'Medium',  $order, $count, SHIRT_NAME);
    build_order_string ($row->Large,   'Large',   $order, $count, SHIRT_NAME);
    build_order_string ($row->XLarge,  'XLarge',  $order, $count, SHIRT_NAME);
    build_order_string ($row->XXLarge, 'XXLarge', $order, $count, SHIRT_NAME);
    build_order_string ($row->X3Large, 'X3Large', $order, $count, SHIRT_NAME);
    build_order_string ($row->X4Large, 'X4Large', $order, $count, SHIRT_NAME);
    build_order_string ($row->X5Large, 'X5Large', $order, $count, SHIRT_NAME);

    build_order_string ($row->Small_2,   'Small',   $order, $count, SHIRT_2_NAME);
    build_order_string ($row->Medium_2,  'Medium',  $order, $count, SHIRT_2_NAME);
    build_order_string ($row->Large_2,   'Large',   $order, $count, SHIRT_2_NAME);
    build_order_string ($row->XLarge_2,  'XLarge',  $order, $count, SHIRT_2_NAME);
    build_order_string ($row->XXLarge_2, 'XXLarge', $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X3Large_2, 'X3Large', $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X4Large_2, 'X4Large', $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X5Large_2, 'X5Large', $order, $count, SHIRT_2_NAME);

    if (0 != $count)
    {
      if (1 == $count)
	$item = 'shirt';
      else
	$item = 'shirts';

      echo "<b>T-Shirts:</b> $order $item ordered<p>\n";
    }
  }

  // Gather the list of any games this user is a GM for

  $sql = "SELECT EventId FROM GMs WHERE UserId=$user_id";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs failed', $sql);

  while ($row = mysql_fetch_object ($result))
    $gms[] = $row->EventId;

  // Now gather the list of games this user is signed up for

  $sql = 'SELECT Events.Title, Events.Hours, Events.EventId,';
  $sql .= ' Runs.Day, Runs.StartHour, Runs.TitleSuffix, Runs.Rooms,';
  $sql .= ' Signup.State';
  $sql .= ' FROM Signup, Runs, Users, Events';
  $sql .= ' WHERE Signup.State!="Withdrawn"';
  $sql .= "   AND Signup.UserId=$user_id";
  $sql .= '   AND Users.UserId=Signup.UserId';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= ' ORDER BY Day, StartHour';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for users games failed', $sql);

  if (0 == mysql_num_rows ($result))
    return true;

  // Go through each of the signup records and display them for this user

  echo "<b>Games You've Signed Up For:</b>\n";
  echo "<table>\n";
  while ($row = mysql_fetch_object ($result))
  {
    echo "  <tr valign=top>\n";
    echo "  <td>$row->Day&nbsp;</td>\n";
    printf ("    <td align=right>%s</td>\n",
	    start_hour_to_24_hour ($row->StartHour));
    echo "       <td>-</td>\n";
    printf ("    <td align=right>%s&nbsp;&nbsp;</td>\n",
	    start_hour_to_24_hour ($row->StartHour + $row->Hours));

    $rooms = pretty_rooms($row->Rooms);
    echo "    <td>$rooms</td>\n";

    echo "    <td>&nbsp;</td>\n";

    $title = trim ("$row->Title $row->TitleSuffix");
    echo "    <td>$title</td>\n";

    if (in_array ($row->EventId, $gms))
      $status = 'GM';
    else
      $status = $row->State;

    echo "    <td>&nbsp;&nbsp;$status</td>\n";

    echo "  </tr>\n";
  }
  echo "</table>\n";
}

/*
 * report_per_game
 *
 * Build all of the per-game reports for Ops and the GMs
 */

function report_per_game ()
{
  // Iterate over all runs, except Ops Track.  We'll have a special
  // report for that

  $sql = 'SELECT Events.Title, Events.MinPlayersMale, Events.MaxPlayersMale,';
  $sql .= ' Events.MinPlayersFemale, Events.MaxPlayersFemale,';
  $sql .= ' Events.MinPlayersNeutral, Events.MaxPlayersNeutral, Runs.Rooms,';
  $sql .= ' Runs.TitleSuffix, Runs.RunId, Runs.Day, Runs.StartHour';
  $sql .= ' FROM Runs, Events';
  $sql .= ' WHERE Events.EventId=Runs.EventId';
  $sql .= '   AND SpecialEvent=0';
  $sql .= '   AND IsOps="N"';
  $sql .= '   AND IsConSuite="N"';
  $sql .= ' ORDER BY Events.Title, Runs.Day, Runs.StartHour';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of games', $sql);

  $y = date ('Y');

  while ($row = mysql_fetch_object ($result))
  {
    echo "<div class=print_logo_break_before><img src=PageBanner.png></div>\n";

    write_game_report ($row->RunId,
		       $row->Title,
		       $row->TitleSuffix,
		       pretty_rooms($row->Rooms),
		       $row->Day,
		       start_hour_to_24_hour ($row->StartHour),
		       $row->MinPlayersMale,
		       $row->MaxPlayersMale,
		       $row->MinPlayersFemale,
		       $row->MaxPlayersFemale,
		       $row->MinPlayersNeutral,
		       $row->MaxPlayersNeutral);

    echo "\n<div class=print_copyright>\n";
    echo "<HR WIDTH=\"50%\" ALIGN=CENTER>\n";
    echo "Copyright &copy; $y, New England Interactive Literature<BR>\n";
    echo "All Rights Reserved\n";
    echo "</div> <!-- copyright-->\n";
  }  
}

/*
 * write_game_report
 *
 * Display the information about a game to be used by Ops and given to the
 * GMs
 */

function write_game_report ($run_id, $title, $title_suffix,
			    $room, $day, $start_time,
			    $min_male, $max_male,
			    $min_female, $max_female,
			    $min_neutral, $max_neutral)
{
  if ('' != $title_suffix)
    $title .= "<br>$title_suffix";

  echo "<table width=\"100%\">";
  echo "  <tr valign=top>\n";
  echo "    <td><font size=\"+3\"><b>$title</b></font></td>\n";
  echo "    <td align=right><font size=\"+1\"><b>$day&nbsp;$start_time<br>$room</b></font></td>\n";
  echo "  </tr>\n";
  echo "</table>";

  if (0 != $max_male)
    echo "<b>Male:</b> $min_male - $max_male &nbsp;&nbsp;&nbsp;&nbsp;\n";
  if (0 != $max_female)
    echo "<b>Female:</b> $min_female - $max_female &nbsp;&nbsp;&nbsp;&nbsp;\n";
  if (0 != $max_neutral)
    echo "<b>Neutral:</b> $min_neutral - $max_neutral<p>\n";

  $max_players = $max_male + $max_female + $max_neutral;

  $gms = array();

  $sql = 'SELECT DISTINCT Users.FirstName, Users.LastName,';
  $sql .= ' Users.Nickname';
  $sql .= ' FROM GMs, Users, Runs';
  $sql .= " WHERE Runs.RunId=$run_id";
  $sql .= "   AND GMs.EventId=Runs.EventId";
  $sql .= "   AND Users.UserId=GMs.UserId";
  $sql .= ' ORDER BY Users.LastName, Users.FirstName';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to fetch list of GMs', $sql);

  if (1 == mysql_num_rows ($result))
    $plural = '';
  else
    $plural = 's';

  echo "<table>\n";
  echo "  <tr valign=top>\n";
  echo "    <th>GM$plural:</th>\n";
  echo "    <td>\n";
  while ($row = mysql_fetch_object ($result))
  {
    if ($row->Nickname != '')
      $name = trim ("$row->FirstName \"$row->Nickname\" $row->LastName");
    else
      $name = trim ("$row->FirstName $row->LastName");
    echo "      $name<br>\n";

    $gms[] = "$row->LastName, $row->FirstName";
  }
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "</table>\n";

  // Gather the list of confirmed and waitlisted players

  $confirmed = array();
  $waitlist = array();

  $sql = 'SELECT Users.FirstName, Users.LastName,';
  $sql .= ' Signup.SignupId, Signup.State, Signup.Counted, Signup.Gender';
  $sql .= ' FROM Signup, Users';
  $sql .= " WHERE Signup.RunId=$run_id";
  $sql .= "   AND Signup.State<>'Withdrawn'";
  $sql .= '   AND Users.UserId=Signup.UserId';
  $sql .= ' ORDER BY SignupId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of players', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo "No signups for this run\n";
    return true;
  }

  $i = 0;
  $nc = 0;
  $confirmed_male = 0;
  $confirmed_female = 0;
  while ($row = mysql_fetch_object ($result))
  {
    // Hold onto any waitlisted players for now...

    if ('Waitlisted' == $row->State)
      $waitlist[] = "$row->LastName, $row->FirstName|$row->Gender";
    else
    {
      if (! in_array ("$row->LastName, $row->FirstName", $gms))
      {
	if ('Y' == $row->Counted)
	{
	  if ('Male' == $row->Gender)
	    $confirmed_male++;
	  else
	    $confirmed_female++;
	  $n = ++$i;
	}
	else
	{
	  $n = 'N/C';
	  $nc++;
	}
	$confirmed[] = "$row->LastName, $row->FirstName|$n";
      }
    }
  }

  //  dump_array ('confirmed', $confirmed);

  // Display the list of players confirmed for this run in two columns

  sort ($confirmed);
  $c = count($confirmed);
  if (0 == $nc)
    $extra = '';
  else
    $extra = " (plus $nc Not Counted)";

  $extra .= " - $confirmed_male Male, $confirmed_female Female";

  printf ("<p><b>%d Confirmed Players$extra</b><br>\n",
	  $c - $nc);

  $max_row = intval (($c + 1) / 2);

  echo "<table width=\"100%\">\n";
  for ($r = 0; $r < $max_row; $r++)
  {
    echo "  <tr>\n";
    $a = explode ('|', $confirmed[$r]);
    printf ("    <td>%s</td>\n",
	    $a[0]);

    if (($r + $max_row) < $c)
    {
      $a = explode ('|', $confirmed[$r + $max_row]);
      printf ("    <td>%s</td>\n",
	      $a[0]);
    }
    echo "  </tr>\n";
  }
  echo "</table>\n";

  // If there's nobody on the waitlist, don't display one

  if (0 != count($waitlist))
  {
    // Display the waitlist, in order

    echo "<p><b>Waitlist - In signup order</b><br>\n";

    echo "<table>\n";
    foreach ($waitlist as $v)
    {
      echo "  <tr>\n";
      $a = explode ('|', $v);
      printf ("    <td>%s</td>\n    <td>&nbsp;&nbsp;%s</td>\n",
	      $a[0],
	      $a[1]);
      echo "  </tr>\n";
    }
    echo "</table>\n";
  }

  // If there are open slots, display signup lines

  $open_slots = $max_players - $i;

  if ($open_slots > 0)
  {
    echo "<p><b>$open_slots Available slots</b><br>\n";

    $rows = intval (($open_slots + 1) / 2);
    //    echo "open_slots: $open_slots,  rows: $rows<p>\n";

    echo "<table class=per_game_openning width=\"100%\">\n";
    for ($r = 0; $r < $rows; $r++)
    {
      echo "<tr class>\n";
      echo "  <td class=per_game_openning width=\"40%\">&nbsp;</td>\n";
      echo "  <td class=per_game_spacer width=\"10%\">&nbsp;</td>\n";
      if (($r != $rows - 1) || (0 == $open_slots % 2))
	echo "  <td class=per_game_openning width=\"40%\">&nbsp;</td>\n";
    }
    echo "</table>\n";
  }

  return true;
}

/*
 * report_volunteer_track
 *
 * Report on the volunteer tracks - Ops and Consuite - who's signed up when.
 * This is in place of the per-game reports for ops and Consuite.
 */

function report_volunteer_track ($is_ops)
{
  if ($is_ops)
  {
    $Title = 'Ops';
    $Where = 'IsOps="Y"';
  }
  else
  {
    $Title = 'ConSuite';
    $Where = 'IsConSuite="Y"';
  }

  echo "<font size=\"+3\"><b>$Title Track Report</b></font>\n";

  // Iterate over all runs for the volunteer track.

  $sql = 'SELECT Events.Title, Runs.RunId, Runs.Day, Runs.StartHour, Events.EventId,';
  $sql .= ' Events.Author';
  $sql .= ' FROM Runs, Events';
  $sql .= ' WHERE Events.EventId=Runs.EventId';
  $sql .= '   AND SpecialEvent=0';
  $sql .= '   AND ' . $Where;
  $sql .= ' ORDER BY Runs.Day, Runs.StartHour';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of volunteer runs', $sql);

  $last_day = '';
  echo "<table width=\"100%\">\n";
  echo "  <tr valign=top>\n";

  while ($row = mysql_fetch_object ($result))
  {
    if ($last_day != $row->Day)
      echo "    </td>\n    <td>";

    $last_day = $row->Day;

    printf ("<p><font size=\"+1\"><b>%s %s</b></font><br>\n",
	    $row->Day,
    	    start_hour_to_12_hour ($row->StartHour));
    echo $row->Title."<br>";
    echo "<b>Head of Area:</b>".$row->Author."<br>";

    $sql = 'SELECT Users.DisplayName ';
    $sql .= ' FROM GMs, Users';
    $sql .= " WHERE GMs.EventId=$row->EventId";
    $sql .= '   AND Users.UserId=GMs.UserId';
    $sql .= ' ORDER BY DisplayName';
    
    $coordinator_result = mysql_query ($sql);
    if (! $coordinator_result)
      return display_mysql_error ('Failed to get list of players', $sql);

    if (0 == mysql_num_rows ($coordinator_result))
    {
      echo "<font color=\"red\"><b>No coordinator</b></font>\n";
      continue;
    }
    else
    {
      $coordinator = mysql_fetch_object ($coordinator_result);
      echo "<b>Coordinator:</b> $coordinator->DisplayName<br>\n";
    }

    $sql = 'SELECT Users.DisplayName ';
    $sql .= ' FROM Signup, Users';
    $sql .= " WHERE Signup.RunId=$row->RunId";
    $sql .= "   AND Signup.State<>'Withdrawn'";
    $sql .= '   AND Users.UserId=Signup.UserId';
    $sql .= ' ORDER BY DisplayName';

    $run_result = mysql_query ($sql);
    if (! $run_result)
      return display_mysql_error ('Failed to get list of volunteers', $sql);

    if (0 == mysql_num_rows ($run_result))
    {
      echo "<font color=\"red\">No signups for this run</font>\n";
      continue;
    }

    while ($run_row = mysql_fetch_object ($run_result))
    {
      echo "$run_row->DisplayName<br>\n";
    }
  }
  echo "    </td>\n  </tr>\n</table>\n";
}

/*
 * whos_not_playing_when_form
 *
 * Allow the user to query who's not playing during a range of hours
 */

function whos_not_playing_when_form ()
{
  echo "<h1>Who's Free When?</h1>\n";
  echo "NOTE:  Does not show unpaid users\n";

  if (! array_key_exists ('Day', $_POST))
  {
    $_POST['Day'] = 'Sat';
    $_POST['StartHour'] = 14;
    $_POST['Hours'] = 4;
  }

  if (array_key_exists ('CSV', $_POST))
    $csv_checked = 'CHECKED';
  else
    $csv_checked = '';

  echo "<form method=post action=Reports.php>\n";
  printf ("<input type=hidden name=action value=%d>\n",
	  REPORT_WHOS_NOT_PLAYING);
  echo "<table border=0>\n";
  form_day ('Day');
  form_start_hour ('Start Hour', 'StartHour');
  form_text (2, 'Hours');
  echo "  <tr>\n";
  echo "    <td>&nbsp;</td>\n";
  echo "    <td><input type=checkbox name=CSV $csv_checked VALUE=1> Display only EMail and as CSV</td>\n";
  echo "  </tr>\n";
  form_submit ('Submit');
  echo "</table>\n";
  echo "</form>\n";
}

/*
 * whos_not_playing_when
 *
 * Process the request for who's not playing in a range of hours
 */

function whos_not_playing_when ()
{
  $Day = $_POST['Day'];
  $StartHour = $_POST['StartHour'];
  $Hours = $_POST['Hours'];
  $EndHour = $StartHour + $Hours;
  $bCSV = array_key_exists ('CSV', $_POST);

  // Make sure we have a valid value for hours

  if (! validate_int ('Hours', 0, 24))
    return false;

  if (! validate_day_time ('StartHour', 'Day', 'Start Hour'))
    return false;


  // Build the mask of hours

  $hour_mask = 0;
  for ($i = 0; $i < $Hours; $i++)
    $hour_mask = ($hour_mask << 1) | 1;

  // Start by building the list of all players.  Set the array value to the
  // full hour_mask.  We'll AND bits off to indicate that they're playing
  // during those hours

  $users = array();

  $sql = 'SELECT UserId FROM Users';
  $sql .= '   AND CanSignup<>"Alumni"';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for user list failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    $users[$row->UserId] = $hour_mask;
  }
    
  // Now get the runs during the period

  $sql = 'SELECT Signup.UserId, Runs.StartHour, Events.Hours';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= " WHERE Runs.Day='$Day'";
  $sql .= "   AND Runs.StartHour<$EndHour";
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= '   AND Signup.State<>"Withdrawn"';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for list of runs failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    // If this run is over before our period starts, skip to the next one

    if (($row->StartHour + $row->Hours) < $StartHour)
      continue;

    // If this run completely encompasses the period, just wipe out the
    // entry in the users array

    if (($row->StartHour == $StartHour) && ($row->Hours >= $Hours))
    {
      $users[$row->UserId] = 0;
      continue;
    }

    // OK.  We've got to work on this one.  Start by building a mask for the
    // hours of the run

    $run_mask = 0;
    for ($i = 0; $i < $row->Hours; $i++)
      $run_mask = ($run_mask << 1) | 1;

    // Shift the mask right or left, depending on the start time

    if ($row->StartHour > $StartHour)
      $run_mask = $run_mask << ($row->StartHour - $StartHour);
    else
    {
      if ($row->StartHour < $StartHour)
	$run_mask = $run_mask >> ($StartHour - $row->StartHour);
    }

    $users[$row->UserId] &= ~$run_mask;
  }

  // Finally, drop out anybody who's away then

  $sql = 'SELECT * FROM Away';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for Away info failed', $sql);

  while ($row = mysql_fetch_assoc ($result))
  {
    $UserId = $row['UserId'];

    if ($row[$Day])
    {
      unset ($users[$UserId]);
      continue;
    }

    $m = 1;
    for ($h = $StartHour; $h < $StartHour + $Hours; $h++)
    {
      $k = sprintf ('%s%02d', $Day, $h);
      if ($row[$k])
	$users[$UserId] &= ~$m;
      $m = $m << 1;
    }
  }

  // Build a new array indexed by the name and email address of any attendees
  // who are not completely busy (or away) during the period

  $names = array();
  $fully_available = 0;

  foreach ($users as $k => $v)
  {
    if (0 == $v)
      continue;

    $sql = "SELECT DisplayName, EMail FROM Users WHERE UserId=$k";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Query failed for user information for $k",
				  $sql);

    $row = mysql_fetch_object ($result);
    if (! $row)
      return display_error ("Failed to get user information information for $k",
			    $sql);

    if ($bCSV)
      $index = "$row->EMail";
    else
      $index = "\"$row->DisplayName &lt;$row->EMail&gt;";

    $names[$index] = $v;

    if ($v == $hour_mask)
      $fully_available++;
  }

  // If we're displaying this as CSV, just show the email addresses.  There's
  // no need to sort them...

  if ($bCSV)
  {
    printf ("<b>%d Users are available during the whole period, %s - %s</b><br>\n",
	    $fully_available,
	    start_hour_to_12_hour ($StartHour),
	    start_hour_to_12_hour ($StartHour + $Hours));

    reset ($names);
    foreach ($names as $k => $v)
    {
      if ($v != $hour_mask)
	continue;

      echo "$k,<br>\n";
    }

    $n = count($names) - $fully_available;
    if (1 == $n)
      $users = 'User is';
    else
      $users = 'Users are';

    printf ("<p><b>%d %s available during part of the period</b><br>\n",
	    $n,
	    $users);

    reset ($names);
    foreach ($names as $k => $v)
    {
      if ($v == $hour_mask)
	continue;

      echo "$k,<br>\n";
    }

    return;
  }

  // Sort the resultant names alphabetically

  ksort ($names);

  // First pass over the name list.  Only show those who are available
  // for the full period

  echo "<table border=1>\n";
  echo "  <tr>\n";
  printf ("    <th colspan=%d>%d Users are available during the whole period</th>\n",
	  $Hours+1,
	  $fully_available);
  echo "  </tr>\n";

  echo "  <tr>\n";
  echo "  <th align=left>Name, EMail</th>\n";
  for ($i = 0; $i < $Hours; $i++)
  {
    printf ("    <th>%s</th>\n", start_hour_to_12_hour ($i + $StartHour));
  }
  echo "  </tr>\n";

  reset ($names);

  foreach ($names as $k => $v)
  {
    if ($v != $hour_mask)
      continue;

    echo "  <tr>\n";
    echo "    <td>$k,</td>\n";

    for ($i = 0; $i < $Hours; $i++)
      echo "    <td bgcolor=green>&nbsp;</td>\n";

    echo "  </tr>\n";
  }

  // Now the people who are available part of the time

  reset ($names);

  echo "  <tr>\n";
  $n = count($names) - $fully_available;
  if (1 == $n)
    $users = 'User is';
  else
    $users = 'Users are';

  printf ("    <th colspan=%d>%d %s available during part of the period</th>\n",
	  $Hours+1,
	  $n,
	  $users);
  echo "  </tr>\n";

  foreach ($names as $k => $v)
  {
    if ($v == $hour_mask)
      continue;

    echo "  <tr>\n";
    echo "    <td>$k</td>\n";

    $m = 1;
    for ($i = 0; $i < $Hours; $i++)
    {
      $bg = '';
      if (0 != ($m & $v))
	$bg = 'bgcolor=green';
      echo "    <td $bg>&nbsp;</td>\n";
      $m = $m << 1;
    }

    echo "  </tr>\n";
  }

  echo "</table>\n";
}

/*
 * report_games_by_time
 *
 * Display a simple list of the available games, ordered by start time,
 * for a given day.
 */

function report_games_by_time ($day)
{
  echo "<div class=print_logo_break_before><img src=PageBanner.png></div>\n";
  printf ("<font size=\"+3\"><b>%s Schedule for %s</b></font><p>\n",
	  CON_NAME,
	  $day);

  // Get the list of games for this day

  $sql = 'SELECT Events.Title, Events.Hours,';
  $sql .= ' Runs.Day, Runs.StartHour, Runs.TitleSuffix, Runs.Rooms';
  $sql .= ' FROM Runs, Events';
  $sql .= " WHERE Day='$day'";
  $sql .= '   AND Events.Eventid=Runs.EventId';
  $sql .= '   AND Events.SpecialEvent=0';
  $sql .= '   AND IsOps="N"';
  $sql .= ' ORDER BY StartHour, Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for games failed', $sql);

  $hour = 0;

  echo "<table>\n";
  while ($row = mysql_fetch_object ($result))
  {
    if ($hour != $row->StartHour)
    {
      if (0 != $hour)
	echo "    <td>\n  </tr>\n";
      echo "  <tr valign=top>\n";
      printf ("    <td><b>%s</b></td>\n",
	      start_hour_to_24_hour ($row->StartHour));
      echo "    <td>\n";
      $hour = $row->StartHour;
    }

    $rooms = pretty_rooms($row->Rooms);
    echo "    $row->Title $row->TitleSuffix $rooms<br>\n";
  }
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "</table>\n";

  $y = date ('Y');
  echo "\n<div class=print_copyright>\n";
  echo "<HR WIDTH=\"50%\" ALIGN=CENTER>\n";
  echo "Copyright &copy; $y, New England Interactive Literature<BR>\n";
  echo "All Rights Reserved\n";
  echo "</div> <!-- copyright-->\n";
}

function count_tshirts_by_user () {
    $sql = 'SELECT * FROM TShirts';
    $result = mysql_query ($sql);

    if (! $result)
        return display_mysql_error ('Query for TShirts failed');

    $userShirts = array();
    while ($row = mysql_fetch_assoc ($result))
    {
        $userId = $row["UserId"];
        if (!array_key_exists($userId, $userShirts)) {
            $userShirts[$userId] = array(
                "Unpaid" => array(),
                "Paid" => array(),
                "Cancelled" => array()
            );
        }
        $status = $row["Status"];
        
        unset($row["Status"]);
        unset($row["UserId"]);
        unset($row["TShirtId"]);
        unset($row["PaymentAmount"]);
        unset($row["PaymentNote"]);
        unset($row["LastUpdated"]);
        
        
        foreach (array_keys($row) as $key) {
            if (!array_key_exists($key, $userShirts[$userId][$status])) {
                $userShirts[$userId][$status][$key] = 0;
            }
            
            $userShirts[$userId][$status][$key] += $row[$key];
        }
    }
    
    return $userShirts;
}

/*
 * report_users_csv
 *
 * List all users in CSV format for use in creating badges & stickers
 *
 */

function report_users_csv ()
{
  // Fetch the list of users

  $sql = 'SELECT Users.UserId UserId, FirstName, Nickname, LastName, EMail, CanSignup, LastLogin, ';
  $sql .= ' (SELECT Status FROM Thursday WHERE Thursday.UserId = Users.UserId AND Status = "Paid") ThursdayStatus,';
  $sql .= ' (SELECT SUM(Quantity) FROM DeadDog WHERE DeadDog.UserId = Users.UserId AND Status = "Paid") DeadDogTickets';
  $sql .= ' FROM Users';
//  $sql .= ' WHERE CanSignup<>"Alumni"';
//  $sql .= '   AND LastName<>"Admin"';
  $sql .= ' ORDER BY LastName, FirstName';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for users failed');

  $userShirts = count_tshirts_by_user();

  echo "\"LastName\",\"FirstName\",\"Nickname\",\"EMail\",\"Status\",\"LastLogin\",\"ShirtOrder\",\"PreCon\",\"DeadDogTickets\"\n";

  while ($row = mysql_fetch_object ($result))
  {
    echo "\"$row->LastName\",";
    echo "\"$row->FirstName\",";
    echo "\"$row->Nickname\",";
    echo "\"$row->EMail\",";
    echo "\"$row->CanSignup\",";
    echo "\"$row->LastLogin\",";
    
    echo "\"";
    if (array_key_exists($row->UserId, $userShirts)) {
        $paidShirts = $userShirts[$row->UserId]["Paid"];
        if (count($paidShirts) > 0)
            report_users_csv_tshirts ($row->UserId, $paidShirts, "Paid");
        $unpaidShirts = $userShirts[$row->UserId]["Unpaid"];
        if (count($unpaidShirts) > 0)
            report_users_csv_tshirts ($row->UserId, $unpaidShirts, "Unpaid");
    }
    echo "\",";
    echo "\"$row->ThursdayStatus\",";
    echo "\"$row->DeadDogTickets\"";
    echo "\n";
  }
}

function report_users_csv_tshirts ($user_id, $shirtCount, $status)
{
  $order = '';
  $count = 0;

  $shirtShortName = "(P)";
  $shirt2ShortName = "(B)";

  build_order_string ($shirtCount["Small"],   'S',   $order, $count, $shirtShortName);
  build_order_string ($shirtCount["Medium"],  'M',   $order, $count, $shirtShortName);
  build_order_string ($shirtCount["Large"],   'L',   $order, $count, $shirtShortName);
  build_order_string ($shirtCount["XLarge"],  'XL',  $order, $count, $shirtShortName);
  build_order_string ($shirtCount["XXLarge"], 'XXL', $order, $count, $shirtShortName);
  build_order_string ($shirtCount["X3Large"], '3XL', $order, $count, $shirtShortName);
  build_order_string ($shirtCount["X4Large"], '4XL', $order, $count, $shirtShortName);
  build_order_string ($shirtCount["X5Large"], '5XL', $order, $count, $shirtShortName);

  build_order_string ($shirtCount["Small_2"],   'S',   $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["Medium_2"],  'M',   $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["Large_2"],   'L',   $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["XLarge_2"],  'XL',  $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["XXLarge_2"], 'XXL', $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["X3Large_2"], '3XL', $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["X4Large_2"], '4XL', $order, $count, $shirt2ShortName);
  build_order_string ($shirtCount["X5Large_2"], '5XL', $order, $count, $shirt2ShortName);

  if ("Unpaid" == $status) {
      $order .= " - UNPAID";
  }
  
  echo $order;
}

/*
 * registration_report
 *
 * Report used by Ops for registration
 */

function registration_report ()
{
  // Gather the list of all users who are able to signup

  $sql = 'SELECT UserId, FirstName, LastName, Nickname,';
  $sql .= ' CanSignup, PaymentNote, CompEventId';
  $sql .= ' FROM Users';
  $sql .= ' WHERE CanSignup!="Alumni"';
  $sql .= ' ORDER BY LastName, FirstName';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for list of users failed', $sql);

  $first = true;

  while ($row = mysql_fetch_object ($result))
  {
    if ('Admin' == $row->LastName)
      continue;

    if (! $first)
      echo "<hr>\n";
    $first = false;

    tshirts_ordered ($row->UserId, $order, $amt_due);

    if ('' == $order)
      $order = 'None';

    if ('' == $row->Nickname)
      $nickname = '';
    else
      $nickname = "\"$row->Nickname\"";

    echo "<table width=\"100%\" style=\"page-break-inside: avoid\">\n";
    echo "  <tr>\n";
    echo "    <td>\n";
    echo "<b><big>$row->LastName, $row->FirstName $nickname</big></b><br>\n";
    echo "    </td>\n";
    echo "    <td align=right>Checked In:</td>\n";
    echo "    <td width=50 style=\"border-bottom: thin solid black\">&nbsp;</td>\n";
    echo "  </tr>\n";
    if ('Comp' != $row->CanSignup)
      echo "<tr><td>$row->CanSignup: $row->PaymentNote</td></tr>\n";
    else
    {
      $sql = "SELECT Title FROM Events WHERE EventId=$row->CompEventId";
      $event_result = mysql_query ($sql);
      if (! $event_result)
	return display_mysql_error ('Query for event title failed', $sql);
      $event_row = mysql_fetch_object ($event_result);
      mysql_free_result ($event_result);

      echo "<tr><td>$row->CanSignup: $event_row->Title</td></tr>\n";
    }
    echo "<tr><td>Shirts Ordered: $order</td>\n";
    echo "    <td align=right>Hotel Room:</td>\n";
    echo "    <td width=50 style=\"border-bottom: thin solid black\">&nbsp;</td>\n";
    echo "</tr>\n";
    echo "<tr><td>Amount Due for Shirts: <b>$$amt_due.00</b></td></tr>\n";
    echo "<tr><td>Notes:&nbsp;</td>\n";
    echo "    <td align=right>Con Breakfast:</td>\n";
    echo "    <td width=50 style=\"border-bottom: thin solid black\">&nbsp;</td>\n";
    echo "</tr>\n";
    echo "<tr><td>&nbsp;<!-- extra space for notes--></td></tr>\n";
    echo "</table>\n";
  }
}

function tshirts_ordered ($user_id, &$order, &$amt_due)
{
  $order = '';
  $amt_due = 0;

  // See if this user has ordered any shirts

  $sql = "SELECT * FROM TShirts WHERE UserId=$user_id";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for shirts failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if ('Cancelled' == $row->Status)
      continue;

    $count = 0;

    build_order_string ($row->Small,   'Small',   $order, $count, SHIRT_NAME);
    build_order_string ($row->Medium,  'Medium',  $order, $count, SHIRT_NAME);
    build_order_string ($row->Large,   'Large',   $order, $count, SHIRT_NAME);
    build_order_string ($row->XLarge,  'XLarge',  $order, $count, SHIRT_NAME);
    build_order_string ($row->XXLarge, 'XXLarge', $order, $count, SHIRT_NAME);
    build_order_string ($row->X3Large, 'X3Large', $order, $count, SHIRT_NAME);
    build_order_string ($row->X4Large, 'X4Large', $order, $count, SHIRT_NAME);
    build_order_string ($row->X5Large, 'X5Large', $order, $count, SHIRT_NAME);

    build_order_string ($row->Small_2,   'Small',   $order, $count, SHIRT_2_NAME);
    build_order_string ($row->Medium_2,  'Medium',  $order, $count, SHIRT_2_NAME);
    build_order_string ($row->Large_2,   'Large',   $order, $count, SHIRT_2_NAME);
    build_order_string ($row->XLarge_2,  'XLarge',  $order, $count, SHIRT_2_NAME);
    build_order_string ($row->XXLarge_2, 'XXLarge', $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X3Large_2, 'X3Large', $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X4Large_2, 'X4Large', $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X5Large_2, 'X5Large', $order, $count, SHIRT_2_NAME);

    if ('Unpaid' == $row->Status)
      $amt_due += $count * TSHIRT_DOLLARS;
  }
}

function report_by_age ()
{
  $sql = 'SELECT FirstName, LastName, BirthYear, CanSignup';
  $sql .= ' FROM Users';
  $sql .= ' WHERE CanSignup<>"Alumni"';
  $sql .= '   AND CanSignup<>"Unpaid"';
  $sql .= '   AND LastName<>"Admin"';

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Query for users by age failed', $sql);

  $total_users = mysql_num_rows ($result);

  $a = array();
  $unspecified = 0;
  $minors = 0;
  $young = 0;
  $adults = 0;
  $unknown = 0;

  while ($row = mysql_fetch_object ($result))
  {
    $age = birth_year_to_age ($row->BirthYear);

    $age_range = 9;

    if (0 == $age)     // Unspecified
    {
      $unspecified++;
      $age_range = 1;
    }
    elseif ($age < 18) // Minors
    {
      $minors++;
      $age_range = 2;
    }
    elseif ($age < 21) // Young Adults
    {
      $young++;
      $age_range = 3;
    }
    else                    // Adults
    {
      $adults++;
      $age_range = 4;
    }

    $a[] = "$age_range|$row->LastName, $row->FirstName|$age|$row->CanSignup";
  }

  sort ($a);

  
  $last_age_range = '';
  echo "<table>\n";
  foreach ($a as $v)
  {
    $info = explode ('|', $v);

    if ($last_age_range != $info[0])
    {
      switch ($info[0])
      {
	case 1:
	  $age_range = 'Unspecified';
	  $pct = ($unspecified * 100.0) / $total_users;
	  break;

        case 2:
	  $age_range = 'Minor (< 18)';
	  $pct = ($minors * 100.0) / $total_users;
	  break;
	break;

        case 3:
	  $age_range = 'Young Adult (18 - 20)';
	  $pct = ($young * 100.0) / $total_users;
	  break;

        case 4:
	  $age_range = 'Adult (21 and up)';
	  $pct = ($adults * 100.0) / $total_users;
	  break;
       
        default:
	  $age_range = 'Unknown';
	  $pct = 0.0;
	  break;
      }

      echo "  <tr>\n";
      printf ("    <td colspan=3><b>&nbsp;<br>%01.2f%% -- %s</b></td>\n",
	      $pct,
	      $age_range);
      echo "  </tr>\n";

      $last_age_range = $info[0];
    }

    if (0 == $age)
      $age = 'Unspecified';

    echo "  <tr valign=top>\n";
    printf ("    <td>%s</td>\n", $info[1]);
    printf ("    <td>&nbsp;&nbsp;%s&nbsp;&nbsp;</td>\n", $info[2]);
    printf ("    <td>%s</td>\n", $info[3]);
    //    echo "    <td align=right>&nbsp;&nbsp;$age&nbsp;&nbsp;</td>\n";
    echo "  </td>\n";
  }
  echo "</table>\n";
}

?>
