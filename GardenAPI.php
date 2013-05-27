<?php

include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

if (!is_logged_in()) {
    header('Location: index.php', 307);
} else {
    header("Content-Type: text/plain");

    echo "UniqueID=".$_SESSION[SESSION_LOGIN_USER_ID]."\n";
    echo "Name=".$_SESSION[SESSION_LOGIN_USER_NAME]."\n";
    echo "Email=".$_SESSION[SESSION_LOGIN_USER_EMAIL]."\n";
    echo "Gender=".$_SESSION[SESSION_LOGIN_USER_GENDER]."\n";
}
?>