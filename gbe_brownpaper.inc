<?php

/* gbe_brownpaper.inc - contains definitions and functions used with integrating with
 * the Brown Paper Tickets development API.  
 * 
 * NOTE:  This file MUST be included AFTER the gbe_ticketing file has been included.  
 * 
 * Last Updated 10/3/2013 by MDB
 *
 */
 
define ('BPT_MAIN_TICKETITEM_ID', '367968');
define ('BPT_EVENT_LINK', 'http://www.brownpapertickets.com/');
define ('BPT_COUNTRY_CODE', '228');

/* function get_bpt_event_list
 * 
 * Used to obtain an array of the current events we watch on the BPT website.
 *
 * Returns: The array of BPT event numbers.
 */
function get_bpt_event_list()
{
	$sql = sprintf("select * from BPTEventList");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no BPT Events in the database. Please add.");

	$event_list = array();
	while ($row = mysql_fetch_object($result))
	{
		array_push($event_list, $row->BPTEvent);
	}
	return $event_list;
}

/* function get_bpt_developer_id
 * 
 * Used to obtain the developer ID to be used with Brown Paper Tickets.
 *
 * Returns: the developer ID.
 */
function get_bpt_developer_id()
{
	$sql = sprintf("select * from BPTSettings limit 0, 1");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no BPT settings in the database. Please add.");

	return mysql_fetch_object($result)->DeveloperID;
}

/* function get_bpt_client_id
 * 
 * Used to obtain the client ID to be used with Brown Paper Tickets.
 *
 * Returns: the client ID.
 */
function get_bpt_client_id()
{
	$sql = sprintf("select * from BPTSettings limit 0, 1");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no BPT settings in the database. Please add.");

	return mysql_fetch_object($result)->ClientID;
}

/* function get_bpt_last_poll_time
 * 
 * Used to obtain the last time the system poled BPT for transactions.
 *
 * Returns: the last poll time ID.
 */
function get_bpt_last_poll_time()
{
	$sql = sprintf("select * from BPTSettings limit 0, 1");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no BPT settings in the database. Please add.");

	return mysql_fetch_object($result)->LastPollTime;
}

/* function set_bpt_last_poll_time
 * 
 * Used to set the last time the system poled BPT for transactions to current time.
 *
 * Returns: nothing
 */
function set_bpt_last_poll_time()
{
	$sql = sprintf("update BPTSettings set LastPollTime = '%s' where DeveloperId = '%s'",
		date("Y-m-d H:i:s", time()), get_bpt_developer_id());

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
}

/* function produce_XML_object_tree
 * 
 * Used to take the xml returned from the BPT server and turn it into something usable
 * in PHP.  It also does error checking.
 *
 * Returns: an xml tree for parsing.
 */
function produce_XML_object_tree($raw_XML) 
{
	libxml_use_internal_errors(true);
	try 
	{
		$xmlTree = new SimpleXMLElement($raw_XML);
	} 
	catch (Exception $e) 
	{
		// Something went wrong.
		$error_message = 'SimpleXMLElement threw an exception.';
		foreach(libxml_get_errors() as $error_line) 
		{
			$error_message .= "\t" . $error_line->message;
		}
		trigger_error($error_message);
		return false;
	}
	return $xmlTree;
}

/* function perform_bpt_api_call
 * 
 * Used to make various calls to the Brown Paper Tickets API system.
 *
 * Returns: a Simple XML object created by SimpleXMLElement()
 */
function perform_bpt_api_call($event_call)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $event_call);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSLVERSION, 3);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result_list_xml = curl_exec($ch);
	
	if (!$result_list_xml) 
		return display_error("Could not retrieve data from BPT:  " . curl_error($ch));
		
	if (0 == strcmp(trim($result_list_xml), "<?xml version=\"1.0\"?>"))
		return display_error("Could not retrieve data from BPT: Data not available " . 
			"on Brown Paper Tickets.");
	
	//echo $event_call . "<br><br>";
	
	curl_close($ch);
	return produce_XML_object_tree($result_list_xml);
}

/* function get_bpt_event_date_id
 * 
 * Used to get the date identifier from the BPT website.
 *
 * $event_id - the event id for the event to query
 * Returns: nothing.
 */
