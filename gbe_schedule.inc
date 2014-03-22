<?php

function build_events_table($day, $bookings, $rooms)
{
  $events_array = array();
  $events_array = array_pad($events_array, 48, array());
  foreach ($bookings as $booking){
    $start = $booking->StartHour;
    $event_rooms = $booking->Rooms;
    $event_rooms = explode(",", $event_rooms);
    foreach ($event_rooms as $long_room) {
      $room = strip_room($long_room);
        if (array_key_exists($room, $rooms)) {
          for ($i = 0; $i < $booking->Event->Hours; $i++) {
            if ($events_array[$start + $i]) {
              $events_array[$start+$i][$room][] = $booking ;
          }
          else {
            $events_array[$start+$i][$room] = array($booking);               
          }
        }  
      }
    }
  }
  return $events_array;
}

function write_events_table($events_array, $events_rooms, $label, $day, $today_start, 
                            $today_end, $selected_type) {
  if ($selected_type = $label) {
     $selected = "selected";
  }
  else {
    $selected = "";
  }
  
  echo "<table class=\"schedule $day $label\" >";
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
      write_schedule_row($events_array, $i, $events_rooms); 
  }
  echo "</tbody></table>\n\n";   
}

function write_schedule_row ($events_array, $i, $events_rooms){
    $start_time = start_hour_to_am_pm($i);
    $row_empty="class=empty";
    $row = $events_array[$i];
    $cells_html = "";
    foreach (array_keys($events_rooms) as $room){  
      if (array_key_exists($room, $row)){
        if ($row[$room]) {                 // if there's an entry for this room
	  $row_empty = "";
          $cells_html .=  "    <td class=\"event\"> <ul>";
	    foreach ($row[$room] as $item) {
            $title_with_link  = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d">%s</a>',
                                     SCHEDULE_SHOW_GAME,
                                     $item->EventId,
                                     $item->RunId,
                                     $item->Event->Title);
            $cells_html .=  "    <li>$title_with_link</li>\n";
          }
	  $cells_html.= "</ul></td>";
        }
	}
        else {                             // if there's no entry for this room write an empty cell
          $cells_html .=  "    <td class=\"none\"></td>\n";
        
      }   
    }
    echo "<tr $row_empty><td class=\"time\">$start_time</td>\n";    
    echo $cells_html;
    echo "</tr>\n";
 
}


function strip_room($room){
  $result =  preg_replace("# \(.*\)#", "", $room);

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