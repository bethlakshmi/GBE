<?php

/* TicketTesting.php - contains testing stuff for ticket integration.
 * 
 *
 */
 
include("intercon_db.inc");
include("gbe_ticketing.inc");
include("gbe_brownpaper.inc");

// Connect to the database -- Really should require staff privilege

if (!intercon_db_connect())
{
	display_mysql_error ('Failed to establish connection to the database');
	exit ();
}

// Standard header stuff

html_begin();

echo get_bpt_event_info()->title;
echo "<br>";
echo get_bpt_event_date_id();
echo "<br>";
process_bpt_order_list();

html_end();

?>

