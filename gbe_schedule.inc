<?php

class ScheduleGrid
{
	public $earliest;
	public $latest;
	public $num_cols;
	private $volunteer_cols;
	private $events_cols;
	public $signup_counts;

/* calculate_columns
 * Find an arrangement of schedule items such that no overlapping items are placed
 * in the same column. 
 * This function assumes that the array of schedule items (Run object) is sorted by start time. 
 * Returns an array of columns of events s.t. each event in a column starts after the previous one concludes. 
 * Within each column, events are indexed by their start time (which is unique in this column). The column has value
 * 0 for each block the event occupies aside from the one in which the event is actually located. Cells in which no 
 * event is situated have a value of -1. 
 */
  function calculate_columns($sched_items, $blocks){
    $this -> events_cols = array();
    $foo = array();
    $this -> events_cols[0] = array_pad($foo, $blocks, -1);
    $this -> volunteer_cols = array();
    $this -> volunteer_cols[0] = array_pad($foo, $blocks, -1);

    foreach ($sched_items  as &$item) 
    {
      
    if ($item->Event->IsOps == "Y" || $item->Event->IsConSuite == "Y") {  
      $this -> place_item_in_grid($item, $this-> volunteer_cols, $blocks);
    }
    else {
      $this -> place_item_in_grid($item, $this-> events_cols, $blocks);
    }
    }
  }

function place_item_in_grid($item, &$columns, $blocks){
      $title = $item->Event->Title;
      $start = $item->StartHour;
      $hours = $item->Event->Hours;


      foreach ($columns as &$col) 
      {	
        if ($col[$start] === -1) 
        {
          $col[$start] = $item;
          for ($i = 1; $i < $hours; $i++)
          {
            $col[$start + $i] = 0;    // mark the following cells so we don't put anything there
	  }
  	  return;     // we've placed this item
        }
      }
        // if we get here, we've exhausted the existing columns, so create a new one
  
      $new_col = array();
      $new_col = array_pad($new_col, $blocks,  -1);
      $new_col[$start] = $item; 
      for ($i = 1; $i < $hours; $i++)
      {
        $new_col[$i] = 0;    // mark the occupied cells
        $columns[] = $new_col;
        continue;
      }
    }
  

function create_schedule_table ($day, $start, $end ) {
	 
   $table_id = "events_" .$day;
   $table_class = "events";
   start_table($table_id, $table_class);
   print_events_header(count($this ->events_cols), count(  $this ->volunteer_cols));

  
   for ($i = $start; $i < $end; $i++) 
   {   
     $time =  start_hour_to_am_pm($i);
     echo "<tr><td class = \"time\">$time</td>";
     foreach ($this->events_cols as $col) 
     {
	$this -> place_event($col[$i]);
      }
     foreach ($this->volunteer_cols as $col) 
     {
	$this -> place_event($col[$i]);
      }

      echo "</tr>\n";
    }
    echo "</table>\n";
  }




  function place_event($event) {
  	 

       if ($event === -1){
         echo '<td>&nbsp</td>';  // no event here, but we need an empty cell
       }
       
       elseif ($event === 0) {
         // do nothing; print no cell here
       }
        else {   // must be an event
          $hours = $event->Event->Hours;
          $title_with_link = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d">%s</a>',
                                   SCHEDULE_SHOW_GAME,
                                   $event->EventId,
                                   $event->RunId,
                                   $event->Event->Title);

          $item_class = "event ".$event->Status;
          echo "<td rowspan=$hours class=\"$item_class\">$title_with_link<br>\n";
	  $room = preg_replace(" \(.*\)", "",  $event->Rooms);
          echo $room.'</td>';
        }

  }


}
	$events_rooms = array("Theater"=>1,"Vendor Hall"=>1, "Crispus Attucks"=>1, 
		    "Pool"=>1);		      


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

