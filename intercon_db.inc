<?php
if (strstr($_SERVER["SERVER_NAME"], "interactiveliterature.org")) {
    session_set_cookie_params(0, '/', '.interactiveliterature.org');
}

// Turn off PHP notices for all these define statements.  PHP for Win throws a Notice when you don't 
// quote the global constants below.  Will turn on/off as needed.

error_reporting(0);

ini_set( 
  'include_path', 
  ini_get( 'include_path' ) . PATH_SEPARATOR . "/Users/bethlakshmi/pear/share/pear/Mail-1.2.0"
);

$pear_user_config = '/home/neiladmin/.pearrc';

require ('intercon_constants.inc');
require ('intercon_navbar.inc');

// File not found in repository or FTP.... 
// require ('Mail.php');

define (SHOW_TSHIRTS, 1);
define (TSHIRT_DOLLARS, 20);
define (UNLIMITED_SIGNUPS, 100);

// Orderings

define (ORDER_BY_SEQ, 1);
define (ORDER_BY_NAME, 2);
define (ORDER_BY_AGE, 3);
define (ORDER_BY_GENDER, 4);
define (ORDER_BY_CREATION, 5);
define (ORDER_BY_PAYMENT_STATUS_CHANGE, 6);
define (ORDER_BY_LAST_MODIFIED, 7);
define (ORDER_BY_LAST_LOGIN, 8);

// Privileges - These must match the database SET for the User Priv field

define (PRIV_BID_COM, 'BidCom');
define (PRIV_BID_CHAIR, 'BidChair');
define (PRIV_CON_COM, 'ConCom');
define (PRIV_GM_LIAISON, 'GMLiaison');
define (PRIV_OUTREACH, 'Outreach');
define (PRIV_REGISTRAR, 'Registrar');
define (PRIV_SCHEDULING, 'Scheduling');
define (PRIV_VENDORS, 'Vendors');
define (PRIV_DONORS, 'Donors');
define (PRIV_PRECON_BID_CHAIR, 'PreConBidChair');
define (PRIV_PRECON_SCHEDULING, 'PreConScheduling');

// Mail privileges

define (PRIV_MAIL_GMS, 'MailToGMs');
define (PRIV_MAIL_ATTENDEES, 'MailToAttendees');
define (PRIV_MAIL_ALL, 'MailToAll');
define (PRIV_MAIL_VENDORS, 'MailToVendors');
define (PRIV_MAIL_UNPAID, 'MailToUnpaid');
define (PRIV_MAIL_ALUMNI, 'MailToAlumni');


// God privileges

define (PRIV_STAFF, 'Staff');
define (PRIV_ADMIN, 'Admin');

// Comp limit

define (COMPS_PER_GAME, 2);

// Action values - index.php

define (PROMPT_FOR_LOGIN, 0);
define (LOGIN_USER, 1);
define (SHOW_USER_HOMEPAGE, 2);
define (WITHDRAW_FROM_GAME, 3);
define (LOGOUT_USER, 4);
define (NEW_USER, 5);
define (ADD_USER, 6);
define (UPDATE_USER, 7);
define (SHOW_USER, 8);
define (SEND_PASSWORD, 9);
define (REQUEST_PASSWORD, 10);
define (CHANGE_PASSWORD, 11);
define (PROCESS_PASSWORD_CHANGE, 12);
define (SELECT_USER_TO_EDIT, 13);
define (EDIT_USER, 14);
define (PROCESS_EDIT_USER, 15);
define (PAID_UP, 16);
define (SHOW_COMPED_USERS, 17);
define (SELECT_USER_TO_DELETE, 18);
define (SHOW_USER_TO_DELETE, 19);
define (DELETE_USER, 80);
define (SELECT_USER_TO_VIEW, 81);
define (VIEW_USER, 82);
define (WITHDRAW_FROM_GAME_CONFIRMED, 83);
define (EDIT_BIO, 84);
define (UPDATE_BIO, 85);
define (BIO_REPORT, 86);
define (WHO_IS_WHO, 87);
define (WITHDRAW_USER_FROM_ALL_GAMES, 88);
define (WITHDRAW_USER_FROM_ALL_GAMES_CONFIRMED, 89);
define (SELECT_USER_TO_SET_PASSWORD, 140);
define (DISPLAY_PASSWORD_FORM_FOR_USER, 141);
define (PROCESS_PASSWORD_FORM_FOR_USER, 142);
define (SELECT_USER_TO_BECOME, 143);
define (BECOME_USER, 144);
define (DISPLAY_POLL_FORM, 145);
define (PROCESS_POLL_FORM, 146);
define (CONVERT_AGE_TO_YEAR, 147);


// Action values - Schedule.php

define (PROCESS_ADD_GAME, 21);
define (EDIT_GAME, 22);
define (LIST_GAMES, 23);
define (SCHEDULE_SHOW, 24);
define (SCHEDULE_SHOW_GAME, 25);
define (SCHEDULE_SIGNUP, 26);
define (SCHEDULE_SHOW_SIGNUPS, 27);
define (SCHEDULE_UPDATE_SIGNUP, 28);
define (DISPLAY_GM_LIST, 29);
define (EDIT_GM, 30);
define (ADD_GM, 31);
define (PROCESS_ADD_GM, 32);
define (SCHEDULE_UPDATE_GM, 33);
define (SCHEDULE_WITH_COUNTS, 34);
define (SCHEDULE_AWAY_FORM, 35);
define (SCHEDULE_PROCESS_AWAY_FORM, 36);
define (SCHEDULE_SHOW_ALL_SIGNUPS, 37);
define (SCHEDULE_FREEZE_GENDER_BALANCE, 38);
define (SCHEDULE_CONFIRM_FREEZE_GENDER_BALANCE, 39);
define (SCHEDULE_COMP_USER_FOR_EVENT, 160);
define (SCHEDULE_IRON_GM_TEAM_LIST, 161);
define (SCHEDULE_SHOW_IRON_GM_TEAM_FORM, 162);
define (SCHEDULE_PROCESS_IRON_GM_TEAM_FORM, 163);
define (SCHEDULE_SELECT_USER_FOR_IRON_GM, 164);
define (SCHEDULE_ADD_IRON_GM, 165);

// Action values in SpecialEvents.php

define (SPECIAL_EVENT_FORM, 40);
define (SPECIAL_EVENT_ADD, 41);
define (SPECIAL_EVENT_LIST, 42);

// Action values in Bids.php

define (BID_GAME, 50);
define (BID_PROCESS_FORM, 51);
define (BID_REVIEW_BIDS, 52);
define (BID_CHANGE_STATUS, 53);
define (BID_PROCESS_STATUS_CHANGE, 54);
define (BID_SHOW_BID, 55);
//define (BID_REGISTER_SUBMITTER, 56);
define (BID_FEEDBACK_BY_GAME, 57);
define (BID_PROCESS_FEEDBACK_BY_GAME, 58);
define (BID_FEEDBACK_SUMMARY, 59);
define (BID_FEEDBACK_BY_ENTRY, 200);
define (BID_FEEDBACK_PROCESS_ENTRY, 201);
define (BID_FEEDBACK_BY_CONCOM, 202);
define (BID_FEEDBACK_PROCESS_BY_CONCOM, 203);

// Action values in ListGames.php

define (LIST_GAMES, 60);
define (ADD_RUN, 61);
define (PROCESS_ADD_RUN, 62);
define (EDIT_RUN, 63);
define (PROCESS_EDIT_RUN, 64);
define (LIST_GAMES_BY_TIME, 65);
//define (ADD_PARALLEL_RUN, 66);
//define (PROCESS_ADD_PARALLEL_RUN, 67);
//define (LIST_TO_ADD_PARALLEL_RUN, 68);
define (LIST_ADD_OPS, 69);
define (LIST_ADD_CONSUITE, 68);

// Action values in MailTo.php

define (MAIL_SHOW_FORM, 70);
define (MAIL_SEND, 71);
define (MAIL_LISTS, 72);
define (MAIL_GM_LISTS, 73);
define (MAIL_BIO_LISTS, 74);
define (MAIL_WAITLISTED, 75);
define (MAIL_BID_SUBMITTERS, 76);
define (MAIL_IRON_GM_LIST, 77);

// Action values in ConStatus.php

define (SHOW_STATUS, 90);
define (UPDATE_STATUS, 91);

// Action values in Reports.php

define (REPORT_PER_GAME, 100);
define (REPORT_PER_USER, 101);
define (REPORT_WHOS_NOT_PLAYING_FORM, 102);
define (REPORT_WHOS_NOT_PLAYING, 103);
define (REPORT_OPS_TRACK, 104);
define (REPORT_GAMES_BY_TIME, 105);
define (REPORT_USERS_CSV, 106);
define (REPORT_REGISTRATION, 107);
define (REPORT_BY_AGE, 108);
define (REPORT_HOW_HEARD, 109);
define (REPORT_CONSUITE_TRACK, 190);
define (REPORT_PER_ROOM, 191);


// Action values in Referrers.php

define (VIEW_REFERRERS, 110);
define (SUMMARIZE_REFERRERS, 111);

// Action values in TShirts.php

define (SHOW_TSHIRT_FORM, 120);
define (PROCESS_TSHIRT_FORM, 121);
define (SHOW_TSHIRT_REPORT, 122);
define (SHOW_TSHIRT_SUMMARY, 123);
define (SHOW_TSHIRT_PAYMENT_LINK, 124);
define (PROCESS_TSHIRT_PAID, 125);
define (PROCESS_TSHIRT_CANCEL, 126);
define (SHOW_INDIV_TSHIRT_FORM, 127);
define (PROCESS_INDIV_TSHIRT_FORM, 128);
define (SELECT_USER_TO_SELL_SHIRT, 129);

// Action values for SignupSpy

define (SHOW_LIMITED_SIGNUPS, 130);
define (SHOW_ALL_SIGNUPS, 131);

// Action values for Vendors

define (SHOW_VENDORS, 150);
define (SHOW_DONORS, 155);
define (EDIT_VENDOR_OR_DONOR, 151);
define (DELETE_VENDOR_OR_DONOR, 152);
define (PROCESS_EDIT_VENDOR_OR_DONOR, 153);
define (PROCESS_DELETE_VENDOR, 154);

// Action values for Thursday

define (THURSDAY_THING, 170);
define (THURSDAY_REPORT, 171);
define (THURSDAY_SELECT_USER, 172);
define (THURSDAY_EDIT_USER, 173);
define (THURSDAY_PROCESS_USER, 174);

// Action values for biddingAGame

define (BAG_SHOW_FORM, 180);
define (BAG_UPDATE, 181);
define (BAG_BID_PAGE, 182);

// Action values for Plugs

define (PLUGS_SHOW_FORM, 190);
define (PLUGS_PROCESS_FORM, 191);
define (PLUGS_SHOW_PAGE, 192);
define (PLUGS_MANAGE_PLUGS, 193);
define (PLUGS_CONFIRM_DELETE, 194);
define (PLUGS_DELETE, 195);

// Action values for PreConEvents

define (PRECON_SHOW_EVENT_FORM, 210);
define (PRECON_PROCESS_EVENT_FORM, 211);
define (PRECON_MANAGE_EVENTS, 212);
define (PRECON_SHOW_STATUS_FORM, 213);
define (PRECON_PROCESS_STATUS_FORM, 214);
define (PRECON_SHOW_EVENT, 215);
define (PRECON_SHOW_RUN_FORM, 216);
define (PRECON_PROCESS_RUN_FORM, 217);

// Action values for Dead Dog

define (DEAD_DOG, 250);
define (DEAD_DOG_REPORT, 251);
define (DEAD_DOG_SELECT_USER, 252);
define (DEAD_DOG_EDIT_USER, 253);
define (DEAD_DOG_PROCESS_USER, 254);
define (DEAD_DOG_NEW_USER, 255);
define (DEAD_DOG_CREATE_USER, 256);
define (DEAD_DOG_PAYPAL_REDIRECT, 257);

// List types

define (LIST_UNDEFINED, 0);
define (LIST_BY_GAME, 1);
define (LIST_BY_TIME, 2);

// Maximum tracks

define (MAX_TRACKS, 15);

