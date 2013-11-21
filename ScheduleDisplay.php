<?php
include ("gbe_run.inc");
include ("intercon_db.inc");


// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

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
}
  
// Add the postamble

html_end ();


?>