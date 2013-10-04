<?php
include ("intercon_db.inc");

// If the user's not logged in, send him to the entrypoint

if (! array_key_exists (SESSION_LOGIN_USER_ID, $_SESSION))
{
  header ('Location: index.php');
  exit ();
}

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to connect to ' . DB_NAME);
  exit ();
}

// Display boilerplate

html_begin ();

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = SHOW_TSHIRT_FORM;

switch ($action)
{
  case SHOW_TSHIRT_FORM:
    show_tshirt_form ();
    break;

   case SHOW_TSHIRT_SUMMARY:
     show_tshirt_summary ();
     break;

   case SHOW_TSHIRT_REPORT:
     show_tshirt_report ();
     break;

   case SHOW_TSHIRT_PAYMENT_LINK:
     show_shirt_payment_link();
     break;

   case SHOW_INDIV_TSHIRT_FORM:
     if (! show_indiv_tshirt_form())
       show_tshirt_report();
     break;

   case PROCESS_INDIV_TSHIRT_FORM:
     if (! process_indiv_tshirt_form())
       show_indiv_tshirt_form();
     else
       show_tshirt_report();
     break;

   case SELECT_USER_TO_SELL_SHIRT:
     select_user_to_sell_shirt();
     break;

   default:
     display_error ("Unknown action code: $action");
}

// Standard postamble

html_end ();

/*
 * quantity_form_text
 *
 * Add a text input field
 */

function quantity_form_text ($display, $key='')
{
  // If not specified, fill in default values

  if ($key == '')
    $key = $display;

  if ("" != $display)
    $display .= ":";

  // If magic quotes are on, strip off the slashes

  if (! array_key_exists ($key, $_POST))
    $text = '';
  else
  {
    if (1 == get_magic_quotes_gpc())
      $text = stripslashes ($_POST[$key]);
    else
      $text = $_POST[$key];
  }

  // Spit out the HTML

  printf ("    <td>%s<input type=text name=%s size=2 maxlength=2 value=\"%s\"> %s</td>\n",
	  $display,
	  $key,
	  htmlspecialchars ($text),
	  '&nbsp;&nbsp;');
}

function hidden_form_text ($key)
{
  // Spit out the HTML

  printf ("    <input type=\"hidden\" name=\"%s\" value=\"0\">\n",
	  $key);
}

function order_string (&$shirts, $index, $order)
{
  if (0 == $shirts[$index])
    return '';

  if ('' != $order)
    $retval = ', ';
  else
    $retval = '';

  $index_text = $index;
  if (FALSE != strstr($index, '_2'))
    $index_text = str_replace ('_2', '', $index);

  $retval .= $shirts[$index] . ' ' . $index_text;

  return $retval;
}

/*
 * show_tshirt_form
 *
 * Display a form to allow a user to request an Intercon TShirt
 */

