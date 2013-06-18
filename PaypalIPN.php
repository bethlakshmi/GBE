<?php
// Based on PayPal's IPN sample listener
// https://cms.paypal.com/cms_content/US/en_US/files/developer/IPN_PHP_41.txt

function exit_400($msg) {
  header("HTTP/1.1 400 Bad Request");
  echo $msg;
  exit();
}

include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  exit_400('Failed to establish connection to the database');
}

/*
 * log_paypal_msgs
 *
 * Send a copy of the PayPal messages home, so we can try to figure out
 * what's going on
 */

function log_paypal_msgs ()
{
  // Dump the POST array to the message

  $msg = "POST Parameters:\n";
  reset ($_POST);
  foreach ($_POST as $k => $v)
  {
    $msg .= "[$k] = $v\n";
  }
  reset ($_POST);

  // And the SESSION array

  $msg .= "\nSESSION Parameters:\n";
  reset ($_SESSION);
  foreach ($_SESSION as $k => $v)
  {
    $msg .= "[$k] = $v\n";
  }
  reset ($_SESSION);

  // And the SERVER array

  $msg .= "\nSERVER Parameters:\n";
  reset ($_SERVER);
  foreach ($_SERVER as $k => $v)
  {
    $msg .= "[$k] = $v\n";
  }
  reset ($_SERVER);

  // Phone home

  intercon_mail (DEVELOPMENT_MAIL_ADDR, 'PayPal Log Message', $msg);
}

/*
 * mark_user_paid
 *
 * If the user has just paid through PayPal, update his status
 */

