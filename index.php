<?php
include ("intercon_db.inc");
include ("files.php");
include("gbe_ticketing.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Note that unlike all other scripts, we delay displaying the boilerplate
// so we can update it when the user successfully logs in
/*
// If this appears to be a PayPal message, log a copy of it

if (array_key_exists ('txn_type', $_POST))
{
  log_paypal_msgs ();

  // Check whether PayPal is notifying us that they've accepted payment for us

  if (array_key_exists ('txn_type', $_POST) &&
      array_key_exists ('payment_status', $_POST))
  {
    if (('web_accept' == $_POST['txn_type']) &&
	('Completed' == $_POST['payment_status']))
      mark_user_paid ();
  }
}
*/

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
{
  if (isset ($_SESSION[SESSION_LOGIN_USER_ID]))
    $action = SHOW_USER_HOMEPAGE;
  else
    $action = PROMPT_FOR_LOGIN;
}

switch ($action)
{
  //  case 1492:
  //    html_begin();
  //    paypal_test();
  //    break;

  case PROMPT_FOR_LOGIN:
    html_begin ();

    // Log the referrer, if one's available
    log_referrer ();

    display_login_form ();
    break;

  case LOGOUT_USER:
      
    // clear the Vanilla Forums SSO cookie if it exists
    setcookie('Vanilla', ' ', time() - 3600, '/', '.interactiveliterature.org');
    unset($_COOKIE['Vanilla']);

    session_unset ();

    html_begin ();
    display_login_form ();
    break;

  case LOGIN_USER:

    // Process the username/password.  If they don't match, redisplay the form

    $result = process_login_form ();
    if (! is_numeric ($result))
    {
      html_begin ();
      display_login_form ($result);
      break;
    }

    // If the user is a returning Alumni, we've just converted him/her to the
    // Unpaid status.  Ask them to update their information

    if ($result < 0)
    {
      html_begin ();
      $result = fill_post ($_SESSION[SESSION_LOGIN_USER_ID]);
      if (is_bool ($result))
	display_user_form (true);
      else
	display_error ($result);
      break;
    }

    // See if we're supposed to go somewhere else after logging in

    $dest = '';
    if (array_key_exists ('dest', $_REQUEST))
      $dest = $_REQUEST['dest'];

    if ('' != $dest)
    {
      header ("Location: $dest");
      exit ();
    }

    // Normal login.  Just show the user's homepage

    html_begin ();
    show_user_homepage ();

    break;

  case SHOW_USER_HOMEPAGE:
    html_begin ();
    show_user_homepage ();
    break;

  case WITHDRAW_FROM_GAME:
    html_begin ();
    if (! confirm_withdraw_from_game ())
      show_user_homepage ();
    break;

  case WITHDRAW_FROM_GAME_CONFIRMED:
    html_begin ();
    withdraw_from_game ();
    show_user_homepage ();
    break;

  case NEW_USER:
    html_begin ();
    display_user_form (false);
    break;

  case UPDATE_USER:
    html_begin ();
    $result = fill_post ($_SESSION[SESSION_LOGIN_USER_ID]);
    if (is_bool ($result))
      display_user_form (false);
    else
      display_error ($result);
    break;

  case ADD_USER:
    if (out_of_sequence ())
    {
      html_begin ();
      show_user_homepage ();
      break;
    }

    $result = add_user ();
    html_begin ();
    if (! is_numeric ($result))
      display_user_form (false, $result);
    else
      show_user_homepage ();
    break;

  case SHOW_USER:
    html_begin ();
    display_user_information ($_REQUEST['UserId']);
    break;

  case REQUEST_PASSWORD:
    html_begin ();
    display_password_form ();
    break;

  case SEND_PASSWORD:
    html_begin ();
    if (! process_password_request ())
      display_password_form ();
    break;

  case CHANGE_PASSWORD:
    html_begin ();
    display_password_change_form ();
    break;
	echo "$row->Day $row->Title<BR>\n";

  case PROCESS_PASSWORD_CHANGE:
    html_begin ();
    if (! process_password_change_request ())
      display_password_change_form ();
    else
      show_user_homepage ();
    break;
    
  case SELECT_USER_TO_EDIT:
    html_begin ();
    select_user_to_edit ();
    break;

  case EDIT_USER:
    html_begin ();
    display_user_form_for_others ();
    break;

  case PROCESS_EDIT_USER:
    html_begin ();
    if (! process_edit_user ())
      display_user_form_for_others ();
    else
      select_user_to_edit ();
    break;

  case SHOW_COMPED_USERS:
    html_begin ();
    display_comped_users ();
    break;

  case SELECT_USER_TO_DELETE:
    html_begin ();
    select_user_to_delete ();
    break;

  case SHOW_USER_TO_DELETE:
    html_begin ();
    show_user_to_delete ();
    break;

  case DELETE_USER:
    html_begin ();
    delete_user ();
    break;

  case SELECT_USER_TO_VIEW:
    html_begin ();
    select_user_to_view ();
    break;

  case VIEW_USER:
    html_begin ();
    view_user ();
    break;

  case SELECT_USER_TO_SET_PASSWORD:
    html_begin ();
    select_user_to_set_password ();
    break;

  case DISPLAY_PASSWORD_FORM_FOR_USER:
    html_begin ();
    display_password_form_for_user ();
    break;

  case PROCESS_PASSWORD_FORM_FOR_USER:
    html_begin ();
    if (! process_password_form_for_user ())
      display_password_form_for_user ();
    break;

  case SELECT_USER_TO_BECOME:
    html_begin ();
    select_user_to_become ('');
    break;

  case BECOME_USER:
    $result = become_user ();
    if (! is_numeric ($result))
    {
      html_begin ();
      select_user_to_become ($result);
    }
    else
    {
      html_begin ();
      show_user_homepage ();
    }
    break;

  case EDIT_BIO:
    html_begin ();
    edit_bio ();
    break;

  case UPDATE_BIO:
    html_begin ();
    if (! update_bio ())
      edit_bio ();
    else
      show_user_homepage ();
    break;

  case BIO_REPORT:
    html_begin ();
    bio_report ();
    break;

  case WHO_IS_WHO:
    html_begin ();
    who_is_who ();
    break;

  case WITHDRAW_USER_FROM_ALL_GAMES:
    html_begin ();
    if (! confirm_withdraw_user_from_all_games ())
      display_user_form_for_others ();
    break;

  case WITHDRAW_USER_FROM_ALL_GAMES_CONFIRMED:
    html_begin ();
    withdraw_user_from_all_games ();
    display_user_form_for_others ();
    break;

/*
 * Already done
  case CONVERT_AGE_TO_YEAR:
    html_begin();
    convert_age_to_year();
    break;
 */

  default:
    html_begin ();
    display_error ("Unknown action code $action!\n");
    break;
}

// Add the postamble

html_end ();

/*
 * log_referrer
 *
 * Log the page that referred the user to us
 */

function log_referrer ()
{
  // If we don't have a referrer, just return

  if (! array_key_exists ('HTTP_REFERER', $_SERVER))
    return;

  // If the con is over, just return.  There's no reason for the database
  // to grow forever...

  $now = time();
  if ($now > parse_date (CON_OVER))
    return;

  // Make sure this isn't coming from one of OUR pages...

  $url = $_SERVER['HTTP_REFERER'];
  $host = 'http://' . $_SERVER['SERVER_NAME'];

  //  echo "referrer: $referrer<br>\n";
  //  echo "host:     $host<br>\n";

  if (0 == strncasecmp ($url, $host, strlen($host)))
    return;


  if (1 != get_magic_quotes_gpc())
    $url = mysql_escape_string ($url);

  $sql = "INSERT INTO Referrers SET Url='$url'";

  $result = mysql_query ($sql);
  if (! $result)
  {
    display_mysql_error ('Insert into Referrers table failed', $sql);
    return 0;
  }

  $referrer_id = mysql_insert_id();

  $_SESSION[SESSION_REFERRER_ID] = $referrer_id;

  return $referrer_id;
}

/*
 * process_login_form
 *
 * Process the login form filled out by the user.  Note that this function
 * is special in that it doesn't display any errors right away, but instead
 * saves them to be displayed later
 */

function process_login_form ()
{
  // Check for a sequence error.

  if (out_of_sequence ())
    return 'Sequence Error.  Did you use the Back Button?';

  // Extract the username and password and make sure they're not empty

  $EMail = trim ($_POST['EMail']);
  if (1 != get_magic_quotes_gpc())
    $EMail = mysql_escape_string ($EMail);

  $Password = trim ($_POST['Password']);
  $HashedPassword = md5 ($Password);

  $errors = '';

  if ('' == $EMail)
    $errors .= "You must specify an EMail address<P>\n";

  if ('' == $Password)
    $errors .= "You must specify a password<P>\n";

  // If we're missing either string, give up

  if ('' != $errors)
    return $errors;

  // Query the database for the EMail address and password

  $sql = 'SELECT FirstName, LastName, UserId, Priv, DisplayName, CanSignup, Email';
  $sql .= ' FROM Users';
  $sql .= " WHERE EMail='$EMail' AND HashedPassword='$HashedPassword'";

  //  echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query', $sql);

  // Make sure we've gotten a single match

  if (0 == mysql_num_rows ($result))
    return 'Failed to find matching EMail address / password';

  if (1 != mysql_num_rows ($result))
    return 'Found more than one matching EMail address';

  // Extract the UserId for the user being logged in and decode the privileges

  $row = mysql_fetch_object ($result);

  return login_with_data ($row, $EMail);
}

function login_with_data ($row, $EMail)
{
  $UserId = $row->UserId;
  $DisplayName = $row->DisplayName;
  $name = trim ("$row->FirstName $row->LastName");

  // Update the login time.  If the user was an Alumni, promote him or her to
  // Unpaid, since he's expressed an interest in this con

  $returning_alumni = false;

  $sql = 'UPDATE Users SET LastLogin=NULL';
  if ('Alumni' == $row->CanSignup)
  {
    $sql .= ', CanSignup="Unpaid", CanSignupModified=NULL';
    $returning_alumni = true;
  }
  $sql .= " WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Attempt to update user login time failed', $sql);

  // Update the referrers table, if an entry was made

  if (array_key_exists (SESSION_REFERRER_ID, $_SESSION))
  {
    $sql = "UPDATE Referrers SET UserId=$UserId WHERE ReferrerId=" .
           $_SESSION[SESSION_REFERRER_ID];

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ('Attempt to update referrer record failed', $sql);
  }

  // Make sure the session is empty

  session_unset ();

  // Create the session variables and set them

  $_SESSION[SESSION_LOGIN_USER_ID] = $UserId;
  $_SESSION[SESSION_LOGIN_USER_PRIVS] = ",$row->Priv,";
  $_SESSION[SESSION_LOGIN_USER_DISPLAY_NAME] = $DisplayName;
  $_SESSION[SESSION_LOGIN_USER_NAME] = $name;
  $_SESSION[SESSION_LOGIN_USER_EMAIL] = $EMail;
  $_SESSION['IncludeAlumni'] = 0;

  // Initialize the session information from the Con table

  can_show_schedule ();

  // Check whether this is a GM

  $sql = 'SELECT GMId FROM GMs';
  $sql .= " WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Cannot query GM list");

  $is_gm = mysql_num_rows($result);

  // Check whether this is an Iron GM

  $sql = 'SELECT IronGmId FROM IronGm';
  $sql .= " WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Cannot query IronGm list");

  $is_gm += mysql_num_rows($result);

  if (0 == $is_gm)
    $_SESSION[SESSION_LOGIN_USER_GM] = 0;
  else
  {
    $_SESSION[SESSION_LOGIN_USER_GM] = 1;

    // If the user is a GM, he may be able to see the schedule now...

    if (0 == $_SESSION[SESSION_CON_SHOW_SCHEDULE])
    {
      $result = mysql_query ('SELECT ShowSchedule FROM Con');
      if ($result)
      {
	$row = mysql_fetch_object ($result);
	if ('GMs' == $row->ShowSchedule)
	  $_SESSION[SESSION_CON_SHOW_SCHEDULE] = 1;
      }
    }
  }

  if ($returning_alumni)
    return - $UserId;
  else
    return $UserId;
}

/*
 * is_user_gm_for_game
 *
 * Check whether the user is a GM for a game
 */

