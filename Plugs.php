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
  $action = PLUGS_SHOW_PAGE;

// Do the work

switch ($action)
{
  case PLUGS_SHOW_PAGE:
    show_plugs();
    break;

  case PLUGS_MANAGE_PLUGS:
    manage_plugs();
    break;

  case PLUGS_SHOW_FORM:
    show_form();
    break;

  case PLUGS_PROCESS_FORM:
    if (! process_form())
      show_form();
    else
      show_plugs();
    break;

  case PLUGS_CONFIRM_DELETE:
    confirm_plug_deletion();
    break;

  case PLUGS_DELETE:
    delete_plug();
    manage_plugs();
    break;

  default:
    display_error ("Unknown action code: $action");
}

// Add the postamble

html_end ();

/*
 * show_plugs
 *
 * Display the page of Shameless Plugs
 */

function show_plugs()
{
  if (is_logged_in())
    $email = sprintf ('<a href="mailto:%s?subject=%s">%s</a>',
		      EMAIL_WEBMASTER,
		      'I want to put up a Shameless Plug!',
		      EMAIL_WEBMASTER);
  else
    $email = obfuscate_email_address(EMAIL_WEBMASTER);

  echo "<h2>Shameless Plugs</h2>\n";

  if (user_has_priv(PRIV_STAFF))
    printf ("<p><a href=\"Plugs.php?action=%d&PlugId=0\">%s</a></p>\n",
	      PLUGS_SHOW_FORM,
	      'Add new plug');

  $time_info = localtime (time(), true);
  $today = sprintf ('%d-%02d-%02d',
		    $time_info['tm_year'] + 1900,
		    $time_info['tm_mon'],
		    $time_info['tm_mday']);

  $sql = 'SELECT * FROM Plugs';
  $sql .= ' WHERE Plugs.Visible="Y"';
  $sql .= "   AND Plugs.EndDate+0 >= CURDATE()+0";
  $sql .= ' ORDER BY RAND()';

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Query for plugs failed', $sql);

  $plug_shown = false;

  while ($row = mysql_fetch_object($result))
  {
    if ('' == $row->Url)
      echo "<h3 id=\"$row->PlugId\">$row->Name</h3>\n";
    else
    {
      $url = $row->Url;
      $parts = parse_url($url);
      if (! array_key_exists('scheme', $parts))
	$url = 'http://' . $row->Url;

      echo "<h3 id=\"$row->PlugId\"><a href=\"$url\" target=\"_blank\">";
      echo "$row->Name</a></h3>\n";
    }
    echo "$row->Text\n";
    $plug_shown = true;

    if (user_has_priv(PRIV_STAFF) || is_user($row->UserId))
      printf ("<p><a href=\"Plugs.php?action=%d&PlugId=%d\">%s</a></p>\n",
	      PLUGS_SHOW_FORM,
	      $row->PlugId,
	      'Edit this plug');
  }

  if ($plug_shown)
    echo "<hr width=\"50%\" align=\"center\">";

  echo "<p>\n";
  echo "Want to promote your event to Intercon New England users?\n";
  echo "Signup to for a Shameless Plug by contacting the Webmaster:\n";
  echo "$email.  Be sure to tell us:</p>\n";
  echo "<ul>\n";
  echo "<li>What event you want to plug</li>\n";
  echo "<li>When your event will run</li>\n";
  echo "<li>What your organization is</li>\n";
  echo "<li>Who will be maintaining your plug - they must have an\n";
  echo "account on this website.</li>\n";
  echo "</ul>\n<p>\nNew England Interactive Literature\n";
  echo "reserves the right to remove or edit any Shameless Plug\n";
  echo "posted to this website.</p>\n";
}

function get_username($user_id)
{
  $sql = "SELECT FirstName, LastName FROM Users WHERE UserId=$user_id";
  $result = mysql_query($sql);
  if (! $result)
    return 'Unknown User';

  if (1 != mysql_num_rows($result))
    return 'Unknown User';

  $row = mysql_fetch_object($result);
  return "$row->LastName, $row->FirstName";
}

/*
 * manage_plugs
 *
 * Display list of active plugs and allow the user to modify them
 */

