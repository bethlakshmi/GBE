<?php

/* gbe_ticketing.inc - contains definitions used with ticketing and cashiering.
 * 
 * Last Updated 9/8/2013 by MDB
 *
 */
 
/* Class TicketItem 
 * 
 * This class represents something that you can purchase in the ticket system.
 * It can also be thought of a Point of Sale Item.  An object of this class is 
 * used to represent a row in the ticketitems table.  There should be a one-to-one
 * relationship between a ticketitem and a POS item at the payment manager side.
 * 
 */
 
class TicketItem
{
	// These public properties represent the various columns in the table.
	
	public $ItemId;
	public $Title;
	public $Description;
	public $Active;
	public $Cost;
	public $Datestamp;
	public $Userstamp;
	
	/* function load_from_itemid
	 * 
	 * Used to load a particular ticket item by item id from the database. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_itemid($ItemId)
	{
		$sql = sprintf("select * from TicketItems where ItemId = '%s'", 
			mysql_real_escape_string($ItemId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple ticket items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Ticket Item not found:  $ItemId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}
	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the TicketItems table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
		$this->ItemId = (string)$row->ItemId;
		$this->Title = (string)$row->Title;
		$this->Description = (string)$row->Description;
		$this->Active = (bool)$row->Active;	
		$this->Cost = (float)$row->Cost;
		$this->Datestamp = (string)$row->Datestamp;	
		$this->Userstamp = (int)$row->Userstamp;
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the TickeItem
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
		$this->ItemId = (string)$array['ItemId'];
		$this->Title = (string)$array['Title'];
		$this->Description = (string)$array['Description'];
		if (array_key_exists('Active', $array))
			$this->Active = (bool)$array['Active'];	
		else
			$this->Active = false;
		$this->Cost = (float)$array['Cost'];
		
		return true;
	}
	
	/* function save_to_db
	 * 
	 * Used to save the current status of the ticket item to the database.	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		// Sanitize database input.  Note, the build_sql_string uses 
		// mysql_real_escape_string() below.  
		
		if (!is_bool($this->Active))
			return display_error("Ticket Item active state is not in a valid format.");	
		if ((!is_double($this->Cost)) || ($this->Cost < 0))
			return display_error("Ticket Item purchase price is not in a valid format.");	
		if (strlen($this->Title) <= 0)
			return display_error("Required Ticket Item field \"Title\" is missing.");	
		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save TicketItem to DB:  not logged in.");	
				
		$this->Datestamp = date("Y-m-d H:i:s", time());	
		$this->Userstamp = $_SESSION[SESSION_LOGIN_USER_ID];
		
		// See if this is an insert or update.
		
		$this->ItemId = mysql_real_escape_string($this->ItemId);
		$sql = "select * from TicketItems where ItemId = '$this->ItemId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple ticket items found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update TicketItems set ";
			$sql .= build_sql_string('Title', $this->Title, false);
		}	
		else
		{
			$sql = "insert into TicketItems set ";
			$sql .= build_sql_string('ItemId', $this->ItemId, false);
			$sql .= build_sql_string('Title', $this->Title);
		}
		
		$sql .= build_sql_string('Description', $this->Description);
		$sql .= build_sql_string('Active', $this->Active);
		$sql .= build_sql_string('Cost', $this->Cost);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('Userstamp', $this->Userstamp);
			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where ItemId = '%s'", $this->ItemId);
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into TicketItems failed");
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this ticket item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		$sql = sprintf("delete from TicketItems where ItemId = '%s'", 
			mysql_real_escape_string($this->ItemId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
	}
}

/* Class Transaction 
 * 
 * This class is used to represent a transaction record - a record of an item
 * that a user has paid for.  It is basically a join of the User and TicketItem
 * Tables.  A user may have zero to many entries in this table - for each 
 * ticket purchased.  (Thus if a user buys two tickets to the same event, there will
 * be two rows in this table.)
 *
 * For purposes of this system, each transaction is a complete payment of a 
 * ticket.  There are no partial/incomplete payments of items, and we will not 
 * keep unapplied or on-account payments.  All transactions are billed and  
 * receipted at the same time.   
 * 
 */
 
class Transaction
{
	// These public properties represent the various columns in the table.
	
