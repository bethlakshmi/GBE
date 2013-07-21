<?php
define (MAIL_LIST_NONE, 0);
define (MAIL_LIST_ALL, 1);
define (MAIL_LIST_ATTENDEES, 2);
define (MAIL_LIST_INTERESTED, 3);
define (MAIL_LIST_UNPAID, 4);
define (MAIL_LIST_ALUMNI, 5);
define (MAIL_LIST_GMS_BY_GAME, 6);
define (MAIL_LIST_GMS_BY_NAME, 7);
define (MAIL_LIST_SUBMITTORS, 8);
define (MAIL_LIST_VENDORS, 9);

include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to connect to ' . DB_NAME);
  exit ();
}

// Display boilerplate

html_begin ();

// Only privileged users may access these pages

if (! user_has_any_mail_priv ())
{
  display_access_error ();
  html_end ();
  exit ();
}

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = MAIL_SHOW_FORM;

switch ($action)
{
  case MAIL_SHOW_FORM:
    show_form ();
    break;

  case MAIL_SEND:
    if (! send_mail ())
      show_form ();
    break;

  case MAIL_LISTS:
    show_mail_lists ();
    break;

  case MAIL_GM_LISTS:
    show_gm_lists ();
    break;

  case MAIL_BIO_LISTS:
    show_bio_lists ();
    break;

  case MAIL_WAITLISTED:
    show_waitlisted_players ();
    break;

   case MAIL_BID_SUBMITTERS:
     show_bid_submitters ();
     break;

   case MAIL_SHOW_LISTS:
     show_gm_lists ();
     break;

  default:
    display_error ("Unknown action code: $action");
}

// Standard postamble

html_end ();

function show_form ()
{
  echo "<FONT SIZE=\"+2\"><B>Send Mail to ".CON_SHORT_NAME." Users</B></FONT>\n";

  // Get user information

  $sql = 'SELECT FirstName, LastName, EMail FROM Users WHERE UserId=';
  $sql .= $_SESSION[SESSION_LOGIN_USER_ID];
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query user information');

  // Sanity check.  There should only be a single row

  if (0 == mysql_num_rows ($result))
    return display_error ('Failed to find user information');

  if (1 != mysql_num_rows ($result))
    return display_error ('Found more than one row of user information?!');

  $row = mysql_fetch_object ($result);

  $name = trim ("$row->FirstName $row->LastName");
  $email = $row->EMail;

  // Get the mail lists the user may use

  $avail_mail_lists = get_mail_lists ();

  if (0 == count ($avail_mail_lists))
    return display_error ('You are not allowed to access this page');

  echo "<FORM METHOD=POST ACTION=MailTo.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", MAIL_SEND);
  printf ("<INPUT TYPE=HIDDEN Name=From Value=\"%s\">\n",
	  "$name<$email>");

  echo "<TABLE>\n";
  echo "  <TR>\n";
  echo "    <TD ALIGN=RIGHT>From:</TD>\n";
  echo "    <TD>$name&lt;$email&gt;</TD>\n";
  echo "  </TR>\n";

  if (1 == count ($avail_mail_lists))
  {
    $to = $avail_mail_lists[0];
    echo "<INPUT TYPE=HIDDEN NAME=To VALUE=$to>\n";
    echo "  <TR>\n";
    echo "    <TD ALIGN=RIGHT>To:</TD>\n";
    echo "    <TD>$desc</TD>\n";
    echo "  </TR>\n";
  }
  else
  {
    if (array_key_exists ('To', $_POST))
      $to = $_POST['To'];
    else
      $to = $avail_mail_lists[0];

    echo "  <TR>\n";
    echo "    <TD ALIGN=RIGHT>To:</TD>\n";
    echo "    <TD>\n";
    echo "      <SELECT NAME=To SIZE=1>\n";
    foreach ($avail_mail_lists as $k)
    {
      if ($to == $k)
	$selected = 'SELECTED';
      else
	$selected = '';
      printf ("          <option value=%d %s>%s</option>\n",
	      $k,
	      $selected,
	      name_mail_list ($k));
    }
    echo "      </SELECT>\n";
    echo "    </TD>\n";
    echo "  </TR>\n";
  }

  form_text (80, 'Subject');
  form_textarea ('Message', 'Message', 20);

  form_submit ("Send Message");

  echo "</TABLE>\n";
  echo "</FORM>\n";
}

/*
 * send_mail
 *
 * Send a message to the specified mailing list
 */

function send_mail ()
{
  if ((! user_has_priv (PRIV_MAIL_ALL)) &&
      (! user_has_priv (PRIV_MAIL_ATTENDEES)) &&
      (! user_has_priv (PRIV_MAIL_GMS)) &&
      (! user_has_priv (PRIV_MAIL_VENDORS)))
    return display_error ('You are not allowed to access this page');

  $ok = validate_string ('From', "Sender's Address");
  $ok &= validate_string ('To', "a Mailing List");
  $ok &= validate_string ('Subject');

  if (! $ok)
    return false;

  $To = trim ($_POST['To']);
  $From = trim ($_POST['From']);
  $Subject = trim ($_POST['Subject']);
  $Message = trim ($_POST['Message']);

  if (! intercon_mail ($To, $Subject, $Message))
    return display_error ('Attempt to send mail failed');

  return TRUE;
}

