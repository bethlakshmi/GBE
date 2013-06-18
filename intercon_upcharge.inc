<?php

include("intercon_db.inc");

class UpchargeReportRow {
    function UpchargeReportRow($data) {
        $this->values = $this->formatData($data);
        $this->data = $data;
    }
    
    function formatData($data) {
        return array(
            "Name" => "$data->LastName, $data->FirstName",
            "Status" => $data->Status,
            "Paid" => sprintf("$%.02f", $data->PaymentAmount / 100),
            "Notes" => $data->PaymentNote
        );
    }
    
    function getColumnNames() {
        return array("Name", "Status", "Paid", "Notes");
    }
    
    function get($col) {
        return $this->values[$col];
    }
}

class UpchargeReportTable {
    function UpchargeReportTable($rows) {
        $this->data = $this->getTableData($rows);
    }
    
    function getColumnNames() {
        if (count($this->data) == 0) {
            return array();
        }
        
        return $this->data[0]->getColumnNames();
    }
    
    function getReportRow($row) {
        return new UpchargeReportRow($row);
    }
    
    function getTableData($rows) {
        $table = array();
        foreach ($rows as $row) {
            array_push($table, $this->getReportRow($row));
        }
        return $table;
    }
}

class UpchargeReportView {
    function UpchargeReportView($manager, $table) {
        $this->manager = $manager;
        $this->table = $table;
    }
    
    function getStatusColor($status) {
        switch ($status)
        {
          case 'Paid':      return get_bgcolor_hex('Confirmed');  // Green
          case 'Unpaid':    return get_bgcolor_hex('Waitlisted'); // Yellow
          case 'Cancelled': return get_bgcolor_hex('Away');       // Gray
          default:          return get_bgcolor_hex('Full');       // Red
        }
    }
    
    function displayCell($row, $col) {
        echo "<td>".$row->get($col)."</td>";
    }
    