	public $TransIndex;
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
	
	/* function load_from_index
	 * 
	 * Used to load a specific transaction from the database. 
	 *
	 * $TransIndex:  The unique Index to be loaded.
	 * Returns: nothing.
	 */
	function load_from_index($TransIndex)
	{
		$sql = sprintf("select * from Transactions where TransIndex = '%d'", $TransIndex);
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple transactions found with same index");	
		if (1 != mysql_num_rows($result))
			return display_error("Transaction not found:  $TransIndex");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}
	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the Transactions table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
		$this->TransIndex = (int)$row->TransIndex;
		$this->ItemId = (string)$row->ItemId;
		$this->UserId = (int)$row->UserId;
		$this->Amount = (float)$row->Amount;
		$this->Datestamp = (string)$row->Datestamp;
		$this->Status = (string)$row->Status;
		$this->TenderType = (string)$row->TenderType;
		$this->Reference = (string)$row->Reference;
		$this->Cashier = (int)$row->Cashier;
		$this->Memo = (string)$row->Memo;
		$this->Override = (bool)$row->Override;
	}

	/* function save_to_db
	 * 
	 * Used to save the current status of the transaction to the database.	 
	 *
	 * $OnlineTrans: if true, this is an online purchase. 
	 * $Update: if true, attempt to update a transaction, not insert.
	 * Returns: nothing.
	 */
	function save_to_db($OnlineTrans, $Update)
	{	
		// Sanitize database input.  Note, the build_sql_string uses 
		// mysql_real_escape_string() below.  
		
		if (!is_integer($this->UserId))
			return display_error("Transaction User ID is not in a valid format.");	
		if (!is_double($this->Amount))
			return display_error("Transaction Amount is not in a valid format");			
		if (!is_bool($this->Override))
			return display_error("Transaction override state is not in a valid format");					
		$this->Datestamp = date("Y-m-d H:i:s", time());	
		
		$this->Userstamp = $_SESSION[SESSION_LOGIN_USER_ID];
		
		// Update the database.
		
		if ($Update)
		{
			if (!is_integer($this->TransIndex))
				return display_error("Transaction index is not in a valid format.");
			$sql = "update Transactions set ";
		}
		else
		{
			$sql = "insert into Transactions set ";
		}	
		
		$sql .= build_sql_string('ItemId', $this->ItemId, false);
		$sql .= build_sql_string('UserId', $this->UserId);
		$sql .= build_sql_string('Amount', $this->Amount);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('Status', $this->Status);
		$sql .= build_sql_string('TenderType', $this->TenderType);
		$sql .= build_sql_string('Reference', $this->Reference);
		if ($OnlineTrans)
		{
			$this->Cashier = null;
			$sql .= ", Cashier=null";
		}	
		else
		{
			if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
				return display_error("Cannot save transaction to DB:  not logged in.");
			$this->Cashier = $_SESSION[SESSION_LOGIN_USER_ID];
			$sql .= build_sql_string('Cashier', $this->Cashier);
		}
		$sql .= build_sql_string('Memo', $this->Memo);
		$sql .= build_sql_string('Override', $this->Override);
		
		if ($Update)
			$sql .= sprintf(" where TransIndex = '%d'", $this->TransIndex);
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert into Transactions failed");
		
		if (!$Update)
			$this->TransIndex = mysql_insert_id();
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this transaction from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{
		if (!is_integer($this->TransIndex))
			return display_error("Transaction index is not in a valid format.");

		$sql = sprintf("delete from Transactions where TransIndex = '%d'", $this->TransIndex);
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
	}
}	
			
/* Class EventTicketLink 
 * 
 * This class is used to represent which ticket items can be used as payment for
 * various Events in the Events table.  It is basically a join of the Event table
 * and the TicketItems table.  
 * 
 */
class EventTicketLink
{
	// These public properties represent the various columns in the table.
	
	public $ETIndex;
	public $EventId;
	public $TicketItemId;
	public $Description;
	public $Userstamp;

