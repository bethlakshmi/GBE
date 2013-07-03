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

/*
$temp = new Transasction();
$temp->TransIndex = 6;
$temp->ItemID = 4;
$temp->UserID = 2;
$temp->Amount = 6.50;
$temp->AdjustmentAmt = 0.0;
$temp->Status = 'Posted';
$temp->TenderType = 'Cash';
$temp->Reference = '000111';
$temp->Memo = 'Hello Animals!';
$temp->Override = true;

$temp->save_to_db(true, false);
echo "<br><br>";
echo serialize($temp);
*/

/*
$tempItem = new TicketItem();

$tempItem->ItemId = 7;
$tempItem->Title = "cows!";
$tempItem->Description = "Hello World!!";
$tempItem->Active = true;
$tempItem->Cost = 333.33;

$tempItem->save_to_db();

$tempItem->load_from_itemid(7);
echo "<br><br>";
echo serialize($tempItem);
*/



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

