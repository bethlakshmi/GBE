<?php
include ("sharedBidding.php");
include ("files.php");
//include ("display_common.php");
include ("gbe_ticketing.inc");
include ("gbe_brownpaper.inc");

global $VIDEO_LIST;
$VIDEO_LIST = array('I don\'t have any video of myself performing', 
                 'This is video of me but not the act I\'m submitting', 
                 'This is video of the act I would like to perform');

$submitFilter = ' AND Bids.GameType = \'Performance\' ';

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

// Figure out what we''re supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action=$_REQUEST['action'];

if (empty ($action))
    $action = BID_GAME;

switch ($action)
{
  case BID_GAME:
    display_bid_form (TRUE);
    break;

  case BID_PROCESS_FORM:
    if (! process_bid_form ($ActIsPaidFor))
      display_bid_form (FALSE);
    else
      display_bid_etc ($ActIsPaidFor);
    break;

  case BID_REVIEW_BIDS:
    display_bids_for_review ();
    break;

  case BID_CHANGE_STATUS:
    change_bid_status ();
    break;

  case BID_PROCESS_STATUS_CHANGE:
    if (! process_status_change ())
      change_bid_status ();
    else
      display_bids_for_review ();
    break;

  case BID_SHOW_BID:
    show_bid ();
    break;

  case BID_FEEDBACK_SUMMARY:
    show_bid_feedback_summary();
    break;

  case BID_FEEDBACK_BY_GAME:
    update_feedback_by_game ();
    break;

  case BID_PROCESS_FEEDBACK_BY_GAME:
    if (! process_feedback_by_game ())
      update_feedback_by_game ();
    else
      show_bid_feedback_summary ();
    break;

  case BID_FEEDBACK_BY_ENTRY:
    show_bid_feedback_entry_form();
    break;

  case BID_FEEDBACK_PROCESS_ENTRY:
    if (! process_feedback_for_entry())
      show_bid_feedback_entry_form();
    else
      show_bid_feedback_summary();
    break;

  case BID_FEEDBACK_BY_CONCOM:
    show_bid_feedback_by_user_form();
    break;

  case BID_FEEDBACK_PROCESS_BY_CONCOM:
    if (! process_feedback_for_user())
      show_bid_feedback_by_user_form();
    else
      show_bid_feedback_summary();
    break;

  default:
    display_error ("Unknown action code: $action");
}

// Add the postamble

html_end ();



/*
 * form_bid_consensus
 *
 * Display the bid committee consensus selections for the user and let him
 * modify them.  If a value has already been chosen, set the selected value
 * to it
 */

function form_bid_consensus ($key, $display='')
{
  if ('' == $display)
    $display = $key . ':';

  if (! isset ($_POST[$key]))
    $value = 'Discuss';
  else
  {
    $value = trim ($_POST[$key]);
    if (1 == get_magic_quotes_gpc())
      $value = stripslashes ($value);
  }

  $early = '';
  $accept = '';
  $discuss = '';
  $reject = '';
  $drop = '';

  switch ($value)
  {
    case 'Accept':            $accept  = 'selected'; break;
    case 'Early Accepted':    $early   = 'selected'; break;
    case 'Discuss':           $discuss = 'selected'; break;
    case 'Reject':            $reject  = 'selected'; break;
    case 'Drop':              $drop    = 'selected'; break;
  }

  echo "  <TR>\n";
  echo "    <TD ALIGN=RIGHT>$display</TD>\n";
  echo "    <TD>\n";
  echo "      <SELECT NAME=$key SIZE=1>\n";
  echo "        <option value=Discuss $discuss>Discuss It</option>\n";
  echo "        <option value=Accept $accept>Accept It</option>\n";
  echo "        <option value=\"Early Accepted\" $early>Early Accepted It&nbsp;&nbsp;</option>\n";
  echo "        <option value=Reject $reject>Reject It</option>\n";
  echo "        <option value=Drop $drop>Drop It</option>\n";
  echo "      </SELECT>\n";
  echo "    </TD>\n";
  echo "  </tr>   \n";
}



/*
 * show_bid
 *
 * Display information about a bid in a read-only format
 */

function show_bid ()
{
  // Only bid committe members, the bid chair and the GM Liaison may access
  // this page

  if ((! user_has_priv (PRIV_SHOW_COM)) &&
      (! user_has_priv (PRIV_SHOW_CHAIR)) &&
      (! user_has_priv (PRIV_GM_LIAISON)))
    return display_access_error ();

  $BidId = intval (trim ($_REQUEST['BidId']));

  $sql = 'SELECT * FROM Bids WHERE BidId=' . $BidId;
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for BidId $BidId failed");

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find bid $BidId");

  $bid_row = mysql_fetch_assoc ($result);

  // If the UserId is valid use that to override any user information

  $UserId = $bid_row['UserId'];
  if (0 != $UserId)
  {
    $sql = 'SELECT * FROM Users WHERE UserId=' . $UserId;
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Query for UserId $UserId failed");

    if (0 == mysql_num_rows ($result))
      return display_error ("Failed to find user $UserId");

    $user_row = mysql_fetch_assoc ($result);
    foreach ($user_row as $key => $value)
      $bid_row[$key] = $value;
  }

  // If the EventId is valid, use that to override any game information

  $EventId = $bid_row['EventId'];
  if (0 != $EventId)
  {
    $sql = 'SELECT * FROM Events WHERE EventId=' . $EventId;
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Query for EventId $EventId failed");

    if (0 == mysql_num_rows ($result))
      return display_error ("Failed to find event $EventId");

    $event_row = mysql_fetch_assoc ($result);
    foreach ($event_row as $key => $value)
      $bid_row[$key] = $value;
  }

  $bid_pref_slots = array();
  get_preferred_shows($BidId, &$bid_pref_slots);
  
  echo "<TABLE BORDER=0 WIDTH=\"100%\">\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD>\n";
  printf ("      <FONT SIZE=\"+2\"><B>%s</B></FONT>\n", $bid_row['Title']);
  echo "    </TD>\n";

  $sql = "SELECT * FROM BidChoice WHERE BidId=".$BidId.";";
  $ahresult = mysql_query ($sql);
  if (! $ahresult)
		return display_mysql_error ("BidChoice query failed for BidId ".$row->BidId);

  $BidChoice = array();
  while ($ahrow = mysql_fetch_array ($ahresult, MYSQL_ASSOC))
	{
		$key = $ahrow['Question'];
		$BidChoice[$key] = mysql_real_escape_string ($ahrow['Answer']);
	}

  // Bid chair & GM Liaison can edit bids

  if (user_has_priv (PRIV_SHOW_CHAIR) || user_has_priv (PRIV_GM_LIAISON))
  {
    echo "    <TD>\n";
    printf ('      [<A HREF=Acts.php?action=%d&BidId=%d>Edit Bid</A>]',
	    BID_GAME,
	    $BidId);
    echo "    </TD>\n";
  }
  
  echo "  </tr>\n";
  echo "</TABLE>\n";

  echo "<TABLE BORDER=0>\n";

  show_section ('Submitter Information');

  show_text ('Submitter',
 		     $bid_row['DisplayName']);

/*		     
  echo "<TD rowspan=\"5\">";
  echo "<img src=\"http://www.firstladyofburlesque.com/wordpress/wp-content/uploads/2013/06/burlesque2.jpg\" width=\"200\">";
  echo "<\TD>";
*/
  $text = $bid_row['Address1'];
  if ('' != $bid_row['Address2'])
    $text .= '<BR>' . $bid_row['Address2'];
  $text .= '<BR>' . $bid_row['City'] . ', ' . $bid_row['State'] . '  ' . $bid_row['Zipcode'];
  if ('' != $bid_row['Country'])
    $text .= '<BR>' . $bid_row['Country'];

  //show_text ('Address', $text);
  show_text ('EMail', $bid_row['EMail']);
  show_text ('Daytime Phone', $bid_row['DayPhone']);
  show_text ('Evening Phone', $bid_row['EvePhone']);
  show_text ('Best Time To Call', $bid_row['BestTime']);
  show_text ('Preferred Contact', $bid_row['PreferredContact']);

  $gametype = $bid_row['GameType'];

  show_section ("$gametype Information");


  show_text ('Performance)', $bid_row['Title']);
  show_text ('Stage Name/Troupe', $bid_row['Organization']);
  show_text ('Fellow Performer(s)', $bid_row['GMs']);
  show_text ('Is this a Troupe?', $bid_row['MultipleRuns']);
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD ALIGN=RIGHT NOWRAP><B>Website:</B></TD><TD ALIGN=LEFT><a href=\"http://{$bid_row['Homepage']}\">{$bid_row['Homepage']}</a></TD>\n";
  echo "  </tr>\n";
  show_text ('POC EMail', $bid_row['GameEMail']);

  //show_text ('Run Before', $bid_row['RunBefore']);
  //show_text ('Space Requirements', $bid_row['SpaceRequirements']);

  show_section ('About Performer');
  
  show_text ('Experience', $bid_row['RunBefore']);

  global $OTHER_SHOWS;
  global $PARTICIPATION;

  
  show_text("Other show experience","");
  foreach ($OTHER_SHOWS as $show)
    show_text ($show, $BidChoice[$show]);
  
  display_show_pref($bid_pref_slots);
  
  show_text ('Performer/Troupe History', $bid_row['Premise']);

  show_section ('About Act');

  show_text ('Song Title', $bid_row['GameSystem']);
  show_text ('Artist', $bid_row['OtherDetails']);
  show_text ('Length', $bid_row['Minutes'].":".$bid_row['Seconds']);
  show_text ('Description', $bid_row['Description']);
  show_text ('Short Blurb', $bid_row['ShortBlurb']);
  show_text ('Video of', $bid_row['VideoOf']);
  display_media($bid_row['PhotoSource'], $bid_row['VideoSource']);
  
  show_section("Participation");
  foreach ($PARTICIPATION as $item)
    show_text ($item, $BidChoice[$item]);
    
  bid_involve($UserId, $bid_row['BidId']);
  
  show_section ('Advertising Information');

  show_text ('Short Sentence', $bid_row['ShortSentence']);
  show_text ('Short Blurb', $bid_row['ShortBlurb']);
  show_text ('Description', $bid_row['Description']);
  show_text ('Shameless Plugs', $bid_row['ShamelessPlugs']);
  //show_text ('GM Advertise Game', $bid_row['GMGameAdvertising']);
  //show_text ('GM Advertise Intercon', $bid_row['GMInterconAdvertising']);
  show_text ('Send Flyers', $bid_row['SendFlyers']);

  echo "</TABLE>\n";
  echo "<P>\n";
}

 /*
 * display_show_pref
 *
 * Display the show preferences for this bid as a table
 */

 function display_show_pref (&$bid_pref_slots)

  {
    global $SHOW_DAYS;
    global $SHOW_SLOTS;
    global $SHOW_NAMES;

    echo "  <TR VALIGN=TOP>\n";
    echo "    <TD ALIGN=RIGHT><br><br><B>Preferred Shows:</B></TD>\n";
    echo "    <TD>\n";
    echo "      <TABLE BORDER=1>\n";
    echo "        <TR ALIGN=CENTER>\n";
    foreach ($SHOW_DAYS as $day)
  	  echo "          <TD COLSPAN=".count($SHOW_SLOTS[$day]).">{$day}</TD>\n";
    echo "        </tr>\n";
    echo "        <TR ALIGN=CENTER>\n";
    foreach ($SHOW_DAYS as $day)
  	  foreach ($SHOW_SLOTS[$day] as $slot)
  		echo "          <TD>".$SHOW_NAMES[$day."_".$slot]."</TD>\n";
    echo "        </tr>\n";
    echo "        <TR ALIGN=CENTER>\n";
    foreach ($SHOW_DAYS as $day)
  	  foreach ($SHOW_SLOTS[$day] as $slot)
  		if (isset($bid_pref_slots[$day.$slot]))
  			show_table_entry ($bid_pref_slots[$day.$slot]);
  		else
  			show_table_entry ('&nbsp;');
    echo "        </tr>\n";
    echo "      </TABLE>\n";
    echo "    </TD>\n";
    echo "  </tr>\n";
  }

