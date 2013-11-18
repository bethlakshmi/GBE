<?php
define (LIST_BY_GAME, 1);
define (LIST_BY_RUNID, 2);
define (LIST_BY_TIME, 3);

include ("intercon_db.inc");

global $LIST_GAME_TEXT;
$LIST_GAME_TEXT = "\n";
$LIST_GAME_TEXT .= "Click on a title to schedule a new run.<br>\n";
$LIST_GAME_TEXT .= "Click on a start time to edit or delete an existing run.<br><br>\n";
$LIST_GAME_TEXT .= "NOTE: Events are scheduled in half-hour blocks. For example,  2 blocks = 1 hour, 3 blocks = 90 minutes <br><br>\n";
$LIST_GAME_TEXT .= "\n";

// Connect to the database -- Really should require staff privilege

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Standard header stuff

html_begin ();

// Everything in this file requires privileges to run

if (! user_has_priv (PRIV_SCHEDULING))
{
  display_access_error ();
  html_end ();
  exit ();
}

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = LIST_GAMES;

switch ($action)
{
 case LIST_GAMES:
   list_games (LIST_BY_GAME);
   break;

 case LIST_GAMES_BY_TIME:
   list_games (LIST_BY_TIME, FALSE);
   break;

 case ADD_RUN:
   add_run_form (false);
   break;

 case PROCESS_ADD_RUN:
   if (process_add_run ())
     list_games ();
   else
     add_run_form (false);
   break;

 case PROCESS_EDIT_RUN:
   if (process_add_run ())
     list_games ();
   else
     add_run_form (true);
   break;

 case EDIT_RUN:
   add_run_form (true);
   break;
   

 case LIST_OPS:
   add_ops (LIST_BY_GAME);
   break;

 case LIST_OPS_BY_TIME:
   add_ops (LIST_BY_TIME);
   break;

 case LIST_ADD_OPS:
   add_ops();
   break;

 case PROCESS_ADD_OPS:
   process_add_ops ();
   break;
 

 default:
   echo "Unknown action code: $action\n";
}

html_end();

/*
 * list_games
 *
 * List the games by the list type
 */

function list_games ($type = 0, $showOps=FALSE)
{
  if (0 == $type)
    $type = $_SESSION['ListType'];
  else
  {
    session_register ('ListType');
    $_SESSION['ListType'] = $type;
  }

  switch ($type)
  {
    case LIST_BY_GAME:
      return list_games_alphabetically ($showOps);
      break;

    case LIST_BY_RUNID:
      return list_games_by ($type,$showOps);
      break;

    case LIST_BY_TIME:
      return list_games_by ($type,$showOps);
      break;

    default:
      return display_error ("Invalid ListType: $type");
  }

  return false;
}

/*
 * list_games_alphabetically
 *
 * List the games in the database alphabetically by title
 */

