<?php
include ("gbe_acttechinfo.inc");
include ("intercon_db.inc");
include ("files.php");
include ("gbe_users.inc");

// this can only be done by admins, as it's ugly and really for testing.
if (! user_has_priv (PRIV_CON_COM))
  return display_access_error ();

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

echo "<h2>Act Information</h2>";
echo "This is all the known act information.<br><br>\n";

// set up column headers for table
$headers = array();
$headers[] = "Performers";
$headers[] = "Troupe"; 
$headers[] = "Show";
$headers[] = "Rehearsal Slot";
$TechCol = get_columns_from_table( 'ActTechInfo' );
foreach ($TechCol as $columnname)
  if ($columnname != "ActTechInfoId" && $columnname != "ActId")
    $headers[] = $columnname;
  
start_table("bookings", "tablesorter");
table_header($headers);

get_acttech_listings($tech_list, $act_list);
get_show_list(&$shows);

foreach ($tech_list as $key => $tech_item)
{
  // handle the linked in show, act, performer information
  $show = $shows[$act_list[$key]->ShowId];
  $rehearsal = new Run();
  $rehearsal->load_from_RunId($act_list[$key]->RehearsalId);
  get_users_for_act($act_list[$key]->ActId, &$performers);
  
  $display_array = array();

  $names = "";
  foreach ($performers as $displayname)
    $names .= $displayname."<br>\n";
  $display_array[]=$names;
  $display_array[]=$act_list[$key]->GroupName;
  $display_array[]=$show['Title'];
  $display_array[]=$rehearsal->Day.", ".start_hour_to_am_pm($rehearsal->StartHour);

  $tech_array = $tech_item->dump_to_array();
  
  // dump all the values of the act tech info forms
  reset($TechCol);
  foreach ($TechCol as $columnname)
  {
    if ($columnname != "ActTechInfoId" && $columnname != "ActId")
      if ($columnname == "MusicPath")
        $display_array[] = make_link($tech_array[$columnname]);
      else
        $display_array[] = $tech_array[$columnname];

  }
  row($display_array);

}
close_table();

  
// Add the postamble

html_end ();

function table_header($column_labels){
  echo "  <thead>";
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
  echo "<th>$text</th>";
}

function section($section_name){
   echo "<div class=" .$section_name . "_wrapper> \n";
}

function close_section(){
  echo "</div><br>\n"; 
}
?>