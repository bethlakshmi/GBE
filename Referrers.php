<?php
define (SORT_BY_HOST, 0);
define (SORT_BY_REFS, 1);
define (SORT_BY_LAST_REF_DATE, 2);
define (SORT_BY_NEW, 3);
define (SORT_BY_LAST_NEW_DATE, 4);

include ("intercon_db.inc");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to connect to ' . DB_NAME);
  exit ();
}

// Display boilerplate

html_begin ();

// Only folks with ConCom privs may access these pages

if (! user_has_priv (PRIV_CON_COM))
{
  display_access_error ();
  html_end ();
  exit ();
}

// Figure out what we're supposed to do

if (array_key_exists ('action', $_REQUEST))
  $action = $_REQUEST['action'];
else
  $action = VIEW_REFERRERS;

switch ($action)
{
  case VIEW_REFERRERS:
    view_referrers ();
    break;

  case SUMMARIZE_REFERRERS:
    summarize_referrers ();
    break;

  default:
    display_error ("Unknown action code: $action");
}

// Standard postamble

html_end ();

/*
 * view_referrers
 *
 * Show the list of referring sites
 */

function view_referrers ()
{
  echo "<H1>Referring Sites</H1>\n";

  // Gather the referring sites

  $sql = 'SELECT Referrers.Url, Referrers.UserId, Referrers.NewUser,';
  $sql .= ' DATE_FORMAT(Referrers.AtSite,"%d-%b-%Y %T") AS Date,';
  $sql .= ' Users.FirstName, Users.LastName';
  $sql .= ' FROM Referrers, Users';
  $sql .= ' WHERE Users.UserId=Referrers.UserId';
  $sql .= ' ORDER BY ReferrerId DESC';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for referring sites failed', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo "There are no records for referring sites.\n";
    return true;
  }

  echo "<TABLE BORDER=1>\n";
  while ($row = mysql_fetch_object ($result))
  {
    if (1 == $row->NewUser)
      $bgcolor = 'BGCOLOR="#CCFFCC"';
    else
      $bgcolor = '';

    echo "  <TR VALIGN=TOP $bgcolor>\n";
    echo "    <TD NOWRAP>$row->Date</TD>\n";
    if (1 == $row->UserId)
      $name = '&lt;Unknown&gt;';
    else
      $name = trim ("$row->FirstName $row->LastName");
    echo "    <TD>$name</TD>\n";
    echo "    <TD><A HREF=$row->Url>$row->Url</A></TD>\n";
    echo "  </TR>\n";
  }
  echo "</TABLE>\n";

  return true;
}

/*
 * summarize_referrers
 *
 * Show the list of referring sites
 */

