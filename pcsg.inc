<?php

// A PHP implementation of PCSG - the ProCon Schedule Generation algorithm
// Written by Nat Budin

// For more information about PCSG, see:
// http://wiki.aegames.org/index.php/ProCon_schedule_generation_algorithm


// set this to 1 to debug schedule generation
// requires Firebug or Firebug Lite
define("PCSG_DEBUG", 0);
define("PCSG_EVENT_BLOCK", 30); // also defined as EVENT_BLOCK in intercon_db.inc


if (PCSG_DEBUG) {
?>
<script type="text/javascript">
// In case we forget to take out console statements. IE becomes very unhappy when we forget. Let's not make IE unhappy
if(typeof(console) === 'undefined') {
    var console = {}
    console.log = console.error = console.info = console.debug = console.warn = console.trace = console.dir = console.dirxml = console.group = console.groupEnd = console.time = console.timeEnd = console.assert = console.profile = function() {};
}
</script>
<?php
}

class ScheduleBlock {
    public $startHour;
    public $endHour;
    public $interval;
    public $eventRuns;
    public $runDimensions;
    public $maxColumns;
  
  function __construct($startHour=null, $endHour=null) {
    $this->startHour = $startHour;
    $this->endHour = $endHour;
    $this->interval = 1;
    $this->eventRuns = array();
    $this->runDimensions = null;
    $this->maxColumns = null;
  }
  
  function getHours() {
    return $this->endHour - $this->startHour;
  }
  
  function addEventRun($eventRun) {
    array_push($this->eventRuns, $eventRun);
    if ($this->startHour == null || $eventRun->getStartHour() < $this->startHour) {
      $this->startHour = $eventRun->getStartHour();
    }
    
    if ($this->endHour == null || $eventRun->getEndHour() > $this->endHour) {
      $this->endHour = $eventRun->getEndHour();
    }
  }
  
  function computeRunDimensions() {
    $columnReservations = new ColumnReservationSet();
    $runDimensions = array();  
    $upcomingRuns = array();
    foreach ($this->eventRuns as $run) {
      $upcomingRuns[$run->id] = $run;
    }
  
    $this->maxColumns = 0;
    $lastColumn = 0;
    
    // loop through each hour
    $now = $this->startHour;
    while ($now < $this->endHour) {
      pcsg_debug("It is now $now:00");
      
      // which runs are starting now?
      $currentRuns = $this->getCurrentRuns($upcomingRuns, $now, $runDimensions);

      // have any of our column reservations expired?
      $columnReservations->expire($now);
      
      if ($columnReservations->isEmpty() && $lastColumn > 0) {
        pcsg_debug("We have no more reserved columns; resetting column count");
        $lastColumn = 0;
      }
      
      // reserve columns for current runs
      $this->reserveColumns($currentRuns, $columnReservations);

      // set last column value
      $lastColumn = $this->setLastColumn($columnReservations, $upcomingRuns, $lastColumn);
      
      
      if (count($currentRuns) > 0 && $lastColumn >= $this->maxColumns) {
        $this->maxColumns = $lastColumn + 1;
      }

      // current run order might have changed because of column sorting, re-sort the list
      $sortedCurrentRuns = array();
      foreach ($currentRuns as $currentRun) {
        $sortedCurrentRuns[$columnReservations->getRunColumn($currentRun->id)] = $currentRun;
      }
      ksort($sortedCurrentRuns);
      $scrDebug = "";
      foreach ($sortedCurrentRuns as $scr) { $scrDebug .= $columnReservations->getRunColumn($scr->id) . ", "; }
      pcsg_debug("Sorted current run columns: " . $scrDebug);
      
      $eventWidth = 100.0 / ($lastColumn + 1);
      $left = 0.0;
      $lastColnum = 0;
      foreach ($sortedCurrentRuns as $currentRun) {
        $colnum = $columnReservations->getRunColumn($currentRun->id);
        if ($colnum - $lastColnum > 0) {
          $left += ($colnum - $lastColnum) * $eventWidth;
        }
        $span = 1;
        
        for ($checkColnum = $colnum + 1; $checkColnum <= $lastColumn; $checkColnum++) {
          pcsg_debug("Run ".$currentRun->id." checking column ".$checkColnum." for spannability");
          if ($columnReservations->columnFreeDuring($checkColnum, $currentRun)) {
            pcsg_debug("Run ".$currentRun->id." spanning over column ".$checkColnum);
            // we can span over this column
            $span++;
          } else {
            break;
          }
        }
        
        $runDimensions[$currentRun->id] = new RunDimensions(
          $currentRun,
          $left,
          $eventWidth * $span,
          (($currentRun->getStartHour() - $this->startHour) / $this->getHours()) * 100.0,
          ($currentRun->getHours() / $this->getHours()) * 100.0
        );
        
        if ($span > 1) {
          $left += ($span - 1) * $eventWidth;
        }
        $lastColnum = $colnum;
      }
      
      $now += $this->interval;
    }    
    $this->runDimensions = $runDimensions;
  }
  