function list_games_alphabetically ($showOps=FALSE)
{
  $sql = 'SELECT EventId, GameType, Title, Hours FROM Events';
  $sql .= ' WHERE ';
  $sql .= ' (SpecialEvent=0 OR GameType = \'Drop-In\') ';
  if ($showOps)
    $sql .= ' AND IsOps = \'Y\'';
  else
    $sql .= ' AND IsOps = \'N\'';  
  $sql .= ' ORDER BY Title';

  $game_result = mysql_query ($sql);
  if (! $game_result)
    return display_error ('Cannot query game list: ' . mysql_error());

  if (0 == mysql_num_rows ($game_result))
    return display_error ('Nothing is available to show.');

  global $LIST_GAME_TEXT;
  echo $LIST_GAME_TEXT;
  
  $action = 0;
  
  if ($showOps)
    $action = LIST_OPS_BY_TIME;
  else
    $action = LIST_GAMES_BY_TIME;

  printf ("<a href=\"ListGames.php?action=%d\">Order Chronologically</a><p>\n",
	  $action);

  echo "<table border=\"1\">\n";
  echo "  <tr>\n";
  echo "    <th>Title</th>\n";
  echo "    <th>Blocks</th>\n";
  echo "    <th>Day</th>\n";
  echo "    <th>Start Time</th>\n";
  echo "    <th>Run Suffix</th>\n";
  echo "    <th>Schedule Note</th>\n";
  echo "    <th>Room(s)</th>\n";
  echo "  </tr>\n";

  while ($game_row = mysql_fetch_object ($game_result))
  {
    $sql = 'SELECT RunId, Track, Day, Span, TitleSuffix, ScheduleNote,';
    $sql .= ' StartHour, Rooms';
    $sql .= ' FROM Runs';
    $sql .= ' WHERE EventId=' . $game_row->EventId;
    $sql .= ' ORDER BY Day, StartHour';

    $runs_result = mysql_query ($sql);
    if (! $runs_result)
      return display_error ("Cannot query runs for Event $game_row->EventId: " . mysql_error());

    $rowspan = mysql_num_rows ($runs_result);
    if (0 == $rowspan)
      $rowspan = 1;

    echo "  <tr valign=\"top\">\n";
    
    $action = 0;
    if ($showOps)
      $action = LIST_OPS;
    else
      $action = ADD_RUN;
    printf ("    <td rowspan=\"%d\"><a href=\"ListGames.php?action=%d&EventId=%d\">%s</a>",
	    $rowspan,
	    $action,
	    $game_row->EventId,
	    $game_row->Title);

    
    printf("  </td>\n");

    printf ("    <td rowspan=\"%d\" align=\"center\">%d</td>\n",
	    $rowspan,
	    $game_row->Hours);

    //    echo "<!-- NumRows: " . mysql_num_rows ($runs_result) . "-->\n";

    if (0 == mysql_num_rows ($runs_result))
    {
      echo "    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>\n";
      echo "  </tr>\n";
    }
    else
    {
      $runs_row = mysql_fetch_object ($runs_result);

      echo "    <td align=\"center\">$runs_row->Day</td>\n";

      $start_time = start_hour_to_am_pm ($runs_row->StartHour);

      printf ("    <td align=\"center\"><a href=\"ListGames.php?action=%d&RunId=%d\">%s</a></td>\n",
	      EDIT_RUN,
	      $runs_row->RunId,
	      $start_time);

      $suffix = $runs_row->TitleSuffix;
      if ('' == $suffix)
	$suffix = '&nbsp;';
      echo "    <td>$suffix</td>\n";

      $note = $runs_row->ScheduleNote;
      if ('' == $note)
	$note = '&nbsp;';
      echo "    <td>$note</td>\n";

      printf ("    <td>%s</td>\n", pretty_rooms($runs_row->Rooms));

      echo "  </tr>\n";

      while ($runs_row = mysql_fetch_object ($runs_result))
      {
	echo "  <tr>\n";
        echo "    <td align=\"center\">$runs_row->Day</td>\n";

	$start_time = start_hour_to_am_pm ($runs_row->StartHour);

	printf ("    <td align=\"center\"><a href=\"ListGames.php?action=%d&RunId=%d\">%s</a></td>\n",
		EDIT_RUN,
		$runs_row->RunId,
		$start_time);

        $suffix = $runs_row->TitleSuffix;
        if ('' == $suffix)
	  $suffix = '&nbsp;';
        echo "    <td>$suffix</td>\n";

	$note = $runs_row->ScheduleNote;
	if ('' == $note)
	  $note = '&nbsp;';
	echo "    <td>$note</td>\n";

	printf ("    <td>%s</td>\n", pretty_rooms($runs_row->Rooms));

        echo "  </tr>\n";
      }
    }
  }
  echo "</table>\n";
}

/*
 * list_games_by
 *
 * List the games in the database ordered by RunId
 */

