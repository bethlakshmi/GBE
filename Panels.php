<?php
define (SIGNUP_FAIL, 0);
define (SIGNUP_OK, 1);
define (SIGNUP_CONFIRM, 2);

include ("intercon_db.inc");
include ("intercon_schedule.inc");
include ("pcsg.inc");
include ("files.php");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = LIST_GAMES;

// echo "Action: $action\n";

switch ($action)
{
  // Show the list with a form for volunteering
  case LIST_GAMES:

    list_panels ();
    break;

  // prospective panelist is offering to be on a panel
  case BID_PROCESS_FORM:

    if (! process_bid_panel_form ())
      list_panels ();
    else
      display_bid_etc ();
    break;


  // using for add panelist - for the selection 
  // to be made by the Bid Chair
  case ADD_GM:
    if (! user_has_priv (PRIV_SCHEDULING))
      display_access_error ();
    else
      set_panelists ();
      
  $page = 'panelists_added.html';

  if (! is_readable ($page))
  {
    if (! is_readable (TEXT_DIR."/$page"))
    {
      display_error ("Unable to read $page");
    }
    else
      include (TEXT_DIR."/$page");
  }
  else
    include ($page);
    
    
    break;

  // commit panelist to panel is the same as teacher
  // added to class (adding a gm)
  case PROCESS_ADD_GM:
    if (! can_edit_game_info ())
      display_access_error ();
    else
    {
      if (! process_add_gm ())
	select_user_as_gm ();
      else
	display_gm_list ();
    }
    break;
    


  default:
    display_error ("Unknown action code: $action");
}

// Add the postamble

html_end ();


/*
 * list_panels
 *
 * Get the list of panels integrated into a signup form.
 */