	/* function load_from_index
	 * 
	 * Used to load a specific event ticket link from the database. 
	 *
	 * $ETIndex:  The unique Index to be loaded.
	 * Returns: nothing.
	 */
	function load_from_index($ETIndex)
	{
		$sql = sprintf("select * from EventTicketLinks where ETIndex = '%d'", $ETIndex);
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple EventTicketLinks found with same index");	
		if (1 != mysql_num_rows($result))
			return display_error("EventTicketLink not found:  $ETIndex");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}	
	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the EventTicketLink table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
		$this->ETIndex = (int)$row->ETIndex;
		$this->EventId = (int)$row->EventId;
		$this->TicketItemId = (string)$row->TicketItemId;
		$this->Datestamp = (string)$row->Datestamp;
		$this->Userstamp = (int)$row->Userstamp;
	}
	
	/* function save_to_db
	 * 
	 * Used to save the current status of the event ticket link to the database.
     * The ETIndex is ignored.  	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		// Sanitize database input.  Note, the build_sql_string uses 
		// mysql_real_escape_string() below.  
		
		if (!is_integer($this->EventId))
			return display_error("EventTicketLink Event ID is not in a valid format.");			
		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save EventTicketLink to DB:  not logged in.");	
			
		// Make sure this link doesn't exist already.
	
		$this->TicketItemId = mysql_real_escape_string($this->TicketItemId);
		$sql = sprintf("select * from EventTicketLinks where EventId = '%d' and TicketItemId = '%s'",
			$this->EventId, $this->TicketItemId);
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error('Cannot execute query', $sql);
			
		if (mysql_num_rows($result) > 0)
		{
			$row = mysql_fetch_object($result);
			$this->convert_from_sql_row($row);
			return;
		}
			
		// Update the database.
				
		$this->Datestamp = date("Y-m-d H:i:s", time());	
		$this->Userstamp = $_SESSION[SESSION_LOGIN_USER_ID];
		
		$sql = "insert into EventTicketLinks set ";	
		
		$sql .= build_sql_string('EventId', $this->EventId, false);
		$sql .= build_sql_string('TicketItemId', $this->TicketItemId);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('Userstamp', $this->Userstamp);
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert into EventTicketLinks failed");
		$this->ETIndex = mysql_insert_id();
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this event ticket link from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{
		if (!is_integer($this->EventId))
			return display_error("EventTicketLink Event ID is not in a valid format.");

		$sql = sprintf("delete from EventTicketLinks where EventId = '%d' and TicketItemId = '%s'",
			$this->EventId, mysql_real_escape_string($this->TicketItemId));

		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
	}
}

/* function get_ticketitem_list
 * 
 * Used to obtain an array of the current ticket items
 *
 * $TicketItems - an array of TicketItem objects in the DB.
 * Returns: nothing.
 */
function get_ticketitem_list(&$TicketItems)
{
	$sql = sprintf("select * from TicketItems order by ItemId");
		 
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no TicketItems to list at this time.  Please add.");
	
	$TicketItems = array();
	
	while ($row = mysql_fetch_object($result)) 
	{
		$item = new TicketItem();
		$item->convert_from_sql_row($row);
		array_push($TicketItems, $item);
	} 
}

/* function is_ticketitem_used_in_transactions
 * 
 * Function is used to determine if a given ticket item ID is used in the
 * transactions table.
 *
 * $ItemId - the ticket item in question.
 * Returns: true if that's the case, false if not. 
 */
function is_ticketitem_used_in_transactions($ItemId)
{
	$sql = sprintf("select * from Transactions where ItemId = '%s'", 
		mysql_real_escape_string($ItemId));
		 
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
		
	return (mysql_num_rows($result) > 0);	 
}

/* function get_event_list
 *
 * Used to obtain an array of the current events
 *
 * $events - an array of Events in the DB. The array key is the event ID, and
 * the value is another array containing the event title.
 * Returns: nothing.
 */
function get_event_list(&$events)
{
	$sql = sprintf("select * from Events order by EventId");

	$result = mysql_query($sql);

	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are no Events to list at this time. Please add.");

	$events = array();
	while ($row = mysql_fetch_object($result))
	{
		$event = array();
		$event['Title'] = $row->Title;
		$event['SpecialEvent'] = $row->SpecialEvent;
		$event['GameType'] = $row->GameType;
		$events[(int)$row->EventId] = $event;
	}
}

/* function get_valid_tickets_for_event
 * 
 * Used to obtain an array of TicketItem objects that authorize the given event.
 *
 * $EventId - the Event ID to query for
 * $TicketItems - array of tickets for the event, or null if none exist.
 * Returns: nothing.
 */
function get_valid_tickets_for_event($EventId, &$TicketItems)
{
	$sql = sprintf("select TicketItems.* from TicketItems, EventTicketLinks ");
    $sql .= sprintf("where TicketItems.ItemId = EventTicketLinks.TicketItemId ");
    $sql .= sprintf("and EventTicketLinks.EventId = '%d'", $EventId);
	
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	
	$TicketItems = array();
	
	while ($row = mysql_fetch_object($result)) 
	{
		$item = new TicketItem();
		$item->convert_from_sql_row($row);
		if ($item->Active)
			array_push($TicketItems, $item);
	} 
}


/* function get_user
 * 
 * Used to obtain information about a user.
 *
 * $UserId - the ID of the user to retrieve. 
 * $User - an array containing the user's information. 
 * Returns: nothing.
 */
function get_user($UserId, &$User)
{
	$sql = sprintf("select * from Users where UserId = '%d'", $UserId);
		 
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("The user with UserID $UserId doesn't exist");
	
	$User = mysql_fetch_array($result);
}

/* function get_user_by_email
 * 
 * Used to obtain information about a user by email address.
 *
 * $EMail - email of the user to retrieve. 
 * $User - an array containing the user's information. 
 * Returns: nothing.
 */
function get_user_by_email($EMail, &$User)
{
	$sql = sprintf("select * from Users where EMail = '%s'", 
		mysql_real_escape_string(trim($EMail)));
		 
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
		
	if (0 == mysql_num_rows($result))
		$User = null;
	else
		$User = mysql_fetch_array($result);
}

/* function ticket_authorizes_event
 * 
 * Function returns true of the given TicketItemId authorizes the given EventId.
 *
 * $TicketItemId - unique ID number for the TicketItem
 * $EventId - unique ID number for the Event 
 * Returns: true if the TicketItem authorizes the Event.  
 */
function ticket_authorizes_event($TicketItemId, $EventId)
{
	$sql = sprintf("select * from EventTicketLinks where EventId = '%d' and TicketItemId = '%s'",
		$EventId, mysql_real_escape_string($TicketItemId));
		 
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);
	return (mysql_num_rows($result) > 0);
}