// Minimum and maximum times for con days

define (FRI_MIN, 12);    // 12 noon
define (FRI_MAX, 24);    // 7AM
define (SAT_MIN, 7);     // 8AM
define (SAT_MAX, 24);    // 7AM
define (SUN_MIN, 7);     // 8AM
define (SUN_MAX, 24);    // 4PM

define (SESSION_LOGIN_USER_GM, 'LoginUserGM_' . CON_ID);
define (SESSION_LOGIN_USER_ID, 'LoginUserId_' . CON_ID);
define (SESSION_LOGIN_USER_PRIVS, 'LoginUserPrivs_' . CON_ID);
define (SESSION_REFERRER_ID, 'ReferrerId_' . CON_ID);
define (SESSION_LOGIN_USER_GENDER, 'LoginUserGender_' . CON_ID);
define (SESSION_LOGIN_USER_NAME, 'LoginUserName_' . CON_ID);
define (SESSION_LOGIN_USER_EMAIL, 'LoginUserEMail_' . CON_ID); 
define (SESSION_CON_SHOW_SCHEDULE, 'ConShowSchedule_' . CON_ID);
define (SESSION_CON_NEWS, 'ConNews_' . CON_ID);
define (SESSION_ATTENDENCE_SHOWN, 'AttendenceShown_' . CON_ID);
define (SESSION_CON_LAST_UPDATED, 'ConLastUpdated_' . CON_ID);
define (SESSION_SEQUENCE_NUMBER, 'SequenceNumber_' . CON_ID);
define (SESSION_TIMEOUT, 'Timeout_' . CON_ID);

// Raise the level of error reporting

if (1 == DEVELOPMENT_VERSION)
  error_reporting (E_ALL);

function request_string($key, $default='')
{
  if (array_key_exists($key, $_REQUEST))
    return trim($_REQUEST[$key]);
  else
    return $default;
}

function request_int($key, $default=0)
{
  $v = $default;
  if (array_key_exists($key, $_REQUEST))
    $v = intval(trim($_REQUEST[$key]));

  return $v;
}

function post_string($key, $default='')
{
  if (array_key_exists($key, $_POST))
    return trim($_POST[$key]);
  else
    return $default;
}

function post_int($key, $default=0)
{
  $v = $default;
  if (array_key_exists($key, $_POST))
    $v = intval(trim($_POST[$key]));

  return $v;
}

/*
 * user_has_priv
 *
 * Checks whether a user has a specified privilege
 */

function user_has_priv ($priv, $accept_staff_priv=true)
{
  
  // If the session hasn't been setup yet, then the user can't be
  // privileged

  if (! array_key_exists (SESSION_LOGIN_USER_PRIVS, $_SESSION))
    return false;

  // See if the user has the privilege

  if (is_string (stristr ($_SESSION[SESSION_LOGIN_USER_PRIVS], ",$priv,")))
    return true;

  // OK, if that didn't work, and if Staff privilege is an acceptable
  // substitute, see if the user has that

  if ($accept_staff_priv)
    if (is_string (stristr ($_SESSION[SESSION_LOGIN_USER_PRIVS], ',Staff,')))
      return true;

  // Admin priv is ALWAYS an acceptable substitute

  return is_string (stristr ($_SESSION[SESSION_LOGIN_USER_PRIVS], ',Admin'));
}

function user_has_any_mail_priv ()
{
  return user_has_priv (PRIV_MAIL_ALL) ||
         user_has_priv (PRIV_MAIL_ATTENDEES) ||
         user_has_priv (PRIV_MAIL_GMS) ||
         user_has_priv (PRIV_MAIL_VENDORS) ||
         user_has_priv (PRIV_MAIL_UNPAID) ||
         user_has_priv (PRIV_MAIL_ALUMNI);
}

/*
 * get_bgcolor
 *
 * Returns the color code for the assorted states we're interested in
 */

function get_bgcolor ($state)
{
  return ' BGCOLOR="'.get_bgcolor_hex($state).'"';
}

/*
 * get_bgcolor_hex
 *
 * Returns just the hex color code as in get_bgcolor
 */
function get_bgcolor_hex ($state)
{
  switch ($state)
  {
    case 'Full':                return '#FFCCCC';  // Light red
    case 'Confirmed':           return '#CCFFCC';  // Light green
    case 'Waitlisted':          return '#FFFFCC';  // Light yellow
    case 'CanPlayConcurrently': return '#CCCCFF';  // Light blue
    case 'Away':                return '#CCCCCC';  // Light gray
  }
  return "#FFFFFF";
}

/*
 * day_to_date
 *
 * Convert the day stored in the database to a complete date string
 */

function day_to_date ($day)
{
  switch ($day)
  {
    case 'Fri': return FRI_TEXT;
    case 'Sat': return SAT_TEXT;
    case 'Sun': return SUN_TEXT;
  }

  // Should never get here!

  return "Invalid day \"$day\"";
}

/*
 * is_logged_in
 *
 * Returns true if the user is logged in
 */

function is_logged_in ()
{
  return ! empty ($_SESSION[SESSION_LOGIN_USER_ID]);
}

/*
 * is_user
 *
 * Returns true if the logged in user ID matches the one specified
 */

