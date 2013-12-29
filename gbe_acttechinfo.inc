<?php

/* gbe_acttechinfo.inc - defines the baselines for act technical information.
 * 
 * Last Updated 10/14/2013 by BB
 *
 */
 
/* Class ActTechInfo
 * 
 * This class represents all that is needed to describe 1 act from a tech perspective.  
 * Currently, it defines a post-bid, post-acceptance act, which assumes the act is:
 *   - assigned to a show
 *   - has at least one performer (the submitter)
 *  This is a subset of information about acts that directly relates to the tech crew
 * 
 */
 
class ActTechInfo
{
	// These public properties represent the various columns in the table.
    public $ActTechInfoId;
    public $ActId;
    public $MobilePhone;
    public $Email;
    public $Hotel;
    public $Song;
    public $Artist;
    public $SongMinutes;
    public $SongSeconds;
    public $ActMinutes;
    public $ActSeconds;
    public $MusicPath;
    public $SoundInstruct;
    public $HaveMusic;
    public $NeedMic;
    public $LightingInstruct;
    public $StageColor;
    public $StageSecondColor;
    public $CycColor;
    public $StageColorVendor;
    public $FollowSpot;
    public $Backlight;
    public $Props;
    public $SetProps;
    public $ClearProps;
    public $CueProps;
    public $PropInstruct;
    public $IntroText;
    	



