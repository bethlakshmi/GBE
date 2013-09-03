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

DisplayContactsPage ();

// Add the postamble

html_end ();

function DisplayContact ($title, $name, $email)
{
  if ('' == $name)
    $name = NAME_CON_CHAIR;
  if ('' == $email)
    $email = EMAIL_CON_CHAIR;

  $email = mailto_or_obfuscated_email_address ($email);

  echo "  <tr valign=top align=left bgcolor=white>\n";
  echo "    <th>$title</th>\n";
  echo "    <td>$name</td>\n";
  echo "    <td>$email</td>\n";
  echo "  </tr>\n";
}

function DisplayContact2 ($title, $name1, $email1, $name2, $email2)
{
  $email1 = mailto_or_obfuscated_email_address ($email1);
  if ('' != $email2)
      $email2 = mailto_or_obfuscated_email_address ($email2);

  echo "  <tr valign=top align=left bgcolor=white>\n";
  echo "    <th>$title</th>\n";
  echo "    <td>$name1<br>$name2</td>\n";
  echo "    <td>$email1<br>$email2</td>\n";
  echo "  </tr>\n";
}

/*
 * DisplayContactsPage
 *
 * Display the contacts for this con
 */

function DisplayContactsPage ()
{
  echo "<h3>Intercon Contacts</h3>\n";
  echo "<p>\n";
  echo "The following people are in charge of various aspects of the\n";
  echo CON_NAME . " convention:\n";
  echo "<p>\n";
  echo "<table cellspacing=2 cellpadding=5 bgcolor=#4b067a>\n";
  echo "  <tr valign=top align=left bgcolor=white>\n";
  echo "    <th rowspan=2>Producer</th>\n";
  printf ("    <td>%s</td><td>%s</td>\n",
	  NAME_CON_CHAIR,
	  mailto_or_obfuscated_email_address (EMAIL_CON_CHAIR));
  echo "  </tr>\n";
  echo "  <tr bgcolor=white>\n";
  printf ("    <td colspan=2>%s</td>\n", ADDR_CON_CHAIR);
  echo "  </tr>\n";

  DisplayContact ('Advertising/Sponsorships',   NAME_ADVERTISING,   EMAIL_ADVERTISING);
  DisplayContact ('Conference Coordinator',     NAME_BID_CHAIR,     EMAIL_BID_CHAIR);
  DisplayContact ('Merch Table',     NAME_MERCH,     EMAIL_MERCH);
  DisplayContact ('Registration',
		                 NAME_REGISTRAR,     EMAIL_REGISTRAR);
  DisplayContact ('Security',     NAME_SECURITY,     EMAIL_SECURITY);
  DisplayContact ('Selection Committee',     NAME_SHOW_CHAIR,     EMAIL_SHOW_CHAIR);
  DisplayContact ('Technical Directory', NAME_TECH_DIR, EMAIL_TECH_DIR);
  DisplayContact ('Vendor Coordinator',       NAME_VENDOR_LIAISON,EMAIL_VENDOR_LIAISON);
  DisplayContact ('Volunteer Coordinator', NAME_VOLUNTEER_COORD, EMAIL_VOLUNTEER_COORD);
  DisplayContact ('Costume Exhibit/Fashion Show', NAME_COSTUME,EMAIL_COSTUME);
  DisplayContact ('Art Show', NAME_ART,EMAIL_ART);
  echo "</table>\n";
  echo "<p>\n";
}
?>