function get_bpt_event_date_id($event_id)
{
	$date_call = sprintf("https://www.brownpapertickets.com/api2/datelist?id=%s&event_id=%s", 
		get_bpt_developer_id(), $event_id);
	
	$date_xml = perform_bpt_api_call($date_call);
	
	$date_list = array();
	
	foreach ($date_xml->date as $date)
	{
		array_push($date_list, (string)$date->date_id);
	}
	
	return $date_list;
}

/* function get_bpt_price_list
 * 
 * Used to get the list of prices from BPT - which relates to ticket items on our system.
 *
 * Returns: the price list as an array of TicketItems.
 */
function get_bpt_price_list()
{
	$ticket_items = array();
	$event_array = get_bpt_event_list();
	
	foreach ($event_array as $event_id)
	{
		$date_list = get_bpt_event_date_id($event_id);
		
		foreach ($date_list as $date_id)
		{
			$price_call = sprintf("https://www.brownpapertickets.com/api2/pricelist?id=%s&event_id=%s&date_id=%s", 
				get_bpt_developer_id(), $event_id, $date_id);
			$price_list_xml = perform_bpt_api_call($price_call);
			
			foreach ($price_list_xml->price as $price)
			{
				$item = new TicketItem();
				$item->ItemId = sprintf("%s-%s", $event_id, (string)$price->price_id);
				$item->Title = (string)$price->name;
				$item->Active = (bool)$price->live;
				$item->Cost = (double)$price->value;
				$item->Description = "**Auto-Imported from BPT**";
				array_push($ticket_items, $item);
			}
		}
	}
	
	return $ticket_items;
}

/* function process_bpt_order_list
 * 
 * Used to get the list of current orders in the BPT database and update the 
 * transaction table accordingly.
 *
 * Returns: Number of transactions imported.
 */
function process_bpt_order_list()
{
	$count = 0;
	$event_array = get_bpt_event_list();
	
	
	foreach ($event_array as $event_id)
	{
		$order_list_call = sprintf("https://www.brownpapertickets.com/api2/orderlist?id=%s&event_id=%s&account=%s&includetracker=1", 
			get_bpt_developer_id(), $event_id, get_bpt_client_id());
		$order_list_xml = perform_bpt_api_call($order_list_call);
		
		foreach ($order_list_xml->item as $order)
		{		
			if (!transaction_reference_exists((string)$order->ticket_number))
			{
				create_trans_from_bpt_order($event_id, $order, $trans);
				if ($trans == null)
					continue;
				for ($i = 0; $i < (int)$order->quantity; $i++)
				{	
					$trans->save_to_db(true, false);
					$count++;
				}
			}
		}
	}
	set_bpt_last_poll_time();
	return $count;
}

/* function create_trans_from_bpt_order
 * 
 * Helper function for creating a Transaction object from an XML order object
 * from the Brown Paper Tickets API.
 *
 * $eventid - the specific event associated with the transaction.
 * $order - the XML order object from the Brown Paper Tickets website.
 * $transaction - the resulting transaction object.
 * Returns: an error if applicable.
 */
function create_trans_from_bpt_order($eventid, $order, &$trans)
{
	$trans = null;
	
	// If the ticket item doesn't exist, throw an error.
	
	$ticketItemId = sprintf("%s-%s", $eventid, (string)$order->price_id);
	
	if (!ticket_item_exists($ticketItemId))
		return display_error(sprintf("Ticket Item %s does not exist in database. " .
			"Please syncrhonize the ticket database with Brown Paper Tickets and retry.", 
			$ticketItemId));
	
	$ticket_item = new TicketItem();
	$ticket_item->load_from_itemid($ticketItemId);
	
	// If the user doesn't exist locally, create a stand in user.
	
	// Since we are having problems with Ticketing and OpenId, we are just going to indicate fake
	// users with a fake user email that the system won't use.  -MDB
	
	$PurchaserUserId = (int)substr((string)$order->tracker_id, 3);
	get_user($PurchaserUserId, $user);
	
	if ($user == null)
	{
		get_user_by_purchase_email((string)$order->email, $user);
	}
	
	if ($user == null)
	{
		$user_info = sprintf("Name:  %s  Phone:  %s  Email:  %s  Country:  %s  Order Time:  %s", 
			$order->fname . " " . $order->lname, $order->phone, $order->email, $order->country,
			$order->order_time);
		return display_error("User not found:  " . $user_info);
	}	
	
	$trans = new Transaction();
	$trans->ItemId = (string)$ticket_item->ItemId;
	$trans->UserId = (int)$user['UserId'];
	$trans->Amount = (float)sprintf("%0.2f", $ticket_item->Cost);	
	$trans->Reference = (string)$order->ticket_number;
	$trans->Status = 'Settled';
	$trans->TenderType = 'Charge';
	$trans->Cashier = null;
	$trans->PaymentDate = (string)$order->order_time;
	$trans->PaymentSource = "BPT";
	$trans->Memo = (string)$order->shipping_method . " " . (string)$order->tracker_id; 
	if (strlen(trim((string)$order->order_notes)) > 0)
		$trans->Memo .= " Notes:  " . (string)$order->order_notes;
	$trans->Override = false;
}