function list_games_by ($type, $showOps=FALSE)
{
  $sql = 'SELECT Runs.RunId, Runs.Track, Runs.TitleSuffix, Runs.Span,';
  $sql .= ' Runs.StartHour, Runs.Day, Runs.EventId, Runs.ScheduleNote,';
  $sql .= ' Events.Hours, Events.Title, Runs.Rooms';
  $sql .= ' FROM Events, Runs';
  $sql .= ' WHERE Events.EventId=Runs.EventId AND ';
  $sql .= ' (Events.SpecialEvent=0 OR Events.GameType = \'Drop-In\') ';
  if ($showOps)
    $sql .= ' AND Events.IsOps = \'Y\'';
  else
    $sql .= ' AND Events.IsOps = \'N\'';  


  switch ($type)
  {
    case LIST_BY_TIME:
      $sql .= ' ORDER BY Runs.Day, Runs.StartHour, Runs.Track';
      break;

    default:
      return display_error ("Invalid ListType: $type");
  }      

  $result = mysql_query ($sql);
  if (! $result)
    return display_error ('Cannot query list: ' . mysql_error());

  if (0 == mysql_num_rows ($result))
    return display_error ('No conference items are accepted/available.');

  global $LIST_GAME_TEXT;
  echo $LIST_GAME_TEXT;
  
  $action = 0;
  if ($showOps)
    $action = LIST_OPS;
  else
    $action = LIST_GAMES;

  printf ("<a href=\"ListGames.php?action=%d\">Order Alphabetically</a><p>\n",
	  $action);

  echo "<table border=\"1\">\n";
  echo "  <tr>\n";
  echo "    <th>Day</th>\n";
  echo "    <th>Start Time</th>\n";
  echo "    <th>Title</th>\n";
  echo "    <th>Run Suffix</th>\n";
  echo "    <th>Schedule Note</th>\n";
  echo "    <th>Room(s)</th>\n";
  echo "    <th>Blocks</th>\n";
  echo "  </tr>\n";

  while ($row = mysql_fetch_object ($result))
  {
    if (LIST_BY_TIME == $type)
    {
      if ((! empty ($day)) && ($day != $row->Day))
      {
	echo "  <tr>\n";
	echo "  <td colspan=\"9\">&nbsp;</td>\n";
	echo "  </tr>\n";
	echo "  <tr>\n";
	echo "    <th>Day</th>\n";
	echo "    <th>Start Time</th>\n";
	echo "    <th>Title</th>\n";
	echo "    <th>Run Suffix</th>\n";
	echo "    <th>Schedule Note</th>\n";
	echo "    <th>Room(s)</th>\n";
	echo "    <th>Blocks</th>\n";
	echo "  </tr>\n";
      }
      $day = $row->Day;
    }

    $start_time = start_hour_to_am_pm ($row->StartHour);

    echo "  <tr valign=\"top\">\n";
    echo "    <td align=\"center\">$row->Day</td>\n";
    printf ("    <td align=\"center\"><a href=\"ListGames.php?action=%d&RunId=%d\">%s</a></td>\n",
	    EDIT_RUN,
	    $row->RunId,
	    start_hour_to_am_pm($start_time));

    $action = 0;
    if ($showOps)
      $action = LIST_OPS;
    else
      $action = ADD_RUN;
    printf ("    <td><a href=\"ListGames.php?action=%d&EventId=%d\">%s</a></td>\n",
	    $action,
	    $row->EventId,
	    $row->Title);

    $suffix = $row->TitleSuffix;
    if ('' == $suffix)
      $suffix = '&nbsp;';
    echo "    <td>$suffix</td>\n";

    $note = $row->ScheduleNote;
    if ('' == $note)
      $note = '&nbsp;';
    echo "    <td>$note</td>\n";

    printf ("    <td>%s</td>\n", pretty_rooms($row->Rooms));

    echo "    <td align=\"center\">$row->Hours</td>\n";

    echo "  </tr>\n";
  }
  echo "</table>\n";
  echo "Note: Events are scheduled in half-hour blocks. 2 blocks = 1 hour, 3 blocks = 90 minutes.";
}

/*
 * add_run_form
 *
 * Display the form to add a run for an event
 */