function is_user($user_id)
{
  if (! array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
    return false;

  return $user_id == $_SESSION[SESSION_LOGIN_USER_ID];
}

/*
 * to_secs
 *
 * Converts the TIME field from the database to a time_t
 */

function to_secs ($start_time)
{
  //  return strtotime ($start_time);
  return (mktime (intval ($start_time),  // Hours
		  0,                     // Minutes
		  0));                   // Seconds
}

/*
 * intercon_db_connect
 *
 * Connects to the database using the privilege level saved in the
 * session.  If there is no saved privilege level, default to
 * user privileges
 */

function intercon_db_connect ($forceadmin=0)
{
  if (user_has_priv (PRIV_ADMIN, FALSE) || $forceadmin)
  {
    $user = DB_ADMIN_USR;
    $pass = DB_ADMIN_PWD;
  }
  elseif (user_has_priv (PRIV_STAFF, FALSE) || 
	  user_has_priv (PRIV_BID_CHAIR, FALSE) ||
	  user_has_priv (PRIV_REGISTRAR, FALSE) ||
	  user_has_priv (PRIV_SCHEDULING, FALSE))
  {
    $user = DB_STAFF_USR;
    $pass = DB_STAFF_PWD;
  }
  else
  {
    $user = DB_USER_USR;
    $pass = DB_USER_PWD;
  }
  
  $link = @mysql_connect (DB_SERVER, $user, $pass);
  if ($link && mysql_select_db (DB_NAME))
    return $link;

  return FALSE;
}

/*
 * dump_array
 *
 * Dump an array as a set of HTML comments for debugging
 */

function dump_array ($description, $array)
{
  echo "\n<!-- Dump of $description -->\n";

  // If this is a simple scalar, just display it

  if (! is_array ($array))
  {
    echo "<!-- scalar value: $array -->\n";
    return;
  }

  // OK, we've got an array  Iterate over it's element and display them

  reset ($array);
  foreach ($array as $index => $value)
  {
    echo "<!--   [$index] = $value -->\n";
  }
}

/*
 * html_begin
 *
 * Applies the standard HTML boilerplate to the HTML page we're building
 */

function html_begin ($bShowLogo=true)
{
  //  dump_array ('$_SERVER', $_SERVER);
  //  dump_array ('$_SESSION', $_SESSION);
  //  dump_array ('$_GET', $_GET);
  //  dump_array ('$_POST', $_POST);
  //  dump_array ('$_COOKIE', $_COOKIE);

  echo "<!-- Begin Standard Prefix -->\n";
  echo "<html>\n";
  echo "<head>\n";
  printf ("<title>%s</title>\n", CON_NAME);
  echo "<meta http-equiv=\"charset\" content=\"iso-8859-1\">\n";
  // Verification for Google apps for intercon-m.org
  echo "<meta name=\"google-site-verification\" content=\"9yWQZ9402aK1LXPwNS5VqaZFV0-O7IGI_ZTMi1Fws3M\" />\n";
  echo "<link href='screen.css.php' rel='stylesheet' type='text/css' media='screen'>\n";
  echo "<link href='print.css' rel='stylesheet' type='text/css' media='print'>\n";
  echo "<STYLE>\n";
  //  echo "  body {font-family: Arial, Helvetica, sans-serif}\n";
  //  echo "  li, td {font-family: Arial, Helvetica, sans-serif}\n";
  echo "  hr { color: #9a8069; width=300 text-align=left}\n";
  echo "  .reserve { background-color: #006600; color: #ffffff }\n";
  echo "  .reserveBody { background-color: #FFFFFF; color: #000000 }\n";
  echo "  .reserveReverse { color: #006600; background-color: #ffffff }\n";
  echo "  .shirt { background-color: #cccccc; color: #503984 }\n";
  echo "  .shirtBody { background-color: #FFFFFF; color: #000000 }\n";
  echo "  .shirtReverse { color: #503984; background-color: #cccccc }\n";
  echo "</STYLE>\n";
  echo "</HEAD>\n";
  echo "<BODY>\n";

  display_navbar();

  if (DEVELOPMENT_VERSION)
    printf ("<p class=dev_warning>Development Version  %s@%s</p>\n",
	    DB_NAME,
	    DB_SERVER);

  // Show the con logo.  Some pages may want to handle this themselves

  if ($bShowLogo)
    echo "<div class=print_logo><img src=".PAGE_BANNER."></div>\n";
}

/*
 * html_end
 *
 * Standard boilerplate to end the page
 */

function html_end ($bShowCopyright=true)
{
  if ($bShowCopyright)
  {
     if (file_exists(TEXT_DIR.'/copyright.html'))
	include(TEXT_DIR.'/copyright.html');	
  }
  echo"</BODY>\n</HTML>\n";
}

/*
 * con_signups_allowed
 *
 * Returns the number of signups allowed now
 */
 
function con_signups_allowed () {
    // we cache this value not only for performance reasons, but because the SELECT
    // statement can sometimes fail if tables are locked, as happens late in the
    // registration functions
    if (!isset($GLOBALS['CON_SIGNUPS_ALLOWED']))
        $GLOBALS['CON_SIGNUPS_ALLOWED'] = calculate_con_signups_allowed();
    
    return $GLOBALS['CON_SIGNUPS_ALLOWED'];
}

function calculate_con_signups_allowed ()
{
  // If we haven't set the session variables, then the user hasn't logged
  // in, and he can't signup for anything

  if (! is_logged_in())
    return 0;

  // Otherwise fetch from con settings.  Yes, really.  Using a session
  // variable is a security risk because site freeze won't affect logged
  // in users.

  $result = mysql_query ("SELECT SignupsAllowed FROM Con");
  if (! $result)
  {
    return 0;
  }

  $row = mysql_fetch_object ($result);
  if (! $row)
  {
    return 0;
  }

  // Return how many games a user can signup for, if any

  switch ($row->SignupsAllowed)
  {
    case '1':   return 1;
    case '2':   return 2;
    case '3':   return 3;
    case 'Yes': return UNLIMITED_SIGNUPS;
  }

  // Signups aren't allowed at this time
  
  return 0;
}

// Only use this if you need to make the distinction between signups not being
// allowed _yet_ and signups being _frozen_.
function con_signups_frozen () {
  $result = mysql_query ("SELECT SignupsAllowed FROM Con");
  if (!$result) {
      return 0;
  }
  
  $row = mysql_fetch_object ($result);
  if (!$row) {
    return 0;
  }
  
  return ($row->SignupsAllowed == "NotNow");
}

function attendees_at_max() {
  $sql = 'SELECT CanSignup, COUNT(*) AS Count FROM Users';
  $sql .= ' WHERE CanSignup<>"Alumni"';
  $sql .= '   AND CanSignup<>"Unpaid"';
  $sql .= ' GROUP BY CanSignup ORDER BY CanSignup';
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error('Failed to get user summary');

  $attendees = 0;
  while ($row = mysql_fetch_object($result))
  {
    if (('Alumni' <> $row->CanSignup) && ('Unpaid' <> $row->CanSignup))
      $attendees += $row->Count;
  }
  
  return $attendees >= CON_MAX;
}

/*
 * form_section
 *
 * Displays a section header for the form being built
 */

function form_section ($text, $leading_blank_line=TRUE)
{
  if ($leading_blank_line)
    echo"  <TR><TD COLSPAN=2>&nbsp;</TD></TR>\n";

  echo "<TR><TD COLSPAN=2 BGCOLOR=\"#CCFFFF\"><B>&nbsp;<BR>$text</B></TD></TR>\n";
}

/*
 * form_textarea
 *
 * Display a textarea field in the form being built
 */

function form_textarea ($display,
			$key,
			$rows,
			$leading_blank_line=TRUE,
			$required = FALSE)
{
  if (array_key_exists ($key, $_POST))
  {
    if (1 == get_magic_quotes_gpc())
      $text = stripslashes ($_POST[$key]);
    else
      $text = $_POST[$key];
  }
  else
    $text='';


  if ($required)
    $req_prefix = '<font color="red">*</font>&nbsp;';
  else
    $req_prefix = '';

  echo "  <tr>\n";
  echo "     <td colspan=\"2\">\n";
  if ($leading_blank_line)
    echo "      &nbsp;<br clear=all>\n";
  echo "      $req_prefix$display<br>\n";
  printf ('      <textarea name="%s" cols="80" rows="%d" wrap="physical">' .
	  "%s</textarea>\n",
	  $key, $rows, $text);
  echo "    </td>\n";
  echo "  </tr>\n";
}

/*
 * form_yn
 *
 * Display a Yes/No field in the form being built
 */

function form_yn ($display, $key)
{
  $sel_n = '';
  $sel_y = '';

  if (! array_key_exists ($key, $_POST))
    $_POST[$key] = 'N';

  switch ($_POST[$key])
  {
    default:
    case 'N': $sel_n = 'SELECTED'; break;
    case 'Y': $sel_y = 'SELECTED'; break;
  }

  echo "  <TR>\n";
  echo "    <TD COLSPAN=2>$display\n";
  echo "      <select name=$key SIZE=1>\n";
  echo "        <option value=N $sel_n>No</option>\n";
  echo "        <option value=Y $sel_y>Yes</option>\n";
  echo "      </select>\n";
  echo "    </TD>\n";
  echo "  </TR>   \n";
}

/*
 * form_preferred_contact
 *
 * Display the controls to allow the user to select his or her preferred
 * method of contact
 */

function form_preferred_contact ($display, $key)
{
  $sel_email = '';
  $sel_dphone = '';
  $sel_ephone = '';

  if (! array_key_exists ($key, $_POST))
    $value = 'EMail';
  else
    $value = $_POST[$key];

  switch ($value)
  {
    default:
    case 'EMail':    $sel_email = 'SELECTED';  break;
    case 'DayPhone': $sel_dphone = 'SELECTED'; break;
    case 'EvePhone': $sel_ephone = 'SELECTED'; break;
  }

  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD ALIGN=RIGHT>$display:</TD>\n";
  echo "    <TD ALIGN=LEFT>\n";
  echo "      <SELECT NAME=$key SIZE=1>\n";
  echo "        <option value=EMail $sel_email>EMail</option>\n";
  echo "        <option value=DayPhone $sel_dphone>Daytime Phone</option>\n";
  echo "        <option value=EvePhone $sel_ephone>Evening Phone</option>\n";
  echo "      </SELECT>\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
}

/*
 * form_submit
 *
 * Display the Submit button on a form
 */

function form_submit ($display, $colspan=2)
{
  echo "  <tr>\n";
  echo "    <td colspan=\"$colspan\" align=\"center\">\n";
  echo "    <input type=\"submit\" value=\"$display\">\n";
  echo "    </td>\n";
  echo "  </tr>\n";
}

/*
 * form_submit2
 */

function form_submit2 ($display, $display2, $name2)
{
  echo "  <tr>\n";
  echo "    <td colspan=\"2\" align=\"center\">\n";
  echo "    <input type=\"submit\" value=\"$display\">\n";
  echo "    &nbsp;&nbsp;&nbsp;\n";
  echo "    <input type=\"submit\" value=\"$display2\" name=\"$name2\">\n";
  echo "    </td>\n";
  echo "  </tr>\n";
}

/*
 * form_submit3
 */

function form_submit3 ($display, $display2, $name2, $display3, $name3)
{
  echo "  <TR>\n";
  echo "    <TD COLSPAN=2 ALIGN=CENTER>\n";
  echo "    <INPUT TYPE=SUBMIT VALUE=\"$display\">\n";
  echo "    &nbsp;&nbsp;&nbsp;\n";
  echo "    <INPUT TYPE=SUBMIT Value=\"$display2\" NAME=\"$name2\">\n";
  echo "    &nbsp;&nbsp;&nbsp;\n";
  echo "    <INPUT TYPE=SUBMIT Value=\"$display3\" NAME=\"$name3\">\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
}

function form_birth_year_and_gender ($year_key, $gender_key)
{
  $MaleChecked = 'CHECKED';
  $FemaleChecked = '';

  if (array_key_exists ($gender_key, $_POST))
  {
    if ('Female' == $_POST[$gender_key])
    {
      $MaleChecked = '';
      $FemaleChecked = 'CHECKED';
    }
  }

  if (array_key_exists ($year_key, $_POST))
    $BirthYear = $_POST[$year_key];
  else
    $BirthYear = '0';

  echo "  <tr valign=\"top\">\n";
  echo "    <TD ALIGN=RIGHT>Birth Year:</TD>\n";
  echo "    <TD ALIGN=LEFT>\n";
  echo "      <select name=\"$year_key\">\n";
  if (0 == $BirthYear)
    $sel = ' selected';
  else
    $sel = '';
  printf ("        <option value=\"0\"%s>Unspecified</option>\n", $sel);
  $y = intval (date ('Y'));
  for ($i = 1; $i < 100; $i++)
  {
    if ($y - $i == $BirthYear)
      $sel = ' selected';
    else
      $sel = '';
    printf ("        <option value=\"%d\"%s>%d</option>\n",
	    $y - $i,
	    $sel,
	    $y - $i);
  }
  echo "      </select>\n";
  echo "      &nbsp;&nbsp;&nbsp;Gender:\n";
  echo "      <INPUT TYPE=RADIO NAME=$gender_key $MaleChecked VALUE=\"Male\"> Male\n";
  echo "      <INPUT TYPE=RADIO NAME=$gender_key $FemaleChecked VALUE=\"Female\"> Female\n";
  echo "      <br>\n";
  echo "      <b>Note:</b> Specifying your birth year is optional. But if\n";
  echo "      you choose not to, you may not be able to sign up for\n";
  echo "      age-restricted games.\n";
  echo "      <br>&nbsp;\n";

  echo "    </TD>\n";
  echo "  </TR>\n";
}

/*
 * form_track_day
 *
 * Add a single line for Track and the Day radio buttons to the form
 */

function form_track_day ($track_display, $day_display,
			 $track_name=0, $day_name=0)
{
  if (0 == $track_name)
    $track_name = $track_display;

  if (0 == $day_name)
    $day_name = $day_display;

  if (array_key_exists ($day_name, $_POST))
    $day = $_POST[$day_name];
  else
    $day = 'Sat';

  $FriChecked = '';
  $SatChecked = '';
  $SunChecked = '';

  switch ($day             )
  {
    case 'Fri': $FriChecked = 'CHECKED'; break;
    case 'Sun': $SunChecked = 'CHECKED'; break;
    default:    $SatChecked = 'CHECKED'; break;
  }

  if (array_key_exists ($track_name, $_POST))
    $track_text = $_POST[$track_name];
  else
    $track_text = '';

  echo "  <TR>\n";
  echo "    <TD ALIGN=RIGHT>$track_display:</TD>\n";
  echo "    <TD ALIGN=LEFT>\n";
  printf ("      <INPUT TYPE=TEXT NAME=%s SIZE=3 MAXLENGTH=3 VALUE=\"%s\">\n",
	  $track_name,
	  htmlspecialchars ($track_text));
  echo "      &nbsp;&nbsp;&nbsp;$day_display:\n";
  echo "      <INPUT TYPE=RADIO NAME=$day_name VALUE=\"Fri\" $FriChecked> Friday\n";
  echo "      <INPUT TYPE=RADIO NAME=$day_name VALUE=\"Sat\" $SatChecked> Saturday\n";
  echo "      <INPUT TYPE=RADIO NAME=$day_name VALUE=\"Sun\" $SunChecked> Sunday\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
}

/*
 * form_day
 *
 * Display the day as a set of radio buttons
 */

function form_day ($display, $key=0)
{
  if (0 == $key)
    $key = $display;

  $value = 'Sat';
  if (array_key_exists ($key, $_POST))
    $value = $_POST[$key];

  $FriChecked = '';
  $SunChecked = '';
  $SatChecked = '';

  switch ($value)
  {
    case 'Fri': $FriChecked = 'CHECKED'; break;
    case 'Sun': $SunChecked = 'CHECKED'; break;
    default:    $SatChecked = 'CHECKED'; break;
  }

  echo "  <TR>\n";
  echo "    <TD ALIGN=RIGHT>$display:</TD>\n";
  echo "    <TD>\n";
  echo "      <INPUT TYPE=RADIO NAME=Day VALUE=\"Fri\" $FriChecked> Friday\n";
  echo "      <INPUT TYPE=RADIO NAME=Day VALUE=\"Sat\" $SatChecked> Saturday\n";
  echo "      <INPUT TYPE=RADIO NAME=Day VALUE=\"Sun\" $SunChecked> Sunday\n";
  echo "    </TD>\n";
  echo "  </TR>\n";
}

/*
 * form_start_hour
 *
 * Display the start hour as a drop-down list.  Start hours run from 8AM to
 * 7AM, calling out Noon and Midnight
 */

function form_start_hour ($display, $key)
{
  echo "  <TR>\n";
  echo "    <TD ALIGN=RIGHT>$display:</TD>\n";
  echo "    <TD>\n";
  echo "      <SELECT NAME=$key SIZE=1>\n";
  for ($t = 8; $t < 8 + 23; $t++)
  {
    // Convert the hour into a string with AM/PM notation, and calling
    // out noon and midnight

    $v = start_hour_to_am_pm ($t);

    $sel = '';
    if (array_key_exists ($key, $_POST))
    {
      if ($t == $_POST[$key])
	$sel = 'SELECTED';
    }

    echo "        <option value=$t $sel>$v</option>\n";
  }
  echo "      </SELECT>\n";
  echo "    </TD>\n";
  echo "  </TR>   \n";
}

/*
 * display_text_info
 *
 * Display text in a two column form
 */

function display_text_info ($display, $value)
{
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD ALIGN=RIGHT>$display:</TD><TD ALIGN=LEFT>$value</TD>\n";
  echo "  </TR>\n";
}

/*
 * display_text_entry
 *
 * Left here until all pages convert to form_text
 */

function display_text_entry ($size, $display, $key='', $maxsize=0)
{
  form_text ($size, $display, $key, $maxsize);
}

/*
 * form_text
 *
 * Add a text input field to a 2 column form
 */

function form_text ($size, $display, $key='', $maxsize=0, $required=FALSE, $suffix='')
{
  // If not specified, fill in default values

  if ('' == $key)
    $key = $display;

  if (0 == $maxsize)
    $maxsize = $size;

  if ("" != $display)
    $display .= ":";

  // If this is a required field, make sure it has a leading '*'

  if ($required)
    $display = '<font color="red">*</font>&nbsp;' . $display;

  // If magic quotes are on, strip off the slashes

  if (! array_key_exists ($key, $_POST))
    $text = '';
  else
  {
    if (1 == get_magic_quotes_gpc())
      $text = stripslashes ($_POST[$key]);
    else
      $text = $_POST[$key];
  }

  // Spit out the HTML

  echo "  <tr>\n";
  echo "    <td align=\"right\">$display</td>\n";
  echo '    <td align="left">';
  printf ('<input type="text" name="%s" size="%d" maxlength="%d" value="%s">' .
	  "%s</td>\n",
	  $key,
	  $size,
	  $maxsize,
	  htmlspecialchars ($text),
	  $suffix);
  echo "  </tr>\n";
}

function form_password ($size, $display, $name='', $maxsize=0, $required=FALSE)
{
  // Fill in the default values for parameters that aren't passed

  if ("" == $name)
    $name = $display;

  if (0 == $maxsize)
    $maxsize = $size;

  if ('' != $display)
    $display .= ":";

  // If this is a required field, add a leading '*' to let the user know

  if ($required)
    $display = '<FONT COLOR=RED>*</FONT> ' . $display;

  if (array_key_exists ($name, $_POST))
    $value = $_POST[$name];
  else
    $value = '';

  // Spit out the HTML

  print ("  <TR>\n");
  printf ("    <TD ALIGN=RIGHT>%s</TD>\n", $display);
  printf ("    <TD ALIGN=LEFT><INPUT TYPE=PASSWORD NAME=%s SIZE=%d MAXLENGTH=%d VALUE=\"%s\"></TD>\n",
	  $name,
	  $size,
	  $maxsize,
	  $value);
  print ("  </TR>\n");
}

function dump_str ($s, $desc='')
{
  echo "<P>$desc\n";
  for ($i = 0; $i < strlen($s); $i++)
  {
    $c = substr ($s, $i, 1);
    $o = ord($c);

    switch ($o)
      {
      case 0: $c = 'NUL'; break;
      case 1: $c = 'SOH'; break;
      case 2: $c = 'STX'; break;
      case 3: $c = 'ETX'; break;
      case 4: $c = 'EOT'; break;
      case 5: $c = 'ENQ'; break;
      case 6: $c = 'ACK'; break;
      case 7: $c = 'BEL'; break;
      case 8: $c = 'BS'; break;
      case 9: $c = 'HT'; break;
      case 10: $c = 'LF'; break;
      case 11: $c = 'VT'; break;
      case 12: $c = 'FF'; break;
      case 13: $c = 'CR'; break;
      case 14: $c = 'SO'; break;
      case 15: $c = 'SI'; break;
      case 16: $c = 'DLE'; break;
      case 17: $c = 'DC1'; break;
      case 18: $c = 'DC2'; break;
      case 19: $c = 'DC3'; break;
      case 20: $c = 'DC4'; break;
      case 21: $c = 'NAK'; break;
      case 22: $c = 'SYN'; break;
      case 23: $c = 'ETB'; break;
      case 24: $c = 'CAN'; break;
      case 25: $c = 'EM'; break;
      case 26: $c = 'SUB'; break;
      case 27: $c = 'ESC'; break;
      case 28: $c = 'FS'; break;
      case 29: $c = 'GS'; break;
      case 30: $c = 'RS'; break;
      case 31: $c = 'US'; break;
      default: $c = "'$c'"; break;
      }

    echo " $c";
  }
}

function build_sql_string ($column, $value='',
			   $prefix_comma=true,
			   $format_paragraphs=false)
{
  //  echo "1: $column: $value<br>\n";

  if ('' == $value)
  {
    if (array_key_exists ($column, $_POST))
      $value = trim ($_POST[$column]);
    else
      $value = '';
  }

  //  echo "2: $column: $value<br>\n";

  if ($format_paragraphs)
  {
    // Convert Windows line ends to Unix line ends

    $value = str_replace ("\r\n", "\n", $value);

    // Convert Mac line ends to Unix line ends

    $value = str_replace ("\r", "\n", $value);

    // Finally!  Convert two line ends to a paragraph tag

    $value = str_replace ("\n\n", "\n<P>\n", $value);

    //    echo "2.5: $column: $value<br>\n";
  }

  if ($prefix_comma)
    $res = ', ';
  else
    $res = '';

  if (1 == get_magic_quotes_gpc())
    $res .= $column . "='$value'";
  else
    $res .= $column . '="' . mysql_real_escape_string ($value) . '"';

  //  echo "3: $res<p>\n";

  return $res;
}

function sql_string_updated_by ($field='', $user=0, $comma_prefix=true)
{
  if ('' == $field)
    $field = 'UpdatedById';

  if (0 == $user)
    $user = $_SESSION[SESSION_LOGIN_USER_ID];

  $prefix = '';
  if ($comma_prefix)
    $prefix = ',';

  return "$prefix$field=$user";
}

/*
 * display_header
 *
 * Display a header
 */

function display_header ($msg)
{
  echo "<font size=\"+1\"><b>$msg</b></font><br>\n";
}

/*
 * display_error
 *
 * Display an error to the user in a red font.
 */

function display_error ($msg)
{
  echo "<FONT COLOR=RED>$msg</FONT><P>\n";
  return false;
}

/*
 * display_mysql_error
 *
 * Display a error accessing the database, including the error from MySQL
 */

function display_mysql_error ($msg, $sql=0)
{
  if (is_string ($sql))
    echo "$sql<p>\n";

  return display_error ($msg . ': ' . mysql_error());
}

/*
 * is_valid_email_address
 *
 * Validate an EMail address
 */

function is_valid_email_address ($key='EMail', $required=TRUE)
{
  $value = trim ($_POST[$key]);

  if ('' == $value)
  {
    if (! $required)
      return TRUE;
    else
      return FALSE;
  }

  // Make sure this lookes like an EMail address

  $pattern =  "^" .                             // Start at BOL  
              "[a-z0-9]+([_\\.-][a-z0-9]+)*" .  //user
              "@" .
              "([a-z0-9]+([\.-][a-z0-9]+)*)+" . //domain
              "\\.[a-z]{2,}" .                  //sld, tld 
              "$";

  return eregi ($pattern, $value);
}

/*
 * validate_email
 *
 * Validate an EMail address
 */

function validate_email ($key='EMail', $display='EMail', $required=TRUE)
{
  if ('' == $display)
    $display = $key;

  // Make sure this lookes like an EMail address

  if (is_valid_email_address ($key, $required))
    return TRUE;

  $v = trim ($_POST[$key]);
  return display_error ("'$v' does not appear to be a valid EMail address");
}

/*
 * validate_string
 *
 * Checks that a string is present (and not empty)
 */

function validate_string ($key, $display='', $required=TRUE)
{
  if ('' == $display)
    $display = $key;

  $value = trim ($_POST[$key]);

  if ('' != $value)
    return TRUE;

  if (! $required)
    return TRUE;

  return display_error ("You must specify $display");
}

/*
 * validate_day_time
 *
 * Validate that the given time is valid on a given day
 */

function validate_day_time ($time_key, $day_key, $time_display=0, $day_display=0)
{
  if (0 == $day_display)
    $day_display = $day_key;

  $day = trim ($_POST[$day_key]);
  $time = intval (trim ($_REQUEST[$time_key]));

  switch ($day)
  {
    case 'Fri':
    {
      $min_hour = FRI_MIN;
      $max_hour = FRI_MAX;
      break;
    }

    case 'Sat':
    {
      $min_hour = SAT_MIN;
      $max_hour = SAT_MAX;
      break;
    }

    case 'Sun':
    {
      $min_hour = SUN_MIN;
      $max_hour = SUN_MAX;
      break;
    }

    default:
      return display_error ("Invalid day specified for $day_display");
  }

  if (($time >= $min_hour) && ($time <= $max_hour))
    return true;

  $min_hour_text = start_hour_to_am_pm ($min_hour);
  $max_hour_text = start_hour_to_am_pm ($max_hour);

  return display_error ("Invalid start time specified for $day." .
			"Valid times are $min_hour_text to $max_hour_text.");
}

function start_hour_to_am_pm ($t)
{
  // Start times go from 8 to 31, which correspond to 8AM to 7AM 'that night'
  // Noon and Midnight are handled specially

  $h = $t % 24;

  if (12 == $h)
    return 'Noon';

  if (0 == $h)
    return 'Midnight';

  if ($h < 12)
    return $h . ' AM';
  else
    return $h - 12 . ' PM';
}

/*
 * start_hour_to_24_hour
 *
 * Start hours go from 8 to 31, which correspond to 08:00 to 07:00 'that night'
 */

function start_hour_to_24_hour ($t)
{
  return sprintf ('%02d:00', $t % 24);
}

function validate_int ($key, $min, $max, $display=0)
{
  if (0 == $display)
    $display = $key;

  $value = trim ($_POST[$key]);
  if (! is_numeric ($value))
    return display_error ("You must specify a number between $min and $max for $display");

  if ($value < $min)
    return display_error ("$display must be greater than $min");

  if ($value > $max)
    return display_error ("$display must be less than $max");

  return TRUE;
}

/*
 * can_signup
 *
 // Check the database for whether the user can
 // signup.  This will be true if the user has paid, been comp'd or whatever.
 //
 // Note that we're NOT checking whether the user is over his limit here.
 // That will be checked when they attempt to signup for this game, and
 // and error message displayed if the signup would push them over their
 // limit.  I'd tried checking it here, and users complained that they
 // didn't understand why the links went away.
 //
 // GMs signing up for thier games are also checked (and allowed) at that
 // time.  HOWEVER, it's assumed that if they get to that point, that
 // we've already checked that they've paid
 */

function can_signup () {
    if (!is_logged_in())
        return false;
    
    if (!isset($GLOBALS['USER_CAN_SIGNUP'])) {
        $sql = 'SELECT CanSignup FROM Users WHERE UserId=';
        $sql .= $_SESSION[SESSION_LOGIN_USER_ID];
        $result = mysql_query ($sql);
        if (!$result)
            display_mysql_error ('Failed to get user information for signup check');
        if (0 == mysql_num_rows ($result))
            display_error ('Failed to find UserId ' . $_SESSION[SESSION_LOGIN_USER_ID]);

        $row = mysql_fetch_object ($result);
    
        if (is_unpaid ($row->CanSignup))
            $GLOBALS['USER_CAN_SIGNUP'] = false;
        else
            $GLOBALS['USER_CAN_SIGNUP'] = true;
    }
    
    return $GLOBALS['USER_CAN_SIGNUP'];
}

/*
 * can_show_schedule
 *
 * Query the Con table to see if the Game Schedule menu entry should
 * be shown
 */

function can_show_schedule ()
{
  // See if we've cached the setting in the session

  $now = time();

  if (isset ($_SESSION[SESSION_CON_SHOW_SCHEDULE]))
  {
    if (isset ($_SESSION[SESSION_TIMEOUT]))
    {
      if ($_SESSION[SESSION_TIMEOUT] > $now)
      {
	printf ("<!-- Within timeout - %d > %d -->\n",
		$_SESSION[SESSION_TIMEOUT],
		$now);
	return $_SESSION[SESSION_CON_SHOW_SCHEDULE];
      }
    }
  }

  // Nope.  First time in, or the timeout's expired.  Fetch it (and the
  // other con settings)

  $sql = 'SELECT SignupsAllowed, ShowSchedule, News,';
  $sql .= ' DATE_FORMAT(LastUpdated, "%b %e, %l:%i %p") AS LastUpdated';
  $sql .= ' FROM Con';

  $result = mysql_query ($sql);
  if (! $result)
  {
    echo "<!-- $sql -->\n";
    echo '<!-- Query for Con.ShowSchedule failed: ' . mysql_error () . "-->\n";
    return FALSE;
  }

  $row = mysql_fetch_object ($result);
  if (! $row)
  {
    echo "<!-- No rows fetched for Con.ShowSchedule -->\n";
    return FALSE;
  }

  $_SESSION[SESSION_TIMEOUT] = $now + 180;
  $_SESSION[SESSION_CON_LAST_UPDATED] = $row->LastUpdated;
  $_SESSION[SESSION_CON_NEWS] = $row->News;

  switch ($row->ShowSchedule)
  {
    // All users can see the schedule

    case 'Yes':
      $_SESSION[SESSION_CON_SHOW_SCHEDULE] = 1;
      break;

    // Only users with Scheduling or GM Liaison Privilege can see the schedule

    case 'Priv':
      $_SESSION[SESSION_CON_SHOW_SCHEDULE] = user_has_priv (PRIV_SCHEDULING) ||
									 user_has_priv (PRIV_GM_LIAISON);
      break;

    // GMs can see the schedule, as well as anyone with ConCom,
    // Scheduling or GM Liaison priv

    case 'GMs':
      if (user_has_priv (PRIV_SCHEDULING) ||
	  user_has_priv (PRIV_GM_LIAISON) ||
	  user_has_priv (PRIV_CON_COM))
	$_SESSION[SESSION_CON_SHOW_SCHEDULE] = 1;
      else
      {
	if (user_is_gm())
	  $_SESSION[SESSION_CON_SHOW_SCHEDULE] = 1;
	else
	  $_SESSION[SESSION_CON_SHOW_SCHEDULE] = 0;
      }
      break;

    // Nobody can see the schedule

    case 'No':
      $_SESSION[SESSION_CON_SHOW_SCHEDULE] = 0;
      break;

    // We should never get here!

    default:
      display_error ('Unexpected value for ShowSchedule');
      $_SESSION[SESSION_CON_SHOW_SCHEDULE] = 0;
  }

  //  dump_array ("SESSION", $_SESSION);

  return $_SESSION[SESSION_CON_SHOW_SCHEDULE];
}

// Get the contents of the single row in Con.  If we already did this, return the cached copy.
function get_con_settings() {
    if (!isset($GLOBALS['CACHED_CON'])) {
        $sql = 'SELECT * FROM Con';
        
        $result = mysql_query ($sql);
        if (! $result)
        {
          echo "<!-- $sql -->\n";
          echo '<!-- Query for Con.PreconBidsAllowed failed: ' . mysql_error () . "-->\n";
          return FALSE;
        }

        $row = mysql_fetch_object ($result);
        if (! $row)
        {
          echo "<!-- No rows fetched for Con.ShowSchedule -->\n";
          return FALSE;
        }
        
        $GLOBALS['CACHED_CON'] = $row;
    }
    
    return $GLOBALS['CACHED_CON'];
}

/*
 * precon_bids_allowed
 *
 * Query the Con table to see if the pre-con is accepting bids
 */

function precon_bids_allowed () {
    return get_con_settings()->PreconBidsAllowed == "Yes";
}

/*
 * accepting_bids
 *
 * Query the Con table to see if the con is accepting bids
 */

function accepting_bids () {
    return get_con_settings()->AcceptingBids == "Yes";
}

/*
 * select_user
 *
 * General function to display the list of users in the database and allow
 * the current user to select one
 */

function select_user ($header,
		      $href,
		      $show_new_user,
		      $link_highlit_users,
		      $highlight,
		      $exclude_alumni = false,
		      $show_payment = false)
{
  $priv = user_has_priv (PRIV_STAFF);

  // Get a list of first characters

  $sql = 'SELECT DISTINCT UCASE(SUBSTRING(LastName,1,1)) AS Ch';
  $sql .= '  FROM Users';
  $sql .= "  WHERE LastName<>'Admin'";
  if ($exclude_alumni)
    $sql .= ' AND CanSignup<>"Alumni"';
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

  // Get a list of all users

  $sql = 'SELECT UserId, FirstName, LastName, EMail, CanSignup, Priv,';
  $sql .= ' LastLogin, PaymentAmount';
  $sql .= '  FROM Users';
  if ($exclude_alumni)
    $sql .= ' WHERE CanSignup<>"Alumni"';
  $sql .= '  ORDER BY LastName, FirstName';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of users', $sql);

  display_header ($header);

  // Display the list of anchors

  echo "<TABLE WIDTH=\"100%\">\n";
  echo "  <TR>\n";

  foreach ($anchors as $key => $value)
  {
    if ($value)
      echo "    <TD><A HREF=\"#$key\">$key</A></TD>\n";
    else
      echo "    <TD>$key</TD>\n";
  }

  echo "  </TR>\n";
  echo "</TABLE>\n";

  if ($show_new_user)
    echo "<A HREF=$href&UserId=0>New User</A>\n";

  $ch = '';

  echo "<TABLE BORDER=0 CELLPADDING=2>\n";

  while ($row = mysql_fetch_object ($result))
  {
    // Skip the Admin account

    if ('Admin' == $row->LastName)
      continue;

    if (empty ($highlight[$row->UserId]))
      $bgcolor = '';
    else
      $bgcolor = $highlight[$row->UserId];

    // Add spacer between names starting with different letters

    if ($ch != strtoupper($row->LastName{0}))
    {
      $ch = strtoupper ($row->LastName{0});
      echo "  <TR BGCOLOR=\"#CCCCFF\"><TD COLSPAN=4><A NAME=$ch>$ch</A></TD></TR>\n";
    }

    // Display the user name for selection

    echo " <TR $bgcolor>\n";

    if (('' == $bgcolor) || $link_highlit_users)
      printf ("    <TD><A HREF=$href&UserId=%d>%s, %s</A></TD>\n",
	      $row->UserId,
	      $row->LastName,
	      $row->FirstName);
    else
      printf ("    <TD>%s, %s</TD>\n",
	      $row->LastName,
	      $row->FirstName);

    echo "    <TD><A HREF=mailto:$row->EMail>$row->EMail</A></TD>\n";

    $status = $row->CanSignup;
    switch ($status)
    {
      case 'Alumni':
	$status = 'Alumni of ' . last_login_to_con ($row->LastLogin);
        break;

      case 'Paid':
	if ($show_payment)
	  $status = sprintf ('Paid $%d', $row->PaymentAmount / 100);
	break;
    }
    echo "    <TD NOWRAP>$status</TD>\n";

    if ($priv)
    {
      $user_priv = str_replace (',', ', ', $row->Priv);
      echo "    <TD>$user_priv</TD>\n";
    }
    echo "  </TR>\n";
  }

  echo "</TABLE>\n";
}

/*
 * last_login_to_con
 *
 * Convert a last login time to a convention name
 */

function last_login_to_con ($timestamp)
{
  // If this is a newer format timestamp, the format is yyyy-mm-dd hh:mm:ss
  //                                                    0000000000111111111
  //                                                    0123456789023456789

  $year = '0000';
  $month = '00';
  $day = '00';

  if (strstr ($timestamp, '-'))
  {
    $year =       substr ($timestamp,  0, 4);
    $month =  substr ($timestamp,  5, 2);
    $day =        substr ($timestamp,  8, 2);
  }
  else
  {
    // This is a pre-4.1 timestamp.  The format is yyyymmddhhmmss
    //                                             00000000001111
    //                                             01234567890123

    $year =       substr ($timestamp,  0, 4);
    $month =  substr ($timestamp,  4, 2);
    $day =        substr ($timestamp,  6, 2);
  }

  $last_login_date = $year . $month . $day;

  // Intercon F ran 3-Mar-2006 - 5-Mar-2006

  if ($last_login_date > 20060303)
    return 'G';

  // Intercon E ran 4-Mar-2005 - 6-Mar-2005

  if ($last_login_date > 20050304)
    return 'F';

  // Intercon D ran 12-Mar-2004 - 14-Mar-2004

  if ($last_login_date > 20040312)
    return 'E';

  // Intercon C ran 28-Feb-2003 - 2-Mar-2003

  if ($last_login_date > 20030228)
    return 'D';

  // Intercon B ran 8-Mar-2002 - 11-Mar-2002

  if ($last_login_date > 20020308)
    return 'C';

  // Anything older than Intercon B in the database is considered part
  // of Intercon B

  return 'B';
}

/*
 * notify_gms_for_user
 *
 *
 */

function notify_gms_for_user ($player_name, $EMail,
			      $EventId, $Title, $Day, $StartHour,
			      $signup_result, $type, $prev_state)
{

  // Build the message we're going to send

  $msg = "$player_name has $signup_result \"$Title\" on $Day starting ";
  $msg .= "at " . start_hour_to_24_hour ($StartHour);

  if (0 != $prev_state)
  {
    $msg .= ".  The player had been $prev_state for the game.";
  }

  $subject = '[' . CON_NAME . ": $Title] $type: $player_name";

  // Fetch the list of GMs who have asked to be notified of signups and
  // withdrawals

  $sql = "SELECT Users.EMail FROM Users, GMs";
  $sql .= "  WHERE GMs.EventId=$EventId";
  $sql .= "    AND GMs.ReceiveSignupEMail='Y'";
  $sql .= '    AND Users.UserId=GMs.UserId';
  $result = mysql_query ($sql);
  if (!$result)
  {
    echo "SQL: $sql<p>\n";
    return display_mysql_error ('Failed to get list of GMs requesting notification');
  }

  //  echo "SQL: $sql<p>\n";
  //  printf ("Sending mail to %d GMs\n", mysql_num_rows ($result));

  while ($row = mysql_fetch_object ($result))
  {
    $send_to = $row->EMail;

    //    echo "Sending mail to $row->EMail<P>\n";

    if (! intercon_mail ($send_to, $subject, $msg))
      return display_error ('Attempt to send mail failed');
  }
}

/*
 * notify_gms
 *
 * Notify any GMs that have requested it that a user has signed up, been
 * wait listed or withdrawn from a game
 */

function notify_gms ($EventId, $Title, $Day, $StartHour, $signup_result,
		     $type, $prev_state = 0)
{
  // Fetch the user name and EMail address from the database

  $sql = 'SELECT FirstName, LastName, EMail FROM Users WHERE UserId=';
  $sql .= $_SESSION[SESSION_LOGIN_USER_ID];
  $result = mysql_query ($sql);
  if (!$result)
    display_mysql_error ('Failed to get user information for GM notification');
  if (0 == mysql_num_rows ($result))
    display_error ('Failed to find UserId ' . $_SESSION[SESSION_LOGIN_USER_ID]);

  $row = mysql_fetch_object ($result);

  $user_name = trim ("$row->FirstName $row->LastName");
  $user_email = $row->EMail;

  notify_gms_for_user (trim ("$row->FirstName $row->LastName"), 
		       $row->EMail,
		       $EventId,
		       $Title,
		       $Day,
		       $StartHour,
		       $signup_result,
		       $type,
		       $prev_state);
}

/*
 * accept_players_from_waitlist_for_run
 *
 * Iterate over the waitlist, if there is one, and accept users into the game
 * in order.  This function expects to run with the following tables locked:
 *     Signup - Write locked
 *     Users, Runs, Events, GMs, - Read locked
 *
 * The locking prevents another user from slipping in while we're scanning
 * the waitlist for this game for a player who's been waiting.  The locks
 * will be released after the function completes
 */

function accept_players_from_waitlist_for_run ($EventId,
					       $SrcRunId,
					       $DstRunId,
					       $run_title,
					       $Day,
					       $StartHour,
					       $Hours,
					       $CanPlayConcurrently,
					       $delta_male,
					       $delta_female,
					       $delta_neutral)
{
  $waitlisted = array ();

  $male = max ($delta_male, 0);
  $female = max ($delta_female, 0);
  $neutral = max ($delta_neutral, 0);

  // echo "Male: $delta_male, Female: $delta_female, Neutral: $delta_neutral<BR>\n";

  $sql = 'SELECT Users.UserId, Users.FirstName, Users.LastName, Users.EMail,';
  $sql .= ' Signup.Counted, Signup.SignupId, Signup.Gender';
  $sql .= ' FROM Signup, Users';
  $sql .= " WHERE Signup.RunId=$SrcRunId";
  $sql .= "   AND Signup.State='Waitlisted'";
  $sql .= '   AND Users.UserId=Signup.UserId';
  $sql .= ' ORDER BY Signup.SignupId';

  $wait_result = mysql_query ($sql);
  if (! $wait_result)
    return display_mysql_error ("Query for users on waitlist for run $row->RunId");
  while ((($male > 0) || ($female > 0) || ($neutral > 0)) &&
	 $wait_row = mysql_fetch_object ($wait_result))
  {
    // echo "Considering $wait_row->FirstName $wait_row->LastName, $wait_row->Gender<BR>\n";

    $accept_user = false;

    if ($neutral > 0)
      $accept_user = true;
    else
    {
      if ('Male' == $wait_row->Gender)
	$accept_user = ($male > 0);
      else
	$accept_user = ($female > 0);
    }

    if ($accept_user)
    {
      $name = trim ("$wait_row->FirstName $wait_row->LastName");
      // echo "Accepting $wait_row->FirstName $wait_row->LastName, $wait_row->Gender<BR>\n";
      if (confirm_waitlisted_user ($wait_row->SignupId,
				   $DstRunId,
				   $wait_row->UserId,
				   stripslashes ($name),
				   $wait_row->EMail,
				   $wait_row->Gender,
				   $run_title,
				   $Day,
				   $StartHour,
				   $EventId,
				   $Hours,
				   $CanPlayConcurrently))
      {
	if ('Male' == $wait_row->Gender)
	{
	  if ($male > 0)
	    $male--;
	  else
	    $neutral--;
	}
	else
	{
	  if ($female > 0)
	    $female--;
	  else
	    $neutral--;
	}
      }
    }
  }
}

/*
 * confirm_waitlisted_user
 *
 * Confirm a waitlisted user for a game
 */

function confirm_waitlisted_user ($SignupId,
				  $RunId,
				  $UserId,
				  $player_name,
				  $EMail,
				  $Gender,
				  $run_title,
				  $Day,
				  $StartHour,
				  $EventId,
				  $Hours,
				  $CanPlayConcurrently)
{
  //  echo "$player_name accepted from waitlist<BR>\n";

  $start_hour = $StartHour;
  $end_hour = $start_hour + $Hours;

  $EndTime = sprintf ('%02d:00', $start_hour + $Hours);

  //  echo "StartHour: $start_hour, EndTime: $EndTime ($end_hour)<p>\n";

  // Scan for any conflict games for this user

  $sql = 'SELECT Signup.SignupId, Signup.State,';
  $sql .= ' Runs.StartHour, Runs.TitleSuffix,';
  $sql .= ' Events.EventId, Events.Hours, Events.Title';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= " WHERE Signup.UserId=$UserId";
  $sql .= '   AND Signup.State!="Withdrawn"';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= "   AND Runs.Day='$Day'";
  $sql .= "   AND Runs.StartHour<$end_hour";
  $sql .= "   AND Runs.EventId!=$EventId";        // Ignore this game!
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= '   AND Events.CanPlayConcurrently="N"';

  //  echo "$player_name:  $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for conflicting games for $player_name failed", $sql);

  $withdrawn_from_games = '';

  while ($row = mysql_fetch_object ($result))
  {
    $row_start_hour = $row->StartHour;
    $row_end_hour = $row_start_hour + $row->Hours;
/*
    echo "Checking <i>$row->Title</I><BR>\n";
    echo "State: $row->State<br>\n";
    echo "row_start_hour: $row_start_hour<br>\n";
    echo "row_end_hour: $row_end_hour<br>\n";
*/
    // If this game doesn't conflict, ignore it

    if ($end_hour <= $row_start_hour)
    {
      //      echo "Ignored - Starts after end of the game<P>\n";
      continue;
    }

    if ($start_hour >= $row_end_hour)
    {
      //      echo "Ignored - Ends before start of the game<P>\n";
      continue;
    }

    if ('Waitlisted' != $row->State)
      return display_error ("Found conflicting $row->State game for $player_name!");

    //    echo "Conflict Found!<P>\n";

    $sql = 'UPDATE Signup SET PrevState=State,';
    $sql .= ' State="Withdrawn",';
    $sql .= ' UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= " WHERE SignupId=$row->SignupId";

    $withdraw_request = mysql_query ($sql);
    if (! $withdraw_request)
      return display_error ("Failed to update signup table");

    // Notify the GMs for this game

    notify_gms_for_user ($player_name, $EMail,
			 $row->EventId, $row->Title, $row->Day, $row->StartHour,
			 'withdrawn from',
			 'Withdrawal',
			 $row->State);

    $withdrawn_from_games .= "- $row->Title $row->TitleSuffix\n";
  }

  // Now confirm the user for this game

  $sql = 'UPDATE Signup SET PrevState=State,';
  $sql .= ' State="Confirmed",';
  $sql .= " RunId=$RunId,";
  $sql .= ' UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= " WHERE SignupId=$SignupId";

  $request = mysql_query ($sql);
  if (! $request)
    return display_mysql_error ("Failed to update signup table for $SignupId");

  notify_gms_for_user ($player_name, $EMail,
		       $EventId, $run_title, $Day, $start_hour,
		       'signedup for', 'Signup',
		       'waitlisted');

  // Now send mail to the user telling him that he's been accepted
  // Build the headers and message we're going to send

  $msg = "A slot has opened in \"$run_title\" on $Day starting ";
  $msg .= "at " . start_hour_to_24_hour ($start_hour);
  $msg .= '.  You have been removed from the waitlist and signed up for';
  $msg .= " the game.\n";

  if ('' != $withdrawn_from_games)
  {
    $msg .= "\nAs a result, you have been removed from the waitlist for the";
    $msg .= " following games which conflicted with \"$run_title\":\n";
    $msg .= stripslashes ($withdrawn_from_games);
  }

  $subject = '[' . CON_NAME . ": Signup Notification] $run_title";

  if (! intercon_mail ($EMail, $subject, $msg))
    display_error ("Attempt to send mail to $EMail failed");

  return true;
}

/*
 * withdraw_from_game_locked
 *
 * This function encapsulates most of the functionality needed to withdraw
 * a user from the game.  This function expects to run with the following
 * tables locked:
 *     Signup - Write locked
 *     Users, Runs, Events, GMs, - Read locked
 *
 * The locking prevents another user from slipping in while we're scanning
 * the waitlist for this game for a player who's been waiting.  The locks
 * will be released after the function completes
 */

function withdraw_from_game_locked ($SignupId, $name, $email,
				    $prev_state, $gender, $counted)
{
  // Mark the user's signup record as withdrawn.  This effectively
  // withdraws the user from the game

  $sql = 'UPDATE Signup SET PrevState=State,';
  $sql .= ' State="Withdrawn",';
  $sql .= ' UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= ' WHERE SignupId=' . $SignupId;

  //  echo $sql . "<p>\n";

  $request = mysql_query ($sql);
  if (! $request)
    return display_mysql_error ('Failed to update signup record');

  // Fetch game information for the game the user is withdrawing from

  $sql = 'SELECT Events.EventId, Events.Title, Events.CanPlayConcurrently,';
  $sql .= '  Events.MaxPlayersNeutral, Events.MaxPlayersMale,';
  $sql .= '  Events.MaxPlayersFemale, Events.Hours,';
  $sql .= '  Runs.RunId, Runs.Day, Runs.StartHour, Runs.TitleSuffix';
  $sql .= '  FROM Events, Runs, Signup';
  $sql .= "  WHERE Signup.SignupId=$SignupId";
  $sql .= '    AND Runs.RunId=Signup.RunId';
  $sql .= '    AND Events.EventId=Runs.EventId';

  //  echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query failed for event info for GM notification');
  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ("Failed to find info for signup $SignupId");

  $run_title = stripslashes (trim ("$row->Title $row->TitleSuffix"));

  printf ("%s withdrawn from <I>%s</I> on %s starting at %s<P>\n",
	  $name,
	  $run_title,
	  $row->Day,
	  start_hour_to_24_hour ($row->StartHour));

  // Notify the GMs that the user has withdrawn

  notify_gms_for_user ($name, $email,
		       $row->EventId, $run_title, $row->Day, $row->StartHour,
		       'withdrawn from',
		       'Withdrawal',
		       $prev_state);

  // If this wasn't a confirmed player, we're done.

  if ('confirmed' != $prev_state)
    return true;

  // If the player didn't count toward the totals, we're done

  //  echo "<P>Counted: $counted<P>\n";

  if ('Y' != $counted)
    return true;

  // Figure out the gender of the slot we've opened

  $male_slots = 0;
  $female_slots = 0;
  $neutral_slots = 0;

  calculate_available_slot ($row->RunId, $gender,
			    $row->MaxPlayersMale, $row->MaxPlayersFemale,
			    $row->MaxPlayersNeutral,
			    $male_slots, $female_slots, $neutral_slots);

  // Accept a player from the waitlist for this game, if there are any

  accept_players_from_waitlist_for_run ($row->EventId,
					$row->RunId, $row->RunId,
					$run_title, $row->Day, $row->StartHour,
					$row->Hours, $row->CanPlayConcurrently,
					$male_slots, $female_slots, $neutral_slots);
}

/*
 * calculate_available_slot
 *
 * Given that a player has withdrawn, figure out what gender (male, female
 * or neutral) slot is available
 */

function calculate_available_slot ($run_id, $gender,
				   $max_males, $max_females, $max_neutrals,
				   &$male_slots, &$female_slots,
				   &$neutral_slots)
{
  // If there are no neutral slots, then the player selected from the
  // waitlist must be the same gender as the player who's withdrawing

  if (0 == $max_neutrals)
  {
    if ('Male' == $gender)
      $male_slots = 1;
    else
      $female_slots = 1;

    return true;
  }

  // Get the count of players already signed up by gender

  $sql = 'SELECT Gender, COUNT(Gender) AS Count';
  $sql .= ' FROM Signup';
  $sql .= " WHERE RunId=$run_id";
  $sql .= "   AND State='Confirmed'";
  $sql .= "   AND Counted='Y'";
  $sql .= ' GROUP BY Gender';

  $confirmed_males = 0;
  $confirmed_females = 0;

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to count number of players', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if ('Male' == $row->Gender)
      $confirmed_males = $row->Count;
    else
      $confirmed_females = $row->Count;
  }

  // Calculate whether we can accept either a male or female, or have to
  // accept a specific gender

  $avail_neutrals = $max_neutrals;

  if ($max_males > $confirmed_males)
    $avail_males = $max_males - $confirmed_males;
  else
  {
    $avail_males = 0;
    $avail_neutrals -= $confirmed_males - $max_males;
  }

  if ($max_females > $confirmed_females)
    $avail_females = $max_females - $confirmed_females;
  else
  {
    $avail_females = 0;
    $avail_neutrals -= $confirmed_females - $max_females;
  }

  if ($avail_neutrals > 0)
  {
    $neutral_slots = 1;
    return true;
  }

  if ($avail_males > 0)
    $male_slots = 1;
  else
    $female_slots = 1;
  
  return true;
}

