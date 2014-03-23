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

// All functions in this file require scheduling priv

if (! user_is_performer() )
{
  display_access_error ();
  html_end ();
  exit ();
}

if (isset($_REQUEST['ActId']) && 
    !user_is_performer_for_act($_SESSION['SESSION_LOGIN_USER_ID'],$_REQUEST['ActId']))
{
  display_access_error ();
  html_end ();
  exit ();
}

$errors = array();

if (isset($_REQUEST['ActId']) && isset($_REQUEST['ShowId']))
{
  echo "Thanks for picking an act";
  edit_act_info($_REQUEST['ActId'], $_REQUEST['ShowId'], $errors);
}
else 
{
  $act_list = array();
  get_acts_for_user($_SESSION['SESSION_LOGIN_USER_ID'], $act_list);
  if ( count($act_list) > 1)
  {
    show_user_act_list($act_list);
  }
  else if ( count($act_list) == 1 )
  {
    foreach ($act_list as $act)
      edit_act_info($act->ActId,$act->ShowId, $errors);
  }
  else
    echo "Error - your acts are not booked for any shows.  Please contact our show coordinator - info@burlesque-expo.com";
    
}

// Add the postamble

html_end ();

/* show_user_act_list
 *
 * Take a list of Acts and display it as a table that lets the user see
 * the title, the show, the date, and gives a link to edit specifics
 *
 * Arguments - act_list - an array of class Act
 */

function show_user_act_list(&$act_list)
{
  $shows = array();
  get_show_list($shows);
  
  echo "<table border=\"1\">\n";
  echo "  <tr valign=\"bottom\">\n";
  
  echo "    <TH>Title</TH>\n";
  echo "    <TH>Show</TH>\n";
  echo "    <TH>Details</TH>\n";
  echo "  </tr>\n";

  foreach ($act_list as $act)
  {
    echo "  <tr>\n";

    $thisShow = $shows[$act->ShowId];
    echo "<br>\n";
    echo "    <TD>".$act->get_Title()."</TD>\n";
    echo "    <TD>".$thisShow['Title']."</TD>\n";
    echo sprintf ("    <TD><a href=\"ManageAct.php?ActId=%d&ShowId=%d\">Edit Act Information</a></TD>\n",$act->ActId,$act->ShowId);
    echo "  </tr>\n";

  }
  
  echo "</table>\n";

}


?>