function manage_plugs()
{
  if (! user_has_priv (PRIV_STAFF))
    return display_access_error();

  echo "<h2>Manage Shameless Plugs</h2>\n";

  $sql = 'SELECT Plugs.PlugId, Plugs.Name, Plugs.Visible,';
  $sql .= ' DATE_FORMAT(Plugs.EndDate, "%d-%b-%Y") AS EndDate,';
  $sql .= ' DATE_FORMAT(Plugs.LastUpdated,  "%d-%b-%Y %H:%i") AS LastUpdated,';
  $sql .= ' Plugs.UpdatedById, LENGTH(Plugs.Text) AS TextLength,';
  $sql .= ' Plugs.EndDate+0 < CURDATE()+0 AS Expired,';
  $sql .= ' Users.FirstName, Users.LastName';
  $sql .= ' FROM Plugs, Users';
  $sql .= ' WHERE Users.UserId=Plugs.UserId';
  $sql .= ' ORDER BY Plugs.Name';

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Query for plugs failed', $sql);

  if (mysql_num_rows($result) > 0)
  {
    echo "<table border=\"1\">\n";
    echo "  <tr>\n";
    echo "    <th align=\"left\">Plug Name</th>\n";
    echo "    <th>Length</th>\n";
    echo "    <th align=\"left\">Owner</th>\n";
    echo "    <th>End Date</th>\n";
    echo "    <th>Visible</th>\n";
    echo "    <th>Last<br>Updated</td>\n";
    echo "    <th>Updated By</th>\n";
    echo "  </tr>\n";
    while ($row = mysql_fetch_object($result))
    {
      $updater = get_username($row->UpdatedById);
      echo "  <tr>\n";
      printf ("    <td><a href=\"Plugs.php?action=%d&PlugId=%d\">%s</a>&nbsp;&nbsp;</td>\n",
	      PLUGS_SHOW_FORM,
	      $row->PlugId,
	      $row->Name);
      echo "    <td>$row->TextLength</td>\n";
      echo "    <td>$row->LastName, $row->FirstName&nbsp;&nbsp;</td>\n";
      echo "    <td>&nbsp;$row->EndDate&nbsp;</td>\n";
      if (1 == $row->Expired)
	$visible = 'Expired';
      else
	$visible = $row->Visible;
      printf ('    <td align="center">' .
	      '<a href="Plugs.php?action=%d&PlugId=%d">%s</td>' . "\n",
	      PLUGS_CONFIRM_DELETE,
	      $row->PlugId,
	      $visible);
      echo "    <td align=\"center\">$row->LastUpdated</td>\n";
      echo "    <td>$updater</td>\n";
      echo "  </tr>\n";
    }
    echo "</table>\n";
  }

  printf ('<a href="Plugs.php?action=%d">', PLUGS_SHOW_FORM);
  echo "Add new plug</a>\n";

}

function form_date($display, $key)
{
  $month_key = $key . 'Month';
  $day_key = $key . 'Day';
  $year_key = $key . 'Year';

  $sel_jan = '';
  $sel_feb = '';
  $sel_mar = '';
  $sel_apr = '';
  $sel_may = '';
  $sel_jun = '';
  $sel_jul = '';
  $sel_aug = '';
  $sel_sep = '';
  $sel_oct = '';
  $sel_nov = '';
  $sel_dec = '';

  if (array_key_exists($month_key, $_POST))
  {
    switch ($_POST[$month_key])
    {
      case  1: $sel_jan = 'selected'; break;
      case  2: $sel_feb = 'selected'; break;
      case  3: $sel_mar = 'selected'; break;
      case  4: $sel_apr = 'selected'; break;
      case  5: $sel_may = 'selected'; break;
      case  6: $sel_jun = 'selected'; break;
      case  7: $sel_jul = 'selected'; break;
      case  8: $sel_aug = 'selected'; break;
      case  9: $sel_sep = 'selected'; break;
      case 10: $sel_oct = 'selected'; break;
      case 11: $sel_nov = 'selected'; break;
      case 12: $sel_dec = 'selected'; break;
    }
  }

  $selected_day = 0;
  if (array_key_exists($day_key, $_POST))
    $selected_day = intval (trim ($_POST[$day_key]));

  $selected_year = 0;
  if (array_key_exists($year_key, $_POST))
    $selected_year = intval (trim ($_POST[$year_key]));

  echo "  <tr>\n";
  echo "    <td align=\"right\">$display:</td>\n";
  echo "    <td>\n";
  echo "      <select name=\"$month_key\" size=\"1\">\n";
  echo "        <option value=\"1\" $sel_jan>January</option>\n";
  echo "        <option value=\"2\" $sel_feb>Febuary</option>\n";
  echo "        <option value=\"3\" $sel_mar>March</option>\n";
  echo "        <option value=\"4\" $sel_apr>April</option>\n";
  echo "        <option value=\"5\" $sel_may>May</option>\n";
  echo "        <option value=\"6\" $sel_jun>June</option>\n";
  echo "        <option value=\"7\" $sel_jul>July</option>\n";
  echo "        <option value=\"8\" $sel_aug>August</option>\n";
  echo "        <option value=\"9\" $sel_sep>September</option>\n";
  echo "        <option value=\"10\" $sel_oct>October</option>\n";
  echo "        <option value=\"11\" $sel_nov>November</option>\n";
  echo "        <option value=\"12\" $sel_dec>December</option>\n";
  echo "      </select>\n";

  echo "      <select name=\"$day_key\" size=\"1\">\n";
  for ($i = 1; $i <= 31; $i++)
  {
    echo "        <option value=\"$i\"";
    if ($i == $selected_day)
      echo ' selected';
    echo ">$i</option>\n";
  }
  echo "      </select>\n";


  $time_info = localtime (time(), true);
  $min_year = $time_info["tm_year"] + 1900;
  $max_year = $min_year + 1;

  echo "      <select name=\"$year_key\" size=\"1\">\n";
  for ($i = $min_year; $i <= $max_year; $i++)
  {
    echo "        <option value=\"$i\"";
    if ($i == $selected_year)
      echo ' selected';
    echo ">$i</option>\n";
  }
  echo "      </select>\n";

  echo "    </td>\n";
  echo "  <tr>\n";
}