/*
 * get_mail_lists
 *
 * Get the array of mailing lists the user has privilege to look at
 */

function get_mail_lists ()
{
  $avail_mail_lists = array ();

  if (user_has_priv (PRIV_MAIL_ATTENDEES))
    array_push ($avail_mail_lists, MAIL_LIST_ATTENDEES);

  if (user_has_priv (PRIV_MAIL_ALL))
    array_push ($avail_mail_lists, MAIL_LIST_ALL);

  if (user_has_priv (PRIV_MAIL_UNPAID))
  {
    array_push ($avail_mail_lists, MAIL_LIST_UNPAID);
    array_push ($avail_mail_lists, MAIL_LIST_INTERESTED);
  }

  if (user_has_priv (PRIV_MAIL_ALUMNI))
    array_push ($avail_mail_lists, MAIL_LIST_ALUMNI);

  if (user_has_priv (PRIV_MAIL_GMS))
  {
    array_push ($avail_mail_lists, MAIL_LIST_GMS_BY_GAME);
    array_push ($avail_mail_lists, MAIL_LIST_GMS_BY_NAME);
  }
  /*
  if (user_has_priv (PRIV_MAIL_VENDORS))
    array_push ($avail_mail_lists, MAIL_LIST_VENDORS);
  */

  return $avail_mail_lists;
}

function get_checkbox_setting ($key, &$settings, &$selections)
{
  if (! array_key_exists ($key, $_REQUEST))
  {
    $settings[$key] = 0;
    $selections[$key] = 0;
    return 0;
  }

  if ('on' == $_REQUEST[$key])
  {
    $settings[$key] = 1;
    $selections[$key] = 'CHECKED';
  }
  else
  {
    $settings[$key] = 0;
    $selections[$key] = '';
  }
}


/*
 * show_gm_lists
 *
 * Show the available GM mail lists that the user has privilege to look at
 */

function show_gm_lists ()
{
  echo "<font size=\"+2\"><b>".CON_SHORT_NAME." Conference Mailing List</b></font>\n";

  if (array_key_exists ('CSV', $_REQUEST))
    $bCSV = intval ($_REQUEST['CSV']);
  else
    $bCSV = 0;

  if (array_key_exists ('ShowNames', $_REQUEST))
    $bNames = intval ($_REQUEST['ShowNames']);
  else
    $bNames = 0;

  if ($bNames)
    $names_checked = 'CHECKED';
  else
    $names_checked = '';

  if (array_key_exists ('ShowGames', $_REQUEST))
    $bGames = intval ($_REQUEST['ShowGames']);
  else
    $bGames = 0;

  if ($bGames)
    $games_checked = 'CHECKED';
  else
    $games_checked = '';

  if (array_key_exists ('ShowAll', $_REQUEST))
    $bShowAll = intval ($_REQUEST['ShowAll']);
  else
    $bShowAll = 0;

  if ($bShowAll)
    $all_gms_checked = 'CHECKED';
  else
    $all_gms_checked = '';

  $comma_checked = 'CHECKED';
  $semicolon_checked = '';
  if (array_key_exists ('separator', $_REQUEST))
  {
    if ('semicolon' == $_REQUEST['separator'])
    {
      $comma_checked = '';
      $semicolon_checked = 'CHECKED';
    }
  }

  $include_nl_checked='';
  if (array_key_exists ('IncludeNL', $_REQUEST))
  {
    if ('on' == $_REQUEST['IncludeNL'])
    {
      $include_nl_checked = 'CHECKED';
    }
  }

  echo "<FORM METHOD=POST ACTION=MailTo.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", MAIL_GM_LISTS);

  echo "<INPUT TYPE=CHECKBOX NAME=ShowGames $games_checked VALUE=1> <B>Show Event Titles</B><BR>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=ShowNames $names_checked VALUE=1> <B>Show Names</B><BR>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=ShowAll $all_gms_checked VALUE=1> <B>Show All ";
  echo "Conference Presenters</B> - Ignore setting for \"Receive Con EMail\" flag<BR>\n";

  echo "<TABLE>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Display As</TD>\n";
  echo "    <TD><B>\n";
  if ($bCSV)
  {
    $csv_checked = 'CHECKED';
    $html_checked = '';
  }
  else
  {
    $csv_checked = '';
    $html_checked = 'CHECKED';
  }
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=0 $html_checked>HTML Table<BR>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=1 $csv_checked>Comma Separated Values<BR>\n";
  echo "    </B></TD>\n";
  echo "  </TR>\n";
  echo "  <tr valign=top>\n";
  echo "    <td>CSV Options</td>\n";
  echo "    <td nowrap><b>\n";
  echo "      <input type=checkbox name=IncludeNL $include_nl_checked>&nbsp;Newline<br>\n";
  echo "      <input type=radio name=separator value='comma' $comma_checked>Comma separated<br>\n";
  echo "      <input type=radio name=separator value='semicolon' $semicolon_checked>Semicolon separated<br>\n";
  echo "    </b></td>\n";
  echo "  </tr>\n";
  echo "</TABLE>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=\"&nbsp;Display Mail List&nbsp;\">\n";
  echo "</FORM>\n";

  return show_gms_by_name ($bCSV, $bGames, $bNames,
			   'CHECKED' == $include_nl_checked,
			   'CHECKED' == $comma_checked,
			   'CHECKED' == $all_gms_checked);
}