function summarize_referrers ()
{
  echo "<h1>Referring Sites Summary</h1>\n";

  if (array_key_exists ('SortBy', $_REQUEST))
    $sort_by = intval (trim ($_REQUEST['SortBy']));
  else
    $sort_by = SORT_BY_REFS;

  $references = array();    // Count of references
  $new_users = array ();    // Count of new users who've signed up
  $last_ref = array ();     // Last date we were referenced through this site
  $last_new_user = array ();// Last date we got a new user through this site

  // Gather the referring sites

  $sql = 'SELECT Referrers.Url, Referrers.UserId, Referrers.NewUser,';
  $sql .= ' Referrers.AtSite,';
  $sql .= ' Users.FirstName, Users.LastName';
  $sql .= ' FROM Referrers, Users';
  $sql .= ' WHERE Users.UserId=Referrers.UserId';
  $sql .= ' ORDER BY ReferrerId DESC';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for referring sites failed', $sql);

  if (0 == mysql_num_rows ($result))
  {
    echo "There are no records for referring sites.\n";
    return true;
  }

  while ($row = mysql_fetch_object ($result))
  {
    $a = parse_url ($row->Url);
    //    dump_array ($row->Url, $a);

    if (! array_key_exists ('host', $a))
    {
      //      echo "No host: $row->Url<br>\n";
      continue;
    }

    $host = $a['host'];

    if (! array_key_exists ($host, $references))
    {
      $references[$host] = 1;
      $new_users[$host] = $row->NewUser;
      $last_ref[$host] = $row->AtSite;
      if ($row->NewUser)
	$last_new_user[$host] = $row->AtSite;
      else
	$last_new_user[$host] = 0;
    }
    else
    {
      $references[$host]++;
      $new_users[$host] += $row->NewUser;
      if ($row->AtSite > $last_ref[$host])
	$last_ref[$host] = $row->AtSite;
      if ($row->NewUser)
      {
	if ($row->AtSite > $last_new_user[$host])
	  $last_new_user[$host] = $row->AtSite;
      }
    }
  }

  echo "<table border=1>\n";
  echo "  <tr>\n";
  printf ("    <th align=left><a href=Referrers.php?action=%d&SortBy=%d>%s</a></th>\n",
	  SUMMARIZE_REFERRERS,
	  SORT_BY_HOST,
	  'Host');
  printf ("    <th><a href=Referrers.php?action=%d&SortBy=%d>%s</a></th>\n",
	  SUMMARIZE_REFERRERS,
	  SORT_BY_REFS,
	  'Refs');
  printf ("    <th><a href=Referrers.php?action=%d&SortBy=%d>%s</a></th>\n",
	  SUMMARIZE_REFERRERS,
	  SORT_BY_LAST_REF_DATE,
	  'Last Reference');
  printf ("    <th><a href=Referrers.php?action=%d&SortBy=%d>%s</a></th>\n",
	  SUMMARIZE_REFERRERS,
	  SORT_BY_NEW,
	  'New Users');
  printf ("    <th><a href=Referrers.php?action=%d&SortBy=%d>%s</a></th>\n",
	  SUMMARIZE_REFERRERS,
	  SORT_BY_LAST_NEW_DATE,
	  'Last New User');

  switch ($sort_by)
  {
    case SORT_BY_HOST:
      ksort ($references);
      reset ($references);
      foreach ($references as $k => $v)
      {
	echo "  <tr align=center>\n";
	printf ("    <td align=left>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>\n",
		$k,
		$v,
		timestamp_to_datetime ($last_ref[$k]),
		$new_users[$k],
		timestamp_to_datetime ($last_new_user[$k]));
	echo "  </tr>\n";
      }
      break;

    case SORT_BY_REFS:
      arsort ($references);
      reset ($references);
      foreach ($references as $k => $v)
      {
	echo "  <tr align=center>\n";
	printf ("    <td align=left>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>\n",
		$k,
		$v,
		timestamp_to_datetime ($last_ref[$k]),
		$new_users[$k],
		timestamp_to_datetime ($last_new_user[$k]));
	echo "  </tr>\n";
      }
      break;

    case SORT_BY_LAST_REF_DATE:
    dump_array ('Before sort', $last_ref);
      arsort ($last_ref);
    dump_array ('After sort', $last_ref);
      reset ($last_ref);
      foreach ($last_ref as $k => $v)
      {
	echo "  <tr align=center>\n";
	printf ("    <td align=left>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>\n",
		$k,
		$references[$k],
		timestamp_to_datetime ($last_ref[$k]),
		$new_users[$k],
		timestamp_to_datetime ($last_new_user[$k]));
	echo "  </tr>\n";
      }
      break;

    case SORT_BY_NEW:
      arsort ($new_users);
      reset ($new_users);
      foreach ($new_users as $k => $v)
      {
	echo "  <tr align=center>\n";
	printf ("    <td align=left>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>\n",
		$k,
		$references[$k],
		timestamp_to_datetime ($last_ref[$k]),
		$new_users[$k],
		timestamp_to_datetime ($last_new_user[$k]));
	echo "  </tr>\n";
      }
      break;

    case SORT_BY_LAST_NEW_DATE:
      arsort ($last_new_user);
      reset ($last_new_user);
      foreach ($last_new_user as $k => $v)
      {
	echo "  <tr align=center>\n";
	printf ("    <td align=left>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>" .
		"<td align>%s</td>\n",
		$k,
		$references[$k],
		timestamp_to_datetime ($last_ref[$k]),
		$new_users[$k],
		timestamp_to_datetime ($last_new_user[$k]));
	echo "  </tr>\n";
      }
      break;
  }

  echo "</table>\n";

  return true;
}
/* Moved to intercon_db.inc
function timestamp_to_datetime ($t)
{
  // If there is no timestamp, return the non-break space

  if (0 == $t)
    return '&nbsp;';

  // Break the timestamp into it's components; YYYYMMDDhhmmss

  $year = substr ($t, 0, 4);
  $month = substr ($t, 4, 2);
  $day = substr ($t, 6, 2);
  $hour = substr ($t, 8, 2);
  $min = substr ($t, 10, 2);

  switch (intval ($month))
  {
    case  1: $month = 'Jan'; break;
    case  2: $month = 'Feb'; break;
    case  3: $month = 'Mar'; break;
    case  4: $month = 'Apr'; break;
    case  5: $month = 'May'; break;
    case  6: $month = 'Jun'; break;
    case  7: $month = 'Jul'; break;
    case  8: $month = 'Aug'; break;
    case  9: $month = 'Sep'; break;
    case 10: $month = 'Oct'; break;
    case 11: $month = 'Nov'; break;
    case 12: $month = 'Dec'; break;
  }

  return "$day-$month-$year $hour:$min";
}
*/
?>