function add_run_form ($update)
{
  // If we're updating the run, fill the $_POST array
  $showId = 0;
  
  if (! $update)
  {
    $action = "Add";

    $EventId = $_REQUEST['EventId'];
    $_POST['Span'] = 1;
  }
  else
  {
    $action = "Edit";

    $RunId = $_REQUEST['RunId'];

    $sql = 'SELECT EventId, Track, Span, Day, TitleSuffix, ScheduleNote,';
    $sql .= ' StartHour, TitleSuffix, Rooms, ShowId';
    $sql .= " FROM Runs WHERE RunId=$RunId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Cannot query run data for RunId $RunId",
				  $sql);

    if (0 == mysql_num_rows ($result))
      return display_error ("Cannot find RunId $RunId in the database!");

    if (1 != mysql_num_rows ($result))
      return display_error ("RunId $RunId matched more than 1 row!");

    $row = mysql_fetch_array ($result, MYSQL_ASSOC);

    //    dump_array ('row', $row);

    foreach ($row as $k => $v)
      $_POST[$k] = $v;

    $EventId = $row['EventId'];
    $_POST['Rooms'] = explode(',', $row['Rooms']);
    $showId = $row['ShowId'];
  }

  // Start by fetching the title, type & BidId 

  $sql = "SELECT Title, GameType FROM Events";
  $sql .= " WHERE EventId=$EventId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_error ("Cannot query title for EventId $EventId: " . mysql_error ());

  if (0 == mysql_num_rows ($result))
    return display_error ("Cannot find EventId $EventId in the database!");

  if (1 != mysql_num_rows ($result))
    return display_error ("EventId $EventId matched more than 1 row!");

  $row = mysql_fetch_object ($result);
  $Title = $row->Title;

  // Display the form for the user

  echo "<h2>$action a run for <i>$row->Title</i></h2>";
  echo "<form method=POST action=\"ListGames.php\">\n";
  form_add_sequence ();
  if ($update)
  {
    form_hidden_value('action', PROCESS_EDIT_RUN);
    form_hidden_value('RunId', $RunId);
    form_hidden_value('Update', '1');
  }
  else
  {
    form_hidden_value('action', PROCESS_ADD_RUN);
    form_hidden_value('EventId', $EventId);
    form_hidden_value('Update', '0');
  }

  echo "<table border=\"0\">\n";

  form_day ('Day');
  form_start_hour ('Start Hour', 'StartHour');
  form_text (32, 'Title Suffix', 'TitleSuffix');
  form_text (32, 'Schedule Note', 'ScheduleNote');
  
  echo "<tr><td colspan = 2><hr></td></tr>";

  
  form_con_rooms('Rooms(s)', 'Rooms');

  if ( $row->GameType == "Class" || $row->GameType == "Panel")
  {
    $sql = "SELECT BidId FROM Bids WHERE EventId=$EventId";
    $result = mysql_query ($sql);
    if (! $result)
      return display_error ("Cannot query title for EventId $EventId: " . mysql_error ());

    if (0 == mysql_num_rows ($result))
      return display_error ("Cannot find EventId $EventId in the database!");

    if (1 != mysql_num_rows ($result))
      return display_error ("EventId $EventId matched more than 1 row!");

    $bidrow = mysql_fetch_object ($result);

    //echo "Panel: ".$row->GameType." BidId: ".$bidrow->BidId;

    display_schedule_pref($bidrow->BidId, $row->GameType == "Panel" );
    
    mysql_free_result ($bidrow);

  }
  
  global $OPS_TYPES;
  if ( $row->GameType == $OPS_TYPES[1] || $row->GameType == $OPS_TYPES[2] 
       || $row->GameType == $OPS_TYPES[3])
  {
    echo "<tr><td colspan = 2><hr></td></tr>";

    echo "<tr><td>Select Show:</td><td>";

    display_show_list($showId);

    echo "</td></tr>";
  }
  if ($update)
    form_submit2 ('Update Run', 'Delete Run', 'DeleteRun');
  else
    form_submit ('Add Run');

  echo "</table>\n";
  echo "</form>\n";

  // If this is an update, warn the user about deletions if there are any
  // players signed up

  if ($update)
  {
    $sql = 'SELECT COUNT(*) AS Count';
    $sql .= ' FROM Signup';
    $sql .= " WHERE State<>'Withdrawn' AND RunId=$RunId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for count of signed up players failed',
				  $sql);

    $row = mysql_fetch_object ($result);
    if (! $row)
      return display_error ('Failed to fetch count of signed up players');

    if (0 != $row->Count)
    {
      echo "<p><b>Warning:</b> There are $row->Count people signed up for\n";
      echo "this run of <i>$Title</i>.  If you delete this run, you should\n";
      echo "send them mail before deleting the run.  The site will NOT\n";
      echo "automatically send them cancellation notices.  You can get a\n";
      echo "list of EMail addresses for the signed up participants\n";
      printf ("<A HREF=\"Schedule.php?action=%d&RunId=%d&EventId=%d&" .
	      "FirstTime=1 TARGET=_blank\">here</a><p>\n",
	      SCHEDULE_SHOW_SIGNUPS,
	      $RunId,
	      $EventId);
    }
  }
  display_valid_start_times ();
}

