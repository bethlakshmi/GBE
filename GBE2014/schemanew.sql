

--
-- Table structure for table `Acts`
--   new for GBE to create a way to make parts of events -- acts.
--   now the link to the submitter is in the commitments table
--   also merging Act Tech Info table to flatten the database.

CREATE TABLE`Acts` (
  `ActId` INT unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `ShowId` INT NOT NULL COMMENT 'Link to assigned show - foreign key to Events',
  `RehearsalId` INT NOT NULL COMMENT 'Link to foreign key to Event for Act Rehearsal',
  `GroupId` INT( 10 ) NOT NULL DEFAULT '0' COMMENT 'Bio for Group - foreign key to bios, optional, only used for groups',
  `isGroup` INT NOT NULL COMMENT 'Boolean - for whether or not this a group act',
  `Order` int(10) unsigned NOT NULL 'order of act within the show',
  `Title` varchar(128) NOT NULL DEFAULT '',
  `Description` text,
  `Homepage` varchar(128) NOT NULL DEFAULT '',
  `Bio` text NOT NULL,
  `YearsOfExperience` text NOT NULL,
  `Hotel` enum('Yes','No') DEFAULT NULL,
  `SongName` varchar(128) DEFAULT NULL,
  `Artist` varchar(128) DEFAULT NULL,
  `SongMinutes` int(3) unsigned DEFAULT NULL,
  `SongSeconds` int(3) unsigned DEFAULT NULL,
  `ActMinutes` int(3) unsigned DEFAULT NULL,
  `ActSeconds` int(3) unsigned DEFAULT NULL,
  `VideoOf` SET( 'I don\'t have any video of myself performing', 
                 'This is video of me but not the act I\'m submitting', 
                 'This is video of the act I would like to perform' ) NULL ,
  `MusicSource` varchar(256) DEFAULT NULL COMMENT 'connection to an uploaded file',
  `VideoSource` VARCHAR( 128 ) NULL COMMENT 'URL to video somewhere else',
  `PhotoSource` VARCHAR( 128 ) NULL COMMENT 'connection to uploaded file',
  `SoundInstruct` varchar(500) DEFAULT NULL,
  `HaveMusic` tinyint(1) unsigned DEFAULT NULL,
  `NeedMic` tinyint(1) unsigned DEFAULT NULL,
  `LightingInstruct` varchar(500) DEFAULT NULL,
  `StageColor` enum('White','Amber','Blue','Cyan','Green','Orange','Pink','Purple','Red','Yellow','No lights (not recommended)') NOT NULL,
  `StageSecondColor` enum('White','Amber','Blue','Cyan','Green','Orange','Pink','Purple','Red','Yellow','No lights (not recommended)') NOT NULL,
  `CycColor` enum('White','No Lights','Amber','Blue','Cyan','Green','Orange','Pink','Purple','Red','Yellow','Back Lit (white light pointing at audience)') NOT NULL,
  `StageColorVendor` enum('White','Blue','Red','No lights (not recommended)') NOT NULL,
  `FollowSpot` tinyint(1) unsigned NOT NULL,
  `Backlight` tinyint(1) unsigned NOT NULL,
  `Props` tinyint(1) unsigned DEFAULT NULL,
  `SetProps` tinyint(1) unsigned DEFAULT NULL,
  `ClearProps` tinyint(1) unsigned DEFAULT NULL,
  `CueProps` tinyint(1) unsigned DEFAULT NULL,
  `PropInstruct` varchar(500) DEFAULT NULL,
  `IntroText` varchar(500) DEFAULT NULL,
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ActId`)
) 


--
-- Table structure for table `BidChoice`
--   used as an extensible way for asking lists of questions in bids, so we don't have to 
--   keep adding columns.  
--

DROP TABLE IF EXISTS `BidChoice`;
CREATE TABLE IF NOT EXISTS `BidChoice` (
  `BidChoice` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ActId` int(10) unsigned NOT NULL COMMENT 'foreign key to acts',
  `EventId` int(10) unsigned NOT NULL COMMENT 'foreign key to events',
  `Question` varchar(128) NOT NULL,
  `Answer` enum('Yes','No','Yes - and Won!','Not Sure') NOT NULL,
  PRIMARY KEY (`BidChoice`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=232 ;


--
-- Table structure for table `BidFeedback`
--

DROP TABLE IF EXISTS `BidFeedback`;
CREATE TABLE `BidFeedback` (
  `FeedbackId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BidStatusId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key to bid status',
  `UserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key to user profile, for feedback giver (they have roles in the system)',
  `Vote` enum('Strong Yes','Yes','Weak Yes','No Comment','Weak No','No','Strong No','Undecided','Author') NOT NULL DEFAULT 'Undecided',
  `Issues` char(64) NOT NULL DEFAULT '',
  `ShowPref`  NULL DEFAULT NULL  COMMENT 'link to list of shows within events - foreign key ',
  PRIMARY KEY (`FeedbackId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




--
-- Table structure for table `BidTimes`
--    the place holder for requests for a specific time.  Specifically used for conf stuff.

DROP TABLE IF EXISTS `BidTimes`;
CREATE TABLE `BidTimes` (
  `BidTimeId` int(10) unsigned NOT NULL auto_increment,
  `EventId` int(10) unsigned NOT NULL COMMENT 'Foreign key to events',
  `Day` enum('Monday','Tuesday','Wednesday','Thursday','Friday', 'Saturday', 'Sunday') NOT NULL,
  `Slot` enum('Morning', 'Lunch', 'Early Afternoon', 'Late Afternoon', 'Dinner', 'Evening', 'After Midnight' ) NOT NULL,
  `Pref` char(1) NOT NULL default '',
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`BidTimeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `Bios`
--

DROP TABLE IF EXISTS `Bios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Bios` (
  `BioId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL DEFAULT '0' foreign key to user profile,
  `Groupid` int(11) NOT NULL DEFAULT '0' foreign key to groups,
  `BioText` text NOT NULL,
  `Title` text NOT NULL,
  `NameChoice` enum("FirstLast", "StageName", "Group")
  `Website` VARCHAR( 128 ) NULL,
  `PhotoSource` VARCHAR( 128 ) NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`BioId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `BPTEventList`
--

DROP TABLE IF EXISTS `BPTEventList`;
CREATE TABLE IF NOT EXISTS `BPTEventList` (
  `BPTEvent` varchar(30) NOT NULL,
  `Primary` tinyint(1) NOT NULL,
  `ActSubmitFee` tinyint(1) NOT NULL,
  PRIMARY KEY (`BPTEvent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `BPTSettings`
--

DROP TABLE IF EXISTS `BPTSettings`;
CREATE TABLE IF NOT EXISTS `BPTSettings` (
  `DeveloperID` varchar(30) NOT NULL,
  `ClientID` varchar(30) NOT NULL,
  `LastPollTime` datetime NOT NULL,
  PRIMARY KEY (`DeveloperID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `Commitments`
-- NEW!  - made to replace GMs and Participants, our relationships are more diverse, 
--   this is one table to hold them all
--

DROP TABLE IF EXISTS `Commitments`;
CREATE TABLE `Commitments` (
  `CommitmentId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL DEFAULT '0',
  `GroupId` int(11) NOT NULL DEFAULT '0' COMMENT "Groups should also have a UserId for primary contact",
  `EventId` int(11) NOT NULL DEFAULT '0',
  `Role` enum( "panelist", "moderator","performer","troupe","teacher","volunteer","paid","staff") NULL DEFAULT NULL,
  `PrevState` enum('Confirmed','Waitlisted','Withdrawn','None') DEFAULT 'None',
  `State` enum('Confirmed','Waitlisted','Withdrawn') NOT NULL DEFAULT 'Confirmed',
  `Counted` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Submitter` enum('Y','N') NOT NULL DEFAULT 'N',
  `DisplayWithEvent` enum('Y','N') NOT NULL DEFAULT 'Y',
  `DisplayEMail` enum('Y','N') NOT NULL DEFAULT 'Y',
  `ReceiveConEMail` enum('Y','N') NOT NULL DEFAULT 'N',
  `ReceiveSignupEMail` enum('Y','N') NOT NULL DEFAULT 'N',
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`GMId`)
) 


-- --------------------------------------------------------
--
-- Table structure for table `Con`
--

DROP TABLE IF EXISTS `Con`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Con` (
  `ConId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SignupsAllowed` enum('NotYet','1','2','3','Yes','NotNow') NOT NULL DEFAULT 'NotYet',
  `ShowSchedule` enum('Yes','GMs','Priv','No') NOT NULL DEFAULT 'No',
  `News` text NOT NULL,
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ConComMeetings` text NOT NULL,
  `AcceptingBids` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `OpenEdit` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  PRIMARY KEY (`ConId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `EventTicketLinks`
--

DROP TABLE IF EXISTS `EventTicketLinks`;
CREATE TABLE IF NOT EXISTS `EventTicketLinks` (
  `ETIndex` int(11) NOT NULL AUTO_INCREMENT,
  `EventId` int(11) NOT NULL,
  `TicketItemId` varchar(30) NOT NULL,
  `Datestamp` datetime NOT NULL,
  `Userstamp` int(11) NOT NULL,
  PRIMARY KEY (`ETIndex`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `Events`
--   Conjoined with Bids table for classes, panels and workshops with Jon's idea 
--    that a Bid is an event that hasn't been booked.  Specifically designed for 
--    class, panel and workshop bids, not for acts or vendors.
--

DROP TABLE IF EXISTS `Events`;



CREATE TABLE `Events` (
  `EventId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ShowId` int(10) DEFAULT NULL COMMENT 'Only for rehearsals - the show event that this is a rehearsal for',
  `Title` varchar(128) NOT NULL DEFAULT '',
  `EventType` enum('Class', 'Panel', 'Workshop', 
                   'Show', 'Master Class', 'Drop-In Class',  'Special Event', 
                   'Act Rehearsal', 'Tech Rehearsal', 'Call', 'Ops') NOT NULL DEFAULT '' COMMENT ',
  `Status` enum('Pending','Under Review','Accepted','Rejected','Dropped','Draft') NOT NULL DEFAULT 'Pending',
  `Group` text NOT NULL COMMENT 'Group of performers, fellow teachers, or name of company',
  `Homepage` text,
  `NotifyOnChanges` enum('Y','N') NOT NULL DEFAULT 'N',
  `Fee` varchar(30) DEFAULT '',
  `ShortBlurb` text NOT NULL,
  `Description` text NOT NULL,
  `Day` enum('Fri','Sat','Sun') NOT NULL DEFAULT 'Fri',
  `StartHour` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `StartMinutes` tinyint(3) unsigned NOT NULL DEFAULT '0',
  'LengthMinutes' int(10) unsigned NOT NULL DEFAULT '0',
  'MinSignup' int COMMENT 'may not be used for every role, depends on Event Type',
  'MaxSignup' int COMMENT 'ditto minSignup',
  'canWaitlist' Boolean COMMENT 'can we have a waitlist for this?',
  `History` text NOT NULL COMMENT 'whether the class has been run before',
  `ScheduleNote` char(32) NOT NULL DEFAULT '',
  `RoomId` int(10) COMMENT "Foreign Key to Room list",
  'ExclusiveUse' boolean COMMENT "Goes with Overbook Size - if the room is overbookable, this shows whether the event is using the room exclusively.  YES by default';
  `PhysicalRestrictions` text,
  `CanSignupConcurrently` enum('Y','N') NOT NULL DEFAULT 'N',
  `ShowPublicCalendar` enum('Y','N') NOT NULL DEFAULT 'N',
  `SchedulingConstraints` text COMMENT 'filled in by teacher for when they can teach',
  `MultipleRuns` enum('Y','N') DEFAULT NULL 'whether teacher is willing to do multiple runs',
  `SpaceRequirements` text NOT NULL,
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`EventId`)
) 

-- 
-- Table - new groups are troupes or companies that vend.  
-- 
 CREATE TABLE 'Groups' (
  'GroupId' primary key
  'PrimaryContact' int COMMENT 'key to Users, submitter of group',
  'BioId' int COMMENT 'foreign key to Bios',
  'GroupName' text,
  'GroupType' enum ('performer','vendor')
}

-- 
-- Table - new groups are troupes or companies that vend.  
-- 
 CREATE TABLE 'GroupConnect' (
  'GroupId' primary key
  'UserId' int COMMENT 'key to Users, submitter of group',
}

  

--
-- Table structure for table `LimboTransactions'
--

DROP TABLE IF EXISTS `LimboTransactions`;
CREATE TABLE IF NOT EXISTS `LimboTransactions` (
  `LimboIndex` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(30) NOT NULL,
  `LastName` varchar(30) NOT NULL,
  `PaymentEmail` varchar(64) NOT NULL,
  `Country` varchar(30) NOT NULL,
  `Phone` varchar(30) NOT NULL,
  `ItemId` varchar(30) NOT NULL,
  `Amount` double(10,2) NOT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentSource` varchar(30) NOT NULL,
  `Status` enum('Posted','Settled','Voided','Error') NOT NULL,
  `TenderType` enum('Cash','Check','Charge','Comp') NOT NULL,
  `Reference` varchar(30) NOT NULL,
  `TrackerId` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`LimboIndex`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `PanelBids'
--

DROP TABLE IF EXISTS `PanelBids`;
CREATE TABLE `PanelBids` (
  `PanelBidsId` int(10) unsigned NOT NULL auto_increment,
  `EventId` int(10) unsigned NOT NULL,
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Interest` enum('no involvement', 'being a panelist', 'being the moderator') NOT NULL,
  `Expertise` text,
  `Panelist` int(1),
  `Moderator` int(1),
  PRIMARY KEY  (`PanelBidsId`)
) 


CREATE TABLE IF NOT EXISTS 'Rooms' {
  RoomId int Primary Key,
  RoomName text,
  RoomDescription text,
  OverbookSize int COMMENT 'Crazy idea - note how many events can run in that room, max, so we can overbook"
}




--
-- Table structure for table `TicketItems`
--

DROP TABLE IF EXISTS `TicketItems`;
CREATE TABLE IF NOT EXISTS `TicketItems` (
  `ItemId` varchar(30) NOT NULL,
  `Title` varchar(30) NOT NULL,
  `Description` varchar(1000) NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `Cost` decimal(10,2) NOT NULL,
  `Datestamp` datetime NOT NULL,
  `Userstamp` int(11) NOT NULL,
  PRIMARY KEY (`ItemId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Transactions`
--

DROP TABLE IF EXISTS `Transactions`;
CREATE TABLE IF NOT EXISTS `Transactions` (
  `TransIndex` int(11) NOT NULL AUTO_INCREMENT,
  `ItemId` varchar(30) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Amount` double(10,2) NOT NULL,
  `Datestamp` datetime NOT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentSource` varchar(30) NOT NULL,
  `Status` enum('Posted','Settled','Voided','Error') NOT NULL,
  `TenderType` enum('Cash','Check','Charge','Comp') NOT NULL,
  `Reference` varchar(30) NOT NULL,
  `Cashier` int(11) DEFAULT NULL,
  `Memo` varchar(500) NOT NULL,
  `Override` tinyint(1) NOT NULL,
  PRIMARY KEY (`TransIndex`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FirstName` char(30) NOT NULL DEFAULT '',
  `LastName` char(30) NOT NULL DEFAULT '',
  `DisplayName` char(64) NOT NULL DEFAULT '',
  `StageName` char(64) NOT NULL DEFAULT '',
  `EMail` char(64) NOT NULL DEFAULT '',
  `PurchaseEmail` char(64) NOT NULL DEFAULT '',
  `Address1` char(64) NOT NULL DEFAULT '',
  `Address2` char(64) NOT NULL DEFAULT '',
  `City` char(64) NOT NULL DEFAULT '',
  `State` char(30) NOT NULL DEFAULT '',
  `Zipcode` char(10) NOT NULL DEFAULT '',
  `Country` char(30) NOT NULL DEFAULT '',
  `OtherPhone` char(20) NOT NULL DEFAULT '',
  `CellPhone` char(20) NOT NULL DEFAULT '',
  `BestTime` char(128) NOT NULL DEFAULT '',
  `HowHeard` char(64) NOT NULL DEFAULT '',
  `PreferredContact` enum('EMail','CellPhone','OtherPhone') DEFAULT NULL,
  `Priv` set('ConfCom', 'ShowCom','Staff','Admin','ConfChair','ShowChair','Mail','Registrar','VendorLiason','Printing') NOT NULL DEFAULT '',
  `CanSignup` enum('volunteer','paid','staff') NOT NULL DEFAULT 'None',
  `ModifiedBy` int(11) NOT NULL DEFAULT '0',
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastLogin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`UserId`),
  KEY `CanSignup` (`CanSignup`)
) 

CREATE TABLE VendorSubmission
{
  VendorSubmitId primary key
  GroupId foreign key to Groups
  State enum("draft", "submitted", "accepted")
  Location text COMMENT "Vendors typically have only the vendor room, so this is location within that room",
}
