<?php

/* gbe_brownpaper.inc - contains definitions and functions used with integrating with
 * the Brown Paper Tickets development API.  
 * 
 * Last Updated 7/25/2013 by MDB
 *
 */
 
define ('BPT_DEVELOPER_ID', '9MLmigzTE2');
define ('BPT_CLIENT_ID', 'marcus.deboyz@gmail.com');
define ('BPT_EVENT_ID', '431061');

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

/* function get_event_object
 * 
 * Used to load information about the GBE from the BPT website.
 *
 * Returns: nothing.
 */
function get_bpt_event_info()
{
	$event_call = sprintf("https://www.brownpapertickets.com/api2/eventlist?id=%s&event_id=%s", 
		BPT_DEVELOPER_ID, BPT_EVENT_ID);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $event_call);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$event_list_xml = curl_exec($ch);
	
	if (!$event_list_xml) 
		return display_error("Could not retrieve events from BPT:  " . curl_error($ch));
		
	if (0 == strcmp(trim($event_list_xml), "<?xml version=\"1.0\"?>"))
		return display_error("Could not retrieve events from BPT: Event ID " . 
			BPT_EVENT_ID . " doesn't exist on Brown Paper Tickets.");
	
	curl_close($ch);
	
	$event_xml = produce_XML_object_tree($event_list_xml);
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
	$event_call = sprintf("https://www.brownpapertickets.com/api2/datelist?id=%s&event_id=%s", 
		BPT_DEVELOPER_ID, BPT_EVENT_ID);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $event_call);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$date_list_xml = curl_exec($ch);
	
	if (!$date_list_xml) 
		return display_error("Could not retrieve date id from BPT:  " . curl_error($ch));
		
	if (0 == strcmp(trim($date_list_xml), "<?xml version=\"1.0\"?>"))
		return display_error("Could not retrieve date id from BPT: Event ID " . 
			BPT_EVENT_ID . " doesn't exist on Brown Paper Tickets.");
	
	curl_close($ch);
	
	$date_xml = produce_XML_object_tree($date_list_xml);
	return $date_xml->date->date_id;
}