/*
 * process_add_run
 *
 * Process the add_run form
 */

function process_add_run ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (true);

  // Is this an update or an add?

  $Update = $_POST['Update'];

  if (! $Update)
  {
    $verb = 'INSERT';
    $action_failed = 'Insert into';

    $EventId = $_POST['EventId'];
  }
  else
  {
    $verb = 'UPDATE';
    $action_failed = 'Update of';

    $RunId = $_POST['RunId'];

    $sql = "SELECT EventId FROM Runs WHERE RunId=$RunId";
    $result = mysql_query ($sql);
    if (! $result)
      return display_error ("Cannot query run data for RunId $RunId: " . mysql_error ());

    if (0 == mysql_num_rows ($result))
      return display_error ("Cannot find RunId $RunId in the database!");

    if (1 != mysql_num_rows ($result))
      return display_error ("RunId $RunId matched more than 1 row!");

    $row = mysql_fetch_object ($result);

    $EventId = $row->EventId;
  }

  // Start by fetching the title

  $sql = "SELECT Title, GameType FROM Events WHERE EventId=$EventId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_error ("Cannot query title for EventId $EventId: " . mysql_error ());

  if (0 == mysql_num_rows ($result))
    return display_error ("Cannot find EventId $EventId in the database!");

  if (1 != mysql_num_rows ($result))
    return display_error ("EventId $EventId matched more than 1 row!");

  $row = mysql_fetch_object ($result);
  $Title = $row->Title;

  // If DeleteRun is one of the Post parameters, this must be an update request
  // and the user has asked us to delete a run.

  // Note that we should check if any users have signed up for this game, and
  // ask the user if he's really sure.  If he is, then delete the entries from
  // the Signups list and possibly send the users a note.

  if (isset ($_POST['DeleteRun']))
  {
    // Remove any players from the confirmed or wait lists for this game

    $sql = 'UPDATE Signup SET PrevState=State,';
    $sql .= ' State="Withdrawn"';
    $sql .= " WHERE RunId=$RunId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Attempt to withdraw players from run failed',
				  $sql);

    $withdrawn_players = mysql_affected_rows ();

    // Delete the run

    $sql = "DELETE FROM Runs WHERE RunId=$RunId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to delete run $RunId", $sql);

    echo "Deleted run for <i>$Title</I>.\n";
    if ($withdrawn_players > 0)
      echo "  $withdrawn_players players were withdrawn from the run.\n";
    echo "<p>\n";

    return true;
  }

  global $OPS_TYPES;
  if ( $row->GameType == $OPS_TYPES[1] || $row->GameType == $OPS_TYPES[2] 
       || $row->GameType == $OPS_TYPES[3] )
    if ( !validate_int ('ShowId', 1, 2000))
    {
      $form_ok &= false;
      display_error("...which means... You must pick a show for this type of Ops run<br>\n");
    }


  if (! validate_day_time ('StartHour', 'Day'))
    return false;

  $Rooms = '';
  if (array_key_exists('Rooms', $_POST))
    $Rooms = implode(',', $_POST['Rooms']);

  $sql = "$verb Runs SET EventId=$EventId";
  $sql .= build_sql_string ('Day');
  $sql .= build_sql_string ('StartHour');
  $sql .= build_sql_string ('TitleSuffix');
  $sql .= build_sql_string ('ScheduleNote');
  $sql .= build_sql_string ('Rooms', $Rooms);
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);

  if ( $row->GameType == $OPS_TYPES[1] || $row->GameType == $OPS_TYPES[2] 
        || $row->GameType == $OPS_TYPES[3])
    $sql .= build_sql_string ('ShowId');

  if ( $row->GameType == $OPS_TYPES[2] || $row->GameType == $OPS_TYPES[3])
    $sql .= build_sql_string ('Viewable','protect');

  if ($Update)
    $sql .= "WHERE RunId=$RunId";

  //  echo "Command: $sql\n";

  $result = mysql_query ($sql);
  if (! $result)
     return display_error ($action_failed . ' Runs table failed: ' . mysql_error ());


  if ($Update)
    echo "Updated run $RunId for <i>$Title</I>\n<p>\n";
  else
  {
    $RunId = mysql_insert_id ();
    echo "Inserted run $RunId for <i>$Title</I>\n<p>\n";
  }

  return $RunId;
}


