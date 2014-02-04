<?php
header('Content-Type: application/excel');
header('Content-Disposition: attachment; filename="sample.csv"');


include ("gbe_acttechinfo.inc");
include ("intercon_db.inc");
include ("files.php");
include ("gbe_users.inc");

if (! intercon_db_connect ())
{
  echo ('Failed to establish connection to the database');
  exit ();
}

if (isset($_REQUEST['ShowId']) )
  $ShowId = $_REQUEST['ShowId'];
elseif (isset($_POST['ShowId']) )
  $ShowId = $_POST['ShowId'];
else
{
	echo "Problem #1";
 	exit();
} 




// set up column headers for table
$headers = array();
$headers[] = "Order";
$headers[] = "Performers";
$headers[] = "Troupe"; 
$headers[] = "Rehearsal Slot";


get_acttech_display_settings($_REQUEST['ShowId'], &$Settings);
foreach ($Settings as $setting)
  if ($setting->On && $setting->Type != "none")
    $headers[] = $setting->DisplayText;

get_acttech_listings($tech_list, $act_list, $_REQUEST['ShowId']);
 
$n = 0;
$display_out = array();
foreach ($tech_list as $key => $tech_item)
{
  //echo "Key: ".$key;
  //echo "ActTechInfoId: ".$tech_item->ActTechInfoId;

  // handle the linked in show, act, performer information
  if ($act_list[$key]->RehearsalId > 0)
  {
    $rehearsal = new Run();
    $rehearsal->load_from_RunId($act_list[$key]->RehearsalId);
  }
    
  get_users_for_act($act_list[$key]->ActId, &$performers);
  
  $display_array = array();

  $display_array[]=$act_list[$key]->Order;

  $names = "";
  foreach ($performers as $displayname)
    $names .= $displayname."<br>\n";
  $display_array[]=$names;
  $display_array[]=$act_list[$key]->GroupName;

  if ($act_list[$key]->RehearsalId > 0)
    $display_array[]=$rehearsal->Day.", ".start_hour_to_am_pm($rehearsal->StartHour);
  else 
    $display_array[]="No Rehearsal Time Selected";

  if ($tech_item->ActTechInfoId > 0)
  {
    $tech_array = $tech_item->dump_to_array();
  
    // dump all the values of the act tech info forms
    reset($Settings);

    foreach ($Settings as $setting)
    {
      if ($setting->On)
      {
        if ($setting->Type == "file")
          $display_array[] = make_link($tech_array[$setting->ColumnName]);
        elseif ($setting->Type == "time")
          $display_array[] = $tech_array[$setting->ColumnName."Minutes"].":".
                           $tech_array[$setting->ColumnName."Seconds"];
        elseif ($setting->Type == "checkbox")
          if ($tech_array[$setting->ColumnName] == 1)
            $display_array[] = "CHECKED";
          else
            $display_array[] = "";

        elseif ($setting->Type != "none")
          $display_array[] = $tech_array[$setting->ColumnName];
      }
    }
  }
  else 
  {
    reset($Settings);

    foreach ($Settings as $setting)
    {
      if ($setting->On && $setting->Type != "none")
      {
        $display_array[] = "Not available";
      }
    }
  }
  $display_out[] = $display_array;
  $n++;

}



$fp = fopen('php://output', 'w');
foreach ( $display_out as $line ) {
  //  $val = explode(",", $line);
    fputcsv($fp, $line);
}
fclose($fp);


/*
// Connect to the database

// Display boilerplate


  
// this can only be done by admins, or show staff.
if (!(user_has_priv (PRIV_CON_COM) || 
    user_is_gm_for_game ($_SESSION[SESSION_LOGIN_USER_ID], $_REQUEST['ShowId'])))
  return display_access_error ();

if (isset($_POST['Order0']))
   update_act_order($_POST);

$show = new Event();
$show->load_from_eventid($ShowId);


*/

?>