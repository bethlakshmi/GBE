<?php

require ("db_constants.inc");



require ("lookandfeel.inc");



// For site isolation, change the session name below to something appropriate
// when <?
// Per-con values - The values of all of these should be visible on

// the status page




// basic storage location for files 
define (FILE_UPLOAD_LOC, "/Applications/MAMP/uploads/");
define (FILE_DISPLAY_LOC, "/media/");

// deploying a new Intercon site.

session_name ('expo_1');
session_start ();



// Setting DEVELOPMENT_VERSION turns on additional error checking, as well
// as diverting all mail to the DEVELOPMENT_MAIL_ADDR

define (DEVELOPMENT_VERSION, 1);



// When DEVELOPMENT_VERSION is set to 1, all mail will be diverted to this
// address

define (DEVELOPMENT_MAIL_ADDR, 'bethlakshmi@gmail.com');

//Set this to the name of the directory that contains your con specific text.
//Copy the intercontext directory to yours and then edit the files there
//For your con specific text.  NEIL Intercons can skip this step.

define (TEXT_DIR, 'steamtext');

// Set to 0 to disable the poll

define (POLL_ENABLED, 0);

// Set to 0 to disable the Thursday Thing

define (THURSDAY_ENABLED, 0);

// Set to 0 to disable the Dead Dog

define (DEAD_DOG_ENABLED, 0);
define (DEAD_DOG_MAX, 100);

// Set to 0 to disable the Iron GM contest

define (IRONGM_ENABLED, 0);

// Set to 0 to disable allowing players to select events.

define (SELECTEVENTS_ENABLED, 1);

// Defining the program file prefix enables the links on the ConCom menu
// which allow users to download the program booklet sections.  The second
// constant enables the entries on the Ops menu for the .PUB files
//BETTY - error in pub prefix, does not work on local site.

define (PROGRAM_PDF_PREFIX, '02-15-2014');
//define (PROGRAM_PUB_PREFIX, '02-15-2014');

// Con identifiers

define (CON_ID, '2014');
define (CON_NAME, 'The Great Burlesque Expo ' . CON_ID);
define (CON_NUMBER, '8th');
define (CON_DOMAIN, 'burlesque-expo.com');
define (CON_MAX, 600);
//set USE_CON_SHORT_NAME for long convention names.
define (CON_SHORT_NAME, 'GBE');
define (USE_CON_SHORT_NAME, 1);

/*define (PAYPAL_ACCOUNT_EMAIL, 'info@burlesque-expo.com');
define (PAYPAL_ITEM_SHIRT, CON_NAME . ' Shirt Payment');
define (PAYPAL_ITEM_CON, CON_NAME . ' Registration');
define (PAYPAL_ITEM_THURSDAY, CON_NAME . ' Thursday');
define (PAYPAL_ITEM_DEAD_DOG, CON_NAME . ' Dead Dog');
*/

define (CON_MAIL_LETTER, '2014');
define (NAME_CON_CHAIR, 'Scratch');
define (EMAIL_CON_CHAIR, 'info@' . CON_DOMAIN);
define (ADDR_CON_CHAIR, 'Great Burlesque Exposition<br>119 Braintree St.<br>Suite #206<br>Allston, MA 02134<br>USA');

define (NAME_SEND_CHECKS, 'Fake Name');
define (ADDR_SEND_CHECKS, '123 Fake St<br>Fake, MA 90210');

// EMail Addresses

define (NAME_ADVERTISING, 'TBA');
define (EMAIL_ADVERTISING, 'ads@'. CON_DOMAIN);

define (NAME_BID_CHAIR, 'Scandal from Bohemia');
define (EMAIL_BID_CHAIR, 'Teachers13@'. CON_DOMAIN);

define (NAME_MERCH, 'Abbey Bricker');
define (EMAIL_MERCH, 'merch@'. CON_DOMAIN);

define (NAME_REGISTRAR, 'Betty Blaize');
define (EMAIL_REGISTRAR, 'registrar@' . CON_DOMAIN);

define (NAME_SECURITY, 'Seth Wilhelm');
define (EMAIL_SECURITY, 'security@' . CON_DOMAIN);

define (NAME_SHOW_CHAIR, 'private');
define (EMAIL_SHOW_CHAIR, 'sel-comm@' . CON_DOMAIN);

define (NAME_TECH_DIR, 'Hunter Heinlen');
define (EMAIL_TECH_DIR, 'TD@' . CON_DOMAIN);

define (NAME_VENDOR_LIAISON, 'TBA');
define (EMAIL_VENDOR_LIAISON, 'sales@' . CON_DOMAIN);

define (NAME_VOLUNTEER_COORD, 'Hoi Polloi');
define (EMAIL_VOLUNTEER_COORD, 'ops@' . CON_DOMAIN);

define (NAME_COSTUME, 'Bettysioux Tailor');
define (EMAIL_COSTUME, 'costumes@'. CON_DOMAIN);

define (NAME_ART, 'John Bilotti');
define (EMAIL_ART, 'art@'. CON_DOMAIN);

define (EMAIL_WEBMASTER, 'info@' . CON_DOMAIN);