/*
 * show_bid_submitters
 *
 * Show the list of bid submitters
 */

function show_bid_submitters ()
{
  echo "<FONT SIZE=\"+2\"><B>".CON_SHORT_NAME." Bid Submitters Mailing List</B></FONT>\n";

  if (array_key_exists ('CSV', $_REQUEST))
    $bCSV = intval ($_REQUEST['CSV']);
  else
    $bCSV = 0;

  if (array_key_exists ('ShowNames', $_REQUEST))
    $bNames = intval ($_REQUEST['ShowNames']);
  else
    $bNames = 0;

  if ($bNames)
    $names_checked = 'CHECKED';
  else
    $names_checked = '';

  if (array_key_exists ('ShowGames', $_REQUEST))
    $bGames = intval ($_REQUEST['ShowGames']);
  else
    $bGames = 0;

  if ($bGames)
    $games_checked = 'CHECKED';
  else
    $games_checked = '';

  if (array_key_exists ('ShowStatus', $_REQUEST))
    $bStatus = intval ($_REQUEST['ShowStatus']);
  else
    $bStatus = 0;

  if ($bStatus)
    $status_checked = 'CHECKED';
  else
    $status_checked = '';

  echo "<FORM METHOD=POST ACTION=MailTo.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", MAIL_BID_SUBMITTERS);

  echo "<TABLE>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>Display As</TD>\n";
  echo "    <TD><B>\n";
  if ($bCSV)
  {
    $csv_checked = 'CHECKED';
    $html_checked = '';
  }
  else
  {
    $csv_checked = '';
    $html_checked = 'CHECKED';
  }
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=0 $html_checked>HTML Table<BR>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=1 $csv_checked>Comma Separated Values<BR>\n";
  echo "    </B></TD>\n";
  echo "  </TR>\n";
  echo "</TABLE>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=ShowGames $games_checked VALUE=1> <B>Show Bids</B><BR>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=ShowNames $names_checked VALUE=1> <B>Show Names</B><BR>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=ShowStatus $status_checked VALUE=1> <B>Show Bid Status</B><BR>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=\"&nbsp;Display Mail List&nbsp;\">\n";
  echo "</FORM>\n";

  $sql = 'SELECT Users.DisplayName, Users.EMail,';
  $sql .= ' Bids.Title, Bids.Status, Bids.GameType';
  $sql .= ' FROM Users, Bids';
  $sql .= ' WHERE Users.UserId=Bids.UserId';
  $sql .= ' ORDER BY Users.DisplayName, Bids.Title';

  // Fetch the information from the database

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for bidders failed');

  if (0 == mysql_num_rows ($result))
    return display_error ('No matching users found');

  // Are we displaying as a CSV (suitable for importing to Excel) or a table?

  if ($bCSV)
  {
    $last_name = '';
    echo "<DIV NOWRAP>";
    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->DisplayName"));
      if ($last_name == $name)
      {
	if ($bGames)
	  echo "\"<b>$row->GameType</b> - $row->Title\",";
	if ($bStatus)
	  echo "\"$row->Status\"";
      }
      else
      {
	echo "\n<BR>";
	if ($bNames)
	  echo "\"$name\",";
	echo "$row->EMail,";
	if ($bGames)
	  echo "\"<b>$row->GameType</b> - $row->Title\",";
	if ($bStatus)
	  echo "\"$row->Status\"";
      }
      $last_name = $name;
    }
    echo "\n<BR>\n</DIV>\n";
  }
  else
  {
    // Display what we've got in a table

    echo "<TABLE>\n";
    echo "  <TR VALIGN=TOP>\n";

    $last_email = '';

    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->DisplayName"));

      if ($last_email == $row->EMail)
      {
	if ($bGames)
	  echo ",<BR><b>$row->GameType</b> - $row->Title";
	if ($bStatus)
	  echo " ($row->Status)";
      }
      else
      {
	if ($last_email != '')
	{
	  if ($bGames)
	    echo "\n    </TD>\n";
	}

	echo "  </TR>\n";
	echo "  <TR VALIGN=TOP>\n";
	if ($bNames)
	  echo "    <TD>$name&nbsp;&nbsp;&nbsp;</TD>\n";

	echo "    <TD>$row->EMail&nbsp;&nbsp;&nbsp;</TD>\n";

	if ($bGames)
	{
	  echo "    <TD>\n";
	  echo "      <b>$row->GameType</b> - $row->Title";
	  if ($bStatus)
	    echo " ($row->Status)";
	}
      }

      $last_email = $row->EMail;
    }

    if ($bGames)
      echo "\n    </TD>\n";
    echo "  </TR>\n";
    echo "</TABLE>\n";
  }

  return true;
}