	/* function load_from_acttechinfoid
	 * 
	 * Used to load a particular row by primary key from the database. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_acttechinfoid($ActTechInfoId)
	{
		$sql = sprintf("select * from ActTechInfo where ActTechInfoId = '%s'", 
			mysql_real_escape_string($ActTechInfoId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("ActTechInfo not found:  $ActTechInfoId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}
	
		/* function load_from_actid
	 * 
	 * Used to load a particular row by primary key from the database. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_actid($ActId)
	{
		$sql = sprintf("select * from ActTechInfo where ActId = '%s'", 
			mysql_real_escape_string($ActId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Act not found:  $ActId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}

	/* function load_bid_user
	 * 
	 * Used to load a particular row by BidId and UserId. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_bid_user($BidId, $UserId)
	{
		$sql = sprintf("select Users.EvePhone as MobilePhone, Users.EMail as Email, "
		    ."Bids.Minutes as SongMinutes, Bids.Minutes as ActMinutes, "
		    ."Bids.Seconds as SongSeconds, Bids.Seconds as ActSeconds, "
		    ."Bids.GameSystem as Song, Bids.OtherDetails as Artist"
		    ." from Bids, Users where Bids.BidId = '%s' and Users.UserId='%s'", 
			mysql_real_escape_string($BidId), mysql_real_escape_string($UserId));
		 
		// echo $sql;
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple act items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("Bid not found:  $BidId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row, 1);
	}
	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.  $loadBidUser is a special switch if this is being loaded from
     * a bid and user entry, not from a preexisting row in the normal table.  
	 *
	 * $row: an object row from the Acts table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row, $loadBidUser=0)
	{
	  if (!$loadBidUser)
	  {
        $this->ActTechInfoId = (int)$row->ActTechInfoId;
        $this->ActId = (int)$row->ActId;
        $this->Hotel = (bool)$row->Hotel;
        $this->MusicPath = (string)$row->MusicPath;
        $this->SoundInstruct = (string)$row->SoundInstruct;
        $this->LightingInstruct = (string)$row->LightingInstruct;
        $this->HaveMusic = (bool)$row->HaveMusic;
        $this->NeedMic = (bool)$row->NeedMic;
        $this->StageColor = (string)$row->StageColor;
        $this->StageSecondColor = (string)$row->StageSecondColor;
        $this->CycColor = (string)$row->CycColor;
        $this->StageColorVendor = (string)$row->StageColorVendor;
        $this->FollowSpot = (bool)$row->FollowSpot;
        $this->Backlight = (bool)$row->Backlight;
        $this->Props = (bool)$row->Props;
        $this->SetProps = (bool)$row->SetProps;
        $this->ClearProps = (bool)$row->ClearProps;
        $this->CueProps = (bool)$row->CueProps;
        $this->PropInstruct = (string)$row->PropInstruct;
        $this->IntroText = (string)$row->IntroText;
      }
      $this->MobilePhone = (string)$row->MobilePhone;
      $this->Email = (string)$row->Email;
      $this->Song = (string)$row->Song;
      $this->Artist = (string)$row->Artist;
      $this->SongMinutes = (int)$row->SongMinutes;
      $this->SongSeconds = (int)$row->SongSeconds;
      $this->ActMinutes = (int)$row->ActMinutes;
      $this->ActSeconds = (int)$row->ActSeconds;

	}
	
	/* function dump_to_array
	 * 
	 * Create and return an array with the contents of this object, the key is the 
	 * column name.
	 *
	 * Returns: an array with entries for each of the properties of the ActTechInfo
	 */
	function dump_to_array()
	{
	  $array = array();
      $array['ActTechInfoId'] = $this->ActTechInfoId;
      $array['ActId'] = $this->ActId;
      $array['MobilePhone'] = $this->MobilePhone;
      $array['Email'] = $this->Email;
      $array['Hotel'] = $this->Hotel;
      $array['Song'] = $this->Song;
      $array['Artist'] = $this->Artist;
      $array['SongMinutes'] = $this->SongMinutes;
      $array['SongSeconds'] = $this->SongSeconds;
      $array['ActMinutes'] = $this->ActMinutes;
      $array['ActSeconds'] = $this->ActSeconds;
      $array['MusicPath'] = $this->MusicPath;
      $array['SoundInstruct'] = $this->SoundInstruct;
      $array['HaveMusic'] = $this->HaveMusic;
      $array['NeedMic'] = $this->NeedMic;
      $array['LightingInstruct'] = $this->LightingInstruct;
      $array['StageColor'] = $this->StageColor;
      $array['StageSecondColor'] = $this->StageSecondColor;
      $array['CycColor'] = $this->CycColor;
      $array['StageColorVendor'] = $this->StageColorVendor;
      $array['FollowSpot'] = $this->FollowSpot;
      $array['Backlight'] = $this->Backlight;
      $array['Props'] = $this->Props;
      $array['SetProps'] = $this->SetProps;
      $array['ClearProps'] = $this->ClearProps;
      $array['CueProps'] = $this->CueProps; 
      $array['PropInstruct'] = $this->PropInstruct;
      $array['IntroText'] = $this->IntroText;
      return $array;
 	}


 	
 	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the item
	 * Returns: $errors: an array for storing errors with key of column name, and description of 
	 *   problem as value.  Empty if successful.
	 */
	function convert_from_array($array)
	{		
	  $errors = array();
	  if (array_key_exists('ActTechInfoId', $array))    {
	    $this->ActTechInfoId = (int)$array['ActTechInfoId'];
	  }
	  if (array_key_exists('ActId', $array))    {
	    $this->ActId = (int)$array['ActId'];
	  }
	  if (array_key_exists('MobilePhone', $array))    {
	    $this->MobilePhone = (string)$array['MobilePhone'];
	  }
	  if (array_key_exists('Email', $array))    {
	    if (is_valid_email_address ('Email', TRUE, $array['Email']))
	     $this->Email = (string)$array['Email']; 
	    else 
	      $errors["Email"]="An invalid email address was provided";
	  }
	  if (array_key_exists('Hotel', $array))    {
	    if (is_valid_enum('ActTechInfo','Hotel',$array['Hotel']))    
	      $this->Hotel = (string)$array['Hotel']; 
	    else 
	      $errors["Hotel"]=$array['Hotel']." is not a valid value for a Hotel choice.";    
      }
	  if (array_key_exists('Song', $array))    {
	    if (strlen($array['Song']) < 128)
	      $this->Song = (string)$array['Song']; 
	    else 
          $errors["Song"]="Text for Song is longer than 128 characters - please shorten.";    
      }
	  if (array_key_exists('Artist', $array))    {
	    if (strlen($array['Artist']) < 128)
	      $this->Artist = (string)$array['Artist']; 
	    else 
          $errors["Artist"]="Text for Artist is longer than 128 characters - please shorten.";    
      }
	  if (array_key_exists('SongMinutes', $array))    {
	    $this->SongMinutes = (int)$array['SongMinutes']; 
      }
	  if (array_key_exists('SongSeconds', $array))    {
	    $this->SongSeconds = (int)$array['SongSeconds']; 
      }
	  if (array_key_exists('ActMinutes', $array))    {
	    $this->ActMinutes = (int)$array['ActMinutes']; 
	    }
	  if (array_key_exists('ActSeconds', $array))    {
	    $this->ActSeconds = (int)$array['ActSeconds'];
      }
      
      
      
	  if (array_key_exists('MusicPath', $array))    { 
	    $this->MusicPath = (string)$array['MusicPath']; 
      }
      
      
      
	  if (array_key_exists('SoundInstruct', $array))    {
	    if (strlen($array['SoundInstruct']) < 500)
	      $this->SoundInstruct = (string)$array['SoundInstruct']; 
	    else 
          $errors["SoundInstruct"]="Text for Sound Instructions is longer than 500 characters - please shorten.";    
      }
	  if (array_key_exists('LightingInstruct', $array))    {
	    if (strlen($array['LightingInstruct']) < 500)
	      $this->LightingInstruct = (string)$array['LightingInstruct']; 
	    else 
          $errors["LightingInstruct"]="Text for Lighting Instructions is longer than 500 characters - please shorten.";    
      }
	  if (array_key_exists('HaveMusic', $array))
        $this->HaveMusic = 1; 
      else 
        $this->HaveMusic = 0; 

	  if (array_key_exists('NeedMic', $array)) 
        $this->NeedMic = 1; 
      else 
        $this->NeedMic = 0; 
	  if (array_key_exists('StageColor', $array))    
	  {
	    if (is_valid_enum('ActTechInfo','StageColor',$array['StageColor']))    
	      $this->StageColor = (string)$array['StageColor']; 
	    else 
	      $errors["StageColor"]=$array['StageColor']." is not a valid value for a Stage Color choice.";    
      }
	  if (array_key_exists('StageSecondColor', $array))    
	  {
	    if (is_valid_enum('ActTechInfo','StageSecondColor',$array['StageSecondColor']))    
	      $this->StageSecondColor = (string)$array['StageSecondColor']; 
	    else 
	      $errors["StageSecondColor"]=$array['StageSecondColor']." is not a valid value for a Stage Second Color choice.";    
      }
	  if (array_key_exists('CycColor', $array))    
	  {
	    if (is_valid_enum('ActTechInfo','CycColor',$array['CycColor']))    
	      $this->CycColor = (string)$array['CycColor']; 
	    else 
	      $errors["CycColor"]=$array['CycColor']." is not a valid value for a Cyc Color choice.";    
      }
	  if (array_key_exists('StageColorVendor', $array))
      {
	    if (is_valid_enum('ActTechInfo','StageColorVendor',$array['StageColorVendor']))    
	      $this->StageColorVendor = (string)$array['StageColorVendor']; 
	    else 
	      $errors["StageColorVendor"]=$array['StageColorVendor']." is not a valid value for a Stage Color choice.";    
      }
      
      
	  if (array_key_exists('FollowSpot', $array))
        $this->FollowSpot = 1; 
      else 
        $this->FollowSpot = 0; 
	  if (array_key_exists('Backlight', $array)) 
        $this->Backlight = 1; 
      else 
        $this->Backlight = 0; 
	  if (array_key_exists('Props', $array))   
        $this->Props = 1; 
      else 
        $this->Props = 0; 
	  if (array_key_exists('SetProps', $array))   
        $this->SetProps = 1; 
      else 
        $this->SetProps = 0; 
	  if (array_key_exists('ClearProps', $array))   
        $this->ClearProps = 1; 
      else 
        $this->ClearProps = 0; 
	  if (array_key_exists('CueProps', $array))   
        $this->CueProps = 1; 
      else 
        $this->CueProps = 0; 
        
        
	  if (array_key_exists('PropInstruct', $array))    {
	    if (strlen($array['PropInstruct']) < 500)
	      $this->PropInstruct = (string)$array['PropInstruct']; 
	    else 
          $errors["PropInstruct"]="Text for Prop Instructions is longer than 500 characters - please shorten.";    
        }
	  if (array_key_exists('IntroText', $array))    {
	    if (strlen($array['IntroText']) < 500)
	      $this->IntroText = (string)$array['IntroText']; 
	    else 
          $errors["IntroText"]="Text for Introduction Text is longer than 500 characters - please shorten.";    
        }		
	  return $errors;
	}