function is_user_gm_for_game ($UserId, $EventId)
{
  $sql = 'SELECT GMId FROM GMs';
  $sql .= " WHERE UserId=$UserId";
  $sql .= "   AND EventId=$EventId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query GM status', $sql);

  if (mysql_num_rows ($result) > 1)
    return display_mysql_error ('Matched more than 1 GM entry', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return false;
  else
    return true;
}

/*
 * show_games
 *
 * Show the games the user is signed up for.  If the sequence number is
 * -1, don't display a "Withdraw" link
 */

function show_games ($UserId, $prefix, $type, $state, $sequence_number = -1)
{
  // Query the database for the games the user is registered for

  $sql = 'SELECT Events.EventId, Events.Title, Events.Hours,';
  $sql .= ' Runs.Day, Runs.StartHour, Runs.TitleSuffix,';
  $sql .= ' Signup.SignupId, Signup.RunId';
  $sql .= ' FROM Signup, Events, Runs';
  $sql .= " WHERE Signup.UserId=$UserId";
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= "   AND Signup.State='$state'";
  $sql .= ' ORDER BY Runs.Day, Runs.StartHour';

  //    print ($sql ."\n<p>\n");

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query signup list', $sql);

  // Special case for now matches

  $num_rows = mysql_num_rows ($result);
  if (0 == $num_rows)
  {
    echo "<B>$prefix not $type for any volunteer opportunities.</B><P>\n";
    return TRUE;
  }

  // Select singular or plural for game(s)

  if (1 == $num_rows)
    $games = 'game';
  else
    $games = 'games';

  // Start displaying the table of games

  echo "<B>$prefix currently $type for $num_rows $games:</B>\n<br>";
  echo "<TABLE>\n";

  // Display one row per game the user is signed up for

  while ($row = mysql_fetch_object ($result))
  {
    $start_time = start_hour_to_12_hour ($row->StartHour);
    $end_time = start_hour_to_12_hour ($row->StartHour + $row->Hours);

    $Title = $row->Title;
    if ("" != $row->TitleSuffix)
      $Title .= " -- " . $row->TitleSuffix;

    echo "  <TR VALIGN=TOP>\n";
    echo "    <TD>$row->Day</TD>\n";
    echo "    <TD NOWRAP>&nbsp;$start_time - $end_time&nbsp;</TD>\n";

    if ('Waitlisted' == $state)
    {
      $wait = get_waitlist_number ($row->RunId, $row->SignupId);
      if (0 == $wait)
	$wait_str = '&nbsp;';
      else
	$wait_str = "Wait #$wait";

      echo "    <TD NOWRAP>$wait_str&nbsp;</TD>\n";
    }

    echo "    <TD><A HREF=Schedule.php?action=" . SCHEDULE_SHOW_GAME .
                       "&EventId=$row->EventId>$Title</A>&nbsp;&nbsp;&nbsp;";

    if (is_user_gm_for_game ($UserId, $row->EventId))
      echo  '[GM]&nbsp;&nbsp;&nbsp;';

    echo "</TD>\n";

    if (-1 != $sequence_number)
    {
      $link = sprintf ('<A HREF=index.php?action=%d&SignupId=%d&Seq=%d>',
		       WITHDRAW_FROM_GAME,
		       $row->SignupId,
		       $sequence_number);
      echo "    <TD BGCOLOR=\"#FFCCCC\">[${link}Withdraw</A>]</TD>\n";
    }
    echo "  </TR>\n";
  }

  // Finish off the table

  echo "</TABLE>\n<P>\n";
}

/*
 * build_order_string
 *
 * Build a string with shirt order information
 */

function build_order_string ($n, $size, &$s, &$count, $type)
{
  if (0 == $n)
    return;

  if ('' != $s)
    $s .= ', ';
  $s .= "$n $size $type";
  $count += $n;
}


/*
 * show_user_homepage_shirts
 *
 * Show any shirts the user has ordered
 */

function show_user_homepage_shirts ($UserId)
{
  // Display the header for the user's TShirt order

  display_header ('<p>' . CON_NAME . ' Shirts Ordered');

  // Count up the number of shirts the user has ordered

  $sql = 'SELECT * FROM TShirts';
  $sql .= " WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to count ordered shirts', $sql);

  $paid_count = 0;
  $unpaid_count = 0;
  $pending_count = 0;

  $paid_order = '';
  $unpaid_order = '';
  $pending_order = '';


  while ($row = mysql_fetch_object ($result))
  if ($row)
  {
    if ('Cancelled' == $row->Status)
      continue;

    $order = '';
    $count = 0;

    build_order_string ($row->Small, "Small", $order, $count, SHIRT_NAME);
    build_order_string ($row->Medium, "Medium", $order, $count, SHIRT_NAME);
    build_order_string ($row->Large, "Large", $order, $count, SHIRT_NAME);
    build_order_string ($row->XLarge, "XLarge", $order, $count, SHIRT_NAME);
    build_order_string ($row->XXLarge, "XXLarge", $order, $count, SHIRT_NAME);
    build_order_string ($row->X3Large, "X3Large", $order, $count, SHIRT_NAME);
    build_order_string ($row->X4Large, "X4Large", $order, $count, SHIRT_NAME);
    build_order_string ($row->X5Large, "X5Large", $order, $count, SHIRT_NAME);

    $order_2 = '';
    $count_2 = 0;

    build_order_string ($row->Small_2, "Small", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->Medium_2, "Medium", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->Large_2, "Large", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->XLarge_2, "XLarge", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->XXLarge_2, "XXLarge", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X3Large_2, "X3Large", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X4Large_2, "X4Large", $order, $count, SHIRT_2_NAME);
    build_order_string ($row->X5Large_2, "X5Large", $order, $count, SHIRT_2_NAME);

    switch ($row->Status)
    {
      case 'Paid':
        if ($count > 0)
	{
	  if ('' != $paid_order)
	    $paid_order .= ', ';
	  $paid_order .= $order;
	}

        if ($count_2 > 0)
	{
	  if ('' != $paid_order)
	    $paid_order .= ', ';
	  $paid_order .= $order_2;
	}

	$paid_count += $count + $count_2;
	break;

      case 'Unpaid':
        if ($count > 0)
	{
          if ('' != $unpaid_order)
	    $unpaid_order .= ', ';
	  $unpaid_order .= $order;
	}

        if ($count_2 > 0)
	{
          if ('' != $unpaid_order)
	    $unpaid_order .= ', ';
	  $unpaid_order .= $order_2;
	}

	$unpaid_count = $count + $count_2;
	break;
    }
  }

  if ((0 == $paid_count) && (0 == $unpaid_count) && (0 == $pending_count))
  {
    echo '<p>You have not requested any ' . CON_NAME . " Shirts.\n";
    if (! past_shirt_deadline())
      echo "Click <a href='TShirts.php'>here</a> to order shirts.<p>\n";
    else
    {
      echo "A limited number of shirts will be available at the con.\n";
      echo "If you want a shirt, check at the registration desk to see\n";
      echo "if any are available in your size.\n";
    }
    echo "</p>\n";
    return true;
  }

  show_shirt_link ($paid_count, $paid_order, 'Paid');
  show_shirt_link ($unpaid_count, $unpaid_order, 'Unpaid');
  show_shirt_link ($pending_count, $pending_order, 'Pending');

  return true;
}

function show_shirt_link ($count, $order, $type)
{
  if (0 == $count)
    return;

  if (1 == $count)
    $shirt = 'shirt';
  else
    $shirt = 'shirts';

  $shirt_close = strftime ('%d-%b-%Y', parse_date (SHIRT_CLOSE));

  switch ($type)
  {
    case 'Paid':
      echo "You have ordered and paid for $order $shirt.\n";
      if (! past_shirt_deadline())
      {
	echo "Click <a href='TShirts.php'>here</a> to order more shirts.\n";
        echo "The deadline for shirt orders is $shirt_close.<p>\n";
      }
      else
      {
	echo "The order deadline for shirts was $shirt_close.  If you want\n";
	echo "additional shirts you should ask whether there are any shirts\n";
	echo "available in your size when you checkin at registration at\n";
	echo "the con.<p>\n";
      }
      break;

    case 'Unpaid':
      echo "You have ordered and not yet paid for $order $shirt.\n";
      echo "The deadline for shirt orders is $shirt_close.  Any unpaid\n";
      echo "shirt orders as of $shirt_close will be cancelled.\n";
      echo "Click <a href='TShirts.php'>here</a> to pay for your $shirt\n";
      echo "using PayPal.  Or you can send a check or money order made out\n";
      echo "to\n";
      echo "&quot<B>New&nbsp;England&nbsp;Interactive&nbsp;Literature</B>&quot;\n";
      echo "to<br>\n";
      echo "<table>\n";
      echo "  <tr>\n";
      echo "    <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
      printf ("    <td><b>%s<br>c/o %s<br>%s</b></td>\n",
	      CON_NAME,
	      NAME_SEND_CHECKS,
	      ADDR_SEND_CHECKS);
      echo "  </tr>\n";
      echo "</table>\n";
      //      echo "Shirt payments must be received by December 25, 2006.<p>";
      break;

    case 'Pending':
      echo "You have ordered $order $shirt and the payment has not been\n";
      echo "registered with the website.  Please contact the\n";
      printf ("<a href=mailto:%s>Registrar</a> to resolve this.<p>\n",
	      EMAIL_REGISTRAR);
      echo "The deadline for shirt orders is $shirt_close.  Any unpaid\n";
      echo "shirt orders as of $shirt_close will be cancelled.\n";
      break;
  }      
}

/*
 * show_user_homepage_gm
 *
 * Add links to any games the user is a GM for
 */

function show_user_homepage_gm ($UserId)
{
  $sql = 'SELECT Events.Title, Events.EventId FROM GMs, Events';
  $sql .= "  WHERE GMs.UserId=$UserId";
  $sql .= '    AND Events.EventId=GMs.EventId';
  $sql .= '  ORDER BY Events.Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of games for GM');

  if (0 == mysql_num_rows ($result))
    return true;

  display_header ("<P>Links To Classes & Panels you are Presenting:");

  echo "<TABLE CELLPADDING=2>\n";
  while ($row = mysql_fetch_object ($result))
  {
    echo "  <TR>\n";
    printf ("    <TD><A HREF=Schedule.php?action=%d&EventId=%d>%s</A></TD>\n",
	    SCHEDULE_SHOW_GAME,
	    $row->EventId,
	    $row->Title);
    echo "  </TR>\n";
  }

  echo "</TABLE>\n";

  return true;
}

/*
 * show_user_homepage_bids
 *
 * Add links to any games the user is a GM for
 */

function show_user_homepage_bids ($UserId)
{
  $sql = 'SELECT BidId, Status, Title FROM Bids';
  $sql .= "  WHERE UserId=$UserId";
  $sql .= '  ORDER BY Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of bid games');

  if (0 == mysql_num_rows ($result))
    return true;

  display_header ("<P>Status of Classes, Panels and Acts you've offered:");

  echo "<TABLE CELLPADDING=2>\n";
  while ($row = mysql_fetch_object ($result))
  {
    echo "  <TR>\n";
    echo "    <TD>$row->Status:</TD>\n";
    printf ("    <TD>%s<A HREF=Bids.php?action=%d&BidId=%d>%s</A></TD>\n",
    	$row->GameType,
	    BID_GAME,
	    $row->BidId,
	    $row->Title);
    echo "  </TR>\n";
  }

  echo "</TABLE>\n";

  return true;
}

/*
 * show_user_homepage_precon_bids
 *
 * Add links to any pre-con events the user has bid
 */

function show_user_homepage_precon_bids ($UserId)
{
  $sql = 'SELECT PreConEventId, Status, Title FROM PreConEvents';
  $sql .= "  WHERE SubmitterUserId=$UserId";
  $sql .= '  ORDER BY Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of bid games');

  if (0 == mysql_num_rows ($result))
    return true;

  display_header ("<P>Status of Pre-Convention Events You've Bid");

  echo "<table cellpadding=\"2\">\n";
  while ($row = mysql_fetch_object ($result))
  {
    echo "  <tr>\n";
    echo "    <td>$row->Status:</td>\n";
    printf ('    <td><a href="Thursday.php?action=%d&PreConEventId=%d">' .
	    "%s</a></td>\n",
	    PRECON_SHOW_EVENT_FORM,
	    $row->PreConEventId,
	    $row->Title);
    echo "  </tr>\n";
  }

  echo "</table>\n";

  return true;
}

/*
 * show_user_homepage_plugs
 *
 * Add links to any shameless plugs the user owns
 */

function show_user_homepage_plugs ($UserId)
{
  $sql = 'SELECT PlugId, Name, Visible, EndDate FROM Plugs';
  $sql .= "  WHERE UserId=$UserId";
  $sql .= '  ORDER BY Name';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of plugs');

  if (0 == mysql_num_rows ($result))
    return true;

  display_header ("<P>Status of Your Shameless Plugs");

  echo "<table cellpadding=\"2\">\n";
  while ($row = mysql_fetch_object ($result))
  {
    echo "  <tr>\n";
    printf ("    <td><a href=\"Plugs.php?action=%d&PlugId=%d\">%s</a></td>\n",
	    PLUGS_SHOW_FORM,
	    $row->PlugId,
	    $row->Name);
    if ('N' == $row->Visible)
      $visibility = 'Hidden';
    else
    {
      $a = explode('-', $row->EndDate);
      $end_time = mktime (0, 0, 0, $a[1], $a[2], $a[0]);
      $now = time();

      if ($end_time > $now)
	$visibility = 'Visible until ';
      else
	$visibility = 'Expired ';
      $visibility .= strftime ('%A, %e-%b-%Y', $end_time);
    }
    echo "    <td>$visibility</td>\n";
    echo "  </tr>\n";
  }

  echo "</table>\n";

  return true;
}

/*
 * mark_user_paid
 *
 * If the user has just paid through PayPal, update his status
 */