/*
 * display_bid_form
 *
 * Display the form the user has to fill out to bid a game
 */

function display_bid_form ($first_try)
{
  $EditGameInfo = 1;
  
  global $BID_TYPES;
  global $OTHER_SHOWS;
  global $ANSWER_SET;
  global $PARTICIPATE_SET;
  global $PARTICIPATION;

  global $SHOW_DAYS;
  global $SHOW_SLOTS;
  global $SHOW_NAMES;

  $BidChoice = array();

  foreach ($OTHER_SHOWS as $show) 
      $BidChoice[$show] = $ANSWER_SET[1];
  foreach ($PARTICIPATION as $part) 
      $BidChoice[$part] = $PARTICIPATE_SET[2];


  if (array_key_exists ('GameType', $_REQUEST))
      $gametype=$_REQUEST['GameType'];
  else
      $gametype = 'Performance';


  if (file_exists(TEXT_DIR.'/actinstruct.html'))
	include(TEXT_DIR.'/actinstruct.html');	
  
  


  // Make sure that the user is logged in

  if (! isset ($_SESSION[SESSION_LOGIN_USER_ID]))
    return display_error ('You must login before submitting a bid');

  // If we're updating a bid, grab the bid ID

  if (empty ($_REQUEST['BidId']))
    $BidId = 0;
  else
    $BidId = intval (trim ($_REQUEST['BidId']));
  



  // Output the note about comps, so nobody can say that they didn't
  // see it
  if (BID_SHOW_COMPS)
  {
	  echo "<div style=\"border-style: solid; border-color: red; padding: 1ex; margin-bottom: 2ex\">\n";
	  echo "<b>Important Note:</b><br>\n";
	  echo "The policy of the " . CON_NAME . " has changed this year. \n";
	  echo "This year, time spent assisting the expo during run time as a teacher, \n";
	  echo "panelist, perfomer, or other volunteer will be taken into consideration \n";
	  echo "for 2015 discounts.  After the " . CON_SHORT_NAME . " has concluded, you ";
	  echo "can expect to hear the about 2015 discounts as part of our thank yous. \n";
	  echo "</div>\n";
  }

  echo "<br><br>\n";

  show_user_homepage_bids ($_SESSION[SESSION_LOGIN_USER_ID],"Performance");

  echo "<br><br>\n";
  
  // If this is an existing bid, fetch the data
  if (0 != $BidId)
  {
    // If this is the first try, and we're updating an existing bid,
    // load the $_POST array from the database

    if ($first_try)
    {
      $sql = "SELECT * FROM Bids WHERE BidId=$BidId";
      $result = mysql_query ($sql);
      if (! $result)
		return display_mysql_error ("Query failed for BidId $BidId");

      if (0 == mysql_num_rows ($result))
		return display_error ("Failed to find BidId $BidId");

      if (1 != mysql_num_rows ($result))
		return display_error ("Found multiple entries for BidId $BidId");

      $row = mysql_fetch_array ($result, MYSQL_ASSOC);

      foreach ($row as $key => $value)
      {
        /*
        if (1 == get_magic_quotes_gpc())
          $_POST[$key] = mysql_real_escape_string ($value);
        else
        */
          $_POST[$key] = $value;
      }
      $EventId = $row['EventId'];

      //Also get the bid slot data.
      $sql = "SELECT * FROM BidTimes WHERE BidId=$BidId;";
      $result = mysql_query ($sql);
      if (! $result)
		return display_mysql_error ("BidTimes query failed for BidId $BidId");

	  while ($row = mysql_fetch_array ($result, MYSQL_ASSOC))
	  {
	  	 $key = $row['Day'].'_'.$row['Slot'];
  		 $key = str_replace ( ' ' , '_' , $key );
		 $_POST[$key] = mysql_real_escape_string ($row['Pref']);
	  }
	  mysql_free_result ($result);


      //Get Bid Choices
	  $sql = "SELECT * FROM BidChoice WHERE BidId=".$BidId.";";
	  $ahresult = mysql_query ($sql);
	  if (! $ahresult)
		return display_mysql_error ("BidChoice query failed for BidId ".$row->BidId);

	  while ($ahrow = mysql_fetch_array ($ahresult, MYSQL_ASSOC))
	  {
		$key = $ahrow['Question'];
		$BidChoice[$key] = mysql_real_escape_string ($ahrow['Answer']);
	  }
	  mysql_free_result ($ahresult);

      // If the user or game IDs are in the record, then have the user
      // modify them using the Edit User or Edit Game links


      if (0 == $EventId)
	    $EditGameInfo = 1;
      else
	    $EditGameInfo = 0;
      //      $EditGameInfo = (0 == $EventId);
    }
    else
    {
      $sql = "SELECT VideoSource, PhotoSource, UserId FROM Bids WHERE BidId=$BidId;";
      $result = mysql_query ($sql);
      if (! $result)
		return display_mysql_error ("Query failed for BidId $BidId");

      if (0 == mysql_num_rows ($result))
		return display_error ("Failed to find BidId $BidId");

      if (1 != mysql_num_rows ($result))
		return display_error ("Found multiple entries for BidId $BidId");

      $row = mysql_fetch_array ($result, MYSQL_ASSOC);

      foreach ($row as $key => $value)
      {
        if (1 == get_magic_quotes_gpc())
          $_POST[$key] = mysql_real_escape_string ($value);
        else
          $_POST[$key] = $value;
      }
      
      
    }

    // Only the Chair, GM Liaison or the bidder can update this bid

    $can_update =
      user_has_priv (PRIV_SHOW_CHAIR) ||
      user_has_priv (PRIV_GM_LIAISON) ||
      ($_SESSION[SESSION_LOGIN_USER_ID] == $_POST['UserId']);

    if (! $can_update)
    {
      //echo "user: ".$_SESSION[SESSION_LOGIN_USER_ID];
      //echo "poster: ".$_POST['UserId'];
      return display_access_error ();
     }
  }



  // Show the header - varies depending on update/submit and the nature of the submission
  if (0 == $BidId)
  {
    $_POST['Author'] =   $_SESSION[SESSION_LOGIN_USER_DISPLAY_NAME];
    $_POST['GameEMail'] = $_SESSION[SESSION_LOGIN_USER_EMAIL];

    if ($gametype == 'Other')
        display_header ("Submit an event for " . CON_NAME);
    else
        display_header ("Submit a {$gametype} for " . CON_NAME);
  }
  else
    display_header ('Update information for <I>' . stripslashes($_POST['Title']) . '</I>');


  echo "<form method=\"POST\" action=\"Acts.php\" enctype=\"multipart/form-data\">\n";
  form_add_sequence ();
  form_hidden_value ('action', BID_PROCESS_FORM);
  form_hidden_value ('BidId', $BidId);
  form_hidden_value ('EditGameInfo', $EditGameInfo);


  echo "<p><font color=red>*</font> indicates a required field\n";
  echo "<TABLE BORDER=0>\n";

  $thingstring = strtolower($gametype);
  if ($gametype == 'Other')
        $thingstring = 'event';
    

  $maininfo .= "Contact Information";
  form_section ($maininfo);

  
  form_hidden_value ('GameType', $gametype);
  
  if (! $EditGameInfo)
  {
    form_hidden_value ('Title', $_POST['Title']);
    echo "  <tr>\n";
    echo "    <td colspan=\"2\">\n";
    echo "The event has been accepted and is already in the Events table.\n";
    printf ("Click <a href=\"Schedule.php?action=%d&EventId=%d\" target=_blank>here</a>",
	    EDIT_GAME,
	    $EventId);
    echo " if you want to modify the event information.\n";
    echo "    </td>\n";
    echo "  </tr>\n";
  }
  else
  {
    form_text (64, 'Title of Act', 'Title', 128, TRUE);

    form_hidden_value ('Author', $_POST['Author']);

    form_text (64, 'Stage Name or Troupe','Organization');
    form_text (64, 'Web Site', 'Homepage', 128);
    form_text (64, 'Email for inquries/updates', 'GameEMail', 128, TRUE);

	form_yn('Is this a Troupe Performance?', 'MultipleRuns');

    $text = "<b>Fellow performers</b>  Please list other people involved/required for this\n";
    $text .= "act.\n";
    form_textarea ($text, 'GMs', 2);

    $maininfo = "About You";

    form_section ($maininfo);

    echo "  <TR>\n";
    echo "    <TD COLSPAN=2>\n";

    $text = "I/We have been performing burlesque for... ";
    $VALUE_LIST = array("I'm not a burlesque performer",
    	"less than 1 year", "1-2 years", "3-4 years", "5-6 years", "more than 6 years");
    $select = $VALUE_LIST[1];

    if ( 0 != $BidId )
    {
        $select = $_POST["RunBefore"];
    }

    form_single_select($text,"RunBefore", $VALUE_LIST, $select);
    echo "    </TD>\n";
    echo "  </TR>\n";



    echo "  <TR>\n";
    echo "    <TD COLSPAN=2>\n";
    echo "      <br><b>I/We would like to be considered for... <font color=\"red\">*</font></b>\n";
    echo "    </TD>\n";
    echo "  </TR>\n";
  


    $DAYS = $SHOW_DAYS;

    // using the bid slot table as the mechanism for storing which
    // shows the act submitter is submitting to.
    echo "  <TR>\n";
    echo "    <TD COLSPAN=2>\n";
    echo "      <TABLE BORDER=0>\n";

	foreach ($DAYS as $day) {
	  foreach ($SHOW_SLOTS[$day] as $slot) {
	    $entry = $day."_".$slot;
	    $checked = "";
	    if ( strlen($_POST[str_replace(' ','_',$entry)]) > 0 )
	    {
	      $checked = "CHECKED";
	    }
 	    echo "        <TR ALIGN=LEFT>\n";
	    echo "          <TD>{$SHOW_NAMES[$entry]} ({$day} evening)</TD>\n";
	    echo "          <TD><input type=checkbox name=".str_replace(' ','_',$entry)." $checked></TD>\n";
	    echo "        </tr>\n";
	  }
	}
    echo "      </table>";


    $label =  "I/We have performed at...";
    form_radio_grid ($label, $OTHER_SHOWS, $ANSWER_SET, $BidChoice,TRUE);




	  
    $text = "Please give a brief performer/troupe history \n";
    form_textarea ($text, 'Premise', 3, TRUE, TRUE);

    $maininfo = "About Your Act";
    form_section ($maininfo);
    form_text (64, 'Title of Song', 'GameSystem', 128);
    form_text (64, 'Name of Artist',  'OtherDetails', 128);
    form_text (2, 'Act Length', 'Minutes', 0, TRUE, '(mm.ss)','Seconds');
    form_hidden_value ('Hours', 12);            

    $text = "<b>Description</b>\n Please give a brief description of your act. ";
    $text .= "Stage kittens will retrieve costumes and props, but we cannot clean ";
    $text .= "the stage after your act. Please do not leave anything on the stage ";
    $text .= "(water, glitter, confetti, etc.)<BR>";
    form_textarea ($text, 'Description', 15, TRUE, TRUE);

    $text = "A <b>short blurb</b> (50 words or less) to be used for\n";
    $text .= "summary listings of convention events.\n";
    $text .= "<br>";
    form_textarea ($text, 'ShortBlurb', 4, TRUE, TRUE);
    
	form_section ("Video and Photo");
    
    echo "  <TR>\n";
    echo "    <TD COLSPAN=2><br><br>\n";

    if (isset($_POST["VideoSource"]))
      form_hidden_value ('OrigVideo', $_POST["VideoSource"]);
    if (isset($_POST["PhotoSource"]))
    form_hidden_value ('OrigPhoto', $_POST["PhotoSource"]);

    global $VIDEO_LIST;
    $select = $VIDEO_LIST[0];

    if ( 0 != $BidId )
    {
        if (1 == get_magic_quotes_gpc())
          $select = stripslashes ($_POST["VideoOf"]);
        else
          $select = $_POST["VideoOf"];
    }

    form_single_select("","VideoOf", $VIDEO_LIST, $select);
    echo "    <br></TD>\n";
    echo "  </TR>\n";
 
    // Change to VideoURL
    echo "  <tr>\n";
    echo "     <td colspan=\"2\">\n";
    echo "      &nbsp;<br clear=all>\n";
    echo "      Link to Video<br>\n";
    if ( strpos($_POST["VideoSource"],FILE_UPLOAD_LOC) === FALSE )
      printf ('      <textarea name="VideoURL" cols="80" rows="1" wrap="physical">' .
	  "%s</textarea>\n", $_POST["VideoSource"]);
	else 
      printf ('      <textarea name="VideoURL" cols="80" rows="1" wrap="physical">' .
	  "</textarea>\n");	
    echo "    </td>\n";
    echo "  </tr>\n";


    form_upload("...OR_upload A VIDEO", "video_upload");
    
    form_upload("Please_upload a photograph of yourself","photo_upload",TRUE);

    if (isset($_POST["VideoSource"]) && isset($_POST["PhotoSource"]))
      display_media($_POST["PhotoSource"], $_POST["VideoSource"], "Current");

    form_hidden_value ('MinPlayersMale', 0);            
    form_hidden_value ('MaxPlayersMale', 0);            
    form_hidden_value ('PrefPlayersMale', 0);
    form_hidden_value ('MinPlayersFemale', 0);            
    form_hidden_value ('MaxPlayersFemale', 0);            
    form_hidden_value ('PrefPlayersFemale', 0);
    form_hidden_value ('MinPlayersNeutral', 0);            
    form_hidden_value ('MaxPlayersNeutral', 0);            
    form_hidden_value ('PrefPlayersNeutral', 0);
    
    $label =  "Are You...";
    form_radio_grid ($label, $PARTICIPATION, $PARTICIPATE_SET, $BidChoice,TRUE);

  }



  form_hidden_value ('Genre', 'X');
  form_hidden_value ('OngoingCampaign', 'N');
  

  echo "  <tr>\n";
  echo "    <td colspan=2>\n";
  echo "&nbsp;<br>\n";

      form_hidden_value ('OtherGames', '');

  //form_textarea ('Other Event Details', 'OtherDetails', 5);
  

 
  
    form_hidden_value ('Fee', 'N');



/*  form_textarea ('Space Requirements', 'SpaceRequirements', 2); */
  

  form_section ('Advertising Information');

  $text = "A short sentence for the {$thingstring} to be used to sell the {$thingstring} to the\n";
  $text .= "general public.\n";
  form_textarea ($text, 'ShortSentence', 2, TRUE, TRUE);

      $text = "Are there any additional interesting things that\n";
      $text .= "we can use when we do Shameless Plugs for the expo?  Do you\n";
      $text .= "have a plug or advertising spiel you use when you\n";
      $text .= "talk about the {$thingstring} that you can send or describe to us?";

  form_textarea ($text, 'ShamelessPlugs', 4);

  $text = "Are you going to any shows, events or other conventions where\n";
  $text .= "you will advertise your {$thingstring} for ".CON_NAME."?  If so, which ones?";
  form_textarea ($text, 'GMGameAdvertising', 4);

  $text = "Would you be willing to advertise ".CON_NAME." at these events, by\n";
  $text .= "taking flyers, doing plugs for ".CON_NAME.", or doing plugs for\n";
  $text .= "other events at ".CON_NAME."?  If so, what are you willing to do,\n";
  $text .= "and when?";
  form_textarea ($text, 'GMInterconAdvertising', 4);

  form_yn ('Do you want us to send you flyers to distribute?',
	   'SendFlyers');

  // Payment Information for Act Submission.
	   
  echo "<br>";	   
  form_section ('Payment Information');   
  $text = "Please note that your act submittal is not complete without payment ";
  $text .= "of the application fee.  You can pay that fee at Brown Paper Tickets using the link below.\n";
  echo "<tr><td colspan=2><br>$text</td></tr>\n";
  $link = create_act_fee_refer_link();
  echo "<tr><td colspan=2><a href=$link target=\"_blank\">Make Payment at Brown Paper Tickets</a></td></tr>\n"; 
  echo "<tr><td colspan=2>&nbsp</td></tr>\n"; 	   
	   
  if ($_POST['Status'] == "Draft" || $_POST['currentDraft'] == TRUE)
	  form_hidden_value("currentDraft",TRUE);

  if (0 == $BidId)
    form_submit2 ('Submit Bid','Save Draft','isDraft');
  else if ($_POST['Status'] == "Draft" || $_POST['currentDraft'] == TRUE)
    form_submit2 ('Submit Bid','Update Draft','isDraft');
  else 
    form_submit ('Update Bid');
	
	
  echo "</TABLE>\n";
  echo "</FORM>\n";
}

