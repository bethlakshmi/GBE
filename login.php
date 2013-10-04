<?php
require_once "common.php";

function escape($thing) {
    return htmlentities($thing);
}
function test_auth () {
    $consumer = getConsumer();
    $return = TRUE;
    
    // Complete the authentication process using the server's
    // response.
    $return_to = getReturnTo();
    $response = $consumer->complete($return_to);
    $msg = '';

    // Check the response status.
    if ($response->status == Auth_OpenID_CANCEL) {
        // This means the authentication was cancelled.
        $msg = 'Verification cancelled.';
    } else if ($response->status == Auth_OpenID_FAILURE) {
        // Authentication failed; display the error message.
        $msg = "Login failed: " . $response->message;
    } else if ($response->status == Auth_OpenID_SUCCESS) {
	} else {
        $msg .= "<p>No PAPE response was sent by the provider.</p>";
	}
    
    if ($msg != '')
    {
      echo "<font color=red>".$msg."</font>";
      $return =  FALSE;
    }

    return $return;
}
//
// Name:  test_reg
//
// Description:  tests that the open id provided is in the user table
//   
// Returns:
//   1 - if user id is found and has been set up successfully
//   0 - if user id is NOT found and therefore user should be registered
//   -1 - if there has been an error that should be reported instead of 
//        login
//
function test_reg ($id) {
  $foundid = 0;

  $sql = 'SELECT *';
  $sql .= ' FROM Users';
  $sql .= "  WHERE openid='$id'";

  // echo "$sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query', $sql);

  // Make sure we've gotten a single match
  // echo "Num Rows:  ".mysql_num_rows ($result)."<br>";

  if (0 == mysql_num_rows ($result))
  {
    return 0;
  }

  if (1 != mysql_num_rows ($result))
  {
    return -1;
  }

  // Extract the UserId for the user being logged in and decode the privileges

  $row = mysql_fetch_object ($result);

  if ( $row )
  {
    $foundid = login_with_data ($row, $row->EMail);
    foreach ($row as $key => $value)
      $_POST[$key] = $value;

    if ($foundid > 0)
      return 1;
  } 

  return 0;
}

function get_openid() {
  $consumer = getConsumer();
  $return_to = getReturnTo();
  $response = $consumer->complete($return_to);

  // This assumes the authentication succeeded; extract the
  // identity URL and Simple Registration data (if it was
  // returned).
  $openid = $response->getDisplayIdentifier();
  $esc_identity = escape($openid);
  return $esc_identity;
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
} // process_login_form

function process_openid ($id)
{
  $returnval = FALSE;
  $_SESSION[SESSION_LOGIN_OPENID] = "yo";
  return $returnval;

} // process_openid

function login_with_data ($row, $EMail)
{
  $UserId = $row->UserId;
  $DisplayName = $row->DisplayName;
  $name = trim ("$row->FirstName $row->LastName");

  // Update the login time.  If the user was an Alumni, promote him or her to
  // None, since he's expressed an interest in this con

  $returning_alumni = false;

  $sql = 'UPDATE Users SET LastLogin=NULL';
  if ('Alumni' == $row->CanSignup)
  {
    $sql .= ', CanSignup="None", CanSignupModified=NULL';
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
  if (strlen($row->openid) > 0)
    $_SESSION[SESSION_LOGIN_OPENID] = $row->openid;
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

?>