function mark_user_paid ()
{
  //  dump_array ('POST - mark_user_paid', $_POST);
  
  // Flip the "Paid" bit in the user's record

  $paid_by = 'Paid via PayPal ' . strftime ('%d-%b-%Y %H:%M');
  if (array_key_exists ('txn_id', $_POST))
    $paid_by .= ' TxnID: ' . $_POST['txn_id'];
  if (array_key_exists ('last_name', $_POST))
  {
    $paid_by .= ' PaidBy: ' . $_POST['last_name'];
    if (array_key_exists ('first_name', $_POST))
      $paid_by .= ', ' . $_POST['first_name'];
  }

  $amount = 0;
  if (array_key_exists ('payment_gross', $_POST))
    $amount = intval ($_POST['payment_gross']) * 100;

  // There are two types of payment that may come in here; con registration and
  // shirt payments.  We can differentiate them by the "item_name" field.

  if (! array_key_exists ('item_name', $_POST))
    exit_400('PayPal message does not contain "item_name" field.  We can\'t tell what\'s being paid for!');

  $user_id = 0;

  $bConPayment = $_POST['item_name'] == PAYPAL_ITEM_CON;
  $bShirtPayment = $_POST['item_name'] == PAYPAL_ITEM_SHIRT;
  $bThursdayPayment = $_POST['item_name'] == PAYPAL_ITEM_THURSDAY;

  /*
  if ($bConPayment)
    echo "<!-- Con payment -->\n";
  if ($bShirtPayment)
    echo "<!-- Shirt payment -->\n";
  if ($bThursdayPayment)
    echo "<!-- Thursday payment -->\n";
  */

  if ($bConPayment)
  {
    // If this is a con payment, the custom field is the UserId.  Mark the
    // user paid

    if (array_key_exists ('custom', $_POST))
      $user_id = intval ($_POST['custom']);

    $sql = "UPDATE Users SET CanSignup='Paid'";
    $sql .= ', CanSignupModified=NULL';
    $sql .= ", CanSignupModifiedId=$user_id";
    $sql .= ", PaymentNote='$paid_by'";
    $sql .= ", PaymentAmount=$amount";
    $sql .= " WHERE UserId=$user_id";
    //  echo "$sql<p>\n";
    $result = mysql_query ($sql);
    if (! $result)
      exit_400("Failed to update user $user_id with notification from PayPal");

    // If we've got session info, we're done

    if (array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
      return TRUE;
  }
  elseif ($bShirtPayment)
  {
    // If this is a shirt payment, the custom field is the TShirtId.  Mark the
    // shirt paid and fetch the UserId from the TShirt record

    $TShirtID = 0;
 
    if (array_key_exists ('custom', $_POST))
      $TShirtID = intval ($_POST['custom']);

    $sql = 'UPDATE TShirts SET Status="Paid"';
    $sql .= ", PaymentNote='$paid_by'";
    $sql .= ", PaymentAmount=$amount";
    $sql .= " WHERE TShirtID=$TShirtID";
    //  echo "$sql<p>\n";
    $result = mysql_query ($sql);
    if (! $result)
      exit_400("Failed to update shirt record $TShirtID with notification from PayPal");

    // If we've got session info, we're done

    if (array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
      return TRUE;

    // Otherwise, we're going to need the UserId so we can log him (or her)
    // back in

    $sql = "SELECT UserId FROM TShirts WHERE TShirtID=$TShirtID";
    //  echo "$sql<p>\n";
    $result = mysql_query ($sql);
    if (! $result)
      exit_400("Failed to fetch shirt record $TShirtID");

    $num_rows = mysql_num_rows($result);
    if (1 != mysql_num_rows($result))
      exit_400("$num_rows rows returned for shirt record $TShirtID");
    $row = mysql_fetch_object($result);
    $user_id = $row->UserId;
  }
  elseif ($bThursdayPayment)
  {
    // If this is a Thursday Thing payment, the custom field is the UserId.
    // Add a Thursday record for the user

    if (array_key_exists ('custom', $_POST))
      $user_id = intval ($_POST['custom']);

    $sql = "INSERT Thursday SET UserId=$user_id,";
    $sql .= 'Status="Paid", ';
    $sql .= "PaymentNote='$paid_by', ";
    $sql .= "PaymentAmount=$amount";

    $result = mysql_query($sql);
    if (! $result)
      exit_400("Failed to insert Thursday record for $user_id", $sql);

    // If we've got session info, we're done

    if (array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
      return true;
  }
  else
  {
    exit_400("Unknown payment type! " . $_POST['item_name']);
  }

  // Refetch the user info & log them in again, since it's probably lost

  $sql = 'SELECT FirstName, LastName, UserId, Priv, Gender, CanSignup, Email';
  $sql .= ' FROM Users';
  $sql .= " WHERE UserId=$user_id";

  $result = mysql_query ($sql);
  if (! $result)
    exit_400('Cannot execute query', $sql);

  // Make sure we've gotten a single match

  if (0 == mysql_num_rows ($result))
    return 'Failed to find matching EMail address / password';

  if (1 != mysql_num_rows ($result))
    return 'Found more than one matching EMail address';

  // Extract the UserId for the user being logged in and decode the privileges

  $row = mysql_fetch_object ($result);

  return login_with_data ($row, $EMail);
}

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
  $value = urlencode(stripslashes($value));
  $req .= "&$key=$value";
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];

if (!$fp) {
  // HTTP ERROR
} else {
  fputs ($fp, $header . $req);
  while (!feof($fp)) {
    $res = fgets ($fp, 1024);
    if (strcmp ($res, "VERIFIED") == 0) {
      // Check whether PayPal is notifying us that they've accepted payment for us

      if (array_key_exists ('txn_type', $_POST) &&
          array_key_exists ('payment_status', $_POST))
      {
        if (('web_accept' == $_POST['txn_type']) &&
    	    ('Completed' == $_POST['payment_status']))
          mark_user_paid ();
      }
      // check the payment_status is Completed
      // check that txn_id has not been previously processed
      // check that receiver_email is your Primary PayPal email
      // check that payment_amount/payment_currency are correct
      // process payment
    } else if (strcmp ($res, "INVALID") == 0) {
      log_paypal_msgs ();
    }
  }
  fclose ($fp);
}
?>