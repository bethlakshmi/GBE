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

// Do the work

signup_spy ();

// Add the postamble

html_end ();

function bump_choice ($EventId, &$game_array)
{
  if (array_key_exists ($EventId, $game_array))
    $game_array[$EventId]++;
  else
    $game_array[$EventId] = 1;
}

/*
 * signup_spy
 *
 * Display the last N signups
 */

function signup_spy ()
{
  // You need ConCom privilege to see this page

  if (! user_has_priv (PRIV_CON_COM))
    return display_access_error ();

  echo "<H2>Event Runs of Choice</H2>\n";
  echo "<p>The people in charge of the event are excluded from this list</p>\n";

  // Get the list of GMs

  $sql = 'SELECT UserId, EventId FROM GMs ORDER BY EventId';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs failed', $sql);

  $gms = array ();
  while ($row = mysql_fetch_object ($result))
  {
    $gms["$row->EventId,$row->UserId"] = 1;
  }

  // Get the list of games

  $sql = 'SELECT Title, EventId FROM Events WHERE SpecialEvent=0';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for games failed', $sql);

  $games = array();

  while ($row = mysql_fetch_object ($result))
    $games[$row->EventId] = $row->Title;

  asort ($games);

  if (array_key_exists ('UntilGame', $_POST))
    $UntilGame = stripslashes ($_POST['UntilGame']);
  else
    $UntilGame = '';

  echo "<form method=post action=Choice3.php>\n";
  echo "Count until full:\n";
  echo "<select name=UntilGame>\n";
  if ('' == $UntilGame)
    echo "<option selected>&lt;All Signups&gt;\n";
  else
    echo "<option>&lt;All Signups&gt;\n";

  foreach ($games as $title)
  {
    if ($UntilGame == $title)
      echo "<option selected>$title\n";
    else
      echo "<option>$title\n";
  }
  echo "</select>\n";

  echo "<input type=submit value=\"Update\">\n";
  echo "</form>\n";

  //  echo "UntilGame: $UntilGame<br>\n";
  // Get the max SignupId

  if ('' == $UntilGame)
    $max_signup_id = 0;
  else
  {
    $EventId = array_search ($UntilGame, $games);
    if (FALSE != $EventId)
    {
      //  echo "EventId: $EventId<br>\n";
      $sql = 'SELECT Signup.SignupId, Signup.UserId, Runs.EventId';
      $sql .= ' FROM Signup, Runs';
      $sql .= ' WHERE Signup.State="Confirmed"';
      $sql .= '   AND Runs.RunId=Signup.RunId';
      $sql .= "   AND Runs.EventId=$EventId";
      $sql .= ' ORDER BY Signup.SignupId';
      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ('Query for Max Signup failed', $sql);

      while ($row = mysql_fetch_object ($result))
      {
	if (! array_key_exists ("$row->EventId,$row->UserId", $gms))
        {
	  //	  echo "SignupId: $row->SignupId<br>\n";
	  $max_signup_id = $row->SignupId;
	}
      }
    }
  }

  if ('' != $UntilGame)
  {
    echo "<i>$UntilGame</i> filled at SignupId: $max_signup_id.<br>\n";
    echo "Note that there may be some players waitlisted for this run due\n";
    echo "to quotas.<p>\n";
  }

  $user_signups = array ();
  $choice_1_games = array ();
  $choice_2_games = array ();
  $choice_3_games = array ();
  $choice_4_games = array ();
  $choice_5_games = array ();
  $choice_6_games = array ();
  $choice_7_games = array ();
  $choice_8_games = array ();
  $choice_9_games = array ();
  $choice_A_games = array ();
  $choice_1_games_confirmed = array ();
  $choice_2_games_confirmed = array ();
  $choice_3_games_confirmed = array ();
  $choice_4_games_confirmed = array ();
  $choice_5_games_confirmed = array ();
  $choice_6_games_confirmed = array ();
  $choice_7_games_confirmed = array ();
  $choice_8_games_confirmed = array ();
  $choice_9_games_confirmed = array ();
  $choice_A_games_confirmed = array ();

  foreach ($games as $title)
  {
    $user_signups[$title] = 0;
    $choice_1_games[$title] = 0;
    $choice_1_games_confirmed[$title] = 0;
    $choice_2_games[$title] = 0;
    $choice_2_games_confirmed[$title] = 0;
    $choice_3_games[$title] = 0;
    $choice_3_games_confirmed[$title] = 0;
    $choice_4_games[$title] = 0;
    $choice_4_games_confirmed[$title] = 0;
    $choice_5_games[$title] = 0;
    $choice_5_games_confirmed[$title] = 0;
    $choice_6_games[$title] = 0;
    $choice_6_games_confirmed[$title] = 0;
    $choice_7_games[$title] = 0;
    $choice_7_games_confirmed[$title] = 0;
    $choice_8_games[$title] = 0;
    $choice_8_games_confirmed[$title] = 0;
    $choice_9_games[$title] = 0;
    $choice_9_games_confirmed[$title] = 0;
    $choice_A_games[$title] = 0;
    $choice_A_games_confirmed[$title] = 0;
  }

  // Get the signups

  $sql = 'SELECT Signup.SignupId, Signup.State, Signup.PrevState,';
  $sql .= ' Users.FirstName, Users.LastName, Signup.UserId,';
  $sql .= ' DATE_FORMAT(Signup.TimeStamp, "%d-%b-%Y %H:%i") AS Timestamp,';
  $sql .= ' Events.Title, Runs.TitleSuffix, Runs.Day, Runs.StartHour,';
  $sql .= ' Signup.UserId, Runs.EventId';
  $sql .= ' FROM Signup, Runs, Events, Users';
  $sql .= ' WHERE Users.UserId=Signup.UserId';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  if (0 != $max_signup_id)
    $sql .= "   AND Signup.SignupId<=$max_signup_id";
  $sql .= ' ORDER BY Signup.SignupId';


  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signups failed', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo "No signups found\n";
    return true;
  }

  while ($row = mysql_fetch_object ($result))
  {
    if (! array_key_exists ("$row->EventId,$row->UserId", $gms))
    {
      if (array_key_exists ($row->UserId, $user_signups))
	$user_signups[$row->UserId]++;
      else
	$user_signups[$row->UserId] = 1;

      $choice_num = $user_signups[$row->UserId];

      switch ($choice_num)
      {
      case 1:
	$choice_1_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_1_games_confirmed[$row->Title]++;
	break;

      case 2:
	$choice_2_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_2_games_confirmed[$row->Title]++;
	break;

      case 3:
	$choice_3_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_3_games_confirmed[$row->Title]++;
	break;

      case 4:
	$choice_4_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_4_games_confirmed[$row->Title]++;
	break;

      case 5:
	$choice_5_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_5_games_confirmed[$row->Title]++;
	break;

      case 6:
	$choice_6_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_6_games_confirmed[$row->Title]++;
	break;

      case 7:
	$choice_7_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_7_games_confirmed[$row->Title]++;
	break;

      case 8:
	$choice_8_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_8_games_confirmed[$row->Title]++;
	break;

      case 9:
	$choice_9_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_9_games_confirmed[$row->Title]++;
	break;

      case 10:
	$choice_A_games[$row->Title]++;
        if ('Confirmed' == $row->State)
	  $choice_A_games_confirmed[$row->Title]++;
	break;
      }
    }
  }
  echo "Numbers are presented as &quot;Confirmed / All Signups&quot;.\n";
  echo "All Signups include Confirmed, Waitlisted and Withdrawn.\n";

  echo "<h3>Event Choices:</h3>\n";
  echo "<table border=1>\n";
  echo "  <tr align=center>\n";
  echo "    <th align=left valign=bottom>Event</th>\n";
  for ($i = 1; $i <= 10; $i++)
    echo "    <th>&nbsp;&nbsp;$i&nbsp;&nbsp;</th>\n";
  echo "    <th>Total</th>\n";
  echo "  </tr>\n";

  $total1 = 0;
  $total2 = 0;
  $total3 = 0;
  $total4 = 0;
  $total5 = 0;
  $total6 = 0;
  $total7 = 0;
  $total8 = 0;
  $total9 = 0;
  $totalA = 0;

  $total1_confirmed = 0;
  $total2_confirmed = 0;
  $total3_confirmed = 0;
  $total4_confirmed = 0;
  $total5_confirmed = 0;
  $total6_confirmed = 0;
  $total7_confirmed = 0;
  $total8_confirmed = 0;
  $total9_confirmed = 0;
  $totalA_confirmed = 0;

  foreach ($games as $title)
  {
    $row_total = 0;
    $row_total_confirmed = 0;

    echo "  <tr align=center>\n";
    echo "    <td align=left>$title</td>\n";

    dump_choices ($title,
		  $choice_1_games_confirmed, $choice_1_games,
		  $total1, $total1_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_2_games_confirmed, $choice_2_games,
		  $total2, $total2_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_3_games_confirmed, $choice_3_games,
		  $total3, $total3_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_4_games_confirmed, $choice_4_games,
		  $total4, $total4_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_5_games_confirmed, $choice_5_games,
		  $total5, $total5_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_6_games_confirmed, $choice_6_games,
		  $total6, $total6_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_7_games_confirmed, $choice_7_games,
		  $total7, $total7_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_8_games_confirmed, $choice_8_games,
		  $total8, $total8_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_9_games_confirmed, $choice_9_games,
		  $total9, $total9_confirmed,
		  $row_total, $row_total_confirmed);

    dump_choices ($title,
		  $choice_A_games_confirmed, $choice_A_games,
		  $totalA, $totalA_confirmed,
		  $row_total, $row_total_confirmed);

    echo "    <th>$row_total_confirmed&nbsp;/&nbsp;$row_total</th>\n";
    echo "  </tr>\n";
  }
  echo "  <tr align=center>\n";
  echo "    <th align=left>Totals</th>\n";
  echo "    <th>$total1_confirmed&nbsp;/&nbsp;$total1</th>\n";
  echo "    <th>$total2_confirmed&nbsp;/&nbsp;$total2</th>\n";
  echo "    <th>$total3_confirmed&nbsp;/&nbsp;$total3</th>\n";
  echo "    <th>$total4_confirmed&nbsp;/&nbsp;$total4</th>\n";
  echo "    <th>$total5_confirmed&nbsp;/&nbsp;$total5</th>\n";
  echo "    <th>$total6_confirmed&nbsp;/&nbsp;$total6</th>\n";
  echo "    <th>$total7_confirmed&nbsp;/&nbsp;$total7</th>\n";
  echo "    <th>$total8_confirmed&nbsp;/&nbsp;$total8</th>\n";
  echo "    <th>$total9_confirmed&nbsp;/&nbsp;$total9</th>\n";
  echo "    <th>$totalA_confirmed&nbsp;/&nbsp;$totalA</th>\n";
  echo "    <th>&nbsp;</th>\n";
  echo "  </tr>\n";
  echo "</table>\n";
}


function dump_choices ($index, &$confirmed_array, &$all_array,
		       &$total, &$total_confirmed,
		       &$row_total, &$row_total_confirmed)
{
    printf ("    <td>%d&nbsp;/&nbsp;%d</td>\n",
	    $confirmed_array[$index],
	    $all_array[$index]);

    $total += $all_array[$index];
    $total_confirmed += $confirmed_array[$index];

    $row_total += $all_array[$index];
    $row_total_confirmed += $confirmed_array[$index];
}