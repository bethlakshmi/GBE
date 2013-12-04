<?php

// Include common stuff

include ("intercon_db.inc");
include ("gbe_users.inc");
include ("bio_controller.inc");
include ("acttech_controller.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display the preamble

html_begin ();

if (!isset($_REQUEST['ActId']) || !isset($_REQUEST['ShowId']) )
{
  display_error("Basic information is missing.  Please try again and if the 
    problem persists, call an administrator");   
  html_end ();
  exit ();
 
}

// Set up the baseline
$ActId = $_REQUEST['ActId'];
$ShowId = $_REQUEST['ShowId'];
$UserId = $_SESSION[SESSION_LOGIN_USER_ID];

// All functions in this file require scheduling priv

if (!user_is_performer_for_act($_SESSION[SESSION_LOGIN_USER_ID],$ActId) || !allowing_edit())
{
  display_access_error ();
  html_end ();
  exit ();
}

//foreach ($_POST as $key => $value)
//  echo $key." = ".$value."<br>\n";

$Act = new Act();
$Act->load_from_actid($ActId);

// Update the Rehearsal
if ( isset($_POST['RunId']))
{
  process_rehearsal_choice($_POST['RunId'], $Act);

}
else
  display_error("REMINDER! - you have not yet selected a rehearsal option for this act.".
    "  Rehearsal slots are booked on a first come, first serve basis.  Please let us ".
    "know if you choose not to attend rehearsal by selecting the no rehearsal option");
    

// Update the Bio
if ( isset($_POST['BioId']))
{
  update_bio();
  
  echo "Your group's bio has been updated.  It can be viewed on the show's ";
  echo "<a href=\"http://localhost:8888/src/GBE/Schedule.php?action=25&EventId=$ShowId\">";
  echo "webpage</a>.<br>\n";

}

// Update the Act Tech Info
$errors = update_acttechinfo($_POST, $Act);

// run_unit_tests_acttech_controller();

$Act->save_to_db();

if (count($errors) >0)
{
  echo "<hr>\n";
  edit_act_info($ActId, $ShowId, $errors);
}

// Add the postamble

html_end ();


?>