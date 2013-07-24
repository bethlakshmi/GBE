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
    if (!process_ticket_item_edit())
		display_ticket_item_edit_form();
	else
		list_ticket_items();
	break;	
	
 /*
    if (! process_edit_user ())
      display_user_form_for_others ();
 
*/ 
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
	echo "Click on a special event title to edit or delete it.<br>\n";
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
		echo "  <td align=\"left\">$Item->Cost</td>\n";
		echo "  <td align=\"left\">$Item->Datestamp</td>\n";
		echo "</tr>\n";
	}
	
	echo "</table><br><br>\n";
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
	
	display_header ("Editing Ticket Item $TicketItem->ItemId\n\n");
	
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
    echo "<td align=\"left\"><input type=\"checkbox\" name=\"Active\" value=\"Active\" $checked></td></tr>\n";	
	
	form_text(30, 'Cost', 'Cost', 0, TRUE);
	form_submit('Add or Update Ticket Item');
	echo "</table>\n";
	echo "</form>\n";
	
	//http://test-expo.local/SpecialEvents.php?action=42
}

/* function process_ticket_item_edit
 * 
 * Used to process updates to the TicketItem database
 *  
 * Returns: nothing.
 */
function process_ticket_item_edit()
{
	// Make sure that only privileged users get here

	if (!user_has_priv(PRIV_STAFF))
		return display_access_error ();

	// Check for sequence errors

	if (out_of_sequence ())
		return display_sequence_error (false);
	
	$Item = new TicketItem();
	if (!$Item->convert_from_array($_POST))
		return false;
	$Item->save_to_db();
	return true;
}

html_end();


?>

