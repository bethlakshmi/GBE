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

  // Get the total number of signups

  $sql = 'SELECT State, COUNT(*) AS Count FROM Signup GROUP BY State';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signup counts failed', $sql);

  $count = array ('Confirmed'=>0, 'Waitlisted'=>0, 'Withdrawn'=>0);

  while ($row = mysql_fetch_object ($result))
    $count[$row->State] = $row->Count;

  $total = $count['Confirmed'] + $count['Waitlisted'];

  echo "<H2>Signups with Choice Numbers</H2>\n";
  echo "<B>Total Signups:</B> $total (Confirmed + Waitlisted)<BR>\n";
  foreach ($count as $key => $value)
    echo "<B>$key:</B> $value &nbsp; &nbsp; &nbsp;\n";
  echo "<P>\n";

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
  //  $sql .= ' LIMIT 100';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signups failed', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo "No signups found\n";
    return true;
  }

  $confirmed_color = get_bgcolor('Confirmed');
  $waitlisted_color = get_bgcolor('Waitlisted');
  $withdrawn_color = get_bgcolor('Full');

  echo "<table border=1>\n";
  echo "  <tr>\n";
  echo "    <th>ID</th>\n";
  echo "    <th align=left>Player</th>\n";
  echo "    <th align=center>Choice #</th>\n";
  //  echo "    <th>Prev State</th>\n";
  echo "    <th>State</th>\n";
  echo "    <th align=center>Timestamp</th>\n";
  echo "    <th align=left>Game</th>\n";
  echo "  </tr>\n";
  while ($row = mysql_fetch_object ($result))
  {
    switch ($row->State)
    {
      case 'Confirmed':  $bgcolor = $confirmed_color;  break;
      case 'Waitlisted': $bgcolor = $waitlisted_color; break;
      case 'Withdrawn':  $bgcolor = $withdrawn_color; break;
      default: $bgcolor = '';  break;
    }

    $name = trim ("$row->LastName, $row->FirstName");

    $game = trim ("$row->Title $row->TitleSuffix");
    $game .= ", $row->Day " . start_hour_to_24_hour ($row->StartHour);
    if (array_key_exists ("$row->EventId,$row->UserId", $gms))
    {
      $choice_num = "GM";
      $game .= " <b>(GM)</b>";
    }
    else
    {
      if (array_key_exists ($row->UserId, $user_signups))
	$user_signups[$row->UserId]++;
      else
	$user_signups[$row->UserId] = 1;
      $choice_num = $user_signups[$row->UserId];
    }

    $prev_state = $row->PrevState;
    if ('None' == $prev_state)
      $prev_state = '&nbsp;';

    echo "  <tr valign=top $bgcolor>\n";
    echo "    <td>$row->SignupId</td>\n";
    echo "    <td>$name</td>\n";
    echo "    <td align=center>$choice_num</td>\n";
    //    echo "    <td align=center>$prev_state</td>\n";
    echo "    <td align=center>$row->State</td>\n";
    echo "    <td align=center>$row->Timestamp</td>\n";
    echo "    <td>$game</td>\n";
    echo "  </tr>\n";
  }
  echo "<table>\n";
  echo "<p>\n";
}
?>