/*
 * process_add_ops
 *
 * Process the add_run form
 */

function process_add_ops ()
{
  // Check for a sequence error
  $set_runs = TRUE;

  if (out_of_sequence ())
    return display_sequence_error (true);

  if (!isset ($_POST['DeleteRun']))
  {
    // Check values
    $form_ok = TRUE;
    //  echo "Sessions: ".$_POST['Sessions']."<br>\n";
    //  echo "isset: ".isset($_POST['Sessions'])."<br>\n";

    $form_ok &= validate_string ('Title');
    $form_ok &= validate_players ('Neutral');
    $form_ok &= validate_int ('Hours', 1, 12, 'Hours');
    $form_ok &= validate_string ('Description');
    $form_ok &= validate_string ('ShortBlurb', 'Short blurb');
    if (isset($_POST['Sessions']) && $_POST['Sessions'] != "")
    {
      $form_ok &= validate_int ('Sessions', 1, 24, 'Sessions');
      $form_ok &= validate_day_time_by_val (trim ($_POST['StartHour']), 
    									trim ($_POST['Day']));

      //calculate and check end hour
      $EndHour = $_POST['StartHour'] + ($_POST['Hours']*$_POST['Sessions']) - 1;
      $form_ok &= validate_day_time_by_val ($EndHour, trim ($_POST['Day']));

      if ( $row->GameType == $OPS_TYPES[1] || $row->GameType == $OPS_TYPES[2] 
             || $row->GameType == $OPS_TYPES[3])
        if ( !validate_int ('ShowId', 1, 2000))
        {
          $form_ok &= false;
          display_error("...which means... You must pick a show for this type of Ops run<br>\n");
		}
    }
    else 
      $set_runs = FALSE;
      
    if (! $form_ok)
      return FALSE;
  }

  // Update events table

  $AddTrack = $_POST['AddTrack'];
  $sql = "";
  $sqlend = ";";
  $EventId = "";

  if ( $AddTrack=='0')
  {
    // Insert Event and get Event Id
    $sql = 'INSERT Events SET ';

  }
  else
  {
    $EventId = $_POST['EventId'];
    $sql = 'UPDATE Events SET ';
    $sqlend = ' WHERE EventId='.$EventId.';';
  }
  
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID], false);
  $sql .= build_sql_string ('Title');
  $sql .= build_sql_string ('Author');
  $sql .= build_sql_string ('GameEMail');
  $sql .= build_sql_string ('GameType');
  $sql .= build_sql_string ('Description');
  $sql .= build_sql_string ('ShortBlurb');
  $sql .= build_sql_string ('Hours');
  $sql .= build_sql_string ('MinPlayersNeutral');
  $sql .= build_sql_string ('MaxPlayersNeutral');
  $sql .= build_sql_string ('PrefPlayersNeutral');
  $sql .= build_sql_string ('IsOps','Y');
  $sql .= $sqlend;
  
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Insert into Bids failed");

  if ( $AddTrack=='0')
    $EventId = mysql_insert_id();
    
    
  if ( $AddTrack=='0')
    echo "Created event $EventId for <i>".$_POST['Title']."</I>\n<br>\n";
  else
  {
    echo "Update $EventId for <i>".$_POST['Title']."</I>\n<br>\n";
  }

  // if we're actually adding tracks - not delete, not empty on the run info
  if (!isset ($_POST['DeleteRun']) && $set_runs )
  {
    // for each n of n sessions

    $Rooms = '';
    if (array_key_exists('Rooms', $_POST))
      $Rooms = implode(',', $_POST['Rooms']);
    $RunHour = $_POST['StartHour'];
  
    for ( $n=0; $n < $_POST['Sessions']; $n++)
    {
      $sql = "INSERT Runs SET EventId=$EventId";
      $sql .= build_sql_string ('Day');
      $sql .= build_sql_string ('StartHour', $RunHour);
      //$sql .= build_sql_string ('TitleSuffix');
      $sql .= build_sql_string ('ScheduleNote');
      if ( $row->GameType == $OPS_TYPES[1] || $row->GameType == $OPS_TYPES[2] 
           || $row->GameType == $OPS_TYPES[3])
          $sql .= build_sql_string ('ShowId');

      if ( $row->GameType == $OPS_TYPES[2] || $row->GameType == $OPS_TYPES[3])
          $sql .= build_sql_string ('Viewable','protect');

      $sql .= build_sql_string ('Rooms', $Rooms);
      $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);
      echo "Created run of ".$_POST['Title']." at ".start_hour_to_am_pm($RunHour)." on ".$_POST['Day'].".<br>";
      $RunHour = $RunHour + $_POST['Hours'];




	  
      //  echo "Command: $sql\n";
      $result = mysql_query ($sql);
      if (! $result)
         return display_error ($action_failed . ' Runs table failed: ' . mysql_error ());
    }

    echo "TOTAL: Created $n runs of ".$_POST['Title']." on ".$_POST['Day'].".<br>";

  }
  // only do a delete if we're not just updating runs.
  else if ($set_runs)
  {
    $sql = "DELETE FROM Runs WHERE EventId=$EventId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to delete run $RunId", $sql);

    $sql = "DELETE FROM Events WHERE EventId=$EventId";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to delete run $RunId", $sql);

    echo "Deleted all runs for <i>".$_POST['Title']."</I>.\n";

  }

  
  
  return TRUE;
}