/* function set_ticket_event_auth
 * 
 * Function updates the EventTicketLink table to establish or remove the
 * authorization relationship between the event and the ticket item.
 *
 * $TicketItemId - unique ID number for the TicketItem
 * $EventId - unique ID number for the Event 
 * $authorize - true if establishing the relationship, false if deleting it.
 * Returns: nothing.
 */
function set_ticket_event_auth($TicketItemId, $EventId, $authorize)
{
	$link = new EventTicketLink();
	$link->EventId = $EventId;
	$link->TicketItemId = $TicketItemId;
		
	if ($authorize)
		$link->save_to_db();
	else 
		$link->remove_from_db();
}

/* function remove_all_event_ticket_auth
 * 
 * Function removes all the event ticket authorizations for the given
 * Ticket Item.
 *
 * $TicketItemId - unique ID number for the TicketItem
 * Returns: nothing.
 */
function remove_all_event_ticket_auth($TicketItemId)
{
	$sql = sprintf("delete from EventTicketLinks where TicketItemId = '%s'",
		mysql_real_escape_string($TicketItemId));

	$result = mysql_query($sql);
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
}

/* function get_transactions_for_user
 * 
 * Used to obtain a list of the transactions (tickets) purchased by a user.
 *
 * $UserId - the ID of the user in question.  
 * $Transactions - an array of Transaction objects in the DB.
 * Returns: nothing.
 */
