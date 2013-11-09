<?php

// Some utility functions extracted out of Schedule.php that are shared with
// Thursday.php.

/*
 * write_hour
 *
 * Helper function to write the table cell containing the start and stop time
 * for the hour displayed in the first column of the schedule table
 */

function write_hour ($tHour)
{
  $hour_start = strftime ('%H:%M', $tHour);
  $hour_end = strftime ('%H:%M', $tHour + (60 * 60));
  $txt = $hour_start . '<BR>--<BR>' . $hour_end;
  write_cell ('TH', $txt);
}

/*
 * write_24_hour
 *
 * Helper function to write the table cell containing the start and stop time
 * for the hour displayed in the first column of the schedule table
 */

function write_24_hour ($hour)
{
  $hour_start = start_hour_to_12_hour ($hour);
  $hour_end = start_hour_to_12_hour ($hour + 1);
  $txt = "<b>" . $hour_start . "</b>";
  write_centering_table($txt);
}


/* 
 * time_blocks_to_datetime : returns a DateTime object representing a time of day
 * $time_in_blocks: number of EVENT_BLOCKS since 0:00 AM/midnight
 * $date: an optional DateTime object. If not provided, an arbitrary default
 *        date is used.
 */
function time_blocks_to_datetime ($time_in_blocks, $date="NULL")
{
  if ($date == "NULL") {
     $date = new DateTime("1/1/2000");
  }
  $time_in_minutes = $time_in_blocks * EVENT_BLOCK;
  $hour = floor($time_in_minutes / 60);
  $minutes = $time_in_minutes % 60;
  $date -> setTime($hour, $minutes);
//  $t = $date->format("Hi:i A");
//   echo "blocks: $time_in_blocks   time_in_mintes: $time_in_minutes  minutes: $minutes  hour: $hour  time: $t <br>";
  return $date;
}

/* 
 * blocktime_to_string : returns a string representing this time as a string
 * $time_in_blocks: the number of EVENT_BLOCKS since midnight (00:00 AM)
 * $format: an optional format string. See PHP datetime man page for options.
 *     default format renders time of 27 as "1:30 PM" 
 */
function  blocktime_to_string($time_in_blocks, $format="H:i A")
{
  $time = time_blocks_to_datetime($time_in_blocks);
  return $time -> format($format);
}
 

/*
 * write_time_block : write a database time to the page as a formatted string
 * $time_in_blocks: time, represented as number of EVENT_BLOCKS since midnight 
 * $format: an optional format string. See PHP datetime man page for options.
 */

function write_time_block($time_in_blocks, $format = "H:i A")
{
  write_centering_table(blocktime_to_string($time_in_blocks, $format)); 
}

/*
 * write_cell
 *
 * Helper function to write a table cell with an optional attribute
 */

function write_cell ($type, $text, $attribute='')
{
  printf ("    <%s %s>%s</%s>\n",
	  $type, $attribute,
	  $text,
	  $type);
}


function write_centering_table($content, $bgcolor="#FFFFFF") {
  echo "<table style=\"width: 100%; height: 100%; border: 1px #777 solid; ";
  echo "background-color: $bgcolor; overflow: hidden;\">";
  echo "<tr>";
  echo "<td style=\"text-align: center; vertical-align: middle; overflow: hidden;\">";
  echo "<div style=\"margin-left: -1px; margin-top: -1px;\">$content</div>";
  echo "</td></tr></table>";
}

?>