/*
 * validate_video
 *
 * Validate the state of video
 */

function validate_video ($haveFile)
{
  
  global $VIDEO_LIST;
  $returnvalue = TRUE;
  $a = stripslashes(trim($_POST["VideoOf"]));
  $b = $VIDEO_LIST[0];
  
//echo $_FILES["video_upload"]["size"];
//echo $_POST["VideoURL"];


  // Test Video URL, if provided
  if (isset($_POST["VideoURL"]) && strlen($_POST["VideoURL"]) > 0)
  {
    //echo $_POST["VideoURL"];
    if(!filter_var($_POST["VideoURL"], FILTER_VALIDATE_URL)) {
      $returnvalue &= display_error("Video URL is not valid");
    }
    else 
    {
      $h = array();
      $h = get_headers( $_POST["VideoURL"]);
      if ( $h[0] == "HTTP/1.1 404 Not Found")
        $returnvalue &= display_error("Video is not available.");
    }
    
    if ( validate_file( "video_upload") )
      $returnvalue &= display_error("Please specify only 1 video source - file_upload".
    								" or URL - not both");
  }
  
   								
//echo $a.$b;
//echo strcmp($a, $b);

// did the applicant tell us there was a video?
$haveVideo = !strcmp($a, $b);

  if (  $haveVideo && 
        (validate_file( "video_upload") || (isset($_POST["VideoURL"]) && 
        		strlen($_POST["VideoURL"]) != 0 )))
    $returnvalue &= display_error("Video description suggests no video, and yet a ".
								    "video was provided.");
  if ( !$haveFile && !$haveVideo && 
        (!validate_file( "video_upload") && 
        (!isset($_POST["VideoURL"]) || strlen($_POST["VideoURL"]) == 0)))
    $returnvalue &= display_error("Video description promises a video, and yet no ".
								    "video was provided.");
    
  return $returnvalue;
}

/*
 * validate_show
 *
 * Validate the value of the show history
 */

function validate_show ($key)
{
  $key = str_replace ( ' ' , '_' , $key );
  $value = trim ($_POST[$key]);

  global $ANSWER_SET;
  
  foreach ($ANSWER_SET as $answer)
  {
    if ($answer == $value)
      return TRUE;
  }
  if ( $_POST[$key] = '')
      return TRUE;

  return display_error ("Invalid value \"$value\" for $display scheduling entry.  Valid values are 1, 2, 3 and X");
}



/*
 * process_bid_form
 *
 * Validate the bid information and write it to the Bids table
 *
 * $ActIsPaidFor - returns if the act has been paid, used by display_bid_etc()  -MDB
 */

