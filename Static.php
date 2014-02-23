<?php

include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}



// Display boilerplate

html_begin ();


$page = $_REQUEST['page'] . '.html';

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

html_end ();

?>