function form_owner($display, $key)
{
  $selected_owner = 0;
  if (array_key_exists ($key, $_POST))
    $selected_owner = intval($_POST[$key]);

  $sql = 'SELECT UserId, FirstName, LastName';
  $sql .= ' FROM Users';
  $sql .= ' ORDER BY LastName, FirstName';
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Query for users failed', $sql);

  echo "  <tr>\n";
  echo "    <td align=\"right\">$display:</td>\n";
  echo "    <td>\n";
  echo "      <select name=\"$key\" size=\"1\">\n";
  while ($row = mysql_fetch_object($result))
  {
    // Skip the admin account

    if ('Admin' == $row->LastName)
      continue;

    echo "        <option value=\"$row->UserId\"";
    if ($row->UserId == $selected_owner)
      echo ' selected';
    echo ">$row->LastName, $row->FirstName</option>\n";
  }
  echo "      </select>\n";
  echo "    </td>\n";
  echo "  <tr>\n";
}

/*
 * name_month
 *
 * Names a month
 */

function name_month($m)
{
  switch($m)
  {
    case 1: return 'Jan';
    case 2: return 'Feb';
    case 3: return 'Mar';
    case 4: return 'Apr';
    case 5: return 'May';
    case 6: return 'Jun';
    case 7: return 'Jul';
    case 8: return 'Aug';
    case 9: return 'Sep';
    case 10: return 'Oct';
    case 11: return 'Nov';
    case 12: return 'Dec';
  }

  return 'Unknown';
}

/*
 * date_is_past
 *
 * Returns 1 if the date is in the past, 0 if it's not.
 */

function date_is_past($year, $month, $day)
{
  $a = explode (' ', date ('Y n d'));
  $today_year = $a[0];
  $today_month = $a[1];
  $today_day = $a[2];

  if ($today_year > $year)
    return 1;
  if ($today_year < $year)
    return 0;

  if ($today_month > $month)
    return 1;
  if ($today_month < $month)
    return 0;

  if ($today_day > $day)
    return 1;
  else
    return 0;
}

/*
 * show_form
 *
 * Show the form that allows users to edit a shameless plug
 */

