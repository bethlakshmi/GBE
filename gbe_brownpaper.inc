<?php

/* gbe_brownpaper.inc - contains definitions and functions used with integrating with
 * the Brown Paper Tickets development API.  
 * 
 * NOTE:  This file MUST be included AFTER the gbe_ticketing file has been included.  
 * 
 * Last Updated 8/16/2013 by MDB
 *
 */
 
define ('BPT_DEVELOPER_ID', '9MLmigzTE2');
define ('BPT_CLIENT_ID', 'marcus.deboyz@gmail.com');
define ('BPT_EVENT_ID', '431061');
define ('BPT_ACT_EVENT_ID', '431061');
define ('BPT_EVENT_LINK', 'http://www.brownpapertickets.com/event/431061');
define ('BPT_COUNTRY_CODE', '228');

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
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result_list_xml = curl_exec($ch);
	
	if (!$result_list_xml) 
		return display_error("Could not retrieve data from BPT:  " . curl_error($ch));
		
	if (0 == strcmp(trim($result_list_xml), "<?xml version=\"1.0\"?>"))
		return display_error("Could not retrieve data from BPT: Data not available " . 
			"on Brown Paper Tickets.");
	
	curl_close($ch);
	return produce_XML_object_tree($result_list_xml);
}

/* function get_bpt_event_info
 * 
 * Used to load information about the GBE from the BPT website.
 *
 * Returns: nothing.
 */
function get_bpt_event_info()
{
	$event_call = sprintf("https://www.brownpapertickets.com/api2/eventlist?id=%s&event_id=%s", 
		BPT_DEVELOPER_ID, BPT_EVENT_ID);
	
	$event_xml = perform_bpt_api_call($event_call);
	return $event_xml->event;
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
		BPT_DEVELOPER_ID, $event_id);
	
	$date_xml = perform_bpt_api_call($date_call);
	return $date_xml->date->date_id;
}

/* function get_bpt_price_list
 * 
 * Used to get the list of prices from BPT - which relates to ticket items on our system.
 *
 * $event_id - the event id for the event to query
 * Returns: the price list as an array of TicketItems.
 */
function get_bpt_price_list($event_id)
{
	$date_id = get_bpt_event_date_id($event_id);
	
	$price_call = sprintf("https://www.brownpapertickets.com/api2/pricelist?id=%s&event_id=%s&date_id=%s", 
		BPT_DEVELOPER_ID, $event_id, $date_id);
	$price_list_xml = perform_bpt_api_call($price_call);
	
	$ticket_items = array();
	foreach ($price_list_xml->price as $price)
	{
		$item = new TicketItem();
		$item->ItemId = (int)$price->price_id;
		$item->Title = (string)$price->name;
		$item->Active = (bool)$price->live;
		$item->Cost = (double)$price->value;
		$item->Description = "**Auto-Imported from BPT**";
		array_push($ticket_items, $item);
	}
	
	return $ticket_items;
}

/* function process_bpt_order_list
 * 
 * Used to get the list of current orders in the BPT database and update the 
 * transaction table accordingly.
 *
 * $event_id - the event id for the event to query
 * Returns: Number of transactions imported.
 */
function process_bpt_order_list($event_id)
{
	$order_list_call = sprintf("https://www.brownpapertickets.com/api2/orderlist?id=%s&event_id=%s&account=%s", 
		BPT_DEVELOPER_ID, $event_id, BPT_CLIENT_ID);
	
	$order_list_xml = perform_bpt_api_call($order_list_call);
	$count = 0;
	
	foreach ($order_list_xml->item as $order)
	{		
		if (!transaction_reference_exists((string)$order->ticket_number))
		{
			create_trans_from_bpt_order($order, $trans);
			for ($i = 0; $i < (int)$order->quantity; $i++)
			{	
				$trans->save_to_db(true, false);
				$count++;
			}
		}
	}
	return $count;
}

/* function create_trans_from_bpt_order
 * 
 * Helper function for creating a Transaction object from an XML order object
 * from the Brown Paper Tickets API.
 *
 * $order - the XML order object from the Brown Paper Tickets website.
 * $transaction - the resulting transaction object.
 * Returns: an error if applicable.
 */
function create_trans_from_bpt_order($order, &$trans)
{
	// If the ticket item doesn't exist, throw an error.
	
	if (!ticket_item_exists((int)$order->price_id))
		return display_error(sprintf("Ticket Item %d does not exist in database. " .
			"Please syncrhonize the ticket database with Brown Paper Tickets and retry.", 
			(int)$order->price_id));
	
	$ticket_item = new TicketItem();
	$ticket_item->load_from_itemid((int)$order->price_id);
	
	// If the user doesn't exist locally, create a stand in user.
	
	get_user_by_email((string)$order->email, $user);
	if ($user == null)
	{
		create_stand_in_user((string)$order->fname, (string)$order->lname, (string)$order->email, 
			(string)$order->phone, (string)$order->address, (string)$order->city, (string)$order->state, 
			(string)$order->zip, (string)$order->country);
		get_user_by_email((string)$order->email, $user);
		if ($user == null)
			return display_error(sprintf("Unable to create a local temporary user for: %s", 
			(string)$order->email));
	}	
	
	$trans = new Transaction();
	$trans->ItemId = (int)$ticket_item->ItemId;
	$trans->UserId = (int)$user['UserId'];
	$trans->Amount = (float)sprintf("%0.2f", $ticket_item->Cost);	
	$trans->Reference = (string)$order->ticket_number;
	$trans->Status = 'Settled';
	$trans->TenderType = 'Charge';
	$trans->Cashier = null;
	$trans->Memo = sprintf("BPT Order (%s) @ %s", 
		(string)$order->shipping_method, (string)$order->order_time);
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
 * $event_id - the event id for the event to query
 * Returns: the link that will be used for the refer.
 */
function create_ticket_refer_link($TicketItemId, $event_id)
{
	$date_id = get_bpt_event_date_id($event_id);
	
	$url = "http://www.brownpapertickets.com/addtocart/";
	$url .= sprintf("%s?event_id=%s&date_id=%s", $event_id, $event_id, $date_id);
	$url .= sprintf("&price_%s=1&shipping_%s=4&country_id=%s", 
		$TicketItemId, $date_id, BPT_COUNTRY_CODE);
	
	return $url;
}

/* function create_act_fee_refer_link
 * 
 * Used to create a link that will refer the user to BPT to pay the fee for 
 * act submission.  Assumes the first price is the act submission price.
 *
 * $event_id - the event id for the event to query
 * Returns: the link that will be used for the refer.
 */
function create_act_fee_refer_link($event_id)
{
	$date_id = get_bpt_event_date_id($event_id);
	$price_list = get_bpt_price_list($event_id);
	
	
	$url = "http://www.brownpapertickets.com/addtocart/";
	$url .= sprintf("%s?event_id=%s&date_id=%s", $event_id, $event_id, $date_id);
	$url .= sprintf("&price_%s=1&shipping_%s=4&country_id=%s", 
		$price_list[0]->ItemId, $date_id, BPT_COUNTRY_CODE);
	
	return $url;
}

?>



