function list_panels ()
{
  // Make sure that the user is logged in

  if (! isset ($_SESSION[SESSION_LOGIN_USER_ID]))
    return display_error ('You must login before applying to sit on a panel.');

  // Always shill for games!
  if (accepting_bids())
  {
     if (file_exists(TEXT_DIR.'/acceptingbids.html'))
	include(TEXT_DIR.'/acceptings.html');	
  }

  $sql = 'SELECT BidId, Title, Description, ShortBlurb,';
  $sql .= ' LENGTH(Description) AS DescLen';
  $sql .= ' FROM Bids';
  $sql .= ' WHERE GameType=\'Panel\' AND Status=\'Under Review\'';
  $sql .= ' ORDER BY Title';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for game list failed');

  $n = mysql_num_rows ($result);

  echo "<h2>2014 Panel Volunteering</h2>";
  echo "<div><big>Thank you for your interest in participating in a panel at " . CON_NAME ;
  echo ".  </big><br /><br />";
  echo CON_SHORT_NAME . " is " . DATE_RANGE . " at " . HOTEL_NAME . " in " . CON_CITY . ".  ";
  echo "<br /><br />";

  if ($n > 0)
  {

//    if (file_exists(TEXT_DIR.'/actinstruct.html'))
//  	  include(TEXT_DIR.'/actinstruct.html');	


    // Output the note about comps, so nobody can say that they didn't
    // see it
    if (BID_SHOW_COMPS)
    {
	  echo "<div style=\"border-style: solid; border-color: red; padding: 1ex; margin-bottom: 2ex\">\n";
	  echo "<b>Important Note:</b><br>\n";
	  echo "The policy of the " . CON_NAME . " has changed this year. \n";
	  echo "This year, time spent assisting the expo during run time as a teacher, \n";
	  echo "panelist, perfomer, or other volunteer will be taken into consideration \n";
	  echo "for 2015 discounts.  After the " . CON_SHORT_NAME . " has concluded, you ";
	  echo "can expect to hear the about 2015 discounts as part of our thank yous. \n";
	  echo "</div>\n";
    }

    echo "<div>Not seeing a panel that interests you?  Use our ";
    echo "<a href=\"biddingAGame.php?action=183\">Panel Suggestion</a> page to ";
    echo "send us a better idea.  If we agree, you'll see it here later.</div>\n";

    echo "<form method=\"POST\" action=\"Panels.php?action=".BID_PROCESS_FORM."\" enctype=\"multipart/form-data\">\n";
    form_add_sequence ();

    echo "<p><font color=red>*</font> indicates a required field\n";
    echo "<TABLE BORDER=0>\n";
    form_section(	"Panel Opportunities");
    $n=0;

    while ($row = mysql_fetch_object ($result))
    {
      if ($n != 0)
        echo "<hr><br>\n";
      // If there's no long description, don't offer a link
      echo "<tr><td colspan=2><b>$row->Title</b><br><br></td></tr>";

      if ('' != $row->ShortBlurb)
	    echo "<tr><td colspan=2><B>Summary:</b>\n$row->ShortBlurb\n</td></tr>";

      if ('' != $row->Description)
	    echo "<tr><td colspan=2><B>Description:</b>\n$row->Description\n</td></tr>";
      
      $text = "I am would like to be a... ";
      form_hidden_value ('BidId-'.$n, $row->BidId);            
      $panel = false;
      $moderator = false;

      if ( isset($_POST['Panelist-'.$n]) )
      {
        $panel = $_POST['Panelist-'.$n];
      }
      if ( isset($_POST['Moderator-'.$n]) )
      {
        $moderator = $_POST['Moderator-'.$n];
      }

      echo "  <TR>\n";
      echo "    <TD COLSPAN=2><br>\n";
      echo "<b>I am interested in participating as a...</b><br>";
      form_checkbox("Panelist-".$n, $panel);
      echo "  Panelist<br>";
      form_checkbox("Moderator-".$n, $moderator);
      echo "  Moderator  ";
      echo "    </TD>\n";
      echo "  </TR>\n";

      form_textarea ('Why are you great for this panel?  What is your basis of expertise in this area?',
                     'Expertise-'.$n, 5, TRUE, TRUE);

      $n = $n + 1;
    }  // while there's more panels under review
    form_hidden_value("NumBids",$n);
  
    form_section ('Scheduling Information');

    echo "  <TR>\n";
    echo "    <TD COLSPAN=2>\n";
    echo "      The expo can schedule your panel into the\n";
    echo "      time slots available over the weekend.  The expo aims to\n";
    echo "      create a power packed agenda of balanced content across a \n";
    echo "      wide selection time slots.  Your flexibility is vital.<p>\n";
    echo "      Please pick your top three preferences for times when you'd like to run ";
    echo "      your panel.  <i>Mornings</i> are 9am to noon; <i>Early Afternoon</i> is noon to 3:00 p.m.;"; 
    echo "      <i>Late Afternoon</i> is 3:00 p.m. to 5:00 p.m.  We'll do our best to meet your ";
    echo "      preferences.  Remember: the less you use the 'Prefer Not' option, ";
    echo "      the easier it is for us to meet your scheduling needs.";
    echo "    </TD>\n";
    echo "  </TR>\n";
  
    global $CLASS_DAYS;
    global $CLASS_DAYS;
    global $BID_SLOTS;

    $DAYS = $CLASS_DAYS;

    echo "  <TR>\n";
    echo "    <TD COLSPAN=2>\n";
    echo "      <TABLE BORDER=1>\n";
    echo "        <TR VALIGN=BOTTOM>\n";
    echo "          <TH></TH>\n";
    foreach ($DAYS as $day)
       echo "          <TH>{$day}</TH>\n";
    echo "        </tr>\n";
    foreach ($BID_SLOTS['All'] as $main_slot) {
    echo "        <TR ALIGN=CENTER>\n";
    echo "          <TH>{$main_slot}</TH>\n";
    foreach ($DAYS as $day)
      if (in_array($main_slot,$BID_SLOTS[$day]))
	  		schedule_table_entry ("{$day}_{$main_slot}");
      else
	  		echo "          <TD>&nbsp;</TD>\n";
      echo "        </tr>\n";
    }
    echo "      </TABLE>\n";
    echo "    </TD>\n";
    echo "  </tr>\n";

    $text = "Are there any other scheduling constraints on your time?  For\n";
    $text .= "example, are you proposing another class, panel, or performance? \n";
    form_textarea ($text, 'SchedulingConstraints', 5);

    form_submit ("Submit");


    echo "</TABLE>\n";
    echo "</FORM>\n";
    }
  else 
  {
    echo "Unfortunately, there are no panels awaiting panelists at this time.  Please ";
    echo "do check here later, panels may be added for consideration in the future or ";
    echo "existing panels may require additional volunteers.";
  }


  mysql_free_result ($result);
}

/*
 * process_bid_panel_form
 *
 * Validate the bid information and write it to the BidTimes and 
 */

