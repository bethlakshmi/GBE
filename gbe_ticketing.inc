<?php

/* gbe_ticketing.inc - contains definitions used with ticketing and cashiering.
 * 
 * Last Updated 7/28/2013 by MDB
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
	// These public properties represent the various indexes in the table.
	
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
		$sql = sprintf("select * from Ticketitems where ItemId = '%d'", $ItemId);
		 
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
	 * Returns: the auto-increment ID (not used).
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
		
		$sql = "select * from Ticketitems where ItemId = $this->ItemId";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple ticket items found with same ID");	
		else if (mysql_num_rows($result) == 1)
			$sql = "update Ticketitems set ";
		else
			$sql = "insert into Ticketitems set ";
			
		$sql .= build_sql_string('ItemId', $this->ItemId, false);
		$sql .= build_sql_string('Title', $this->Title);
		$sql .= build_sql_string('Description', $this->Description);
		$sql .= build_sql_string('Active', $this->Active);
		$sql .= build_sql_string('Cost', $this->Cost);
		$sql .= build_sql_string('Datestamp', $this->Datestamp);
		$sql .= build_sql_string('Userstamp', $this->Userstamp);
			
		$result = mysql_query ($sql);
		if (!$result)
			return display_mysql_error ("Insert into Ticketitems failed");

		return mysql_insert_id();
	}	
}				

?>


