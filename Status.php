<?php
include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to connect to ' . DB_NAME);
  exit ();
}

// Display boilerplate

html_begin ();

// All functions in this file require staff priv

if (! user_has_priv (PRIV_STAFF))
{
  display_access_error ();
  html_end ();
  exit ();
}

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = SHOW_STATUS;

switch ($action)
{
  case SHOW_STATUS:
    show_status ();
    break;

  case UPDATE_STATUS:
    update_status ();
    show_status ();
    break;

  default:
    display_error ("Unknown action code: $action");
}

// Standard postamble

html_end ();

/*
 * form_show_schedule
 *
 * Display the Show Schedule field in the form being built
 */

function form_show_schedule ($display, $key)
{
  $sel_n = '';
  $sel_y = '';
  $sel_priv = '';
  $sel_gms = '';

  switch ($_POST[$key])
  {
    default:
    case 'No':   $sel_n = 'SELECTED';    break;
    case 'Yes':  $sel_y = 'SELECTED';    break;
    case 'GMs':  $sel_gms = 'SELECTED';  break;
    case 'Priv': $sel_priv = 'SELECTED'; break;
  }

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>$display\n";
  echo "      <SELECT NAME=$key SIZE=1>\n";
  echo "        <option value=No $sel_n>No</option>\n";
  echo "        <option value=Priv $sel_priv>Requires Scheduling Priv</option>\n";
  echo "        <option value=GMs $sel_gms>For GMs and ConCom Priv</option>\n";
  echo "        <option value=Yes $sel_y>Yes</option>\n";
  echo "      </SELECT>\n";
  echo "    </TD>\n";
  echo "  </TR>   \n";
}

/*
 * form_signups_allowed
 *
 * Display the Signups Allowed field in the form being built
 */

function form_signups_allowed ($display, $key)
{
  $sel_notyet = '';
  $sel_yes = '';
  $sel_1 = '';
  $sel_2 = '';
  $sel_3 = '';
  $sel_notnow = '';

  switch ($_POST[$key])
  {
    default:
    case 'NotYet': $sel_notyet = 'SELECTED'; break;
    case '1':      $sel_1 = 'SELECTED';      break;
    case '2':      $sel_2 = 'SELECTED';      break;
    case '3':      $sel_3 = 'SELECTED';      break;
    case 'Yes':    $sel_yes = 'SELECTED';    break;
    case 'NotNow': $sel_notnow = 'SELECTED'; break;
  }

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>$display\n";
  echo "      <SELECT NAME=$key SIZE=1>\n";
  echo "        <option value=NotYet $sel_notyet>Con not yet ready to take signups</option>\n";
  echo "        <option value=1 $sel_1>Only 1 game</option>\n";
  echo "        <option value=2 $sel_2>Only 2 games</option>\n";
  echo "        <option value=3 $sel_3>Only 3 games</option>\n";
  echo "        <option value=Yes $sel_yes>Game signups open</option>\n";
  echo "        <option value=NotNow $sel_notnow>Game signups frozen</option>\n";
  echo "      </SELECT>\n";
  echo "    </TD>\n";
  echo "  </TR>   \n";
}

/*
 * form_bids_allowed
 *
 * Display the Bids Allowed field in the form being built
 */

function form_bids_allowed ($display, $key)
{
  $sel_yes = '';
  $sel_no = '';

  switch ($_POST[$key])
  {
    default:
    case 'Yes':    $sel_yes = 'SELECTED';    break;
    case 'No':     $sel_no = 'SELECTED'; break;
  }

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>$display\n";
  echo "      <SELECT NAME=$key SIZE=1>\n";
  echo "        <option value=Yes $sel_yes>Yes</option>\n";
  echo "        <option value=No $sel_no>No</option>\n";
  echo "      </SELECT>\n";
  echo "    </TD>\n";
  echo "  </TR>   \n";
}

