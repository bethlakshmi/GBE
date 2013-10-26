<?php
include ("intercon_db.inc");
include ("files.php");

// Connect to the database

if (! intercon_db_connect ())
{
  display_mysql_error ('Failed to establish connection to the database');
  exit ();
}

/* function get_who_is_who_for_show
 * 
 * Gets bio information for a list of users based on the 
 * show they are in.
 *
 * $showId - the show that the users are cast in 
 * $bio_users - the list of Users to display.  Formatted as an array
 *  with the key of a bioId in the Bios table, and Display Name or GroupName as the value
 * Returns: none
 */
function get_who_is_who_for_show ($showId)
{
    $sql = "SELECT DISTINCT Users.DisplayName AS Name, Bios.BioText , Bios.Title , "
    . "Bios.Website, Bios.PhotoSource, Acts.isGroup\n"
    . "FROM Acts\n"
    . "JOIN GMs on Acts.ActId = GMs.EventId AND GMs.Role = 'performer'\n"
    . "JOIN Users on GMs.UserId = Users.UserId\n"
    . "LEFT JOIN Bios on GMs.UserId = Bios.UserId\n"
    . "WHERE Acts.ShowId =$showId\n"
    . "AND Acts.isGroup =0";    
    $sql .= ' UNION ';
    $sql .= "SELECT DISTINCT Acts.GroupName as Name, Bios.BioText , Bios.Title , Bios.Website, Bios.PhotoSource, Acts.isGroup FROM Acts\n"
    . "LEFT JOIN Bios on Acts.GroupBio = Bios.BioId\n"
    . "WHERE Acts.ShowId = $showId AND Acts.isGroup = 1 ORDER BY Name LIMIT 0, 30 ";

// echo $sql."<br><br>";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for '.$type.' failed', $sql);
    

  echo "<table align=center border=1>\n";
  $n=0;
  $num_rows = 3;
  while ($row = mysql_fetch_object ($result))
  {
    if ($n==0)
      echo "<tr><td align=center>";
    else 
      echo "</td>\n<td align=center>";

    show_user_profile_with_thumbnail($row->Name,$row->Title,$row->Website,$row->BioText,
    	$row->PhotoSource);

    if ($n==$num_rows-1)
      echo "</td></tr>\n";

    $n = ($n+1) % $num_rows;
    	
    	
  }
  echo "</table>\n";

}

/* function get_who_is_who
 * 
 * Gets bio information for a list of users based on the 
 * type passed to it.
 *
 * $type - focus for the display, ie, Staff, Conference, Shows 
 * $bio_users - the list of Users to display.  Formatted as an array
 *  with the key of a userid in the Users table, and Display Name as the value
 * Returns: none
 */
function get_who_is_who ($type, &$bio_users)
{
  echo "<H1>Who's Who at " . CON_NAME . "</H1>\n";
  $sql;
  
  switch ($type)
  {

   case "Conference":
    // The Confernce is all teachers, panelists and moderators
    $sql = 'SELECT DISTINCT Users.UserId, Users.DisplayName ';
    $sql .= ' FROM GMs, Users';
    $sql .= ' WHERE Users.UserId=GMs.UserId and ';
    $sql .= ' ( Role="teacher" or Role="panelist" or Role="moderator" ) ';
    $sql .= ' ORDER BY Users.DisplayName ';
    break;
  case "Staff":
    // Now add the con staff.  Don't forget to skip Admin (UserId==1)

    $sql = 'SELECT UserId, DisplayName';
    $sql .= ' FROM Users';
    $sql .= ' WHERE ""<>Priv';
    $sql .= ' ORDER BY Users.DisplayName ';
    break;
  case "AllShows":
    // All Shows is every *person* listed as a performer.  
    // That's cause this is is the easy way.
    $sql = 'SELECT DISTINCT Users.UserId, Users.DisplayName ';
    $sql .= ' FROM GMs, Users';
    $sql .= ' WHERE Users.UserId=GMs.UserId and ';
    $sql .= ' ( Role="performer" ) ';
    $sql .= ' ORDER BY Users.DisplayName ';
    break;
  default:
    display_error("Who's Who category is unavailable or unrecognized.");
    return;
  }


  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for '.$type.' failed', $sql);

  while ($row = mysql_fetch_object ($result))
  {
    $bio_users[$row->UserId] = "$row->DisplayName";
  }

  
}

/* function display_who_is_who
 * 
 * Displays bio information for a list of users based on the 
 * list passed to it.
 *
 * $type - focus for the display, ie, Staff, Conference, Shows 
 * $bio_users - the list of Users to display.
 * Returns: none
 */
