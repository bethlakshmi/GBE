<?php

include ("gbe_acts.inc");

/* function get_acts_for_user
 * 
 * Function retrieves the accepted acts for a given user.  Act must
 * be cast in a show to show up here.
 *
 * $User - unique ID number for the user, primary key of user table
 * $act_list - the list of accepted acts that this user is in.
 * Returns: act_list as reference.
 */
function get_acts_for_user($UserId, &$act_list)
{

  //Get the Act Ids from GM table, join with act info on Acts table
  $sql = 'SELECT GMs.EventId, Acts.ActId, Acts.BidId, Acts.ShowId, Acts.RehearsalId,';
  $sql .=' Acts.ActInfoId, Acts.GroupBio, Acts.isGroup FROM GMs, Acts WHERE UserId=' . $UserId;
  $sql .= ' and Role = "performer" and GMs.EventId = Acts.ActId';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for UserId $UserId failed");

  $n=0;
  while ($row = mysql_fetch_object($result)) {
    $act = new Act();
    $act->convert_from_sql_row($row);
    $act_list[$n] = $act;
    $n++;

  }
}

/* function get_conf_items_for_user
 * 
 * Function retrieves the accepted conference items for a given user.  
 * Conference item must be accepted and user must be listed as teacher or
 * moderator.
 *
 * $User - unique ID number for the user, primary key of user table
 * $event_list - the list of accepted items that this user can edit.
 * Returns: event_list as reference.
 */
function get_conf_items_for_user($UserId, &$event_list)
{
  //Get the Event Ids from GM table
  $sql = 'SELECT EventId FROM GMs WHERE UserId=' . $UserId;
  $sql .= ' and (Role = "moderator" or Role = "teacher")';
  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ("Query for UserId $UserId failed");

  $n=0;
  while ($row = mysql_fetch_assoc($result)) {
    $event_list[$n] = $row['EventId'];
    $n++;
  }
}

/*
 * user_is_gm_for_game
 *
 * Returns true if the user is a GM for the specified game.
 */

function user_is_gm_for_game ($UserId, $EventId)
{
  // If the user isn't logged in, then they're not a GM, are they?

  if (0 == $UserId)
    return false;

  // Query the database to see if the user is GM

  $sql = "SELECT GMId FROM GMs WHERE UserId=$UserId AND EventId=$EventId";
  $sql .= '  LIMIT 1';

  //  echo "Query: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for GMs failed');

  $num = mysql_num_rows ($result);
  mysql_free_result ($result);

  return $num != 0;
}

/*
 * user_is_moderator
 *
 * Returns true if the user is a moderator for the specified panel.
 */

function user_is_moderator ($UserId, $EventId)
{
  // If the user isn't logged in, then they're not a GM, are they?

  if (0 == $UserId)
    return false;

  // Query the database to see if the user is GM

  $sql = "SELECT GMId FROM GMs WHERE UserId=$UserId AND EventId=$EventId";
  $sql .= ' and Role="moderator" LIMIT 1';

  //  echo "Query: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for moderator failed');

  $num = mysql_num_rows ($result);
  mysql_free_result ($result);

  return $num != 0;
}

/*
 * user_is_teacher
 *
 * Returns true if the user is a teacher for the specified class.
 */

function user_is_teacher ($UserId, $EventId)
{
  // If the user isn't logged in, then they're not a GM, are they?

  if (0 == $UserId)
    return false;

  // Query the database to see if the user is GM

  $sql = "SELECT GMId FROM GMs WHERE UserId=$UserId AND EventId=$EventId";
  $sql .= ' and Role="teacher" LIMIT 1';

  //  echo "Query: $sql<p>\n";

  $result = mysql_query ($sql);
  if (! $result)
    return display_mysql_error ('Query for teacher failed');

  $num = mysql_num_rows ($result);
  mysql_free_result ($result);

  return $num != 0;
}


?>