function show_tshirt_form ()
{
  // Are we past the shirt order deadline?

  $can_order_shirts = ! past_shirt_deadline();

  // See if there's are any records in the database for this user

  $sql = 'SELECT * FROM TShirts';
  $sql .= ' WHERE UserId=' . $_SESSION[SESSION_LOGIN_USER_ID];

  //  echo "Query: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for TShirt record failed', $sql);

  $shirts = array('Small'=>0,'Medium'=>0,'Large'=>0,'XLarge'=>0,
		  'XXLarge'=>0,'X3Large'=>0,'X4Large'=>0,'X5Large'=>0,
                  'Small_2'=>0,'Medium_2'=>0,'Large_2'=>0,'XLarge_2'=>0,
		  'XXLarge_2'=>0,'X3Large_2'=>0,'X4Large_2'=>0,'X5Large_2'=>0);
  $status = 'Paid';
  $count = 0;
  $order = '';
  $TShirtID = 0;

  while ($row = mysql_fetch_object ($result))
  {
    // The only records we're interested in are Unpaid

    if ('Unpaid' != $row->Status)
      continue;

    $shirts['Small'] += $row->Small;
    $shirts['Medium'] += $row->Medium;
    $shirts['Large'] += $row->Large;
    $shirts['XLarge'] += $row->XLarge;
    $shirts['XXLarge'] += $row->XXLarge;
    $shirts['X3Large'] += $row->X3Large;
    $shirts['X4Large'] += $row->X4Large;
    $shirts['X5Large'] += $row->X5Large;

    $shirts['Small_2'] += $row->Small_2;
    $shirts['Medium_2'] += $row->Medium_2;
    $shirts['Large_2'] += $row->Large_2;
    $shirts['XLarge_2'] += $row->XLarge_2;
    $shirts['XXLarge_2'] += $row->XXLarge_2;
    $shirts['X3Large_2'] += $row->X3Large_2;
    $shirts['X4Large_2'] += $row->X4Large_2;
    $shirts['X5Large_2'] += $row->X5Large_2;

    $count = $row->Small+$row->Medium+$row->Large+$row->XLarge+
             $row->XXLarge+$row->X3Large+$row->X4Large+$row->X5Large;
    $count += $row->Small_2+$row->Medium_2+$row->Large_2+$row->XLarge_2+
             $row->XXLarge_2+$row->X3Large_2+$row->X4Large_2+$row->X5Large_2;
    $status = $row->Status;

    $TShirtID = $row->TShirtID;
    break;
  }

  $order = order_string ($shirts, 'Small', $order);
  $order .= order_string ($shirts, 'Medium', $order);
  $order .= order_string ($shirts, 'Large', $order);
  $order .= order_string ($shirts, 'XLarge', $order);
  $order .= order_string ($shirts, 'XXLarge', $order);
  $order .= order_string ($shirts, 'X3Large', $order);
  //  $order .= order_string ($shirts, 'X4Large', $order);
  //  $order .= order_string ($shirts, 'X5Large', $order);

  $_POST['Small'] = $shirts['Small'];
  $_POST['Medium'] = $shirts['Medium'];
  $_POST['Large'] = $shirts['Large'];
  $_POST['XLarge'] = $shirts['XLarge'];
  $_POST['XXLarge'] = $shirts['XXLarge'];
  $_POST['X3Large'] = $shirts['X3Large'];
  $_POST['X4Large'] = $shirts['X4Large'];
  $_POST['X5Large'] = $shirts['X5Large'];

  $order2 = order_string ($shirts, 'Small_2', $order);
  $order2 .= order_string ($shirts, 'Medium_2', $order);
  $order2 .= order_string ($shirts, 'Large_2', $order);
  $order2 .= order_string ($shirts, 'XLarge_2', $order);
  $order2 .= order_string ($shirts, 'XXLarge_2', $order);
  //  $order2 .= order_string ($shirts, 'X3Large_2', $order);
  //  $order2 .= order_string ($shirts, 'X4Large_2', $order);
  //  $order2 .= order_string ($shirts, 'X5Large_2', $order);

  $_POST['Small_2'] = $shirts['Small_2'];
  $_POST['Medium_2'] = $shirts['Medium_2'];
  $_POST['Large_2'] = $shirts['Large_2'];
  $_POST['XLarge_2'] = $shirts['XLarge_2'];
  $_POST['XXLarge_2'] = $shirts['XXLarge_2'];
  $_POST['X3Large_2'] = $shirts['X3Large_2'];
  $_POST['X4Large_2'] = $shirts['X4Large_2'];
  $_POST['X5Large_2'] = $shirts['X5Large_2'];

  $shirt_close = strftime ('%d-%b-%Y', parse_date (SHIRT_CLOSE));

  //  echo "Shirt_Close: $shirt_close<p>\n";
  //  echo "TShirtID: $TShirtID<p>\n";
  //  echo "status: $status<p>\n";
  //  echo "can_order_shirts: $can_order_shirts<p>\n";
  //  printf ("Now: %d, shirt_close: %d<p>\n", $now, parse_date (SHIRT_CLOSE));
  //  $can_order_shirts = parse_date (SHIRT_CLOSE) < time();

  $thing_singular = SHIRT_NAME . ' Shirt';
  $thing_plural = SHIRT_NAME . ' Shirts';
  $thing2_singular = SHIRT_2_NAME . ' Shirt';
  $thing2_plural = SHIRT_2_NAME . ' Shirts';

  if (1 == $count)
  {
    $thing = $thing_singular;
    $thing2 = $thing2_singular;
    $it = 'It';
    $it_lc = 'it';
  }
  else
  {
    $thing = $thing_plural;
    $thing2 = $thing2_plural;
    $it = 'They';
    $it_lc = 'them';
  }

  if (! $can_order_shirts)
  {
    // If it's past the deadline and you don't have any orders pending,
    // you're out of luck.

    if (0 == $TShirtID)
    {
      echo "The order deadline for shirts was $shirt_close.  When you\n";
      echo "checkin at registration at the con ask if there are any shirts\n";
      echo "in your size.<p>\n";
      
      return show_shirts();
    }

    // If you've ordered shirts, you can only see what you've ordered.

    if ('Paid' == $status)
    {
      echo "You have ordered $order $thing.  $it will be available when you ";
      echo "checkin at the convention.<p>\n";
      echo "The order deadline for shirts was $shirt_close.  If you want\n";
      echo "additional shirts you should ask whether there are any shirts\n";
      echo "available in your size when you checkin at registration at the\n";
      echo "con.<p>\n";

      return show_shirts();
    }

    // If you've ordered shirts but haven't paid for them, you can pay now,
    // but not ask for any more.

    echo "You have ordered $order $thing.  You can pay for $it_lc now by\n";
    echo "clicking on the button below.\n";
    echo "<form method=post action=TShirts.php>\n";
    form_add_sequence ();
    printf ("<input type=hidden name=action value=%d>\n",
	    SHOW_TSHIRT_PAYMENT_LINK);
    echo "<input type=hidden name=TShirtID value=$TShirtID>\n";
    foreach ($shirts as $k => $v)
      echo "<input type=hidden name=\"$k\" value=\"$v\">\n";
    echo "<input type=submit value=\"Pay now\">\n";
    echo "</form>\n";

    return show_shirts();
  }


  // Display the header for the user's TShirt order

  display_header ('Don\'t Lose Your Shirt!');

  echo "Only a small number of " . CON_NAME . " shirts will be available\n";
  echo "for sale at the convention.  The only way to guarantee that you get\n";
  echo "the shirt you want is to order and pay for it now.<p>\n";
  echo "The deadline for shirt orders is $shirt_close.\n";
  echo "Why wait and risk losing your shirt?<p>\n";

  if (0 <> $count)
  {
    if ('Paid' != $status)
    {
      $unpaid_order = '';
      if ('' != $order)
	$unpaid_order = "$order Unpaid $thing";
      if ('' != $order2)
      {
	if ('' != $unpaid_order)
	  $unpaid_order .= ' and ';
	$unpaid_order .=  "$order2 Unpaid $thing2";
      }
      echo "You have $unpaid_order ordered.  You must pay for these\n";
      echo "shirts before you order more.\n";
    }
    else
    {
      $paid_order = '';
      if ('' != $order)
	$paid_order = "$order $thing";
      if ('' != $order2)
      {
	if ('' != $paid_order)
	  $paid_order .= ' and ';
	$paid_order .=  "$order2 $thing2";
      }
      echo "You have ordered $paid_order.  It will be available when you ";
      echo "register at the convention.<p>\n";
    }
  }

  echo "<form method=post action=TShirts.php>\n";
  form_add_sequence ();
  printf ("<input type=hidden name=action value=%d>\n",
	  SHOW_TSHIRT_PAYMENT_LINK);
  echo "<input type=hidden name=TShirtID value=$TShirtID>\n";

  echo "<table class=\"shirt\" border=1 width=\"100%\">\n";
  echo "  <tr>\n";
  if (0 == $count)
  {
    echo "    <td align=center>\n";
    printf ("Shirts cost \$%d.00 per shirt, payable when you order them.<p>\n",
	    TSHIRT_DOLLARS);

    $email = mailto_or_obfuscated_email_address (EMAIL_OPS);
    echo "This year there are TWO shirts for you to choose from! ";
    /*
    echo "The\n";
    echo "first is a burgundy men's polo shirt.\n";
    echo "The second is a baby blue babydoll-style shirt.\n";
    echo "Both shirts are 100% cotton.\n";
    */
    
    echo "We'll have information about these shirts for you soon, once we finalize ";
    echo "the designs.\n";
    
    echo "If you want a size that's not listed on the website,\n";
    echo 'please contact ' . NAME_OPS . " at $email.<p>\n";
    echo "We will be ordering fewer shirts this year, so if you want\n";
    echo "to be sure of getting one in your size, be sure to order it\n";
    echo "now.\n";

    echo "    </td>\n";
  }
  else
  {
    $paid_order = '';
    if ('' != $order)
    $paid_order = "$order $thing";
    if ('' != $order2)
    {
      if ('' != $paid_order)
	$paid_order .= ' and ';
      $paid_order .=  "$order2 $thing2";
    }

    echo "    <td align=center class=\"shirtReverse\">\n";
    echo "You have ordered $paid_order.\n";
    echo "    </td>\n";
  }

  echo "  </tr>\n";
  echo "  <tr class=\"shirtBody\">\n";
  echo "    <td align=center>\n";
  echo "      <table>\n";
  echo "        <tr class=\"shirtBody\">\n";
  echo "          <th colspan=8>$thing_singular Sizes Available</th>\n";
  echo "        </tr>\n";
  echo "        <tr class=\"shirtBody\">\n";
  quantity_form_text ('Small');
  quantity_form_text ('Medium');
  quantity_form_text ('Large');
  quantity_form_text ('XLarge');
  quantity_form_text ('XXLarge');
  quantity_form_text ('X3Large');
  //  quantity_form_text ('X4Large');
  hidden_form_text ('X4Large');
  //  quantity_form_text ('X5Large');
  hidden_form_text ('X5Large');
  echo "        </tr>\n";

  if (SHIRT_TWO_SHIRTS)
  {
    echo "        <tr class=\"shirtBody\">\n";
    echo "          <th colspan=8><br>$thing2_singular Sizes Available</th>\n";
    echo "        </tr>\n";
    echo "        <tr class=\"shirtBody\">\n";
    quantity_form_text ('Small', 'Small_2');
    quantity_form_text ('Medium', 'Medium_2');
    quantity_form_text ('Large', 'Large_2');
    quantity_form_text ('XLarge', 'XLarge_2');
    quantity_form_text ('XXLarge', 'XXLarge_2');
    //  quantity_form_text ('X3Large_2');
    hidden_form_text ('X3Large_2');
    //  quantity_form_text ('X4Large_2');
    hidden_form_text ('X4Large_2');
    //  quantity_form_text ('X5Large_2');
    hidden_form_text ('X5Large_2');
    echo "        </tr>\n";
  }

  echo "        <tr class=\"shirtBody\" valign=top>\n";
  echo "          <td colspan=8 align=center>\n";
  echo "            <input type=submit value=\"Submit\">\n";
  echo "          </td>\n";
  echo "        </tr>\n";
  echo "      </table>\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "</table>\n";
  echo "</form>\n";
  echo "<p>\n";

  return show_shirts();
}

