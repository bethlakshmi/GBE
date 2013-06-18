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

// Do what we came here for

display_con_com_schedule ();

// Add the postamble

html_end ();

/*
 * display_con_com_schedule
 *
 * Display the ConCom meeting information from the Con table
 */

function display_con_com_schedule ()
{
  $sql = 'SELECT ConComMeetings FROM Con';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for ConComMeetings failed', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('No entries found in the Con table');

  echo "$row->ConComMeetings<P>\n";
}

?>