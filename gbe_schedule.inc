<?php

function build_events_table($day, $bookings, $rooms)
{
	$events_array = array();
	$events_array = array_pad($events_array, 48, array());
	foreach ($bookings as $booking){
	
	    $start = $booking->StartHour;
	    $room = strip_room($booking->Rooms);
	    $raw_room = $booking->Rooms;
  	    if (array_key_exists($room, $rooms)) {
	      $events_array[$start][$room] = $booking;
	      for ($i = 1; $i < $booking->Event->Hours; $i++)
	      	  $events_array[$start+$i][$room] = 0;
	    }
	}
	return $events_array;
}

function write_events_table($events_array, $events_rooms, $label, $day, $today_start, $today_end, $selected_type) {
  if ($selected_type = $label) {
     $selected = "selected";
  }
  else {
    $selected = "";
  }
  
  echo "<table class=\"schedule $day $label\" $class = $highlighted>";
  echo "<thead>";
  $column_count = count($events_rooms) +1;
  echo "<th colspan=$column_count >$day: $label</th>";
  echo "</thead>";
  echo "<thead>";
  echo "<th class>Time</th>";
  foreach (array_keys($events_rooms) as $room) {
    echo "<th >$room</th>";
  }
  echo "</thead>\n";
  echo "<tbody>";
  for ($i = $today_start; $i<$today_end; $i++) {
    $start_time = start_hour_to_am_pm($i);
    $row_empty="class=empty";
    $row = $events_array[$i];
    $cells_html = "";
    foreach (array_keys($events_rooms) as $room){  
      if (array_key_exists($room, $row)){
        $item = $row[$room];
        if ($item === 0) {
         $row_empty = "";
               // don't write a cell here
        } 
        else {          
	  $row_empty = "";
          $title_with_link  = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d">%s</a>',
                                   SCHEDULE_SHOW_GAME,
                                   $item->EventId,
                                   $item->RunId,
                                   $item->Event->Title);
          $item_class = "event ".$item->Status;
          if ($label=="Conference" && $item->Status != "Confirmed")
            $item_class = "event";
          $rows = $item->Event->Hours;  
          $cells_html .=  "    <td rowspan=$rows class=\"$item_class\">$title_with_link</td>\n";
        }
      }
      else {
        $cells_html .=  "    <td class=\"none\"></td>\n";
      }
    }
    echo "<tr $row_empty><td class=\"time\">$start_time</td>\n";
    echo $cells_html;
  echo "</tr>\n";
  }
  echo "</tbody></table>\n\n";   
}

function strip_room($room){
  $result =  ereg_replace(" \(.*\)", "", $room);

  return $result;
}


function table_header($column_labels){
  echo "<thead>";
  foreach ($column_labels as $label){
    header_cell($label); 
  } 
  echo "</thead>\n";
}

function print_events_header($events_cols, $vol_cols){
	 echo("<thead><th>Time</th><th colspan=$events_cols>Events</th>");
         echo("<th colspan=$vol_cols>Volunteer Opportunities</th></thead>");
}

function row($values){
  echo "</tr>";
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