function show_shirt_payment_link()
{
  // Make sure the user hasn't used the back key

  if (out_of_sequence ())
    return display_sequence_error (false);

  // Validate our data

  $ok = validate_quantity ('Small');
  $ok &= validate_quantity ('Medium');
  $ok &= validate_quantity ('Large');
  $ok &= validate_quantity ('XLarge');
  $ok &= validate_quantity ('XXLarge');
  $ok &= validate_quantity ('X3Large');
  $ok &= validate_quantity ('X4Large');
  $ok &= validate_quantity ('X5Large');

  if (SHIRT_TWO_SHIRTS)
  {
    $ok &= validate_quantity ('Small_2');
    $ok &= validate_quantity ('Medium_2');
    $ok &= validate_quantity ('Large_2');
    $ok &= validate_quantity ('XLarge_2');
    $ok &= validate_quantity ('XXLarge_2');
    $ok &= validate_quantity ('X3Large_2');
    $ok &= validate_quantity ('X4Large_2');
    $ok &= validate_quantity ('X5Large_2');
  }

  if (! $ok)
    return false;

  // If we've already got a TShirtID, don't create a new one

  //  dump_array ('POST', $_POST);

  $TShirtID = 0;
  if (array_key_exists ('TShirtID', $_POST))
    $TShirtID = intval($_POST['TShirtID']);

  // Add this order to the database

  if (0 == $TShirtID)
    $sql = 'INSERT TShirts SET Status="Unpaid"';
  else
    $sql = 'UPDATE TShirts SET Status="Unpaid"';

  $sql .= build_sql_string ('UserId', $_SESSION[SESSION_LOGIN_USER_ID]);
  $sql .= build_sql_string ('Small');
  $sql .= build_sql_string ('Medium');
  $sql .= build_sql_string ('Large');
  $sql .= build_sql_string ('XLarge');
  $sql .= build_sql_string ('XXLarge');
  $sql .= build_sql_string ('X3Large');
  $sql .= build_sql_string ('X4Large');
  $sql .= build_sql_string ('X5Large');

  if (SHIRT_TWO_SHIRTS)
  {
    $sql .= build_sql_string ('Small_2');
    $sql .= build_sql_string ('Medium_2');
    $sql .= build_sql_string ('Large_2');
    $sql .= build_sql_string ('XLarge_2');
    $sql .= build_sql_string ('XXLarge_2');
    $sql .= build_sql_string ('X3Large_2');
    $sql .= build_sql_string ('X4Large_2');
    $sql .= build_sql_string ('X5Large_2');
  }
  $sql .= ', LastUpdated=NULL';

  if (0 != $TShirtID)
    $sql .= " WHERE TShirtID=$TShirtID";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to create TShirt record', $sql);

  if (0 == $TShirtID)
    $TShirtID = mysql_insert_id();

  //  echo "TShirtID: $TShirtID<p>\n";

  $count = $_POST['Small'] + $_POST['Medium'] + $_POST['Large'];
  $count += $_POST['XLarge'] + $_POST['XXLarge'] + $_POST['X3Large'];
  $count += $_POST['X4Large'] + $_POST['X5Large'];

  if (SHIRT_TWO_SHIRTS)
  {
    $count += $_POST['Small_2'] + $_POST['Medium_2'] + $_POST['Large_2'];
    $count += $_POST['XLarge_2'] + $_POST['XXLarge_2'] + $_POST['X3Large_2'];
    $count += $_POST['X4Large_2'] + $_POST['X5Large_2'];
  }

  $cost = $count * TSHIRT_DOLLARS;
  $cost = "$cost.00";

  // If this is a development installation, force the price down to a nickle.
  // I'm willing to spend 5 cents/test, but no full price

  if (DEVELOPMENT_VERSION)
    $cost = '0.05';

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
  $url .= build_url_string ('item_name', PAYPAL_ITEM_SHIRT);
  $url .= build_url_string ('no_note', '0');
  $url .= build_url_string ('cn', 'Any notes about your payment?');
  $url .= build_url_string ('no_shipping', '1');
  $url .= build_url_string ('custom', $TShirtID);
  $url .= build_url_string ('currency_code', 'USD');
  $url .= build_url_string ('amount', $cost);
  $url .= build_url_string ('rm', '2');
  $url .= build_url_string ('cancel_return', $cancel_url);
  $url .= build_url_string ('return', $return_url, FALSE);

  //  echo "Return URL: $return_url<br>\n";
  //  echo "Encoded URL: $url<p>\n";
  //  printf ("%d characters<p>\n", strlen ($url));

  echo "To complete your shirt purchase, click <a href=$url>here</a> to\n";
  echo "pay \$$cost using PayPal.  Please be sure to click the \"Return to Merchant\" button on the\n";
  echo "PayPal site to return to the " . CON_NAME . " website to register\n";
  echo "your payment for the shirts.<p>\n";
  echo "If you don't want to join PayPal, you can send a check or money\n";
  echo "order for \$$cost made out to\n";
  echo "&quot<B>New&nbsp;England&nbsp;Interactive&nbsp;Literature</B>&quot;\n";
  echo "to<br>\n";
  echo "<table>\n";
  echo "  <tr>\n";
  echo "    <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
  printf ("    <td><b>%s<br>c/o %s<br>%s</b></td>\n",
	  CON_NAME,
	  NAME_SEND_CHECKS,
	  ADDR_SEND_CHECKS);
  echo "  </tr>\n";
  echo "</table>\n";
  echo "<p>\n";
}