function add_ops($type=0)
{

  if (! user_has_priv (PRIV_SCHEDULING) )
  {
    display_access_error ();
    html_end ();
  }
  
  list_games ($type, TRUE);
  
  // Display what we're proposing to do for the user

   if (file_exists(TEXT_DIR.'/AddRunsForOps.html'))
	include(TEXT_DIR.'/AddRunsForOps.html');	
  

  // Check that we've got one (and only one) event marked as Ops!
  $update = FALSE;
  if ( $_REQUEST['EventId'] > 0 )
  {
    $update = TRUE;
    $EventId = $_REQUEST['EventId'];
    $sql = "SELECT * FROM Events WHERE EventId=$EventId";

    $result = mysql_query ($sql);
    if (! $result)
		return display_mysql_error ("Query failed for EventId $EventId");

    if (0 == mysql_num_rows ($result))
		return display_error ("Failed to find EventId $EventId");

    if (1 != mysql_num_rows ($result))
		return display_error ("Found multiple entries for EventId $EventId");

    $row = mysql_fetch_array ($result, MYSQL_ASSOC);

    foreach ($row as $key => $value)
    {/*
        if (1 == get_magic_quotes_gpc())
          $_POST[$key] = mysql_real_escape_string ($value);
        else */
          $_POST[$key] = $value;
    }

  }

  // Display the form for the user
  echo "<form method=POST action=\"ListGames.php?action=".PROCESS_ADD_OPS."\">\n";
  form_add_sequence ();
  form_hidden_value('action', PROCESS_ADD_OPS);
  
  if ($update)
  {
    echo "<a href=\"ListGames.php?action=".$_REQUEST['action']."\"><b>New Ops Event</b></a>";
    form_hidden_value('EventId', $EventId);
    form_hidden_value('AddTrack', '1');
  }
  else
  {
    form_hidden_value('AddTrack', '0');
  }
  echo "<table border=\"0\">\n";

  form_section ('Run Information');

  form_day ('Day');
  form_start_hour ('Start Hour', 'StartHour');
  form_text (2, 'Number of Sessions', 'Sessions');
  //form_text (32, 'Title Suffix', 'TitleSuffix');
  form_text (32, 'Location Note', 'ScheduleNote');
  
  
  echo "<tr><td colspan = 2><hr></td></tr>";
  
  form_con_rooms('Rooms(s)', 'Rooms');
  
  echo "<tr><td colspan = 2><hr></td></tr>";

  echo "<tr><td>Select Show:</td><td>";

  $showId = 0;
  display_show_list($showId);

  echo "</td></tr>";


  form_section ('Event Information');

  form_text (64, 'Title', 'Title', 128, true);
  form_text (32, 'Contact Person (Staff)', 'Author');
  form_text (32, 'Contact Email (Staff)', 'GameEMail');

  // editing the presenters of the event is limited to scheduling people 
  if ($update)
  {
    global $GM_TYPES;
    echo "<tr><td>";
    printf ('<a href="Schedule.php?action=%d&EventId=%d">Edit %s</a>',
	          DISPLAY_GM_LIST, $EventId, $GM_TYPES["Ops"]);
	echo "</td><td>Coordinators are at the con running this ops track";
	echo "</td></tr>";
  }

  echo "<tr><td align=right>";
  global $OPS_TYPES;
  form_single_select('Ops Type:</td><td>', 'GameType', $OPS_TYPES, $_POST['GameType']);

  form_text (2, 'Length', 'Hours', 0, TRUE);
  form_players_entry ('Neutral',false);
  echo "</td></tr>";


   $text = "A <b>short blurb</b> (50 words or less) to be used for\n";
   $text .= "volunteer listings.\n";
   $text .= "<br>";
   if (file_exists('HtmlPrimer.html'))
        $text .= "<A HREF=HtmlPrimer.html TARGET=_blank>HTML Primer</A>.\n";
   else
        $text .= "<A HREF=".TEXT_DIR."/HtmlPrimer.html TARGET=_blank>HTML Primer</A>.\n";

   form_textarea ($text, 'ShortBlurb', 4, TRUE, TRUE);
    
    
   $text = "<b>Description</b>\n For use on the " . CON_NAME . " website, in soliciting <br>";
   $text .= "help for ".CON_SHORT_NAME.".\n";
   $text .= "The description should be 1-2 of paragraphs.<br><br>";
   $text .= "You may use HTML tags for formatting.  A quick primer on ";
   $text .= "a couple of useful HTML <br>tags is available ";
   if (file_exists('HtmlPrimer.html'))
        $text .= "<A HREF=HtmlPrimer.html TARGET=_blank>here</A>.\n";
   else
        $text .= "<A HREF=".TEXT_DIR."/HtmlPrimer.html TARGET=_blank>here</A>.\n";

   form_textarea ($text, 'Description', 15, TRUE, TRUE);
  

  if ($update)
    form_submit2 ('Update Run', 'Delete All Tracks', 'DeleteRun');
  else
    form_submit ('Add Run');

  echo "</table>\n";
  echo "</form>\n";


  display_valid_start_times ();
}


?>