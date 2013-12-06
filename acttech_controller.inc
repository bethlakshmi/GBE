<?php
include_once ("gbe_acttechinfo.inc");
include_once ("files.php");
include_once ("gbe_users.inc");

/* display_acttechinfo
 *
 * Display editing capabilities for act tech information, requires:
 *  ShowId = which show - this controls the display
 *  ActTechInfoId = if available, 0 if not
 *  BidId = optional, but required if ActTechId not available
 *  UserId = optional, but required if ActTectId not available
 * Returns = nothing
 */
function display_acttechinfo($errors, $ShowId, $ActTechInfoId, $BidId=0, $UserId=0, $editable=TRUE)
{
  
  // get the current or default value sets.
  $ActTechInfo = new ActTechInfo();

  if ($ActTechInfoId > 0)
    $ActTechInfo->load_from_acttechinfoid($ActTechInfoId);
  else if ($BidId > 0 && $UserId > 0)
    $ActTechInfo->load_bid_user($BidId, $UserId);
  else 
    return display_error("Can't find any baseline information.");
    
  // get the display settings for this show
  get_acttech_display_settings($ShowId, &$Settings);

  // if there's errors, this is a redo of a form, use the previous attempted data
  if (count($errors) > 0 )
  { 
    echo "Music Path: ".$_POST['MusicPath'];
    echo "Orig Music: ".$_POST['OrigMusicPath'];
    $values = $_POST;
  }
  // if there's no errors, pull from memory
  else 
    $values = $ActTechInfo->dump_to_array();
        
  echo "<TABLE BORDER=0>\n";
  $requiredmark = "<font color=\"red\">*</font>&nbsp;";
  
  foreach ($Settings as $setting)
  {
    show_field($setting, $values, $errors, $editable);
  }

  echo "</TABLE>\n";
  
  return;
}

/* show_field
 *
 * Display a field as defined by the settings of type ActTechDisplay and the current
 *  value as represented by a value in an array with index of $setting->ColumnName
 * Returns = nothing
 */