function mark_user_paid ()
{
    
  //  dump_array ('POST - mark_user_paid', $_POST);
  
  // Flip the "Paid" bit in the user's record

  $paid_by = 'Paid via PayPal ' . strftime ('%d-%b-%Y %H:%M');
  if (array_key_exists ('txn_id', $_POST))
    $paid_by .= ' TxnID: ' . $_POST['txn_id'];
  if (array_key_exists ('last_name', $_POST))
  {
    $paid_by .= ' PaidBy: ' . $_POST['last_name'];
    if (array_key_exists ('first_name', $_POST))
      $paid_by .= ', ' . $_POST['first_name'];
  }

  $amount = 0;
  if (array_key_exists ('payment_gross', $_POST))
    $amount = intval ($_POST['payment_gross']) * 100;

  // There are two types of payment that may come in here; con registration and
  // shirt payments.  We can differentiate them by the "item_name" field.

  if (! array_key_exists ('item_name', $_POST))
    display_error ('PayPal message does not contain "item_name" field.  We can\'t tell what\'s being paid for!');

  $user_id = 0;


  $bConPayment = $_POST['item_name'] == PAYPAL_ITEM_CON;
  $bShirtPayment = $_POST['item_name'] == PAYPAL_ITEM_SHIRT;
  $bThursdayPayment = $_POST['item_name'] == PAYPAL_ITEM_THURSDAY;

//  if ($bConPayment)
//    echo "<!-- Con payment -->\n";
//  if ($bShirtPayment)
//    echo "<!-- Shirt payment -->\n";
//  if ($bThursdayPayment)
//    echo "<!-- Thursday payment -->\n";


  if ($bConPayment)
  {
    // If this is a con payment, the custom field is the UserId.  Mark the
    // user paid

    if (array_key_exists ('custom', $_POST))
      $user_id = intval ($_POST['custom']);

    $sql = "UPDATE Users SET CanSignup='Paid'";
    $sql .= ', CanSignupModified=NULL';
    $sql .= ", CanSignupModifiedId=$user_id";
    $sql .= ", PaymentNote='$paid_by'";
    $sql .= ", PaymentAmount=$amount";
    $sql .= " WHERE UserId=$user_id";
    //  echo "$sql<p>\n";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to update user $user_id with notification from PayPal");

    // If we've got session info, we're done

    if (array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
      return TRUE;
  }
  elseif ($bShirtPayment)
  {
    // If this is a shirt payment, the custom field is the TShirtId.  Mark the
    // shirt paid and fetch the UserId from the TShirt record

    $TShirtID = 0;
 
    if (array_key_exists ('custom', $_POST))
      $TShirtID = intval ($_POST['custom']);

    $sql = 'UPDATE TShirts SET Status="Paid"';
    $sql .= ", PaymentNote='$paid_by'";
    $sql .= ", PaymentAmount=$amount";
    $sql .= " WHERE TShirtID=$TShirtID";
    //  echo "$sql<p>\n";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to update shirt record $TShirtID with notification from PayPal");

    // If we've got session info, we're done

    if (array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
      return TRUE;

    // Otherwise, we're going to need the UserId so we can log him (or her)
    // back in

    $sql = "SELECT UserId FROM TShirts WHERE TShirtID=$TShirtID";
    //  echo "$sql<p>\n";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Failed to fetch shirt record $TShirtID");

    $num_rows = mysql_num_rows($result);
    if (1 != mysql_num_rows($result))
      return display_error ("$num_rows rows returned for shirt record $TShirtID");
    $row = mysql_fetch_object($result);
    $user_id = $row->UserId;
  }
  elseif ($bThursdayPayment)
  {
    // If this is a Thursday Thing payment, the custom field is the UserId.
    // Add a Thursday record for the user

    if (array_key_exists ('custom', $_POST))
      $user_id = intval ($_POST['custom']);

    $sql = "INSERT Thursday SET UserId=$user_id,";
    $sql .= 'Status="Paid", ';
    $sql .= "PaymentNote='$paid_by', ";
    $sql .= "PaymentAmount=$amount";

    $result = mysql_query($sql);
    if (! $result)
      return display_mysql_error ("Failed to insert Thursday record for $user_id", $sql);

    // If we've got session info, we're done

    if (array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
      return true;
  }
  else
  {
    printf ("<!-- Unknown payment type! \"%s\" -->\n",
	    $_POST['item_name']);
  }

  // Refetch the user info & log them in again, since it's probably lost

  $sql = 'SELECT FirstName, LastName, UserId, Priv, DisplayName, CanSignup, Email';
  $sql .= ' FROM Users';
  $sql .= " WHERE UserId=$user_id";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query', $sql);

  // Make sure we've gotten a single match

  if (0 == mysql_num_rows ($result))
    return 'Failed to find matching EMail address / password';

  if (1 != mysql_num_rows ($result))
    return 'Found more than one matching EMail address';

  // Extract the UserId for the user being logged in and decode the privileges

  $row = mysql_fetch_object ($result);

  return login_with_data ($row, $EMail);
}

function display_signup_status ()
{

  $signups_allowed = con_signups_allowed();

  switch ($signups_allowed)
  {
    case '1':
      echo "You may signup for 1 item at this time.  &quot;Ops!&quot;,\n";
      echo "or items you are running do not count towards your total.<p>\n";
      break;

    case '2':
    case '3':
      echo 'You may signup to for ' . $signups_allowed;
      echo " items at this time.  &quot;Ops!&quot;, or items that you are\n";
      echo "running do not count towards your total.<p>\n";
      break;
      
    case UNLIMITED_SIGNUPS:
      echo "Conference schedule is now fully open!  Please signup for volunteer ";
      echo "slots, rehearsal slots, buy tickets, or check the schedule for interesting";
      echo "shows, classes, special events and more!<p>\n";
      break;

    default:
      echo "Schedule signup is not allowed at this time.\n";
      break;
  }

  // Tell users that to register for games, they should view the game schedule

  echo "To signup for volunteer slots or rehearsal options, select them on the ";
  echo "<A HREF=Schedule.php>Schedule</A> or\n";
  printf ("<A HREF=Schedule.php?action=23&type=Ops>Volunteer Opportunities</A> pages.\n",
	  LIST_GAMES);
}

/*
 * show_con_attendence
 *
 * Show the user how many are attending the con, assuming that they've got
 * ConCom priv
 */

function show_con_attendence()
{
  // Only do this if the user has ConCom priv

  if (! user_has_priv (PRIV_CON_COM))
    return;

/*  // Only do this the first time the user logs in

  if (isset ($_SESSION[SESSION_ATTENDENCE_SHOWN]))
    return;

  $_SESSION[SESSION_ATTENDENCE_SHOWN] = 1; */

  // Get a summary of users

  $sql = 'SELECT CanSignup, COUNT(*) AS Count FROM Users';
  $sql .= '  GROUP BY CanSignup ORDER BY CanSignup';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get summary of users');

  if (0 != mysql_num_rows ($result))
  {
    $summary = array ('Paid'=>0, 'Unpaid'=>0, 'Comp'=>0, 'Marketing'=>0);
    $total = 0;
    $attendees = 0;

    while ($row = mysql_fetch_object ($result))
    {
      $summary[$row->CanSignup] = $row->Count;
      $total += $row->Count;

      if (('Unpaid' != $row->CanSignup) &&
	  ('Alumni' != $row->CanSignup))
	$attendees += $row->Count;
    }

    printf ("Total Attending %s: <b>%d</b><p>\n",
	    CON_NAME,
	    $attendees);
  }

  // Get a summary of Thursday Thing attendees
  if (THURSDAY_ENABLED) 
  {
    $sql = 'SELECT COUNT(*) AS Count FROM Thursday';
    $sql .= ' WHERE Status="Paid"';
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Failed to get summary of Thursday attendees');

    $row = mysql_fetch_object($result);
    if ($row)
    {
      printf ("<b>%d</b> attendees for the %s Thursday Thing<p>\n",
	      $row->Count,
	      CON_NAME);
    }
  }
}

/*
 * show_user_homepage_status
 *
 * Fetch information about the user
 */

function show_user_homepage_status ()
{
  $sql = 'SELECT DisplayName, CanSignup, CompEventId FROM Users';
  $sql .= '  WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];

  //  echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query user information');

  // Sanity check.  There should only be a single row

  if (0 == mysql_num_rows ($result))
    return display_error ('Failed to find user information');

  $row = mysql_fetch_object ($result);

  $name = $row->DisplayName;
  $status = $row->CanSignup;

  display_header ("Welcome $name");

  $user_status = '';
  
  // Ideally we replace this with ticketing info.
/*  switch ($status)
  {
    case 'Unpaid':
    case 'Alumni':
      return status_unpaid ();

    case 'Paid':
      $user_status = 'You are paid up';
      break;

    case 'Comp':
      $user_status = "You are comp'd";

      if (0 != $row->CompEventId)
      {
	$sql = "SELECT Title FROM Events WHERE EventId=$row->CompEventId";
	$result = mysql_query ($sql);
	if (! $result)
	  return display_mysql_error ("Failure querying comp'd game");
	$comp_row = mysql_fetch_object ($result);
	if ($comp_row)
	  $user_status = "You are comp'd by <I>$comp_row->Title</I>";
      }

      break;

    case 'Marketing':
      $user_status = 'You have a gift certificate';
      break;

    case 'Rollover':
      $user_status = 'Your membership has been rolled over from a previous convention';
      break;

    case 'Vendor':
      $user_status = 'You are registered as a vendor';
      break;

    default:
      display_error ("Unknown user status $status");
      return false;
  }

  printf ("%s for %s.<P>\n",
	  $user_status,
	  CON_NAME);
*/
  // Show the user what attendence is looking like, if they've got ConCom
  // priv

  show_con_attendence();

  // Give the user the news, if any

  if ('' != $_SESSION[SESSION_CON_NEWS])
  {
    display_header (CON_NAME.' News - Last updated ' .
		    $_SESSION[SESSION_CON_LAST_UPDATED]);
    echo $_SESSION[SESSION_CON_NEWS] . "<P>\n";
  }

  display_header ('Scheduling Status');

  // If the schedule's not yet available, direct users to the list of games
  // and exit

  if (! $_SESSION[SESSION_CON_SHOW_SCHEDULE])
  {
      echo "The schedule for " . CON_NAME . " is not yet available.  You \n";
      echo "can see the list of conference and speical events being planned by clicking on \n";
      printf ("<A HREF=Schedule.php>The Events Lists</A>\n",
	      LIST_GAMES, CON_NAME);
      echo "in the Navigation menu.<p>\n";
      return true;
  }

  // Tell the user if signups are not yet available

  display_signup_status ();
  echo "<p>\n";

  // If game signup is allowed, or *has* been allowed, show the user
  // the games they are signed up for.
  //
  // If game signup is not allowed at the time, don't allow them to
  // withdraw

    // Use a single sequence number for all of the entries.  Note that
    // -1 is used as a flag that the "Withdraw" link is not supposed
    // to be displayed.  This should only be true when we've frozen
    // signups.

    if (!con_signups_allowed())
      $sequence_number = -1;
    else
      $sequence_number = increment_sequence_number ();

    show_games ($_SESSION[SESSION_LOGIN_USER_ID],
                'You are',
                'signed up',
                'Confirmed',
                $sequence_number);

    show_games ($_SESSION[SESSION_LOGIN_USER_ID],
                'You are',
                'wait listed',
                'Waitlisted',
                $sequence_number);

  return true;
}

/*
 * is_site_frozen
 *
 * Returns true if the signups are no longer allowed
 */

function is_site_frozen()
{
  $sql = 'SELECT SignupsAllowed FROM Con';
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error('Failed to get con signup status', $sql);

  $row = mysql_fetch_object($result);
  if (! $row)
    return display_error('Failed to get con signup status');

  return 'NotNow' == $row->SignupsAllowed;
}

/*
 * show_unpaid_messages
 *
 * If the user is unpaid, there are a number of possible messages:
 * - The con may be full
 * - The site may be frozen
 * - They may be able to pay
 *
 * Display the appropriate message.  Return true if homepage processing
 * should continue
 */

function show_unpaid_messages()
{
  printf ("<p><b>You are currently unpaid for %s!</b></p>", CON_NAME);

  // Check for a full con
  if (attendees_at_max())
  {
    printf ("<p>Unfortunately, %s has reached its attendance limit.\n",
	    CON_NAME);
    echo "We cannot accept any more registrations at this time.</p>\n";
    echo "<p>".NEXT_CON_INFO."</p>";

    return false;
  }

  // If the website is frozen, don't display the PayPal info
  if (is_site_frozen())
  {
    printf ("<p>We're sorry, but signups for %s are not allowed at this\n",
	    CON_NAME);
    echo "time. We can not accept payment for the convention\n";
    echo "on the website.</p>\n";
    printf ("<p>Please contact the <a href=%s>office</a> if you\n",
	    mailto_url(EMAIL_CON_CHAIR, 'Registration question'));
    printf ("still want to pay and attend %s.</p>\n", CON_NAME);
    return false;
  }

  // If we get here, the user can still pay for the con and signup for games,
  // assuming that signups have opened and games are still available
  echo "Until you pay, you won't be able to attend the conference - you'll get access ";
  echo "to any show or class you've been accepted for, but won't be able to partipate in ";
  echo "any other part of the conference, shows or vendor exhibits. ";
  echo "<a href=\"PaymentStatus.php\">Click here to pay!</a>\n";

  return true;
}

/*
 * status_unpaid
 *
 * Tell user that he has to pay to attend the con and direct him to PayPal
 */

function status_unpaid ()
{
  // Put a red box around the unpaid message in the hopes that this will
  // get the user's attention

  echo "<div style=\"border: 3px red solid; padding: 1ex; margin: 2ex;\">";
  $result = show_unpaid_messages();
  echo "</div>";

  // Give the user the news, if any
  if ('' != $_SESSION[SESSION_CON_NEWS])
  {
    display_header (CON_NAME.' News');
    echo '<p>' . $_SESSION[SESSION_CON_NEWS] . "</p>\n";
  }

  return $result;
}

/*
 * show_user_homepage_bio_info
 *
 * This function fetches the user's bio, and returns false if the user is
 * not expected to enter one
 */

function show_user_homepage_bio_info ($website="", $bio_text="", $photo="")
{
  // if it's for this user
  if ($website=="" && $bio_text=="" && $photo=="")
  {

    // All GMs are expected to enter a bio, as are all privileged users
    $should_enter_bio = user_is_gm () ||
                      (',,' != $_SESSION[SESSION_LOGIN_USER_PRIVS]);

    // If this user isn't expected to enter a bio, return now
    if (! $should_enter_bio)
      return;

    // If this user has NOT entered a bio, then issue a warning.

    $sql = 'SELECT Title, BioText, Website, PhotoSource FROM Bios WHERE UserId=';
    $sql .= $_SESSION[SESSION_LOGIN_USER_ID];

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Cannot query bio information');

    $row = mysql_fetch_object ($result);

    if (! $row)
    {
      $bio_text = '';
      $title = '';
    }
    else
    {
      $bio_text = $row->BioText;
      $title = $row->Title;
    }
    echo "<br><br>";
    display_header ('Bio');
    if ('' != $title)
        echo "Title(s): <I>$title</I><P>\n";
  
    if ('' != $row->PhotoSource)
 		$photo = $row->PhotoSource;
  
    if ('' != $row->Website)
        $website = $bio_row->Website;

    if ('' == $bio_text)
    {
      echo "<p><font color=\"red\">No bio text found.</font>  ";
      printf ("Click <A HREF=index.php?action=%d>Edit My Bio</A> to enter" .
	    " biographical information.  Bios are due by" .
	    " <b><font color=red>%s</font></b></p>.\n",
	    EDIT_BIO,
	    BIO_DUE_DATE);
	}
  } // if we had to grab user info

    
  // now display it
  display_photo($photo);
  echo "<BR><b>Website:</b> <a href=\"http://$website\">$website</a></br></br>\n";
  echo "$bio_text\n";


}

/*
 * show_user_homepage
 *
 * This implements the user's "homepage"
 */

function show_user_homepage ()
{
  // Show user status info

  if (! show_user_homepage_status ())
    return;

  // If the user is a GM, provide a link to their game(s)

  if (user_is_gm())
    show_user_homepage_gm ($_SESSION[SESSION_LOGIN_USER_ID]);


  // See if the user has bid any games

  show_user_homepage_bids ($_SESSION[SESSION_LOGIN_USER_ID]);

  // See if the user owns any shameless plugs

  show_user_homepage_plugs ($_SESSION[SESSION_LOGIN_USER_ID]);

  // Show any TShirts the user has ordered.

  if (SHOW_TSHIRTS)
    show_user_homepage_shirts ($_SESSION[SESSION_LOGIN_USER_ID]);
    
  // Show whether the user has signed up for the Dead Dog

  // Fetch whether the user is expected to submit a bio, and the text of that
  // bio, if one is available

  show_user_homepage_bio_info ();
  echo "<P>\n";
}

/*
 * add_user
 *
 * Process the user form.  Note that because processing this form can
 * result in logging a user in we can't simply call display_error()
 * to highlight our errors.  We have to gather them together and return them
 * as the result of this function so they can be displayed by
 * display_user_form() *AFTER* we've called html_begin() and setup the left
 * column.
 */

function add_user ()
{
  $update = isset ($_SESSION[SESSION_LOGIN_USER_ID]);
  
  // If this is a new registration, check the CAPTCHA
  if (!$update) {
      require_once('recaptchalib.php');
      $resp = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

      if (!$resp->is_valid) {
          // What happens when the CAPTCHA was entered incorrectly
          return "Sorry, the two words you entered for the reCAPTCHA check were incorrect.  Please try again.";
      }
  }

  // If the user asked to reset the form, just wipe out the $_POST array and return

  if (isset ($_POST['ResetForm']))
  {
    if ($update)
    {
      fill_post ($_SESSION[SESSION_LOGIN_USER_ID]);
    }
    else
    {
      foreach ($_POST as $k => $v)
	$_POST[$k] = '';
    }

    return '';
  }

  // The EMail address is our unique identifier.  Make sure we've got one that
  // looks like it's valid, and that it's unique.  Sending SPAM to make sure
  // that it's valid would be impolite

  $EMail = trim ($_POST['EMail']);
  if (1 != get_magic_quotes_gpc())
    $EMail = mysql_escape_string ($EMail);

  if (! is_valid_email_address ('EMail'))
    return "'$EMail' does not appear to be a valid EMail address";

  // Check that the EMail address isn't already being used by another player

  $sql = "SELECT UserId FROM Users WHERE EMail='$EMail'";
  $result = mysql_query ($sql);
  if (! $result)
    return "Check for EMail address $EMail failed: ". mysql_error ();

  $email_in_use = FALSE;
  if (! $update)
    $email_in_use = (0 != mysql_num_rows ($result));
  else
  {
    if (1 == mysql_num_rows ($result))
    {
      $row = mysql_fetch_object ($result);

      $email_in_use = ($row->UserId != $_SESSION[SESSION_LOGIN_USER_ID]);
    }
  }

  // Start by assuming that the form is OK

  $errors = '';

  if ($email_in_use)
    $errors = "Another user has already registered with an EMail address of \"$EMail\".  Please choose a different EMail address.<P>\n";

  // Make sure we got the required information

  $Password = trim ($_POST['Password']);
  $HashedPassword = md5 ($Password);

  if (0 == strlen ($Password))
    $errors .= "You must enter the password to verify your identity<P>\n";

  if (strlen ($Password) < 8)
    $errors .= "Passwords must be at least 8 characters long<P>\n";

  // If this is an update, see if the password is correct

  if ($update)
  {
    $sql = 'SELECT EMail FROM Users';
    $sql .= ' WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= " AND HashedPassword='$HashedPassword'";

    $result = mysql_query ($sql);
    if (! $sql)
      return 'Password check query failed: ' . mysql_error();

    if (1 != mysql_num_rows ($result))
      $errors .= "Incorrect password<P>\n";
  }

  if (! $update)
  {
    $Password2 = trim ($_POST['Password2']);
    if ($Password != $Password2)
    {
      $_POST['Password'] = '';
      $_POST['Password2'] = '';
      $errors .= "The passwords do not match<P>\n";
    }
  }

  if ('' == trim ($_POST['FirstName']))
    $errors .= "You must specify a first name<P>\n";

  if ('' == trim ($_POST['LastName']))
    $errors .= "You must specify a last name<P>\n";

  $BirthYear = trim ($_POST['BirthYear']);
  if ($BirthYear)
    if (! is_numeric ($BirthYear))
      $errors .= "Invalid value specified for Year of Birth<P>\n";

  // If anything was wrong, abort now

  if ('' != $errors)
    return $errors;

  // Since all of the validations passed, register the user (or update his record)

  if ($update)
    $sql = 'UPDATE Users SET ';
  else
    $sql = 'INSERT Users SET ';

  if ( strlen($_POST['StageName']) > 0 )
    $DisplayName = $_POST['StageName'];
  else
    $DisplayName = $_POST['FirstName']." ".$_POST['LastName'];


  $sql .= build_sql_string ('FirstName', '', FALSE);
  $sql .= build_sql_string ('LastName');
  $sql .= build_sql_string ('StageName');
  $sql .= build_sql_string ('DisplayName', $DisplayName);
  $sql .= build_sql_string ('Nickname');
  $sql .= build_sql_string ('EMail');
  $sql .= build_sql_string ('BirthYear');
  $sql .= build_sql_string ('Gender');
  $sql .= build_sql_string ('Address1');
  $sql .= build_sql_string ('Address2');
  $sql .= build_sql_string ('City');
  $sql .= build_sql_string ('State');
  $sql .= build_sql_string ('Zipcode');
  $sql .= build_sql_string ('Country');
  $sql .= build_sql_string ('DayPhone');
  $sql .= build_sql_string ('EvePhone');
  $sql .= build_sql_string ('BestTime');
  $sql .= build_sql_string ('HowHeard');
  $sql .= build_sql_string ('PreferredContact');
  if ($update)
    $sql .= ', ModifiedBy=' . $_SESSION[SESSION_LOGIN_USER_ID];
  else
    $sql .= ', ModifiedBy=UserId';
  $sql .= ', Modified=NULL';

  if ($update)
  {
    $sql .= ' WHERE UserId = ' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= " AND HashedPassword='$HashedPassword'";
  }
  else
  {
    $sql .= build_sql_string ('HashedPassword', $HashedPassword);
    $sql .= ', LastLogin=NULL, Created=NULL';
  }

  //echo "sql: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return 'Insert into Users table failed: ' . mysql_error ();

  if ($update)
  {
    $UserId=$_SESSION[SESSION_LOGIN_USER_ID];
  }
  else
  {
    // Update the session variables

    $UserId = mysql_insert_id ();
    $_SESSION[SESSION_LOGIN_USER_ID] = $UserId;
    $_SESSION[SESSION_LOGIN_USER_PRIVS] = ',,';
    $_SESSION[SESSION_LOGIN_USER_DISPLAY_NAME] = $DisplayName;

    $name = $_POST['FirstName'] . ' ' . $_POST['LastName'];
    $_SESSION[SESSION_LOGIN_USER_NAME] = trim ($name);
    $_SESSION[SESSION_LOGIN_USER_EMAIL] = $_POST['EMail'];

    // Users who've just registered can't have paid or be GMs
    $_SESSION[SESSION_LOGIN_USER_GM] = 0;

    if (array_key_exists (SESSION_REFERRER_ID, $_SESSION))
    {
      $sql = "UPDATE Referrers SET UserId=$UserId, NewUser=1";
      $sql .= ' WHERE ReferrerId=' . $_SESSION[SESSION_REFERRER_ID];

      $result = mysql_query ($sql);
      if (! $result)
	display_mysql_error ('Attempt to update referrer record failed', $sql);

      session_unregister (SESSION_REFERRER_ID);
    }
    
  }

  return $UserId;
}

/*
 * fill_post
 *
 * Fill the $_POST array with data from the User's table for the specified
 * UserId
 */

function fill_post ($UserId)
{
  $sql = "SELECT * FROM Users WHERE UserId=$UserId";

  //  print ($sql . "<p>\n");

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');

  if (1 != mysql_num_rows ($result))
    return display_error ("Failed to find entry for UserId $UserId");

  $row = mysql_fetch_array ($result, MYSQL_ASSOC);

  foreach ($row as $key => $value)
    $_POST[$key] = $value;

  return TRUE;
}

function show_con_price ($now)
{
  $one_day = 60 * 60 * 24;

  // Figure out where we are in the sequence

  $k = 0;
  while (get_con_price ($k++, $price, $start_date, $end_date))
  {
    if (0 == $end_date)
      break;
    if ($now < $end_date)
      break;
  }

  // If we're after the last cutoff, just display the final price.  Otherwise,
  // show the list

  if (0 == $end_date)
    printf ("<h1>%s is only $%s!</h1>\n",
	    CON_NAME,
	    $prices[count($prices)-1]);
  else
  {
    echo "<h1>Save BIG if you pay today!</h1>\n";
    printf ("<h2>%s is only $%d.00!</h2>\n",
	    CON_NAME,
	    $price);

    while (1)
    {
      get_con_price ($k++, $price, $start_date, $end_date);
      if (0 == $end_date)
	break;

      printf ("$%d.00 after %s<br>\n",
	      $price,
	      strftime ('%d-%b-%Y', $start_date));
    }

    printf ("$%d.00 after %s or at the door.<p>\n",
	    $price,
	    strftime ('%d-%b-%Y', $start_date));
  }

  $reg_close = strftime ('%d-%b-%Y', parse_date (REGISTRATION_CLOSE));
  echo "Online registration will close $reg_close<p>\n";
}

/*
 * display_user_form
 *
 * Display a form for the user to fill out to register for Intercon
 */

function display_user_form ($returning_alumni, $errors='')
{
  // If we've got errors from a previous attempt to add a user, display them
  // now

  if ('' != $errors)
    display_error ($errors);

  $update = isset ($_SESSION[SESSION_LOGIN_USER_ID]);

  if ($returning_alumni)
  {
    $name = trim ($_POST['FirstName'] . ' ' . $_POST['LastName']);

    display_header ("Welcome back $name");
    printf ("Please take a moment to update your user information for %s<P>\n",
	    CON_NAME);
  }
  else
  {
    if ($update)
      $text = 'Update Your User Information';
    else
    {
      // Get today's date.  If the con is over, warn the user

      $now = time ();
      if ($now > parse_date (CON_OVER))
	$text = CON_NAME . ' is over';
      else
      {
	$text = 'Register for Intercon';
	show_con_price ($now);
      }
    }
    display_header ($text);
  }

  echo "<p>Your contact information will be made available to GBE\n";
  echo "staff and the stage managers of any shows you are a part of.  Supplying the\n";
  echo "contact information is optional.  If you have concerns about\n";
  echo "sharing this information, please do not enter it.  But if you do\n";
  echo "not provide it, GBE staff may not be able to send you Expo-related\n";
  echo "materials.</p>\n";

  print ("<form method=\"post\" action=\"index.php\">\n");
  form_add_sequence ();
  print ("<input type=\"hidden\" name=\"action\" value=" . ADD_USER . ">\n");
  print ("<table border=\"0\">\n");

  if ($update)
  {
    $_POST['Password'] = '';
    $_POST['Password2'] = '';
  }

  if (! $update)
  {
    form_password (30, 'Password', '', 0, TRUE);
    form_password (30, 'Re-Enter Password', 'Password2', 0, TRUE);
    print ("  <tr><td colspan=\"2\">&nbsp;</td></tr>\n");
  }

  form_text (30, 'First Name', 'FirstName', 0, TRUE);
  form_text (30, 'Last Name', 'LastName', 0, TRUE);
  form_text (64, 'Stage Name', 'StageName', 0);
  form_text (64, 'EMail', '', 0, TRUE);
//  form_text (30, 'Nickname');
//  form_birth_year_and_gender ('BirthYear', 'Gender');
  form_text (64, 'Address', 'Address1');
  form_text (64, '', 'Address2');
  form_text (64, 'City');
  form_text (30, 'State / Province', 'State');
  form_text (10, 'Zipcode');
  form_text (30, 'Country');
  form_text (20, 'Daytime Phone', 'DayPhone');
  form_text (20, 'Evening Phone', 'EvePhone');
  form_text (64, 'Best Time to Call', 'BestTime', 128);
  form_preferred_contact ('Preferred Contact', 'PreferredContact');
  form_text (64, 'How did you hear about ' . CON_NAME . '?', 'HowHeard');
  
  if (! $update) {
      require_once('recaptchalib.php');
      echo "<tr><td></td><td>";
      echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
      echo "</td></tr>\n";
  }

  if (! $update)
    $button_title = 'Register Now!';
  else
  {
    $button_title = 'Update';
    echo "  <tr><td colspan=\"2\">\n";
    echo "    &nbsp;<BR>Enter your current password to verify your identity.\n";
    printf ("    Use the <a href=\"index.php?action=%d\">Change Password</a>\n",
	    CHANGE_PASSWORD);
    echo "    page to change your password.\n";
    echo "  </td></tr>\n";
    form_password (30, 'Password', '', 0, TRUE);
  }

  form_submit2 ($button_title, 'Reset Form', 'ResetForm');

  echo "</table>\n";
  echo "</form>\n";

  echo "<p><font color=\"red\">*</font> indicates a required field</p>\n";
}

/*
 * form_comped_for_game
 */

function form_comped_for_game ()
{
  if (array_key_exists ('CompEventId', $_POST))
    $game_id = intval (trim ($_POST['CompEventId']));
  else
    $game_id = 0;

  $sql = 'SELECT EventId, Title FROM Events';
  $sql .= '  WHERE SpecialEvent=0';
  $sql .= '  ORDER BY Title';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for game list failed');

  echo "  <tr valign=\"top\">\n";
  echo "    <td nowrap align=\"right\">Comp'd For:</td>\n";
  echo "    <td>\n";
  echo "      <select Name=\"CompEventId\" size=\"1\">\n";
  printf ("        <option value=\"0\"%s>None</option>\n",
	  (0 == $game_id) ? ' selected' : '');
  while ($row = mysql_fetch_object ($result))
  {
    printf ("        <option value=%d%s>$row->Title</option>\n",
	    $row->EventId,
	    ($row->EventId == $game_id) ? ' SELECTED' : '');
  }
  echo "      </select\n";
  echo "    </td>\n";
  echo "  </tr>\n";

  mysql_free_result ($result);

  return TRUE;
}

function priv_checkbox ($privs, $value, $text)
{
  $checked = '';

  if (is_array ($privs))
    if (array_key_exists ($value, $privs))
      $checked = $privs[$value];

  echo "            <INPUT TYPE=CHECKBOX NAME=Priv[] VALUE=$value $checked>$text<BR>\n";
}

function user_form_section ($text)
{
  echo "  <tr>\n";
  echo "    <td colspan=\"2\">&nbsp;<br><font size=\"+1\">$text</font></td>\n";
  echo "  </tr>\n";
}

/*
 * display_user_form_for_others
 *
 * Display a form for the user to fill out for a different user
 */

function display_user_form_for_others ()
{
  $UserId = trim ($_REQUEST['UserId']);
  $UserId = intval($UserId);

  // Make sure that only privileged users get here

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error ();

  if (0 == $UserId)
    $text = 'Register a New User';
  else
  {
    if (empty ($_POST['EMail']))
    {
      $sql = 'SELECT * FROM Users WHERE UserId=' . $UserId;
      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Query for UserId $UserId failed");

      $row = mysql_fetch_assoc ($result);

      //  dump_array ("row", $row);

      foreach ($row as $k => $v)
	$_POST[$k] = $v;

      // Convert payment amount to dollars

      $_POST['PaymentAmount'] = $_POST['PaymentAmount']/100;
    }

    $text = 'Update Registration for ';
    $text .= $_POST['DisplayName'];
  }

  $seq = increment_sequence_number();

  display_header ($text);

  echo "<P><FONT COLOR=RED>*</FONT> Indicates a required field\n";

  print ("<FORM METHOD=POST ACTION=index.php>\n");
  form_add_sequence ($seq);
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", PROCESS_EDIT_USER);
  printf ("<INPUT TYPE=HIDDEN NAME=UserId VALUE=%d>\n", $UserId);
  print ("<TABLE BORDER=0>\n");

  user_form_section ('User Info');

  //  form_text (30, 'Password', '', 0, TRUE);
  form_text (30, 'First Name', 'FirstName', 0, TRUE);
  form_text (30, 'Last Name', 'LastName', 0, TRUE);
  form_text (64, 'EMail', '', 0, TRUE);
  form_text (64, 'Stage Name', 'StageName',0);
  //form_birth_year_and_gender ('BirthYear', 'Gender');
  form_text (64, 'Address', 'Address1');
  form_text (64, '', 'Address2');
  form_text (64, 'City');
  form_text (30, 'State / Province', 'State');
  form_text (10, 'Zipcode');
  form_text (30, 'Country');
  form_text (20, 'Daytime Phone', 'DayPhone');
  form_text (20, 'Evening Phone', 'EvePhone');
  form_text (64, 'Best Time to Call', 'BestTime', 128);
  form_preferred_contact ('Preferred Contact', 'PreferredContact');
  form_text (64, 'How did you hear about ' . CON_NAME . '?', 'HowHeard');

  $privs = 0;
  if ($UserId != 0)
  {
    if (array_key_exists ('Priv', $_POST))
    {
      $privs = array_flip (explode (',', $_POST['Priv']));
      foreach ($privs as $k => $v)
	$privs[$k] = 'CHECKED';
    }
  }

  echo "  <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  echo "  <tr>\n";
  echo "    <td align=\"right\" valign=\"top\">Privileges:</td>\n";
  echo "    <td>\n";
  echo "      <table>\n";
  echo "        <tr valign=\"top\">\n";
  echo "          <td>\n";
  priv_checkbox ($privs, PRIV_BID_COM,    'Bid Committee');
  priv_checkbox ($privs, PRIV_BID_CHAIR,  'Bid Comm. Chair');
  priv_checkbox ($privs, PRIV_CON_COM,    'Con Committee');
  priv_checkbox ($privs, PRIV_GM_LIAISON, 'GM Liaison');
  echo "          </td>\n";
  echo "          <td>\n";
  priv_checkbox ($privs, PRIV_OUTREACH,   'Outreach');
  priv_checkbox ($privs, PRIV_PRECON_BID_CHAIR, 'Pre-Con Bid Chair');
  priv_checkbox ($privs, PRIV_PRECON_SCHEDULING, 'Pre-Con Scheduling');
  priv_checkbox ($privs, PRIV_REGISTRAR,  'Registrar');
  echo "          </td>\n";
  echo "          <td>\n";
  priv_checkbox ($privs, PRIV_SCHEDULING, 'Scheduling');
  priv_checkbox ($privs, PRIV_STAFF,      'Website Staff');
  priv_checkbox ($privs, PRIV_SHOW_COM,    'Show Committee');
  priv_checkbox ($privs, PRIV_SHOW_CHAIR,  'Show Comm. Chair');
  echo "          </td>\n";
  echo "        </tr>\n";
  echo "      </table>\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "  <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  echo "  <tr>\n";
  echo "    <td align=\"right\" valign=\"top\">Mail Privileges:</td>\n";
  echo "    <td>\n";
  echo "      <table>\n";
  echo "        <tr valign=\"top\">\n";
  echo "          <td>\n";
  priv_checkbox ($privs, PRIV_MAIL_ALL,       'Mail to All');
  //  priv_checkbox ($privs, PRIV_MAIL_ALUMNI,    'Mail to Alumni');
  priv_checkbox ($privs, PRIV_MAIL_ATTENDEES, 'Mail to Attendees');
  priv_checkbox ($privs, PRIV_MAIL_GMS,       'Mail to GMs');
  echo "          </td>\n";
  echo "          <td>\n";
  priv_checkbox ($privs, PRIV_MAIL_VENDORS,   'Mail to Vendors');
  priv_checkbox ($privs, PRIV_MAIL_UNPAID,    'Mail to Unpaid');
  echo "          </td>\n";
  echo "        </tr>\n";
  echo "      </table>\n";
  echo "    </td>\n";
  echo "  </tr>\n";

  user_form_section(CON_NAME . ' Payment Info');

  $PaidChecked = '';
  $CompChecked = '';
  $MktChecked =  '';
  $VendChecked = '';
  $UnpaidChecked = '';
  $AlumniChecked = '';
  $RollChecked = '';

  if (! array_key_exists ('CanSignup', $_POST))
    $UnpaidChecked = 'CHECKED';
  else
  {
    switch ($_POST['CanSignup'])
    {
      case 'Alumni':    $AlumniChecked = 'CHECKED'; break;
      case 'Paid':      $PaidChecked = 'CHECKED'; break;
      case 'Comp':      $CompChecked = 'CHECKED'; break;
      case 'Marketing': $MktChecked =  'CHECKED'; break;
      case 'Rollover':  $RollChecked = 'CHECKED'; break;
      case 'Vendor':    $VendChecked = 'CHECKED'; break;
      default:          $UnpaidChecked = 'CHECKED'; break;
    }
  }

  print ("  <TR>\n");
  print ("    <TD ALIGN=RIGHT valign=top>Status:</TD>\n");
  print ("    <TD>\n");
  print ("    <INPUT TYPE=RADIO NAME=CanSignup VALUE=\"Alumni\" $AlumniChecked>Alumni\n");
  print ("    &nbsp;&nbsp;&nbsp;\n");
  print ("    <INPUT TYPE=RADIO NAME=CanSignup VALUE=\"Unpaid\" $UnpaidChecked>Not Paid\n");
  print ("    &nbsp;&nbsp;&nbsp;\n");
  print ("    <INPUT TYPE=RADIO NAME=CanSignup VALUE=\"Paid\" $PaidChecked>Paid\n");
  print ("    &nbsp;&nbsp;&nbsp;\n");
  print ("    <INPUT TYPE=RADIO NAME=CanSignup VALUE=\"Comp\" $CompChecked>Comp\n");
  print ("    <br>\n");
  print ("    <INPUT TYPE=RADIO NAME=CanSignup VALUE=\"Marketing\" $MktChecked>Marketing\n");
  print ("    &nbsp;&nbsp;&nbsp;\n");
  print ("    <INPUT TYPE=RADIO NAME=CanSignup VALUE=\"Vendor\" $VendChecked>Vendor\n");
  print ("    &nbsp;&nbsp;&nbsp;\n");
  print ("    <input type=radio name=CanSignup value=\"Rollover\" $RollChecked>Rollover\n");
  print ("    </TD>\n");
  print ("  </TR>\n");
  form_text (2, 'Payment Amount $', 'PaymentAmount');
  form_text (64, 'Payment Note', 'PaymentNote', 128);
  //form_comped_for_game ();

  form_submit ('Update');

  echo "</table>\n";
  echo "</form>\n";

  // Fetch info on updating user

  if ($UserId != 0)
  {
    $Modified = timestamp_to_datetime ($_POST['Modified']);
    $LastLogin = timestamp_to_datetime ($_POST['LastLogin']);
    $Created = timestamp_to_datetime ($_POST['Created']);
    
    // If the ModifiedBy column is 0, the user's record hasn't ever been
    // modified

    if (0 == $_POST['ModifiedBy'])
      echo "<P>Created $Created\n";
    else
    {
      $sql = 'SELECT DisplayName FROM Users WHERE UserId=' .
	     mysql_real_escape_string ($_POST['ModifiedBy']);
      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Query for modifying UserId $UserId failed");

      $row = mysql_fetch_object ($result);

      echo "<P>Last modified by $row->DisplayName, $Modified\n";
    }

    $StatusModified = timestamp_to_datetime ($_POST['CanSignupModified']);

    if (0 != $_POST['CanSignupModifiedId'])
    {
      $sql = 'SELECT DisplayName FROM Users WHERE UserId=' .
	     mysql_real_escape_string ($_POST['CanSignupModifiedId']);
      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Query for modifying UserId $UserId failed");

      $row = mysql_fetch_object ($result);

      echo "<P>Payment status last modified by $row->DisplayName, $StatusModified\n";
      echo "<BR>Note: This will be the user himself if he paid using PayPal<P>\n";
    }
  }

  if (0 != $UserId)
  {
    // Show the games the player is registered for, if any, as well as any
    // games he/she is GMing

    echo "<P>\n";

    $name = trim ($_POST['DisplayName']);

    show_games ($UserId, "$name is", 'signed up', 'Confirmed');
    show_games ($UserId, "$name is", 'wait listed', 'Waitlisted');

    show_gm_games ($UserId, $name);

    // If signups are allowed, give the user the option of removing this
    // player from all games.
    //
    // If signups haven't opened yet, there shouldn't be anything to remove.
    // If signups are frozen, withdrawals aren't allowed.

    if (0 != con_signups_allowed())
    {
      echo "<table>\n  <tr>\n    <td bgcolor=\"ffcccc\">\n";
      printf ("<a href=index.php?action=%d&UserId=%d&Seq=%d>%s</a>\n",
	      WITHDRAW_USER_FROM_ALL_GAMES,
	      $UserId,
	      $seq,
	      'Withdraw User From ALL Games');
      echo "    </td>\n  </tr>\n</table>\n";
    }
  }
}

/*
 * process_edit_user
 *
 * Process the user form.
 */

function process_edit_user ()
{
  $errors = '';
  $UserId = intval ($_POST['UserId']);

  // Make sure that only privileged users get here

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error ();

  // Check for sequence errors

  if (out_of_sequence ())
    return display_sequence_error (false);

  // The EMail address is our unique identifier.  Make sure we've got one that
  // looks like it's valid, and that it's unique.  Sending SPAM to make sure
  // that it's valid would be impolite

  $EMail = trim ($_POST['EMail']);
  if (! is_valid_email_address ('EMail'))
    return "'$EMail' does not appear to be a valid EMail address";

  if (1 == get_magic_quotes_gpc())
    $EMail = stripslashes ($EMail);

  $EMail = mysql_escape_string ($EMail);

  // Check that the EMail address isn't already being used by another player

  $sql = "SELECT UserId, CanSignup FROM Users WHERE EMail='$EMail'";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Check for EMail address $EMail failed");

  $old_CanSignup = '';
  $email_in_use = FALSE;
  if (0 == $UserId)
    $email_in_use = (0 != mysql_num_rows ($result));
  else
  {
    if (1 == mysql_num_rows ($result))
    {
      $row = mysql_fetch_object ($result);
      $email_in_use = ($row->UserId != $UserId);
      $old_CanSignup = $row->CanSignup;
    }
  }

  if ($email_in_use)
    return display_error ("Another user has already registered with an EMail address of '$EMail'.  Please choose a different EMail address.");

  $form_ok = validate_string ('FirstName', 'First Name');
  $form_ok &= validate_string ('LastName', 'Last Name');
  $form_ok &= validate_email ('EMail');

  $PaymentAmount = intval ($_POST['PaymentAmount']) * 100;

  $BirthYear = intval (trim ($_POST['BirthYear']));

  // If anything was wrong, abort now

  if (! $form_ok)
    return FALSE;

  // Since all of the validations passed, register the user (or update his record)

  if (0 == $UserId)
    $sql = 'INSERT Users SET ';
  else
    $sql = 'UPDATE Users SET ';

  if (! array_key_exists ('Priv', $_REQUEST))
    $Priv = '';
  else
  {
    if (count ($_REQUEST['Priv']) > 1)
      $Priv = implode (',', $_REQUEST['Priv']);
    else
      $Priv = $_REQUEST['Priv'][0];
  }

  //  echo "Priv: $Priv<P>\n";
  if ( strlen($_REQUEST['StageName']) > 0 )
    $DisplayName = $_REQUEST['StageName'];
  else
    $DisplayName = $_REQUEST['FirstName']." ".$_REQUEST['LastName'];


  $sql .= build_sql_string ('FirstName', '', FALSE);
  $sql .= build_sql_string ('LastName');
  $sql .= build_sql_string ('StageName');
  $sql .= build_sql_string ('DisplayName',$DisplayName);
  $sql .= build_sql_string ('Nickname');
  $sql .= build_sql_string ('EMail');
  $sql .= build_sql_string ('BirthYear');
  $sql .= build_sql_string ('Gender');
  $sql .= build_sql_string ('Address1');
  $sql .= build_sql_string ('Address2');
  $sql .= build_sql_string ('City');
  $sql .= build_sql_string ('State');
  $sql .= build_sql_string ('Zipcode');
  $sql .= build_sql_string ('Country');
  $sql .= build_sql_string ('DayPhone');
  $sql .= build_sql_string ('EvePhone');
  $sql .= build_sql_string ('BestTime');
  $sql .= build_sql_string ('HowHeard');
  $sql .= build_sql_string ('PreferredContact');
  $sql .= build_sql_string ('Priv', $Priv);
  $sql .= build_sql_string ('CanSignup');
  $sql .= build_sql_string ('PaymentAmount', $PaymentAmount);
  $sql .= build_sql_string ('PaymentNote');
  $sql .= ', ModifiedBy=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= ', Modified=NULL';

  if ('Comp' != $_POST['CanSignup'])
    $sql .= ', CompEventId=0';
  else
    $sql .= build_sql_string ('CompEventId');

  if (('' != $_POST['CanSignup']) && ($old_CanSignup != $_POST['CanSignup']))
  {
    $sql .= ', CanSignupModified=NULL';
    $sql .= ', CanSignupModifiedId=' . $_SESSION[SESSION_LOGIN_USER_ID];
  }

  if (0 != $UserId)
    $sql .= ' WHERE UserId = ' . $UserId;

  //  printf ("Insert: %s<p>\n", $sql);

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Insert into Users table failed');

  return true;
}

function display_user_information ($user_id)
{
  if (empty ($user_id))
    return display_error ('UserId not specified');

  // Fetch the information about the logged in user

  $sql = 'SELECT * FROM Users WHERE UserId=' . $user_id . ';';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');

  // We should match precisely 1 user

  if (1 != mysql_num_rows ($result))
    return display_error ('Failed to find entry for user ' . $user_id);

  $row = mysql_fetch_object ($result);

  if ('' != $row->EMail)
    $EMail = "<A HREF=mailto:$row->EMail>$row->EMail</A>";
  else
    $EMail = '';

  print ("<TABLE BORDER=0>\n");
  display_text_info ('First Name', $row->FirstName);
  display_text_info ('Last Name', $row->LastName);
  display_text_info ('Stage Name', $row->StageName);
//  display_text_info ('Nickname', $row->Nickname);
  display_text_info ('Age', birth_year_to_age ($row->BirthYear));
  display_text_info ('Gender', $row->Gender);
  $address = $row->Address1;
  if ('' != $row->Address2)
    $address .= ', ' . $row->Address2;
  display_text_info ('Address', $address);
  display_text_info ('City', $row->City);
  display_text_info ('State / Province', $row->State);
  display_text_info ('Zipcode', $row->Zipcode);
  display_text_info ('Country', $row->Country);
  display_text_info ('EMail', $EMail);
  display_text_info ('Daytime Phone', $row->DayPhone);
  display_text_info ('Evening Phone', $row->EvePhone);
  display_text_info ('Best Time to Call', $row->BestTime);
  display_text_info ('Preferred Contact', $row->PreferredContact);
  display_text_info ('Heard about Con', $row->HowHeard);

  //  $sql = "SELECT DATE_FORMAT(Created, '%a, %d-%b-%Y %H:%i') AS Created,";
  //  $sql .= " DATE_FORMAT(LastLogin, '%a, %d-%b-%Y %H:%i') AS LastLogin,";
  //  $sql .= " DATE_FORMAT(Modified, '%a, %d-%b-%Y %H:%i') AS Modified";
  //  $sql .= ' FROM Users WHERE UserId=' . $user_id . ';';

  //  echo 'query: ' . $sql;

  //  $result = mysql_query ($sql);
  //  if (! $result)
  //  {
  //    echo '<-- Cannot execute query: ' . mysql_error ();
  //    return 'Cannot execute query: ' . mysql_error();
  //  }
  
  //  $row = mysql_fetch_object ($result);

  //  display_text_info ('Created', $row->Created);
  //  display_text_info ('Last Login', $row->LastLogin);
  //  display_text_info ('Last Modified', $row->Modified);

  print ("</TABLE>\n");
  return false;
}

/*
 * display_password_form
 *
 * Display a form allowing users to request their passwords be set to a
 * random string and mailed to them
 */

function display_password_form ()
{
  echo "To reset the password for your account with ".CON_NAME.", enter the\n";
  echo "e-mail address you registered with.  A new password will be\n";
  echo "randomly generated and the new password will be mailled to\n";
  echo "that account.<br>&nbsp;\n";

  echo "<FORM METHOD=POST ACTION=index.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%s>\n", SEND_PASSWORD);
  echo "<TABLE BORDER=0>\n";
  form_text (30, 'e-mail', 'EMail');
  form_submit ('Send Password');
  echo "</TABLE>\n";
  echo "</FORM>\n";

  echo "<P>If you cannot access that e-mail account or do not receive your\n";
  echo "new password shortly after you request it, contact the\n";
  printf ("<a href=%s>webmaster</a>.\n", mailto_url(EMAIL_WEBMASTER, ''));

  /*echo "<P><B>AOL Users:</B> We are experiencing difficulties sending\n";
  echo "mail to AOL accounts.  We expect to have this fixed soon, but if\n";
  echo "you do not receive your password shortly after requesting it, send\n";
  echo "mail to the\n";
  
  printf ("<a href=%s>Registrar</a> or the\n",
	  mailto_url(EMAIL_REGISTRAR, 'Password reset problem'));
  printf ("<a href=%s>Webmaster</a>\n",
	  mailto_url(EMAIL_WEBMASTER, 'Password reset problem'));
  echo "for assistance.\n";*/
}

/*
 * process_password_request
 *
 * Process a request to reset a user's password
 */

function process_password_request ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (false);

  // Lookup the email address in the database.  It's the key to the user's
  // record.

  $EMail = trim ($_POST['EMail']);
  if (1 != get_magic_quotes_gpc())
    $EMail = mysql_escape_string ($EMail);

  $sql = "SELECT UserId FROM Users Where EMail='$EMail'";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find user with EMail address $EMail");

  $row = mysql_fetch_object ($result);
  $UserId = $row->UserId;

  // Generate a new random password
  
  $NewPassword = '';
  for ($i=0; $i<8; $i++)
  {
    $ascii = rand(ord('a'), ord('z'));
    $NewPassword .= chr($ascii);
  }
    
  // Reconnect to the database with forced admin privileges in order
  // to reset user's password
  
  intercon_db_connect (1);

  $sql = 'UPDATE Users SET ';
  $sql .= build_sql_string ('HashedPassword', md5($NewPassword), FALSE);
  $sql .= ' WHERE UserId=' . $UserId;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');
 
  // Send the user his password

  if (! intercon_mail ($EMail,
		       'Your '.CON_SHORT_NAME.' account',
		       "The password to your ".CON_NAME." account has been reset to a " .
                       "random string.  The new password is $NewPassword."))
    return display_error ('Attempt to send mail failed');
 
  echo "Your new password has been mailed to your EMail account";

  return TRUE;
}

/*
 * display_password_change_form
 *
 * Display a form allowing users to change their passwords
 */

function display_password_change_form ()
{
  echo "<FORM METHOD=POST ACTION=index.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%s>\n",
	  PROCESS_PASSWORD_CHANGE);
  echo "<TABLE BORDER=0>\n";
  form_password (30, 'Current Password', 'Password');
  echo "  <TR><TD>&nbsp;</TD></TR>\n";
  form_password (30, 'New Password', 'NewPassword1');
  form_password (30, 'Re-Enter New Password', 'NewPassword2');

  form_submit ('Change Password');
  echo "</TABLE>\n";
  echo "</FORM>\n";
}