/*
 * withdraw_from_game
 *
 * Process a request to withdraw from a game
 */

function withdraw_from_game ($SignupId = 0)
{
  if (0 == $SignupId)
  {
    // Check for a sequence error

    if (out_of_sequence ())
      return display_sequence_error (false);

    $SignupId = intval (trim ($_REQUEST['SignupId']));

    // Make sure that this is the user or a staff member

    if (! user_has_priv (PRIV_STAFF))
    {
	$sql = "SELECT UserId FROM Signup WHERE SignupId=$SignupId";

	$result = mysql_query ($sql);
	if (! $result)
	  return display_mysql_error ('Signup table query failed');

	$row = mysql_fetch_object ($result);
	if (! $row)
	  return display_error ("Failed to find info for signup $SignupId");

	if ($row->UserId != $_SESSION[SESSION_LOGIN_USER_ID])
	  return display_error ('You are not allowed to withdraw from a game for another user.');
    }
  }

  if (!con_signups_allowed()) {
      return display_error("Game signups and withdrawals are not allowed at this time.");
  }

  $sql = 'SELECT Signup.State, Signup.Counted, Signup.Gender,';
  $sql .= ' Users.FirstName, Users.LastName, Users.EMail';
  $sql .= ' FROM Signup, Users';
  $sql .= " WHERE SignupId=$SignupId";
  $sql .= '   AND Users.UserId=Signup.UserId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for current signup state failed', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('Failed to find signup to check previous state');

  $prev_state = strtolower ($row->State);
  $name = trim ("$row->FirstName $row->LastName");

  // Lock the Signup table to make sure that if there are two users trying
  // to get the last slot in a game, then only one will succeed.  A READ lock
  // allows clients that only read the table to continue, but will block
  // clients that attempt to write to the table

  $result = mysql_query ('LOCK TABLE Signup WRITE, Users READ, Runs READ, Events READ, GMs READ');
  if (! $result)
    return display_mysql_error ('Failed to lock the Signup table');

  withdraw_from_game_locked ($SignupId,
			     $name,
			     $row->EMail,
			     $prev_state,
			     $row->Gender,
			     $row->Counted);

  // Unlock the Signup table so that other queries can access it

  $result = mysql_query ('UNLOCK TABLES');
  if (! $result)
    return display_mysql_error ('Failed to unlock the Signup table');

  return TRUE;
}