function show_field($setting, $values, $errors, $editable)
{
    // not used everywhere, but this makes it easy to reuse
    $requiredmark = "";
    if ($setting->Required)
      $requiredmark = "<font color=\"red\">*</font>&nbsp;";

    // if there is an error, make the display text red
    if (array_key_exists($setting->ColumnName, $errors))
      $setting->DisplayText = "<font color=\"red\">".$setting->DisplayText."</font>";

    // not used everywhere but created for consistency
    $text="";
    if ($setting->HelpText != NULL && $setting->HelpText != "none")   
    {
      $text = " <span class=\"dropt\" title=\"Help\">\n";
      $text .=  "<img src=\"moz-screenshot.png\" alt=\"Help\">\n";
      $text .=   "  <span style=\"width:200px;float:right;text-align:left;\">$setting->HelpText</span>\n";
      $text .=   "</span>\n";
    }

    if ($setting->On)
    {
      switch ($setting->Type)
      {
        case 'radio':
        
          echo "<tr valign=top><td align=right>$requiredmark$setting->DisplayText$text:</td>\n  <td>";
          $enum = get_enum_values("ActTechInfo",$setting->ColumnName);
          $n=0;
          echo "<TABLE BORDER=0>\n";

          foreach ($enum as $entry)
          {
            if ($n==0) echo "<tr>";
            echo "<td width=30%>";
            $checked = 0;
            if ($values[$setting->ColumnName] == $entry)
            {
               $checked = 1;
            }
            form_radio($setting->ColumnName,$entry,$checked, !$editable);
            echo $entry;
            echo "</td>\n";
            if ($n==2)
              echo "<td width=10%>&nbsp;</td></tr>\n";
            $n = ($n+1)%3;
          }
          if ($n !=0)
            echo "</tr>";
          echo "</TABLE>\n";
          break;
          
        case 'checkbox':
          echo "<tr><td align=right>$requiredmark$setting->DisplayText$text</td>\n";
		  echo "<td>";
          form_checkbox($setting->ColumnName,$values[$setting->ColumnName],1, !$editable);
          echo "</td></tr>";
          break;
          
        case 'textbox':
          form_text(60, $setting->DisplayText.$text, $setting->ColumnName,0,$setting->Required,$text, '',$values, !$editable);
          break;
          
        case 'textarea':
          if (!isset($values[$setting->ColumnName]) || $values[$setting->ColumnName] == "")
          {
          	//echo $setting->ColumnName.":  ".$values[$setting->ColumnName];
            $values[$setting->ColumnName] = "<font color=gray><i>".$setting->HelpText."</i></font>";
		  }
          form_textarea($setting->DisplayText.":", 
                        $setting->ColumnName,4, TRUE, $setting->Required, $values, !$editable);
          break;
          
        case 'pulldown':
          echo "<tr><td align=right>$requiredmark$setting->DisplayText$text:</td>\n";
		  echo "<td>";        
          $enum = get_enum_values("ActTechInfo",$setting->ColumnName);
          form_single_select("", $setting->ColumnName, $enum, $values[$setting->ColumnName], FALSE, !$editable);
          echo "</td></tr>";
          break;
          
        case 'file':
          echo "<tr><td colspan=2><br></td></tr>";
          if ($editable)
            form_upload ($setting->DisplayText.$text.":", $setting->ColumnName, $setting->Required,$twoCol=TRUE);
          if (isset($values[$setting->ColumnName]) && $values[$setting->ColumnName] !== '')
          {
            display_media_type($values[$setting->ColumnName]);
            form_hidden_value ('Orig'.$setting->ColumnName, $values[$setting->ColumnName]);
          }
          // if this is a repeat of an error form, then the file path from the database 
          // will be reposted with the Orig prefix in previous post
          elseif (isset($values["Orig".$setting->ColumnName]) && $values["Orig".$setting->ColumnName] !== '')
          {
            display_media_type($values["Orig".$setting->ColumnName]);
            form_hidden_value ('Orig'.$setting->ColumnName, $values["Orig".$setting->ColumnName]);
          }

          break;
          
        case 'time':
          form_text (2, $setting->DisplayText.$text, $setting->ColumnName.'Minutes', 0, 
                     $setting->Required, '(mm.ss)',$setting->ColumnName.'Seconds', $values, !$editable);
          break;
          
        case 'none':
          echo "<tr><td colspan=2>";
          echo "<H3>".$setting->DisplayText."</H3>";  
          if ($setting->HelpText != NULL && $setting->HelpText != "none")   
            echo $setting->HelpText."<br><br>";
          echo "</td></tr>";
          break;
          
        default:
          echo "<b>".$setting->DisplayText."</b> - ".$setting->HelpText."<br><br>";
      }
    }
}


/* update_acttechinfo
 *
 * Update the act tech info after a form is submitted.  Take the values as an array,
 * where the key is the ColumnName and the value is the value of the field.  And an Act 
 * class Act that is connected to this tech info.
 *  - makes a new ActTechInfo entry or updates the existing one.
 *  - checks that all fields that were required by the form are present
 *  - makes sure the values are within the required limites for each field
 *  - displays any errors
 * Returns = error array
 */
function update_acttechinfo($values, &$Act)
{
  $ActTechInfo = new ActTechInfo();
  $errors = array();

  // make sure that all required fields are filled in
  get_acttech_display_settings($Act->ShowId, &$Settings);
  foreach ($Settings as $setting)
  {
    $error = check_field($setting, $values);
    if (strlen($error) > 0)
      $errors[$setting->ColumnName]=$error;
  }

  if (count($errors) > 0)
  {
    foreach ($errors as $value)
      display_error($value);
  }
  else
  {
    // move the music file
    $file = "MusicPath";
    //echo "Extension: ".$_FILES[$file]["type"];
    if (validate_file($file))
    {
      $path2 = "";
      $path2 = process_file($file, "music", "Music-".$Act->ActId );

      if ( strpos($path2,FILE_UPLOAD_LOC) === FALSE )
      {
        $errors["MusicPath"]="file upload problem";
        display_error ("Error uploading the music file.  ".$path2);
        return $errors;
      }
    }
    else 
      $path2 = $_POST["OrigMusicPath"];
      
    // this is the thing to store now that music is uploaded
    $_POST['MusicPath'] = $path2;
    
    $errors = $ActTechInfo->convert_from_array($_POST);
    
    // if the act had an ActTechInfo before, overwrite it.
    if ($Act->ActInfoId > 0)
      $ActTechInfo->ActTechInfoId = $Act->ActInfoId;
    $ActTechInfo->ActId = $Act->ActId;

    // run through any errors
    if (count($errors) > 0)
      foreach ($errors as $value)
        display_error($value);
    else
    {
      $ActTechInfo->save_to_db();
      echo "Your Act's technical information has been updated.<br><br>Thank you for filling it out!<br><br>\n";
    }
    
    $Act->ActInfoId = $ActTechInfo->ActTechInfoId;

   }
 
  return $errors;

}