function process_bid_form(&$ActIsPaidFor)
{
  // Just in case something goes wrong... assume user hasn't paid. -MDB
  
  $ActIsPaidFor = false;
  
  if (out_of_sequence ())
    return display_sequence_error (false);

  //dump_array ('$_POST', $_POST);

  // Make sure that the user is logged in

  if (! isset ($_SESSION[SESSION_LOGIN_USER_ID]))
    return display_error ('You must login before submitting a class, panel or performance.');

  $BidId = intval (trim ($_REQUEST['BidId']));
  
  // Edit Game Info is to control cases where the bid has already been
  // accepted.  If it's got an associated event, it's not editable.
  $EditGameInfo = intval (trim ($_REQUEST['EditGameInfo']));

  // Sanity checks
  if (0 == $BidId)
  {
    if (! $EditGameInfo)
      return display_error ("BidId = 0 when EditGameInfo = $EditGameInfo");
  }

  // if this is a draft, we don't check all the required fields.
  $isDraft = FALSE;  
  if (isset($_POST['isDraft']))
    $isDraft = TRUE;
  
  //echo "isDraft=$isDraft<br>\n";
    
  //echo "EditGameInfo: $EditGameInfo<br>\n";

  //echo "BidId: $BidId<br>\n";  
  
  //echo "GameType: ".$_REQUEST['GameType']."<br>\n";

  // Always hopeful...

  $form_ok = TRUE;

  // Event Information
  if ($EditGameInfo)
    $form_ok &= validate_string ('Title');

  if ($EditGameInfo && !$isDraft)
  {
    $form_ok &= validate_string ('GameType');
    $form_ok &= validate_string ('Title');
    $form_ok &= validate_string ('Author');
    $form_ok &= validate_string ('GameEMail', 'EMail for game inquiries');

    if (! (validate_players ('Male') &&
	   validate_players ('Female') &&
	   validate_players ('Neutral')))
      $form_ok = FALSE;

    $form_ok &= validate_int ('Hours', 1, 12, 'Hours');
    $form_ok &= validate_int ('Minutes', 1, 60, 'Minutes');
    $form_ok &= validate_int ('Seconds', 0, 60, 'Seconds');
    $form_ok &= validate_string ('Description');
    $form_ok &= validate_string ('ShortBlurb', 'Short blurb');
  }

  // Scheduling Information
  global $SHOW_DAYS;
  global $SHOW_SLOTS;

  // Advertising Information
  global $OTHER_SHOWS;
  global $PARTICIPATION;

  // Make sure that we don't already have a game with this title
  $Title = trim ($_POST['Title']);

  if (!$EditGameInfo)
  {
    if (!title_not_in_events_table ($Title))
      return false;
  }

  // Game Details - required to submit/update a bid
  //  not required for a draft.
  if (!$isDraft)
  {
    $form_ok &= validate_string ('Genre');
    $form_ok &= validate_string ('Premise','Performer history');

    $checkedEver = FALSE;
    foreach ($SHOW_DAYS as $day)
      foreach ($SHOW_SLOTS[$day] as $slot)
      {
      	// echo "box: ".$_POST[str_replace(' ','_',"{$day}_{$slot}")];
  		if (isset($_POST[str_replace(' ','_',"{$day}_{$slot}")]))
  		  $checkedEver = TRUE;
      }
    if (! $checkedEver)
      $form_ok &= display_error("You must pick at least one show to apply to.");
    foreach ($OTHER_SHOWS as $show) {
      $form_ok &= validate_show($show);
    }

    $form_ok &= validate_string ('ShortSentence', 'Short sentence');
  
    if (!validate_file ("photo_upload") && 
        (!isset($_POST["OrigPhoto"]) || $_POST["OrigPhoto"] == "" ))
    {
      $form_ok &= display_error("Please provide a photo");
    }
    $form_ok &= validate_video(isset($_POST["OrigVideo"]) && $_POST["OrigVideo"] != "");
  }
    
  // If any errors were found, abort now
  if (! $form_ok) 
      return FALSE;
  
  $new_bid = (0 == $BidId);

  // If this is a new bid, create an entry in the bid table
  if ($new_bid)
  {
    $sql = 'INSERT Bids SET Created=NULL';
    $sql .= build_sql_string ('UserId', $_SESSION[SESSION_LOGIN_USER_ID]);

    // if this is a Draft, mark it as such
    if ($isDraft)
      $sql .= build_sql_string('Status','Draft');
      
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Insert into Bids failed");

    $BidId = mysql_insert_id();
  }
  


  // Now the event information

  if ($EditGameInfo)
  {
    $sql = 'UPDATE Bids SET ';
    $sql .= build_sql_string ('Title', $Title, false);
    $sql .= build_sql_string ('Author');
    $sql .= build_sql_string ('Homepage');
    $sql .= build_sql_string ('GameEMail');
    $sql .= build_sql_string ('Organization');    
    $sql .= build_sql_string ('GameType');

    // if we've submitted this as a bid
    if ($_POST['currentDraft'] == TRUE && !$isDraft)
      $sql .= build_sql_string('Status','Pending');

    $sql .= build_sql_string ('MinPlayersMale');
    $sql .= build_sql_string ('MaxPlayersMale');
    $sql .= build_sql_string ('PrefPlayersMale');

    $sql .= build_sql_string ('MinPlayersFemale');
    $sql .= build_sql_string ('MaxPlayersFemale');
    $sql .= build_sql_string ('PrefPlayersFemale');

    $sql .= build_sql_string ('MinPlayersNeutral');
    $sql .= build_sql_string ('MaxPlayersNeutral');
    $sql .= build_sql_string ('PrefPlayersNeutral');

    $sql .= build_sql_string ('Hours');
    $sql .= build_sql_string ('Minutes');
    $sql .= build_sql_string ('Seconds');
    $sql .= build_sql_string ('CanPlayConcurrently');
    $sql .= build_sql_string ('Description', '', true, true);
    $sql .= build_sql_string ('ShortBlurb', '', true, true);

    $sql .= " WHERE BidId=$BidId";

//   echo "Event Info: $sql<P>\n";

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ("Insert into Bids failed");
  }

  // Game details

  $sql = 'UPDATE Bids SET ';
  $sql .= build_sql_string ('Genre', '', FALSE);
  $sql .= build_sql_string ('OngoingCampaign');
/*  $sql .= build_sql_string ('IsSmallGameContestEntry'); */
  $sql .= build_sql_string ('GMs', '', true, true);
  $sql .= build_sql_string ('Premise', '', true, true);
  $sql .= build_sql_string ('RunBefore');  
  $sql .= build_sql_string ('VideoOf');  
  $sql .= build_sql_string ('Fee');
  $sql .= build_sql_string ('GameSystem');
  $sql .= build_sql_string ('CombatResolution');
  $sql .= build_sql_string ('OtherDetails', '', true, true);
  $sql .= build_sql_string ('OtherGMs');
  $sql .= build_sql_string ('OtherGames', '', true, true);

  $sql .= " WHERE BidId=$BidId";

  //echo "Game Details: $sql<P>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Insert into Bids failed");

  // Restrictions & Scheduling Information

  $sql = 'UPDATE Bids SET ';

  $sql .= build_sql_string ('Offensive', '', FALSE, TRUE);
  $sql .= build_sql_string ('PhysicalRestrictions', '', TRUE, TRUE);
  $sql .= build_sql_string ('AgeRestrictions', '', TRUE, TRUE);
  $sql .= build_sql_string ('SchedulingConstraints', '', TRUE, TRUE);
  $sql .= build_sql_string ('SpaceRequirements', '', TRUE, TRUE);
  $sql .= build_sql_string ('MultipleRuns');
  $sql .= " WHERE BidId=$BidId";

  //echo "Restrictions and Scheduling: $sql<P>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Insert into Bids failed");

  $sql = "DELETE from BidTimes WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Delete from BidTimes failed");

  foreach ($SHOW_DAYS as $day)
  	foreach ($SHOW_SLOTS[$day] as $slot) {
	  $sql = "INSERT into BidTimes (BidId, Day, Slot, Pref) values (";
	  $sql .= "{$BidId}, ";
	  $sql .= "'{$day}', ";
	  $sql .= "'{$slot}', ";
	  if ( isset( $_POST[str_replace(' ','_',"${day}_{$slot}")]))
	    $sql .= "'1'";
	  else
	    $sql .= "''";
	    
	  $sql .= ");";
	  $result = mysql_query ($sql);
	  if (! $result)
		return display_mysql_error ("Add {$day} {$slot} to BidTimes failed");

  	}

  // update act history
  $sql = "DELETE from BidChoice WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Delete from BidChoice failed");

  foreach ($OTHER_SHOWS as $item) {
	  $sql = "INSERT INTO BidChoice (`BidId` ,`Question` ,`Answer`) VALUES (";
	  $sql .= "{$BidId}, ";
	  $sql .= "'{$item}', ";
	  $sql .= "'";
	  $sql .= $_POST[str_replace(' ','_',"${item}")];
	  $sql .= "');";
	  $result = mysql_query ($sql);
	  if (! $result)
		return display_mysql_error ("Add {$item} to BidChoice failed");

  	}

  foreach ($PARTICIPATION as $item) {
	  $sql = "INSERT INTO BidChoice (`BidId` ,`Question` ,`Answer`) VALUES (";
	  $sql .= "{$BidId}, ";
	  $sql .= "'{$item}', ";
	  $sql .= "'";
	  $sql .= $_POST[str_replace(' ','_',"${item}")];
	  $sql .= "');";
	  $result = mysql_query ($sql);
	  
	  if (! $result)
		return display_mysql_error ("Add {$item} to BidChoice failed");

  	}

  // Advertising Information

  $sql = 'UPDATE Bids SET ';

  $sql .= build_sql_string ('ShortSentence', '', FALSE);
  $sql .= build_sql_string ('ShamelessPlugs', '', TRUE, TRUE);
  $sql .= build_sql_string ('GMGameAdvertising', '', TRUE, TRUE);
  $sql .= build_sql_string ('GMInterconAdvertising', '', TRUE, TRUE);
  $sql .= build_sql_string ('SendFlyers');
  $sql .= " WHERE BidId=$BidId";

  //echo "Advertising info: $sql <P>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Insert into Bids failed");
	
  // Where are we sending this information?

  if (1 == DEVELOPMENT_VERSION)
    $send_to = 'DEVELOPMENT_MAIL_ADDR';
  else
    $send_to = EMAIL_SHOW_CHAIR;

  // See who's doing this

  $sql = 'SELECT FirstName, LastName, EMail FROM Users WHERE UserId=';
  $sql .= $_SESSION[SESSION_LOGIN_USER_ID];
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query user information');

  // Sanity check.  There should only be a single row

  if (0 == mysql_num_rows ($result))
    return display_error ('Failed to find user information');

  $row = mysql_fetch_object ($result);

  // Start with file processing
  // name it with the bid Id
  $file = "video_upload";
  if (validate_file($file))
  {
    $path = "";
    $path = process_file($file, "video", "Bid-".$BidId );
    if ( strpos($path,FILE_UPLOAD_LOC) === FALSE )
    {
      return display_error ("Error uploading the video file.");
    }
  }
  else 
    $path = $_POST["OrigVideo"];
    
  $file = "photo_upload";
  if (validate_file($file))
  {
    $path2 = "";
    $path2 = process_file($file, "picture", "Bid-".$BidId );

    if ( strpos($path2,FILE_UPLOAD_LOC) === FALSE )
    {
      return display_error ("Error uploading the photo file.  File output is: ".$path2);
    }
  }
  else 
    $path2 = $_POST["OrigPhoto"];

  $sql = 'UPDATE Bids SET ';

  global $VIDEO_LIST;


  // trim($_POST["VideoOf"]) == $VIDEO_LIST[0] does not work?
  if ( strcmp(trim($_POST["VideoOf"]),$VIDEO_LIST[0]) == 53 )
  {
    //echo $VIDEO_LIST[0];
    $sql .= 'VideoSource="", ';
  }
  else if ($_POST["VideoURL"]) {
    //echo "have URL ".$_POST["VideoURL"];
    $sql .= 'VideoSource="'.$_POST["VideoURL"].'", ';
  }
  else if ( isset($path) && strlen($path) != 0 ) {
      //echo "have video ".$path;
    $sql .= 'VideoSource="'.$path.'", ';
  }
  else {
    //echo "in else";
    $sql .= 'VideoSource="", ';
  }
  $sql .= 'PhotoSource="'.$path2.'"';
  $sql .= " WHERE BidId=".$BidId.";";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Cannot query user information');

	
  // Last Step before Email - If this user has not paid for the act, change the BID status back 
  // to Draft.  We will warn later in display_bid_etc().  -MDB
	
  $UserId = $_SESSION[SESSION_LOGIN_USER_ID];
  $ActIsPaidFor = user_paid_act_submittal_fee($UserId);
  
  if (!$ActIsPaidFor)
  {
	//echo "Switching Event to DRAFT";
  
    $sql = "update Bids set ";
	$sql .= build_sql_string('Status','Draft', false);
	$sql .= " WHERE BidId=$BidId";
	
    $result = mysql_query ($sql);
    if (!$result)
      return display_mysql_error ("Update into Bids failed");
  }	  
 	
  // Start the email logic.
  
  $name = trim ("$row->FirstName $row->LastName");
  $email = $row->EMail;

  $Title = stripslashes (trim ($_POST['Title']));

  if ($new_bid)
  {
    $subject = '[' . CON_NAME . " - Bid] New: $Title";

    $msg = "The act bid has been submitted by $name";
  }
  else
  {
    $subject = '[' . CON_NAME . " - Bid] Update: $Title";

    $msg = "The act bid has been updated by $name";
  }

  $msg .= ' and is waiting for your review at ';
  $msg .= sprintf ('http://www.%s/Acts.php' .
		   '?action=%d&BidId=%d', CON_DOMAIN,
		   BID_SHOW_BID,
		   $BidId);
  $msg .= ' . You must be logged in to see this bid.';

  //echo "subject: $subject<br>\n";
  //echo "message: $msg<br>\n";

  if (!$isDraft)
  {
    if (! intercon_mail ($send_to,
		       $subject,
		       $msg,
		       $email))
      display_error ('Attempt to send mail failed');
  }
  return TRUE;
}

function table_value ($value)
{
  if ('' == trim ($value))
    return '&nbsp;';

  return $value;
}

/*
 * display_bids_for_review
 *
 * Display the bids for review
 */

