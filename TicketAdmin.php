<?php

/* TicketItem.php - contains GUI functions and interface for working with Ticketable Items.
 * 
 * Last Updated 7/22/2013 by MDB
 *
 */
 
include("gbe_ticketing.inc");

// Connect to the database -- Really should require staff privilege

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Standard header stuff

html_begin ();

// Everything in this file requires registration to run

if (! user_has_priv (PRIV_REGISTRAR))
{
  display_access_error ();
  html_end ();
  exit ();
}

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = TICKETITEM_LIST;

switch ($action)
{
 case TICKETITEM_LIST:
   list_ticket_items();
   break;

 case TICKETITEM_EDIT:
	display_ticket_item_edit_form();
	break;

 case TICKETITEM_EDIT_PROCESS:
	if (array_key_exists('Delete', $_POST))
	{
		process_ticket_item_delete();
		list_ticket_items();
	}
	else if (array_key_exists('Close', $_POST))
	{
		list_ticket_items();
	}
	else
	{
		process_ticket_item_edit();
		list_ticket_items();
	}
	break;	

 default:
   echo "Unknown action code: $action\n";
}

/* function list_ticket_items
 * 
 * Used to display ticket items to the admin console.
 *  
 * $edit_mode:  if true, display the list with edit options (links).
 * Returns: nothing.
 */
function list_ticket_items($edit_mode = false)
{
	get_ticketitem_list($TicketItems);	
	if (sizeof($TicketItems) == 0)
	{
		display_ticket_item_edit_form();
		return;
	}
	
	echo "<b>\n";
	echo "Click on a type of ticket to edit or delete it, or click the button below to add.<br>\n";
	echo "</b><br>\n";
	echo "<table border=\"1\">\n";
	echo "  <tr>\n";
	echo "    <th>Item ID</th>\n";
	echo "    <th>Title</th>\n";
	echo "    <th>Description</th>\n";
	echo "    <th>Active</th>\n";
	echo "    <th>Ticket Price</th>\n";
	echo "    <th>Update Date</th>\n";
	echo "  </tr>\n";
  
	foreach ($TicketItems as $Item)
	{
		echo "<tr valign=\"top\">\n";
		
	    printf ("  <td><a href=\"TicketAdmin.php?action=%d&TicketItemId=%d\">%s</a>\n",
			TICKETITEM_EDIT, $Item->ItemId, $Item->ItemId);
		echo "  <td align=\"left\">$Item->Title</td>\n";
		if (strlen($Item->Description) == 0)
			echo "  <td align=\"left\">&nbsp;</td>\n";	
		else
			echo "  <td align=\"left\">$Item->Description</td>\n";
		if ($Item->Active)
			echo "  <td align=\"left\">Yes</td>\n";
		else
			echo "  <td align=\"left\">No</td>\n";
		printf("  <td align=\"left\">%0.2f</td>\n", $Item->Cost);
		echo "  <td align=\"left\">$Item->Datestamp</td>\n";
		echo "</tr>\n";
	}
	echo "</table><br>\n";
	
	printf("<FORM METHOD=\"POST\" ACTION=\"TicketAdmin.php?action=%d\">", 
		TICKETITEM_EDIT);
	echo "<INPUT TYPE=\"submit\" VALUE=\"Add a Ticket Item\"></FORM><br>";
}

/* function display_ticket_item_edit_form
 * 
 * Used to display a form to add/edit ticket items to the system.
 *  
 * Returns: nothing.
 */
function display_ticket_item_edit_form()
{
	$TicketItem = new TicketItem();
	
	if ((array_key_exists('TicketItemId', $_REQUEST)) && ($_REQUEST['TicketItemId'] > 0))
	{
		$TicketItem->load_from_itemid($_REQUEST['TicketItemId']);
	}
	$seq = increment_sequence_number();
	
	foreach($TicketItem as $k => $v)
	{
		$_POST[$k] = $v;
	}
	
	display_header("Editing Ticket Item $TicketItem->ItemId\n\n");
	
	echo "<P><FONT COLOR=RED>*</FONT> Indicates a required field\n";
	print("<FORM METHOD=POST ACTION=TicketAdmin.php>\n");
	form_add_sequence($seq);
	printf("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n", TICKETITEM_EDIT_PROCESS);
	printf("<INPUT TYPE=HIDDEN NAME=TicketItemId VALUE=%d>\n", $TicketItem->ItemId);
	print("<TABLE BORDER=0>\n");
		
	// Note:  We will eventually populate the Item ID directly from BPT.
	
	form_text(10, 'Ticket Item ID (Must Match BPT ID)', 'ItemId', 0, TRUE);	
	form_text(80, 'Title', 'Title', 0, TRUE);
	form_text(80, 'Description', 'Description', 0);
	
	if ($TicketItem->Active == true)
		$checked = "checked";
	else
		$checked = "";	
		
    echo "<tr><td align=\"right\"><font color=\"red\">*</font>&nbsp;Active:</td>";
    echo "<td align=\"left\"><input type=\"checkbox\" name=\"Active\" ";
	echo "value=\"Active\" $checked></td></tr>\n";
	
	form_text(30, 'Cost', 'Cost', 0, TRUE);
	display_ticket_item_events();
	echo "<tr><td><br><br></td></tr>";
	form_submit3("Update Ticket Item", "Delete Item", "Delete", "Close Form", "Close");	
	echo "</table>\n</form>\n";
	
	
	//http://test-expo.local/SpecialEvents.php?action=42
}

/* function display_ticket_item_events
 * 
 * Used to display a a table with events to be associated with ticket items.
 * Function must be called within the context of a table.
 *  
 * Returns: nothing.
 */
function display_ticket_item_events()
{
	get_ticket_event_join_list($EventArray);
	
	echo "<tr><td><br>Events Purchased by This Item:<br></td></tr>";
	
	if (1 == true)
		$checked = "checked";
	else
		$checked = "";	
	
	foreach ($EventArray as $event)
	{
		printf("<tr><td align=\"right\"><input type=\"checkbox\" name=\"%d\" value=\"%d\" $checked></td>",
			$event['EventId'], $event['EventId']);
		printf("<td align=\"left\">Event ID %d:  %s</td></tr>\n", $event['EventId'], $event['Title']);	
	}

	//echo serialize($EventArray);
}

/* function process_ticket_item_edit
 * 
 * Used to process updates to the TicketItem table
 *  
 * Returns: nothing.
 */
function process_ticket_item_edit()
{
	// Make sure that only privileged users get here

	if (!user_has_priv(PRIV_STAFF))
		return display_access_error();

	// Check for sequence errors

	if (out_of_sequence ())
		return display_sequence_error(false);
	
	$Item = new TicketItem();
	$Item->convert_from_array($_POST);
	$Item->save_to_db();
}

/* function process_ticket_item_delete
 * 
 * Used to delete items from the TicketItem table
 *  
 * Returns: nothing.
 */
function process_ticket_item_delete()
{
	// Make sure that only privileged users get here

	if (!user_has_priv(PRIV_STAFF))
		return display_access_error();

	// Check for sequence errors

	if (out_of_sequence ())
		return display_sequence_error(false);
	
	// NOTE:  we need to add something that prevents a delete of the ticket item 
	// has been already purchased by a user.
	
	$Item = new TicketItem();
	$Item->convert_from_array($_POST);
	$Item->remove_from_db();
}

html_end();


?>

