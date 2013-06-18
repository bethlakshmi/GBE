<?php
include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to connect to ' . DB_NAME);
  exit ();
}

// Display boilerplate

html_begin ();

// Show the current cost

show_cost ();

// Standard postamble

html_end ();

function show_cost ()
{
  // Get today's date.  If the con is over, warn the user and show the whole
  // price schedule.

  $now = time ();
  if ($now > parse_date (CON_OVER))
  {
    printf ("<font color=red>%s is over.  The following was the price schedule for %s</font><p>\n",
	    CON_NAME,
	    CON_NAME);
    get_con_price (0, $price, $start_date, $end_date);
    $now = $start_date - 1;
  }

  $one_day = 60 * 60 * 24;

  // Get the maximum price
  $k = 0;
  $max_price = 0;
  $max_index = -1;
  while (get_con_price ($k++, $price, $start_date, $end_date))
  {
    if ($price > $max_price)
    {
      $max_price = $price;
      $max_index = $k;
    }
  }

  // Figure out where we are in the sequence
  $k = 0;
  while (get_con_price ($k++, $price, $start_date, $end_date))
  {
    if (0 == $end_date)
      break;
    if ($now < $end_date)
      break;
  }

  // If the con is over, warn the user and show the whole price
  // schedule
  if (0 == $end_date)
  {
    printf ("<font color=red>%s is over.  The following was the price schedule for %s</font><p>\n",
	    CON_NAME,
	    CON_NAME);
    $k = 0;
    get_con_price (0, $price, $start_date, $end_date);
    $now = $end_date - 1;
  }

//  printf ("%d: $%d.00, cutoff: %s, now: %s<p>\n", $k, $price,
//	  strftime ('%d-%b-%Y', $end_date),
//	  strftime ('%d-%b-%Y', $now));

	  
  // If we're after the last cutoff, just display the final price.  Otherwise,
  // show the list

  if (0 == $end_date)
    printf ("<h1>%s is only $%s!</h1>\n",
	    CON_NAME,
	    $prices[count($prices)-1]);
  else
  {
    if (($price < $max_price) && ($k < $max_index))
      echo "<h1>Save BIG if you pay today!</h1>\n";
    printf ("<h2>%s is only $%d.00!</h2>\n",
	    CON_NAME,
	    $price);

    while (1)
    {
      get_con_price ($k++, $price, $start_date, $end_date);
      if (0 == $end_date)
	break;

      printf ("$%d.00 after %s<br>\n",
	      $price,
	      strftime ('%d-%b-%Y', $start_date));
    }

    printf ("$%d.00 after %s or at the door.<p>\n",
	    $price,
	    strftime ('%d-%b-%Y', $start_date));
  }

  $reg_close = strftime ('%d-%b-%Y', parse_date (REGISTRATION_CLOSE));
  echo "Online registration will close $reg_close<p>\n";
}
?>