function show_price_dates ($price, $start_date, $end_date)
{

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>\n";
  echo "      The price is \$$price";
  if (0 != $start_date)
  {
    $d = date ('D, d-M-Y', $start_date);
    echo " from $d";
  }

  if (0 != $end_date)
  {
    $d = date ('D, d-M-Y', $end_date);
    echo " until $d";
  }
  echo "\n    </TD>\n";
  echo "  </TR>\n";
}

/*
 * show_status
 *
 * Show status for the database
 */

function show_status ()
{
  // Make sure the user is logged in and has at least Staff privileges

  if (! user_has_priv ('Staff'))
    return display_access_error ();

  // Query the database for the con information

  $sql = 'SELECT Con.SignupsAllowed, Con.ShowSchedule, Con.News,';
  $sql .= ' Con.ConComMeetings,';
  $sql .= ' DATE_FORMAT(Con.LastUpdated , "%d-%b-%Y %H:%i") AS TimeStamp,';
  $sql .= ' Con.PreconBidsAllowed, Con.AcceptingBids,';
  $sql .= ' Users.FirstName, Users.LastName';
  $sql .= ' FROM Con, Users';
  $sql .= ' WHERE Users.UserId=Con.UpdatedById';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query the convention information',
				$sql);

  // Sanity check.  There should only be single row
  $usedb = true;
  if (0 == mysql_num_rows ($result))
  {
    $usedb = false;
    display_error ('Failed to find convention information.  Using defaults.');
  }

  if (mysql_num_rows ($result) > 1)
    return display_error ('Found more than one row of convention information?!');

  if ($usedb)
  {
      $row = mysql_fetch_object ($result);

      // Fill the $_POST array from the object

      foreach ($row as $key => $value)
      {
        $_POST[$key] = $value;
      }
  }

  echo "<FORM METHOD=POST ACTION=Status.php>\n";
  form_add_sequence ();
  echo "<INPUT TYPE=HIDDEN NAME=action VALUE=" . UPDATE_STATUS . ">\n";

  echo "<TABLE>\n";

  form_section ('Constant Information', FALSE);
  display_text_info ('Con Name', CON_NAME);
  display_text_info ('Fri', FRI_TEXT);
  display_text_info ('Sat', SAT_TEXT);
  display_text_info ('Sun', SUN_TEXT);
  display_text_info ('Database', DB_NAME);

  form_section ('Prices and Dates');
  $k = 0;
  while (get_con_price ($k++, $price, $start_date, $end_date))
  {
    echo "<!-- $k, $price, $start_date, $end_date -->\n";
    show_price_dates ($price, $start_date, $end_date);
  }

  form_section ('Site Control');
  form_signups_allowed ('Signups Allowed:', 'SignupsAllowed');
  form_bids_allowed ('Accepting Bids:', 'AcceptingBids');
  form_bids_allowed ('Accepting Pre-Con Event Bids:', 'PreconBidsAllowed');
  form_show_schedule ('Show Schedule:', 'ShowSchedule');
  form_textarea ('News', 'News', 20);

  form_textarea ('ConCom Meeting Notices', 'ConComMeetings', 20);

  form_submit ('Update now');

  echo "</TABLE>\n";

  echo "<P>Last updated $row->TimeStamp by $row->FirstName $row->LastName\n";

  echo "</FORM>\n";
}

/*
 * update_status
 *
 * Respond to a request to change the settings in the Con table
 */

function update_status ()
{
  // If we're out of sequence, don't do anything

  if (out_of_sequence ())
    return display_sequence_error (false);

  $sql = 'UPDATE Con SET ';
  $sql .= build_sql_string ('News', '', FALSE);
  $sql .= build_sql_string ('ConComMeetings');
  $sql .= build_sql_string ('SignupsAllowed');
  $sql .= build_sql_string ('AcceptingBids');
  $sql .= build_sql_string ('PreconBidsAllowed');
  $sql .= build_sql_string ('ShowSchedule');
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);

  //  echo "$sql<P>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to update Con table');

  echo "Con settings updated<P>\n";

  // Unregister the session copies so we'll fetch new values from the database

  session_unregister (SESSION_CON_NEWS);
  session_unregister (SESSION_CON_SHOW_SCHEDULE);

  return TRUE;
}

?>
