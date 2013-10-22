<?php
include ("intercon_db.inc");
include ("files.php");
include ("login.php");
include ("gbe_ticketing.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

html_begin ();

  if (! user_has_priv (PRIV_STAFF))
    return display_access_error ();

  $sql = "SELECT UserId, DisplayName, EMail FROM Users Where openid != ''";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot execute query');

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find user with EMail address $EMail");

  intercon_db_connect (1);

  $Users = array();
  $n=0;
  echo "<table><tr><th>UserId</th><th>Email</th><th>Name</th><th>Password</th></tr>";
  while ($row = mysql_fetch_object ($result))
  {
    $User = array($row->UserId, $row->EMail, $row->DisplayName);
    $Users[$n] = $User;
    $n++;
  }
  foreach ($Users as $User)
  {
    echo "<tr><td>$User[0]</td><td>$User[1]</td><td>$User[2]</td>";
    
    // Generate a new random password
  
    $NewPassword = '';
    for ($i=0; $i<8; $i++)
    {
      $ascii = rand(ord('a'), ord('z'));
      $NewPassword .= chr($ascii);
    }
    echo "<td>$NewPassword</td></tr>";
    
    // Reconnect to the database with forced admin privileges in order
    // to reset user's password
  

    $sql = 'UPDATE Users SET ';
    $sql .= build_sql_string ('HashedPassword', md5($NewPassword), FALSE);
    $sql .= ' WHERE UserId=' . $User[0];
    $result = mysql_query ($sql);
    if (! $result)
       display_mysql_error ('Cannot execute query');
 
  }
  echo "</table>";
  
html_end ();

?>