function display_bids_for_review ()
{
  // Depends on whether the pages is accessed as an Act or as a Conference Bid
  $reviewTopic = "Act";
  global $submitFilter;

  // access is denied you aren't the chair, a committee member or the liason.
  // there are now 2 committees - one for the conference (aka bid) and one for acts/shows
  // the text displayed for each type of review is controlled by $reviewType
  // and a sql filter is added to restrict submissions to only what is applicable to 
  // this page.
  if ((! user_has_priv (PRIV_SHOW_COM)) &&
      (! user_has_priv (PRIV_SHOW_CHAIR)) &&
      (! user_has_priv (PRIV_GM_LIAISON)))
        return display_access_error ();

  $order = 'Status, DisplayName, Title';
  $desc = 'Status';

  if (array_key_exists ('order', $_REQUEST))
  {
    switch ($_REQUEST['order'])
    {
      case 'Game':
	$order = 'Title';
        $desc = 'Game Title';
        break;

      case 'LastUpdated':
	$order = 'LastUpdated DESC';
        $desc = 'Last Updated';
        break;

      case 'Created':
	$order = 'Bids.Created DESC';
        $desc = 'Created';
        break;

      case 'Submitter':
	$order = 'DisplayName';
        $desc = 'Submitter';
        break;
    }
  }

  $sql = 'SELECT Bids.BidId, Bids.Title, Bids.Hours, Bids.Minutes, Bids.Seconds,';
  $sql .= ' Bids.Status, Users.EMail, Users.DisplayName,';
  $sql .= ' Bids.Organization, Bids.EventId, Bids.UserId,';
  $sql .= ' DATE_FORMAT(Bids.LastUpdated, "%H:%i <NOBR>%d-%b-%y</NOBR>") AS LastUpdatedFMT,';
  $sql .= ' DATE_FORMAT(Bids.Created, "%H:%i <NOBR>%d-%b-%y</NOBR>") AS CreatedFMT,';
  $sql .= ' Bids.MinPlayersNeutral AS Min,';
  $sql .= ' Bids.MaxPlayersNeutral AS Max,';
  $sql .= ' Bids.PrefPlayersNeutral AS Pref';
  $sql .= ' FROM Bids, Users';
  $sql .= ' WHERE Users.UserId=Bids.UserId';
  $sql .= $submitFilter;
  $sql .= ' AND Bids.Status != \'Draft\' ';
  $sql .= " ORDER BY $order";

  //  echo "SQL: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Query failed for bids');

  if (0 == mysql_num_rows ($result))
    display_error ('There are no bids to review');

  display_header ($reviewTopic . ' Submitted for ' . CON_NAME . ' by ' . $desc);

  echo "Click on the item's title to view the details<br>\n";
  echo "Click on the submitter to send mail\n";
  if (user_has_priv (PRIV_SHOW_CHAIR) || user_has_priv (PRIV_SHOW_CHAIR))
    echo "<br>Click on the status to change the status\n";
  echo "<p>\n";

  global $SHOW_DAYS;
  global $SHOW_SLOTS;
  global $SHOW_NAMES;

  $numslots = 0;
  foreach ($SHOW_DAYS as $day)
	$numslots += count($SHOW_SLOTS[$day]);

  echo "<table border=\"1\">\n";
  echo "  <tr valign=\"bottom\">\n";
  printf ("    <th rowspan=\"3\" align=\"left\">" .
	  "<a href=\"Acts.php?action=%d&order=Submitter\">Submitter</th>\n",
	  BID_REVIEW_BIDS);
  printf ("    <th rowspan=\"3\" align=\"left\">" .
	  "<a href=\"Acts.php?action=%d&order=Game\">" . $reviewTopic . "</th>\n",
	  BID_REVIEW_BIDS);
  echo "    <TH ROWSPAN=3>Time<br>mm.ss</TH>\n";
  echo "    <TH COLSPAN={$numslots}>Preferred Shows</TH>\n";
  printf ("    <th rowspan=\"3\" align=\"left\">" .
	  "<a href=\"Acts.php?action=%d&order=Status\">Status</th>\n",
	  BID_REVIEW_BIDS);
  printf ("    <th rowspan=\"3\" align=\"left\">" .
	  "<a href=\"Acts.php?action=%d&order=LastUpdated\">LastUpdated</th>\n",
	  BID_REVIEW_BIDS);
  printf ("    <th rowspan=\"3\" align=\"left\">" .
	  "<a href=\"Acts.php?action=%d&order=Created\">Created</th>\n",
	  BID_REVIEW_BIDS);
  echo "  </tr>\n";

  foreach ($SHOW_DAYS as $day)
	echo "    <TH COLSPAN='".count($SHOW_SLOTS[$day])."'>".substr($day,0,3)."</TH>\n";
  echo "  </tr>\n";

  echo "  <TR VALIGN=BOTTOM>\n";
  foreach ($SHOW_DAYS as $day)
  	foreach ($SHOW_SLOTS[$day] as $slot)
  		echo "          <TH><font size=\"-1\">".$SHOW_NAMES[$day."_".$slot]."</font></TH>\n";
  echo "  </TR>\n";

  while ($row = mysql_fetch_object ($result))
  {
    // Determine the background color for this row

    switch ($row->Status)
    {
      case 'Pending':        $bgcolor = '#FFFFCC'; break;
      case 'Under Review':   $bgcolor = '#DDDDFF'; break;
      case 'Accepted':       $bgcolor = '#CCFFCC'; break;
      case 'Rejected':       $bgcolor = '#FFCCCC'; break;
      case 'Dropped':        $bgcolor = '#FFCC99'; break;
      default:               $bgcolor = '#FFFFFF'; break;
    }

    // If we've got a UserId, fetch the name for the Users record and
    // override any information from the Bid record

    if (0 != $row->UserId)
    {
      $sql = "SELECT DisplayName, EMail";
      $sql .= " FROM Users WHERE UserId=$row->UserId";
      $user_result = mysql_query ($sql);
      if (! $user_result)
	echo "<!-- Query failed for user $row->UserId -->\n";
      else
      {
	if (1 != mysql_num_rows ($user_result))
	  echo "<!-- Unexpected number of rows for user $row->UserId -->\n";
	else
	{
	  $user_row = mysql_fetch_object ($user_result);
	  $row->DisplayName;
	  $row->EMail = $user_row->EMail;
	}

	mysql_free_result ($user_result);
      }
    }

    // If we've got an EventId, fetch the name for the Users record and
    // override any information from the Bid record

    if (0 != $row->EventId)
    {
      $sql = "SELECT ";
      $sql .= ' MinPlayersNeutral AS Min,';
      $sql .= ' MaxPlayersNeutral AS Max,';
      $sql .= ' PrefPlayersNeutral AS Pref';
      $sql .= " FROM Events WHERE EventId=$row->EventId";

      $event_result = mysql_query ($sql);
      if (! $event_result)
	echo "<!-- Query failed for event $row->EventId -->\n";
      else
      {
	if (1 != mysql_num_rows ($event_result))
	  echo "<!-- Unexpected number of rows for event $row->EventId -->\n";
	else
	{
	  $event_row = mysql_fetch_object ($event_result);
	  $row->Min = $event_row->Min;
	  $row->Max = $event_row->Max;
	  $row->Pref = $event_row->Pref;
	}

	mysql_free_result ($event_result);
      }
    }


	$sql = "SELECT * FROM BidTimes WHERE BidId=".$row->BidId.";";
	$btresult = mysql_query ($sql);
	if (! $btresult)
		return display_mysql_error ("BidTimes query failed for BidId ".$row->BidId);

	$bidtimes = array();
	while ($btrow = mysql_fetch_array ($btresult, MYSQL_ASSOC))
	{
		$key = $btrow['Day'].'_'.$btrow['Slot'];
		$bidtimes[$key] = mysql_real_escape_string ($btrow['Pref']);
	}
	mysql_free_result ($btresult);

   	$Act = new Act();
    if ( $row->Status == 'Accepted')
    {
	  $Act->load_from_bidid($row->BidId);
    }


    $name = $row->DisplayName;

    echo "  <TR ALIGN=CENTER BGCOLOR=\"$bgcolor\">\n";

    // If the status is "Pending" then folks with ShowCom priv can know that
    // it's there, but they can't see the game.  The Bid Chair or the GM
    // Liaison can see bid.

    $game_link = true;
    $priv = user_has_priv (PRIV_SHOW_CHAIR) || user_has_priv (PRIV_GM_LIAISON);

    if (('Pending' == $row->Status) && (! $priv))
      $game_link = false;

    echo "    <TD ALIGN=LEFT><A HREF=mailto:$row->EMail>$name</A></TD>\n";

    if ($game_link)
      $title = sprintf ("<A HREF=Acts.php?action=%d&BidId=%d>$row->Title</A>",
	      BID_SHOW_BID,
	      $row->BidId);
    else
      $title = $row->Title;
    echo "    <TD ALIGN=LEFT>$title</TD>\n";


    echo "    <TD>$row->Minutes:$row->Seconds</TD>\n";

	global $SHOW_DAYS;
	global $SHOW_SLOTS;

  	foreach ($SHOW_DAYS as $day)
  		foreach ($SHOW_SLOTS[$day] as $slot) {
  			$key = $day.'_'.$slot;
  			echo "    <TD>" . table_value ($bidtimes[$key]) . "</TD>\n";
  	    }

    if (user_has_priv (PRIV_SHOW_CHAIR))
      printf ("    <TD><A HREF=Acts.php?action=%d&BidId=%d>$row->Status</A>",
	      BID_CHANGE_STATUS,
	      $row->BidId);
    else
      echo "    <TD>$row->Status";

    if ( $row->Status == 'Accepted')
    {
	  echo " - ".$Act->get_show_name();
    }
    echo "    </td>\n";

    echo "    <TD>$row->LastUpdatedFMT</TD>\n";
    echo "    <TD>$row->CreatedFMT</TD>\n";
    echo "  </tr>\n";
  }

  echo "</TABLE>";

  echo "<P>\n";

  echo "<TABLE>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD BGCOLOR=#FFFFCC>Pending</TD>\n";
  echo "    <TD>\n";
  echo "      A newly submitted item.  The Performance Coordinator is working\n";
  echo "      with the submitter to make sure that it is complete\n";
  echo "    </TD>\n";
  echo "  </tr>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD BGCOLOR=#DDDDFF>Under Review</TD>\n";
  echo "    <TD>\n";
  echo "      An item that is available for review by the Performance Committee\n";
  echo "    </TD>\n";
  echo "  </tr>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD BGCOLOR=#CCFFCC>Accepted</TD>\n";
  echo "    <TD>\n";
  echo "      An item that has been accepted for ".(USE_CON_SHORT_NAME ? CON_SHORT_NAME : CON_NAME)."\n";
  echo "    </TD>\n";
  echo "  </tr>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD BGCOLOR=#FFCCCC>Rejected</TD>\n";
  echo "    <TD>\n";
  echo "      An item that has been rejected for ".(USE_CON_SHORT_NAME ? CON_SHORT_NAME : CON_NAME)."\n";
  echo "    </TD>\n";
  echo "  </tr>\n";
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD BGCOLOR=#FFCC99>Dropped</TD>\n";
  echo "    <TD>\n";
  echo "      An item that was previously accepted and has been dropped\n";
  echo "      from the schedule\n";
  echo "    </TD>\n";
  echo "  </tr>\n";
  echo "</TABLE>\n";

  echo "<P>\n";
}

/*
 * change_bid_status
 *
 * Allow a user to change the status of a bid
 */

function change_bid_status ()
{
  // Only the bid chair has privilege to access this page

  if (! user_has_priv (PRIV_SHOW_COM))
    return display_access_error ();

  // Extract the BidId

  $BidId = intval (trim ($_REQUEST['BidId']));
  if (0 == $BidId)
    return display_error ("BidId not specified!");

  // Fetch information to display about the bid

  $sql = "SELECT Title, Status, Homepage, Organization, Premise, MultipleRuns, PhotoSource From Bids WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for BidId $BidId");

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find sId $BidId");

  if (1 != mysql_num_rows ($result))
    return display_error ("Found multiple entries for BidId $BidId");

  $row = mysql_fetch_object ($result);

  display_header ("Change status for <I>$row->Title</I>");

  echo "<form method=\"POST\" action=\"Acts.php\">\n";
  form_add_sequence ();
  printf ("<INPUT TYPE=HIDDEN NAME=action VALUE=%d>\n",
	  BID_PROCESS_STATUS_CHANGE);
  printf ("<INPUT TYPE=HIDDEN NAME=BidId VALUE=%d>\n", $BidId);

  echo "<P>Bid Status: \n";
  echo "<SELECT Name=Status SIZE=1>\n";

  switch ($row->Status)
  {
    case 'Pending':
      echo "  <option value=Pending selected>Pending</option>\n";
      echo "  <option value=\"Under Review\">Under Review</option>\n";
      echo "  <option value=Rejected>Rejected</option>\n";
      break;

    case 'Under Review':
      echo "  <option value=\"Under Review\" selected>Under Review</option>\n";
      echo "  <option value=Accepted>Accepted</option>\n";
      echo "  <option value=Rejected>Rejected</option>\n";
      echo "  <option value=Dropped>Dropped</option>\n";
      break;

    case 'Accepted':
      echo "  <option value=Accepted selected>Accepted</option>\n";
      echo "  <option value=Dropped>Dropped</option>\n";
      break;

    case 'Rejected':
      echo "  <option value=\"Under Review\">Under Review</option>\n";
      echo "  <option value=Accepted>Accepted</option>\n";
      echo "  <option value=Rejected selected>Rejected</option>\n";
      break;

    case 'Dropped':
      echo "  <option value=\"Under Review\">Under Review</option>\n";
      echo "  <option value=Rejected>Rejected</option>\n";
      echo "  <option value=Dropped selected>Dropped</option>\n";
      break;

    default:
      echo "</SELECT>\n";
      echo "</FORM>\n";
      return display_error ("Invalid Status: $row->Status");
  }

  echo "</SELECT><br><br>\n";
  
  if ($row->Status == 'Accepted' || $row->Status == 'Under Review' )
  {
    $showId = 0;
    $isGroup = $row->MultipleRuns;
    $GroupName = $row->Organization;
    $Website = $row->Website;
    $PhotoSource = $row->PhotoSource;
    $BioText = $row->Premise;
    
    if ( $row->Status == 'Accepted')
    {
      $Act = new Act();
  	  $Act->load_from_bidid($BidId);
  	  $showId = $Act->ShowId;
  	  form_hidden_value("ActId",$Act->ActId);
  	  $isGroup = $Act->isGroup;
  	  if ($isGroup == 1)
  	    $GroupName = $Act->$GroupName;
    }
    echo "Cast Act in show:  <br>";
    display_show_list($showId);
    echo "Is this a group act?  ";
    form_checkbox("isGroup", $isGroup);
    
    $bid_pref_slots = array();
    get_preferred_shows($BidId, &$bid_pref_slots);
    display_show_pref($bid_pref_slots);

    echo "<br><br>\n";
    
    display_header("Bio Information");
    // what a pain
    $_POST["GroupName"] =$GroupName;
    form_text(48, "Group Name (if applicable)","GroupName",$GroupName);

  }
  
  echo "<P>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=\"Update Status\">\n";
  echo "</FORM>\n";
}


