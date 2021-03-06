<?php
/* Cost.php - Contains the GUI for displaying the Convention costs and links to 
 * purchase for all users.
 * 
 * Last Updated 8/15/2013 by MDB
 *
 */
 
include("intercon_db.inc");
include("gbe_ticketing.inc");
include("gbe_brownpaper.inc");

// Connect to the database

if (!intercon_db_connect ())
{
  display_mysql_error('Failed to connect to ' . DB_NAME);
  exit ();
}

// Display boilerplate

html_begin ();

// Show the current cost

show_cost();

// Standard postamble

html_end();

/* function show_cost
 * 
 * Used to display the various ticket items that are available for purchase.
 */
function show_cost()
{
	get_ticketitem_list($TicketItems);

	display_header(sprintf("Ticket Purchase Options for %s<br>", CON_NAME));
	printf("Thank you for your interest in the %s!  ", CON_NAME);
	printf("Below are the ticket options available for purchase.  ");
	printf("There are several ways you can be a part of the convention, so ");
	printf("please read the descriptions carefully.<br><br>\n");
	
	echo "<table border=\"0\">\n";	
	foreach ($TicketItems as $item)
	{
		if ($item->Active)
			show_cost_for_single_item($item);
	}
	echo "</table><br>\n";
}

/* function show_cost_for_single_item
 * 
 * Used to display the cost information for a specific ticket item.
 *
 * $item - the TicketItem object to display.
 * Returns:  nothing.
 */
function show_cost_for_single_item($item)
{
    if (file_exists(TEXT_DIR.'/betweencosts.html'))
	  include(TEXT_DIR.'/betweencosts.html');	     

	echo "<tr valign=\"top\">\n";
	printf("  <th align=\"left\">%s </th>\n", $item->Title);
	//printf("  <td align=\"right\">$%0.2f </td>\n", $item->Cost);
	
	// Removed cost from this page per scratch.
	
	echo "  <td align=\"right\">&nbsp</td>\n";
	echo "</tr>\n";
	
	echo "<tr valign=\"top\">\n";
	printf("  <td align=\"left\" colspan=2>%s </td>\n", $item->Description);
	echo "</tr>\n";
		
	echo "<tr valign=\"top\">\n";
	printf("  <td align=\"left\" colspan=2>&nbsp</td>\n");
	echo "</tr>\n";
	
	$link = create_ticket_refer_link($item->ItemId);
	echo "<tr valign=\"top\">\n";
	printf("  <td align=\"left\" colspan=2><a href=\"%s\" target=\"_blank\">", $link);
	printf("Purchase %s from Brown Paper Tickets</tr>\n", $item->Title);

		
	echo "<tr valign=\"top\">\n";
	printf("  <td align=\"left\" colspan=2>&nbsp</td>\n");
	echo "</tr>\n";
	
}

?>
