// Con dates

define (FRI_TEXT, 'Friday, 7-Feb-2014');
define (SAT_TEXT, 'Saturday, 8-Feb-2014');
define (SUN_TEXT, 'Sunday, 9-Feb-2014');

define (DATE_RANGE, 'February 7-9, 2014');
define (THR_DATE, '6-Feb-2014');
define (FRI_DATE, '7-Feb-2014');

// Deadlines - Format is YYYY-MM-DD

define (REGISTRATION_CLOSE, '2014-02-6');
define (CON_OVER, '2014-02-10');
define (SHIRT_CLOSE, '2014-02-6');
define (BIO_CLOSE, '2014-01-15');

// Information about the next convention for people who can't sign up for this one
define (NEXT_CON_INFO, "Registration for GBE 2014 will open in May 2013.");

// Shirt stuff
define (SHOW_TSHIRTS, 0);
define (TSHIRT_DOLLARS, 20);

define (SHIRT_IMG_AVAILABLE, 1);
define (SHIRT_TWO_SHIRTS, 1);
define (SHIRT_NAME, CON_NAME . " Men's Polo");
define (SHIRT_2_NAME, CON_NAME . ' Babydoll');

define (BIO_DUE_DATE, "January&nbsp;1,&nbsp;2013");

// Locations
define (HOTEL_NAME, "The Hyatt Regency Hotel");
define (CON_CITY, "Cambridge, MA");
global $ROOM_TYPES;
$ROOM_TYPES =  array( "No Preference", "Movement", "Lecture", "Workshop" );

// Theme colors

//define (COLOR_MENU_PUBLIC_FG, "#24f");
//define (COLOR_MENU_PUBLIC_BG, "#bdf");
//define (COLOR_MENU_PRIV_FG, "#237723");
//define (COLOR_MENU_PRIV_BG, "#bfb");

//These are for the NavMenu
define (NAV_SHOW_COST, 1);
define (NAV_SHOW_FLYER, 0);
define (NAV_SHOW_LINKS, 0);

// 1 (true) if we're accepting bids, 0 if we're not

define (ACCEPTING_BIDS, 1);

define (RECAPTCHA_PUBLIC_KEY, "6Ld3_70SAAAAABWMABItLF0iRTQlzk9KoQtBCCY7");
define (RECAPTCHA_PRIVATE_KEY, "6Ld3_70SAAAAAPLq2uF5DdkW4DBF5g46svK7IH1o");

// Backend for PEAR::Mail to use.  Should be either "mail", "sendmail", or "smtp"
define(MAIL_BACKEND, "sendmail");

// Define these if you specified "smtp" above.
// Must be an SSL-enabled SMTP server listening on port 485
define(SMTP_HOST, "smtp.gmail.com");
define(SMTP_USERNAME, "web@" . CON_DOMAIN);
define(SMTP_PASSWORD, "");



//Look and feel items
define (PAGE_BANNER, 'PageBanner.png');

// rooms at event - must match DB set in Runs  
global $ROOM_LIST;
$ROOM_LIST = array("Crispus Attucks  (Master/Drop-in Classes)",
	"Haym Solomon (Food)",
	"Molly Pitcher (Workshops)",
	"Pool",
	"Theater (Shows/Rehearsals)",
	"Thomas Paine A&B (Dance; dressing room)",
	"Vendor Hall (Shows/Special Events)",
	"William Dawes A (Lectures)",
	"William Dawes B (Movement; Panels)");

//Items for Bids/Schedule
global $CON_DAYS;
$CON_DAYS = array('Friday', 'Saturday', 'Sunday');
global $CLASS_DAYS;
$CLASS_DAYS = array('Saturday', 'Sunday');
global $SHOW_DAYS;
$SHOW_DAYS = array('Friday','Saturday', 'Sunday');

// open id settings
global $OPENIDURLS;
$OPENIDURLS = array();
$OPENIDURLS['livejournal'] = "http://$username.livejournal.com";

// Goes into GameType in Events
global $OPS_TYPES;
$OPS_TYPES = array('Ops','Tech Rehearsal','Act Rehearsal Slot','Call');

//These must match the text values in the BidTimes table slot field.
global $BID_SLOTS;
$BID_SLOTS = array();
$BID_SLOTS['Friday'] = array('Early Afternoon', 'Late Afternoon', 'Evening', 'After Midnight');
$BID_SLOTS['Saturday'] = array('Morning', 'Early Afternoon', 'Late Afternoon');
$BID_SLOTS['Sunday'] = array('Morning', 'Early Afternoon', 'Late Afternoon');
$BID_SLOTS['All'] = array('Morning', 'Early Afternoon', 'Late Afternoon'); //all possible bid slots.

//These must match the text values in the BidTimes table slot field.
global $SHOW_SLOTS;
global $SHOW_NAMES;
$SHOW_SLOTS = array();
$SHOW_SLOTS['Friday'] = array('After Midnight');
$SHOW_SLOTS['Saturday'] = array('Evening','After Midnight');
$SHOW_SLOTS['Sunday'] = array('Evening', 'After Midnight');
$SHOW_SLOTS['All'] = array('Evening', 'After Midnight'); //all possible bid slots.


