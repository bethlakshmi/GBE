<?php
include("intercon_db.inc");
include("gbe_ticketing.inc");
include("gbe_brownpaper.inc");

// Connect to the database -- Really should require staff privilege

if (!intercon_db_connect())
{
	display_mysql_error ('Failed to establish connection to the database');
	exit ();
}

// Run the synchronization process;
	
$count = process_bpt_order_list();
printf("Ticket Import Results:  %d new transactions added to the system.<br><br>", $count);
?>