/*
 * confirm_withdraw_from_game
 *
 * Have the user confirm that he (or she) really did intend to withdraw
 * from the game
 */

function confirm_withdraw_from_game ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (false);

  $SignupId = intval (trim ($_REQUEST['SignupId']));

  $sql = 'SELECT Events.Title, Events.Hours, Runs.Day, Runs.StartHour, ';
  $sql .= ' Signup.State';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= " WHERE Signup.SignupId=$SignupId";
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for withdrawal information failed',
				$sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('Failed to find signup record');

  $start_time = start_hour_to_24_hour ($row->StartHour);
  $end_time = start_hour_to_24_hour ($row->StartHour + $row->Hours);

  echo "<H1>Confirm Withdrawal From Game</H1>\n";
  echo "<FORM METHOD=POST ACTION=index.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n",
	  WITHDRAW_FROM_GAME_CONFIRMED);
  printf ("<INPUT TYPE=HIDDEN NAME=SignupId VALUE=%d>\n",
	  $SignupId);
  echo "Are you sure that you want to withdraw from <I>$row->Title</I>\n";
  echo "being played on <NOBR>$row->Day, $start_time - $end_time?</NOBR><p>\n";
  echo "If you withdraw ";
  if ('Waitlisted' == $row->State)
    echo "you will lose your place in the waitlist.\n";
  else
  {
    echo "and the game is full, you may not be able get into the game\n";
    echo "at a future time.<p>\n";
  }
  echo "<CENTER><INPUT TYPE=SUBMIT VALUE=\"Confirm Withdrawal\"></CENTER>\n";
  echo "</FORM>\n";

  return true;
}