/*
 * process_password_change_request
 *
 * Process a request to change a user's password
 */

function process_password_change_request ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (false);

  // Extract the information from the form and clear it, for security reasons

  $Password = trim ($_POST['Password']);
  $HashedPassword = md5 ($Password);

  $NewPassword1 = trim ($_POST['NewPassword1']);
  $NewPassword2 = trim ($_POST['NewPassword2']);

  // Make sure the user will have to re-enter the passwords if the form is redisplayed

  $_POST['Password'] = '';
  $_POST['NewPassword1'] = '';
  $_POST['NewPassword2'] = '';

  // Make sure we were given a password

  if ('' == $Password)
    return display_error ('You must enter your current password to verify your identity');

  // Make sure we got two copies of the new password

  if (strlen ($NewPassword1) < 8)
    return display_error ('Passwords must be at least 8 characters long');

  if ($NewPassword1 != $NewPassword2)
    return display_error ('The new passwords do not match');

  // See if the password is correct for this user

  $sql = 'SELECT UserId FROM Users';
  $sql .= ' WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
  $sql .= " AND HashedPassword='$HashedPassword'";

  $result = mysql_query ($sql);
  if (! $sql)
    return display_mysql_error ('Password check query failed');

  if (1 != mysql_num_rows ($result))
    return display_error ('Incorrect password');

  // I guess it's OK.  Update the password in the database

  $HashedPassword = md5 ($NewPassword1);

  $sql = 'UPDATE Users SET ';
  $sql .= build_sql_string ('HashedPassword', $HashedPassword, FALSE);
  $sql .= ' WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Attempt to set the new password failed');

  echo "Your password has been changed<P>\n";

  return TRUE;
}