/*
 * show_mail_lists
 *
 * Show the available mail lists that the user has privilege to look at
 */

function show_mail_lists ()
{
  echo "<FONT SIZE=\"+2\"><B>".CON_SHORT_NAME." Users Mailing Lists</B></FONT>\n";

  //  dump_array ('REQUEST', $_REQUEST);

  $settings = array ();
  $selections = array ();

  if (array_key_exists ('CSV', $_REQUEST))
    $bCSV = intval ($_REQUEST['CSV']);
  else
    $bCSV = 0;

  $include_name = 0;
  $include_name_checked = '';

  if (array_key_exists ('IncludeName', $_REQUEST))
  {
    if ('on' == $_REQUEST['IncludeName'])
    {
      $include_name = 1;
      $include_name_checked = 'CHECKED';
    }
  }

  $comma_checked = 'CHECKED';
  $semicolon_checked = '';
  if (array_key_exists ('separator', $_REQUEST))
  {
    if ('semicolon' == $_REQUEST['separator'])
    {
      $comma_checked = '';
      $semicolon_checked = 'CHECKED';
    }
  }

  $include_date = 0;
  $include_date_checked = '';

  if (array_key_exists ('IncludeDate', $_REQUEST))
  {
    if ('on' == $_REQUEST['IncludeDate'])
    {
      $include_date = 1;
      $include_date_checked = 'CHECKED';
    }
  }

  $include_nl_checked='';
  if (array_key_exists ('IncludeNL', $_REQUEST))
  {
    if ('on' == $_REQUEST['IncludeNL'])
    {
      $include_nl_checked = 'CHECKED';
    }
  }

  get_checkbox_setting ('Paid',      $settings, $selections);
  get_checkbox_setting ('Unpaid',    $settings, $selections);
  get_checkbox_setting ('Comp',      $settings, $selections);
  get_checkbox_setting ('Marketing', $settings, $selections);
  get_checkbox_setting ('Rollover',  $settings, $selections);
  get_checkbox_setting ('Vendor',    $settings, $selections);
  get_checkbox_setting ('Alumni',    $settings, $selections);

  echo "<FORM METHOD=POST ACTION=MailTo.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", MAIL_LISTS);

  echo "Include users with the following status:<BR><B>\n";
  foreach ($selections as $k=>$v)
    echo "<input type=checkbox name=$k value='on' $v>&nbsp;$k &nbsp;&nbsp; \n";
  echo "</B><BR>\n";
  echo "<table>\n";
  echo "  <tr valign=top>\n";
  echo "    <TD>Display As</TD>\n";
  echo "    <TD nowrap><B>\n";
  if ($bCSV)
  {
    $csv_checked = 'CHECKED';
    $html_checked = '';
  }
  else
  {
    $csv_checked = '';
    $html_checked = 'CHECKED';
  }
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=0 $html_checked>HTML Table<BR>\n";
  echo "      <INPUT TYPE=RADIO NAME=CSV VALUE=1 $csv_checked>Comma Separated Values<BR>\n";
  echo "    </B></TD>\n";
  echo "  </TR>\n";
  echo "</TABLE>\n";
  echo "<TABLE>\n";
  echo "  <tr valign=top nowrap>\n";
  echo "    <TD nowrap>Include fields<BR>(CSV only)</TD>\n";
  echo "    <TD><B>\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeName $include_name_checked>&nbsp;Names<BR>\n";
  echo "      <INPUT TYPE=CHECKBOX NAME=IncludeDate $include_date_checked>&nbsp;Last Login<BR>\n";
  echo "    </B></TD>\n";
  echo "  </TR>\n";
  echo "  <tr valign=top>\n";
  echo "    <td>CSV Options</td>\n";
  echo "    <td nowrap><b>\n";
  echo "      <input type=checkbox name=IncludeNL $include_nl_checked>&nbsp;Newline<br>\n";
  echo "      <input type=radio name=separator value='comma' $comma_checked>Comma separated<br>\n";
  echo "      <input type=radio name=separator value='semicolon' $semicolon_checked>Semicolon separated<br>\n";
  echo "    </B></TD>\n";
  echo "  </TR>\n";
  echo "</TABLE>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=\"&nbsp;Display Mail List&nbsp;\">\n";
  echo "</FORM>\n";

  // If none of the settings are set, just return now, without fetching
  // anything from the database

  $num_set = 0;
  foreach ($settings as $v)
    $num_set += $v;

  if (0 == $num_set)
    return true;

  $sql = 'SELECT DisplayName, EMail';
  $sql .= ', DATE_FORMAT(LastLogin, "%Y-%m-%d") AS LastLogin';
  $sql .= ' FROM Users';

  // If the number of settings set = the count of the settings then
  // we don't need a WHERE clause, since we're taking EVERYTHING

  if (count($settings) != $num_set)
  {
    $where = '';
    foreach ($settings as $k => $v)
    {
      if ($v)
      {
	if ('' != $where)
	  $where .= ' OR ';
	$where .= "CanSignup='$k'";
      }
    }

    $sql .= " WHERE ($where)";
  }

  if ($settings['Alumni'])
    $sql .= ' ORDER BY LastLogin DESC, DisplayName, ';
  else
    $sql .= ' ORDER BY DisplayName';

  // Fetch the information from the database

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for users failed', $sql);

  if (0 == mysql_num_rows ($result))
    return display_error ('No matching users found');

  // Are we displaying as a CSV (suitable for importing to Excel) or a table?

  if ($bCSV)
  {
    $bFirstRow = true;
    $text = '';

    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->DisplayName"));

      if ('Admin,' != $name)
      {
	if ($bFirstRow)
	  $bFirstRow = false;
	else
	{
	  if ($comma_checked == '')
	    $text = ";\n";
	  else
	    $text = ",\n";

	  if ('CHECKED' == $include_nl_checked)
	    $text .= '<br>';
	}

	$text .= $row->EMail;

	if ($include_name)
	  $text .= ",\"$name\"";

	if ($include_date)
	  $text .= ",\"$row->LastLogin\"";

	echo "$text";
      }
    }
  }
  else
  {
    // Display what we've got in a table

    echo "<TABLE>\n";
    echo "  <TR>\n";
    echo "    <TH ALIGN=LEFT>Name</TH>\n";
    echo "    <TH ALIGN=LEFT>EMail</TH>\n";
    if ($settings['Alumni'])
      echo "    <TH ALIGN=LEFT>Last Login</TH>\n";
    echo "  </TR>\n";

    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->DisplayName"));

      if ('Admin,' != $name)
      {
	echo "  <TR>\n";
	echo "    <TD>$name&nbsp;&nbsp;&nbsp;</TD>\n";
	echo "    <TD>$row->EMail</TD>\n";
	if ($settings['Alumni'])
	  echo "    <TD ALIGN=LEFT>$row->LastLogin</TD>\n";
	echo "  </TR>\n";
      }
    }

    echo "</TABLE>\n";
  }

  return true;
}