/* check_field
 *
 * Check that if the field is required, on, and not a checkbox, that the setting is 
 *   set.  Also - 
 *   If the field is a checkbox, make sure the value is 0 or 1
 *   If the field is a time, make sure there are minutes, seconds and that the values are
 *   0 to 60.
 *   If the field is a file, the file is valid.
 *   If the setting is NOT on, there should be no value!
 * Assumption is that any radio or pull down will be checked by the data transfer mechanism.
 * Returns = a string of errors separated by lines and line breaks.
 *   string is empty if there are no errors.
 *   string has a descriptive error line for every issue encountered.
 */
function check_field($setting, $values)
{
  $error = "";
  if ($setting->On && $setting->Type != "none")
  {
    if ($setting->Required && $setting->Type != "checkbox" && 
        $setting->Type != "time" && $setting->Type != "file" )
    {
      if (!isset($values[$setting->ColumnName]) || strlen($values[$setting->ColumnName]) == 0)
        $error .= "Choice for the required field - $setting->DisplayText - is missing.<br>\n";
    }
    
    if ($setting->Type == "checkbox" && isset($values[$setting->ColumnName]))
    {
      if ( $values[$setting->ColumnName] != 1 )
        $error .= "Choice for $setting->DisplayText is invalid.<br>\n";
    }
    
    if ($setting->Type == "time" )
    {
      if ($setting->Required)
      {
        // check minutes
        if (!isset($values[$setting->ColumnName."Minutes"]) || strlen($values[$setting->ColumnName."Minutes"]) == 0)
          $error .= "Minutes for $setting->DisplayText - are missing.<br>\n";
        // check seconds
        if (!isset($values[$setting->ColumnName."Seconds"]) || strlen($values[$setting->ColumnName."Seconds"]) == 0)
          $error .= "Seconds for $setting->DisplayText - are missing.<br>\n";
        // check minutes
        if ( $values[$setting->ColumnName."Minutes"] < 0 || $values[$setting->ColumnName."Minutes"] > 59)
          $error .= "Minutes for $setting->DisplayText - are invalid - please enter a value between 0 and 59.<br>\n";
        // check seconds
        if ( $values[$setting->ColumnName."Seconds"] < 0 || $values[$setting->ColumnName."Seconds"] > 59)
          $error .= "Seconds for $setting->DisplayText - are invalid - please enter a value between 0 and 59.<br>\n";
      }
    }
    if ($setting->Type == "file" && $setting->Required)
    {
      // check minutes
      if ( !validate_file($setting->ColumnName) && (!isset($values['Orig'.$setting->ColumnName])
             || strlen($values['Orig'.$setting->ColumnName]) ==0 ))
          $error .= "Required file for $setting->DisplayText - was not uploaded.<br>\n";
    }
  }
  else
  {
    if (isset($values[$setting->ColumnName]) && strlen($values[$setting->ColumnName]) > 0)
      $error .= "No value should be provided for $setting->ColumnName and yet a value has been provided.<br>\n";
  }
  
  return $error;
}

/* edit_act_info
 *
 * Display editing capabilities for act information, including:
 *   - choice of rehearsal slot
 *   - bio setup 
 *   - act tech data
 * Can be edited any time, up until the do-not-edit switch is pulled.
 *
 * Arguments - act_list - an array of class Act
 */