function process_bid_panel_form ()
{
  if (out_of_sequence ())
    return display_sequence_error (false);

  dump_array ('$_POST', $_POST);

  // Make sure that the user is logged in

  if (! isset ($_SESSION[SESSION_LOGIN_USER_ID]))
    return display_error ('You must <a href="index.php">login</a> or <a href="/index.php?action=5">register</a> before submitting a panel.');


  // Always hopeful...

  $form_ok = TRUE;

  // Event Information
  $form_ok &= validate_int ('NumBids', 1, 100, 'Number of Bids');
  for ($n=0; $n < $_POST['NumBids']; $n++)
  {
    if ( isset($_POST['Panelist-'.$n]) ||  isset($_POST['Moderator-'.$n]) )
      $form_ok &= validate_string ('Expertise-'.$n);
  }
  
  // Scheduling Information
  global $CLASS_DAYS;
  global $BID_SLOTS;
  foreach ($CLASS_DAYS as $day)
  	foreach ($BID_SLOTS[$day] as $slot)
  		$form_ok &= validate_schedule_table_entry ("{$day}_{$slot}", "{$day} {$slot}");


  // If any errors were found, abort now

  if (! $form_ok)
    return FALSE;

  $sql = "DELETE from PanelBids WHERE UserId=".$_SESSION[SESSION_LOGIN_USER_ID];
  // echo $sql;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Delete from BidTimes failed");

  for ($n=0; $n < $_POST['NumBids']; $n++)
  {
    if ( isset($_POST['Panelist-'.$n]) ||  isset($_POST['Moderator-'.$n]) )
    {
      $sql = "INSERT PanelBids SET ";
      $sql .= build_sql_string ('BidId', $_POST['BidId-'.$n],FALSE);
      $sql .= build_sql_string ('UserId', $_SESSION[SESSION_LOGIN_USER_ID]);

      if ( isset($_POST['Panelist-'.$n]) )
        $sql .= build_sql_string('Panelist',1);
        
      if ( isset($_POST['Moderator-'.$n]) )
        $sql .= build_sql_string('Moderator',1);

      $sql .= build_sql_string ('Expertise', $_POST['Expertise-'.$n]);
	
	  // echo $sql;
      $result = mysql_query ($sql);
      if (! $result)
        return display_mysql_error ("Add ".$_POST['BidId-'.$n]." to BidTimes failed");
	}
  }


  $sql = "DELETE from BidTimes WHERE UserId=".$_SESSION[SESSION_LOGIN_USER_ID];
  // echo $sql;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Delete from BidTimes failed");

  global $CLASS_DAYS;
  global $BID_SLOTS;
  foreach ($CLASS_DAYS as $day)
  	foreach ($BID_SLOTS[$day] as $slot) {
	  $sql = "INSERT into BidTimes (UserId, Day, Slot, Pref) values (";
	  $sql .= "{$_SESSION[SESSION_LOGIN_USER_ID]}, ";
	  $sql .= "'{$day}', ";
	  $sql .= "'{$slot}', ";
	  $sql .= "'";
	  $sql .= $_POST[str_replace(' ','_',"${day}_{$slot}")];
	  $sql .= "');";
	  $result = mysql_query ($sql);
	  if (! $result)
		return display_mysql_error ("Add {$day} {$slot} to BidTimes failed");

  	}



  $sql = 'SELECT DisplayName, EMail FROM Users WHERE UserId=';
  $sql .= $_SESSION[SESSION_LOGIN_USER_ID];
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query user information');

  // Sanity check.  There should only be a single row

  if (0 == mysql_num_rows ($result))
    return display_error ('Failed to find user information');

  $row = mysql_fetch_object ($result);

  $name = trim ("$row->DisplayName");
  $email = $row->EMail;

  $subject = '[' . CON_NAME . " - Bid] Panel Volunteering";

  $msg = "Thank you, $name, for volunteering to sit on or moderate panels at ".CON_NAME.".";
  $msg = "We'll be in contact shortly with panelist assignments when scheduling is completed.";

  //echo "subject: $subject<br>\n";
  //echo "message: $msg<br>\n";

  if (! intercon_mail ($send_to,
		       $subject,
		       $msg,
		       $email))
    display_error ('Attempt to send mail failed');

  return TRUE;
}
/*
 * display_bid_etc
 *
 * What to show when panel was successfully submitted.
 */

function display_bid_etc ()
{
  echo "<FONT SIZE=\"+2\">Thank you for offering to be a panelist or moderator for ";
  echo CON_NAME . "!</FONT>\n";
  echo "<P>\n";
  echo "The bid committee has been notified of your submission.\n";
  echo "<P>\n";

  $page = 'panelfollowup.html';

  if (! is_readable ($page))
  {
    if (! is_readable (TEXT_DIR."/$page"))
    {
      display_error ("Unable to read $page");
    }
    else
      include (TEXT_DIR."/$page");
  }
  else
    include ($page);
}

/*
 * set_panelists
 *
 * set panelists based on the bid form.
 */

function set_panelists ()
{
  global $PANELIST_TYPE;
  $Users = explode(".",trim ($_REQUEST['UserList']));
  foreach ($Users as $user)
  {
    if (strlen($user) > 0 )
    {
      //echo "<br>".trim ($_REQUEST['User-'.$user])."<BR>\n";
      $sql = "DELETE FROM GMs WHERE EventId=".$_REQUEST['EventId'];
      $sql .= " AND UserId=$user; ";
      
      // echo $sql;
      $insert_result = mysql_query ($sql);
      if (! $insert_result)
        return display_mysql_error ("GM Insertion failed", $sql);

      if (trim($_REQUEST['User-'.$user]) != $PANELIST_TYPE[0])
      {
        $sql = "INSERT INTO GMs SET EventId=".$_REQUEST['EventId'].",";
        $sql .= " UserId=$user, Role='".$_REQUEST['User-'.$user]."',";
        $sql .= ' UpdatedById=' . $_SESSION[SESSION_LOGIN_USER_ID];

        // echo $sql;
        $insert_result = mysql_query ($sql);
        if (! $insert_result)
          return display_mysql_error ("GM Insertion failed", $sql);
      }// if the panelist has a role
    }// if there is a value... the array has some empty bits

  } // for each panel bid

}

?>