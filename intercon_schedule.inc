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