function edit_act_info($ActId, $ShowId, $errors)
{

  $Act = new Act();
  $Act->load_from_actid($ActId);

  echo "<h2>Manage Information for <i>".$Act->get_Title()."</i></h2>\n";
  echo "<font color=red>*</font> indicates a required field<br><br>\n";
  
  // check the switch for editable information.
  $editable = allowing_edit();
  if (!$editable)
    echo "<font color=red>It is too close to the show to edit your act information.  If this is an ".
      "emergency, please contact the stage manager or producer listed for the show.</font>";
  
  echo "<h3>Select Rehearsal Time</h3>\n";
  get_act_rehearsals($ShowId, &$Rehearsals);

  echo "<form method=\"POST\" action=\"ProcessAct.php\" enctype=\"multipart/form-data\">\n"; 
  form_hidden_value("ActId",$ActId);
  form_hidden_value("ShowId",$ShowId);
  display_rehearsal_options($Rehearsals, $Act->RehearsalId, $editable);
      

  if ($Act->isGroup)
  {
    echo "<h3>Provide Biography</h3>";
    echo "For group acts, we ask that you provide a bio as a group.<br>\n";
    edit_bio($Act->GroupBio, $Act->GroupName, $editable);
  }

  display_acttechinfo($errors, $Act->ShowId,$Act->ActInfoId,$Act->BidId, 
                      $_SESSION[SESSION_LOGIN_USER_ID], $editable);
  echo "<br><br><center>";
  if ($editable)
    form_submit("Submit Act Information",0);
  echo "</center><br><br>";
  echo "</form>\n";

  return;
}

/* display_rehearsal_options
 *
 * Display the list of act rehearsal slots, and let the user choose 
 *  any of the open slots or none at all.
 *
 * Arguments - act_list - an array of class Act,
 *             current = the RunId of the currently selected rehearsal for this act
 */

function display_rehearsal_options($Rehearsals, $current, $editable)
{
  echo "Choose an act rehearsal time from the list of times.  Rehearsal slots are 
    chosen on a first-come first serve basis.  Options which are greyed out are not 
    currently available.<br><br>\n";
  echo current($Rehearsals)->Event->Description."<br><br>\n";
  echo "<table border=0>\n";
  echo "<tr><td width=200px>Select Rehearsal Time:</td><td colspan=2>&nbsp;</td></tr>";
  foreach ($Rehearsals as $slot)
  { 
    if ($editable || $current == $slot->RunId)
    {
      echo "  <tr><td>&nbsp;</td><td>";
      $color = "black";
      $disabled = 0;
      $checked = 0;
    
      if (act_rehearsal_full($slot->RunId, $slot->Event->MaxPlayersNeutral))
      {
        $disabled = 1;
        $color = "grey";
      }

      if ($current == $slot->RunId)
        $checked=TRUE;

      form_radio('RunId',$slot->RunId, $checked, $disabled);
      echo "    </td>\n    <td><font color=$color>";
      echo $slot->Day.", ".start_hour_to_am_pm($slot->StartHour);
      echo "-".start_hour_to_am_pm($slot->StartHour + $slot->Event->Hours)."</font><br>";
      echo "  </td></tr>\n";
    }
  }
 if ($editable)
 {
    echo "  <tr><td>&nbsp;</td><td>";
  
    // this is always an option
    if ($current == -1)
      $checked=TRUE;
    else
      $checked=FALSE;
    form_radio('RunId',-1, $checked);
  
    echo "    </td>\n<td>";
    echo "<b>I/We will not be at tech rehearsal.</b> I/We understand that ";
    echo "The Expo's official position is that this is a really, really, really bad idea.";
    echo "  </td></tr>\n";
  }
  echo "</table>\n";
}

/* process_rehearsal_choice
 *
 * Process the choice for rehearsal time - sign up all act performers, and update
 * the act.  It will withdraw the user from any prior rehearsal time.  It will display 
 * errors if any user is being overbooked.
 *
 * NOTE - assumes that the Act will be persisted later.
 * Arguments - RunId - the new Rehearsal Run,
 *             Act = the act that will be rehearsed.
 */