function name_mail_list ($i)
{
  switch ($i)
  {
    case MAIL_LIST_NONE: return 'Not a mail list';
    case MAIL_LIST_ALL:  return 'All Users in the Database (includes alumni)';
    case MAIL_LIST_UNPAID:  return 'Unpaid Users for ' . CON_NAME;
    case MAIL_LIST_INTERESTED:  return 'Users who\'ve expressed interest in ' . CON_NAME;
    case MAIL_LIST_ALUMNI:  return 'Alumni (not registered for ' . CON_NAME . ')';
    case MAIL_LIST_ATTENDEES: return CON_NAME . ' Attendees (paid, comp, etc.)';
    case MAIL_LIST_GMS_BY_NAME: return 'GMs who elected to receive Con mail sorted by name';
    case MAIL_LIST_GMS_BY_GAME: return 'GMs who elected to receive Con mail sorted by game';
    case MAIL_LIST_SUBMITTORS: return 'Game submittors';
    case MAIL_LIST_VENDORS: return 'Vendors';
  }

  return "Unknown mailing list $i";
}

function show_a_list ($to, $bCSV)
{
  switch ($to)
  {
    case MAIL_LIST_NONE:
      return true;

    case MAIL_LIST_ALL:
    case MAIL_LIST_ATTENDEES:
    case MAIL_LIST_UNPAID:
    case MAIL_LIST_ALUMNI:
    case MAIL_LIST_INTERESTED:
      return show_user_list ($to, $bCSV);

    case MAIL_LIST_GMS_BY_GAME:
      return show_gms_by_game ($bCSV);

    case MAIL_LIST_GMS_BY_NAME:
      return show_gms_by_name ($bCSV);

    default:
      return display_error ("Unknown Maillist ID $to");
  }

}