function get_transactions_for_user($UserId, &$Transactions)
{
	$sql = "select * from Transactions, TicketItems ";
	$sql .= "where TicketItems.ItemId = Transactions.ItemId "; 
	$sql .= sprintf("and Transactions.UserId = '%d' ", $UserId);
    $sql .= "order by Transactions.ItemId";
	
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);

	$Transactions = array();
	
	while ($row = mysql_fetch_object($result)) 
	{
		$trans = new Transaction();
		$trans->convert_from_sql_row($row);
		$trans->Title = $row->Title;
		array_push($Transactions, $trans);
	} 
}

/* function get_transactions_for_ticket_item
 * 
 * Used to obtain a list of the transactions (tickets) purchased by 
 * ticket item.
 *
 * $TickeItem - the ID of the ticket item in question.  
 * $Transactions - an array of Transaction objects in the DB.
 * Returns: nothing.
 */
function get_transactions_for_ticket_item($TickeItem, &$Transactions)
{
	$sql = "select * from Transactions, Users ";
	$sql .= "where Users.UserId = Transactions.UserId "; 
	$sql .= sprintf("and Transactions.ItemId = '%s' ", mysql_real_escape_string($TickeItem));
    $sql .= "order by Transactions.UserId";
	
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error('Cannot execute query', $sql);

	$Transactions = array();
	
	while ($row = mysql_fetch_object($result)) 
	{
		$trans = new Transaction();
		$trans->convert_from_sql_row($row);	
		$trans->UserId = $row->UserId;
		array_push($Transactions, $trans);
	} 
}

/* function create_stand_in_user
 * 
 * Used to create a temporary stand-in user in the event a user purchased tickets 
 * but doesn't have an account locally (e.g. purchased over BPT).
 *
 * The user is automatically added to the local user's table and marked as unregistered.
 *
 * $fname - the first name of the user, or empty
 * $lname - the last name of the user, or empty
 * $email - the email of the user, or empty
 * $phone - the phone number of the user, or empty
 * $address - the address of the user, or empty
 * $city - the city of the user, or empty
 * $state - the state of the user, or empty
 * $zip - the zip of the user, or nothing
 * $country - the country of the user, or nothing.
 * Returns: nothing.
 */
function create_stand_in_user($fname, $lname, $email, $phone, $address, $city, $state, $zip, $country)
{
	// Check that the email address isn't already being used by another player.
  
	get_user_by_email($email, $user);
	if ($user != null)
		return display_error("Cannot create stand-in user, email is already in use:  $email");
	
	if ((0 == strlen(trim($fname))) && (0 == strlen(trim($lname))))
	{
		$lname = "*Unknown Name*";
		$disp_name = "*Unknown Name*";
	}
	else
	{
		$disp_name = $fname . " " . $lname;
	}
	
	// Create the stand in user.  Whoohoo! 

    $sql = 'insert into Users set ';
	$sql .= build_sql_string('FirstName', trim($fname), false);
	$sql .= build_sql_string('LastName', trim($lname));
	$sql .= build_sql_string('DisplayName',$disp_name);
	$sql .= build_sql_string('Nickname', "*Auto-Generated User*");
	$sql .= build_sql_string('EMail', trim($email));
	$sql .= build_sql_string('Address1', trim($address));
	$sql .= build_sql_string('City', trim($city));
	$sql .= build_sql_string('State', trim($state));
	$sql .= build_sql_string('Zipcode', trim($zip));
	$sql .= build_sql_string('Country', trim($country));
	$sql .= build_sql_string('DayPhone', trim($phone));
	$sql .= build_sql_string('EvePhone', trim($phone));	
	$sql .= build_sql_string('openid', "");
	$sql .= ', ModifiedBy=' . $_SESSION[SESSION_LOGIN_USER_ID];
	$sql .= ', Modified=NULL';

	$result = mysql_query ($sql);
	if (! $result)
		return display_mysql_error ('Insert into Users table failed');
}

/* function ticket_item_exists
 * 
 * Used to determine if a given ticket item exists in the local database.
 *
 * $ItemId:  the Ticket Item Id to verify
 * Returns:  true if it exists, false if not.
 */
function ticket_item_exists($ItemId)
{
	$sql = sprintf("select * from TicketItems where ItemId = '%s'", 
		mysql_real_escape_string($ItemId));
	 
	$result = mysql_query($sql);
	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	return (mysql_num_rows($result) > 0);
}
	