  function getRunDimensions() {
    if ($this->runDimensions == null) {
      $this->computeRunDimensions();
    }
    return $this->runDimensions;
  }


  function getCurrentRuns($upcomingRuns, $now,  $runDimensions) {
    $currentRuns = array();
    foreach ($this->eventRuns as $run) {
      if (!(array_key_exists($run->id, $runDimensions))) {
        if ($now >= $run->getStartHour() && $now < $run->getEndHour() ) {
          array_push($currentRuns, $run);
          unset($upcomingRuns[$run->id]);
          pcsg_debug("Run " . $run->id . " is starting now");
        }
      }
    }
    return $currentRuns;
  }

  function reserveColumns ($currentRuns, $columnReservations){
    foreach ($currentRuns as $currentRun) {
      if ($columnReservations->runColumnReserved($currentRun->id)) {
        pcsg_debug("Not reserving a column for run ".$currentRun->id." because we already reserved one");
      } else {
        $colnum = $columnReservations->nextFreeColumn();    
        pcsg_debug("Reserving column $colnum for run " . $currentRun->id . " until " . $currentRun->getEndHour());
        $columnReservations->reserve($colnum, $currentRun);
      }
    }
  }

  function setLastColumn($columnReservations, $upcomingRuns, $lastColumn)
  {
      if (!$columnReservations->isEmpty()) {
        $planUntil = $columnReservations->getLastEndHour();
        $addedUpcomingReservations = false;
        
        foreach ($upcomingRuns as $upcomingRun) {
          if ($upcomingRun->getStartHour() >= $planUntil) {
            continue;
          }
          
          $colnum = $columnReservations->findFreeColumnForEventRun($upcomingRun);                   
          pcsg_debug("Reserving column $colnum for upcoming run " . $upcomingRun->id . " until " . $upcomingRun->getEndHour());        
          $columnReservations->reserve($colnum, $upcomingRun);
          $addedUpcomingReservations = true;
          unset($upcomingRuns[$upcomingRun->id]);
          
          if ($planUntil < $upcomingRun->getEndHour()) {
            $planUntil = $upcomingRun->getEndHour();
          }
        }        
        if ($addedUpcomingReservations) {
          // attempt to sort the columns to avoid blank spaces
          $columnReservations->sortColumns();
        } 
      
        $lastColumnNow = $columnReservations->getLastColumn();
      } else {
        $lastColumnNow = 0;
      }
      if ($lastColumnNow > $lastColumn) {
        $lastColumn = $lastColumnNow;
      }
      pcsg_debug("Last column now is ".$lastColumnNow.", which makes the last column count ".$lastColumn);
   
      return $lastColumn;
  }

}   // end class ScheduleBlock

function pcsg_get_default_blocks() {
  return array(
    new ScheduleBlock(17, 24, "Fri"),
    new ScheduleBlock(10, 24, "Sat"),
    new ScheduleBlock(9, 13, "Sun")
  );

}

class EventRun {
  public $startHour;
  public $hours;
  public $id;
  
  function __construct($startHour, $hours, $id) {
    $this->startHour = $startHour;
    $this->hours = $hours;
    $this->id = $id;
  }
  
  function getStartHour() {
	return $this->startHour;
  }
  
  function getEndHour() {
	return $this->startHour + $this->hours;
  }
  
  function setEndHour($hour) {
    $this->hours = $hour - $this->startHour;
  }
  
  function getHours() {
	return $this->hours;
  }
}

class ColumnReservation {
  public $startHour;
  public $endHour;
  public $eventRunIds;
  
  function __construct($eventRun) {
    $this->startHour = $eventRun->getStartHour();
    $this->endHour = $eventRun->getEndHour();
    $this->eventRunIds = array($eventRun->id);
  }
  
  function getStartHour() {
    return $this->startHour;
  }
  
  function getEndHour() {
    return $this->endHour;
  }
  
  function getEventRunIds() {
    return $this->eventRunIds;
  }
  
  function addEventRun($eventRun) {
    if ($eventRun->getStartHour() < $this->startHour) {
      $this->startHour = $eventRun->getStartHour();
    }
    if ($eventRun->getEndHour() > $this->endHour) {
      $this->endHour = $eventRun->getEndHour();
    }
    array_push($this->eventRunIds, $eventRun->id);
  }
  
  function getEventRunCount() {
    return count($this->eventRunIds);
  }
}

class ColumnReservationSet {
  public $reservations;
  public $runColumns;
  
  function __construct() {
    $this->clear();
  }
  
