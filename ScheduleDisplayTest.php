<?php
include ("gbe_run.inc");
include ("intercon_db.inc");

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

echo "<h2>Conference Data Test</h2>";
echo "This returns all data related to the conference - namely panels and classes.<br><br>";
get_conference_bookings ($Bookings,$Rooms);
  
echo "<b>Rooms are:</b><br><br>";

section("rooms");
div_wrap("Rooms", "section_header");

foreach ($Rooms as $room)
{
  div_wrap("Room", "label");
  div_wrap($room, "value");
}
close_section();

echo "<b>Bookings are:</b>";
/*
foreach ($Bookings as $key => $booking)
{
  echo "Booking: <i>key</i>= ".$key.", <i>StartTime</i>= ".$booking->StartHour.
    ", <i>Day</i>= ".$booking->Day.", <i>EventId</i>= ".$booking->EventId."<br>";
  echo "&nbsp;&nbsp;EventInfo: <i>Title</i>= ".$booking->Event->Title.
    ", <i>Blocks</i>= ".$booking->Event->Hours.", <i>Type</i>= ".$booking->Event->GameType."<br>";
}
  */
  
echo "<h2>Volunteer Event Data Test</h2>";
echo "This returns all data related to the volunteer opportunities - any event that is ";
echo "created with Schedule Ops and NOT an Act Rehearsal Slot.<br><br>";
get_volunteer_bookings ($Bookings2,$Tracks);
  
echo "<b>Tracks are:</b><br><br>";

foreach ($Tracks as $track)
{
  echo "Track: ".$track."<br>";
}

echo "<br><br><b>Bookings are:</b><br><br>";

foreach ($Bookings2 as $key => $booking)
{
  echo "Booking: <i>key</i>= ".$key.", <i>StartTime</i>= ".$booking->StartHour.
    ", <i>Day</i>= ".$booking->Day.", <i>EventId</i>= ".$booking->EventId."<br>";
  echo "&nbsp;&nbsp;EventInfo: <i>Title</i>= ".$booking->Event->Title.
    ", <i>Blocks</i>= ".$booking->Event->Hours.", <i>Type</i>= ".$booking->Event->GameType."<br>";
}


echo "<h2>General Event Data Test</h2>";
echo "This returns all data related to general weekend events - namely anything made ";
echo "through the Manage/Add Special Events menu.  Yes, it is viable that sometimes there is"; 
echo " no room.<br><br>\n";
get_general_bookings ($Bookings3,$Rooms3);
  
echo "<b>Rooms are:</b><br><br>\n";



foreach ($Rooms3 as $room)
{
  echo "Room: ".$room."<br>";
}

echo "<br><br><b>Bookings are:</b><br>";

section("bookings");

start_table();
table_header(array("Title", "Day", "Start Time", "End Time", "Type"));

foreach ($Bookings3 as $key => $booking)
{
  row(array($booking->Event->Title, $booking->Day, 
      start_hour_to_am_pm($booking->StartHour), 
      start_hour_to_am_pm($booking->StartHour + $booking->Event->Hours), 
      $booking->Event->GameType));

}
end_table();
close_section();
  
// Add the postamble

html_end ();

function table_header($column_labels){
  echo "<tr class=\"header_row\">";
  foreach ($column_labels as $label){
    cell($label); 
  } 
  echo "</tr>\n\n";
}

function row($values){
  foreach ($values as $value){
    cell($value); 
  } 
  echo "</tr>\n\n";
}

function start_table(){
  echo "<table>\n";
}

function end_table(){
  echo "</table>\n";
}

function div_wrap($text, $div_class){
  $foo =  "<div class=".$div_class.">";
  echo $foo;
  echo $text;
  echo "</div>";
}

function cell($text){
  $foo =  "<td>".$text."</td>";
  echo $foo;
}

function section($section_name){

   $foo =  "<div class=" .$section_name . "_wrapper> \n";
   echo $foo;
}

function close_section(){
  echo "</div><br>\n"; 
}
?>