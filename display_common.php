<?php

/*
 *   Common functions used to show data on both Acts.php and Bids.php, and
 *    others as necessary.
 *
 */

/*
 * show_text
 *
 * Display text in a two column form
 */

function show_text ($display, $value)
{
  echo "  <TR VALIGN=TOP>\n";
  echo "    <TD ALIGN=RIGHT NOWRAP><B>$display:</B></TD><TD ALIGN=LEFT>$value</TD>\n";
  echo "  </tr>\n";
}

function show_players ($array, $onlyTotal=0)
{
  $text = "<TABLE BORDER=1>\n";
  $text .= "  <TR ALIGN=CENTER>\n";
  $text .= "    <TD></TD>\n";
  $text .= "    <TD>Minimum</TD>\n";
  $text .= "    <TD>Preferred</TD>\n";
  $text .= "    <TD>Maximum</TD>\n";
  $text .= "  </tr>\n";
  if (!$onlyTotal)
  {
    $text .= "  <TR ALIGN=CENTER>\n";
    $text .= "    <TD ALIGN=RIGHT>Male</TD>\n";
    $text .= "    <TD>" . $array["MinPlayersMale"] . "</TD>\n";
    $text .= "    <TD>" . $array['PrefPlayersMale'] . "</TD>\n";
    $text .= "    <TD>" . $array['MaxPlayersMale'] . "</TD>\n";
    $text .= "  </tr>\n";

    $min = $array['MinPlayersMale'];
    $pref = $array['PrefPlayersMale'];
    $max = $array['MaxPlayersMale'];

    $text .= "  <TR ALIGN=CENTER>\n";
    $text .= "    <TD ALIGN=RIGHT>Female</TD>\n";
    $text .= "    <TD>" . $array['MinPlayersFemale'] . "</TD>\n";
    $text .= "    <TD>" . $array['PrefPlayersFemale'] . "</TD>\n";
    $text .= '    <TD>' . $array['MaxPlayersFemale'] . "</TD>\n";
    $text .= "  </tr>\n";

    $min += $array['MinPlayersFemale'];
    $pref += $array['PrefPlayersFemale'];
    $max += $array['MaxPlayersFemale'];

    $text .= "  <TR ALIGN=CENTER>\n";
    $text .= "    <TD ALIGN=RIGHT>Neutral</TD>\n";
    $text .= "    <TD>" . $array['MinPlayersNeutral'] . "</TD>\n";
    $text .= "    <TD>" . $array['PrefPlayersNeutral'] . "</TD>\n";
    $text .= "    <TD>" . $array['MaxPlayersNeutral'] . "</TD>\n";
    $text .= "  </tr>\n";
  }

  $min += $array['MinPlayersNeutral'];
  $pref += $array['PrefPlayersNeutral'];
  $max += $array['MaxPlayersNeutral'];

  $text .= "  <TR ALIGN=CENTER>\n";
  $text .= "    <TD ALIGN=RIGHT>Total</TD>\n";
  $text .= "    <TD>$min</TD>\n";
  $text .= "    <TD>$pref</TD>\n";
  $text .= "    <TD>$max</TD>\n";
  $text .= "  </tr>\n";

  $text .= "</TABLE>";

  show_text ('Capacity', $text);
}

function show_section ($text)
{
  echo "  <TR>\n";
  echo "    <TD COLSPAN=2><FONT SIZE=\"+1\"><HR><B>$text</B></FONT></TD>\n";
  echo "  </tr>\n";
}

function show_table_entry ($text)
{
  if ('' == $text)
     $text = '&nbsp;';

  echo "          <TD>$text</TD>\n";
}


?>