function display_available_games ()
{
  // Fetch whether signups are allowed.  The they're not, or they're
  // restricted, then we're done

  $result = mysql_query ('SELECT SignupsAllowed FROM Con');
  if (! $result)
    return display_mysql_error ('Failed to get SignupsAllowed');

  $row = mysql_fetch_object ($result);
  if ("Yes" != $row->SignupsAllowed)
    return;

  // Get the max players for each game

  $sql = 'SELECT EventId,';
  $sql .= ' MaxPlayersMale + MaxPlayersFemale + MaxPlayersNeutral AS Players';
  $sql .= '  FROM Events';
  $sql .= '  WHERE SpecialEvent=0';
  $sql .= '    AND IsOps="N"';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get player count', $sql);

  $max_players = array ();
  while ($row = mysql_fetch_object ($result))
  {
    $max_players[$row->EventId] = $row->Players;
  }

//dump_array ('max_players', $max_players);

  // OK, now get the runs with the number of players signed up for each

  $sql = 'SELECT COUNT(*) AS Count,';
  $sql .= ' Events.EventId, Events.Title, Runs.Day, Runs.StartHour';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= ' WHERE Signup.State="Confirmed"';
  $sql .= '   AND Signup.Counted="Y"';
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= '   AND Events.IsOps="N"';
  $sql .= '   AND Events.IsConSuite="N"';
  $sql .= '   AND Events.SpecialEvent=0';
  $sql .= ' GROUP BY Signup.RunId';
  $sql .= ' ORDER BY Runs.Day, Runs.StartHour';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get signedup counts', $sql);

  $header_shown = false;

  while ($row = mysql_fetch_object ($result))
  {
//echo "<!-- EventId: $row->EventId, Count: $row->Count, Title: $row->Title -->\n";

    if ($row->Count < $max_players[$row->EventId])
    {
      if (! $header_shown)
      {
	$header_shown = true;
	echo "<p><b>There are still openings in these great games!</b><br>\n";
      }

      printf ("&#149;&nbsp;&nbsp;%s - %s&nbsp;%s<br>\n",
	      $row->Title,
	      $row->Day,
	      start_hour_to_24_hour ($row->StartHour));
    }
  }
  echo "<br>\n";
}