/*
 * select_user_to_edit
 *
 * Display the list of users and let the user pick one to edit
 */

function select_user_to_edit ()
{
  // Make sure that only privileged users get here

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error ();

  if (array_key_exists ('OrderBy', $_REQUEST))
    $OrderBy = intval (trim($_REQUEST ['OrderBy']));
  else
    $OrderBy = ORDER_BY_NAME;

  // Get the status for the admin so we can subtract him from the totals

  $sql = 'SELECT CanSignup FROM Users WHERE UserId=1';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query failed for admin status', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('Failed to find admin!');

  $admin_status = $row->CanSignup;

  // Get a summary of users

  $sql = 'SELECT CanSignup, COUNT(*) AS Count FROM Users';
  $sql .= '  GROUP BY CanSignup ORDER BY CanSignup';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get summary of users');

  if (0 != mysql_num_rows ($result))
  {
    $summary = array ('Paid'=>0, 'Unpaid'=>0, 'Comp'=>0, 'Marketing'=>0);
    $total = 0;
    $attendees = 0;

    while ($row = mysql_fetch_object ($result))
    {
      $summary[$row->CanSignup] = $row->Count;
      $total += $row->Count;

      if (('Unpaid' != $row->CanSignup) &&
	  ('Alumni' != $row->CanSignup))
	$attendees += $row->Count;
    }

    // Subtract the admin user

    $total--;
    $summary[$admin_status]--;

    //    echo "Admin status: $admin_status<BR>\n";

    display_header ('Summary:');

    foreach ($summary as $key => $value)
      echo "$key: <B>$value</B> &nbsp; &nbsp; &nbsp; \n";

    printf ("<BR>Total Attending %s: <B>%d</B> (excludes Unpaid and Alumni)<BR>\n",
	    CON_NAME,
	    $attendees);
    echo "Total Users: <B>$total</B><P>\n";
  }

  // Display the sorting options

  $name_selected = '';
  $create_selected = '';
  $payment_selected = '';
  $modified_selected = '';
  $login_selected = '';

  switch ($OrderBy)
  {
    case ORDER_BY_LAST_LOGIN:            $login_selected = 'SELECTED';   break;
    case ORDER_BY_LAST_MODIFIED:         $modified_selected = 'SELECTED';break;
    case ORDER_BY_PAYMENT_STATUS_CHANGE: $payment_selected = 'SELECTED'; break;
    case ORDER_BY_CREATION:              $create_selected = 'SELECTED';  break;
    default:
    case ORDER_BY_NAME:                  $name_selected = 'SELECTED';    break;
  }

  echo "<FORM METHOD=POST ACTION=index.php>\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", SELECT_USER_TO_EDIT);
  echo "<SELECT NAME=OrderBy SIZE=1>\n";
  printf ("  <option value=%d $name_selected>Order by Name</option>\n",
	  ORDER_BY_NAME);
  printf ("  <option value=%d $create_selected>Order by Creation Date</option>\n",
	  ORDER_BY_CREATION);
  printf ("  <option value=%d %s>Order by Payment Status Change</option>\n",
	  ORDER_BY_PAYMENT_STATUS_CHANGE,
	  $payment_selected);
  printf ("  <option value=%d %s>Order by Last Login</option>\n",
	  ORDER_BY_LAST_LOGIN,
	  $login_selected);
  printf ("  <option value=%d %s>Order by Last Modified</option>\n",
	  ORDER_BY_LAST_MODIFIED,
	  $modified_selected);
  echo "</SELECT>\n";

  $alumni = include_alumni ();

  echo "<INPUT TYPE=SUBMIT VALUE=\"Update\"><BR>\n";
  echo "</FORM>\n";

  // Get a list of privileged users.  They'll be highlighted

  $sql = 'SELECT UserId, Priv FROM Users WHERE ""<>Priv';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of privileged users');

  $highlight = array ();

  while ($row = mysql_fetch_object ($result))
  {
    if ('' != $row->Priv)
      $highlight[$row->UserId] = 'BGCOLOR="#FFCCCC"';
  }

  $link = sprintf ('index.php?action=%d&Seq=%d',
		   EDIT_USER,
		   increment_sequence_number());

  if (ORDER_BY_NAME == $OrderBy)
    select_user ('Select User To Edit',
		 $link,
		 TRUE,
		 TRUE,
		 $highlight,
		 0 == $alumni,
		 true);
  else
    select_user_by_date ('Select User To Edit',
			 $link,
			 true,
			 $highlight,
			 $OrderBy,
			 0 == $alumni);

  /*
  $spaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

  echo "<P>\n";
  echo "<TABLE CELLSPACING=3>\n";
  echo "  <TR>\n";
  echo "    <TD BGCOLOR=\"#CCFFCC\">$spaces</TD>\n";
  echo "    <TD>Bid Committee Privilege$spaces</TD>\n";
  echo "    <TD BGCOLOR=\"#FFCCCC\">$spaces</TD>\n";
  echo "    <TD>Staff Privilege$spaces</TD>\n";
  echo "  </TR>\n";
  echo "</TABLE>\n";
  */
}

