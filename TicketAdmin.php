<?php

/* TicketAdmin.php - contains GUI functions and interface for working with Ticketable Items.
 * 
 * Last Updated 8/15/2013 by MDB
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

html_begin ();

// Everything in this file requires registration to run

if (!user_has_priv(PRIV_REGISTRAR))
{
	display_access_error ();
	html_end ();
	exit ();
}

if (array_key_exists('action', $_REQUEST))
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
			process_ticket_item_delete();
		else if (!array_key_exists('Close', $_POST))
			process_ticket_item_edit();
		list_ticket_items();
		break;	

	case TICKETITEM_SYNC:
		display_ticketitem_bpt_sync();
		break;

	case TICKETITEM_SYNC_PROCESS:
		if (!array_key_exists('Close', $_POST))
			process_ticket_item_bpt_sync();
		list_ticket_items();
		break;	

	case TRANSACTION_SYNC:
		display_transaction_bpt_sync();
		break;		
	
	case TRANSACTION_SYNC_PROCESS:
		if (!array_key_exists('Close', $_POST))
			process_transaction_bpt_sync();
		list_users_for_pos();
		break;	
	
	case POS_LISTUSERS:
		list_users_for_pos();
		break;
	
    case POS_LISTTICKETS:
		list_tickets_for_user();
		break;
		
	case POS_RECEIPT:
		if (array_key_exists('Manual', $_POST))
			show_ticket_receipt_form();
		else if (array_key_exists('Close', $_POST))
			list_users_for_pos();
		else
			header(sprintf("Location: %s", BPT_EVENT_LINK));
		break;
		
	case TRANSACTION_STATUS:
		list_ticket_status();
		break;
	
	default:
		echo "Unknown action code: $action\n";
}

/* function list_ticket_items
 * 
 * Used to display ticket items to the admin console.
 *  
 * Returns: nothing.
 */
function list_ticket_items()
{
	get_ticketitem_list($TicketItems);	
	if (sizeof($TicketItems) == 0)
	{
		display_ticket_item_edit_form();
		return;
	}
	
	display_header("Click on a type of ticket to edit or delete it, or click the button below to add.<br>");
	
	echo "</b>\n";
	echo "<table border=\"1\">\n";
	echo "  <tr>\n";
	echo "    <th>Item Id</th>\n";
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
		$TicketItem->load_from_itemid($_REQUEST['TicketItemId']);
	$seq = increment_sequence_number();
	
	foreach ($TicketItem as $k => $v)
		$_POST[$k] = $v;
	
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
	
	if ($TicketItem->Active == true)
		$checked = "checked";
	else
		$checked = "";	
		
    echo "<tr><td align=\"right\"><font color=\"red\">*</font>&nbsp;Active:</td>";
    echo "<td align=\"left\"><input type=\"checkbox\" name=\"Active\" ";
	echo "value=\"Active\" $checked></td></tr>\n";
	
	form_text(30, 'Cost', 'Cost', 0, TRUE);
	form_textarea('Description', 'Description', 0);
	display_ticket_item_events($TicketItem->ItemId);
	echo "<tr><td><br><br></td></tr>";
	form_submit3("Add/Update Ticket Item", "Delete Item", "Delete", "Close Form", "Close");	
	echo "</table>\n</form>\n";
}

/* function display_ticket_item_events
 * 
 * Used to display a a table with events to be associated with ticket items.
 * Function must be called within the context of a table.
 *  
 * $TicketItemId - The ID of the TicketItem being displayed.
 * Returns: nothing.
 */
