<?php
include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

function bidfaq_link ($hash, $text)
{
  printf ("<p><a href=\"Static.php?page=bidFAQ#%s\">%s</a></p>\n",
	  $hash,
	  $text);
}

function static_link ($page, $text)
{
  echo "<p><a href=\"Static.php?page=$page\">$text</a></p>\n"; 
}

function show_bid_schedule()
{
  echo "      <h3><a name=\"deadlines\">Bid Deadlines</a></h3>\n";

  $sql = 'SELECT * FROM BidInfo';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Error querying Bid Info', $sql);

  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ('Submission Deadlines coming soon!');


  echo "$row->BidInfo\n";

  if (('' != $row->FirstBid) && ('' != $row->FirstDecision))
  {
    echo "<div align=\"center\">\n";
    echo "<table cellspacing=\"2\" cellpadding=\"2\" bgcolor=\"#4B067A\">\n";
    echo "<tr bgcolor=\"#cc99ff\" align=\"center\">\n";
    echo "<th>Round</th>\n";
    echo "<th>Bid Deadline</th>\n";
    echo "<th>Decision Date</th></tr>\n";
    echo "<tr bgColor=\"white\" vAlign=\"bottom\" align=\"center\">\n";
    echo "<th>First</th>\n";
    echo "<td>&nbsp;$row->FirstBid&nbsp;</td>\n";
    echo "<td>&nbsp;$row->FirstDecision&nbsp;</td>\n";
    echo "</tr>\n";
    if (('' != $row->SecondBid) && ('' != $row->SecondDecision))
    {
      echo "<tr bgColor=\"white\" valign=\"bottom\" align=\"center\">\n";
      echo "<th>Second</th>\n";
      echo "<td>&nbsp;$row->SecondBid&nbsp;</td>\n";
      echo "<td>&nbsp;$row->SecondDecision&nbsp;</td>\n";
      echo "</tr>\n";
      if (('' != $row->ThirdBid) && ('' != $row->ThirdDecision))
      {
	echo "<tr bgColor=\"white\" valign=\"bottom\" align=\"center\">\n";
	echo "<th>Third</th>\n";
	echo "<td>&nbsp;$row->ThirdBid&nbsp;</td>\n";
	echo "<td>&nbsp;$row->ThirdDecision&nbsp;</td>\n";
	echo "</tr>\n";
      }
    }
    echo "</table>\n";
    echo "</div>\n";
    if (('' != $row->ThirdBid) && ('' != $row->ThirdDecision))
    {
      echo "<p>\n";
    }
  }

}

function show_bid_faq()
{
  echo "<table width=\"100%\" cellpadding=\"2\" cellspacing=\"2\" bgcolor=\"#4B067A\">\n";
  echo "<tr>\n";
  echo "<td bgcolor=\"white\">\n";
  echo "<h3>Questions?</h3>\n";
  bidfaq_link ('gamekind', 'What kind of events are you looking for?');
  bidfaq_link ('audience', "What kind of attendees come to ".CON_NAME."?");
  echo "<p><A href=\"#deadlines\">When do I have to get my bid in?</a></p>\n";
  static_link ('bidFollowup', 'What happens when I submit my bid?');
  bidfaq_link ('', 'Other Frequently Asked Questions About Bidding');

  echo "<h3>What do I have to know if I become a teacher or panelist?</h3>\n";
  static_link ('GMPolicies', 'GBE Policies and Services');
  echo "</td></tr></table>\n";

  echo "<table cellspacing=\"2\" cellpadding=\"2\" bgcolor=\"#4B067A\">\n";
  echo "<tr bgcolor=\"white\">\n";
  echo "<td>\n";
    if (file_exists(TEXT_DIR.'/bidearly.html'))
	include(TEXT_DIR.'/bidearly.html');	
  echo "</td></tr></table>\n";
}



?>