/*
 * process_status_change
 *
 * Change the status of a bid
 */

function process_status_change ()
{
  // Only the bid chair has privilege to access this page

  if (! user_has_priv (PRIV_SHOW_COM))
    return display_access_error ();

  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (false);

  // Extract the BidId

  $BidId = intval (trim ($_REQUEST['BidId']));
  if (0 == $BidId)
    return display_error ("BidId not specified!");

  $Status = trim ($_POST['Status']);
  if (1 == get_magic_quotes_gpc())
    $Status = stripslashes ($Status);

  $ShowId = intval (trim ($_REQUEST['ShowId']));
  if (0 == $ShowId && $Status == 'Accepted')
    return display_error ("Show not specified!");

  // Fetch the status to see if this is really a change

  $sql = "SELECT Title, Status, EventId From Bids WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for BidId $BidId");

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find BidId $BidId");

  if (1 != mysql_num_rows ($result))
    return display_error ("Found multiple entries for BidId $BidId");

  $row = mysql_fetch_object ($result);

  if ($row->Status == $Status)
    display_error ("Status unchanged for $row->Title");

  // Update the bid status

  $sql = "UPDATE Bids SET Status='$Status' WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Failed to update status for BidId $BidId");

  // Handle dropped bids

  if ('Dropped' == $Status)
    return drop_bid ($BidId);

  // Bids that have moved to Under Review need to have a discussion entry
  // added

  if ('Under Review' == $Status)
    return create_feedback_forum ($BidId);

  // If the status isn't accepted, we're done

  if ('Accepted' != $Status)
    return TRUE;

  // Fetch the bid information and stuff it into the $_POST array so we
  // so we can write it into the User and Event tables

  $sql = "SELECT * FROM Bids WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query failed for BidId $BidId");

  if (0 == mysql_num_rows ($result))
    return display_error ("Failed to find BidId $BidId");

  if (1 != mysql_num_rows ($result))
    return display_error ("Found multiple entries for BidId $BidId");

  $row = mysql_fetch_array ($result, MYSQL_ASSOC);

  foreach ($row as $key => $value)
  {
    if (1 == get_magic_quotes_gpc())
      $_POST[$key] = mysql_real_escape_string ($value);
    else
      $_POST[$key] = $value;
  }

//  dump_array ("_POST", $_POST);



  // If the submitter is unpaid, comp him or her now

  $UserId = intval ($_POST['UserId']);

  // Let the submitter deal with who's comped
/*
  $sql = "SELECT CanSignup FROM Users WHERE UserId=$UserId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for user payment status failed for UserId $UserId");
  $row = mysql_fetch_object ($result);
  if (! $row)
    return display_error ("Failed to find user for UserId $UserId");

  if (is_unpaid ($row->CanSignup))
    comp_user ($UserId, $EventId);
*/

  // Set up the Act links for scheduling the act and performer
  $Act = new Act();
  $Act->convert_from_array($_POST);
  $Act->save_to_db();
  $Act->load_from_bidid($BidId);
  $Act->add_performer($UserId);

  return TRUE;
}

/*
 * showsbid_feedback_summary
 *
 */

function show_bid_feedback_summary()
{

  global $submitFilter;

  // Only bid committe members, the bid chair and the GM Liaison may access
  // this page

  if ((! user_has_priv (PRIV_SHOW_COM)) &&
      (! user_has_priv (PRIV_SHOW_CHAIR)) &&
      (! user_has_priv (PRIV_GM_LIAISON)))
    return display_access_error ();


  display_header ('Show Committee Feedback');
  echo "<p>Click on a title to update all entries for that act<br>\n";
  echo "Click on a Performance Committee member to update all entries under discussion\n";
  echo "for the member<br>\n";
  echo "Click on a vote to update just the entry for that member</p>\n";

  // Get the names of all Bid Committee members

  $sql = "SELECT UserId, DisplayName FROM Users";
  $sql .= "  WHERE FIND_IN_SET('ShowCom', Priv)";
  $sql .= "  ORDER BY DisplayName";

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Query failed for Bid Committee Members');

  if (0 == mysql_num_rows ($result))
    display_error ('There are no Bid Committee Members to display');

  $committee = array ();
  $committee_users = array();
  $committee_headers = array();

  while ($row = mysql_fetch_object ($result))
  {
    $name = trim ("$row->DisplayName");
    $committee[$name] = '';
    $committee_users[$name] = $row->UserId;
    $committee_headers[$name] = trim ("$row->DisplayName");
  }

  //  dump_array ('$committee', $committee);
  //  dump_array ('$committee_users', $committee_users);

  $sql = 'SELECT Bids.Title, Bids.Status, BidStatus.BidStatusId,';
  $sql .= ' BidStatus.Consensus, BidStatus.Issues,';
  $sql .= ' DATE_FORMAT(BidStatus.LastUpdated, "<nobr>%d-%b</nobr> %H:%i") AS LastUpdated';
  $sql .= '  FROM BidStatus, Bids';
  $sql .= '  WHERE Bids.BidId=BidStatus.BidId';
  $sql .= $submitFilter;

  $sql .= '  ORDER BY BidStatus.Consensus, Bids.Title';

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Query failed for bids');

  if (0 == mysql_num_rows ($result))
    display_error ('There are no entries with feedback to display');

  $prefix = '';
  $suffix = '';
  if (user_has_priv (PRIV_SHOW_CHAIR))
    $suffix = '</a>';

  echo "<table border=\"1\">\n";
  echo "  <tr valign=\"bottom\">\n";
  echo "    <th align=\"left\">Act</th>\n";
  echo "    <th>Status / Updated</th>\n";
  foreach ($committee as $key => $value)
  {
    if (user_has_priv (PRIV_SHOW_CHAIR))
    {
      $prefix = sprintf ('<a href="Acts.php?action=%d&UserId=%d">',
			 BID_FEEDBACK_BY_CONCOM,
			 $committee_users[$key]);

    }
    printf ("    <th>%s%s%s</th>\n",
	    $prefix, $committee_headers[$key], $suffix);
  }
  echo "    <th nowrap>Issue Summary</th>\n";
  echo "  </tr>\n";

  while ($row = mysql_fetch_object ($result))
  {
    // Initialize all committee members to Undecided

    $prefix = '';
    $suffix = '';
    if (user_has_priv (PRIV_SHOW_CHAIR))
      $suffix = '</a>';

    foreach ($committee as $key => $value)
    {
      if (user_has_priv (PRIV_SHOW_CHAIR))
      {
	$prefix = sprintf ('<a href="Acts.php?action=%d&BidStatusId=%d&UserId=%d">',
			   BID_FEEDBACK_BY_ENTRY,
			   $row->BidStatusId,
			   $committee_users[$key]);
      }
      else if (user_has_priv(PRIV_SHOW_COM) && $committee_users[$key] == $_SESSION[SESSION_LOGIN_USER_ID] )
    $prefix = sprintf ('<a href="Acts.php?action=%d&BidStatusId=%d&UserId=%d">',
			   BID_FEEDBACK_BY_ENTRY,
			   $row->BidStatusId,
			   $_SESSION[SESSION_LOGIN_USER_ID]);
      else 
        $prefix = "";

      $committee[$key] = "${prefix}Undecided${suffix}";
    }

    // Fetch committee votes from the database

    $sql = 'SELECT Users.DisplayName,';
    $sql .= ' BidFeedback.Vote, BidFeedback.Issues, BidFeedback.FeedbackId,';
    $sql .= ' BidFeedback.UserId, BidFeedback.ShowPref';
    $sql .= ' FROM Users, BidFeedback';
    $sql .= " WHERE BidFeedback.BidStatusId=$row->BidStatusId";
    $sql .= '   AND Users.UserId=BidFeedback.UserId';
    $sql .= ' ORDER BY Users.FirstName';

    $committee_result = mysql_query ($sql);
    if (! $committee_result)
      return display_mysql_error ("Query for bid committee info failed");

    while ($committee_row = mysql_fetch_object ($committee_result))
    {
      $prefix = '';
      $suffix = '';

      $name = trim ("$committee_row->DisplayName");
      if (user_has_priv (PRIV_SHOW_CHAIR))
      {
	        $prefix = sprintf ('<a href="Acts.php?action=%d&FeedbackId=%d&UserId=%d">',
			   BID_FEEDBACK_BY_ENTRY,
			   $committee_row->FeedbackId,
			   $committee_row->UserId);
			$suffix = '</a>';

	  }
      else if (user_has_priv(PRIV_SHOW_COM) && $committee_row->UserId == $_SESSION[SESSION_LOGIN_USER_ID] )
      {
        $prefix = sprintf ('<a href="Acts.php?action=%d&FeedbackId=%d&UserId=%d">',
			   BID_FEEDBACK_BY_ENTRY,
			   $committee_row->FeedbackId,
			   $_SESSION[SESSION_LOGIN_USER_ID]);
        $suffix = '</a>';
      }

      $committee[$name] = "$prefix<nobr><b>$committee_row->Vote</b></nobr>$suffix";
      if ($committee_row->ShowPref != NULL)
	      $committee[$name] .= '<br><b>'.$committee_row->ShowPref.'</b>';
      if ('' != $committee_row->Issues)
	      $committee[$name] .= '<br>'.$committee_row->Issues;
    }

    // If this is the bid chairman the feedback information can be edited

    $title = $row->Title;
    if (user_has_priv (PRIV_SHOW_CHAIR))
      $title = sprintf ('<a href="Acts.php?action=%d&BidStatusId=%d">%s</a>',
			BID_FEEDBACK_BY_GAME,
			$row->BidStatusId,
			$title);

    // Make HTML happy about an empty cell

    $issues = $row->Issues;
    if ('' == $issues)
      $issues = '&nbsp;';

    // Determine the background color for this row

    switch ($row->Consensus)
    {
      case 'Discuss':         $bgcolor = '#DDDDFF'; break;
      case 'Accept':          $bgcolor = '#CCFFCC'; break;
      case 'Early Accepted':  $bgcolor = '#99CC99'; break;
      case 'Reject':          $bgcolor = '#FFCCCC'; break;
      case 'Drop':            $bgcolor = '#FFCC99'; break;
      default:                $bgcolor = '#FFFFFF'; break;
    }

    $Consensus = sprintf ("<a name=\"BidStatusId%d\">$row->Consensus</a>",
			  $row->BidStatusId);

    echo "  <tr valign=\"top\" bgcolor=\"$bgcolor\">\n";
    echo "    <td>$title</td>\n";
    echo "    <td><b>$Consensus It</b><br>$row->LastUpdated</td>\n";

    foreach ($committee as $key => $value)
      echo "    <td>$value</td>\n";

    echo "    <td>$issues</td>\n";
    echo "  </tr>\n";

    //    dump_array ('committee', $committee);
  }
  echo "</table>\n<P>\n";

  // Display the key for the feedback table

  echo "<p>\n";

  echo "<table>\n";
  echo "  <tr valign=\"top\">\n";
  echo "    <td bgcolor=\"#DDDDFF\">Discuss It</td>\n";
  echo "    <td>\n";
  echo "      An entry that is available for review by the Class Coordinator or Performance Committee\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "  <tr valign=\"top\">\n";
  echo "    <td bgcolor=\"#CCFFCC\">Accept It</td>\n";
  echo "    <td>\n";
  echo "      A class, panel or performance that has been accepted for \n";
  echo CON_SHORT_NAME;
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "  <tr valign=\"top\">\n";
  echo "    <td bgcolor=\"#99CC99\">Early Accepted It</td>\n";
  echo "    <td>\n";
  echo "      An entry that was Early Accepted for " . CON_SHORT_NAME . "\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "  <tr valign=\"top\">\n";
  echo "    <td bgcolor=\"#FFCCCC\">Reject It</td>\n";
  echo "    <td>\n";
  echo "      An entry that has been rejected for ".CON_SHORT_NAME."\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "  <tr valign=\"top\">\n";
  echo "    <td bgcolor=\"#FFCC99\">Drop It</td>\n";
  echo "    <td>\n";
  echo "      An entry that was previously accepted and has been dropped\n";
  echo "      from the schedule\n";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "</table>\n";

  echo "<p>\n";
}


