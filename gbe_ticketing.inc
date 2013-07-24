<?php

/* gbe_ticketing.inc - contains definitions used with ticketing and cashiering.
 * 
 * Last Updated 7/8/2013 by MDB
 *
 */
 
include("intercon_db.inc");

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
	 * $ItemID:  the Item ID to be loaded.
	 * Returns: nothing.
	 */
	function load_from_itemid($ItemId)
	{
		$sql = sprintf("select * from TicketItems where ItemId = '%d'", $ItemId);
		 
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
		$this->ItemId = (int)$row->ItemId;
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
		$this->ItemId = (int)$array['ItemId'];
		$this->Title = (string)$array['Title'];
		$this->Description = (string)$array['Description'];
		if (array_key_exists('Active', $array))
			$this->Active = (bool)$array['Active'];	
		else
			$this->Active = false;
		$this->Cost = (float)$array['Cost'];
		
		return true;
	}
	
	/* we need a convert-from-array here for _POST stuff */

	/* function save_to_db
	 * 
	 * Used to save the current status of the ticket item to the database.	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		// Sanitize database input.  Note, the build_sql_string uses mysql_real_escape_string() below.  
		
		if (!is_integer($this->ItemId) || ($this->ItemId <= 0))
			return display_error("Ticket Item ID is not in a valid format.");
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
		
		$sql = "select * from TicketItems where ItemId = $this->ItemId";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple ticket items found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update Ticketitems set ";
			$sql .= build_sql_string('Title', $this->Title, false);
		}	
		else
		{
			$sql = "insert into Ticketitems set ";
			$sql .= build_sql_string('ItemId', $this->ItemId, false);
			$sql .= build_sql_string('Title', $this->Title);
		}
		
		$sql .= build_sql_string('Description', $this->Description);
		$sql .= build_sql_string('Active', $this->Active);
		$sql .= build_sql_string('Cost', $this->Cost);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('Userstamp', $this->Userstamp);
			
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where ItemId = '%d'", $this->ItemId);
		
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
		if (!is_integer($this->ItemId))
			return display_error("Ticket Item index is not in a valid format.");

		$sql = sprintf("delete from TicketItems where ItemId = '%d'", $this->ItemId);
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
	public $ItemID;
	public $UserID;
	public $Amount;
	public $Datestamp;
	public $AdjustmentAmt;
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
		$this->ItemID = (int)$row->ItemID;
		$this->UserID = (int)$row->UserID;
		$this->Amount = (float)$row->Amount;
		$this->Datestamp = (string)$row->Datestamp;
		$this->AdjustmentAmt = (float)$row->AdjustmentAmt;
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
		// Sanitize database input.  Note, the build_sql_string uses mysql_real_escape_string() below.  
		
		if (!is_integer($this->ItemID))
			return display_error("Transaction Item ID is not in a valid format.");
		if (!is_integer($this->UserID))
			return display_error("Transaction User ID is not in a valid format.");	
		if (!is_double($this->Amount))
			return display_error("Transaction Amount is not in a valid format");
		if (!is_double($this->AdjustmentAmt))
			return display_error("Transaction Adj. Amount is not in a valid format");			
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
		
		$sql .= build_sql_string('ItemID', $this->ItemID, false);
		$sql .= build_sql_string('UserID', $this->UserID);
		$sql .= build_sql_string('Amount', $this->Amount);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('AdjustmentAmt', $this->AdjustmentAmt);
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
	public $EventID;
	public $TicketItemID;
	public $Description;
	public $Active;
	public $Datetamp;
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
		$sql = sprintf("select * from EventTicketLink where ETIndex = '%d'", $ETIndex);
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple event ticket links found with same index");	
		if (1 != mysql_num_rows($result))
			return display_error("Event ticket link not found:  $ETIndex");
		
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
		$this->EventID = (int)$row->EventID;
		$this->TicketItemID = (int)$row->TicketItemID;
		$this->Description = (string)$row->Description;	
		$this->Active = (bool)$row->Active;
		$this->Datestamp = (string)$row->Datestamp;
		$this->Userstamp = (int)$row->Userstamp;
	}
	
	/* function save_to_db
	 * 
	 * Used to save the current status of the event ticket link to the database.	 
	 *
	 * $Update: if true, attempt to update a transaction, not insert.
	 * Returns: nothing.
	 */
	function save_to_db($Update)
	{	
		// Sanitize database input.  Note, the build_sql_string uses mysql_real_escape_string() below.  
		
		if (!is_integer($this->EventID))
			return display_error("Transaction Event ID is not in a valid format.");
		if (!is_integer($this->TicketItemID))
			return display_error("Transaction Item ID is not in a valid format.");				
		if (!is_bool($this->Active))
			return display_error("Transaction active state is not in a valid format");	
		if (!array_key_exists(SESSION_LOGIN_USER_ID, $_SESSION))
			return display_error("Cannot save TicketItem to DB:  not logged in.");	
			
		$this->Datestamp = date("Y-m-d H:i:s", time());	
		$this->Userstamp = $_SESSION[SESSION_LOGIN_USER_ID];
		
		// Update the database.
		
		if ($Update)
		{
			if (!is_integer($this->ETIndex))
				return display_error("Event ticket link index is not in a valid format.");
			$sql = "update EventTicketLink set ";
		}
		else
		{
			$sql = "insert into EventTicketLink set ";
		}	
		
		$sql .= build_sql_string('EventID', $this->EventID, false);
		$sql .= build_sql_string('TicketItemID', $this->TicketItemID);
		$sql .= build_sql_string('Description', $this->Description);
		$sql .= build_sql_string('Active', $this->Active);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('Userstamp', $this->Userstamp);

		if ($Update)
			$sql .= sprintf(" where ETIndex = '%d'", $this->ETIndex);
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert into EventTicketLink failed");
		
		if (!$Update)
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
		if (!is_integer($this->ETIndex))
			return display_error("Event ticket index is not in a valid format.");

		$sql = sprintf("delete from EventTicketLink where ETIndex = '%d'", $this->ETIndex);
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
	$sql = sprintf("select * from TicketItems");
		 
	$result = mysql_query($sql);
		
	if (!$result)
		return display_mysql_error ('Cannot execute query', $sql);
	if (0 == mysql_num_rows($result))
		return display_error("There are not TicketItems to list at this time.  Please add.");
	
	$TicketItems = array();
	
	while ($row = mysql_fetch_object($result)) 
	{
		$item = new TicketItem();
		$item->convert_from_sql_row($row);
		array_push($TicketItems, $item);
	} 
}


?>