$SHOW_NAMES['Friday_Evening'] = 'The Rhinestone Review';

$SHOW_NAMES['Friday_After Midnight'] = 'The Bordello';

$SHOW_NAMES['Saturday_Evening'] = 'The Main Event (Competition)';

$SHOW_NAMES['Saturday_After Midnight'] = 'The Main Event (Not in Competition)';

$SHOW_NAMES['Sunday_Evening'] = 'The Newcomer\'s Showcase';

$SHOW_NAMES['Sunday_After Midnight'] = 'The Sideshow Ball';

global $MOVEMENT_OPTIONS;
global $LECTURE_OPTIONS;
$MOVEMENT_OPTIONS=array("Don't Care about Floor","Carpet","Dance Floor", "Both");
$LECTURE_OPTIONS=array("Don't Care about Seating","Lecture Style - tables and chairs face podium",
  			"Conversational - seating in a ring");


// controls radio buttons for performer show history in other 
// big conventions
global $ANSWER_SET;
global $OTHER_SHOWS;

$ANSWER_SET = array("Yes", "No", "Yes - and Won!");
$ANSWER_SET_SMALL = array("Yes", "No");
$OTHER_SHOWS = array("The Great Burlesque Exposition", 			
					"Miss Exotic World/Burlesque Hall",			
					"NYBF",
					"The New Orleans Burlesque Fest",		
					"Texas Burlesque Festival");

// controls radio buttons on participation at expo
global $PARTICIPATE_SET;
global $PARTICIPATION;

$PARTICIPATE_SET = array("Yes", "No", "Not Sure");
$PARTICIPATION = array("staying at the hotel",	"volunteering for the event",
						"attending the conference during the day");

global $BID_SLOT_ABBREV;
$BID_SLOT_ABBREV = array();
$BID_SLOT_ABBREV['Morning'] = 'AM';
$BID_SLOT_ABBREV['Early Afternoon'] = 'Early PM';
$BID_SLOT_ABBREV['Late Afternoon'] = 'Late PM';
$BID_SLOT_ABBREV['Evening'] = 'Eve';
$BID_SLOT_ABBREV['After Midnight'] = 'Late';
$BID_SLOT_ABBREV['Lunch'] = 'Lunch';
$BID_SLOT_ABBREV['Dinner'] = 'Din';

define(BID_SHOW_COMPS, 1);
define(ALLOW_EVENT_FEES, 1);  //set to 1 to allow additional fees for events.

//Types of bids; only use the ones you will accept.
global $BID_TYPES;
$BID_TYPES = array('Class', 'Panel', 'Performance');
//$BID_TYPES = array('LARP');

global $EVENT_TYPES;
$EVENT_TYPES = array('Class', 'Panel', 'Show', 'Special','Ops', 'Show','MasterClass','Drop-In');

global $SPECIAL_EVENT_TYPES;
$SPECIAL_EVENT_TYPES = array('Special','Show','MasterClass','Drop-In');

global $GM_TYPES;
$GM_TYPES = array();
$GM_TYPES['Class'] = 'Teacher(s)';
$GM_TYPES['Panel'] = 'Panelist(s)';
$GM_TYPES['Show'] =  'Cast & Crew';
$GM_TYPES['Special'] = 'OnSite Coordinator(s)';
$GM_TYPES['Ops'] = 'Coordinator(s)';
$GM_TYPES['Call'] = 'Show Staff';
$GM_TYPES['Act Rehearsal Slot'] = 'Show Staff';
$GM_TYPES['Tech Rehearsal'] = 'Show Staff';

global $PANELIST_TYPE;
$PANELIST_TYPE = array("nothing", "panelist", "moderator");


function parse_date ($d)
{
  $a = sscanf ($d, '%d-%d-%d');

  $year = $a[0];
  $month = $a[1];
  $day = $a[2];

  return mktime (0, 0, 0, $month, $day, $year);
}

/*
 * get_con_price
 *
 * The ONE routine that returns the dates and prices for the con
 *
 * Note - start_date is always the correct date.
 *      - end_date is always the end of the range + 1
 * 
 * Note:  This function has been depreciated for the ticketing system.  -MDB
 */

function get_con_price ($i, &$price, &$start_date, &$end_date)
{
/*
  switch ($i)
  {

    // The price is $25 until 20-Jul-2009 - Was end of Intercon/DEXPO, now just arbitrary date
    case 0:
      $price = 99;
      $start_date = 0;
      $end_date = parse_date ('2013-05-31');
      return true;

    // The price is $30 21-Jul-2009 - 12-Oct-2009 - End of Intercon Mid-Atlantic
    case 1:
      $price = 125;
      $start_date = parse_date ('2013-05-31');
      $end_date = parse_date ('2013-10-31');
      return true;

    // The price is $35 13-Oct-2009 - 20-Jan-2010
    // Wednesday after the end of Arisia '09
    case 2:
      $price = 150;
      $start_date = parse_date ('2013-10-31');
      $end_date = 0;
      return true;
  }

  $price = 0;
  $start_date = 0;
  $end_date = 0;
*/
  return false;
}

?>