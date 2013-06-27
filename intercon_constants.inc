<?php

require ('db_constants.inc');


// For site isolation, change the session name below to something appropriate
// when deploying a new Intercon site.

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

define (SELECTEVENTS_ENABLED, 0);

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

define (PAYPAL_ACCOUNT_EMAIL, 'info@burlesque-expo.com');
define (PAYPAL_ITEM_SHIRT, CON_NAME . ' Shirt Payment');
define (PAYPAL_ITEM_CON, CON_NAME . ' Registration');
define (PAYPAL_ITEM_THURSDAY, CON_NAME . ' Thursday');
define (PAYPAL_ITEM_DEAD_DOG, CON_NAME . ' Dead Dog');

define (CON_MAIL_LETTER, '2013');
define (NAME_CON_CHAIR, 'Scratch');
define (EMAIL_CON_CHAIR, 'info@' . CON_DOMAIN);
define (ADDR_CON_CHAIR, 'NEED ADDRESS');

define (NAME_SEND_CHECKS, 'Fake Name');
define (ADDR_SEND_CHECKS, '123 Fake St<br>Fake, MA 90210');

// EMail Addresses

define (NAME_ADVERTISING, '');
define (EMAIL_ADVERTISING, '');

define (NAME_BID_CHAIR, 'Scandal from Bohemia');
define (EMAIL_BID_CHAIR, 'Teachers12@Burlesque-Expo.com');

define (NAME_SHOW_CHAIR, 'Scratch');
define (EMAIL_SHOW_CHAIR, 'info@Burlesque-Expo.com');

define (NAME_CON_SUITE, 'NONE');
define (EMAIL_CON_SUITE, 'none@test.com');

define (NAME_GM_COORDINATOR, 'NONE');
define (EMAIL_GM_COORDINATOR, 'none@' . CON_DOMAIN);

define (NAME_HOTEL_LIAISON, 'Scratch');
define (EMAIL_HOTEL_LIAISON, 'info@' . CON_DOMAIN);

define (NAME_OPS, 'Ashley');
define (EMAIL_OPS, 'ashley@' . CON_DOMAIN);
define (NAME_OPS2, '');
define (EMAIL_OPS2, '');

define (NAME_OUTREACH, '');
define (EMAIL_OUTREACH, '@' . CON_DOMAIN);

define (NAME_REGISTRAR, 'Chad Bergeron');
define (EMAIL_REGISTRAR, 'registrar@' . CON_DOMAIN);

define (NAME_THURSDAY, '');
define (EMAIL_THURSDAY, '@' . CON_DOMAIN);

define (NAME_TREASURER, '');
define (EMAIL_TREASURER, '');

define (NAME_VENDOR_LIAISON, 'Ashley');
define (EMAIL_VENDOR_LIAISON, 'vendors@' . CON_DOMAIN);

define (EMAIL_WEBMASTER, 'webmaster@' . CON_DOMAIN);

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

define (BIO_DUE_DATE, "December&nbsp;25,&nbsp;2009");

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

define (COLOR_MENU_PUBLIC_FG, "#224099");
define (COLOR_MENU_PUBLIC_BG, "#b9d1ec");
define (COLOR_MENU_PRIV_FG, "#ca9a00");
define (COLOR_MENU_PRIV_BG, "#FADB5E");

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

// basic storage location for files 
define (FILE_UPLOAD_LOC, "/Applications/MAMP/uploads/");
define (FILE_DISPLAY_LOC, "/media/");


//Look and feel items
define (PAGE_BANNER, 'PageBanner.png');
  
//Items for Bids/Schedule
global $CON_DAYS;
$CON_DAYS = array('Friday', 'Saturday', 'Sunday');
global $CLASS_DAYS;
$CLASS_DAYS = array('Saturday', 'Sunday');
global $SHOW_DAYS;
$SHOW_DAYS = array('Friday','Saturday', 'Sunday');

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

$SHOW_NAMES['Friday_After Midnight'] = 'The Last Chance Casino Party';

$SHOW_NAMES['Saturday_Evening'] = 'The Main Event (Competition)';

$SHOW_NAMES['Saturday_After Midnight'] = ' The Main Event (Not in Competition)';

$SHOW_NAMES['Sunday_Evening'] = ' The Newcomer\'s Showcase';

$SHOW_NAMES['Sunday_After Midnight'] = 'The Sideshow Ball';

// controls radio buttons for performer show history in other 
// big conventions
global $ANSWER_SET;
global $OTHER_SHOWS;

$ANSWER_SET = array("Yes", "No", "Yes - and Won!");
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
 */

function get_con_price ($i, &$price, &$start_date, &$end_date)
{
  switch ($i)
  {

    // The price is $25 until 20-Jul-2009 - Was end of Intercon/DEXPO, now just arbitrary date
    case 0:
      $price = 25;
      $start_date = 0;
      $end_date = parse_date ('2009-07-20');
      return true;

    // The price is $30 21-Jul-2009 - 12-Oct-2009 - End of Intercon Mid-Atlantic
    case 1:
      $price = 30;
      $start_date = parse_date ('2009-07-20');
      $end_date = parse_date ('2009-10-12');
      return true;

    // The price is $35 13-Oct-2009 - 20-Jan-2010
    // Wednesday after the end of Arisia '09
    case 2:
      $price = 35;
      $start_date = parse_date ('2009-10-12');
      $end_date = parse_date ('2010-01-20');
      return true;

    // The price is $40 from 21-Jan-2010 - 28-Feb-2010 - End of Boskone '09
    case 3:
      $price = 40;
      $start_date = parse_date ('2010-01-21');
      $end_date = parse_date ('2010-02-28');
      return true;

    // The price is $45 from 29-Feb-2010 until the end of Intercon I
    case 4:
      $price = 45;
      $start_date = parse_date ('2010-02-28');
      $end_date = 0;
      return true;
  }

  $price = 0;
  $start_date = 0;
  $end_date = 0;

  return false;
}

?>