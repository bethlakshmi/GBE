<?php

include("gbe_ticketing.inc");

// Connect to the database -- Really should require staff privilege

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Standard header stuff

html_begin ();

// Everything in this file requires registration to run

if (! user_has_priv (PRIV_REGISTRAR))
{
  display_access_error ();
  html_end ();
  exit ();
}

$sql = "select * from Transactions";
$result = mysql_query($sql);

if (!$result)
	return display_mysql_error ('Cannot execute query', $sql);

$row = mysql_fetch_object($result);
echo serialize($row);
echo "<br><br>";

$temp = new Transaction();
$temp->convert_from_sql_row($row);

echo serialize($temp);





/*
if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = LIST_GAMES;

switch ($action)
{
 case LIST_GAMES:
   list_games (LIST_BY_GAME);
   break;

 case LIST_GAMES_BY_TIME:
   list_games (LIST_BY_TIME);
   break;

 case ADD_RUN:
   add_run_form (false);
   break;

 case PROCESS_ADD_RUN:
   if (process_add_run ())
     list_games ();
   else
     add_run_form (false);
   break;

 case PROCESS_EDIT_RUN:
   if (process_add_run ())
     list_games ();
   else
     add_run_form (true);
   break;

 case EDIT_RUN:
   add_run_form (true);
   break;

 case LIST_ADD_OPS:
   add_ops();
   break;
 

 default:
   echo "Unknown action code: $action\n";
}

*/

html_end();


?>