function validate_quantity ($key)
{
  if (validate_int ($key, 0, 10))
    return true;

  return display_error ("Invalid number of $key Shirts.  " .
			'Values must be in the range of 0 and 10');
}

function show_tshirt_summary ()
{
  // You need ConCom privilege to see this page

  if (! user_has_priv (PRIV_CON_COM))
    return display_access_error ();

  $small = 0;
  $medium = 0;
  $large = 0;
  $xlarge = 0;
  $xxlarge = 0;
  $x3large = 0;
  $x4large = 0;
  $x5large = 0;

  $small_2 = 0;
  $medium_2 = 0;
  $large_2 = 0;
  $xlarge_2 = 0;
  $xxlarge_2 = 0;
  $x3large_2 = 0;
  $x4large_2 = 0;
  $x5large_2 = 0;

  $count = 0;

  // Get the list of orders

  $sql = 'SELECT Small, Medium, Large, XLarge, XXLarge,';
  $sql .= ' X3Large, X4Large, X5Large,';
  $sql .= ' Small_2, Medium_2, Large_2, XLarge_2, XXLarge_2,';
  $sql .= ' X3Large_2, X4Large_2, X5Large_2';
  $sql .= ' FROM TShirts';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for TShirts failed', $sql);

  display_header (CON_NAME . ' Shirt Order Summary');
  
  while ($row = mysql_fetch_object ($result))
  {
    $small += $row->Small;
    $medium += $row->Medium;
    $large += $row->Large;
    $xlarge += $row->XLarge;
    $xxlarge += $row->XXLarge;
    $x3large += $row->X3Large;
    $x4large += $row->X4Large;
    $x5large += $row->X5Large;

    $small_2 += $row->Small_2;
    $medium_2 += $row->Medium_2;
    $large_2 += $row->Large_2;
    $xlarge_2 += $row->XLarge_2;
    $xxlarge_2 += $row->XXLarge_2;
    $x3large_2 += $row->X3Large_2;
    $x4large_2 += $row->X4Large_2;
    $x5large_2 += $row->X5Large_2;

    if ((0 != $row->Small) ||
	(0 != $row->Medium) ||
	(0 != $row->Large) ||
	(0 != $row->XLarge) ||
	(0 != $row->XXLarge) ||
	(0 != $row->X3Large) ||
	(0 != $row->X4Large) ||
	(0 != $row->X5Large) ||
	(0 != $row->Small_2) ||
	(0 != $row->Medium_2) ||
	(0 != $row->Large_2) ||
	(0 != $row->XLarge_2) ||
	(0 != $row->XXLarge_2) ||
	(0 != $row->X3Large_2) ||
	(0 != $row->X4Large_2) ||
	(0 != $row->X5Large_2))
      $count++;
  }

  $total = $small + $medium + $large + $xlarge + $xxlarge;
  $total += $x3large + $x4large + $x5large;

  $total += $small_2 + $medium_2 + $large_2 + $xlarge_2 + $xxlarge_2;
  $total += $x3large_2 + $x4large_2 + $x5large_2;

  echo "<table border=\"1\">\n";
  printf ("  <tr><th>&nbsp;</th><th>%s</th><th>%s</th></tr>\n",
	  SHIRT_NAME, SHIRT_2_NAME);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'Small', $small, $small_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'Medium', $medium, $medium_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'Large', $large, $large_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'XLarge', $xlarge, $xlarge_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'XXLarge', $xxlarge, $xxlarge_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'X3Large', $x3large, $x3large_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'X4Large', $x4large, $x4large_2);
  printf ("  <tr align=\"center\"><th>%s:</th><td>%d</td><td>%d</td></tr>\n",
	  'X5Large', $x5large, $x5large_2);
  echo "  <tr align=\"center\"><th>Total:</th><th colspan=\"2\">$total shirts</th></tr>\n";
  echo "</table>\n";
}