/*
 * display_login_form
 *
 * Display the login form for a user
 */

function display_login_form ($error='', $dest='')
{
  if (('' == $dest) && (array_key_exists ('dest', $_REQUEST)))
    $dest = $_REQUEST['dest'];

  //  echo "dest: $dest\n";

  if ('' == $dest)
  {
  	if (file_exists(TEXT_DIR.'/logintop.html'))
		include(TEXT_DIR.'/logintop.html');	
  }

  // If there's an error from a prior screen, display it now

  if ('' != $error)
  {
    echo "<P>\n";
    display_error ($error);
  }

  // Build the login form

  echo "<form method=POST action=\"index.php\">\n";
  form_add_sequence ();
  form_hidden_value('action', LOGIN_USER);
  form_hidden_value('dest', $dest);
  echo "<table border=\"0\">\n";
  form_text (64, 'EMail');
  
  // Yeah, yeah, I copied and pasted this from form_password.  I wanted to add a "forgot your password?"
  // link and didn't want to break other pages that use the function. - Nat
  print ("  <TR>\n");
  printf ("    <TD ALIGN=RIGHT>Password:</TD>\n");
  printf ("    <TD ALIGN=LEFT><INPUT TYPE=PASSWORD NAME=%s SIZE=%d MAXLENGTH=%d>\n",
	  "Password", 30, 30);
  echo "<a href=\"index.php?action=".REQUEST_PASSWORD."\" style=\"font-style: italic; font-size: 90%\">Forgot your password?</a>";
  echo "</TD>\n";
  print ("  </TR>\n");
  form_submit("Login");
  echo "</table>\n";
  echo "</form>\n";
  
  

  if ('' != $dest)
    return false;

  // Show a list of available games

  display_available_games ();

  if (file_exists(TEXT_DIR.'/loginbottom.html'))
	include(TEXT_DIR.'/loginbottom.html');	
}

/*
 * display_access_error
 *
 * Common access error routine, so all access errors look the same
 */

function display_access_error ()
{
  if (is_logged_in())
    return display_error ('You are not allowed to access this page');

  //  dump_array ('SERVER', $_SERVER);

  return display_login_form('You must be logged in to access this page',
			    $_SERVER['REQUEST_URI']);
}

/*
 * increment_sequence_number
 *
 * Sequence numbers are used to work around the "Back Key Problem".  That is,
 * the user can click the Back Key on their browser, and return to a page that
 * has modified the database in some way.  Typically this will be in response
 * to a form submission, but doesn't have to be.
 *
 * Anyway, since the database has changed in some way, this can cause problems,
 * including database corruption.  So some way is need to detect it.  This
 * solution is to have a monotonically increasing sequence number in the
 * session data, and a sequence number encoded in the POST/GET data.  If the
 * two sequence numbers don't match, the update is ignored.
 *
 * Bump the sequence number and return it.
 */

function increment_sequence_number ()
{
  if (! array_key_exists (SESSION_SEQUENCE_NUMBER, $_SESSION))
  {
    $_SESSION[SESSION_SEQUENCE_NUMBER] = 0;
  }

  return ++$_SESSION[SESSION_SEQUENCE_NUMBER];
}

function form_add_sequence ($n = 0)
{
  if (0 == $n)
    $n = increment_sequence_number();

  form_hidden_value ('Seq', $n);

  //  echo 'Hidden Seq: ' . $_SESSION[SESSION_SEQUENCE_NUMBER] . "<P>\n";
}

function out_of_sequence ()
{
  $seq = intval (trim ($_REQUEST['Seq']));
  /*
  printf ("Form Sequence: %d, Session Sequence: %d<p>\n",
	  $seq,
	  $_SESSION[SESSION_SEQUENCE_NUMBER]);
  */
  return $seq != $_SESSION[SESSION_SEQUENCE_NUMBER]++;
}

function in_sequence ()
{
  $seq = intval (trim ($_REQUEST['Seq']));
  return $seq == $_SESSION[SESSION_SEQUENCE_NUMBER]++;
}

function display_sequence_error ($result)
{
  display_error ('Sequence error.  Did you use the Back Button?');
  return $result;
}

function display_valid_start_times ()
{
  echo "<P>\n";
  echo "<TABLE>\n";
  echo "  <TR><TD COLSPAN=2 ALIGN=CENTER><B>Valid Start Times</B></TD></TR>\n";
  start_time_table_row ('Friday',   FRI_MIN, FRI_MAX);
  start_time_table_row ('Saturday', SAT_MIN, SAT_MAX);
  start_time_table_row ('Sunday',   SUN_MIN, SUN_MAX);
  echo "</TABLE>\n";
}

function start_time_table_row ($day, $min, $max)
{
  echo "  <TR><TD>$day&nbsp;</TD><TD>" . start_hour_to_am_pm($min);
  echo ' &#151; ' . start_hour_to_am_pm($max) . "</TD></TR>\n";
}

/*
 * get_waitlist_number
 *
 * Get the waitlist number for a user's signup
 */

function get_waitlist_number ($RunId, $SignupId)
{
  $sql = 'SELECT SignupId FROM Signup';
  $sql .= " WHERE RunId=$RunId";
  $sql .= '   AND State="Waitlisted"';
  $sql .= '   AND Counted="Y"';

  //  echo "$sql<P>\nSignupId: $SignupId<P>\n";

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Query for waitlist failed', $sql);
    return 0;
  }

  $wait = 0;
  while ($row = mysql_fetch_object ($result))
  {
    $wait++;
    if ($row->SignupId == $SignupId)
    {
      mysql_free_result ($result);
      return $wait;
    }
  }

  // Didn't find it

  mysql_free_result ($result);
  return 0;
}

/*
 * intercon_mail
 *
 * Common function to send mail to a user.  This allows us to intercept
 * mail on development systems, as well as substitute in something other
 * than the default PHP mail function if I want to...
 */

function intercon_mail ($to, $subject, $msg, $reply_to='')
{
  // If this is the development version, all mail goes to me

  if (1 == DEVELOPMENT_VERSION)
  {
    $msg .= "\n\nOriginally to: $to";
    $to = DEVELOPMENT_MAIL_ADDR;
  }

  if ('' == $reply_to)
    $reply_to = sprintf (CON_SHORT_NAME.' Webmaster<%s>', EMAIL_WEBMASTER);

  $headers = array("From" => sprintf(CON_SHORT_NAME." Webmaster<%s>", EMAIL_WEBMASTER),
    "To" => $to,
    "Subject" => $subject,
    "Reply-To" => $reply_to,
    "X-Priority" => "1",
    "X-MSMail-Priority" => "High",
    "X-Mailer" => "php",
    "Errors-To" => EMAIL_WEBMASTER,
    "Return-Path" => EMAIL_WEBMASTER);

//  echo "No email sent<p>\n";
//  return true;

//  ini_set ('sendmail_from', EMAIL_WEBMASTER);
//  $ret =  mail ($to, $subject, $msg, $headers, '-f' . EMAIL_WEBMASTER);
//  ini_restore ('sendmail_from');
//  return $ret;

//echo "mail to: $to<br>\n";
//echo "subject: $subject<br>\n";
//echo "headers: $headers<br>\n";
//echo "message: $msg<br>\n";

  if (MAIL_BACKEND == "smtp") {
      $mail = Mail::factory('smtp', array(
          'host' => 'ssl://' . SMTP_HOST,
          'port' => 465,
          'auth' => true,
          'username' => SMTP_USERNAME,
          'password' => SMTP_PASSWORD
          ));
  } else {
      $mail = Mail::factory(MAIL_BACKEND);
  }
  $result = $mail->send($to, $headers, $msg);
  return !(PEAR::isError($result));
}

/*
 * obfuscate_email_address
 *
 * Attempt to obfuscate an EMail address so spammers don't harvest them
 * from our site.
 *
 * barry@tannenbaum.mv.com => barry AT tannenbaum DOT mv DOT com
 */

function obfuscate_email_address ($addr)
{
  // First convert the '@' to ' AT '

  $parts = explode ('@', $addr);
  $addr = $parts[0] . ' AT ' . $parts[1];

  // Now break out the dots

  $parts = explode ('.', $addr);
  $addr = $parts[0];
  for ($i = 1; $i < count($parts); $i++)
    $addr .= ' DOT ' . $parts[$i];
    
  return $addr;
}