/* function transaction_reference_exists
 * 
 * Used to determine if a given transaction with the indicated reference exists in the local db.
 *
 * $reference:  the Reference number to verify
 * Returns:  true if it exists, false if not.
 */
function transaction_reference_exists($reference)
{
	$sql = sprintf("select * from Transactions where Reference = '%s'", $reference);
	 
	$result = mysql_query($sql);
	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	return (mysql_num_rows($result) > 0);
}	

/* function show_user_ticket_table
 * 
 * Used to show the table with all transactions for the given user.
 *  
 * $UserId - the ID of the user in question.
 * Returns: nothing.
 */
function show_user_ticket_table($UserId)
{
	get_transactions_for_user($UserId, $Transactions);
	if (sizeof($Transactions) == 0)
	{
		echo "This user has not purchased any tickets.<br><br>\n";
		return;
	}

	echo "<table border=\"1\">\n";
	echo "  <tr>\n";
	echo "    <th>Ticket Item</th>\n";
	echo "    <th>Ticket Description</th>\n";
	echo "    <th>Amount Paid</th>\n";
	echo "    <th>Date Stamp</th>\n";
	echo "    <th>Status</th>\n";
	echo "    <th>Tender Type</th>\n";
	echo "    <th>Payment Reference</th>\n";
	echo "    <th>Cashier's Memo</th>\n";
	echo "  </tr>\n";

	foreach ($Transactions as $trans)
	{
		echo "<tr valign=\"top\">\n";
		echo "  <td align=\"left\">$trans->ItemId</td>\n";
		echo "  <td align=\"left\">$trans->Title</td>\n";
		printf("  <td align=\"left\">%0.2f</td>\n", $trans->Amount);
		echo "  <td align=\"left\">$trans->Datestamp</td>\n";
		echo "  <td align=\"left\">$trans->Status</td>\n";
		echo "  <td align=\"left\">$trans->TenderType</td>\n";
		echo "  <td align=\"left\">$trans->Reference</td>\n";
		echo "  <td align=\"left\">$trans->Memo</td>\n";
		echo "</tr>\n";
	}
	echo "</table><br>\n";
}

/* function show_trans_item_ticket_table
 * 
 * Used to show the table with all transactions for the given ticket item
 *  
 * $TicketItem - the ticket item to use for the query
 * Returns: nothing.
 */
function show_trans_item_ticket_table($TicketItem)
{
	get_transactions_for_ticket_item($TicketItem, $Transactions);
	if (sizeof($Transactions) == 0)
	{
		echo "This Ticket Item has not been purchased.<br><br>\n";
		return;
	}
	
	echo "<table border=\"1\">\n";
	echo "  <tr>\n";
	echo "    <th>UserId</th>\n";
	echo "    <th>User Name</th>\n";
	echo "    <th>Stage Name</th>\n";
	echo "    <th>Nick Name</th>\n";
	echo "    <th>EMail</th>\n";
	echo "    <th>Amount Paid</th>\n";
	echo "    <th>Date Stamp</th>\n";
	echo "    <th>Status</th>\n";
	echo "    <th>Tender Type</th>\n";
	echo "    <th>Payment Reference</th>\n";
	echo "    <th>Cashier's Memo</th>\n";
	echo "  </tr>\n";

	foreach ($Transactions as $trans)
	{
		get_user($trans->UserId, $User);
		
		echo "<tr valign=\"top\">\n";
		printf("  <td align=\"left\">%s</td>\n", $User['UserId']);
		printf("  <td align=\"left\">%s &nbsp;</td>\n", $User['DisplayName']);
		printf("  <td align=\"left\">%s &nbsp;</td>\n", $User['StageName']);
		printf("  <td align=\"left\">%s &nbsp;</td>\n", $User['Nickname']);
		printf("  <td align=\"left\">%s</td>\n", $User['EMail']);
		printf("  <td align=\"left\">%0.2f</td>\n", $trans->Amount);
		echo "  <td align=\"left\">$trans->Datestamp</td>\n";
		echo "  <td align=\"left\">$trans->Status</td>\n";
		echo "  <td align=\"left\">$trans->TenderType</td>\n";
		echo "  <td align=\"left\">$trans->Reference</td>\n";
		echo "  <td align=\"left\">$trans->Memo</td>\n";
		echo "</tr>\n";
	}
	echo "</table><br>\n";
}

