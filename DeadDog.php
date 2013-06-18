<?php

include("intercon_upcharge.inc");

class DeadDogReportRow extends UpchargeReportRow {
    function formatData($data) {
        $values = parent::formatData($data);
        $values["Tickets"] = $data->Quantity;
        return $values;
    }
    
    function getColumnNames() {
        $cols = parent::getColumnNames();
        array_splice($cols, 1, 0, array("Tickets"));
        return $cols;
    }
}

class DeadDogReportTable extends UpchargeReportTable {
    function getReportRow($row) {
        return new DeadDogReportRow($row);
    }
}

class DeadDogPaypalLink extends UpchargePaypalLink {
    function DeadDogPaypalLink($quantity=1) {
        $cost = 21;
        if (DEVELOPMENT_VERSION) {
            $cost = 0.05;
        }
        
        parent::__construct(PAYPAL_ITEM_DEAD_DOG, $cost);
        $this->quantity = $quantity;
    }
    
    function displayQuantitySelector($max) {
        if ($max > 6) {
            $max = 6;
        }
        
        echo "<select name=\"quantity\">\n";
        echo "<option value=\"\"></option>\n";
        
        for ($i = 1; $i <= $max; $i++) {
            echo sprintf('<option value="%d">%d tickets - $%0.02f</option>', $i, $i, $i * $this->cost);
        }
        echo "</select>\n";
    }
    
    function getUrl() {
        $url = parent::getUrl();
        $url .= "&" . build_url_string("quantity", $this->quantity, FALSE);
        return $url;
    }
}

class DeadDogManager extends UpchargeItemManager {
    function getColumnNames($tableAlias = null) {
        $cols = parent::getColumnNames($tableAlias);
        array_push($cols, "Quantity");
        array_push($cols, "TxnId");
        return $cols;
    }
    
    function getCollectionName() {
        return "Dead Dog";
    }
    
    function getTableName() {
        return "DeadDog";
    }
    
    function getPrimaryKeyColumn() {
        return "PaymentId";
    }
    
    function getReportAction() {
        return DEAD_DOG_REPORT;
    }
    
    function getSelectUpchargeAction() {
        return DEAD_DOG_SELECT_USER;
    }
    
    function getEditUpchargeAction() {
        return DEAD_DOG_EDIT_USER;
    }
    
    function getNewUpchargeAction() {
        return DEAD_DOG_NEW_USER;
    }
    
    function getProcessUpchargeAction() {
        return DEAD_DOG_PROCESS_USER;
    }
    
    function getCreateUpchargeAction() {
        return DEAD_DOG_CREATE_USER;
    }
    
    function getReportTable($rows) {
        return new DeadDogReportTable($rows);
    }
    
    public function userCanView() {
        return user_has_priv(PRIV_CON_COM);
    }
    
    public function userCanEdit() {
        return user_has_priv(PRIV_REGISTRAR);
    }
    
    public function availableSlots() {
        $sql = "SELECT SUM(Quantity) FROM ".$this->getTableName()." WHERE STATUS = 'Paid'";
        $result = mysql_query($sql);
        if (!$result) {
            return display_mysql_error('Failed to get paid user count', $sql);
        }
        $row = mysql_fetch_array($result);
        return (DEAD_DOG_MAX - $row[0]);
    }
    
    public function ticketsFrozen() {
        return con_signups_frozen();
    }
    
    function displayFormFields($row=null) {
        parent::displayFormFields($row);
        form_text(2, 'Number of Tickets', 'Quantity');
        form_text(80, 'PayPal Transaction ID', 'TxnId');
    }
    
    function getSqlSaveFieldsFromPost() {
        $fields = parent::getSqlSaveFieldsFromPost();
        $fields['Quantity'] = $_POST['Quantity'];
        $fields['TxnId'] = $_POST['TxnId'];
        return $fields;
    }
}

function pluralizeTickets($count) {
    if ($count == 1) {
        return "ticket";
    } else {
        return "tickets";
    }
}

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Standard header stuff

$action = request_int('action', DEAD_DOG);
$manager = new DeadDogManager();

if ($action == DEAD_DOG) {
    html_begin ();
    dead_dog($manager);
} else if ($action == DEAD_DOG_PAYPAL_REDIRECT) {
    if (!is_logged_in()) {
        display_error("Sorry, you must be logged in to buy Dead Dog tickets.");
    } else {
        $quantity = request_int('quantity', 0);
        if ($quantity < 1) {
            html_begin();
            display_error("Please specify a number of Dead Dog tickets to purchase.");
            dead_dog($manager);
        } else {
            $paypalLink = new DeadDogPaypalLink($_REQUEST["quantity"]);
            header("Location: ".$paypalLink->getUrl());
            exit;
        }
    }
} else {
    html_begin ();
    if (!$manager->processAction($action)) {
        echo "Unknown action code: $action\n";
    }
}

function dead_dog($manager) {
    printf ("<h2>%s Dead Dog</h2>\n", CON_NAME);
    readfile("DeadDogInfo.html");
    
    echo "<div class=\"dead_dog_signup\">";
    if (is_logged_in()) {
        $payments = $manager->fetchRowsForLoggedInUser(array("where" => "i.Status = 'Paid'"));
        $ticketCount = 0;
        foreach ($payments as $payment) {
            $ticketCount += $payment->Quantity;
        }
        
        if ($ticketCount > 0) {
            echo "<h3>You have bought $ticketCount Dead Dog ";
            echo pluralizeTickets($ticketCount);
            echo ".  Thank you!</h3>\n";
        }
        
        $availableSlots = $manager->availableSlots();
        if ($manager->ticketsFrozen()) {
            echo "<h3>Pre-orders for tickets have ended</h3>";
            
            echo "<p>There are up to $availableSlots tickets available at ";
            echo "the convention; please see Ops if you want to purchase ";
            echo "one.</p>";
        } elseif ($availableSlots > 0) {
            if ($ticketCount == 0) {
                echo "<h3>Sign up for the Dead Dog!</h3>";
            } else {
                echo "<p><b>Buy additional tickets</b></p>\n";
            }
            
            echo "<p>There are currently <b>".$availableSlots."</b> ";
            echo pluralizeTickets($availableSlots);
            echo " available for the Dead Dog.</p>\n";

            $paypalLink = new DeadDogPaypalLink();
            echo "<form action=\"".$_SERVER["PHP_SELF"]."\">\n";
            echo "<div style=\"text-align: center;\">";
            echo "<input type=\"hidden\" name=\"action\" value=\"".DEAD_DOG_PAYPAL_REDIRECT."\"/>\n";
            $paypalLink->displayQuantitySelector($availableSlots);
            echo "<input type=\"submit\" value=\"Buy Dead Dog Tickets\"/>\n";
            echo "</div>\n";
            
            if ($ticketCount == 0) {
                echo "<p>Please note that we cannot guarantee availability unless you\n";
                echo "pay in advance!</p>\n";
            }
            echo "</form>\n";
        } else {
            if ($ticketCount == 0) {
                echo "<h3>Sorry, there are no more seats available!</h3>\n";
            
                echo "<p>We've sold all the seats in the house.  Sorry to disappoint!</p>\n";
            } else {
                echo "<p>Dead Dog is now sold out.  Thanks for registering!</p>\n";
            }
        }
    } else {
        echo "<p>To register for the Dead Dog, please <a href=\"index.php\">log in</a>.</p>";
    }
    echo "</div>\n";
}

html_end();

?>