function show_quantity ($n)
{
  if (0 == $n)
    echo "    <td>&nbsp;</td>\n";
  else
    echo "    <td>$n</td>\n";
}

function show_tshirt_report ()
{
  // You need Staff privilege to see this page

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  // If not specified, sort by name

  if (array_key_exists ('OrderBy', $_REQUEST))
    $OrderBy = intval ($_REQUEST['OrderBy']);
  else
    $OrderBy = ORDER_BY_NAME;

  // Initialize our counters

  $small = 0;
  $medium = 0;
  $large = 0;
  $xlarge = 0;
  $xxlarge = 0;
  $x3large = 0;
  $x4large = 0;
  $x5large = 0;

  $small_2 = 0;
  $medium_2 = 0;
  $large_2 = 0;
  $xlarge_2 = 0;
  $xxlarge_2 = 0;
  $x3large_2 = 0;
  $x4large_2 = 0;
  $x5large_2 = 0;

  $count = 0;

  // Get the list of orders

  $sql = 'SELECT Users.FirstName, Users.LastName, Users.EMail,';
  $sql .= 'TShirts.*';
  $sql .= ' FROM TShirts, Users';
  $sql .= ' WHERE Users.UserId=TShirts.UserId';

  if (ORDER_BY_NAME == $OrderBy)
    $sql .= ' ORDER BY LastName, FirstName';
  else
    $sql .= ' ORDER BY TShirtID';

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for TShirts failed', $sql);

  display_header (CON_NAME . ' Shirt Order Report');

  echo "<p>Click on the user name to send mail<br>\n";
  echo "Click on the status to update the order<br>\n";
  printf ("<a href=\"TShirts.php?action=%d\">Add new shirt sale</a><p>\n",
	  SELECT_USER_TO_SELL_SHIRT);

  // Display the orders

  echo "<table border=1>\n";
  echo "  <tr>\n";
  printf ("    <th rowspan=\"2\"><a href=TShirts.php?action=%d&OrderBy=%d>ID</a></th>\n",
	  SHOW_TSHIRT_REPORT,
	  ORDER_BY_SEQ);
  printf ("    <th align=left rowspan=\"2\"><a href=TShirts.php?action=%d&OrderBy=%d>Name</a></th>\n",
	  SHOW_TSHIRT_REPORT,
	  ORDER_BY_NAME);
  echo "    <th rowspan=\"2\">Status</th>\n";
  printf ("    <th colspan=\"6\">%s</th>\n", SHIRT_NAME);
  if (SHIRT_TWO_SHIRTS)
    printf ("    <th colspan=\"6\">%s</th>\n", SHIRT_2_NAME);
  echo "  </tr>\n";
  echo "  <tr>\n";
  echo "    <th>Small</th>\n";
  echo "    <th>Medium</th>\n";
  echo "    <th>Large</th>\n";
  echo "    <th>XLarge</th>\n";
  echo "    <th>XXLarge</th>\n";
  echo "    <th>X3Large</th>\n";
  //  echo "    <th>X4Large</th>\n";
  //  echo "    <th>X5Large</th>\n";
  if (SHIRT_TWO_SHIRTS)
  {
    echo "    <th>Small</th>\n";
    echo "    <th>Medium</th>\n";
    echo "    <th>Large</th>\n";
    echo "    <th>XLarge</th>\n";
    echo "    <th>XXLarge</th>\n";
    echo "    <th>X3Large</th>\n";
    //  echo "    <th>X4Large</th>\n";
    //  echo "    <th>X5Large</th>\n";
  }
  echo "  </tr>\n";

  // Now display each order, skipping any empty ones

  while ($row = mysql_fetch_object ($result))
  {
    if ((0 == $row->Small) &&
	(0 == $row->Medium) &&
	(0 == $row->Large) &&
	(0 == $row->XLarge) &&
	(0 == $row->XXLarge) &&
	(0 == $row->X3Large) &&
	(0 == $row->X4Large) &&
	(0 == $row->X5Large) &&
        (0 == $row->Small_2) &&
	(0 == $row->Medium_2) &&
	(0 == $row->Large_2) &&
	(0 == $row->XLarge_2) &&
	(0 == $row->XXLarge_2) &&
	(0 == $row->X3Large_2) &&
	(0 == $row->X4Large_2) &&
	(0 == $row->X5Large_2))
      continue;

    switch ($row->Status)
    {
      case 'Paid':      $bgcolor = get_bgcolor('Confirmed');  break; // Green
      case 'Unpaid':    $bgcolor = get_bgcolor('Waitlisted'); break; // Yellow
      case 'Cancelled': $bgcolor = get_bgcolor('Away');       break; // Gray
      default:          $bgcolor = get_bgcolor('Full');       break; // Red
    }

    if ($row->Status!='Cancelled')
    {
      $count++;

      $small += $row->Small;
      $medium += $row->Medium;
      $large += $row->Large;
      $xlarge += $row->XLarge;
      $xxlarge += $row->XXLarge;
      $x3large += $row->X3Large;
      //      $x4large += $row->X4Large;
      //      $x5large += $row->X5Large;

      $small_2 += $row->Small_2;
      $medium_2 += $row->Medium_2;
      $large_2 += $row->Large_2;
      $xlarge_2 += $row->XLarge_2;
      $xxlarge_2 += $row->XXLarge_2;
      $x3large_2 += $row->X3Large_2;
      //      $x4large_2 += $row->X4Large_2;
      //      $x5large_2 += $row->X5Large_2;
    }

    echo "  <tr align=\"center\" $bgcolor>\n";
    show_quantity ($row->TShirtID);
    echo "    <td align=\"left\"><a href=mailto:$row->EMail>$row->LastName, $row->FirstName</a></td>\n";
    printf ("    <td align=\"center\"><a href=TShirts.php?action=%d&ID=%d>$row->Status</a></td>\n",
	    SHOW_INDIV_TSHIRT_FORM,
	    $row->TShirtID);
    show_quantity ($row->Small);
    show_quantity ($row->Medium);
    show_quantity ($row->Large);
    show_quantity ($row->XLarge);
    show_quantity ($row->XXLarge);
    show_quantity ($row->X3Large);
    //    show_quantity ($row->X4Large);
    //    show_quantity ($row->X5Large);

    if (SHIRT_TWO_SHIRTS)
    {
      show_quantity ($row->Small_2);
      show_quantity ($row->Medium_2);
      show_quantity ($row->Large_2);
      show_quantity ($row->XLarge_2);
      show_quantity ($row->XXLarge_2);
      show_quantity ($row->X3Large_2);
      //    show_quantity ($row->X4Large_2);
      //    show_quantity ($row->X5Large_2);
    }
    echo "  </tr>\n";
  }

  // Display the summary

  echo "  <tr>\n";
  echo "    <th align=left colspan=3>Total Active Orders: $count</th>\n";
  echo "    <th>$small</th>\n";
  echo "    <th>$medium</th>\n";
  echo "    <th>$large</th>\n";
  echo "    <th>$xlarge</th>\n";
  echo "    <th>$xxlarge</th>\n";
  echo "    <th>$x3large</th>\n";
  //  echo "    <th>$x4large</th>\n";
  //  echo "    <th>$x5large</th>\n";
  if (SHIRT_TWO_SHIRTS)
  {
    echo "    <th>$small_2</th>\n";
    echo "    <th>$medium_2</th>\n";
    echo "    <th>$large_2</th>\n";
    echo "    <th>$xlarge_2</th>\n";
    echo "    <th>$xxlarge_2</th>\n";
    echo "    <th>$x3large_2</th>\n";
    //  echo "    <th>$x4large_2</th>\n";
    //  echo "    <th>$x5large_2</th>\n";
  }
  echo "  </tr>\n";
  echo "</table>\n";
}