function show_user_list ($to, $bCSV)
{
  // The only difference between all users, and all attendees is that we
  // exclude Unpaid users if we're showing attendees

  switch ($to)
  {
    case MAIL_LIST_ALL:
      $title = 'All Users in the Database';
      $where = '';
      break;

    case MAIL_LIST_ATTENDEES:
      $title = 'All Users Attending ' . CON_NAME . ' (Paid, Comp, etc.)';
      $where = 'WHERE CanSignup!="Unpaid" AND CanSignup!="Alumni"';
      break;

    case MAIL_LIST_UNPAID:
      $title = 'Unpaid Users';
      $where = 'WHERE CanSignup="Unpaid"';
      break;

    case MAIL_LIST_ALUMNI:
      $title = CON_SHORT_NAME.' Alumni';
      $where = 'WHERE CanSignup="Alumni"';
      break;

    case MAIL_LIST_INTERESTED:
      $title = 'Users who\'ve expressed an interest in ' . CON_NAME;
      $where = 'WHERE CanSignup!="Alumni"';
      break;

    default:
      return display_error ("Unexpected Maillist ID $to");
  }

  echo '<FONT="+1"><B>' . $title . "</B></FONT><P>\n";;

  $sql = 'SELECT FirstName, LastName, EMail';
  if (MAIL_LIST_ALUMNI == $to)
    $sql .= ', DATE_FORMAT(LastLogin, "%Y-%m-%d") AS LastLogin';
  $sql .= ' FROM Users';
  if ('' != $where)
    $sql .= " $where";
  if (MAIL_LIST_ALUMNI == $to)
    $sql .= ' ORDER BY LastLogin DESC, LastName, FirstName';
  else
    $sql .= ' ORDER BY LastName, FirstName';

  // Fetch the information from the database

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for users failed', $sql);

  if (0 == mysql_num_rows ($result))
    return display_error ('No matching users found');

  // Are we displaying as a CSV (suitable for importing to Excel) or a table?

  if ($bCSV)
  {
    echo "<DIV NOWRAP>\n";
    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->LastName, $row->FirstName"));

      if ('Admin,' != $name)
      {
	echo "\"$name\",\"$row->EMail\",";
	if (MAIL_LIST_ALUMNI == $to)
	  echo "\"$row->LastLogin\",";
	echo "<BR>\n";
      }
    }
    echo "</DIV>\n";
  }
  else
  {
    // Display what we've got in a table

    echo "<TABLE>\n";
    echo "  <TR>\n";
    echo "    <TH ALIGN=LEFT>Name</TH>\n";
    echo "    <TH ALIGN=LEFT>EMail</TH>\n";
    if (MAIL_LIST_ALUMNI == $to)
      echo "    <TH ALIGN=LEFT>Last Login</TH>\n";
    echo "  </TR>\n";

    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->LastName, $row->FirstName"));

      if ('Admin,' != $name)
      {
	echo "  <TR>\n";
	echo "    <TD>$name&nbsp;&nbsp;&nbsp;</TD>\n";
	echo "    <TD>$row->EMail</TD>\n";
	if (MAIL_LIST_ALUMNI == $to)
	  echo "    <TD ALIGN=LEFT>$row->LastLogin</TD>\n";
	echo "  </TR>\n";
      }
    }

    echo "</TABLE>\n";
  }

  return true;
}

function show_gms_by_game ($bCSV)
{
  echo '<FONT="+1"><B>GMs Who Elected to Receive Con Mail, Sorted by Game</B></FONT><P>';

  $sql = 'SELECT Users.FirstName, Users.LastName, Users.EMail,';
  $sql .= ' Events.Title';
  $sql .= ' FROM Users, GMs, Events';
  $sql .= ' WHERE GMs.ReceiveConEMail="Y"';
  $sql .= '   AND Users.UserId=GMs.UserId';
  $sql .= '   AND Events.EventId=GMs.EventId';
  $sql .= ' ORDER BY Events.Title, Users.LastName, Users.FirstName';

  // Fetch the information from the database

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs by game failed');

  if (0 == mysql_num_rows ($result))
    return display_error ('No matching users found');

  // Are we displaying as a CSV (suitable for importing to Excel) or a table?

  if ($bCSV)
  {
    echo "<DIV NOWRAP>\n";
    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->LastName, $row->FirstName"));
      echo "\"$row->Title\",\"$name\",\"$row->EMail\",<BR>\n";
    }
    echo "</DIV>\n";
  }
  else
  {
    // Display what we've got in a table

    echo "<TABLE>\n";

    $last_game = '';

    while ($row = mysql_fetch_object ($result))
    {
      if ($last_game != $row->Title)
      {
	echo "  <TR>\n";
	echo "    <TH ALIGN=LEFT COLSPAN=2>&nbsp;<BR>$row->Title</TH>\n";
	echo "  </TR>\n";
      }

      $last_game = $row->Title;

      $name = stripslashes (trim ("$row->LastName, $row->FirstName"));

      echo "  <TR>\n";
      echo "    <TD>$name&nbsp;&nbsp;&nbsp;</TD>\n";
      echo "    <TD>$row->EMail</TD>\n";
      echo "  </TR>\n";
    }

    echo "</TABLE>\n";
  }

  return true;
}