function display_who_is_who($type, &$bio_users)
{ 
  // Sort the array BY THE VALUE (as opposed to the key)

  uasort ($bio_users, "strcasecmp");
  reset ($bio_users);

  // Display who's who at Intercon

  $user_count = 0;

  foreach ($bio_users as $user_id => $name)
  {
    
    if (0 != $user_count++)
      echo "<center><hr width=\"50%\"></center>\n";
    echo "<p>";

    // Gather information from the Bios table, if it's available
    $Bio = '';
    $Title = '';
    $website = '';
    $photo = '';

    $sql = "SELECT * FROM Bios WHERE UserId=$user_id";
    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Query for bio failed', $sql);

    $row = mysql_fetch_object ($result);
    if ($row)
    {

      if ('' != $row->Title)
 	    $Title = $row->Title;

      $Bio = $row->BioText;
      $website = $row->Website;
      $photo = $row->PhotoSource;

    }

    display_header ($name);
    
    // only people with fixed privileges get titles.  This is 
    // set by the user in the bio if the user has permission to set it.
    if ('' != $Title)
      echo "$Title<br>\n";

    if ($type=="Conference")
    {
      // Now add any games the user is a GM for

      $sql = 'SELECT Events.Title FROM GMs, Events';
      $sql .= ' WHERE Events.EventId=GMs.EventId';
      $sql .= "   AND GMs.UserId=$user_id";
      $sql .= '   AND GMs.DisplayAsGM="Y"';
      $sql .= '   AND Events.IsConSuite="N"';
      $sql .= '   AND Events.IsOps="N"';

      $result = mysql_query ($sql);
      if (! $result)
        display_mysql_error ('Query for events failed', $sql);

      if (mysql_num_rows ($result) > 0)
      {
        echo "Presenting: ";
        $games = 0;

        while ($row = mysql_fetch_object ($result))
        {
	      if ($games++ > 0)
	        echo ', ';
	      echo "<i>$row->Title</i>";
        }
        echo "<br>\n";
      }
    }
    


    if (!($website == '' && $Bio == '' && $photo == ''))
      show_user_homepage_bio_info ($website, $Bio, $photo);

  }
}

/*
 * show_user_homepage_bio_info
 *
 * This function fetches the user's bio, and returns false if the user is
 * not expected to enter one
 */

function show_user_homepage_bio_info ($website="", $bio_text="", $photo="")
{
  // if it's for this user
  if ($website=="" && $bio_text=="" && $photo=="")
  {

    // All GMs are expected to enter a bio, as are all privileged users
    $should_enter_bio = user_is_gm () ||
                      (',,' != $_SESSION[SESSION_LOGIN_USER_PRIVS]);

    // If this user isn't expected to enter a bio, return now
    if (! $should_enter_bio)
      return;

    // If this user has NOT entered a bio, then issue a warning.

    $sql = 'SELECT Title, BioText, Website, PhotoSource FROM Bios WHERE UserId=';
    $sql .= $_SESSION[SESSION_LOGIN_USER_ID];

    $result = mysql_query ($sql);
    if (! $result)
      return display_mysql_error ('Cannot query bio information');

    $row = mysql_fetch_object ($result);

    if (! $row)
    {
      $bio_text = '';
      $title = '';
    }
    else
    {
      $bio_text = $row->BioText;
      $title = $row->Title;
    }
    echo "<br><br>";
    display_header ('Bio');
    if ('' != $title)
        echo "Title(s): <I>$title</I><P>\n";
  
    if ('' != $row->PhotoSource)
 		$photo = $row->PhotoSource;
  
    if ('' != $row->Website)
        $website = $row->Website;

    if ('' == $bio_text)
    {
      echo "<p><font color=\"red\">No bio text found.</font>  ";
      printf ("Click <A HREF=index.php?action=%d>Edit My Bio</A> to enter" .
	    " biographical information.  Bios are due by" .
	    " <b><font color=red>%s</font></b></p>.\n",
	    EDIT_BIO,
	    BIO_DUE_DATE);
	}
  } // if we had to grab user info

    
  // now display it
  display_photo($photo);
  echo "<BR><b>Website:</b> <a href=\"http://$website\">$website</a></br></br>\n";
  echo "$bio_text\n";


}

/*
 * show_user_profile_with_thumbnail
 * Displays a user profile as a thumbnail with a mouseover.
 */
function show_user_profile_with_thumbnail($name,$title,$website,$bioText,$photoSource)
{
  echo "<span class=\"dropt\" title=\"$name\">\n";
  echo "<figure>\n";
  display_thumbnail($photoSource);
  echo "   <figcaption>$name</figcaption>\n";
  echo "</figure>\n";

  echo "  <span>\n";
    display_header ($name);
    if ($title != NULL)
      echo "$title<br>\n";
    echo "<div align=left>";
    if (!($website == NULL && $bioText == NULL && $photoSource == NULL))
      show_user_homepage_bio_info ($website, $bioText, $photoSource);
    echo "</div>";
  echo "  </span>";
  echo "</span>";
  echo "<br><br>";
}
?>