/* function show_user_simple_ticket_table
 * 
 * Used to show the table with all transactions for the given user (simple version).
 *  
 * $UserId - the ID of the user in question.
 * Returns: nothing.
 */
function show_user_simple_ticket_table($UserId)
{
	get_transactions_for_user($UserId, $Transactions);
	if (sizeof($Transactions) == 0)
	{
		echo "This user has not purchased any tickets.<br><br>\n";
		return;
	}

	echo "<table border=\"1\">\n";
	echo "  <tr>\n";
	echo "    <th>Ticket Description</th>\n";
	echo "    <th>Amount Paid</th>\n";
	echo "    <th>Date Stamp</th>\n";
	echo "    <th>Payment Reference</th>\n";
	echo "    <th>Cashier's Memo</th>\n";
	echo "  </tr>\n";

	foreach ($Transactions as $trans)
	{
		if (($trans->Status == 'Voided') || ($trans->Status == 'Error'))
			continue;
	
		echo "<tr valign=\"top\">\n";
		echo "  <td align=\"left\">$trans->Title</td>\n";
		printf("  <td align=\"left\">%0.2f</td>\n", $trans->Amount);
		echo "  <td align=\"left\">$trans->Datestamp</td>\n";
		echo "  <td align=\"left\">$trans->Reference</td>\n";
		echo "  <td align=\"left\">$trans->Memo</td>\n";
		echo "</tr>\n";
	}
	echo "</table><br>\n";
}

/* function show_ticket_status_table
 * 
 * Used to show the table with current status of ticket items
 *  
 * Returns: nothing.
 */
function show_ticket_status_table()
{
	// Get Posted Transactions
	
	$sql = "select TicketItems.ItemId, Title, Status, Count(TransIndex), Sum(Amount) ";
	$sql .= "from Transactions, TicketItems ";
	$sql .= "where Transactions.ItemId = TicketItems.ItemId ";
	$sql .= "group by Transactions.ItemId, Title, Status ";
	$sql .= "order by Transactions.ItemId, Title, Status";
	
	$result = mysql_query($sql);
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);

	// reorganize the query results for our table.

	$item_list = array();
	$posted = array();
	$settled = array();
	$voided = array();
	$error = array();
	
	while ($row = mysql_fetch_array($result))
	{
		$item_list[$row['ItemId']] = $row['Title'];
		if ($row['Status'] == 'Posted')
			$posted[$row['ItemId']] = sprintf("%s / $%0.2f", 
				$row['Count(TransIndex)'], $row['Sum(Amount)']);
		if ($row['Status'] == 'Settled')
			$settled[$row['ItemId']] = sprintf("%s / $%0.2f", 
				$row['Count(TransIndex)'], $row['Sum(Amount)']);
		if ($row['Status'] == 'Voided')
			$voided[$row['ItemId']] = sprintf("%s / $%0.2f", 
				$row['Count(TransIndex)'], $row['Sum(Amount)']);
		if ($row['Status'] == 'Error')
			$error[$row['ItemId']] = sprintf("%s / $%0.2f", 
				$row['Count(TransIndex)'], $row['Sum(Amount)']);
	}
	
	echo "<table border=\"1\">\n";
	echo "  <tr>\n";
	echo "    <th>Ticket Item</th>\n";
	echo "    <th>Ticket Description</th>\n";
	echo "    <th>Trans. Posted</th>\n";
	echo "    <th>Trans. Settled</th>\n";
	echo "    <th>Trans. Voided</th>\n";
	echo "    <th>Trans. Error</th>\n";
	echo "  </tr>\n";
	
	foreach ($item_list as $id => $title)
	{
		echo "<tr valign=\"top\">\n";
		
		printf( "  <td align=\"left\"><a href=\"TicketAdmin.php?action=%d&TicketItemId=%s\">%s</td>\n", 
			TICKETITEM_LIST_PMTS, $id, $id);
		
		echo "    <td align=\"left\">$title</td>\n";
		if (array_key_exists($id, $posted))
			printf("    <td align=\"left\">%s</td>\n", $posted["$id"]);
		else
			echo "<td align=\"left\">0 / $0.00</td>\n";
		if (array_key_exists($id, $settled))
			printf("    <td align=\"left\">%s</td>\n", $settled["$id"]);
		else
			echo "<td align=\"left\">0 / $0.00</td>\n";
		if (array_key_exists($id, $voided))
			printf("    <td align=\"left\">%s</td>\n", $voided["$id"]);
		else
			echo "<td align=\"left\">0 / $0.00</td>\n";
		if (array_key_exists($id, $error))
			printf("    <td align=\"left\">%s</td>\n", $error["$id"]);
		else
			echo "<td align=\"left\">0 / $0.00</td>\n";	
		echo "  </tr>\n";
	}
	echo "</table><br>\n";
}