/*
 * display_comped_users
 *
 * Display the list of comp'd users, both by game and by user
 */

function display_comped_users ()
{
  // Make sure that only privileged users get here

  if (! user_has_priv (PRIV_CON_COM))
    return display_access_error ();

  $sql = "SELECT Users.FirstName, Users.LastName, Users.StageName, Users.CompEventId,";
  $sql .= "  Events.Title";
  $sql .= "  FROM Users, Events";
  $sql .= "  WHERE Users.CompEventId<>0";
  $sql .= "    AND Events.EventId=Users.CompEventId";
  $sql .= "  ORDER BY Events.Title, Users.LastName, Users.FirstName";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for list of comp'd users failed");

  $n = mysql_num_rows ($result);
  display_header ("$n Comp'd users by Game");

  if (0 != $n)
  {
    $cur_title = '';
    $titles = array ();

    while ($row = mysql_fetch_object ($result))
    {
      if ($cur_title != $row->Title)
      {
	echo "<P><B><I>$row->Title</I></B>\n";
	$cur_title = $row->Title;
	$titles[$row->CompEventId] = $row->Title;
      }

      echo "<BR>&nbsp;&nbsp;&nbsp;&nbsp;$row->LastName, $row->FirstName -- $row->StageName\n";
    }
  }
  echo "<P>\n";

  $sql = "SELECT FirstName, LastName, StageName, CompEventId";
  $sql .= "  FROM Users";
  $sql .= "  WHERE CanSignup='Comp'";
  $sql .= "  ORDER BY LastName, FirstName";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for list of comp'd users failed");

  $n = mysql_num_rows ($result);
  display_header ("$n Comp'd users by User");

  if (0 != $n)
  {
    echo "<TABLE BORDER=1 CELLPADDING=2>\n";
    echo "<TR><TH>Stage Name</TH><TH>Last, First</TH><TH>Comp Description</TH></TR>\n";
    while ($row = mysql_fetch_object ($result))
    {
      $game = $titles[$row->CompEventId];

      echo "  <TR>\n";
      echo "    <TD>$row->StageName</TD>\n";
      echo "    <TD>$row->LastName, $row->FirstName</TD>\n";
      echo "    <TD>$game</TD>\n";
      echo "  </TR>\n";
    }
    echo "</TABLE>\n";
  }

  echo "<P>\n";
}

/*
 * select_user_to_delete
 *
 * Display the list of users and let the user pick one to delete
 */

function select_user_to_delete ()
{
  // Make sure the user is allowed to visit this page

  if (! user_has_priv (PRIV_STAFF))
    return display_error ("You are do not have sufficient privilege to use this page");

  // Get a list of privileged users.  They'll be highlighted

  $sql = 'SELECT UserId, Priv FROM Users WHERE ""<>Priv';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of privileged users');

  $highlight = array ();

  while ($row = mysql_fetch_object ($result))
  {
    if ('' != $row->Priv)
      $highlight[$row->UserId] = 'BGCOLOR="#FFCCCC"';
  }

  // Display the form to allow the user to include the alumni in the list
  // of users to choose from and allow them to select one

  $alumni = include_alumni_form ('index.php', SELECT_USER_TO_DELETE);

  select_user ('Select User To Delete',
	       'index.php?action=' . SHOW_USER_TO_DELETE,
	       TRUE,
	       TRUE,
	       $highlight,
	       0 == $alumni);
}

function show_user_to_delete ()
{
  // Make sure the user is allowed to visit this page

  if (! user_has_priv (PRIV_STAFF))
    return display_error ("You are do not have sufficient privilege to use this page");

  $UserId = intval ($_REQUEST['UserId']);

  $sql = 'SELECT DisplayName, CanSignup, EMail FROM Users';
  $sql .= "  WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for user $UserId failed", $sql);

  // Make sure we've gotten a match

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find user $UserId");;

  $row = mysql_fetch_object ($result);

  $name = trim ("$row->DisplayName");

  echo "<H2>User to delete: $name</H2>\n";
  echo "<B>Status:</B>: $row->CanSignup<P>\n";

  // Show the games the user is registered for, if any

  show_games ($UserId, "$name is", 'signed up', 'Confirmed');
  show_games ($UserId, "$name is", 'wait listed', 'Waitlisted');

  // Show any games that the user is the GM for

  show_gm_games ($UserId, $name);

  echo "<FORM METHOD=POST ACTION=index.php>\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%s>\n", DELETE_USER);
  echo "<INPUT TYPE=HIDDEN NAME=UserId VALUE=$UserId>\n";
  echo "<CENTER>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=\"Delete User\">\n";
  echo "</CENTER>\n";
  echo "</FORM>\n";
}

function show_gm_games ($UserId, $name)
{
  $sql = 'SELECT GMs.EventId, Events.Title, Runs.Day, Runs.StartHour, Events.Hours';
  $sql .= '  FROM Events, Runs, GMs';
  $sql .= "  WHERE GMs.UserId=$UserId";
  $sql .= '    AND Events.EventId=GMs.EventId';
  $sql .= '    AND Runs.EventId=GMs.EventId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for games user will GM failed', $sql);

  $num = mysql_num_rows ($result);


  if ($num > 0)
  {
    echo "<B>$name is a part of $num shows, classes & panels:</B><BR>\n";
    echo "<TABLE>\n";
    while ($row = mysql_fetch_object ($result))
    {
      $start_time = start_hour_to_24_hour ($row->StartHour);
      $end_time = start_hour_to_24_hour ($row->StartHour + $row->Hours);

      echo "  <TR VALIGN=TOP>\n";
      echo "    <TD>$row->Day</TD>\n";
      echo "    <TD NOWRAP>&nbsp;$start_time - $end_time&nbsp;</TD>\n";
      echo "    <TD><A HREF=Schedule.php?action=" . SCHEDULE_SHOW_GAME .
	"&EventId=$row->EventId>$row->Title</A>&nbsp;&nbsp;&nbsp;</TD>\n";
    }
    echo "</TABLE>\n";
  }
}

/*
 * delete_user
 *
 * Delete a user from the database.  Note that this deletes the user
 * completely, not just withdraws them from any games they're signed up for
 */

function delete_user ()
{
  // Make sure the user is allowed to visit this page

  if (! user_has_priv (PRIV_STAFF))
    return display_error ("You are do not have sufficient privilege to use this page");

  $UserId = intval ($_REQUEST['UserId']);

  // Fetch the user name yet again...

  $sql = 'SELECT DisplayName, Gender FROM Users';
  $sql .= "  WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for user $UserId failed", $sql);

  // Make sure we've gotten a match

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find user $UserId");;

  $row = mysql_fetch_object ($result);

  $name = trim ("$row->DisplayName");
  $pronoun = gender_to_pronoun ($row->Gender);
    
  // Remove the user as a GM from any games

  $gm_count = 0;

  $sql = 'SELECT GMs.GMId, GMs.EventId,';
  $sql .= ' Events.Title, Runs.Day, Runs.StartHour, Events.Hours';
  $sql .= '  FROM Events, Runs, GMs';
  $sql .= "  WHERE GMs.UserId=$UserId";
  $sql .= '    AND Events.EventId=GMs.EventId';
  $sql .= '    AND Runs.EventId=GMs.EventId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for games user will present failed', $sql);

  //  echo "GM: $gm_count<BR>\n";

  while ($row = mysql_fetch_object ($result))
  {
    // Notify the submitter?

    $sql = "DELETE FROM GMs WHERE GMId=$row->GMId";

    //    echo "$sql<BR>\n";

    $delete_result = mysql_query ($sql);
    if (! $delete_result)
      return display_mysql_error ("Failed to delete presenter record $row->GMId",
				  $sql);

    $gm_count++;
  }

  // Withdraw from any games

  $game_count = 0;

  $sql = 'SELECT Signup.SignupId';
  $sql .= ' FROM Signup';
  $sql .= " WHERE Signup.UserId=$UserId";
  $sql .= '   AND State<>"Withdrawn"';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Failed to get list of events", $sql);

  while ($row = mysql_fetch_object ($result))
  {
    withdraw_from_game ($row->SignupId);
    $game_count++;
  }

  // Finally, delete the user record

  $sql = "DELETE FROM Users WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Failed to delete user record $row->GMId",
				$sql);

  if (1 == $gm_count)
    $gm_count .= ' conference item';
  else
    $gm_count .= ' conference items';

  if (1 == $game_count)
    $game_count .= ' ops track';
  else
    $game_count .= ' ops tracks';

  echo "<H2>$name has been removed from the database</H2>\n";
  echo "$name has been removed as teacher/panelist from $gm_count\n";
  echo "and withdrawn from $game_count<P>\n";
}

/*
 * select_user_to_view
 *
 * Display the list of users and let the user pick one to view
 */

function select_user_to_view ()
{
	// Make sure that only users with ConCom priv view this page

	if (! user_has_priv (PRIV_CON_COM))
		return display_access_error ();

	// There are no highlit users in this display, so just pass an emtpy array

	$highlight = array ();

	$link = sprintf ('index.php?action=%d&Seq=%d',
		   VIEW_USER,
		   increment_sequence_number());

	// Display the form to allow the user to include the alumni in the list
	// of users to choose from and allow them to select one
	
	select_user('Select User To View', $link, false, TRUE, $highlight);
}

/*
 * select_user_to_become
 *
 * Display the list of users and let the staff member select one to become
 */

function select_user_to_become ($result)
{
  if ('' != $result)
    display_error ($result);

  // Make sure that only users with Staff priv view this page

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error ();

  // There are no highlit users in this display, so just pass an empty array

  $highlight = array ();

  $link = sprintf ('index.php?action=%d&Seq=%d',
		   BECOME_USER,
		   increment_sequence_number());

  // Display the form to allow the user to include the alumni in the list
  // of users to choose from and allow them to select one

  $alumni = include_alumni_form ('index.php', SELECT_USER_TO_BECOME);

  select_user ('Select User To Become',
	       $link,
	       false,
	       TRUE,
	       $highlight,
	       0 == $alumni);
}

/*
 * become_user
 *
 * Change the logged in user for a staff member to the selected user ID
 */

function become_user ()
{
  // Make sure that only users with Staff priv view this page

  if (! user_has_priv (PRIV_STAFF))
    return 'You are not allowed access to this page';

  $UserId = intval (trim ($_REQUEST['UserId']));

  // Fetch the user information

  $sql = 'SELECT FirstName, LastName, UserId, Priv, Gender, CanSignup, EMail';
  $sql .= ' FROM Users';
  $sql .= ' WHERE UserId=' . $UserId;
  $result = mysql_query ($sql);
  if (! $result)
    return "$sql<p>Query for UserId $UserId failed: " . mysql_error();

  $row = mysql_fetch_object ($result);
  if (! $row)
    return "Failed to find user record with UserID=$UserId";

  // Login as the specified user.  The user's login information will replace
  // the staff member's info

  return login_with_data ($row, $row->EMail);
}

/*
 * select_user_to_set_password
 *
 * Display the list of users and let the user pick one to change it's password
 */