/*
 * create_feedback_forum
 *
 * Create a BidStatus for a bid that's now Under Review
 */

function  create_feedback_forum ($BidId)
{
  // Check whether a forum for this bid already exists

  $sql = "SELECT BidStatusId FROM BidStatus WHERE BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Check for existing BidStatus entry failed for Bid Id $BidId");

  // If a forum already exists, use it

  if (0 != mysql_num_rows ($result))
  {
    display_error ("Using existing forum for bid ID $BidId");
    return true;
  }

  $sql = "INSERT INTO BidStatus SET BidId=$BidId";
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Failed to create forum for bid ID $BidId");

  return true;
}



/*
 * drop_bid
 *
 * Change the status of a bid from Accepted to Dropped
 */

function drop_bid ($BidId)
{

  // Build the string to update the game information in the bid

  $sql = 'UPDATE Bids SET ';

  $sql .= build_sql_string ('UpdatedById', $_SESSION[SESSION_LOGIN_USER_ID], false);
  $sql .= ', EventId=0';

  $sql .= " WHERE BidId=$BidId";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Update of Bids failed");

  // Now remove the entry from the Events table



  // Set up the Act links for scheduling the act and performer
  $Act = new Act();
  $Act->load_from_bidid($BidId);
  $Act->remove_from_db();


  return TRUE;
}

function display_bid_etc($ActIsPaidFor)
{
  $UserId = $_SESSION[SESSION_LOGIN_USER_ID];
  
  if (isset($_POST['isDraft']))
  {
    $thanks = "<FONT SIZE=\"+2\">Thank you for starting a <b>draft</b> with ";
    $page = 'draftInfo.html';
  }
  else if (!$ActIsPaidFor)
  {
	$thanks = "<FONT SIZE=\"+2\">Thank you for submitting your work for ";
    $page = 'actUnpaidInfo.html';
  }
  else
  { 
	$thanks = "<FONT SIZE=\"+2\">Thank you for submitting your work for ";
	$page = 'actFollowup.html';
  }
  
  echo $thanks;
  echo CON_NAME . "!</FONT>\n";

  if (! is_readable ($page))
  {
    if (! is_readable (TEXT_DIR."/$page"))
    {
      display_error ("Unable to read $page");
    }
    else
      include (TEXT_DIR."/$page");
  }
  else
    include ($page);
}


/*
 * update_feedback_by_game
 *
 * Allow the Bid Committee Chairman to update the bid feedback displayed
 * for bid committee members
 */

function update_feedback_by_game ()
{
  global $SHOW_NAMES;
  
  // Only the bid chair may access this page

  if (! (user_has_priv (PRIV_SHOW_CHAIR) || user_has_priv (PRIV_SHOW_COM)) )
    return display_access_error ();

  $BidStatusId = intval ($_REQUEST['BidStatusId']);

  // Get the information about the bid

  $sql = 'SELECT Bids.Title, Bids.Status,';
  $sql .= ' BidStatus.Consensus, BidStatus.Issues,';
  $sql .= ' DATE_FORMAT(BidStatus.LastUpdated, "%d-%b %H:%i") AS LastUpdated';
  $sql .= '  FROM BidStatus, Bids';
  $sql .= "  WHERE BidStatus.BidStatusId=$BidStatusId";
  $sql .= '    AND Bids.BidId=BidStatus.BidId';

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Query failed for bid status information');

  if (0 == mysql_num_rows ($result))
    display_error ('There are no entries with feedback to display');

  $row = mysql_fetch_object ($result);

  display_header ("Feedback for <I>$row->Title</I>");
  echo "Last updated $row->LastUpdated<p>\n";

  $Consensus = $row->Consensus;
  $Issues = $row->Issues;

  // Get the names of all Bid Committee members

  $sql = "SELECT UserId, DisplayName FROM Users";
  $sql .= "  WHERE FIND_IN_SET('ShowCom', Priv)";
  $sql .= "  ORDER BY DisplayName";

  $result = mysql_query ($sql);
  if (! $result)
    display_mysql_error ('Query failed for Bid Committee Memebers');

  if (0 == mysql_num_rows ($result))
    display_error ('There is no one to display');

  $committee = array ();
  $user_id = array ();
  $issues = array ();
  $feedback_ids = array ();
  $show = array();

  while ($row = mysql_fetch_object ($result))
  {
    $name = trim ("$row->DisplayName");
    $committee[$name] = 'Undecided';
    $show[$name] = " ";
    $user_id[$name] = $row->UserId;
    $issues[$name] = '';
    $feedback_ids[$name] = 0;
  }

  // If this is the first time in, fill in the $_POST array

  if (! array_key_exists ('Consensus', $_POST))
  {
    $_POST['Consensus'] = $Consensus;
    $_POST['Issues'] = $Issues;

    // Fetch committee votes from the database

    $sql = 'SELECT Users.UserId, Users.DisplayName, ';
    $sql .= ' BidFeedback.Vote, BidFeedback.Issues, BidFeedback.FeedbackId,';
    $sql .= ' BidFeedback.ShowPref';
    $sql .= ' FROM Users, BidFeedback';
    $sql .= " WHERE BidFeedback.BidStatusId=$BidStatusId";
    $sql .= '   AND Users.UserId=BidFeedback.UserId';
    $sql .= ' ORDER BY Users.DisplayName';

    echo "<!-- $sql -->\n";

    $committee_result = mysql_query ($sql);
    if (! $committee_result)
      return display_mysql_error ("Query for bid committee info failed");

    while ($committee_row = mysql_fetch_object ($committee_result))
    {
      $name = trim ("$committee_row->DisplayName");
      $committee[$name] = $committee_row->Vote;
      $show[$name] = $committee_row->ShowPref;
      $user_id[$name] = $committee_row->UserId;
      $issues[$name] = $committee_row->Issues;
      $feedback_ids[$name] = $committee_row->FeedbackId;
    }

    //dump_array("committee", $committee);
    //dump_array("user_id", $user_id);
    //dump_array("issues", $issues);
    //dump_array("feedback_ids", $feedback_ids);

    $i = 0;
    foreach ($committee as $k => $v)
    {
      $i++;
      $_POST["vote_$i"] = $v;
      $_POST["issues_$i"] = $issues[$k];
      $_POST["uid_$i"] = $user_id[$k];

      if (array_key_exists ($k, $feedback_ids))
	$_POST["id_$i"] = $feedback_ids[$k];
      else
	$_POST["id_$i"] = 0;
    }
  }

  printf ("<form method=\"POST\" action=\"Acts.php?BidStatusId%d\">\n",
	  $BidStatusId);
  form_add_sequence ();
  form_hidden_value ('action', BID_PROCESS_FEEDBACK_BY_GAME);
  form_hidden_value ('BidStatusId', $BidStatusId);
  echo "<table>\n";

  $i = 0;

  foreach ($committee as $k => $v)
  {
    $i++;
    $u = $user_id[$k];
    form_hidden_value ("id_$i", $_POST["id_$i"]);
    form_hidden_value ("uid_$i", $_POST["uid_$i"]);

    echo "  <tr>\n";
    echo "    <td align=\"right\">$k:&nbsp;&nbsp;</td>\n";

    form_vote ("vote_$i");
    form_issues ("issues_$i");
    echo "  </tr>\n";
    echo "  <tr><td>&nbsp;</td><td colspan=2>\n";
    form_single_select('','ShowPref_'.$i, $SHOW_NAMES, $show[$k], TRUE);
    echo "  </td></tr>\n";
  }

  echo "  <tr><td>&nbsp;</td></tr>\n";

  form_bid_consensus ('Consensus');

  // If magic quotes are on, strip off the slashes

  $key = 'Issues';
  if (1 == get_magic_quotes_gpc())
    $text = stripslashes ($_POST[$key]);
  else
    $text = $_POST[$key];

  echo "  <tr>\n";
  echo "    <td align=\"right\">Issue Summary:</td>\n";
  echo "    <td colspan=\"2\">\n";
  echo "    <INPUT TYPE=TEXT NAME=$key SIZE=80 MAXLENGTH=128 VALUE=\"$text\">\n";
  echo "    </td>\n";
  echo"  </tr>\n";


  echo "  <tr>\n";
  echo "    <td colspan=\"3\" align=\"center\">\n";
  echo "      <INPUT TYPE=SUBMIT VALUE=\"Update Feedback\">\n";
  echo "    </td>\n";
  echo "  </tr>\n";

  echo "</table>\n";
  echo "</form>\n";

}

function process_feedback_by_game ()
{
  // Only the bid chair may access this page

  if (! (user_has_priv (PRIV_SHOW_CHAIR) || user_has_priv(PRIV_SHOW_COM)))
    return display_access_error ();

  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (true);

  $BidStatusId = intval ($_POST['BidStatusId']);

  // Start by updating the BidStatus table, since it's easier

  $sql = 'UPDATE BidStatus SET ';
  $sql .= build_sql_string ('Consensus', '', FALSE);
  $sql .= build_sql_string ('Issues');
  $sql .= ', LastUpdated=NULL';
  $sql .= " WHERE BidStatusId=$BidStatusId";

  //  echo "BidStatus: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Update of BidStatus for Id $BidStatusId failed");

  $i = 1;
  while (isset ($_POST["id_$i"]))
  {
    $id = intval ($_POST["id_$i"]);
    if (0 == $id)
      $sql = 'INSERT INTO BidFeedback SET ';
    else
      $sql = 'UPDATE BidFeedback SET ';

    $issues = $_POST["issues_$i"];
    if ('' == $issues)
      $issues = ' ';

    $sql .= build_sql_string ('Vote', $_POST["vote_$i"], FALSE);
    $sql .= build_sql_string ('Issues', $issues);
    $sql .= build_sql_string ('ShowPref', $_POST["ShowPref_".$i]);

    if (0 == $id)
    {
      $uid = $_POST["uid_$i"];
      $sql .= build_sql_string ('BidStatusId');
      $sql .= build_sql_string ('UserId', $uid);;
    }
    else
    {
      $uid = 0;
      $sql .= " WHERE FeedbackId=$id";
    }

    // echo "$sql<p>\n";

    $result = mysql_query ($sql);
    if (! $result)
      display_mysql_error ("BidFeedback update for id $id, Uid $uid failed");

    $i++;
  }

  return true;
}

