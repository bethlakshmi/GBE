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

foreach ($Rooms as $room)
{
  echo "Room: ".$room."<br>";
}

echo "<br><br><b>Bookings are:</b><br><br>";

foreach ($Bookings as $key => $booking)
{
  echo "Booking: <i>key</i>= ".$key.", <i>StartTime</i>= ".$booking->StartHour.
    ", <i>Day</i>= ".$booking->Day.", <i>EventId</i>= ".$booking->EventId."<br>";
  echo "&nbsp;&nbsp;EventInfo: <i>Title</i>= ".$booking->Event->Title.
    ", <i>Blocks</i>= ".$booking->Event->Hours.", <i>Type</i>= ".$booking->Event->GameType."<br>";
}
  
  
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
echo " no room.<br><br>";
get_general_bookings ($Bookings3,$Rooms3);
  
echo "<b>Rooms are:</b><br><br>";

foreach ($Rooms3 as $room)
{
  echo "Room: ".$room."<br>";
}

echo "<br><br><b>Bookings are:</b><br><br>";

foreach ($Bookings3 as $key => $booking)
{
  echo "Booking: <i>key</i>= ".$key.", <i>StartTime</i>= ".$booking->StartHour.
    ", <i>Day</i>= ".$booking->Day.", <i>EventId</i>= ".$booking->EventId."<br>";
  echo "&nbsp;&nbsp;EventInfo: <i>Title</i>= ".$booking->Event->Title.
    ", <i>Blocks</i>= ".$booking->Event->Hours.", <i>Type</i>= ".$booking->Event->GameType."<br>";
}
  
// Add the postamble

html_end ();


?>