function select_user_to_set_password ()
{
  // Make sure that only users with Registrar priv view this page

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  // There are no highlit users in this display, so just pass an empty array

  $highlight = array ();

  $link = sprintf ('index.php?action=%d&Seq=%d',
		   DISPLAY_PASSWORD_FORM_FOR_USER,
		   increment_sequence_number());

  // Display the form to allow the user to include the alumni in the list
  // of users to choose from and allow them to select one

  $alumni = include_alumni_form ('index.php', SELECT_USER_TO_VIEW);

  select_user ('Select User To Set Password',
	       $link,
	       false,
	       TRUE,
	       $highlight,
	       0 == $alumni);
}

/*
 * display_password_form_for_user
 *
 * Display the form the registrar can use to change a user's password
 */

function display_password_form_for_user ()
{
  // Make sure that only users with Registrar priv view this page

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  $UserId = intval (trim ($_REQUEST['UserId']));

  // Fetch the user information

  $sql = 'SELECT DisplayName FROM Users WHERE UserId=' . $UserId;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for UserId $UserId failed", $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ("Failed to find user record with UserID=$UserId");

  $full_name = trim ("$row->DisplayName");
  display_header ("Set password for $full_name");
  echo "&nbsp;<br>\n";

  echo "<form method=post action=index.php>\n";
  form_add_sequence ();
  printf ("<input type=hidden name=action value=%d>\n",
	  PROCESS_PASSWORD_FORM_FOR_USER);
  echo "<input type=hidden name=UserId value=$UserId>\n";

  echo "<TABLE BORDER=0>\n";
  form_text (30, 'New Password', 'NewPassword1');
  form_text (30, 'Re-Enter New Password', 'NewPassword2');
  form_submit ('Set Password');
  echo "</TABLE>\n";
  echo "</FORM>\n";

  return true;
}

/*
 * process_password_form_for_user
 *
 * Process the form that allows the registrar to set a user's password
 */

function process_password_form_for_user ()
{
  // Make sure that only users with ConCom priv view this page

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  //  dump_array ("_POST", $_POST);

  $UserId = intval (trim ($_POST['UserId']));
  $NewPassword1 = trim ($_POST['NewPassword1']);
  $NewPassword2 = trim ($_POST['NewPassword2']);

  // Make sure we got two copies of the new password

  if (strlen ($NewPassword1) < 8)
    return display_error ('Passwords must be at least 8 characters long');

  if ($NewPassword1 != $NewPassword2)
    return display_error ('The new passwords do not match');

  $HashedPassword = md5 ($NewPassword1);

  $sql = 'UPDATE Users SET ';
  $sql .= build_sql_string ('HashedPassword', $HashedPassword, FALSE);
  $sql .= " WHERE UserId=$UserId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Attempt to set the new password failed');

  echo "The password has been changed<P>\n";

  return TRUE;
}

function display_array_info (&$array, $display, $key='', $skip_blank=false)
{
  echo "<!-- Key: $key -->";

  if ('' == $key)
    $key = $display;

  if (! array_key_exists ($key, $array))
    $value = '';
  else
    $value = $array[$key];

  echo "<!-- key: $key, value: '$value' -->\n";

  if (('' == $value) && $skip_blank)
    return;

  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD ALIGN=RIGHT nowrap><B>$display:</B></TD><TD ALIGN=LEFT>$value</TD>\n";
  echo "  </TR>\n";
}

/*
 * view_user
 *
 * Display information for a user
 */

function view_user ()
{
  $UserId = trim ($_REQUEST['UserId']);
  $UserId = intval($UserId);

  // Make sure that only privileged users get here

  if (! user_has_priv (PRIV_CON_COM))
    return display_access_error ();

  // Fetch the user information

  $sql = 'SELECT * FROM Users WHERE UserId=' . $UserId;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for UserId $UserId failed", $sql);

  $row = mysql_fetch_array ($result, MYSQL_ASSOC);

  // Convert payment amount to dollars

  $PaymentAmount = $row['PaymentAmount']/100;

  $name = $row['DisplayName'];

  display_header ($name);

  echo "<TABLE BORDER=0>\n";

  display_array_info ($row, 'EMail');
  echo "  <tr valign=\"top\">\n";
  echo "    <td align=\"right\"><b>Age:</b></td>\n";
  //printf ("    <td align=\"left\">%d</td>\n",
  //	  birth_year_to_age($row['BirthYear']));
  echo "  </tr>\n";
  //  display_array_info ($row, birth_year_to_age($row->BirthYear), 'Age');
  //display_array_info ($row, 'Gender');
  display_array_info ($row, 'Address', 'Address1');
  display_array_info ($row, '', 'Address2', true);
  display_array_info ($row, 'City');
  display_array_info ($row, 'State / Province', 'State');
  display_array_info ($row, 'Zipcode');
  display_array_info ($row, 'Country');
  display_array_info ($row, 'Daytime Phone', 'DayPhone');
  display_array_info ($row, 'Evening Phone', 'EvePhone');
  display_array_info ($row, 'Best Time to Call', 'BestTime');
  display_array_info ($row, 'Preferred Contact', 'PreferredContact');
  display_array_info ($row, 'Heard about Con', 'HowHeard');
  display_array_info ($row, 'Privileges', 'Priv');
  display_array_info ($row, 'Status', 'CanSignup');
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD ALIGN=RIGHT><B>Payment Amount $:</B></TD>";
  echo "<TD ALIGN=LEFT>$PaymentAmount</TD>\n";
  echo "  </TR>\n";
  //  display_array_info ($row, '', 'PaymentAmount');
  display_array_info ($row, 'Payment Note', 'PaymentNote');
  /*
  form_comped_for_game ($_POST['CompEventId']);

  echo "</TABLE>\n";

  // Fetch info on updating user

  if ($UserId != 0)
  {
    $Modified = timestamp_to_datetime ($_POST['Modified']);
    $LastLogin = timestamp_to_datetime ($_POST['LastLogin']);
    $Created = timestamp_to_datetime ($_POST['Created']);
    
    // If the ModifiedBy column is 0, the user's record hasn't ever been
    // modified

    if (0 == $_POST['ModifiedBy'])
      echo "<P>Created $Created\n";
    else
    {
      $sql = 'SELECT FirstName, LastName FROM Users WHERE UserId=' .
	     $_POST['ModifiedBy'];
      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Query for modifying UserId $UserId failed");

      $row = mysql_fetch_object ($result);

      echo "<P>Last modified by $row->FirstName $row->LastName, $Modified\n";
    }

    $StatusModified = timestamp_to_datetime ($_POST['CanSignupModified']);

    if (0 != $_POST['CanSignupModifiedId'])
    {
      $sql = 'SELECT FirstName, LastName FROM Users WHERE UserId=' .
	     $_POST['CanSignupModifiedId'];
      $result = mysql_query ($sql);
      if (! $result)
	return display_mysql_error ("Query for modifying UserId $UserId failed");

      $row = mysql_fetch_object ($result);

      echo "<P>Payment status last modified by $row->FirstName $row->LastName, $StatusModified\n";
      echo "<BR>Note: This will be the user himself if he paid using PayPal<P>\n";
    }
  }
*/
  echo "</TABLE>\n";

  // Show the games the user is registered for, if any, as well as any games
  // he/she is GMing

  echo "<P>\n";

  show_games ($UserId, "$name is", 'signed up', 'Confirmed');
  show_games ($UserId, "$name is", 'wait listed', 'Waitlisted');

  show_gm_games ($UserId, $name);
}

/*
 * select_user_by_date
 *
 * General function to display the list of users in the database and allow
 * the current user to select one
 */

function select_user_by_date ($header,
			      $href,
			      $show_new_user,
			      $highlight,
			      $order_by,
			      $exclude_alumni)
{
  $priv = user_has_priv (PRIV_STAFF);

  // Our SQL statement depends on what we're ordering by

  switch ($order_by)
  {
    default:
    case ORDER_BY_CREATION:
      $sql_date = ' DATE_FORMAT(Created, "%d-%b-%Y") AS Date';
      $sql_order = '  ORDER BY Created DESC';
      $header .= ' - Ordered by Record Creation';
      break;

    case ORDER_BY_PAYMENT_STATUS_CHANGE:
      $sql_date = ' DATE_FORMAT(CanSignupModified, "%d-%b-%Y") AS Date';
      $sql_order = '  ORDER BY CanSignupModified DESC';
      $header .= ' - Ordered by Payment Status Change';
      break;

    case ORDER_BY_LAST_MODIFIED:
      $sql_date = ' DATE_FORMAT(Modified, "%d-%b-%Y") AS Date';
      $sql_order = '  ORDER BY Modified DESC';
      $header .= ' - Ordered by Last Modification Date';
      break;

    case ORDER_BY_LAST_LOGIN:
      $sql_date = ' DATE_FORMAT(LastLogin, "%d-%b-%Y") AS Date';
      $sql_order = '  ORDER BY LastLogin DESC';
      $header .= ' - Ordered by Last Login';
      break;
  }

  $sql = 'SELECT UserId, FirstName, LastName, EMail, CanSignup, Priv, PaymentAmount,';
  $sql .= $sql_date;
  $sql .= '  FROM Users';
  if ($exclude_alumni)
    $sql .= '  WHERE CanSignup<>"Alumni"';
  $sql .= $sql_order;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get list of users', $sql);

  display_header ($header);

  if ($show_new_user)
    echo "<A HREF=$href&UserId=0>New User</A>\n";

  $cur_month = '';

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

    if ('' == $row->Date)
      $Order = '&nbsp;';
    else
      $Order = substr ($row->Date, 3);
    if ($cur_month != $Order)
    {
      $cur_month = $Order;
      echo "  <TR BGCOLOR=\"#CCCCFF\"><TD COLSPAN=5>$cur_month</TD></TR>\n";
    }

    // Display the user name for selection

    echo " <TR $bgcolor>\n";

    printf ("    <TD><A HREF=$href&UserId=%d>%s, %s</A></TD>\n",
	    $row->UserId,
	    $row->LastName,
	    $row->FirstName);

    echo "    <TD><A HREF=mailto:$row->EMail>$row->EMail</A></TD>\n";

    $status = $row->CanSignup;
    if ('Paid' == $row->CanSignup)
      $status = sprintf ('Paid $%d', $row->PaymentAmount / 100);
    echo "    <TD>$status</TD>\n";
    echo "    <TD NOWRAP>$row->Date</TD>\n";
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
 * edit_bio
 *
 * Allow a user to edit the biographical information on him (or her) in the
 * database
 */

function edit_bio ()
{
  // If this is the first time in, try to read any existing information
  if (! array_key_exists ('BioId', $_POST))
  {
    $sql = 'SELECT * FROM Bios';
    $sql .= ' WHERE Bios.UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for bio failed');

    $row = mysql_fetch_object ($result);
    if ($row)
    {
      $_POST['BioId'] = $row->BioId;
      $_POST['BioText'] = $row->BioText;
      $_POST['Title'] = $row->Title;
      $_POST['ShowNickname'] = $row->ShowNickname;
      $_POST['Website'] = $row->Website;
      $_POST['PhotoSource'] = $row->PhotoSource;
    }
    else
    {
      $_POST['BioId'] = 0;
      $_POST['BioText'] = '';
      $_POST['Title'] = '';
      $_POST['ShowNickname'] = 1;
      $_POST['Website'] = '';
      $_POST['PhotoSource'] = '';
    }
  }

    $sql = 'SELECT DisplayName FROM Users';
    $sql .= ' WHERE Users.UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for bio failed');

    $row = mysql_fetch_object ($result);


  echo '<H1>Bio for ' . $row->DisplayName . "</H1>\n";


  if ($_POST['ShowNickname'])
    $show_nickname_checked = 'CHECKED';
  else
    $show_nickname_checked = '';

  echo "<B>Note:</B> The deadline for bios for the program booklet is<b><font color=red>\n";
  echo BIO_DUE_DATE . "</font></b>.  Any modifications after this date will be displayed\n";
  echo "on the website, but not in the program booklet.<p><b>Also note</b> that any\n";
  echo "formatting in your bio may be modified to match the format of the\n";
  echo "program booklet.  In addition, your bio in the program booklet\n";
  echo "may be edited to fit in the space available.<P>\n";
  echo "Stage Names will be used if available.  If your stage names is not in your ";
  echo "profile, your first and last name will be used.\n<br><br>";


  if (user_is_gm ())
  {
    echo "<TABLE>\n";
    echo "  <TR VALIGN=TOP>\n";
    echo "    <TD>Presenter for:</TD>\n";
    echo "    <TD>\n";

    $sql = 'SELECT Events.Title FROM Events, GMs';
    $sql .= '  WHERE GMs.UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= '    AND Events.EventId=GMs.EventId';
    $sql .= '  ORDER BY Events.Title';

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for games failed');

    while ($row = mysql_fetch_object ($result))
    {
      echo "$row->Title<BR>\n";
    }

    echo "    </TD>\n";
    echo "  </TR>\n";
    echo "</TABLE>\n";
  }

  // Gather the list of games the user is the GM for
  echo "<FORM METHOD=POST ACTION=index.php enctype=\"multipart/form-data\">\n";
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", UPDATE_BIO);
  printf ("<INPUT TYPE=HIDDEN NAME=BioId VALUE=%d>\n", $_POST['BioId']);
  form_hidden_value ('OrigPhoto', $_POST["PhotoSource"]);

  if (',,' == $_SESSION[SESSION_LOGIN_USER_PRIVS])
    echo "<INPUT TYPE=HIDDEN NAME=Title VALUE=\"\">\n";
  else
  {
    echo "Privileges: ";
    $count = 0;
    $privs = explode (',', $_SESSION[SESSION_LOGIN_USER_PRIVS]);

    for ($i = 1; $i < count($privs); $i++)
    {
      if ($privs[$i] != '')
      {
	if ($count++ > 0)
	  echo ", ";
	echo $privs[$i];
      }
    }
	    
  }
    
  echo "<p><font color=red>*</font> indicates a required field\n<br><br>";
  echo "<TABLE BORDER=0>\n";

  if (',,' != $_SESSION[SESSION_LOGIN_USER_PRIVS])
    form_text (64, 'Title', 'Title', 128, FALSE);

  $text = "Biography.  Your bio can use HTML tags for formatting.  A quick\n";
  $text .= "primer on a couple of useful HTML tags is available\n";
  $text .= "<A HREF=HtmlPrimer.html TARGET=_blank>here</A>.<BR>\n";

  form_textarea ($text, 'BioText', 15);
  form_text (64, 'Website', 'Website', 128, FALSE);
  form_upload("Photo:","photo_upload", FALSE, TRUE);
  display_media( $_POST["PhotoSource"]);
  echo "</TABLE>\n";
      

  echo "<CENTER><INPUT TYPE=SUBMIT VALUE=\"Submit\"></CENTER>\n";
  echo "</FORM>\n";

  return true;
}

/*
 * update_bio
 *
 * Process a bio update
 */

function update_bio ()
{
  $BioId = intval (trim ($_POST['BioId']));

  if (array_key_exists ('ShowNickname', $_POST))
    $ShowNickname = intval ($_POST['ShowNickname']);
  else
    $ShowNickname = 0;
  $file = "photo_upload";
  $path = "";

  if (validate_file($file))
  {
    $path = process_file($file, "picture", "User-".$_SESSION[SESSION_LOGIN_USER_ID] );
    if ( strpos($path,FILE_UPLOAD_LOC) === FALSE )
    {
      return display_error ("Error_uploading the photo file.");
    }
  }
  else 
    $path = $_POST["OrigPhoto"];

  if (0 == $BioId)
  {
    $sql = 'INSERT INTO Bios SET UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
    $sql .= build_sql_string ('BioText', '', true, true);
    $sql .= build_sql_string ('Title');
    $sql .= build_sql_string ('ShowNickname', $ShowNickname);
    $sql .= build_sql_string ('Website');
    $sql .= ', PhotoSource="'.$path.'"';

  }
  else
  {
    $sql = 'UPDATE Bios SET ';
    $sql .= build_sql_string ('BioText', '', false, true);
    $sql .= build_sql_string ('Title');
    $sql .= build_sql_string ('ShowNickname', $ShowNickname);
    $sql .= build_sql_string ('Website');
    $sql .= ', PhotoSource="'.$path.'"';
    $sql .= ' WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];
  }

  //  echo "SQL: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Insert into Bios failed', $sql);

  return true;
}