function dump_bool ($b, $name)
{
  echo "<!-- $name: ";
  if ($b)
    echo 'true';
  else
    echo 'false';
  echo " -->\n";
}

function show_gms_by_name ($bCSV, $bGames, $bNames, $bIncludeNL, $bComma, $bAllGMs)
{
  if ($bComma)
    $separator = ',';
  else
    $separator = ';';

  // Start with the game email lists for those games that have elected to
  // send mail to the game list

  $sql = 'SELECT GameEMail, Title';
  $sql .= ' FROM Events';
  $sql .= ' WHERE Events.ConMailDest = "GameMail"';

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Query for game email addresses failed');

  if (0 != mysql_num_rows($result))
    echo "<b>Addresses for event that elected to send con mail to the event mail address</b><br>\n";

  if ($bCSV)
  {
    echo "<div nowrap>\n";
    while ($row = mysql_fetch_object ($result))
    {
      printf ('%s%s ', $row->GameEMail, $separator);
      if ($bGames)
	printf ('"%s"%s ', $row->Title, $separator);
      if ($bIncludeNL)
	echo "\n<br>";
    }
    echo "<br></div>\n";
  }
  else
  {
    echo "<table>\n";
    while ($row = mysql_fetch_object ($result))
    {
      echo "  <tr>\n";
      echo "    <td>$row->GameEMail</td>\n";
      if ($bGames)
	echo "    <td>&nbsp;&nbsp;&nbsp;$row->Title</td>\n";
      echo "  </tr>\n";
    }
    echo "</table>\n";
  }

  // Now go through the GMs

  $sql = 'SELECT Users.DisplayName, Users.EMail,';
  $sql .= ' Events.Title';
  $sql .= ' FROM Users, GMs, Events';
  $sql .= ' WHERE Users.UserId=GMs.UserId';
  $sql .= '   AND Events.EventId=GMs.EventId';

  if (! $bAllGMs)
  {
    $sql .= ' AND GMs.ReceiveConEMail="Y"';
    $sql .= '   AND Events.ConMailDest="GMs"';
  }
  
  if ($bNames)
    $sql .= ' ORDER BY Users.DisplayName, Events.Title';
  else
    $sql .= ' ORDER BY Users.EMail';

  // Fetch the information from the database

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs by game failed');

  if (0 == mysql_num_rows ($result))
    return display_error ('No matching users found');

  // Are we displaying as a CSV (suitable for importing to Excel) or a table?

  if ($bCSV)
  {
    $last_name = '';
    echo "<DIV NOWRAP>";
    echo "<b>Address for GMs who've elected to receive Con mail</b>\n";

    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->DisplayName"));
      if ($last_name == $name)
      {
	if ($bGames)
	  printf ('"%s"%s ', $row->Title, $separator);
      }
      else
      {
	if ($bIncludeNL)
	  echo "\n<BR>";

	if ($bNames)
	  printf ('"%s"%s ', $name, $separator);

	printf ('%s%s ', $row->EMail, $separator);
	if ($bGames)
	  printf ('"%s"%s ', $row->Title, $separator);
      }
      $last_name = $name;
    }
    echo "\n<BR>\n</DIV>\n";
  }
  else
  {
    // Display what we've got in a table
    if (! $bAllGMs)
      echo "<b>Address for presenters who've elected to receive mail</b>\n";
    else
      echo "<b>Address for all the presenters</b>\n";

    echo "<table>\n";
    echo "  <tr valign=\"top\">\n";

    $last_email = '';

    while ($row = mysql_fetch_object ($result))
    {
      $name = stripslashes (trim ("$row->DisplayName"));

      if ($last_email == $row->EMail)
      {
	    if ($bGames)
	      echo ",<BR>$row->Title";
      }
      else
      {
	    if ($last_email != '')
	    {
	      if ($bGames)
	        echo "\n    </TD>\n";
	    }

	    echo "  </TR>\n";
	    echo "  <TR VALIGN=TOP>\n";
	    if ($bNames)
	      echo "    <TD>$name&nbsp;&nbsp;&nbsp;</TD>\n";

	    echo "    <TD>$row->EMail&nbsp;&nbsp;&nbsp;</TD>\n";

	    if ($bGames)
	    {
	       echo "    <TD>\n";
	       echo "      $row->Title";
	    }
      } // if new row.

      $last_email = $row->EMail;
    }

    if ($bGames)
      echo "\n    </TD>\n";
    echo "  </TR>\n";
    echo "</TABLE>\n";
  }

  return true;
}

