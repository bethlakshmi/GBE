<?php
include("gbe_ticketing.inc");
include("gbe_brownpaper.inc");

// Run the synchronization process;
	
$count = process_bpt_order_list();
printf("Ticket Import Results:  %d new transactions added to the system.<br><br>", $count);
?>