function process_rehearsal_choice($RunId, &$Act)
{
  if ($Act->RehearsalId == $RunId)
  {
    echo "There is no change to the rehearsal time.<br>\n";
    return;
  }
  else
  {
    get_users_for_act($Act->ActId, &$user_list);
  
    // there was a previous, different slot, remove users
    if ($Act->RehearsalId > 0 )
    {
      foreach ($user_list as $id => $name)
      {
        $run = new Run();
        $run->RunId = $Act->RehearsalId;
        $array = array();
        $array[0] = $run;
        $status = unbook_user($array, $id);
        if ($status == SIGNUP_FAIL)
          display_error("We encountered a problem removing the original act rehearsal time ".
            "for $name.  Please contact the show producer or stage manager and let them know.");
      }
    
      echo "Your act has been removed from the previous act rehearsal slot.<br>\n";
    }
  
    // book users for this run and set it in the Act 
    if ($RunId > 0)
    {
      reset($user_list);
  
      $run = new Run();
      $run->load_from_runid($RunId);
      $array = array();
      $array[0] = $run;

      foreach ($user_list as $id => $name)
      {
        $status = book_user($array, $id);
        if ( $status == SIGNUP_FAIL)
          display_error("We encountered a problem booking rehearsal time for $name. ".
            "Make sure this person's calendar is clear at this time and try again.  ".
            "If a problem persists, please contact our webmistress.");
      }
      
      echo "Your act has been scheduled for rehearsal at ".
        start_hour_to_am_pm($run->StartHour).".<br>\n";
    }
    else
      echo "No rehearsal slot selected - NOTE:  This is not a recommended option.<br>/n";
      
    $Act->RehearsalId = $RunId;
  
  }
}
/* show_act_tech_reminder
 *
 * if the user is performing in an act and the act does not have a tech form filled out
 *  send them an annoying message.
 */

function show_act_tech_reminder()
{
  if (!user_is_performer())
    return;
    
  $techInfoDone = TRUE;

  get_acts_for_user($_SESSION[SESSION_LOGIN_USER_ID], $act_list);
  if ( count($act_list) > 0)
  {
    foreach($act_list as $Act)
    {
      if ($Act->ActInfoId == 0)
        $techInfoDone = FALSE;
    }
  }
  if (!$techInfoDone)
  {
    echo "<br><font color=red>REMINDER!</font> You have not filled out your technical ";
    echo "info for an act in one of the shows at the Expo.  Please go to ";
    echo "<a href=ManageAct.php>Manage My Acts</a> and fill it out.<br>";
  }
}

/* run_unit_tests_acttech_controller
 *
 * Place to collect unit tests for this controller.
 *
 */

function run_unit_tests_acttech_controller()
{
  $ActTechDisplay = new ActTechDisplay();
  $ActTechDisplay->On = 1;
  $ActTechDisplay->Required=1;
  $ActTechDisplay->Type = "text";
  $ActTechDisplay->ColumnName = "value";
  $ActTechDisplay->DisplayText = "Display Text";
  $values["value"]="text examples";

  // full positive case
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be empty: ".$error."||<br>\n";
  
  // * Check that if the field is required, on, and not a checkbox, that the setting is 
  // *   set.  
  $ActTechDisplay->ColumnName = "novalue";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present - required value absent: ".$error."<br>\n";

  // *   If the setting is NOT on, there should be no value!
  $ActTechDisplay->On = 0;
  $ActTechDisplay->ColumnName = "value";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present - setting is off, value is on: ".$error."<br>\n";

  // *   If the field is a checkbox, make sure the value is 0 or 1
  $ActTechDisplay->On = 1;
  $ActTechDisplay->Type = "checkbox";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present, checkbox test, value = ".$values[$ActTechDisplay->ColumnName].": ".$error."<br>\n";

  // *   If the field is a time, make sure there are minutes, seconds and that the values are
  // *   0 to 60.
  $ActTechDisplay->Type = "time";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present, time test, no value: ".$error."<br>\n";

  $values["valueMinutes"]="-1";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present, time test, bad minutes - minutes are ".(int)$values["valueMinutes"]." : ".$error."<br>\n";

  $values["valueMinutes"]="59";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present, time test, no seconds: ".$error."<br>\n";

  $values["valueSeconds"]="65";
 $error = check_field($ActTechDisplay, $values);
  echo "Error should be present, time test, bad seconds: ".$error."<br>\n";

  $values["valueSeconds"]="0";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be empty: ".$error."||<br>\n";

  // *   If the field is a file, the file is valid.
  $ActTechDisplay->Type = "file";
  $error = check_field($ActTechDisplay, $values);
  echo "Error should be present, the file upload is not good: ".$error."<br>\n";

}


?>
