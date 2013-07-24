<?php
include ("sharedBidding.php");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = BAG_BID_PAGE;

switch ($action)
{
  case BAG_BID_PAGE:
    display_bid_intro ("Conference");
    break;
  case BAG_PANEL_PAGE:
    display_bid_intro ("Conference");
    break;
  case BAG_ACT_PAGE:
    display_bid_intro ("Show");
    break;

  case BAG_SHOW_FORM:
    show_bidinfo_form();
    break;

  case BAG_UPDATE:
    if (update_bidinfo())
      display_bid_intro();
    else
      show_bidinfo_form();
    break;

  default:
    display_error ("Unknown action code: $action");
}


// Add the postamble

html_end ();




function display_bid_intro ($area)
{
  echo "<table cellspacing=\"2\" cellPadding=\"2\" width=\"100%\" border=\"0\">\n";
  echo "  <tr>\n";
  echo "    <td width=\"60%\" valign=\"top\">\n";
  echo "      <h3>Applying to ".CON_NAME." ".$area."</h3>\n";

  if (file_exists(TEXT_DIR.'/'.$area.'bidding1.html'))
	include(TEXT_DIR.'/'.$area.'bidding1.html');	
  
  if (user_has_priv (PRIV_SCHEDULING))
  {
    printf ("<p>[<a href=\"biddingAGame.php?action=%d\">Edit Bid Deadline Info</a>]</p>\n",
	    BAG_SHOW_FORM);
  }

  show_bid_schedule();

 if (file_exists(TEXT_DIR.'/'.$area.'bidding2.html'))
	include(TEXT_DIR.'/'.$area.'bidding2.html');	

  echo "<p>\n";
  if (! isset ($_SESSION[SESSION_LOGIN_USER_ID]))
    $dest = 'index.php?action=' . PROMPT_FOR_LOGIN . '&dest=Bids.php';
  else
    $dest = 'Bids.php';

  echo "<div align=center>\n";
  if ( $area == "Conference")
  {
      echo "<a href=$dest?GameType=Class&Seq=41&action=50><img src=submitClass.gif alt=\"Submit Class\" border=0></a>\n";
      echo "<a href=$dest?GameType=Panel&Seq=41&action=50><img src=submitPanel.gif alt=\"Submit Panel\" border=0></a>\n";

  }
  else if ($area == "Show")
  {
  		$dest = "Acts.php";
        echo "<a href=$dest?GameType=Performance&Seq=41&action=50><img src=submitAct.gif alt=\"Submit Act\" border=0></a>\n";
  }
  else
    echo "<a href=$dest><img src=IWantToBid.gif width=115 height=27 alt=\"I Want To BID!\" border=0></a>\n";
  echo "</div>\n";

  echo "<p>\n";
  echo "Please fill out the submission form with <i>as much information as\n";
  echo "possible</i>. This is the <i>best</i> way to help us evaluate your\n";
  echo "class or panel quickly!</p>\n";
  echo "<p>\n";
  
  if ( $area == "Show")
  {
      printf ("If you have <i>any</i> questions, please contact %s, our\n",
	  NAME_SHOW_CHAIR);
    printf ("Performance Selection Chair at %s\n",
	  mailto_or_obfuscated_email_address (EMAIL_SHOW_CHAIR));
  }
  else
  {
    printf ("If you have <i>any</i> questions, please contact %s, our\n",
	  NAME_BID_CHAIR);
    printf ("Teacher Coordinator at %s\n",
	  mailto_or_obfuscated_email_address (EMAIL_BID_CHAIR));
  }
  echo "</p>\n";
  echo "</td>\n";

  echo "<td valign=\"top\" width=\"40%\">\n";
  show_bid_faq();

  echo "</td></tr></table>\n";
}



