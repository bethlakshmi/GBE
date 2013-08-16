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

$link = create_ticket_refer_link(2482205, BPT_EVENT_ID);

html_end();

?>