function show_indiv_tshirt_form ()
{
  // You need Staff privilege to see this page

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  if (array_key_exists ('ID', $_REQUEST))
    $TShirtID = intval ($_REQUEST['ID']);
  else
    return display_error ('Invalid TShirtID');

  // If the ID is -1, this is a new record

  if (-1 == $TShirtID)
  {
    if (! array_key_exists ('UserId', $_REQUEST))
      return display_error ('Missing UserId');

    $UserId = intval ($_REQUEST['UserId']);

    $sql = 'SELECT FirstName, LastName';
    $sql .= ' FROM Users';
    $sql .= " WHERE UserId=$UserId";

    $result = mysql_query ($sql);

    if (! $result)
      return display_mysql_error ("Query for UserId $UserId failed", $sql);

    if (! $row = mysql_fetch_object ($result))
      return display_error ("No user records fetched for ID $UserId");

    display_header ("Add shirt order for $row->FirstName $row->LastName");

    $_POST['Status'] = 'Unpaid';

    $_POST['Small'] = 0;
    $_POST['Medium'] = 0;
    $_POST['Large'] = 0;
    $_POST['XLarge'] = 0;
    $_POST['XXLarge'] = 0;
    $_POST['X3Large'] = 0;
    $_POST['X4Large'] = 0;
    $_POST['X5Large'] = 0;

    $_POST['Small_2'] = 0;
    $_POST['Medium_2'] = 0;
    $_POST['Large_2'] = 0;
    $_POST['XLarge_2'] = 0;
    $_POST['XXLarge_2'] = 0;
    $_POST['X3Large_2'] = 0;
    $_POST['X4Large_2'] = 0;
    $_POST['X5Large_2'] = 0;

    $_POST['PaymentAmount'] = 0;
    $_POST['PaymentNote'] = '';
  }
  else
  {
    // Get the shirt order record

    $sql = 'SELECT Users.FirstName, Users.LastName, TShirts.*';
    $sql .= ' FROM TShirts, Users';
    $sql .= ' WHERE Users.UserId=TShirts.UserId';
    $sql .= "   AND TShirtID=$TShirtID";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for shirt order', $sql);

    if (! $row = mysql_fetch_object ($result))
      return display_error ("No shirt order records fetched for ID $TShirtID");

    display_header ("Shirt Order ID $TShirtID for $row->FirstName $row->LastName");

    $UserId = $row->UserId;

    if (! array_key_exists ('Status', $_POST))
      $_POST['Status'] = $row->Status;

    if (! array_key_exists ('Small', $_POST))
      $_POST['Small'] = $row->Small;

    if (! array_key_exists ('Medium', $_POST))
      $_POST['Medium'] = $row->Medium;

    if (! array_key_exists ('Large', $_POST))
      $_POST['Large'] = $row->Large;

    if (! array_key_exists ('XLarge', $_POST))
      $_POST['XLarge'] = $row->XLarge;

    if (! array_key_exists ('XXLarge', $_POST))
      $_POST['XXLarge'] = $row->XXLarge;

    if (! array_key_exists ('X3Large', $_POST))
      $_POST['X3Large'] = $row->X3Large;

    if (! array_key_exists ('X4Large', $_POST))
      $_POST['X4Large'] = $row->X4Large;

    if (! array_key_exists ('X5Large', $_POST))
      $_POST['X5Large'] = $row->X5Large;

    if (! array_key_exists ('Small_2', $_POST))
      $_POST['Small_2'] = $row->Small_2;

    if (! array_key_exists ('Medium_2', $_POST))
      $_POST['Medium_2'] = $row->Medium_2;

    if (! array_key_exists ('Large_2', $_POST))
      $_POST['Large_2'] = $row->Large_2;

    if (! array_key_exists ('XLarge_2', $_POST))
      $_POST['XLarge_2'] = $row->XLarge_2;

    if (! array_key_exists ('XXLarge_2', $_POST))
      $_POST['XXLarge_2'] = $row->XXLarge_2;

    if (! array_key_exists ('X3Large_2', $_POST))
      $_POST['X3Large_2'] = $row->X3Large_2;

    if (! array_key_exists ('X4Large_2', $_POST))
      $_POST['X4Large_2'] = $row->X4Large_2;

    if (! array_key_exists ('X5Large_2', $_POST))
      $_POST['X5Large_2'] = $row->X5Large_2;

    if (! array_key_exists ('PaymentAmount', $_POST))
      $_POST['PaymentAmount'] = $row->PaymentAmount;

    if (! array_key_exists ('PaymentNote', $_POST))
      $_POST['PaymentNote'] = $row->PaymentNote;
  }

  echo "<form method=post action=TShirts.php>\n";
  form_add_sequence ();
  printf ("<input type=hidden name=action value=%d>\n",
	  PROCESS_INDIV_TSHIRT_FORM);
  echo "<input type=hidden name=TShirtID value=$TShirtID>\n";
  echo "<input type=hidden name=UserId value=$UserId>\n";

  echo "<table>\n";

  form_section (SHIRT_NAME . ' ordered');
  form_quantity ('Small');
  form_quantity ('Medium');
  form_quantity ('Large');
  form_quantity ('XLarge');
  form_quantity ('XXLarge');
  form_quantity ('X3Large');
  //  form_quantity ('X4Large');
  //  form_quantity ('X5Large');
  hidden_form_text ('X4Large');
  hidden_form_text ('X5Large');

  if (SHIRT_TWO_SHIRTS)
  {
    form_section (SHIRT_2_NAME . ' ordered');
    form_quantity ('Small', 'Small_2');
    form_quantity ('Medium', 'Medium_2');
    form_quantity ('Large', 'Large_2');
    form_quantity ('XLarge', 'XLarge_2');
    form_quantity ('XXLarge', 'XXLarge_2');
    form_quantity ('X3Large', 'X3Large_2');
    //  form_quantity ('X4Large', 'X4Large_2');
    //  form_quantity ('X5Large', 'X5Large_2');
    hidden_form_text ('X4Large_2');
    hidden_form_text ('X5Large_2');
  }

  form_section ('Order status');
  form_status ('Status');
  form_text (3, 'Payment Amount $', 'PaymentAmount');
  form_text (64, 'Payment Note', 'PaymentNote', 128);
  form_submit ('Update');
  echo "</table>\n";
  echo "</form>\n";

  return true;
}