	/* function save_to_db
	 * 
	 * Used to save the current status of the ticket item to the database.	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		
		// See if this is an insert or update.
		
		$this->ActTechInfoId = mysql_real_escape_string($this->ActTechInfoId);
		$sql = "select * from ActTechInfo where ActTechInfoId = '$this->ActTechInfoId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update ActTechInfo set ";
		}	
		else
		{
			$sql = "insert into ActTechInfo set ";
		}

        $sql .= build_sql_string('ActTechInfoId', $this->ActTechInfoId, false);
        $sql .= build_sql_string('ActId', $this->ActId);
        $sql .= build_sql_string('MobilePhone', $this->MobilePhone);
        $sql .= build_sql_string('Email', $this->Email);
        $sql .= build_sql_string('Hotel', $this->Hotel);
        $sql .= build_sql_string('Song', $this->Song);
        $sql .= build_sql_string('Artist', $this->Artist);
        $sql .= build_sql_string('SongMinutes', $this->SongMinutes);
        $sql .= build_sql_string('SongSeconds', $this->SongSeconds);
        $sql .= build_sql_string('ActMinutes', $this->ActMinutes);
        $sql .= build_sql_string('ActSeconds', $this->ActSeconds);
        $sql .= build_sql_string('MusicPath', $this->MusicPath);
        $sql .= build_sql_string('SoundInstruct', $this->SoundInstruct);
        $sql .= build_sql_string('LightingInstruct', $this->LightingInstruct);
        $sql .= build_sql_string('HaveMusic', $this->HaveMusic);
        $sql .= build_sql_string('NeedMic', $this->NeedMic);
        $sql .= build_sql_string('StageColor', $this->StageColor);
        $sql .= build_sql_string('StageSecondColor', $this->StageSecondColor);
        $sql .= build_sql_string('CycColor', $this->CycColor);
        $sql .= build_sql_string('StageColorVendor', $this->StageColorVendor);
        $sql .= build_sql_string('FollowSpot', $this->FollowSpot);
        $sql .= build_sql_string('Backlight', $this->Backlight);
        $sql .= build_sql_string('Props', $this->Props);
        $sql .= build_sql_string('SetProps', $this->SetProps);
        $sql .= build_sql_string('ClearProps', $this->ClearProps);
        $sql .= build_sql_string('CueProps', $this->CueProps);
        $sql .= build_sql_string('PropInstruct', $this->PropInstruct);
        $sql .= build_sql_string('IntroText', $this->IntroText);
        
		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where ActTechInfoId = '%s'", $this->ActTechInfoId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into ActTechInfo failed");

		if ($this->ActTechInfoId < 1)
          $this->ActTechInfoId = mysql_insert_id();
        //echo "Just saved or made ActTechInfoId: ".$this->ActTechInfoId."<br>\n";
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this ticket item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		$sql = sprintf("delete from ActTechInfo where ActTechInfoId = '%s'", 
			mysql_real_escape_string($this->ActTechInfoId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
	}
}

class ActTechDisplay
{
	// These public properties represent the various columns in the table.
    public $ActTechDisplayId;
    public $ShowId;
    public $ColumnName;
    public $DisplayText;
    public $HelpText;
    public $Type;  
    public $Order;  
    public $On;  
    public $Required;  



	/* function load_from_acttechdisplayid
	 * 
	 * Used to load a particular row by primary key from the database. 
	 *
	 * $ItemId:  the Item Id to be loaded.
	 * Returns: nothing.
	 */
	function load_from_acttechdisplayid($ActTechDisplayId)
	{
		$sql = sprintf("select * from ActTechDisplay where ActTechInfoId = '%s'", 
			mysql_real_escape_string($ActTechInfoId));
		 
		$result = mysql_query($sql);
		
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple items found with same ID");	
		if (1 != mysql_num_rows($result))
			return display_error("ActTechDisplay not found:  $ActTechDisplayId");
		
		$row = mysql_fetch_object($result);
		$this->convert_from_sql_row($row);
	}
	
	
	/* function convert_from_sql_row
	 * 
	 * Used override the object contents with the resultset row from an SQL
     * select query.	 
	 *
	 * $row: an object row from the Acts table. 
	 * Returns: nothing.
	 */
	function convert_from_sql_row($row)
	{
        $this->ActTechDisplayId = (int)$row->ActTechDisplayId;
        $this->ShowId = (int)$row->ShowId;
        $this->ColumnName = (string)$row->ColumnName;
        $this->DisplayText = (string)$row->DisplayText;
        $this->HelpText = (string)$row->HelpText;
        $this->Type = (string)$row->Type;
        $this->Order = (int)$row->Order;
        $this->On = (int)$row->On;
        $this->Required = (int)$row->Required;
	}
	
