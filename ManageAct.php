<?php

// Include common stuff

include ("intercon_db.inc");
include ("gbe_users.inc");

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

$act_list = array();
get_acts_for_user($_SESSION[SESSION_LOGIN_USER_ID], $act_list);
show_user_act_list($act_list);


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
  get_show_list(&$shows);
  
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
    echo sprintf ("    <TD><a href=\"EditActDetail.php?action=%d&UserId=%d\">Edit Tech Info and Rehearsal Slot</a></TD>\n");
    echo "  </tr>\n";

  }
  
  echo "</table>\n";

}

?>