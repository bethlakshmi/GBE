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
          $title_with_link  =   $text = sprintf ('<a href="Schedule.php?action=%d&EventId=%d&RunId=%d">%s</a>',
                                   SCHEDULE_SHOW_GAME,
                                   $event->EventId,
                                   $event->RunId,
                                   $event->Event->Title);

          $item_class = "event ".$event->Status;
          echo "<td rowspan=$hours class=\"$item_class\">$title_with_link<br>\n";
          echo $event -> Rooms.'</td>';
        }

  }


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