<?php

// Include common stuff

include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display the preamble

html_begin ();

// All functions in this file require scheduling priv

if (! user_has_priv (PRIV_SCHEDULING))
{
  display_access_error ();
  html_end ();
  exit ();
}

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = SPECIAL_EVENT_FORM;

// echo "Action: $action\n";

switch ($action)
{
  case SPECIAL_EVENT_FORM:
    display_special_event_form ();
    break;

  case SPECIAL_EVENT_ADD:
    if (! process_special_event_form ())
      display_special_event_form ();
    else
      list_special_events ();
    break;

  case SPECIAL_EVENT_LIST:
    list_special_events ();
    break;

  default:
    display_error ("Unknown SpecialEvent action code: $action");
}

// Add the postamble

html_end ();


/*
 * display_special_event_form
 *
 * Display the special event form and let the user fill it in
 */

function display_special_event_form ()
{
  global $SPECIAL_EVENT_TYPES;

  $update = isset ($_REQUEST['RunId']);
  
  // If this is an update, load the $_POST array from the database

  if ($update)
  {
    $RunId = intval (trim ($_REQUEST['RunId']));

    $sql = 'SELECT Events.Title, Events.Hours, Events.Description,';
    $sql .= ' Events.ShortBlurb, Events.GameType, Runs.*';
    $sql .= ' FROM Runs, Events';
    $sql .= " WHERE RunId=$RunId";
    $sql .= '   AND Events.EventId=Runs.Eventid';

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ("Query failed for RunId $RunId");

    $row = mysql_fetch_array ($result, MYSQL_ASSOC);

    foreach ($row as $k => $v)
      $_POST[$k] = $v;

    $_POST['Rooms'] = explode(',', $row['Rooms']);

    $EventId = $row['EventId'];

    echo "<h2>Update a special event for ".CON_NAME."</h2>\n";
  }
  else
    echo "<h2>Add a special event for ".CON_NAME."</h2>\n <br> Note - this is available only to privileged users, and includes ALL rooms.  Room coordination requires communication with head of convention.<br>";

  echo "<form method=POST action=\"SpecialEvents.php\">\n";
  form_add_sequence ();
  form_hidden_value('action', SPECIAL_EVENT_ADD);
  $type = $SPECIAL_EVENT_TYPES[0];
  
  if ($update)
  {
    form_hidden_value('RunId', $RunId);
    form_hidden_value('EventId', $EventId);
    $type = $_POST['GameType'];
  }

  echo "<table border=\"0\">\n";
  
  form_text (64, 'Event Text', 'Title');
  form_day ('Day');
  form_start_hour ('Start Hour', 'StartHour');
  form_text (2, 'Blocks', "Hours");
  echo "<h3>Events are scheduled in half-hour \"blocks\", so 2 blocks = 1 hour, 3 blocks = 90 minutes, etc.</h3><br>";
  
  echo "  <tr>\n";
  echo "    <td>\n";
  echo "Event Type:";
  echo "    </td><td>";
  form_single_select('', 'GameType', $SPECIAL_EVENT_TYPES, $type);
  echo "	</td></tr>\n";
  
  echo "  <tr>\n";
  echo "    <td colspan=2>\n";
  echo "      Leave the descriptions blank if you don't want them included\n";
  echo "      in the list of events or to have a \"event page\".\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  form_textarea ('Short Description', 'ShortBlurb', 4, TRUE, TRUE);
  form_textarea ('Description', 'Description', 20, TRUE, TRUE);

  form_con_rooms('Room(s)', 'Rooms');

  if ($update)
    form_submit2 ('Update Event', 'Delete Event', 'DeleteRun');
  else
    form_submit ('Add Event');

  echo "</table>\n";
  echo "</form>\n";

  display_valid_start_times ();
}

/*
 * process_special_event_form
 *
 * Add or update a special event
 */

