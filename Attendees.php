<?php
include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

// Display boilerplate

html_begin ();

// Do the work

AttendeesByPayment ();

// Add the postamble

html_end ();

/*
 * AttendeesByPayment
 *
 * Display the last N signups
 */

function AttendeesByPayment ()
{
  // You need ConCom privilege to see this page

  if (! user_has_priv (PRIV_CON_COM))
    return display_access_error ();

  $count = array();
  $paid = array();

  // Get the total number of attendees

  $sql = 'SELECT CanSignup, COUNT(*) AS Count';
  $sql .= ' FROM Users';
  $sql .= ' WHERE CanSignup<>"Alumni"';
  $sql .= '   AND CanSignup<>"Unpaid"';
  $sql .= '   AND CanSignup<>"Paid"';
  $sql .= ' GROUP BY CanSignup';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signup counts failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    $count[$row->CanSignup] = $row->Count;
    $paid[$row->CanSignup] = 0;
  }

  // Now get all of the paid attendees

  $sql = 'SELECT PaymentAmount, COUNT(*) AS Count';
  $sql .= ' FROM Users';
  $sql .= ' WHERE CanSignup="Paid"';
  $sql .= ' GROUP BY PaymentAmount';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for signup counts failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    $index = sprintf ('Paid $%d.00', $row->PaymentAmount / 100);
    $count[$index] = $row->Count;
    $paid[$index] = $row->PaymentAmount / 100;
  }

  // Now display totals by amount paid

  $total = 0;

  echo "<h2>Attendence by Payment</h2>\n";
  echo "<table>";
  echo "  <tr align=right>\n";
  echo "    <th align=left>Category</th>\n";
  echo "    <th>&nbsp;Count&nbsp;</th>\n";
  echo "    <th>\$ / Category</th>\n";
  echo "  </tr>\n";

  foreach ($count as $k => $v)
  {
    echo "  <tr align=right>\n";
    echo "    <td align=left>$k</td><td>&nbsp;$v&nbsp;</td>";
    printf ("<td>\$%d.00</td>\n", $v * $paid[$k]);
    $total += $v * $paid[$k];
    echo "  </tr>\n";
  }

  echo "  <tr align=right>\n";
  echo "    <th colspan=2>Total Paid</th>\n";
  echo "    <th>\$$total.00</th>\n";
  echo "  </tr>\n";
  echo "</table>\n";

  echo "<p>\n";
}
?>