/* function ticketed_user_count
 * 
 * Used to get the number of paid users, returned in a variable.
 *  
 * Returns: number of paid users in the system.
 */
function ticketed_user_count()
{
	$sql = "select distinct UserId from Transactions where Status in ('Posted', 'Settled')";
	 
	$result = mysql_query($sql);	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	return (mysql_num_rows($result));
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
	if (!ticket_item_exists(BPT_ACT_TICKETITEM_ID))
	{
		display_error("Cannot verify if user paid act submittal: ticket type doesn't exist");
		return false;
	}

	// Something is going wrong here, so we are going to bypass this check
	// until we figure out ticketing.  -MDB
	
	/*
	// Get Number of Acts Submitted
	
	$sql = sprintf("select * from Bids where UserId = '%d' and Status in " .
		"('Pending','Under Review','Accepted','Rejected');", $UserId);
	 
	$result = mysql_query($sql);	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	$acts_submitted = (mysql_num_rows($result));
	
	// Get number of act fees paid
	
	$sql = sprintf("select * from Transactions where UserId = '%d' and ItemId = '%s'",
		$UserId, BPT_ACT_TICKETITEM_ID);
	 
	$result = mysql_query($sql);	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	$fees_paid = (mysql_num_rows($result));

	//echo "fees paid:  $fees_paid  acts_submitted:  $acts_submitted";
	
	return ($fees_paid >= $acts_submitted);
	
	*/
	return true;
}

/* function email_indicates_autogen_user
 * 
 * Used to determine if a given email is associated with an auto-generated user
 *
 * $Email - the email to query against.
 * Returns:  true if the user is auto-generated, false otherwise.
 */
function email_indicates_autogen_user($EMail)
{
	// Something is going wrong here, so we are going to bypass this check
	// until we figure out ticketing.  -MDB
	
	/*
	$sql = "select * from Users, Transactions where Transactions.UserId = Users.UserId";
	$sql .= sprintf(" and Users.openid = '' and Users.Email = '%s'", mysql_real_escape_string($EMail));

	$result = mysql_query($sql);	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	return (mysql_num_rows($result) > 0);
	*/
	
	return false;
}

/* function verify_autogen_user_code
 * 
 * Used to determine if the given email address and BPT transaction code match - 
 * indicating the given email address is OK to use for this user.  
 *
 * $Email - the email to query against.
 * $trans_code - the transaction code to match.
 * Returns:  The valid UserId for the email/code combo, otherwise false
 */
function verify_autogen_user_code($EMail, $trans_code)
{
	// Something is going wrong here, so we are going to bypass this check
	// until we figure out ticketing.  -MDB
	
	/*
	if (strlen($trans_code) == 0)
		return false;
		
	// If the trans code doesn't start with an "A", add it.  This is BPT specific.
	
	if (substr($trans_code, 0, 1) != "A")
		$trans_code = "A" . $trans_code;
	
	$sql = "select * from Users, Transactions where Transactions.UserId = Users.UserId";
	$sql .= sprintf(" and Users.openid = '' and Users.Email = '%s'", 
		mysql_real_escape_string($EMail));
	$sql .= sprintf(" and Transactions.Reference = '%s'", mysql_real_escape_string($trans_code));

	$result = mysql_query($sql);	
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	
	if (mysql_num_rows($result) <= 0)
		return false;
	
	return mysql_fetch_object($result)->UserId;
	*/
	return false;
}

?>