/*
 * obfuscate_email_address_if_not_logged_in
 *
 * Obfuscate an email address if nobody is logged in.  This function
 * assumes that none of the harvesting software can login
 */

function obfuscate_email_address_if_not_logged_in ($addr)
{
  if (is_logged_in ())
    return $addr;
  else
    return obfuscate_email_address ($addr);
}

/*
 * mailto_or_obfuscated_email_address
 *
 * If noone is logged in, return an obfuscated EMail address.
 * If a user is logged in, return an MAILTO: link
 */

function mailto_or_obfuscated_email_address ($addr)
{
  if (is_logged_in ())
    return "<a href=\"mailto:$addr\">$addr</a>";
  else
    return obfuscate_email_address ($addr);
}

/*
 * gender_to_pronoun
 *
 * Given a gender, return the appropriate pronoun
 */

function gender_to_pronoun ($gender)
{
  if ('Male' == $gender)
    return 'He';
  else
    return 'She';
}

/*
 * user_is_gm
 *
 * Checks the session data to see whether the user is a GM for one or more
 * games
 */

function user_is_gm ()
{
  if (array_key_exists (SESSION_LOGIN_USER_GM, $_SESSION))
    return $_SESSION[SESSION_LOGIN_USER_GM];
  else
    return false;
}

function is_unpaid ($value)
{
  if (('Unpaid' == $value) || ('Alumni' == $value))
    return true;
  else
    return false;
}

/*
 * include_alumni
 *
 * Display the checkbox for whether to include alumni.  Defaults to a value
 * stored in the session array
 */

function include_alumni ()
{
  // If this is the first time in, default the value from the session array

  if (! array_key_exists ('AlumniInitialized', $_REQUEST))
    $alumni = $_SESSION['IncludeAlumni'];
  else
  {
    // If the checkbox is checked, we'll find 'Alumni' in the $_REQUEST
    // array.  If not, it will be missing

    if (! array_key_exists ('Alumni', $_REQUEST))
      $alumni = 0;
    else
      $alumni = 1;

    // Save the setting for next time

    $_SESSION['IncludeAlumni'] = $alumni;
  }

  //  dump_array ('SESSION', $_SESSION);

  if ($alumni)
    $alumni_checked = 'CHECKED';
  else
    $alumni_checked = '';

  echo "<INPUT TYPE=HIDDEN NAME=AlumniInitialized VALUE=1>\n";
  echo "<INPUT TYPE=CHECKBOX NAME=Alumni $alumni_checked>";
  echo "&nbsp;Include Alumni\n";

  return $alumni;
}

function include_alumni_form ($action, $code)
{
  echo "<FORM METHOD=POST ACTION=$action>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", $code);
  $alumni = include_alumni ();
  echo "<INPUT TYPE=SUBMIT VALUE=\"Update\"><BR>\n";
  echo "</FORM>\n";

  return $alumni;
}

/*
 * OBSOLETE FUNCTION - DO NOT USE!!!
 * Use mailto_url instead
 *
 * mailto
 *
 * Generate a mailto URL
 */

function mailto ($addr, $subject='')
{
  $s = sprintf ('mailto:%s-%s@interactiveliterature.org',
		$addr,
		CON_MAIL_LETTER);
  if ('' != $subject)
    $s .= '?subject=[' . CON_NAME . '] ' . $subject;

  return '"' . $s . '"';
}

/*
 * mailto_url
 *
 * Generate a mailto URL
 */

function mailto_url ($addr, $subject='')
{
  $s = "mailto:$addr";
  if ('' != $subject)
    $s .= '?subject=[' . CON_NAME . '] ' . $subject;

  return '"' . $s . '"';
}

/*
 * timestamp_to_datetime
 *
 * Convert a timestamp to a date/time (dd-mmm-yyyy hh:ss)
 *
 * This is made more complicated by the fact that the format for timestamps
 * changed in MySQL 4.1, and I'm running the older version at home, while
 * PowWeb is running the newer version
 */

function timestamp_to_datetime ($timestamp)
{
  // If there is no timestamp, return the non-break space

  if (0 == $timestamp)
    return '&nbsp;';

  // If this is a newer format timestamp, the format is yyyy-mm-dd hh:mm:ss
  //                                                    0000000000111111111
  //                                                    0123456789023456789

  if (strstr ($timestamp, '-'))
  {
    $year =       substr ($timestamp,  0, 4);
    $month_num =  substr ($timestamp,  5, 2);
    $day =        substr ($timestamp,  8, 2);
    $hour =       substr ($timestamp, 11, 2);
    $minute =     substr ($timestamp, 14, 2);
  }
  else
  {
    // This is a pre-4.1 timestamp.  The format is yyyymmddhhmmss
    //                                             00000000001111
    //                                             01234567890123

    $year =       substr ($timestamp,  0, 4);
    $month_num =  substr ($timestamp,  4, 2);
    $day =        substr ($timestamp,  6, 2);
    $hour =       substr ($timestamp,  8, 2);
    $minute =     substr ($timestamp, 10, 2);
  }

  switch ($month_num)
  {
    case  1: $month = 'Jan'; break;
    case  2: $month = 'Feb'; break;
    case  3: $month = 'Mar'; break;
    case  4: $month = 'Apr'; break;
    case  5: $month = 'May'; break;
    case  6: $month = 'Jun'; break;
    case  7: $month = 'Jul'; break;
    case  8: $month = 'Aug'; break;
    case  9: $month = 'Sep'; break;
    case 10: $month = 'Oct'; break;
    case 11: $month = 'Nov'; break;
    case 12: $month = 'Dec'; break;

    default: $month = 'Invalid'; break;
  }

  return $day . '-' . $month . '-' . $year . ' ' . $hour . ':' . $minute;
}

/*
 * build_shirt_order_string
 *
 * Given an array containing shirt sizes and quantities, build an string
 * showing the size of each ordered shirt.
 */

function build_shirt_order_string ($a)
{
  $order = '';

  foreach ($a as $key => $value)
    if (0 != $value)
    {
      if ('' != $order)
	$order .= ', ';

      $order .= "$value $key";
    }

  return $order;
}

/*
 * past_shirt_deadline
 *
 * Return 1 if it's past the shirt ordering deadline, 0 if it's not
 */

function past_shirt_deadline()
{
  if (time() > parse_date(SHIRT_CLOSE))
    return 1;
  else
    return 0;
}

/*
 * birth_year_to_age
 *
 * Converts the birth year to an age.  Note that '0' means 'unspecified'
 */

function birth_year_to_age ($year)
{
  if (0 == $year)
    return 0;

  $this_year = intval (date ('Y'));

  return $this_year - $year;
}

/*
 * title_not_in_events_table
 *
 * Check the Events table for an event with the specified title
 */

function title_not_in_events_table ($title)
{
  $sql = sprintf ("SELECT EventId FROM Events WHERE Title='%s'",
	mysql_real_escape_string ($title));
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for title '$title' failed");

  if (0 != mysql_num_rows ($result))
    return display_error ("Title '$title' is already in the database");

  return true;
}

/*
 * build_url_string
 *
 */

function build_url_string ($name, $value, $continued=TRUE)
{
  if ($continued)
    $suffix = '&';
  else
    $suffix = '';

  return $name . '=' . urlencode ($value) . $suffix;
}

function form_hidden_value ($name, $value)
{
  echo "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
}


function form_checkbox ($name, $checked, $value='')
{
  if ($checked)
    $chk = 'checked';
  else
    $chk = '';

  if ('' == $value)
    $val = '';
  else
    $val = "value=\"$value\"";

  echo "<input type=\"checkbox\" name=\"$name\" $val $chk>";
}

function form_checkboxYN ($name)
{
  $checked = '';

  if (array_key_exists($name, $_POST))
    if ('Y' == $_POST[$name])
      $checked = 'checked';

  echo "<input type=\"checkbox\" name=\"$name\" value=\"Y\" $checked>\n";
}

function form_single_select($text, $name, $VALUE_LIST, $choice=" ")
{
  echo $text;
  echo "      <SELECT NAME=$name SIZE=1>\n";
  foreach ($VALUE_LIST as $value)
   if ($choice == $value)
     echo "        <option value=$value selected>$value</option>\n";

   else 
     echo "        <option value=$value>$value</option>\n";

  echo "      </SELECT>\n";
}

function form_radio ($name, $value, $checked)
{
  if ($checked)
    $chk = 'checked';
  else
    $chk = '';

  echo "<input type=\"radio\" name=\"$name\" value=\"$value\" $chk>";
}

function pretty_rooms($str)
{
  // Quick check for empty string - why waste time?
  if ('' == $str)
    return '&nbsp;';

  // Convert combined rooms into something 'pretty'
  $str = str_replace('Cambridge East,Cambridge West', 'Cambridge', $str);
  $str = str_replace('Lincoln East,Lincoln West', 'Lincoln', $str);
  $str = str_replace('Eden Vale A1,Eden Vale A2,Eden Vale A3', 'Eden Vale A', $str);
  $str = str_replace('Eden Vale A1,Eden Vale A2', 'Eden Vale A1 &amp; A2', $str);
  $str = str_replace('Eden Vale A2,Eden Vale A3', 'Eden Vale A2 &amp; A3', $str);
  $str = str_replace('Eden Vale A1,Eden Vale A3', 'Eden Vale A1 &amp; A3', $str);
  $str = str_replace('Eden Vale C1,Eden Vale C2,Eden Vale C3', 'Eden Vale C', $str);
  $str = str_replace('Eden Vale C1,Eden Vale C2', 'Eden Vale C1 &amp; C2', $str);
  $str = str_replace('Eden Vale C2,Eden Vale C3', 'Eden Vale C2 &amp; C3', $str);
  $str = str_replace('Eden Vale C1,Eden Vale C3', 'Eden Vale C1 &amp; C3', $str);
  $str = str_replace('Eden Vale A,Eden Vale B,Eden Vale C', 'Eden Vale A, B &amp; C', $str);
  $str = str_replace('Eden Vale A,Eden Vale B', 'Eden Vale A &amp; B', $str);
  $str = str_replace('Eden Vale B,Eden Vale C', 'Eden Vale B &amp; C', $str);
  $str = str_replace('Eden Vale A,Eden Vale C', 'Eden Vale A &amp; C', $str);

  // Check for single rooms
  $ary = explode(',', $str);
  if (1 == count($ary))
    return $ary[0];

  // Make a pretty list
  $Rooms = implode(', ', $ary);

  return $Rooms;
}

function room_check($ary, $room)
{
  $checked = '';
  if (is_array($ary))
    if (array_key_exists($room, $ary))
      $checked = 'checked';

  printf ('            <input type="checkbox" name="Rooms[]" ' .
	  "value=\"%s\" %s>%s<br>\n",
	  $room,
	  $checked,
	  $room);
}

function form_con_rooms($display, $key)
{
  if (! array_key_exists($key, $_POST))
    $rooms = '';
  else
  {
    if (! is_array($_POST[$key]))
      $rooms = $_POST[$key];
    else
      $rooms = array_flip($_POST[$key]);
  }

  echo "  <tr>\n";
  echo "    <td valign=\"right\">$display:</td>\n";
  echo "    <td>\n";
  echo "      <table>\n";
  echo "        <tr valign=\"top\">\n";
  echo "          <td>\n";
  room_check($rooms, 'Dance Space');
  room_check($rooms, 'Lecture Space A');
  room_check($rooms, 'Lecture Space B');
  room_check($rooms, 'Workshop Space');
  echo "          </td>\n";
  echo "          <td>\n";
  room_check($rooms, 'Food Space');
  room_check($rooms, 'Theater');
  room_check($rooms, 'Vendors');
  room_check($rooms, 'Pool');
  echo "          </td>\n";
  echo "          <td>\n";
 
  echo "          </td>\n";
  echo "          <td>\n";

  echo "          </td>\n";
  echo "        </tr>\n";
  echo "      </table>\n";
  echo "    </td>\n";
  echo "  </tr>\n";
}
?>
