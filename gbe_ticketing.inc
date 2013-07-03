<?php

/* gbe_ticketing.inc - contains definitions used with ticketing and cashiering.
 * 
 * Last Updated 7/1/2013 by MDB
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
	 * Used to load a paticular ticket item by item id from the database. 
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
		
		$this->ItemId = $row->ItemId;
		$this->Title = $row->Title;
		$this->Description = $row->Description;
		$this->Active = $row->Active;	
		$this->Cost = $row->Cost;
		$this->Datestamp = $row->Datestamp;	
		$this->Userstamp = $row->Userstamp;
	}
	
	/* function save_to_db
	 * 
	 * Used to save the current status of the ticket item to the database.	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		// Sanitize database input.  Note, the build_sql_string uses mysql_real_escape_string() below.  
		
		if (!is_integer($this->ItemId))
			return display_error("Ticket Item ID is not in a valid format.");
		if (!is_bool($this->Active))
			return display_error("Ticket Item active state is not in a valid format");	
		if (!is_double($this->Cost))
			return display_error("Ticket Item cost is not in a valid format");	
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
			
		echo $sql;
		
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
 
class Transasction
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
		
		$this->TransIndex = $row->TransIndex;
		$this->ItemID = $row->ItemID;
		$this->UserID = $row->UserID;
		$this->Amount = $row->Amount;	
		$this->Datestamp = $row->Datestamp;
		$this->AdjustmentAmt = $row->AdjustmentAmt;
		$this->Status = $row->Status;
		$this->TenderType = $row->TenderType;
		$this->Reference = $row->Reference;
		$this->Cashier = $row->Cashier;
		$this->Memo = $row->Memo;
        $this->Override = $row->Override;
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

?>

