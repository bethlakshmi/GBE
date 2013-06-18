<?php
include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

display_all_signups ();

// Add the postamble

html_end ();

/*
 * display_all_signups
 */

function display_all_signups ()
{
  // You need ConCom privilege to see this page

  if (! user_has_priv (PRIV_CON_COM))
    return display_access_error ();

  // Initialize a few things

  $users = array ();
  $signedup_users = 0;

  // Gather the list of all users who are able to signup

  $sql = 'SELECT UserId, FirstName, LastName, Nickname';
  $sql .= ' FROM Users';
  $sql .= ' WHERE (CanSignup!="Unpaid" AND CanSignup!="Alumni")';
  $sql .= ' ORDER BY LastName, FirstName, Nickname';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for list of users failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if ('Admin != $row->LastName')
    {
      $name = "$row->LastName, $row->FirstName";
      if (($row->Nickname != '') && ($row->FirstName != $row->Nickname))
	$name .= " ($row->Nickname)";
      $users[$name] = $row->UserId;
    }
  }

  $users_count = count($users);

  // Sort the array so it's case insensative and reset the internal pointer
  // to the first element

  //  dump_array ('Users - Before sort', $users);
  uksort ($users, "strcasecmp");
  //  dump_array ('Users - After sort', $users);
  reset ($users);
  $cur_name = '';

  // Get a list of first characters

  $sql = 'SELECT DISTINCT UCASE(SUBSTRING(LastName,1,1)) AS Ch';
  $sql .= '  FROM Users';
  $sql .= "  WHERE LastName<>'Admin'";
  $sql .= '  ORDER BY Ch';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of characters');

  // Initialize the list of anchors to the alphabet, and then FALSE,
  // indicating that we haven't seen the character yet.  Then pull the
  // list of leading characters from the database and set them to TRUE,
  // indicating that we've got an anchor for that character

  $anchors = array ();
  for ($i = ord('A'); $i <= ord('Z'); $i++)
    $anchors[chr($i)] = FALSE;

  while ($row = mysql_fetch_object ($result))
    $anchors[$row->Ch] = TRUE;

  // Display the list of anchors

  echo "<table width=\"100%\">\n";
  echo "  <tr>\n";

  foreach ($anchors as $key => $value)
  {
    if ($value)
      echo "    <td><a href=\"#$key\">$key</a></td>\n";
    else
      echo "    <td>$key</td>\n";
  }

  echo "  </tr>\n";
  echo "</table>\n";
  echo "<p>\n";

  // Now gather the list of users who've signed up for games

  $sql = 'SELECT Users.UserId, Users.FirstName, Users.LastName';
  $sql .= ', Users.Nickname, Users.CanSignup, Users.EMail';
  $sql .= ', Events.Title, Events.Hours, Events.CanPlayConcurrently';
  $sql .= ', Events.EventId';
  $sql .= ', Runs.Day, Runs.StartHour, Runs.TitleSuffix';
  $sql .= ', Signup.State, Signup.RunId, Signup.SignupId, Signup.RunId';
  $sql .= ' FROM Signup, Runs, Users, Events';
  $sql .= ' WHERE Signup.State!="Withdrawn"';
  $sql .= '   AND Users.UserId=Signup.UserId';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= ' ORDER BY LastName, FirstName, Nickname, Day, StartHour';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for users games failed', $sql);

  // Go through each of the signup records and display them by user

  $cur_letter = '';

  while ($row = mysql_fetch_object ($result))
  {
    $name = "$row->LastName, $row->FirstName";
    if (($row->Nickname != '') && ($row->Nickname != $row->FirstName))
      $name .= " ($row->Nickname)";

    // If we're starting a new user, close the table

    if (('' != $cur_name) && ($name != $cur_name))
      echo "</table>\n\n<P>\n";

    //    echo "<!-- name: '$name', next user: '" . key($users) . "' -->\n";

    if (count($users) > 0)
    {
      // Display users who have not signed up for any games

      while (strcasecmp ($name, key ($users)) > 0)
      {
	$cur_name = sprintf ('<a href=mailto:%s>%s</A>',
			     $row->EMail,
			     key($users));

	$ch = strtoupper (substr ($cur_name, 0, 1));
	if ($cur_letter != $ch)
	  echo ("<a name=$ch>");

	printf ("<b><font size=\"+1\">%s</font></b><br>\n" .
		"<font color=\"red\">Not signed up for any games</font>\n\n<P>\n",
		$cur_name);

	if ($cur_letter != $ch)
	{
	  echo ("</a>");
	  $cur_letter = $ch;
	}

	array_shift ($users);
      }
    }

    if (0 == strcasecmp ($name, key ($users)))
      array_shift ($users);

    if ($name != $cur_name)
    {
      $cur_name = $name;

      $ch = strtoupper (substr ($cur_name, 0, 1));
      if ($cur_letter != $ch)
      {
	echo "<a name=$ch></a>\n";
	$cur_letter = $ch;
      }

      $name = sprintf ('<a href=mailto:%s>%s</A>',
		       $row->EMail,
		       $name);
      if ('Unpaid' == $row->CanSignup)
	$name = sprintf ('<font color=\"red">%s - Sanity check failure!  ' .
			 'This user is Unpaid!!!!!</font>',
			 $name);
      elseif ('Alumni' == $row->CanSignup)
	$name = sprintf ('<font color=\"red\">%s - Sanity check failure!  ' .
			 'This user is an Alumni!!!!!</font>',
			 $name);
      else
	$signedup_users++;
      printf ("<b><font size=\"+1\">%s</font></b><br>\n<table>\n",
	      $name);

      $last_day = '';
      $last_start_hour = 0;
      $last_end_hour = 0;
      $last_can_play_concurrently = 'N';
    }
    
    $game = trim ("$row->Title $row->TitleSuffix");
    $start_hour = $row->StartHour;
    $end_hour = $start_hour + $row->Hours;

    $game_time = start_hour_to_24_hour ($start_hour) . ' - ' .
                 start_hour_to_24_hour ($start_hour + $row->Hours);

//    echo "<!-- last_can_play_concurrently: $last_can_play_concurrently -->\n";
//    echo "<!-- CanPlayConcurrently: $row->CanPlayConcurrently -->\n";
    if (! (('Y' == $last_can_play_concurrently) ||
	   ('Y' == $row->CanPlayConcurrently)))
    {
      if ($last_day == $row->Day)
      {
	if ($last_end_hour > $start_hour)
	  $game = "<font color=\"red\">Conflict: $game</font>";
      }
    }

    $game = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d" target="_blank">%s</a>',
		     SCHEDULE_SHOW_SIGNUPS,
		     $row->EventId,
		     $row->RunId,
		     $game);

    $state = $row->State;
    if ('Waitlisted' == $state)
    {
      $wait = get_waitlist_number ($row->RunId, $row->SignupId);
      if (0 != $wait)
	$state .= ' #' . $wait;
    }

    echo "  <tr valign=\"top\">\n";
    echo "    <td>$row->Day&nbsp;&nbsp;&nbsp;</td>\n";
    echo "    <td nowrap>$game_time&nbsp;&nbsp;&nbsp;</td>\n";
    echo "    <td nowrap>$state&nbsp;&nbsp;&nbsp;</td>\n";
    echo "    <td>$game</td>\n";
    echo "  </tr>\n";

    // Save the information for this game to check for conflicts

    $last_day = $row->Day;
    $last_start_hour = $start_hour;
    $last_end_hour = $end_hour;
    $last_can_play_concurrently = $row->CanPlayConcurrently;
  }

  echo "</table>\n";
  echo "<p>\n";

  // Get the last entries in the list or users who haven't signedup for
  // anything

  while (count($users) > 0)
  {
    $name = key ($users);

    $ch = strtoupper (substr ($name, 0, 1));
    //    echo "<!-- ch: $ch, cur_letter: $cur_letter, name: $name -->\n";
    if ($cur_letter != $ch)
    {
      $name = sprintf ('<a name=%s>%s</a>', $ch, $name);
      $cur_letter = $ch;
    }

    printf ("<b><font size=\"+1\">%s</font></b><br>\n" .
	    "<font color=\"red\">Not signed up for any games</font>\n\n<P>\n",
	    $name);
    array_shift($users);
  }

  $percent = (float)$signedup_users / (float)($users_count);
  //  echo "percent: $percent<p>\n";

  printf ("%d out of %d (%.1f%%) attendees have signed up for at least one game<P>\n",
	  $signedup_users,
	  $users_count,
	  100.0 * $percent);
/*
  foreach ($users as $k => $v)
    echo "'$k' -> '$v'<BR>\n";
*/
}
?>
