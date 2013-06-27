<?php
define (FILE_NOT_FOUND, "No File");
global $EXTENSIONS;
global $FILE_SIZES;
$EXTENSIONS = array();
$EXTENSIONS["picture"] = array("image/gif", "image/jpeg", "image/jpg", "image/png");
$EXTENSIONS["video"] = array("video/x-m4v", "video/quicktime");

$FILE_SIZES = array();
$FILE_SIZES["picture"]= 1048576;
$FILE_SIZES["video"]= 52228800;


/*
 * form_upload
 *
 * Creates the upload form.
 *
 */

function form_upload ($display, $name, $required=FALSE)
{    
  if ($required)
    $req_prefix = '<font color="red">*</font>&nbsp;';
  else
    $req_prefix = '';

    echo "  <TR>\n";
    echo "    <TD COLSPAN=2><br><br>\n";
    echo "      {$req_prefix}{$display}";
    echo "		<input type=\"file\" name=\"{$name}\"/>";
    echo "    </TD>\n";
    echo "  </TR>\n";
}

/*
 * process_file
 *
 * Takes in the new file, provides a string for where it's been stored
 *
 */

function process_file($name, $format, $destname, $required=FALSE)

{
  global $EXTENSIONS;
  global $FILE_SIZES;

  $path = "";
  //$extension = end(explode(".", $_FILES["file"]["name"]));
  $ext_OK = FALSE;
 
  if ( $EXTENSIONS[$format] == "" )
    return $path;
  
  // check that this is an allowed extension    
  foreach ($EXTENSIONS[$format] as $ext)
    if ( $_FILES[$name]["type"] == $ext)
    {
      $ext_OK = TRUE;
    }

  if ($_FILES[$name]["error"] > 0)
  {
    //echo "Error: " . $_FILES[$name]["error"] . "<br>";
    if ($_FILES[$name]["error"] == 4)
      $path =  FILE_NOT_FOUND;
    else
      $path = "Error: Upload error, retry and ask our web masters for help, if needed.";
  }
  else if ( $ext_OK && ($_FILES[$name]["size"] < $FILE_SIZES[$format]) )
  {
    //echo "Upload: " . $path . "<br>";
    //echo "Type: " . $_FILES[$name]["type"] . "<br>";
    //echo "Size: " . ($_FILES[$name]["size"] / 1024) . " kB<br>";
    //echo "Stored in: " . $_FILES[$name]["tmp_name"]."<BR>";
    $extension = substr($_FILES[$name]["name"], strrpos($_FILES[$name]["name"],"."));
    $path = FILE_UPLOAD_LOC.$format."/".$destname.$extension;
    
    // It doesn't matter if it exists, over write it
    if(move_uploaded_file($_FILES[$name]["tmp_name"], $path)) 
    {
      echo "The file ".  $path . " has been uploaded";
    } else {
      echo "There was an error uploading the file, please try again!";
    }  
  }
  // if the file was too big or not an allowed type.
  else {
    echo "Extension:  ".$_FILES[$name]["type"]."<BR>";
    $path = "Error:  file is larger than 1MB or not of an allowed type.";
    $path .= "  Allowed formats are <br>";
    foreach ($EXTENSIONS[$format] as $ext)
      $path .= " - ".$ext."<br>";
  }

  
  return $path;
  
}

/*
 * validate_files
 *
 * Takes in the new file, provides a string for where it's been stored
 *
 */

function validate_file ($name)
{
  
  return $_FILES[$name]["size"] != 0;
}