function show_bid_feedback_entry_form()
{
  // Only the bid chair may access this page

  if ((! user_has_priv (PRIV_SHOW_COM)) &&
      (! user_has_priv (PRIV_SHOW_CHAIR)) &&
      (! user_has_priv (PRIV_GM_LIAISON)))
    return display_access_error ();

  if (! array_key_exists ('UserId', $_REQUEST))
    return display_error ('Failed to find UserId in $_REQUEST array');

  $UserId = intval($_REQUEST['UserId']);
  if ($UserId < 1)
    return display_error ("Invalid value for $$UserId: $UserId");

  $sql = "SELECT DisplayName FROM Users WHERE UserId=$UserId";
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Query for User Name failed', $sql);

  $row = mysql_fetch_object($result);
  if (! $row)
    return display_error ("Failed to find user for $$UserId: $UserId");

  $name = trim ("$row->DisplayName");

  $BidStatusId = 0;
  $FeedbackId = 0;

  if (array_key_exists ('FeedbackId', $_REQUEST))
    $FeedbackId = intval ($_REQUEST['FeedbackId']);

  $Vote = 'Undecided';
  $Title = 'Unknown';
  $Issues = '';
  $Show = 'No Clue';

  if (0 != $FeedbackId)
  {
    $sql = 'SELECT BidFeedback.Vote, BidFeedback.Issues,';
    $sql .= ' BidFeedback.BidStatusId, BidFeedback.ShowPref, Bids.Title';
    $sql .= ' FROM Bids, BidStatus, BidFeedback';
    $sql .= " WHERE BidFeedback.FeedbackId=$FeedbackId";
    $sql .= '   AND BidStatus.BidStatusId=BidFeedback.BidStatusId';
    $sql .= '   AND Bids.BidId=BidStatus.BidId';

    $result = mysql_query($sql);
    if (! $result)
      return display_mysql_error('Query failed for bid info', $sql);

    if (0 == mysql_num_rows($result))
      return display_error("Failed to find bid info for FeedbackId: $FeedbackId");

    $row = mysql_fetch_object($result);
    $Vote = $row->Vote;
    $Show = $row->ShowPref;
    $Issues = $row->Issues;
    $Title = $row->Title;
    $BidStatusId = $row->BidStatusId;
  }
  else
  {
    // If we don't have a FeedbackId, we'd better have a BidStatusId

    if (! array_key_exists ('BidStatusId', $_REQUEST))
      return display_error ('Failed to find BidStatusId in $_REQUEST array');

    $BidStatusId = intval($_REQUEST['BidStatusId']);
    if (0 == $BidStatusId)
      return display_error ("Invalid BidStatusId: $BidStatusId");

    $sql = 'SELECT Bids.Title';
    $sql .= ' FROM Bids, BidStatus';
    $sql .= " WHERE BidStatus.BidStatusId=$BidStatusId";
    $sql .= '   AND Bids.BidId=BidStatus.BidId';

    $result = mysql_query($sql);
    if (! $result)
      return display_mysql_error('Query failed for bid info', $sql);

    if (0 == mysql_num_rows($result))
      return display_error("Failed to find bid info for FeedbackId: $FeedbackId");

    $row = mysql_fetch_object($result);
    $Title = $row->Title;
  }

  // If this is the first time in, fill in the $_POST array

  if (! array_key_exists ('Vote', $_POST))
  {
    $_POST['Vote'] = $Vote;
    $_POST['Issues'] = $Issues;
  }

  display_header ("Feedback for $name on <i>$Title</i>");

  echo "<form method=\"POST\" action=\"Acts.php\">\n";
  form_add_sequence ();
  form_hidden_value ('action', BID_FEEDBACK_PROCESS_ENTRY);
  form_hidden_value ('FeedbackId', $FeedbackId);
  form_hidden_value ('BidStatusId', $BidStatusId);
  form_hidden_value ('UserId', $UserId);

  global $SHOW_NAMES;
  
  echo "<table>\n";
  echo "  <tr>\n";
  echo "    <th align=\"right\">Vote:&nbsp;&nbsp;</th>\n";
  form_vote('Vote');
  echo "  </tr>\n";
  echo "  <tr>\n";
  echo "    <th align=\"right\">Show Recommendation:&nbsp;&nbsp;</th>\n";
  echo "    <td>";
  form_single_select('','ShowPref', $SHOW_NAMES, $Show, TRUE);
  echo "    </td>";
  echo "  </tr>\n";
  echo "  <tr>\n";
  echo "    <th align=\"right\">Issues:&nbsp;&nbsp;</th>\n";
  form_issues('Issues');
  echo "  </tr>\n";
  form_submit ('Update Feedback');
  echo "</table>\n";
}

function process_feedback_for_entry()
{
  // Only the bid chair may access this page

  if (! ( user_has_priv (PRIV_SHOW_CHAIR) || user_has_priv (PRIV_SHOW_COM)) )
    return display_access_error ();

  // Check for a sequence error

  if (out_of_sequence ())
    return display_sequence_error (true);

  $BidStatusId = intval ($_POST['BidStatusId']);
  $FeedbackId = intval ($_POST['FeedbackId']);
  $UserId = intval ($_POST['UserId']);
  $ShowPref = $_POST['ShowPref'];

  if (0 == $FeedbackId)
    $sql = 'INSERT INTO BidFeedback SET ';
  else
    $sql = 'UPDATE BidFeedback SET ';

  $sql .= build_sql_string ('Vote', $_POST['Vote'], false);
  $sql .= build_sql_string ('Issues');
  if ($ShowPref == " ")
  {
    $sql .= build_sql_string ('ShowPref', "NULL");
  }
  else
    $sql .= build_sql_string ('ShowPref');

  if (0 == $FeedbackId)
  {
    $sql .= build_sql_string ('UserId');
    $sql .= build_sql_string ('BidStatusId');
  }
  else
    $sql .= " WHERE FeedbackId=$FeedbackId";


  //  echo "$sql<p>\n";

  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('BidFeedback entry update failed', $sql);
  else
    return true;
}

function show_bid_feedback_by_user_form()
{
  global $submitFilter;
  global $SHOW_NAMES;
  
  // Only the bid chair may access this page

  if (! user_has_priv (PRIV_SHOW_CHAIR))
    return display_access_error ();

  // Make sure we've got a UserId

  if (! array_key_exists ('UserId', $_REQUEST))
    return display_error ('Failed to find UserId in $_REQUEST array');

  $UserId = intval($_REQUEST['UserId']);
  if ($UserId < 1)
    return display_error ("Invalid value for $$UserId: $UserId");

  // Get the ConCom member's name

  $sql = "SELECT DisplayName FROM Users WHERE UserId=$UserId";
  $result = mysql_query($sql);
  if (! $result)
    return display_mysql_error ('Query for User Name failed', $sql);

  $row = mysql_fetch_object($result);
  if (! $row)
    return display_error ("Failed to find user for $$UserId: $UserId");

  $name = trim ("$row->DisplayName");

  display_header ("Committee Feedback for $name");

  //  dump_array ('$_REQUEST', $_REQUEST);
  //  dump_array ('$_POST before being filled', $_POST);

  // Populate the $_POST array, if necessary

  if (! array_key_exists ('BidCount', $_POST))
  {
    // Gather the list of bids under discussion

    $sql = 'SELECT Bids.Title, BidStatus.BidStatusId';
    $sql .= ' FROM BidStatus,Bids';
    $sql .= ' WHERE Consensus="Discuss"';
    $sql .= '   AND Bids.BidId=BidStatus.BidId';
    $sql .= $submitFilter;
    $sql .= ' ORDER BY Bids.Title';

    $result = mysql_query($sql);
    if (! $result)
      return display_mysql_error ('Query failed for bids under discussion',
				  $sql);

    $_POST['BidCount'] = mysql_num_rows($result);
    if (0 == $_POST['BidCount'])
      return display_error ('There are no submissions under discussion');

    $bids = array();
    $show = array();
    $b = 1;

    while ($row = mysql_fetch_object($result))
    {
      $_POST["BidStatusId_$b"] = $row->BidStatusId;
      $_POST["Title_$b"] = $row->Title;
      $bids[$row->BidStatusId] = $b;
      $show[$b] = " ";
      $b++;
    }

    // Now gather any existing Feedback

    $sql = 'Select BidFeedback.Vote, BidFeedback.Issues, BidFeedback.ShowPref, ';
    $sql .= 'BidFeedback.FeedbackId, BidFeedback.BidStatusId';
    $sql .= ' FROM BidFeedback, BidStatus';
    $sql .= " WHERE BidFeedback.UserId=$UserId";
    $sql .= '   AND BidStatus.BidStatusId=BidFeedback.BidStatusId';
    $sql .= '   AND BidStatus.Consensus="Discuss"';

    $result = mysql_query($sql);
    if (! $result)
      return display_mysql_error ('Failed to fetch feedback', $sql);
    
    while ($row = mysql_fetch_object($result))
    {
      $b = $bids[$row->BidStatusId];
      $_POST["Vote_$b"] = $row->Vote;
      $_POST["Issues_$b"] = $row->Issues;
      $_POST["FeedbackId_$b"] = $row->FeedbackId;
      $_POST["BidStatusId_$b"] = $row->BidStatusId;
      $bids[$row->BidStatusId] = 0;
      $show[$b] = $row->ShowPref;
    }

    // Now deal with any new entries

    foreach ($bids as $key => $b)
    {
      if (0 == $b)
	continue;

      $_POST["Vote_$b"] = 'Undecided';
      $_POST["Issues_$b"] = '';
      $_POST["FeedbackId_$b"] = 0;
      $_POST["BidStatusId_$b"] = $key;
    }

    //    dump_array ('$_POST after being filled', $_POST);

  }

  $BidCount = intval($_POST['BidCount']);

  echo "<form method=\"POST\" action=\"Acts.php\">\n";
  form_add_sequence ();
  form_hidden_value ('action', BID_FEEDBACK_PROCESS_BY_CONCOM);
  form_hidden_value ('UserId', $UserId);
  form_hidden_value ('BidCount', $BidCount);
  echo "<table>\n";
  echo "  <tr>\n";
  echo "    <th>Act</th>\n";
  echo "    <th>Vote</th>\n";
  echo "    <th>Show Recommend</th>";
  echo "    <th>Issue(s)</th>\n";
  echo "  </tr>\n";

  for ($b = 1; $b <= $BidCount; $b++)
  {
    echo "  <tr>\n";
    printf ("    <td>%s</td>\n", $_POST["Title_$b"]);
    form_vote ("Vote_$b");
    echo "  <td>\n";
    form_single_select('','ShowPref_'.$b, $SHOW_NAMES, $show[$b], TRUE);
    echo "  </td>\n";

    form_issues ("Issues_$b");
    form_hidden_value ("Title_$b", $_POST["Title_$b"]);
    form_hidden_value ("FeedbackId_$b", $_POST["FeedbackId_$b"]);
    form_hidden_value ("BidStatusId_$b", $_POST["BidStatusId_$b"]);
    echo "  </tr>\n";
  }

  form_submit ('Submit', 3);

  echo "</table>\n";
}

function process_feedback_for_user()
{
  dump_array ('$_POST', $_POST);

  // Only the bid chair may access this page

  if (! user_has_priv (PRIV_SHOW_CHAIR))
    return display_access_error ();

  // Make sure we've got a UserId

  if (! array_key_exists ('UserId', $_REQUEST))
    return display_error ('Failed to find UserId in $_REQUEST array');

  $UserId = intval($_REQUEST['UserId']);
  if ($UserId < 1)
    return display_error ("Invalid value for $$UserId: $UserId");

  // Make sure we've got a BidCount

  if (! array_key_exists ('BidCount', $_REQUEST))
    return display_error ('Failed to find BidCount in $_REQUEST array');

  $BidCount = intval($_REQUEST['BidCount']);
  if ($BidCount < 1)
    return display_error ("Invalid value for $$BidCount: $BidCount");

  for ($b = 1; $b <= $BidCount; $b++)
  {
    $FeedbackId = intval($_POST["FeedbackId_$b"]);
    if (0 == $FeedbackId)
      $sql = 'INSERT INTO BidFeedback SET ';
    else
      $sql = 'UPDATE BidFeedback SET ';

    $issues = $_POST["Issues_$b"];
    if ('' == $issues)
      $issues = ' ';

    $sql .= build_sql_string ('Vote', $_POST["Vote_$b"], false);
    $sql .= build_sql_string ('Issues', $issues);
    $sql .= build_sql_string ('ShowPref', $_POST["ShowPref_".$b]);

    if (0 == $FeedbackId)
    {
      $sql .= build_sql_string ('UserId', $UserId);
      $sql .= build_sql_string ('BidStatusId', $_POST["BidStatusId_$b"]);
    }
    else
      $sql .= " WHERE FeedbackId=$FeedbackId";

    //    echo "$sql<br>\n";
    $result = mysql_query($sql);
    if (! $result)
    {
      if (0 == $FeedbackId)
	return display_mysql_error ('Insert into BidFeedbackId failed', $sql);
      else
	return display_mysql_error ('Update of BidFeedback failed', $sql);
    }
  }

  return true;
}



?>
