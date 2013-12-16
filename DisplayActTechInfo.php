<?php
include ("gbe_acttechinfo.inc");
include ("intercon_db.inc");
include ("files.php");
include ("gbe_users.inc");


// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

if (isset($_REQUEST['ShowId']) )
  $ShowId = $_REQUEST['ShowId'];
elseif (isset($_POST['ShowId']) )
  $ShowId = $_POST['ShowId'];
else
{
  display_error("Basic information is missing.  Please try again and if the 
    problem persists, call an administrator");   
  html_end ();
  exit ();
 
} 
  
// this can only be done by admins, or show staff.
if (!(user_has_priv (PRIV_CON_COM) || 
    user_is_gm_for_game ($_SESSION[SESSION_LOGIN_USER_ID], $_REQUEST['ShowId'])))
  return display_access_error ();

if (isset($_POST['Order0']))
  update_act_order($_POST);

$show = new Event();
$show->load_from_eventid($ShowId);

echo "<h2>Act Information for $show->Title</h2>";
echo "This is all the known act information.<br><br>\n";
echo "<form method=\"POST\" action=\"DisplayActTechInfo.php\">\n";
echo "<input type=\"hidden\" name=\"ShowId\" value=\"".$ShowId."\">\n";

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
  
start_table("acttech", "tablesorter");
table_header($headers);

get_acttech_listings($tech_list, $act_list, $_REQUEST['ShowId']);

$n = 0;
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

  $display_array[]=sprintf("<input type=\"text\" name=\"%s\" size=\"1\" value=\"%d\" >\n".
                           "<input type=\"hidden\" name=\"%s\" value=\"%d\">\n",
                          'Order'.$n,$act_list[$key]->Order,
                          'ActId'.$n,$act_list[$key]->ActId);

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
  row($display_array);
  $n++;

}
close_table();

echo "    <input type=\"submit\" value=\"Change Order of Acts\">";
echo "</form>";
// Add the postamble

html_end ();

function table_header($column_labels){
  echo "  <thead>\n";
  foreach ($column_labels as $label){
    header_cell($label."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"); 
  } 
  echo "</thead>\n";
}

function row($values){
  echo "  <tr>";
  foreach ($values as $value){
    cell($value); 
  } 
  echo "</tr>\n";
}

function start_table($table_id, $table_class){
  echo "<table id=\"$table_id\" class=\"$table_class\">\n";
}

function close_table(){
  echo "</table>\n";
}
function thead(){
  echo "<thead>\n";
}
function close_thead(){
  echo "</thead>\n";
}


function div_wrap($text, $div_class){
  echo "<div class=".$div_class."> $text </div>";
}

function cell($text){
  echo "<td>".$text."</td>";
}

function header_cell($text){
  echo "    <th>$text</th>\n";
}

function section($section_name){
   echo "<div class=" .$section_name . "_wrapper> \n";
}

function close_section(){
  echo "</div><br>\n"; 
}


?>