function process_special_event_form ()
{
  // If we're out of sequence, don't do anything

  if (out_of_sequence ())
    return display_sequence_error (false);

  $update = isset ($_POST['RunId']);

  $Title = trim ($_POST['Title']);

  if ($update)
  {
    $verb = 'UPDATE';
    $action_failed = 'Update for';

    $EventId = intval (trim ($_POST['EventId']));
    $RunId = intval (trim ($_POST['RunId']));

    // If DeleteRun is one of the Post parameters the user has asked us to
    // delete a run.

    if (isset ($_POST['DeleteRun']))
    {
      $sql = "DELETE FROM Runs WHERE RunId=$RunId";

      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Failed to delete run $RunId");

      // Check for additional runs

      $sql = "SELECT RunId FROM Runs WHERE EventId=$EventId";

      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Query failed for Runs of EventId $EventId");
      // If there are additional runs, we're done

      if (0 == mysql_num_rows ($result))
      {
	// No additional runs.  Delete the event.  Neatness counts

	$sql = "DELETE FROM Events WHERE EventId=$EventId";
      
	$result = mysql_query ($sql);
	if (! $result)
	  return display_mysql_error ("Failed to delete event $EventId");
      }
      
      echo "Deleted special event <I>$Title</I><P>\n";

      return true;
    }
  }
  else
  {
    $verb = 'INSERT';
    $action_failed = 'Insert into';

    // First make sure that we don't already have a special event with this
    // title

    if (! title_not_in_events_table ($Title))
	return false;
  }

  // Validate the track

  if (! validate_day_time ('StartHour', 'Day'))
    return FALSE;

  $Rooms = '';
  if (array_key_exists('Rooms', $_POST))
    $Rooms = implode(',', $_POST['Rooms']);

  $sql = "$verb Events SET Title='$Title', SpecialEvent=1";
  $sql .= build_sql_string ('GameType');
  $sql .= build_sql_string ('Hours');
  $sql .= build_sql_string ('ShortBlurb');
  $sql .= build_sql_string ('Description');
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);
  if ($update)
    $sql .= " WHERE EventId=$EventId";

  //  echo $sql . '<p>';

  $result = mysql_query ($sql);
  if (! $result)
    return display_error ("$action_failed Events table failed: " . mysql_error ());

  if (! $update)
    $EventId = mysql_insert_id ();

  $sql = "$verb Runs SET EventId=$EventId";
  $sql .= build_sql_string ('Day');
  $sql .= build_sql_string ('StartHour');
  $sql .= build_sql_string ('Rooms', $Rooms);
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);
  if ($update)
    $sql .= " WHERE RunId=$RunId";

  //  echo $sql . '<p>';

  $result = mysql_query ($sql);
  if (! $result)
    return display_error ("$action_failed Runs table failed: " . mysql_error ());

  return TRUE;
}

/*
 * list_special events
 *
 * List the special events in the database ordered by time
 */

function list_special_events ()
{
  echo "<h2>Special Events</h2>";

  $sql = 'SELECT Runs.RunId, Runs.EventId, Runs.StartHour,';
  $sql .= ' Runs.Day, Runs.Rooms,';
  $sql .= ' Events.Hours, Events.Title';
  $sql .= ' FROM Events, Runs';
  $sql .= ' WHERE Events.EventId=Runs.EventId AND Events.SpecialEvent=1';
  $sql .= ' ORDER BY Runs.Day, Runs.StartHour, Runs.Track';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query special event list', $sql);

  if (0 == mysql_num_rows ($result))
    return display_error ('No special events in database');

  echo "<b>\n";
  echo "Click on a special event title to edit or delete it.<br>\n";
  echo "</b>\n";

  echo "<table border=\"1\">\n";
  echo "  <tr>\n";
  echo "    <th>Special Event</th>\n";
  echo "    <th>Day</th>\n";
  echo "    <th>Start Time</th>\n";
  echo "    <th>Blocks</th>\n";
  echo "    <th>Room(s)</th>\n";
  echo "  </tr>\n";

  while ($row = mysql_fetch_object ($result))
  {
    $start_time = start_hour_to_am_pm ($row->StartHour);

    echo "  <tr valign=\"top\">\n";
    printf ("    <td><a href=\"SpecialEvents.php?action=%d&RunId=%d\">%s</a>\n",
	    SPECIAL_EVENT_FORM,
	    $row->RunId,
	    $row->Title);
    echo "    <td align=\"center\">$row->Day</td>\n";
    echo "    <td align=\"center\">$start_time</td>\n";
    echo "    <td align=\"center\">$row->Hours</td>\n";
    printf ("    <td>%s</td>\n", pretty_rooms($row->Rooms));
    echo "  </tr>\n";
  }
  echo "</table>\n";
}



?>