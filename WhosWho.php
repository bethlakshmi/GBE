<?php
include ("WhosWho.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();


// Do the work
if (array_key_exists ('action', $_REQUEST))
{
  $action = $_REQUEST['action'];
  $bio_users = array ();

  get_who_is_who ($action, $bio_users);
  display_who_is_who($action, $bio_users);

}
else if (array_key_exists ('show',$_REQUEST))
{
  $show = $_REQUEST['show'];

  get_who_is_who_for_show ($show);
}
 else
  display_error("No Who's Who category provided, please provide an action");
  
// Add the postamble

html_end ();


?>