function form_status ($display)
{
  $sel_Unpaid = '';
  $sel_Paid = '';
  $sel_Cancelled = '';
  $sel_Pending = '';

  $key = $display;

  switch ($_POST[$key])
  {
    case 'Unpaid':    $sel_Unpaid = 'selected'; break;
    case 'Paid':      $sel_Paid = 'selected'; break;
    case 'Cancelled': $sel_Cancelled = 'selected'; break;
    case 'Pending':   $sel_Pending = 'selected'; break;
  }

  echo "  <tr>\n";
  echo "    <td>$display</td>\n";
  echo "    <td>\n";
  echo "      <select name=\"$key\" size=\"1\">\n";
  echo "        <option value=\"Unpaid\" $sel_Unpaid>Unpaid</option>\n";
  echo "        <option value=\"Paid\" $sel_Paid>Paid</option>\n";
  echo "        <option value=\"Cancelled\" $sel_Cancelled>Cancelled</option>\n";
  //  echo "        <option value=\"Pending\" $sel_Pending>Pending</option>\n";
  echo "      </select>\n";
  echo "    </td>\n";
  echo "  </tr>\n";
}

/*
 * form_quantity
 *
 * Add a text input field
 */

function form_quantity ($display, $key='')
{
  // If not specified, fill in default values

  if ('' == $key)
    $key = $display;

  // If magic quotes are on, strip off the slashes

  if (! array_key_exists ($key, $_POST))
    $text = '';
  else
  {
    if (1 == get_magic_quotes_gpc())
      $text = stripslashes ($_POST[$key]);
    else
      $text = $_POST[$key];
  }

  // Spit out the HTML

  echo "  <tr>\n";
  echo "    <td>$display</td>\n";
  echo "    <td>\n";
  printf ("      <input type=\"text\" name=\"$key\" size=\"2\" maxlength=\"2\" value=\"%s\">\n",
	  htmlspecialchars ($text));
  echo "    </td>\n";
  echo "  </tr>\n";
}