function display_ticket_item_events($TicketItemId)
{
	get_event_list($events);
	
	echo "<tr><td><br>This Ticket Item Admits to These Events:<br></td></tr>";
	
	foreach ($events as $eventid => $event)
	{
		if (!$event['SpecialEvent'])
			continue;
			
		if (ticket_authorizes_event($TicketItemId, $eventid))
			$checked = "checked";
		else
			$checked = "";	
		
		printf("<tr><td align=\"right\">");
		printf("<input type=\"checkbox\" name=\"!!Event:%d\" value=\"%d\" $checked></td>",
			$eventid, $event['Title']);
		printf("<td align=\"left\">Event ID %d:  %s</td></tr>\n", 
			$eventid, $event['Title']);	
	}
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
	
	// Save or Update the Ticket Item.
	
	$Item = new TicketItem();
	$Item->convert_from_array($_POST);
	$Item->save_to_db();
	
	// Save or Update Event Ticket Relationships. 
	
	get_event_list($events);
	$auth_events = array();
	
	foreach($_POST as $k => $v)
	{
		if (substr($k, 0, 8) == "!!Event:")
			array_push($auth_events, substr($k, 8));
	}
	
	foreach ($events as $eventid => $event)
	{
		set_ticket_event_auth($Item->ItemId, $eventid, 
			in_array($eventid, $auth_events));
	}
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

	if (out_of_sequence())
		return display_sequence_error(false);
	
	$Item = new TicketItem();
	$Item->convert_from_array($_POST);
	
	// Check if we have a transaction in the table - don't delete in this case.
	
	if (is_ticketitem_used_in_transactions($Item->ItemId))
		return display_error("Cannot Remove Ticket Item $Item->ItemId: " .
			"It is used in a transaction.");
	
	remove_all_event_ticket_auth($Item->ItemId);
	$Item->remove_from_db();
}

/* function display_ticketitem_bpt_sync
 * 
 * Used to synchronize the ticket item list with Brown Paper Tickets.
 *  
 * Returns: nothing.
 */
function display_ticketitem_bpt_sync()
{
	display_header("Synchronize Ticket Types with Brown Paper Tickets");
	
	echo "<br>This feature copies the Brown Paper Tickets price list into the " .
		" Ticket Type system to be used with Ticket Receipting.  It will not ". 
		" remove exisitng ticket types.<br>\n";
	echo "Please select an option below.<br><br>\n";

	$seq = increment_sequence_number();
	
	printf("<FORM METHOD=\"POST\" ACTION=\"TicketAdmin.php?action=%d\">\n", 
		TICKETITEM_SYNC_PROCESS);
	printf("<TABLE BORDER=0>\n");
	form_add_sequence($seq);
	form_submit2("Synchronize Types with BPT", "Close Form", "Close");	
	echo "</TABLE></FORM>\n";
}

/* function process_ticket_item_bpt_sync
 * 
 * Used to actually perform the ticket item sync with BPT.
 *  
 * Returns: nothing.
 */
function process_ticket_item_bpt_sync()
{
	// Make sure that only privileged users get here

	if (!user_has_priv(PRIV_STAFF))
		return display_access_error();

	// Check for sequence errors

	if (out_of_sequence ())
		return display_sequence_error(false);
		
	// get the two lists
	
	$bpt_ticket_items = get_bpt_price_list(BPT_EVENT_ID);
	if (sizeof($bpt_ticket_items) == 0)
		return;	
	get_ticketitem_list($local_ticket_items);
	
	foreach ($bpt_ticket_items as $bpt_item)
	{
		$found = false;
		foreach ($local_ticket_items as $local_item)
		{
			if ($bpt_item->ItemId == $local_item->ItemId)
			{
				$found = true;
				break;
			}
		}
		if (!$found)
			$bpt_item->save_to_db();
	}
}

/* function display_transaction_bpt_sync
 * 
 * Used to synchronize the transaction table with Brown Paper Tickets.
 *  
 * Returns: nothing.
 */
function display_transaction_bpt_sync()
{
	display_header("Synchronize Ticket Transactions with Brown Paper Tickets");
	
	echo "<br>This feature copies the Brown Paper Tickets torder list into the " .
		" Transactions table to be used with Ticket Receipting.  It will not ". 
		" remove exisitng transactions.<br>\n";
	echo "Please select an option below.<br><br>\n";

	$seq = increment_sequence_number();
	
	printf("<FORM METHOD=\"POST\" ACTION=\"TicketAdmin.php?action=%d\">\n", 
		TRANSACTION_SYNC_PROCESS);
	printf("<TABLE BORDER=0>\n");
	form_add_sequence($seq);
	form_submit2("Synchronize Transactions BPT", "Close Form", "Close");	
	echo "</TABLE></FORM>\n";
}

