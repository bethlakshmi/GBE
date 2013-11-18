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

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = SHOW_LIMITED_SIGNUPS;

// Do the work

switch ($action)
{
  case SHOW_LIMITED_SIGNUPS:
    signup_spy (true);
    break;

  case SHOW_ALL_SIGNUPS:
    signup_spy (false);
    break;

  default:
    display_error ("Unknown action code: $action");
}

// Add the postamble

html_end ();

/*
 * signup_spy
 *
 * Display the last N signups
 */

function signup_spy ($limited)
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

  if ($limited)
    echo "<h2>Last 100 Signups - Now Including Withdrawals</h2>\n";
  else
    echo "<h2>All Signups</h2>\n";
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

  // Get the last 50 signups, in reverse order

  $sql = 'SELECT Signup.SignupId, Signup.State, Signup.PrevState,';
  $sql .= ' Users.DisplayName, ';
  $sql .= ' DATE_FORMAT(Signup.TimeStamp, "%d-%b-%Y %H:%i") AS Timestamp,';
  $sql .= ' Events.Title, Runs.TitleSuffix, Runs.Day, Runs.StartHour,';
  $sql .= ' Signup.UserId, Runs.EventId';
  $sql .= ' FROM Signup, Runs, Events, Users';
  $sql .= ' WHERE Users.UserId=Signup.UserId';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= ' ORDER BY Signup.TimeStamp DESC, Signup.SignupId DESC';
  if ($limited)
    $sql .= ' LIMIT 100';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signups failed', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo "No signups found\n";
    return true;
  }
  echo "This describes signup of regular participants, it does not list teachers, panelists, heads of staff or other presenters.";
  $confirmed_color = get_bgcolor('Confirmed');
  $waitlisted_color = get_bgcolor('Waitlisted');
  $withdrawn_color = get_bgcolor('Full');

  echo "<table border=1>\n";
  echo "  <tr>\n";
  echo "    <th>ID</th>\n";
  echo "    <th align=left>Participant</th>\n";
  echo "    <th>Prev State</th>\n";
  echo "    <th>State</th>\n";
  echo "    <th align=center>Timestamp</th>\n";
  echo "    <th align=left>Event Run</th>\n";
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

    $name = trim ("$row->DisplayName");

    $game = trim ("$row->Title $row->TitleSuffix");
    $game .= ", $row->Day " . start_hour_to_24_hour ($row->StartHour);
    if (array_key_exists ("$row->EventId,$row->UserId", $gms))
      $game .= " <b>(in charge)</b>";

    $prev_state = $row->PrevState;
    if ('None' == $prev_state)
      $prev_state = '&nbsp;';

    echo "  <tr valign=top $bgcolor>\n";
    echo "    <td>$row->SignupId</td>\n";
    echo "    <td>$name</td>\n";
    echo "    <td align=center>$prev_state</td>\n";
    echo "    <td align=center>$row->State</td>\n";
    echo "    <td align=center>$row->Timestamp</td>\n";
    echo "    <td>$game</td>\n";
    echo "  </tr>\n";
  }
  echo "<table>\n";
  echo "<p>\n";
}
?>