/*
 * bio_report
 *
 * Display a list of who has and who hasn't updated their bio
 */

function bio_report ()
{
  $bio_users = array ();

  // Start by gathering the list of GMs

  $sql = 'SELECT DISTINCT Users.UserId, Users.DisplayName, ';
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

  uasort ($bio_users, "strcasecmp");
  reset ($bio_users);

  // Build the table of whether folks have submitted bios

  echo "<table border=\"1\">\n";
  echo "  <tr align=\"left\">\n";
  echo "    <th>Name</th>\n";
  echo "    <th>Privs</th>\n";
  echo "    <th>Last Updated</th>\n";
  echo "    <th>Title(s)</th>\n";
  echo "    <th>Games GM-ing For</th>\n";
  echo "    <th>Bio</th>\n";
  echo "  </tr>\n";

  foreach ($bio_users as $user_id => $v)
  {
    $tmp = explode ('|', $v);
    $name = $tmp[0];
    $email = $tmp[1];

    echo "  <tr valign=\"top\">\n";
    //    echo "<!-- $v -->\n";
    echo "    <td><a href=\"mailto:$email\">$name</a></td>\n";

    // If the user has privileges, show them

    $sql = "SELECT Priv FROM Users WHERE UserId=$user_id";
    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ('Query for privs failed', $sql);

    echo '    <td>';
    $row = mysql_fetch_object ($result);
    if ('' != $row->Priv)
    {
      $privs = explode (',', $row->Priv);
      echo $privs[0];
      if (count ($privs) > 1)
	for ($i = 1; $i < count($privs); $i++)
	  echo ", $privs[$i]";
    }
    else
      echo '&nbsp;';
    echo '    </td>';

    // Fetch bio information

    $sql = 'SELECT BioId, Title, BioText,';
    $sql .= ' DATE_FORMAT(LastUpdated, "%d-%b-%Y %H:%i") AS LastUpdated';
    $sql .= ' FROM Bios';
    $sql .= " WHERE UserId=$user_id";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for bio failed', $sql);

    $row = mysql_fetch_object ($result);

    $title = '&nbsp;';
    $have_bio = '<font color=\"red\">No</font>';
    $updated = 'Never';

    if ($row)
    {
      if ('' != $row->Title)
	$title = $row->Title;

      if ('' != $row->BioText)
	$have_bio = 'Yes';

      $updated = $row->LastUpdated;
    }

    echo "    <td>$updated</td>\n";
    echo "    <td>$title</td>\n";

    // Show the games a user is GM for

    $sql = 'SELECT Events.Title FROM GMs, Events';
    $sql .= ' WHERE Events.EventId=GMs.EventId';
    $sql .= "   AND GMs.UserId=$user_id";

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ('Query for events failed', $sql);

    $games = 0;
    echo '    <td>';

    while ($row = mysql_fetch_object ($result))
    {
      if ($games++ > 0)
	echo ', ';
      echo "<i>$row->Title</i>";
    }

    if (0 == $games)
      echo '&nbsp;';

    echo "    </td>\n";

    echo "    <td>$have_bio</td>\n";
    echo "  </tr>\n";
  }
  echo "</table>\n";
}

function who_is_who ()
{
  echo "<H1>Who's Who at " . CON_NAME . "</H1>\n";
  $bio_users = array ();

  // Start by gathering the list of GMs

  $sql = 'SELECT DISTINCT Users.UserId, Users.DisplayName ';
  $sql .= ' FROM GMs, Users';
  $sql .= ' WHERE Users.UserId=GMs.UserId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    $bio_users[$row->UserId] = "$row->DisplayName";
  }

  // Now add the con staff.  Don't forget to skip Admin (UserId==1)

  $sql = 'SELECT UserId, DisplayName';
  $sql .= ' FROM Users';
  $sql .= ' WHERE ""<>Priv';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for con staff failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if (1 != $row->UserId)
      $bio_users[$row->UserId] = "$row->DisplayName";
  }

  // Sort the array BY THE VALUE (as opposed to the key)

  uasort ($bio_users, "strcasecmp");
  reset ($bio_users);

  // Display who's who at Intercon

  $user_count = 0;

  foreach ($bio_users as $user_id => $name)
  {
    
    if (0 != $user_count++)
      echo "<center><hr width=\"50%\"></center>\n";
    echo "<p>";

    // Gather information from the Bios table, if it's available
    $Bio = '';
    $Title = '';
    $website = '';
    $photo = '';

    $sql = "SELECT * FROM Bios WHERE UserId=$user_id";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for bio failed', $sql);

    $row = mysql_fetch_object ($result);
    if ($row)
    {

      if ('' != $row->Title)
 	    $Title = $row->Title;

      $Bio = $row->BioText;
      $website = $row->Website;
      $photo = $row->PhotoSource;

    }

    display_header ($name);
    if ('' != $Title)
      echo "$Title<br>\n";

    // Now add any games the user is a GM for

    $sql = 'SELECT Events.Title FROM GMs, Events';
    $sql .= ' WHERE Events.EventId=GMs.EventId';
    $sql .= "   AND GMs.UserId=$user_id";
    $sql .= '   AND GMs.DisplayAsGM="Y"';
    $sql .= '   AND Events.IsConSuite="N"';
    $sql .= '   AND Events.IsOps="N"';

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ('Query for events failed', $sql);

    if (mysql_num_rows ($result) > 0)
    {
      echo "Presenting: ";
      $games = 0;

      while ($row = mysql_fetch_object ($result))
      {
	if ($games++ > 0)
	  echo ', ';
	echo "<i>$row->Title</i>";
      }
      echo "<br>\n";
    }

    // Add con staffers

    $sql = 'SELECT Priv FROM Users';
    $sql .= " WHERE UserId=$user_id";

    $staff_positions = 0;
    $result = mysql_query($sql);
    if (! $result)
      display_mysql_error ('Query for user privs failed', $sql);
    else
    {
      $row = mysql_fetch_object($result);
      $privs = explode (',', $row->Priv);
      foreach ($privs as $k => $v)
      {
	switch ($v)
	{
	  case "BidCom":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo 'Conference Committee';
	    break;

	  case "BidChair":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo "Conference Coordinator<br>\n";
	    break;

	  case "ShowCom":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo 'Performance Selection Committee';
	    break;

	  case "ShowChair":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo "Performance Selection Chairperson<br>\n";
	    break;

	  case "GMLiaison":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo 'Liaison';
	    break;

	  case "Registrar":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo 'Registrar';
	    break;

	  case "Outreach":
	    if ($staff_positions++ > 0)
	      echo ', ';
	    echo 'Outreach';
	    break;
	}
      }

      if ((0 == $staff_positions) && ('ConCom' == $row->Priv))
      {
	echo 'Staff';
	$staff_positions++;
      }
    }

    // Check for Con Suite

    $sql = 'SELECT Events.Title FROM GMs, Events';
    $sql .= ' WHERE Events.EventId=GMs.EventId';
    $sql .= "   AND GMs.UserId=$user_id";
    $sql .= '   AND Events.IsConSuite="Y"';

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ('Query for events failed', $sql);
    else
    {
      if (mysql_num_rows($result) > 0)
      {
	if ($staff_positions++ > 0)
	  echo ', ';
	echo 'Con Suite';
      }
    }

    // Check for Ops

    $sql = 'SELECT Events.Title FROM GMs, Events';
    $sql .= ' WHERE Events.EventId=GMs.EventId';
    $sql .= "   AND GMs.UserId=$user_id";
    $sql .= '   AND Events.IsOps="Y"';

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ('Query for events failed', $sql);
    else
    {
      if (mysql_num_rows($result) > 0)
      {
	if ($staff_positions++ > 0)
	  echo ', ';
	echo 'Ops';
      }
    }

    if ($staff_positions > 0)
      echo "<br>\n";


    if (!($website == '' && $Bio == '' && $photo == ''))
      show_user_homepage_bio_info ($website, $Bio, $photo);

  }
}

/*
 * log_paypal_msgs
 *
 * Send a copy of the PayPal messages home, so we can try to figure out
 * what's going on
 */

function log_paypal_msgs ()
{
  // Dump the POST array to the message

  $msg = "POST Parameters:\n";
  reset ($_POST);
  foreach ($_POST as $k => $v)
  {
    $msg .= "[$k] = $v\n";
  }
  reset ($_POST);

  // And the SESSION array

  $msg .= "\nSESSION Parameters:\n";
  reset ($_SESSION);
  foreach ($_SESSION as $k => $v)
  {
    $msg .= "[$k] = $v\n";
  }
  reset ($_SESSION);

  // And the SERVER array

  $msg .= "\nSERVER Parameters:\n";
  reset ($_SERVER);
  foreach ($_SERVER as $k => $v)
  {
    $msg .= "[$k] = $v\n";
  }
  reset ($_SERVER);

  // Phone home

  intercon_mail ('barry@tannenbaum.mv.com', 'PayPal Log Message', $msg);
}

function paypal_test ()
{
  // Build the URL for the PayPal links

  $return_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
  $cancel_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];

  $url = 'https://www.paypal.com/cgi-bin/webscr?';
  $url .= build_url_string ('cmd', '_xclick');
  $url .= build_url_string ('business', PAYPAL_ACCOUNT_EMAIL);
  $url .= build_url_string ('item_name', 'Intercon PayPal Test');
  $url .= build_url_string ('no_note', '0');
  $url .= build_url_string ('cn', 'Any notes about your payment?');
  $url .= build_url_string ('no_shipping', '1');
  //    $url .= build_url_string ('invoice', $_SESSION[SESSION_LOGIN_USER_ID].' '.$name);
  $url .= build_url_string ('currency_code', 'USD');
  $url .= build_url_string ('amount', '0.05');
  $url .= build_url_string ('rm', '2');
  $url .= build_url_string ('cancel_return', $cancel_url);
  $url .= build_url_string ('return', $return_url, FALSE);


  echo "<a href=$url>Test PayPal</a><p>";
}

/*
 * confirm_withdraw_user_from_all_games
 *
 * Have the user confirm that he (or she) really did intend to withdraw
 * from the user from all games
 */

function confirm_withdraw_user_from_all_games ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (false);

  // Make sure that only users with Registrar privilege get here

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  $UserId = intval (trim ($_REQUEST['UserId']));

  // Fetch the user's name

  $sql = "SELECT DisplayName, Gender FROM Users WHERE UserId=$UserId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for user information failed', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('Failed to find user record');

  $name = trim ("$row->DisplayName");
  if ('Male' == $row->Gender)
    $pronoun = 'he';
  else
    $pronoun = 'she';

  // Show the games that the user is signed up or waitlisted for

  $sql = 'SELECT Events.Title, Events.Hours, Runs.Day, Runs.StartHour, ';
  $sql .= ' Signup.State';
  $sql .= ' FROM Signup, Runs, Events';
  $sql .= " WHERE Signup.UserId=$UserId";
  $sql .= '   AND Runs.RunId=Signup.RunId';
  $sql .= '   AND Events.EventId=Runs.EventId';
  $sql .= '   AND Signup.State<>"Withdrawn"';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signup information failed', $sql);

  $count = mysql_num_rows ($result);
  if (0 == $count)
  {
    display_error ("$name is not signed up for any games.");
    return false;
  }

  echo "<h1>Confirm Removal from ALL events</h1>\n";
  echo "<b>$name</b> is signed up for the following games:<br>\n";

  echo "<table>\n";
  while ($row = mysql_fetch_object ($result))
  {

    echo "  <tr>\n";
    echo "    <td>$row->State</td>\n";
    printf ("    <td>&nbsp;%s&nbsp;%s&nbsp;-&nbsp;%s&nbsp;</td>\n",
	    $row->Day,
	    start_hour_to_12_hour ($row->StartHour),
	    start_hour_to_12_hour ($row->StartHour + $row->Hours));
    echo "    <td>$row->Title</td>\n";
    echo "  </tr>\n";
  }
  echo "</table>\n";

  echo "<p>Are you sure you want to withdraw $name from\n";
  if ($count > 1)
    echo 'all the events';
  else
    echo 'the event';
  echo " that $pronoun is signed up for?</p>\n";

  echo "<form method=post action=index.php>\n";
  form_add_sequence ();
  printf ("<input type=hidden name=action value=%d>\n",
	  WITHDRAW_USER_FROM_ALL_GAMES_CONFIRMED);
  printf ("<input type=hidden name=UserId value=%d>\n",
	  $UserId);
  echo "<center><input type=submit value=\"Confirm Withdrawal\"></center>\n";
  echo "</form>\n";

  echo "<p>Note that this will not remove the user from running, presenting or performing in any classses, panels or shows.\n";
  return true;
}


/*
 * withdraw_user_from_all_games
 *
 * Process a request to withdraw a user from all game the games they're signed
 * up for
 */

function withdraw_user_from_all_games ()
{
  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (false);

  // Make sure that only users with Registrar privilege get here

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  // Grab the user ID from the HTTP request

  $UserId = intval (trim ($_POST['UserId']));

  // We can't keep the connection to the database while the table is locked.
  // So build an array of all the information we'll need...

  $sql = 'SELECT Signup.SignupId, Signup.State, Signup.Counted,';
  $sql .= ' Signup.Gender, Users.DisplayName, Users.EMail';
  $sql .= ' FROM Signup, Users';
  $sql .= " WHERE Signup.UserId=$UserId";
  $sql .= '   AND Signup.State<>"Withdrawn"';
  $sql .= '   AND Users.UserId=Signup.UserId';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signup info failed', $sql);

  $a = array ();

  while ($row = mysql_fetch_object ($result))
  {
    $prev_state = strtolower ($row->State);
    $name = trim ("$row->DisplayName");

    $a[] = "$row->SignupId|$name|$row->EMail|$prev_state|$row->Gender|$row->Counted";
  }

  mysql_free_result ($result);

  //  dump_array ("A", $a);

  // Lock the Signup table to make sure that if there are two users trying
  // to get the last slot in a game, then only one will succeed.  A READ lock
  // allows clients that only read the table to continue, but will block
  // clients that attempt to write to the table

  $result = mysql_query ('LOCK TABLE Signup WRITE, Users READ, Runs READ, Events READ, GMs READ');
  if (! $result)
    return display_mysql_error ('Failed to lock the Signup table');

  foreach ($a as $v)
  {
    $event_info = explode ('|', $v);

    withdraw_from_game_locked ($event_info[0],    // UserId
			       $event_info[1],    // name
			       $event_info[2],    // EMail
			       $event_info[3],    // prev_state
			       $event_info[4],    // Gender
			       $event_info[5]);   // Subject
  }

  // Unlock the Signup table so that other queries can access it

  $result = mysql_query ('UNLOCK TABLES');
  if (! $result)
    return display_mysql_error ('Failed to unlock the Signup table');

  return TRUE;
}

/*
 * convert_age_to_year
 *
 * Convert all user's ages to birth year.  This should only be used once!
 * In fact, the function should be commented out after it's been used!
 */

/*
 * Already done
function convert_age_to_year()
{
  // Only do this if the user has Staff priv

  if (! user_has_priv (PRIV_STAFF))
    return;

  // Convert the Age column to BirthYear

  $this_year = intval (date ('Y'));

  $sql = 'SELECT FirstName, LastName, UserId, Age FROM Users';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to get users and ages!', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    if (0 == $row->Age)
      $BirthYear = 0;
    else
      $BirthYear = $this_year - $row->Age;

    echo "$row->LastName, $row->FirstName: $row->Age => $BirthYear<br>\n";

    $sql = sprintf ('UPDATE Users SET BirthYear=%d WHERE UserId=%s',
		    $BirthYear,
		    $row->UserId);
    $update_result = mysql_query ($sql);
    if (! $update_result)
      return display_mysql_error ('Attempt to update user failed', $sql);
  }
}
*/

?>