function show_form()
{
  display_header ('Shameless Plug Form');

  $PlugId = 0;
   if (array_key_exists('PlugId', $_REQUEST))
    $PlugId = intval($_REQUEST['PlugId']);

  // If needed load the data from the database

  if ($PlugId == 0)
  {
    // First time in - initialize what me must
    $_POST['PlugId'] = 0;
    $_POST['UserId'] = 0;
  }
  else
  {
    // If we don't already have data, load it from the database

    if (!array_key_exists ('Text', $_REQUEST))
    {
      $sql = "SELECT * FROM Plugs WHERE PlugId=$PlugId";
      $result = mysql_query($sql);
      if (! $result)
	return display_mysql_error ("Query for plug $PlugId failed", $sql);

      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      foreach ($row as $k => $v)
      {
	if ("EndDate" != $k)
	  $_POST[$k] = $v;
	else
	{
	  $a = explode('-', $v);
	  dump_array('a', $a);
	  $_POST['EndDateYear'] = $a[0];
	  $_POST['EndDateMonth'] = $a[1];
	  $_POST['EndDateDay'] = $a[2];
	}
      }

      mysql_free_result($result);
    }
  }

  // Make sure the user is allowed here

  if (($_POST['UserId'] != $_SESSION[SESSION_LOGIN_USER_ID]) &&
      (! user_has_priv (PRIV_STAFF)))
    return display_access_error();

  //	dump_array('POST', $_POST);

  // Build the form

  echo "<form method=\"post\" action=\"Plugs.php\">\n";
  form_add_sequence();
  form_hidden_value ('action', PLUGS_PROCESS_FORM);
  form_hidden_value ('PlugId', $PlugId);
  echo "<table>\n";
  
  form_text (64, 'Name', '', 128);
  form_text (64, 'Url', '', 128);
  form_textarea ('Plug Text - Max 1000 characters', 'Text', 5, false);

  if (user_has_priv (PRIV_STAFF))
  {
    form_owner ('Owner', 'UserId');
    form_date ('End Date', 'EndDate');
    echo "  <tr>\n    <td colspan=\"2\">\n";
    form_checkboxYN('Visible');
    echo " Plug is visible</td>\n  </tr>\n";
  }
  else
  {
    if (array_key_exists('EndDateYear', $_POST))
    {
      echo "  <tr>\n";
      echo "    <td colspan=\"2\">\n";
      if ('N' == $_POST['Visible'])
	echo "    This plug has been hidden.\n";
      else
      {
	$verb = 'will expire';
	if (date_is_past($_POST['EndDateYear'],
			 $_POST['EndDateMonth'],
			 $_POST['EndDateDay']))
	  $verb = 'expired';
	printf ("    This plug $verb %s-%s-%s.\n",
		$_POST['EndDateDay'],
		name_month($_POST['EndDateMonth']),
		$_POST['EndDateYear']);
      }
      echo "    </td>\n";
      echo "  </tr>\n";
    }
  }


  if (0 == $PlugId)
    form_submit ('Add New Plug');
  else
    form_submit ('Update Plug');

  echo "</table>\n";
  echo "</form>\n";
  echo "<p><b>Note:</b> New England Interactive Literature reserves the\n";
  echo "right to remove or edit any Shameless Plug posted to this website.</p>\n";
  
}

function process_form()
{
  dump_array ('POST', $_POST);

  $PlugId = 0;
  if (array_key_exists('PlugId', $_REQUEST))
    $PlugId = intval (trim ($_REQUEST['PlugId']));

  if (0 == $PlugId)
    $sql = 'INSERT Plugs SET ';
  else
    $sql = 'UPDATE Plugs SET ';

  if (array_key_exists('Text', $_REQUEST))
  {
    $text_length = strlen($_REQUEST['Text']);
    if ($text_length > 1000)
      return display_error ('Plug text is limited to 1000 characters.' .
			    "You have $text_length characters.");
  }

  $sql .= build_sql_string('Name', '', false);
  $sql .= build_sql_string('Url');
  $sql .= build_sql_string('Text', '', true, true);
  if (user_has_priv (PRIV_STAFF))
  {
    $end_date = sprintf ('%d-%d-%d',
			 intval($_REQUEST['EndDateYear']),
			 intval($_REQUEST['EndDateMonth']),
			 intval($_REQUEST['EndDateDay']));

    $visible = 'N';
    if (array_key_exists('Visible', $_REQUEST))
      $visible = $_REQUEST['Visible'];

    $sql .= build_sql_string('UserId');
    $sql .= build_sql_string('EndDate', $end_date);
    $sql .= build_sql_string('Visible', $visible);
  }

  $sql .= ', UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];

  if (0 != $PlugId)
    $sql .= " WHERE PlugId=$PlugId";

  //  echo "$sql<br>\n";

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error('Failed to update Plugs', $sql);
  else
    return true;
}

function confirm_plug_deletion()
{
  // Make sure the user is allowed here

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error();

  // Make sure we've got a PlugId and it's valid

  if (! array_key_exists('PlugId', $_REQUEST))
    return display_error('PlugId not specified');

  $PlugId = intval(trim($_REQUEST['PlugId']));
  if (0 == $PlugId)
    return display_error('Invalid PlugId');

  $sql = "SELECT Name FROM Plugs WHERE PlugId=$PlugId";
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error('Query for plug name failed', $sql);

  $row = mysql_fetch_object($result);
  if (! $row)
    return display_error('Invalid PlugId');

  display_header ("Confirm Plug Deletion");
  printf ("Click <a href=\"Plugs.php?action=%d&PlugId=%d\">here</a>\n",
	  PLUGS_DELETE,
	  $PlugId);
  echo " to confirm that the plug for <i>$row->Name</i> should be deleted.\n";
}

function delete_plug()
{
  // Make sure the user is allowed here

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error();

  // Make sure we've got a PlugId and it's valid

  if (! array_key_exists('PlugId', $_REQUEST))
    return display_error('PlugId not specified');

  $PlugId = intval(trim($_REQUEST['PlugId']));
  if (0 == $PlugId)
    return display_error('Invalid PlugId');

  $sql = "DELETE FROM Plugs WHERE PlugId=$PlugId";
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error('Plug deletion failed', $sql);
  else
    return true;
}

?>