  function reserve($colnum, $eventRun) {
    pcsg_debug("Asked to reserve $colnum for run $eventRun->id");
    if (array_key_exists($colnum, $this->reservations)) {
      $this->reservations[$colnum]->addEventRun($eventRun);
    } else {
      $this->reservations[$colnum] = new ColumnReservation($eventRun);
    }
    
    if (array_key_exists($eventRun->id, $this->runColumns)) {
      pcsg_debug("Warning: re-reserving column $colnum for run $eventRun->id");
    }
    $this->runColumns[$eventRun->id] = $colnum;
  }
  
  function clear() {
    $this->reservations = array();
    $this->runColumns = array();
  }
  
  function get($colnum) {
    return $this->reservations[$colnum];
  }
  
  function getColumnNumbers() {
    return array_keys($this->reservations);
  }
  
  function expire($hour) {
    foreach (array_keys($this->reservations) as $colnum) {
      if ($this->reservations[$colnum]->getEndHour() <= $hour) {
        unset($this->reservations[$colnum]);
        pcsg_debug("Column reservation for column $colnum has expired");
      }
    }
  }
  
  function isEmpty() {
    return (count($this->reservations) == 0);
  }
  
  function nextFreeColumn() {
    $colnum = 0;
    while (array_key_exists($colnum, $this->reservations)) {
      $colnum += 1;
    }
    return $colnum;
  }
  
  function getLastEndHour() {
    $lastEndHour = null;
    foreach ($this->reservations as $reservation) {
      if ($lastEndHour == null || $reservation->getEndHour() > $lastEndHour) {
        $lastEndHour = $reservation->getEndHour();
      }
    }
    return $lastEndHour;
  }
  
  function getLastColumn() {
    return max(array_keys($this->reservations));
  }
  
  function findFreeColumnForEventRun($run) {
    // in the worst case, we'll need to reserve a new column
    $colnum = $this->getLastColumn() + 1;
        
    // can we re-use an existing reserved column which will expire before this event starts?
    foreach (array_keys($this->reservations) as $reservedColnum) {
      $reservationEnd = $this->reservations[$reservedColnum]->getEndHour();
      if ($reservationEnd <= $run->getStartHour()) {
        $colnum = $reservedColnum;
        break;
      }
    }
    
    return $colnum;
  }
  
  function columnFreeDuring($colnum, $run) {
    if (!array_key_exists($colnum, $this->reservations)) {
      return true;
    } else {
      $reservation = $this->get($colnum);
      if ($reservation->getStartHour() > $run->getStartHour() &&
          $reservation->getStartHour() >= $run->getEndHour()) {
        return true;
      } else {
        return false;
      }
    }
  }
  
  function getColumnEventRunCount($colnum) {
    return $this->reservations[$colnum]->getEventRunCount();
  }
  
  function recalculateRunColumns() {
    $this->runColumns = array();

    foreach (array_keys($this->reservations) as $colnum) {
      $reservation = $this->get($colnum);
      foreach ($reservation->getEventRunIds() as $runId) {
        $this->runColumns[$runId] = $colnum;
        pcsg_debug("Run ".$runId." ended up in ".$colnum);
      }
    }
  }
  
  // helper function for sorting columns - should help eliminate holes
  // in the schedule by allowing events to span columns if possible
  //
  // reservations that start earlier go first.
  // in case of two reservations starting at the same time, reservations that end
  // later go first.
  // finally, reservations with fewer events go first.
  static function columnCmp($a, $b) {
    if ($a->getStartHour() != $b->getStartHour()) {
      return $a->getStartHour() - $b->getStartHour();
    } else if ($a->getEndHour() != $b->getEndHour()) {
      return $b->getEndHour() - $a->getEndHour();
    } else {
      return $a->getEventRunCount() - $b->getEventRunCount();
    }
  }
  
  function sortColumns() {    
    pcsg_debug("Sorting columns");
    usort($this->reservations, array("ColumnReservationSet", "columnCmp"));
    $this->recalculateRunColumns();
  }
  
  function getRunColumn($eventRunId) {
    return $this->runColumns[$eventRunId];
  }
  
  function runColumnReserved($eventRunId) {
    return array_key_exists($eventRunId, $this->runColumns);
  }
}

class RunDimensions {
  public $left;
  public $width;
  public $top;
  public $height;
  public $run;
  
  function __construct($run, $left, $width, $top, $height) {
	$this->run = $run;
	$this->left = $left;
	$this->width = $width;
	$this->top = $top;
	$this->height = $height;
  }
  
  function getCSS() {
    $css = "top: " . $this->top . "%; ";
    $css .= "left: " . $this->left ."%; ";
    $css .= "width: " . $this->width ."%; ";
    $css .= "height: " . $this->height ."%; ";
    $css .= "position: absolute;";
    return $css;
  }
}

function pcsg_debug($str) {
  if (PCSG_DEBUG) {
    echo "<script type=\"text/javascript\">console.log('$str')</script>\n";
  }
}



?>
