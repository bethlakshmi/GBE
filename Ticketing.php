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

$tempItem = new TicketItem();

$tempItem->ItemId = 5;
$tempItem->Title = "ducks01";
$tempItem->Description = "Hello World!!";
$tempItem->Active = true;
$tempItem->Cost = 333.33;

$tempItem->save_to_db();

$tempItem->load_from_itemid(5);

echo serialize($tempItem);

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