/* function process_transaction_bpt_sync
 * 
 * Used to actually perform the transaction sync with BPT.
 *  
 * Returns: nothing.
 */
function process_transaction_bpt_sync()
{
	// Make sure that only privileged users get here

	if (!user_has_priv(PRIV_STAFF))
		return display_access_error();

	// Check for sequence errors

	if (out_of_sequence ())
		return display_sequence_error(false);
		
	// Run the synchronization process;
	
	$count = process_bpt_order_list(BPT_EVENT_ID);
	printf("Ticket Import Results:  %d new transactions added to the system.<br><br>", $count);
	$count = process_bpt_order_list(BPT_ACT_EVENT_ID);
	printf("Act Payment Import Results:  %d new transactions added to the system.<br><br>", $count);
	
}

/* function list_users_for_pos
 * 
 * Used to list the user table in anticipation of ticket receipting.
 *  
 * Returns: nothing.
 */
function list_users_for_pos()
{
	// There are no highlit users in this display, so just pass an empty array

	$highlight = array ();

	$link = sprintf('TicketAdmin.php?action=%d&Seq=%d',
		   POS_LISTTICKETS,
		   increment_sequence_number());

	// Display the form to allow the user to include the alumni in the list
	// of users to choose from and allow them to select one
	
	select_user('Select a User for Ticket Purchase or Modification<br>', $link, false, 
		TRUE, $highlight);
}

/* function list_tickets_for_user
 * 
 * Used to list the tickets purchased by this user.
 *  
 * Returns: nothing.
 */
function list_tickets_for_user()
{
	if ((!array_key_exists('UserId', $_REQUEST)) || ($_REQUEST['UserId'] <= 0))
		return display_error("Cannot list tickets purchased: no user selected.");
	
	$UserId = $_REQUEST['UserId'];
	get_user($UserId, $User);
	
	echo "<b>\n";
	printf("Tickets Purchased for User ID %s:  %s %s (%s)<br>\n", $UserId, 
		$User['FirstName'], $User['LastName'], $User['DisplayName']);
	echo "</b><br>\n";
	
	echo "You can purchase tickets with a credit card through Brown Paper Tickets, ";
	echo "or manually receipt tickets using cash or check below.\n";
	echo "<br><br>\n";
		
	show_user_ticket_table($UserId);
	
	printf("<FORM METHOD=\"POST\" ACTION=\"TicketAdmin.php?action=%d&UserId=%d\">\n", 
		POS_RECEIPT, $UserId);
	print("<TABLE BORDER=0>\n");
	form_submit3("Receipt with BPT", "Manually Receipt", "Manual", "Close Form", "Close");	
	echo "</TABLE></FORM>\n";
}

/* function list_ticket_status
 * 
 * Used to display the current ticket purchase status.  
 *  
 * Returns: nothing.
 */
function list_ticket_status()
{
	echo "<b>\n";
	printf("Ticket Purchase Status for %s:", CON_NAME);
	echo "</b><br><br>\n";
		
	show_ticket_status_table();
}

/* function show_ticket_receipt_form
 * 
 * Used to receipt in new transactions (purchase tickets manually).
 *  
 * Returns: nothing.
 */
function show_ticket_receipt_form()
{
	display_header("Ticket Receipting (Point of Sale)<br>");

	if ((!array_key_exists('UserId', $_REQUEST)) || ($_REQUEST['UserId'] <= 0))
		return display_error("Cannot list tickets purchased: no user selected.");
	
	$UserId = $_REQUEST['UserId'];
	get_user($UserId, $User);
	
	echo "<b>\n";
	printf("Tickets Purchased for User ID %s:  %s %s (%s)<br>\n", $UserId, 
		$User['FirstName'], $User['LastName'], $User['DisplayName']);
	echo "</b><br>\n";
	
	echo "This feature has not been implemented yet.<br><br>\n";
}

html_end();

?>