function process_indiv_tshirt_form()
{
  dump_array ('POST', $_POST);
  // Make sure the user hasn't used the back key

  if (out_of_sequence())
    return display_sequence_error(false);

  // Validate our data

  $ok = validate_quantity ('Small');
  $ok &= validate_quantity ('Medium');
  $ok &= validate_quantity ('Large');
  $ok &= validate_quantity ('XLarge');
  $ok &= validate_quantity ('XXLarge');
  $ok &= validate_quantity ('X3Large');
  $ok &= validate_quantity ('X4Large');
  $ok &= validate_quantity ('X5Large');

  $ok = validate_quantity ('Small_2');
  $ok &= validate_quantity ('Medium_2');
  $ok &= validate_quantity ('Large_2');
  $ok &= validate_quantity ('XLarge_2');
  $ok &= validate_quantity ('XXLarge_2');
  $ok &= validate_quantity ('X3Large_2');
  $ok &= validate_quantity ('X4Large_2');
  $ok &= validate_quantity ('X5Large_2');

  if (! $ok)
    return false;

  // Extract the TShirtID

  $TShirtID = 0;
  if (array_key_exists ('TShirtID', $_POST))
    $TShirtID = intval($_POST['TShirtID']);

  if (0 == $TShirtID)
    return display_error ('Invalid TShirtID');

  // Extract the UserId

  $UserId = 0;
  if (array_key_exists ('UserId', $_POST))
    $UserId = intval($_POST['UserId']);

  if (0 == $UserId)
    return display_error ('Invalid UserId');

  $Status = '';
  if (array_key_exists ('Status', $_POST))
    $Status = $_POST['Status'];

  if (('Unpaid' != $Status) && ('Paid' != $Status) && ('Cancelled' != $Status))
    return display_error ('Invalid status');


  $PaymentNote = '';
  if (array_key_exists ('PaymentNote', $_POST))
    $PaymentNote = $_POST['PaymentNote'];


  $PaymentAmount = 0;
  if (array_key_exists ('PaymentAmount', $_POST))
    $PaymentAmount = intval($_POST['PaymentAmount']) * 100;

  if (0 != ($PaymentAmount % (TSHIRT_DOLLARS * 100)))
    return display_error ('Invalid payment amount - must be a multiple of $' .
			  TSHIRT_DOLLARS);

  if (-1 == $TShirtID)
    $sql = 'INSERT TShirts SET ';
  else
    $sql = 'UPDATE TShirts SET ';

  $sql .= build_sql_string ('Status', '', false);
  $sql .= build_sql_string ('Small');
  $sql .= build_sql_string ('Medium');
  $sql .= build_sql_string ('Large');
  $sql .= build_sql_string ('XLarge');
  $sql .= build_sql_string ('XXLarge');
  $sql .= build_sql_string ('X3Large');
  $sql .= build_sql_string ('X4Large');
  $sql .= build_sql_string ('X5Large');
  $sql .= build_sql_string ('Small_2');
  $sql .= build_sql_string ('Medium_2');
  $sql .= build_sql_string ('Large_2');
  $sql .= build_sql_string ('XLarge_2');
  $sql .= build_sql_string ('XXLarge_2');
  $sql .= build_sql_string ('X3Large_2');
  $sql .= build_sql_string ('X4Large_2');
  $sql .= build_sql_string ('X5Large_2');
  $sql .= build_sql_string ('PaymentNote');
  $sql .= build_sql_string ('PaymentAmount', $PaymentAmount);
  if (-1 == $TShirtID)
    $sql .= build_sql_string ('UserId');
  $sql .= ', LastUpdated=NULL';

  if (-1 != $TShirtID)
    $sql .= " WHERE TShirtID=$TShirtID";

  //  echo "SQL: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Failed to update TShirt record', $sql);

  return true;
}

function shirt_table_entry ($file, $caption)
{
  echo "    <td align=\"center\" valign=\"top\">\n";
  echo "      <img src=\"$file\" border=0><br>\n";
  echo "      <b>$caption</b>\n";
  echo "    </td>\n";
}

function show_shirts()
{
  if (! SHIRT_IMG_AVAILABLE)
    return false;

  if (! SHIRT_TWO_SHIRTS)
  {
    echo "<img src=\"shirt.gif\" border=0>\n";
    return true;
  }

  echo "<table width=\"100%\">\n";
  echo "  <tr>\n";
  shirt_table_entry ("shirt.gif", SHIRT_NAME);
  shirt_table_entry ("shirt2.gif", SHIRT_2_NAME);
  echo "  </tr>\n";
  echo "</table>\n";

  return true;
}

/*
 * select_user_to_sell_shirt
 *
 * Display the list of users and let the staff member select one to to
 * record a shirt sale for
 */

function select_user_to_sell_shirt ()
{
  // Make sure that only users with Registrar priv view this page

  if (! user_has_priv (PRIV_REGISTRAR))
    return display_access_error ();

  // There are no highlit users in this display, so just pass an empty array

  $highlight = array ();

  $link = sprintf ('TShirts.php?action=%d&Seq=%d&ID=-1',
		   SHOW_INDIV_TSHIRT_FORM,
		   increment_sequence_number());

  // Display the form to allow the user to include the alumni in the list
  // of users to choose from and allow them to select one

  $alumni = include_alumni_form ('index.php', SELECT_USER_TO_SELL_SHIRT);

  select_user ('Select User To Add Shirt Sale For',
	       $link,
	       false,
	       TRUE,
	       $highlight,
	       0 == $alumni);
}

?>
