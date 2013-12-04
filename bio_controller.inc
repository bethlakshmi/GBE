<?php
include_once ("gbe_users.inc");
include_once ("files.php");

/* edit_bio
 *
 * Display editing capabilities for bio information, including:
 *    $BioId = for an existing bio, 0 to create a new one
 *    $isGroup = if this is a group bio, not an individual
 *    $editable = switch to turn off editing capabilities 
 * Returns = nothing
 */
function edit_bio($BioId=0, $GroupName="", $editable=TRUE)
{
  // see if they have a bio already
  // and get the info in the bio 
  $Bio = new Bio();

  if ( $BioId > 0)
  {
    $Bio->load_from_bioid($BioId);
    form_hidden_value("BioId",$Bio->BioId);
  }

  $_POST['BioText'] = $Bio->BioText;
  $_POST['Website'] = $Bio->Website;
  $_POST['GroupName'] = $GroupName;
  $_POST['Title'] = $Bio->Title;

  if ($GroupName == "")
  {
    echo "<br><br>";
    echo "<font color=red>Here is your current</font> - This is the current bio provided 
       for all teacher, panelist and other GBE information.<br><br>";
  }
      
  echo "<TABLE BORDER=0>\n";
  if ($GroupName != "")
  {
    form_text(48, "Group Name (if applicable)","GroupName",$GroupName, FALSE, "", "", "", !$editable);
  }
  else
  {
    form_text(48, "Title","Title",$GroupName, FALSE, "", "", "", !$editable);
  }

  form_text (64, 'Website', 'Website', 128, FALSE, "", "", "", !$editable);

  $text = " Biography. This text will be seen on our website in association with any 
     acts or shows you are participating in.<BR>\n";

  form_textarea ($text, 'BioText', 15, TRUE, FALSE, "", !$editable);
  if ($Bio->PhotoSource != '')
    form_hidden_value ('OrigPhoto', $Bio->PhotoSource);
  if ($editable)
    form_upload("Photo:","photo_upload", FALSE, TRUE);
  if ($Bio->PhotoSource != '')
    display_media( $Bio->PhotoSource);
  echo "</TABLE>\n";
  

  return;
}

/* update_bio
 *
 * Update a bio that was edited by edit_bio.
 * Built on the assumption that all information came from the post array
 * Returns = nothing
 */
function update_bio()
{
  $file = "photo_upload";
  if (validate_file($file))
  {
    $path2 = "";
    $path2 = process_file($file, "picture", "Bio-".$_POST['BioId'] );

    if ( strpos($path2,FILE_UPLOAD_LOC) === FALSE )
    {
      return display_error ("Error uploading the bio picture.  File output is: ".$path2);
    }
  }
  else 
    $path2 = $_POST["OrigPhoto"];
    
  $Bio = new Bio();
  if (isset($_POST['isGroup']))
    $_POST['UserId'] = NULL;
  $Bio->convert_from_array($_POST);
  $Bio->PhotoSource = $path2;
  $Bio->save_to_db();
  return;
  
}


?>