function write_events_table($events_array, $events_rooms, $label, $day, $today_start, $today_end) {
	$events_rooms = array("Theater"=>1,"Vendor Hall"=>1, "Crispus Attucks"=>1, 
		    "Pool"=>1);

	 echo "<table>";
 	 echo "<thead>";
	 $column_count = count($events_rooms) +1;
	 echo "<th colspan=$column_count>$day: $label</th>";
	 echo "</thead>";
 	 echo "<thead>";
	 echo "<th>Time</th>";
	 foreach (array_keys($events_rooms) as $room) {
 	     echo "<th>$room</th>";
	 }
  	 echo "</thead>";
	 echo "<tbody>";
	 for ($i = $today_start; $i<$today_end; $i++) {
	     $start_time = start_hour_to_am_pm($i);
	     echo "<tr><td>$start_time</td>";
	      $row = $events_array[$i];
	     	 foreach (array_keys($events_rooms) as $room){
		 	 
		     if (array_key_exists($room, $row)){
		     	
		        $item = $row[$room];
			if ($item === 0) {
  			   // don't write a cell here
			} 
          		$title_with_link  = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d">%s</a>',
                                   SCHEDULE_SHOW_GAME,
                                   $item->EventId,
                                   $item->RunId,
                                   $item->Event->Title);
		        $item_class = "event ".$item->Status;
			$rows = $item->Event->Hours;  
		     	echo "<td rowspan=$rows class=\"$item_class\">$title_with_link</td>";
		     }
		     else {
		       echo "<td></td>";
		     }
		 
	     }
	     echo "</tr>\n";
	 }
	 echo "</tbody></table>";	 
}

function build_volunteer_table($day)
{
	$vol_rooms = array("Theater"=>1,"Vendor Hall"=>1, "Crispus Attucks"=>1, 
		    "Haym Solomon"=>1, "Registration"=>1);

		      
	$bookings = array();
	$rooms = array();
	get_volunteer_bookings($bookings, $rooms, $day);
	$vol_array = array();
	$vol_array = array_pad($vol_array, 48, array());
	foreach ($bookings as $booking){
	
	    $start = $booking->StartHour;
	    $room = strip_room($booking->Rooms);
	    $raw_room = $booking->Rooms;
  	    if (array_key_exists($room, $vol_rooms)) {
	      $vol_array[$start][$room] = $booking;
	    }
	}
	return $vol_array;
}

function write_volunteer_table($vol_array, $today_start, $today_end) {
	$vol_rooms = array("Theater"=>1,"Vendor Hall"=>1, "Crispus Attucks"=>1, 
		    "Haym Solomon"=>1, "Registration"=>1);

	 echo "<table>";
 	 echo "<thead>";
	 echo "<th>Time</th>";
	 foreach (array_keys($vol_rooms) as $room) {
 	     echo "<th>$room</th>";
	 }
  	 echo "</thead>";
	 echo "<tbody>";
	 for ($i = $today_start; $i<$today_end; $i++) {
	     $start_time = start_hour_to_am_pm($i);
	     echo "<tr><td>$start_time</td>";
	      $row = $vol_array[$i];
	     	 foreach (array_keys($vol_rooms) as $room){
		 	 
		     if (array_key_exists($room, $row)){
		        $item = $row[$room];
		     	$title = $item->Event->Title;
		     	echo "<td>$title</td>";
		     }
		     else {
		       echo "<td></td>";
		     }
		 
	     }
	     echo "</tr>\n";
	 }
	 echo "</tbody></table>";	 
}

function build_conference_table($day)
{
	$conf_rooms = array("Thomas Paine A&B"=>1,"William Dawes A"=>1, "William Dawes B"=>1, 
		    "Molly Pitcher"=>1, "Crispus Attucks"=>1);

		      
	$bookings = array();
	$rooms = array();
	get_conference_bookings($bookings, $rooms, $day);
	$conf_array = array();
	$conf_array = array_pad($conf_array, 48, array());
	foreach ($bookings as $booking){
	
	    $start = $booking->StartHour;
	    $room = strip_room($booking->Rooms);
	    $raw_room = $booking->Rooms;
  	    if (array_key_exists($room, $conf_rooms)) {
	      $conf_array[$start][$room] = $booking;
	    }
	}
	return $conf_array;
}

function write_conference_table($conf_array, $today_start, $today_end) {
	$conf_rooms = array("Thomas Paine A&B"=>1,"William Dawes A"=>1, "William Dawes B"=>1, 
		      "Molly Pitcher"=>1, "Crispus Attucks"=>1);

	 echo "<table>";
 	 echo "<thead>";
	 echo "<th>Time</th>";
	 foreach (array_keys($conf_rooms) as $room) {
 	     echo "<th>$room</th>";
	 }
  	 echo "</thead>";
	 echo "<tbody>";
	 for ($i = $today_start; $i<$today_end; $i++) {
	     $start_time = start_hour_to_am_pm($i);
	     echo "<tr><td>$start_time</td>";
	      $row = $conf_array[$i];
	     	 foreach (array_keys($conf_rooms) as $room){
		 	 
		     if (array_key_exists($room, $row)){
		        $item = $row[$room];
		     	$title = $item->Event->Title;
		     	echo "<td>$title</td>";
		     }
		     else {
		       echo "<td></td>";
		     }
		 
	     }
	     echo "</tr>\n";
	 }
	 echo "</tbody></table>";	 

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