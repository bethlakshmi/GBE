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

 echo "<H2>Games of Choice</H2>\n";
 echo "<p>GMs are excluded from this list</p>\n";

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
  $sql .= ' ORDER BY Signup.SignupId';
  //  $sql .= ' LIMIT 20';

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
	bump_choice ($row->Title, $choice_1_games);
	break;

      case 2:
	bump_choice ($row->Title, $choice_2_games);
	break;

      case 3:
	bump_choice ($row->Title, $choice_3_games);
	break;

      case 4:
	bump_choice ($row->Title, $choice_4_games);
	break;

      case 5:
	bump_choice ($row->Title, $choice_5_games);
	break;

      case 6:
	bump_choice ($row->Title, $choice_6_games);
	break;

      case 7:
	bump_choice ($row->Title, $choice_7_games);
	break;

      case 8:
	bump_choice ($row->Title, $choice_8_games);
	break;

      case 9:
	bump_choice ($row->Title, $choice_9_games);
	break;

      case 10:
	bump_choice ($row->Title, $choice_A_games);
	break;
      }
    }
  }

  dump_choices ('First', $choice_1_games);
  dump_choices ('Second', $choice_2_games);
  dump_choices ('Third', $choice_3_games);
  dump_choices ('Fourth', $choice_4_games);
  dump_choices ('Fifth', $choice_5_games);
  dump_choices ('Sixth', $choice_6_games);
  dump_choices ('Seventh', $choice_7_games);
  dump_choices ('Eighth', $choice_8_games);
  dump_choices ('Nineth', $choice_9_games);
  dump_choices ('Tenth', $choice_A_games);
}

function dump_choices ($choice, $games)
{
  arsort ($games);
  echo "<h3>$choice Choice Games:</h3>\n";
  echo "<table border=1>\n";
  echo "  <tr>\n";
  echo "    <th>Players</th>\n";
  echo "    <th align=left>Game</th>\n";
  echo "  </tr>\n";

  foreach ($games as $title => $count)
  {
    echo "  <tr valign=top>\n";
    echo "    <td>$count</td>\n";
    echo "    <td>$title</td>\n";
    echo "  </tr>\n";
  }
  echo "<table>\n";
  echo "<p>\n";
}
?>