	/* function convert_from_array
	 * 
	 * Used override the object contents with the given array, possibly from a POST object.
	 *
	 * $array: an array with entries for each of the properties of the TickeItem
	 * Returns: true if successful.
	 */
	function convert_from_array($array)
	{		
      $this->ActTechDisplayId = (int)$array['ActTechDisplayId']; 
      $this->ShowId = (int)$array['ShowId'];
      $this->ColumnName = (string)$array['ColumnName'];
      $this->DisplayText = (string)$array['DisplayText'];
      if (array_key_exists('HelpText', $array))    {
              $this->HelpText = (string)$array['HelpText'];
      }
      $this->Type = (string)$array['Type']; 
      $this->Order = (int)$array['Order']; 
      $this->On = (int)$array['On']; 
      $this->Required = (int)$array['Required']; 
      return true;
	}

	/* function save_to_db
	 * 
	 * Used to save the current status of the ticket item to the database.	 
	 *
	 * Returns: nothing.
	 */
	function save_to_db()
	{	
		
		// See if this is an insert or update.
		
		$this->ActTechDisplayId = mysql_real_escape_string($this->ActTechDisplayId);
		$sql = "select * from ActTechDisplay where ActTechInfoId = '$this->ActTechDisplayId'";
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
				
		// Update the database.
		
		if (mysql_num_rows($result) > 1)
			return display_error("Error:  multiple rows found with same ID");
			
		else if (mysql_num_rows($result) == 1)
		{
			$sql = "update ActTechDisplay set ";
		}	
		else
		{
			$sql = "insert into ActTechDisplay set ";
		}

        $sql .= build_sql_string('ActTechDisplayId', $this->ActTechDisplayId, false);
        $sql .= build_sql_string('ShowId', $this->ShowId, false);
        $sql .= build_sql_string('ColumnName', $this->ColumnName, false);
        $sql .= build_sql_string('DisplayText', $this->DisplayText);
        $sql .= build_sql_string('HelpText', $this->HelpText);
        $sql .= build_sql_string('Type', $this->Type);
        $sql .= build_sql_string('Order', $this->Order);
        $sql .= build_sql_string('On', $this->On);
        $sql .= build_sql_string('Required', $this->Required);

		if (mysql_num_rows($result) == 1)
			$sql .= sprintf(" where ActTechDisplayId = '%s'", $this->ActTechDisplayId);
		//echo $sql;
		
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ("Insert or Update into ActTechDisplay failed");

		if ($this->ActTechInfoId < 1)
          $this->ActTechInfoId = mysql_insert_id();
        echo "Just saved or made ActTechDisplayId: ".$this->ActTechDisplayId."<br>\n";
	}
	