    function display() {
        if (count($this->table->data) == 0) {
            echo "<p>No payments yet for ".$this->manager->getCollectionName().".</p>\n";
            return;
        }
        
        $cols = $this->table->getColumnNames();
    
        echo "<table border=1>\n";
        echo "<tr>";
        foreach ($cols as $col) {
            echo "<th>".$col."</th>";
        }
        echo "</tr>\n";
        foreach ($this->table->data as $row) {
            echo "<tr bgcolor=\"".$this->getStatusColor($row->get("Status"))."\">";
            foreach ($cols as $col) {
                $this->displayCell($row, $col);
            }
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
}

class UpchargeIndexView extends UpchargeReportView {
    function displayCell($row, $col) {
        echo "<td>";
        if ($col == "Name") {
            if ($this->manager->userCanEdit()) {
                echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=".$this->manager->getEditUpchargeAction()."&id=";
                $pkey = $this->manager->getPrimaryKeyColumn();
                echo $row->data->$pkey."\">".$row->get($col)."</a>";
            } else {
                echo $row->get($col);
            }
        } else {
            echo $row->get($col);
        }
        echo "</td>";
    }
}

class UpchargePaypalLink {
    function UpchargePaypalLink($itemName, $cost) {
        $this->itemName = $itemName;
        $this->cost = $cost;
    }
    
    function getUrl() {
        // Build the URL for the PayPal links.  If the user cancels, just return
        // to index.php which will default to his homepage

        $path_parts = pathinfo($_SERVER['PHP_SELF']);
        $dirname = '';
        if ("/" != $path_parts['dirname'])
          $dirname = $path_parts['dirname'];

        $return_url = sprintf ('http://%s%s/index.php',
    			   $_SERVER['SERVER_NAME'],
    			   $dirname);
        //  echo "dirname: $dirname<br>\n";
        //  echo "return_url: $return_url<br>\n";
        $cancel_url = $return_url;

        $url = 'https://www.paypal.com/cgi-bin/webscr?';
        $url .= build_url_string ('cmd', '_xclick');
        $url .= build_url_string ('business', PAYPAL_ACCOUNT_EMAIL);
        $url .= build_url_string ('item_name', $this->itemName);
        $url .= build_url_string ('no_note', '0');
        $url .= build_url_string ('cn', 'Any notes about your payment?');
        $url .= build_url_string ('no_shipping', '1');
        $url .= build_url_string ('custom', $_SESSION[SESSION_LOGIN_USER_ID]);
        $url .= build_url_string ('currency_code', 'USD');
        $url .= build_url_string ('amount', $this->cost);
        $url .= build_url_string ('rm', '2');
        $url .= build_url_string ('cancel_return', $cancel_url);
        $url .= build_url_string ('return', $return_url, FALSE);
        
        return $url;
    }
    
    function displayButton() {
        echo "<a href=\"".$this->getUrl()."\"><img\n";
        echo "src=\"http://images.paypal.com/images/x-click-but3.gif\"\n";
        echo "border=\"0\"\n";
        printf ('alt=\"Click to pay for the %s.\"></a>', $this->itemName);
    }
}

// Subclass this for all upcharge item types (t-shirt, dead dog, thursday thing, etc)
abstract class UpchargeItemManager {
    abstract function getTableName();
    abstract function getPrimaryKeyColumn();
    abstract function getReportAction();
    abstract function getNewUpchargeAction();
    abstract function getSelectUpchargeAction();
    abstract function getEditUpchargeAction();
    abstract function getProcessUpchargeAction();
    abstract function getCreateUpchargeAction();
    abstract function userCanView();
    abstract function userCanEdit();
    abstract function getCollectionName();
    
    function getItemName($row) {
        return $this->getCollectionName() . " record for ".$row->FirstName." ".$row->LastName;
    }

    function getColumnNames($tableAlias = null) {
        $cols = explode(' ', 'Status PaymentAmount PaymentNote UserId UpdatedById LastUpdated');
        if (!array_search($this->getPrimaryKeyColumn(), $cols)) {
            array_push($cols, $this->getPrimaryKeyColumn());
        }
        
        if (is_null($tableAlias)) {
            return $cols;
        } else {
            $aliasCols = array();
            foreach ($cols as $col) {
                array_push($aliasCols, $tableAlias . "." . $col);
            }
            return $aliasCols;
        }
    }

    // gets a single upcharge row from the database by its primary key
    // (primary key column can vary from implementation to implementation)
    function fetchRow($id) {
        $sql =  "SELECT u.FirstName, u.LastName, upd.FirstName UpdaterFirstName, upd.LastName UpdaterLastName, ";
        $sql .= implode(", ", $this->getColumnNames("i"));
        $sql .= " FROM ".$this->getTableName().' i';
        $sql .= " LEFT OUTER JOIN Users upd ON i.UpdatedById = upd.UserId";
        $sql .= " LEFT OUTER JOIN Users u ON i.UserId = u.UserId";
        $sql .= " WHERE i.".$this->getPrimaryKeyColumn()." = '".mysql_real_escape_string($id)."' ";
        
        $result = mysql_query($sql);
        if (! $result)
          return display_mysql_error ('Failed to get upcharge row',
      				$sql);
      	
      	return mysql_fetch_object($result);
    }
    
    // query the table for a list of upcharge records, joining with Users
    // to retrieve name
    // 
    // optional parameters (passed as keys to the $options argument):
    //   "where" - specify additional conditions for the where clause
    //   "order" - specify an order clause
    function fetchRows($options = array()) {
        $sql =  'SELECT u.FirstName, u.LastName, upd.FirstName UpdaterFirstName, upd.LastName UpdaterLastName, ';
        $sql .= implode(", ", $this->getColumnNames("i"));
        $sql .= ' FROM '.$this->getTableName().' i';
        $sql .= " LEFT OUTER JOIN Users upd ON i.UpdatedById = upd.UserId";
        $sql .= " LEFT OUTER JOIN Users u ON i.UserId = u.UserId";
        if (array_key_exists("where", $options)) {
            $sql .= ' WHERE ('.$options['where'].')';
        }
        if (array_key_exists("order", $options)) {
            $sql .= ' ORDER BY ' . $options["order"];
        }
        
        $rows = array();
        $result = mysql_query ($sql);
        if (! $result)
          return display_mysql_error ('Failed to get list of upcharges',
      				$sql);

        while ($row = mysql_fetch_object($result)) {
            array_push($rows, $row);
        }
        
        return $rows;
    }
    
    function fetchRowsForUser($userId, $options = array()) {
        if (array_key_exists("where", $options)) {
            $options["where"] = " AND (".$options["where"].")";
        } else {
            $options["where"] = "";
        }
        $options["where"] = "i.UserId = $userId " . $options["where"];
        return $this->fetchRows($options);
    }
    
    function fetchRowsForLoggedInUser($options = array()) {
        if (is_logged_in()) {
            return $this->fetchRowsForUser($_SESSION[SESSION_LOGIN_USER_ID], $options);
        } else {
            return array();
        }
    }
    
    function getReportTable($rows) {
        return new UpchargeReportTable($rows);
    }
    
    function getReportView($table) {
        return new UpchargeReportView($this, $table);
    }
    
    function getIndexView($table) {
        return new UpchargeIndexView($this, $table);
    }
        
    function displayReport() {
        $rows = $this->fetchRows(array("order" => "i.Status, LastName, FirstName"));

        $statusCount = array("Paid" => 0, "Unpaid" => 0, "Cancelled" => 0);
        foreach ($rows as $row) {
            if (!array_key_exists($row->Status, $statusCount)) {
                $statusCount[$row->Status] = 0;
            }
            $statusCount[$row->Status] += 1;
        }
        $headerCounts = array();
        $statuses = array_keys($statusCount);
        sort($statuses);
        foreach ($statuses as $status) {
            array_push($headerCounts, $statusCount[$status] . " " . $status);
        }
        display_header ($this->getCollectionName() . " Report: " . implode(", ", $headerCounts));

        $this->getReportView($this->getReportTable($rows))->display();
    }
    
    function displayUpchargeIndex() {
        $rows = $this->fetchRows(array("order" => "i.Status, LastName, FirstName"));

        display_header($this->getCollectionName() . " Payment Records");

        $this->getIndexView($this->getReportTable($rows))->display();
        
        echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=".$this->getNewUpchargeAction()."\">";
        echo "Create new record";
        echo "</a>\n";
    }
    
    function status_radio($value)
    {
      $checked = '';
      if (array_key_exists("Status", $_POST) && $value == $_POST['Status'])
        $checked = ' CHECKED';

      printf ("    <input type=\"radio\" name=\"Status\" id=\"Status_%s\" value=\"%s\"%s><label for=\"Status_%s\">%s</label>\n",
    	  $value, $value, $checked, $value, $value);
    }
    
    function displayFormFields($row=null) {
    	$pkeyColumn = $this->getPrimaryKeyColumn();
    	if (is_null($row)) {
    	    printf ("<input type=\"hidden\" name=\"UserId\" value=%d>\n", $_REQUEST["UserId"]);
	    } else {
    	    printf ("<input type=\"hidden\" name=\"%s\" value=%d>\n", "id", $row->$pkeyColumn);
            printf ("<input type=\"hidden\" name=\"UserId\" value=%d>\n", $row->UserId);
        } 
        echo "<table border=\"0\">\n";

        echo "  <tr>\n";
        echo "    <td align=\"right\" valign=\"top\">Status:</td>\n";
        echo "    <td>\n";
        $this->status_radio ('Unpaid');
        $this->status_radio ('Paid');
        $this->status_radio ('Cancelled');
        echo "    </td>\n";
        echo "  </tr>\n";

        form_text (5, 'Payment Amount $', 'PaymentAmount');
        form_text (64, 'Payment Note', 'PaymentNote');
    }
    
    function displayUserSelector($caption, $action) {
        $upcharges = array();
        foreach ($this->fetchRows() as $row) {
            $upcharges[$row->UserId] = $row;
        }

        // Get a list of first characters

        $sql = 'SELECT DISTINCT UCASE(SUBSTRING(LastName,1,1)) AS Ch';
        $sql .= '  FROM Users';
        $sql .= "  WHERE LastName<>'Admin'";
        $sql .= ' AND CanSignup<>"Alumni"';
        $sql .= '  ORDER BY Ch';
        $result = mysql_query ($sql);
        if (! $result)
          return display_mysql_error ('Failed to get list of characters');

        // Initialize the list of anchors to the alphabet, and then FALSE,
        // indicating that we haven't seen the character yet.  Then pull the
        // list of leading characters from the database and set them to TRUE,
        // indicating that we've got an anchor for that character

        $anchors = array ();
        for ($i = ord('A'); $i <= ord('Z'); $i++)
          $anchors[chr($i)] = FALSE;

        while ($row = mysql_fetch_object ($result))
          $anchors[$row->Ch] = TRUE;

        // Get a list of all users

        $sql = 'SELECT UserId, FirstName, LastName';
        $sql .= '  FROM Users';
        $sql .= ' WHERE CanSignup<>"Unpaid"';
        $sql .= '   AND CanSignup<>"Alumni"';
        $sql .= '  ORDER BY LastName, FirstName';
        $result = mysql_query ($sql);
        if (! $result)
          return display_mysql_error ('Failed to get list of users', $sql);

        display_header ($caption);

        // Display the list of anchors

        echo "<table width=\"100%\">\n";
        echo "  <tr>\n";

        foreach ($anchors as $key => $value)
        {
          if ($value)
            echo "    <td><a href=\"#$key\">$key</a></td>\n";
          else
            echo "    <td>$key</td>\n";
        }

        echo "  </tr>\n";
        echo "</table>\n";

        $ch = '';

        echo "<table border=\"0\" cellpadding=\"2\">\n";

        while ($row = mysql_fetch_object ($result))
        {
          // Skip the Admin account

          if ('Admin' == $row->LastName)
            continue;

          // Add spacer between names starting with different letters

          if ($ch != strtoupper($row->LastName{0}))
          {
            $ch = strtoupper ($row->LastName{0});
            echo "  <tr bgcolor=\"#CCCCFF\">\n";
            echo "    <td colspan=\"4\"><A name=\"$ch\">$ch</a></td>\n";
            echo "  </tr>\n";
          }

          // Display the user name for selection

          echo " <tr>\n";

          printf ("    <td><a href=\"%s&UserId=%d\">%s, %s</a></td>\n",
      	    $_SERVER["PHP_SELF"]."?action=".$action,
      	    $row->UserId,
      	    $row->LastName,
      	    $row->FirstName);

          if (array_key_exists ($row->UserId, $upcharges))
          {
              $upcharge = $upcharges[$row->UserId];
              if ('Paid' == $upcharge->Status)
      	          printf ("    <td>&nbsp;%s $%d&nbsp;</td>\n", $upcharge->Status, $upcharge->PaymentAmount / 100);
              else
      	          printf ("    <td>&nbsp;%s&nbsp;&nbsp;</td>\n", $upcharge->Status);
                  printf ("    <td>%s</td>\n", $upcharge->PaymentNote);
          }

          echo "  </tr>\n";
        }

        echo "</table>\n";
    }
    
    function displayNewUpchargeForm() {
        if (array_key_exists("UserId", $_REQUEST)) {
            $userId = intval(trim($_REQUEST["UserId"]));
            
            $sql = "SELECT CONCAT(FirstName, ' ', LastName) FROM Users WHERE UserId = ".$userId;
            $result = mysql_query($sql);
            if (! $result)
              return display_mysql_error ('Failed to get user name',
          				$sql);
            $row = mysql_fetch_array($result);
            $name = $row[0];
            
            $seq = increment_sequence_number();
            display_header ("Creating new ".$this->getCollectionName(). " record for ".$name);

            echo "<p><form method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">\n";
            form_add_sequence ($seq);
            printf ("<input type=\"hidden\" name=\"action\" value=%d>\n",
        	  $this->getCreateUpchargeAction());
            $this->displayFormFields();

            form_submit ('Create');
            
            echo "</table>\n";
            echo "</form>\n";
        } else {
            $this->displayUserSelector("Choose a user to create a new ".$this->getCollectionName()." for", 
                $this->getNewUpchargeAction());
        }
    }
    
    function displayUpchargeEditor() {
        $id = intval(trim($_REQUEST["id"]));
        if (0 == $id)
            return display_error ('Invalid id');
        
        $row = $this->fetchRow($id);
        if (is_null($row))
            return display_error ("Couldn't find any rows with ID $id");
        
        // Fetch the selected user's name
        $name = trim ("$row->FirstName $row->LastName");

        foreach ($this->getColumnNames() as $col) {
            if (!array_key_exists($col, $_POST)) {
                $_POST[$col] = $row->$col;
            }
        }
        
        $_POST['PaymentAmount'] /= 100;
        
        # dump_array ('$_POST', $_POST);

        if (0 == $_POST['UpdatedById'])
          $updater = 0;
        else
          $updater = "$row->UpdaterLastName, $row->UpdaterFirstName";

        $seq = increment_sequence_number();
        display_header ("Editing ".$this->getItemName($row));

        echo "<p><form method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">\n";
        form_add_sequence ($seq);
        printf ("<input type=\"hidden\" name=\"action\" value=%d>\n",
    	  $this->getProcessUpchargeAction());
        $this->displayFormFields($row);

        form_submit ('Update');

        echo "<!-- updater: $updater -->\n";

        if (is_string ($updater))
        {
            echo "  <tr>\n";
            echo "    <td colspan=\"2\">\n";
            printf ($this->getItemName($row)." last updated %s by %s\n",
                $_POST['LastUpdated'],
                $updater);
            echo "    </td>\n";
            echo "  </tr>\n";
        }
        echo "</table>\n";
        echo "</form>\n";

        printf ("<p><a href=\"%s?action=%d\">Select a different record to edit</a></p>\n",
          $_SERVER['PHP_SELF'], $this->getSelectUpchargeAction());
    }
    
    function getSqlSaveFieldsFromPost() {
        $PaymentAmount = intval(floatval ($_POST['PaymentAmount']) * 100);

        return array(
            "Status" => $_POST['Status'],
            "PaymentAmount" => $PaymentAmount,
            "PaymentNote" => $_POST['PaymentNote'],
            "UpdatedById" => $_SESSION[SESSION_LOGIN_USER_ID],
            "UserId" => $_POST['UserId']
        );
    }
    
    function saveRecordFromPost($row=null) {
        $newRecord = is_null($row);
        if ($newRecord) {
            $sql = 'INSERT INTO '.$this->getTableName().' SET ';
        } else {
            $sql = 'UPDATE '.$this->getTableName().' SET ';
        }
        
        $fields = $this->getSqlSaveFieldsFromPost();
        $setClauses = array();
        foreach (array_keys($fields) as $fieldName) {
            array_push($setClauses, build_sql_string($fieldName, $fields[$fieldName], false));
        }
        $sql .= implode(', ', $setClauses);
        if (!$newRecord) {
            $pkey = $this->getPrimaryKeyColumn();
            $sql .= ' WHERE '.$pkey." = ".$row->$pkey;
        }

        $result = mysql_query ($sql);
        if (! $result)
          return display_mysql_error ('Record update failed');
        else
          return true;
    }
    
    function processUpchargeEdit() {
        // Fetch the UserId

        $id = intval (trim ($_REQUEST['id']));
        if (0 == $id)
          return display_error ('Invalid id');

        // Check for sequence errors

        if (out_of_sequence ())
          return display_sequence_error (false);

        $row = $this->fetchRow($id);
        return $this->saveRecordFromPost($row);
        
    }
    
    function processUpchargeCreate() {
        if (out_of_sequence ())
          return display_sequence_error (false);

        return $this->saveRecordFromPost();
    }
    
    function userHasPermission($type) {
        switch ($type) {
            case "view":
                return $this->userCanView();
            case "edit":
                return $this->userCanEdit();
            default:
                return false;
        }
    }
    
    function enforcePermission($type) {
        if (!$this->userHasPermission($type)) {
            display_error("Sorry, you don't have permissions to view this page.");
            return false;
        } else {
            return true;
        }
    }
    
    function processAction($action) {
        switch ($action) {
            case $this->getReportAction():
                if ($this->enforcePermission("view")) {
                    $this->displayReport();
                }
                return true;
            
            case $this->getSelectUpchargeAction():
                if ($this->enforcePermission("edit")) {
                    $this->displayUpchargeIndex();
                }
                return true;
                
            case $this->getNewUpchargeAction():
                if ($this->enforcePermission("edit")) {
                    $this->displayNewUpchargeForm();
                }
                return true;
                
            case $this->getCreateUpchargeAction():
                if ($this->enforcePermission("edit")) {
                    if ($this->processUpchargeCreate()) {
                        $this->displayUpchargeIndex();
                    }
                }
                return true;
            
            case $this->getEditUpchargeAction():
                if ($this->enforcePermission("edit")) {
                    $this->displayUpchargeEditor();
                }
                return true;
            
            case $this->getProcessUpchargeAction():
                if ($this->enforcePermission("edit")) {
                    if ($this->processUpchargeEdit()) {
                        $this->displayUpchargeIndex();
                    }
                }
                return true;
        }
        
        return false;
    }
    
}
?>