function show_bio_lists ()
{
  if (array_key_exists ('ShowNames', $_REQUEST))
    $bNames = intval ($_REQUEST['ShowNames']);
  else
    $bNames = 0;

  if ($bNames)
    $names_checked = 'CHECKED';
  else
    $names_checked = '';

  if (array_key_exists ('ShowOnlyMissingBios', $_REQUEST))
    $bOnlyMissing = intval ($_REQUEST['ShowOnlyMissingBios']);
  else
    $bOnlyMissing = 0;

  if ($bOnlyMissing)
    $only_missing_checked = 'CHECKED';
  else
    $only_missing_checked = '';

  if ($bOnlyMissing)
    echo '<FONT="+1"><B>Mail List of Users Who Need to Submit Bios</B></FONT><P>';
  else
  {
    echo '<FONT="+1"><B>Mail List of Users Who Should to Submit Bios</B></FONT><BR>';
    echo "Users who have submitted bios are in black<BR>\n";
    echo "Users who have <b>not</b> submitted bios are in <font color=red>red</font>.  ";
    echo "We will spank them like a bad, bad donkey.<P>\n";
  }

  echo "<FORM METHOD=POST ACTION=MailTo.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", MAIL_BIO_LISTS);
  echo "<INPUT TYPE=CHECKBOX NAME=ShowNames $names_checked VALUE=1> Show Names<BR>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=ShowOnlyMissingBios $only_missing_checked VALUE=1> Show only users who have not submitted a bio<BR>\n";

  echo "<INPUT TYPE=SUBMIT VALUE=\"&nbsp;Update Mail List&nbsp;\">\n";
  echo "</FORM>\n";

  $bio_users = array ();

  // Start by gathering the list of GMs

  $sql = 'SELECT DISTINCT Users.UserId, Users.DisplayName,';
  $sql .= ' Users.EMail';
  $sql .= ' FROM GMs, Users';
  $sql .= ' WHERE Users.UserId=GMs.UserId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    $bio_users[$row->UserId] = "$row->DisplayName|$row->EMail";
  }

  // Now add the con staff.  Don't forget to skip Admin (UserId==1)

  $sql = 'SELECT UserId, DisplayName, EMail';
  $sql .= ' FROM Users';
  $sql .= ' WHERE ""<>Priv';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for con staff failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if (1 != $row->UserId)
      $bio_users[$row->UserId] = "$row->DisplayName|$row->EMail";
  }

  // Sort the array BY THE VALUE (as opposed to the key)

  asort ($bio_users);
  reset ($bio_users);

  $users_missing_bios = 0;
  $users_submitted_bios = 0;

  //  dump_array ('bio_users', $bio_users);

  foreach ($bio_users as $user_id => $v)
  {
    $tmp = explode ('|', $v);
    $display_name = $tmp[0];
    $email = $tmp[1];

    //    echo "first_name: $first_name<br>\n";
    //    echo "last_name: $last_name<br>\n";
    //    echo "email: $email<br>\n";

    // Fetch bio information

    $sql = 'SELECT LENGTH(BioText) AS BioTextLen';
    $sql .= " FROM Bios WHERE UserId=$user_id";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for bio failed', $sql);

    $row = mysql_fetch_object ($result);

    $have_bio = false;

    if ($row)
    {
      $have_bio = (0 != $row->BioTextLen);
    }

    if ($have_bio)
      $users_submitted_bios++;
    else
      $users_missing_bios++;

    // If we're supposed to include users who have submitted their bios,
    // do so

    if ($bOnlyMissing & $have_bio)
      continue;

    // Show the name, if asked

    if ((! $bOnlyMissing) && (! $have_bio))
      echo "<FONT COLOR=red>";

    if ($bNames)
      echo "\"$display_name\" ";

    echo "&lt;$email&gt;";

    if ((! $bOnlyMissing) && (! $have_bio))
      echo "</FONT>";
    
    echo ",<BR>\n";
  }
}

function show_waitlisted_players ()
{
  echo '<h1>Mail Lists of Waitlisted Volunteers</h1>';

  // Gather the list of waitlisted players

  $sql = 'SELECT Users.DisplayName, Users.EMail,';
  $sql .= ' Events.Title, Runs.Day, Runs.StartHour, Runs.RunId';
  $sql .= ' FROM Users, Events, Runs, Signup';
  $sql .= ' WHERE Signup.State="Waitlisted"';
  $sql .= '   AND Users.UserId=Signup.UserId';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= ' ORDER BY Runs.Day, Runs.StartHour, Events.Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for Waitlisted players failed', $sql);

  $cur_run_id = -1;

  while ($row = mysql_fetch_object ($result))
  {
    if ($cur_run_id != $row->RunId)
    {
      printf ("<br><b>%s %s - %s</b><p>\n",
	      $row->Day,
	      start_hour_to_12_hour($row->StartHour),
	      $row->Title);
      $cur_run_id = $row->RunId;
    }

    //    echo "\"$row->LastName, $row->FirstName\" ";
    //    echo "&lt;$row->EMail&gt;,<br>";
    echo "$row->DisplayName - $row->EMail<br>";
  }
}

?>