/* function create_ticket_refer_link
 * 
 * Used to create a link that will refer the user to BPT to purchase 
 * a given type of ticket.
 *
 * $TicketItemId - the TicketItemID to be used for the refer.  
 * Returns: the link that will be used for the refer.
 */
function create_ticket_refer_link($TicketItemId)
{
	if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
		$UserId = "None";
	else
		$UserId = $_SESSION[SESSION_LOGIN_USER_ID];
			
	$url = sprintf("http://www.brownpapertickets.com/event/ID-%s/%d", $UserId, 
		$TicketItemId);
	
	return $url;
}

/* function create_act_fee_refer_link
 * 
 * Used to create a link that will refer the user to BPT to pay the fee for 
 * act submission.  Assumes the first price is the act submission price.
 *
 * Returns: the link that will be used for the refer.
 */
function create_act_fee_refer_link()
{
	if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
		$UserId = "None";
	else
		$UserId = $_SESSION[SESSION_LOGIN_USER_ID];
	
	$sql = sprintf("SELECT * FROM BPTEventList where ActSubmitFee = '1' limit 0, 1");
	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There is not BPT act submittal event in the database. Please add.");

	$url = sprintf("http://www.brownpapertickets.com/event/ID-%s/%d", $UserId, 
		mysql_fetch_object($result)->BPTEvent);
	return $url;
}

/* function create_act_fee_refer_link
 * 
 * Used to create a link that will refer the user to BPT to pay the fee for 
 * act submission.  Assumes the first price is the act submission price.
 *
 * Returns: the link that will be used for the refer.
 */
function create_main_fee_refer_link()
{
	if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
		$UserId = "None";
	else
		$UserId = $_SESSION[SESSION_LOGIN_USER_ID];
		
	$sql = sprintf("SELECT * FROM BPTEventList where Primary = '1' limit 0, 1");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There is not BPT primary event in the database. Please add.");

	$url = sprintf("http://www.brownpapertickets.com/event/ID-%s/%d", $UserId, 
		mysql_fetch_object($result)->BPTEvent);
	return $url;
}

/* function user_paid_act_submittal_fee
 * 
 * Used to determine if a given UserId has paid for act submittal in the 
 * transactions table.
 *
 * $UserId:  the UserId in question
 * Returns:  true if the user has paid, false if not.
 */
function user_paid_act_submittal_fee($UserId)
{
	$sql = sprintf("SELECT * FROM BPTEventList where ActSubmitFee = '1' limit 0, 1");
	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There is not BPT act submittal event in the database. Please add.");
	
	$bpt_act_submit_fee_id = mysql_fetch_object($result)->BPTEvent;
	
	// Get Number of Acts Submitted

	$sql = sprintf("select * from Bids where UserId = '%d' and " .
		"Status in ('Pending','Under Review','Accepted','Rejected') and " .
		"GameType = 'Performance'", $UserId);
	
	//echo $sql . "<br>";
		
	$result = mysql_query($sql);
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);

	$acts_submitted = (mysql_num_rows($result));

	// Get number of act fees paid

	$sql = sprintf("select * from Transactions where UserId = '%d' and ItemId like '%s%%'",
		$UserId, $bpt_act_submit_fee_id);
	
	//echo $sql . "<br>";

	$result = mysql_query($sql);
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);

	$fees_paid = (mysql_num_rows($result));

	//echo "fees paid: $fees_paid acts_submitted: $acts_submitted" . "<br>";

	return ($fees_paid >= $acts_submitted);
}

?>



