	/* function remove_from_db
	 * 
	 * Used to remove this ticket item from the database. 
	 *
	 * Returns: nothing.
	 */
	function remove_from_db()
	{

		$sql = sprintf("delete from ActTechDisplay where ActTechDisplayId = '%s'", 
			mysql_real_escape_string($this->ActTechDisplayId));
		$result = mysql_query($sql);
		if (!$result)
			return display_mysql_error ('Cannot execute query', $sql);
	}
}

/* function get_acttech_display_settings
 * 
 * Get the settings for a given Show to decide what and how to display
 *
 * Returns: array of settings, passed by reference.  Settings are of type ActTechDisplay
 */
function get_acttech_display_settings($ShowId, &$Settings)
{
  if ($ShowId <= 0)
    return display_error("Problem finding the show - no show information provided.");
    
  $Settings = array();
  
  $sql = "select * from ActTechDisplay where ShowId='$ShowId' ORDER BY `ActTechDisplay`.`Order` ASC";
  //echo $sql;
  
  $result = mysql_query($sql);
  //echo $sql;
  
  if (!$result)
    return display_mysql_error ('Cannot execute query', $sql);
    
  while ($row = mysql_fetch_object($result))
  {
    $setting = new ActTechDisplay();
    $setting->convert_from_sql_row($row);
    $Settings[$setting->ActTechDisplayId]=$setting;
  }  
  
}

/* function get_acttech_listings
 * 
 * Get the settings for a given Show to decide what and how to display
 *
 * Returns: array of settings, passed by reference.  Settings are of type ActTechDisplay
 */
function get_acttech_listings(&$tech_info, &$act_info, $ShowId=0)
{
  $tech_info = array();
       
  $sql = "select * from ActTechInfo, Acts WHERE (Acts.ActInfoId = ActTechInfo.ActTechInfoId or Acts.ActInfoid = 0) ";

  if ($ShowId > 0)
    $sql .= " AND Acts.ShowId = ".$ShowId;
    
  $sql .= " order by Acts.Order";
    
  $result = mysql_query($sql);
  //echo $sql;
  
  if (!$result)
    return display_mysql_error ('Cannot execute query', $sql);
    
  while ($row = mysql_fetch_object($result))
  {
    $list_item = new ActTechInfo();
    if ($row->ActInfoId == $row->ActTechInfoId)
      $list_item->convert_from_sql_row($row);
    $act_item = new Act();
    $act_item ->convert_from_sql_row($row);
    $tech_info[$act_item->ActId]=$list_item;
    $act_info[$act_item->ActId]=$act_item;
  }  
  
}


?>

