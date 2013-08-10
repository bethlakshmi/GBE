<?php

/* gbe_brownpaper.inc - contains definitions and functions used with integrating with
 * the Brown Paper Tickets development API.  
 * 
 * NOTE:  This file MUST be included AFTER the gbe_ticketing file has been included.  
 * 
 * Last Updated 8/5/2013 by MDB
 *
 */
 
define ('BPT_DEVELOPER_ID', '9MLmigzTE2');
define ('BPT_CLIENT_ID', 'marcus.deboyz@gmail.com');
define ('BPT_EVENT_ID', '431061');
define ('BPT_EVENT_LINK', 'http://www.brownpapertickets.com/event/431061');

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
 * Returns: nothing.
 */
function get_bpt_event_date_id()
{
	$date_call = sprintf("https://www.brownpapertickets.com/api2/datelist?id=%s&event_id=%s", 
		BPT_DEVELOPER_ID, BPT_EVENT_ID);
	
	$date_xml = perform_bpt_api_call($date_call);
	return $date_xml->date->date_id;
}

/* function get_bpt_price_list
 * 
 * Used to get the list of prices from BPT - which relates to ticket items on our system.
 *
 * Returns: the price list as an array of TicketItems.
 */
function get_bpt_price_list()
{
	$date_id = get_bpt_event_date_id();
	
	$price_call = sprintf("https://www.brownpapertickets.com/api2/pricelist?id=%s&event_id=%s&date_id=%s", 
		BPT_DEVELOPER_ID, BPT_EVENT_ID, $date_id);
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
 * Returns: Nothing.
 */
function process_bpt_order_list()
{
	$order_list_call = sprintf("https://www.brownpapertickets.com/api2/orderlist?id=%s&event_id=%s&account=%s", 
		BPT_DEVELOPER_ID, BPT_EVENT_ID, BPT_CLIENT_ID);
	
	$order_list_xml = perform_bpt_api_call($order_list_call);
	
	foreach ($order_list_xml->item as $order)
	{			
		for ($i = 0; $i < (int)$order->quantity; $i++)
		{	
			create_trans_from_bpt_order($order, $trans);
			
		}
	}
}

/* function create_trans_from_bpt_order
 * 
 * Helper function for creating a Transaction object from an XML order object
 * from the Brown Papter Tickets API.
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
			
	// If the user doesn't exist locally, create a stand in user.
	
	get_user_by_email((string)$order->email, $user);
	if ($user == null)
	{
		/* create user here */
		return display_error("Could not create user with email: $order->email");
	}
	
	
		
	$trans = new Transaction();
	$trans->ItemId = (int)$order->price_id;
	$trans->UserId = $user['UserId'];
			
	/* what if price id doesn't exist?  return null*/
		
	$trans->Reference = (string)$order->ticket_number;
	$trans->Status = 'Settled';
	$trans->TenderType = 'Charge';
	$trans->Cashier = null;
	$trans->Memo = sprintf("BPT Order Method:  %s  Time:  %s  Notes:  %s", 
		(string)$order->shipping_method, (string)$order->order_time, 
		(string)$order->order_notes);
}


	//<order_time>2013-08-05 12:25:09</order_time><date_id>870568</date_id><price_id>2482205</price_id><quantity>1</quantity><fname/><lname/><address/><city/><state/><zip/><country>United States</country><email/><phone/><cc>N/A</cc><shipping_method>Will Call</shipping_method><order_notes/><ticket_number>A28578748</ticket_number>
	
	/*	public $TransIndex;
	public $ItemId;
	public $UserId;
	public $Amount;
	public $Datestamp;
	public $Status;
	public $TenderType;
	public $Reference;
	public $Cashier;
	public $Memo;
	public $Override;
	*/