function show_bidinfo_form()
{
  // Accessing this form requires Scheduling priv

  if (! user_has_priv (PRIV_SCHEDULING))
    return display_access_error ();

  // Query the database for the bid info

  $sql = 'SELECT * FROM BidInfo';

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Error querying Bid Info', $sql);

  // There may be no data - use defaults

  if (0 != mysql_num_rows ($result))
  {
    // Fill the $_POST array from the object

    $row = mysql_fetch_object ($result);
    foreach ($row as $key => $value)
      $_POST[$key] = $value;
  }
  else
  {
    $year = strftime ('%Y');
    $_POST['FirstBid'] = "October 24, $year";
    $_POST['FirstDecision'] = "October 28, $year";
    $_POST['SecondBid'] = "December 15, $year";
    $_POST['SecondDecision'] = "December 24, $year";
    $_POST['ThirdBid'] = '';
    $_POST['ThirdDecision'] = '';
    $_POST['BidInfo'] =
      "<p>\n" .
      CON_NAME . "solicits bids for games in rounds, as needed, based on the\n" .
      "number of registrants we get. It's our goal to have a great schedule\n".
      "of games up as  early as possible!</p>\n" .
      "<p>\n" .
      "Game bids received before the deadline will be evaluated in a timely\n".
      "manner.\n" .
      "<i>Early bids will get an early decision!</i></p>";
    $_POST['UpdatedById'] = 0;
  }

  dump_array ('POST', $_POST);

  echo "<form method=\"post\" action=\"biddingAGame.php\">\n";
  form_add_sequence ();
  echo '<input type="hidden" name="action" value=' . BAG_UPDATE . ">\n";
  printf ("<input type=\"hidden\" name=\"UpdatedById\" value=%d>\n",
	  $_POST['UpdatedById']);

  echo "<table>\n";

  form_section ('Bid Dates', FALSE);
  echo "  <tr>\n";
  echo "    <td colspan=\"2\">\n";
  echo "      <table width=\"100%\">\n";
  echo "        <tr>\n";
  echo "          <th>&nbsp;</th>\n";
  echo "          <th align=\"left\">Submission Deadline</th>\n";
  echo "          <th align=\"left\">Decision Reached By</th>\n";
  echo "        </tr>\n";
  bidinfo_line ('First', 'FirstBid', 'FirstDecision');
  bidinfo_line ('Second', 'SecondBid', 'SecondDecision');
  bidinfo_line ('Third', 'ThirdBid', 'ThirdDecision');
  echo "      </table>\n";
  echo "<p>Leave Deadline & Decision blank to hide entry in bid dates table</p>\n";
  echo "    </td>\n";
  echo "  </tr>\n";

  form_section ('Bid News');
  form_textarea ('Use HTML to format', 'BidInfo', 10);

  form_submit ('Update now');

  echo "</table>\n";

  echo "</form>\n";

  // If we've got an UpdatedBy UserId, show who's been mucking with the
  // bid info

  if (0 != $_POST['UpdatedById'])
  {
    $sql = 'SELECT FirstName, LastName FROM Users';
    $sql .= sprintf (' WHERE UserId=%d', $_POST['UpdatedById']);
    $result = mysql_query($sql);
    if ($result)
    {
      $row = mysql_fetch_object($result);
      if ($row)
	printf ("<p>Last updated %s by $row->FirstName $row->LastName</p>\n",
		$_POST['LastUpdated']);
    }
  }
}

function bidinfo_line ($name, $bid, $decision)
{
  echo "        <tr>\n";
  echo "          <th>$name</th>\n";
  bidinfo_text (32, $bid);
  bidinfo_text (32, $decision);
  echo "        </tr>\n";
}
/*
 * bidinfo_text
 *
 * Add a text input field to a 2 column form
 */

function bidinfo_text ($size, $key, $maxsize=32)
{
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

  printf ("    <td align=\"left\"><input type=\"text\" name=\"%s\" size=%d maxlength=%d value=\"%s\"></td>\n",
	  $key,
	  $size,
	  $maxsize,
	  htmlspecialchars ($text));
}

function update_bidinfo()
{
  // Updating this data requires Scheduling priv

  if (! user_has_priv (PRIV_SCHEDULING))
    return display_access_error ();

  // If we're out of sequence, don't do anything

  if (out_of_sequence ())
    return display_sequence_error (false);

  if (0 == $_POST['UpdatedById'])
    $sql = 'INSERT BidInfo SET ';
  else
    $sql = 'UPDATE BidInfo SET ';
  $sql .= build_sql_string ('FirstBid', '', false);
  $sql .= build_sql_string ('FirstDecision');
  $sql .= build_sql_string ('SecondBid');
  $sql .= build_sql_string ('SecondDecision');
  $sql .= build_sql_string ('ThirdBid');
  $sql .= build_sql_string ('ThirdDecision');
  $sql .= build_sql_string ('BidInfo');
  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID]);

  $result = mysql_query($sql);
  if (! $result)
    display_mysql_error ('Failed to update BidInfo table', $sql);

  return true;
}

?>