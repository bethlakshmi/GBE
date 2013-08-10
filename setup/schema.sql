-- MySQL dump 10.13  Distrib 5.1.50, for apple-darwin10.3.0 (i386)
--
-- Host: localhost    Database: ideploy
-- ------------------------------------------------------
-- Server version	5.1.50

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Away`
--

DROP TABLE IF EXISTS `Away`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Away` (
  `AwayId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Fri` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri12` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri13` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri14` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri15` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri16` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri17` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri18` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri19` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri20` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri21` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri22` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri23` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri24` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri25` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri26` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri27` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri28` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri29` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri30` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fri31` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat08` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat09` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat10` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat11` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat12` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat13` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat14` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat15` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat16` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat17` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat18` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat19` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat20` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat21` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat22` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat23` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat24` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat25` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat26` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat27` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat28` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat29` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat30` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sat31` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun08` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun09` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun10` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun11` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun12` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun13` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun14` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun15` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun16` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun17` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun18` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun19` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun20` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun21` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun22` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun23` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun24` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun25` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun26` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun27` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun28` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun29` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun30` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Sun31` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AwayId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Away`
--

LOCK TABLES `Away` WRITE;
/*!40000 ALTER TABLE `Away` DISABLE KEYS */;
/*!40000 ALTER TABLE `Away` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BidFeedback`
--

DROP TABLE IF EXISTS `BidFeedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BidFeedback` (
  `FeedbackId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BidStatusId` int(10) unsigned NOT NULL DEFAULT '0',
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Vote` enum('Strong Yes','Yes','Weak Yes','No Comment','Weak No','No','Strong No','Undecided','Author') NOT NULL DEFAULT 'Undecided',
  `Issues` char(64) NOT NULL DEFAULT '',
  `ShowPref` SET( 'The Rhinestone Review', 'The Last Chance Casino Party', 'The Main Event (Competition)', 'The Main Event (Not in Competition)', 'The Newcomer\'s Showcase', 'The Sideshow Ball' )  NULL DEFAULT NULL,
  PRIMARY KEY (`FeedbackId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BidFeedback`
--

LOCK TABLES `BidFeedback` WRITE;
/*!40000 ALTER TABLE `BidFeedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `BidFeedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BidInfo`
--

DROP TABLE IF EXISTS `BidInfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BidInfo` (
  `BidInfoId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FirstBid` char(32) NOT NULL DEFAULT '',
  `FirstDecision` char(32) NOT NULL DEFAULT '',
  `SecondBid` char(32) NOT NULL DEFAULT '',
  `SecondDecision` char(32) NOT NULL DEFAULT '',
  `ThirdBid` char(32) NOT NULL DEFAULT '',
  `ThirdDecision` char(32) NOT NULL DEFAULT '',
  `BidInfo` text NOT NULL,
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`BidInfoId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BidInfo`
--

LOCK TABLES `BidInfo` WRITE;
/*!40000 ALTER TABLE `BidInfo` DISABLE KEYS */;
INSERT INTO `BidInfo` VALUES (2,'','','','','','','<p>\r\nIntercon solicits bids for games in rounds, as needed, based on the\r\nnumber of registrants we get. It\'s our goal to have a great schedule\r\nof games up as  early as possible!</p>\r\n<p>\r\nGame bids received before the deadline will be evaluated in a timely\r\nmanner.\r\n<i>Early bids will get an early decision!</i></p>',36,'2011-02-09 19:12:24');
/*!40000 ALTER TABLE `BidInfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BidStatus`
--

DROP TABLE IF EXISTS `BidStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BidStatus` (
  `BidStatusId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BidId` int(10) unsigned NOT NULL DEFAULT '0',
  `Consensus` enum('Discuss','Accept','Early Accepted','Reject','Drop') NOT NULL DEFAULT 'Discuss',
  `Issues` char(128) NOT NULL DEFAULT '',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`BidStatusId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BidStatus`
--

LOCK TABLES `BidStatus` WRITE;
/*!40000 ALTER TABLE `BidStatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `BidStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Bids`
--

DROP TABLE IF EXISTS `Bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Bids` (
  `BidId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `GameType` varchar(30) NOT NULL DEFAULT '',
  `Status` enum('Pending','Under Review','Accepted','Rejected','Dropped','Draft') NOT NULL DEFAULT 'Pending',
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `FirstName` varchar(30) NOT NULL DEFAULT '',
  `LastName` varchar(30) NOT NULL DEFAULT '',
  `EMail` varchar(64) NOT NULL DEFAULT '',
  `Age` int(11) NOT NULL DEFAULT '0',
  `Gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `Address1` varchar(64) NOT NULL DEFAULT '',
  `Address2` varchar(64) NOT NULL DEFAULT '',
  `City` varchar(64) NOT NULL DEFAULT '',
  `State` varchar(30) NOT NULL DEFAULT '',
  `Zipcode` varchar(10) NOT NULL DEFAULT '',
  `Country` varchar(30) NOT NULL DEFAULT '',
  `DayPhone` varchar(20) NOT NULL DEFAULT '',
  `EvePhone` varchar(20) NOT NULL DEFAULT '',
  `PreferredContact` enum('EMail','DayPhone','EvePhone') DEFAULT NULL,
  `BestTime` varchar(128) NOT NULL DEFAULT '',
  `EventId` int(10) unsigned NOT NULL DEFAULT '0',
  `Title` varchar(128) NOT NULL DEFAULT '',
  `Author` varchar(128) NOT NULL DEFAULT '',
  `GMs` text NOT NULL,
  `Homepage` varchar(128) NOT NULL DEFAULT '',
  `GameEMail` varchar(64) NOT NULL DEFAULT '',
  `Organization` varchar(64) NOT NULL DEFAULT '',
  `MinPlayersMale` int(10) unsigned NOT NULL DEFAULT '0',
  `MaxPlayersMale` int(10) unsigned NOT NULL DEFAULT '0',
  `PrefPlayersMale` int(10) unsigned NOT NULL DEFAULT '0',
  `MinPlayersFemale` int(10) unsigned NOT NULL DEFAULT '0',
  `MaxPlayersFemale` int(10) unsigned NOT NULL DEFAULT '0',
  `PrefPlayersFemale` int(10) unsigned NOT NULL DEFAULT '0',
  `MinPlayersNeutral` int(10) unsigned NOT NULL DEFAULT '0',
  `MaxPlayersNeutral` int(10) unsigned NOT NULL DEFAULT '0',
  `PrefPlayersNeutral` int(10) unsigned NOT NULL DEFAULT '0',
  `Hours` int(10) unsigned NOT NULL DEFAULT '0',
  `Minutes` int(10) unsigned NOT NULL DEFAULT '0',
  `Seconds` int(10) unsigned NOT NULL DEFAULT '0',
  `CanPlayConcurrently` enum('Y','N') DEFAULT NULL,
  `Fee` enum('Y','N') DEFAULT NULL,
  `Description` text,
  `Genre` varchar(64) NOT NULL DEFAULT '',
  `OngoingCampaign` enum('Y','N') DEFAULT NULL,
  `Premise` text NOT NULL,
  `RunBefore` text NOT NULL,
  `GameSystem` text,
  `CombatResolution` enum('Physical','NonPhysical','NoCombat','Other') DEFAULT NULL,
  `OtherDetails` text,
  `OtherGMs` text NOT NULL,
  `OtherGames` text NOT NULL,
  `Offensive` text,
  `PhysicalRestrictions` text,
  `AgeRestrictions` text,
  `SchedulingConstraints` text,
  `SpaceRequirements` text NOT NULL,
  `MultipleRuns` enum('Y','N') DEFAULT NULL,
  `ShortSentence` text,
  `ShortBlurb` text,
  `ShamelessPlugs` text,
  `GMGameAdvertising` text,
  `GMInterconAdvertising` text,
  `SendFlyers` enum('Y','N') DEFAULT NULL,
  `IsSmallGameContestEntry` enum('Y','N') NOT NULL DEFAULT 'N',
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `VideoOf` SET( 'I don\'t have any video of myself performing', 
                 'This is video of me but not the act I\'m submitting', 
                 'This is video of the act I would like to perform' ) NULL ,
  `VideoSource` VARCHAR( 128 ) NULL ,
  `PhotoSource` VARCHAR( 128 ) NULL,
  PRIMARY KEY (`BidId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Bids`
--

LOCK TABLES `Bids` WRITE;
/*!40000 ALTER TABLE `Bids` DISABLE KEYS */;
/*!40000 ALTER TABLE `Bids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BidTimes`
--

DROP TABLE IF EXISTS `BidTimes`;
CREATE TABLE `BidTimes` (
  `BidTimeId` int(10) unsigned NOT NULL auto_increment,
  `BidId` int(10) unsigned NOT NULL,
  `Day` enum('Monday','Tuesday','Wednesday','Thursday','Friday', 'Saturday', 'Sunday') NOT NULL,
  `Slot` enum('Morning', 'Lunch', 'Early Afternoon', 'Late Afternoon', 'Dinner', 'Evening', 'After Midnight' ) NOT NULL,
  `Pref` char(1) NOT NULL default '',
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`BidTimeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `PanelBids'
--

DROP TABLE IF EXISTS `PanelBids`;
CREATE TABLE `PanelBids` (
  `PanelBidsId` int(10) unsigned NOT NULL auto_increment,
  `BidId` int(10) unsigned NOT NULL,
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Interest` enum('no involvement', 'being a panelist', 'being the moderator') NOT NULL,
  `Expertise` text,
  PRIMARY KEY  (`PanelBidsId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `BidChoice`
--

DROP TABLE IF EXISTS `BidChoice`;
CREATE TABLE `BidChoice` (
  `BidChoice` int(10) unsigned NOT NULL auto_increment,
  `BidId` int(10) unsigned NOT NULL,
  `Question` varchar(128) NOT NULL,
  `Answer` enum('Yes', 'No', 'Yes - and Won!','Not Sure') NOT NULL,
  PRIMARY KEY  (`BidChoice`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `BidTimes`
--

LOCK TABLES `BidTimes` WRITE;
/*!40000 ALTER TABLE `BidTimes` DISABLE KEYS */;
/*!40000 ALTER TABLE `BidTimes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Bios`
--

DROP TABLE IF EXISTS `Bios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Bios` (
  `BioId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL DEFAULT '0',
  `BioText` text NOT NULL,
  `Title` text NOT NULL,
  `ShowNickname` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Website` VARCHAR( 128 ) NULL,
  `PhotoSource` VARCHAR( 128 ) NULL,
  PRIMARY KEY (`BioId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Bios`
--

LOCK TABLES `Bios` WRITE;
/*!40000 ALTER TABLE `Bios` DISABLE KEYS */;
/*!40000 ALTER TABLE `Bios` ENABLE KEYS */;
UNLOCK TABLES;

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
  `PreconBidsAllowed` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  PRIMARY KEY (`ConId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Con`
--

LOCK TABLES `Con` WRITE;
/*!40000 ALTER TABLE `Con` DISABLE KEYS */;
INSERT INTO `Con` VALUES (1,'NotYet','No','No news is good news',36,'2011-02-23 14:11:12','Stay tuned to this station for more exciting details','Yes','Yes');
/*!40000 ALTER TABLE `Con` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DeadDog`
--

DROP TABLE IF EXISTS `DeadDog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DeadDog` (
  `UserId` int(10) NOT NULL,
  `Status` enum('Unpaid','Paid','Cancelled') DEFAULT NULL,
  `PaymentAmount` int(10) DEFAULT NULL,
  `PaymentNote` text,
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Quantity` int(11) NOT NULL,
  `PaymentId` int(11) NOT NULL AUTO_INCREMENT,
  `TxnId` varchar(255) NOT NULL,
  PRIMARY KEY (`PaymentId`),
  KEY `Status` (`Status`),
  KEY `TxnId` (`TxnId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DeadDog`
--

LOCK TABLES `DeadDog` WRITE;
/*!40000 ALTER TABLE `DeadDog` DISABLE KEYS */;
/*!40000 ALTER TABLE `DeadDog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Events`
--

DROP TABLE IF EXISTS `Events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Events` (
  `EventId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(128) NOT NULL DEFAULT '',
  `Author` varchar(128) DEFAULT '',
  `GameType` varchar(30) NOT NULL DEFAULT '',
  `GameEMail` varchar(64) NOT NULL DEFAULT '',
  `Organization` varchar(64) DEFAULT '',
  `Homepage` text,
  `CastingPage` varchar(128) NOT NULL DEFAULT '',
  `CastingReleased` enum('Y','N') NOT NULL DEFAULT 'N',
  `NotifyOnChanges` enum('Y','N') NOT NULL DEFAULT 'N',
  `MinPlayersMale` int(10) unsigned NOT NULL DEFAULT '0',
  `MaxPlayersMale` int(10) unsigned NOT NULL DEFAULT '0',
  `PrefPlayersMale` int(10) unsigned NOT NULL DEFAULT '0',
  `MinPlayersFemale` int(10) unsigned NOT NULL DEFAULT '0',
  `MaxPlayersFemale` int(10) unsigned NOT NULL DEFAULT '0',
  `PrefPlayersFemale` int(10) unsigned NOT NULL DEFAULT '0',
  `MinPlayersNeutral` int(10) unsigned NOT NULL DEFAULT '0',
  `MaxPlayersNeutral` int(10) unsigned NOT NULL DEFAULT '0',
  `PrefPlayersNeutral` int(10) unsigned NOT NULL DEFAULT '0',
  `Fee` varchar(30) DEFAULT '',
  `Hours` int(10) unsigned NOT NULL DEFAULT '0',
  `SpecialEvent` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `CanPlayConcurrently` enum('Y','N') NOT NULL DEFAULT 'N',
  `IsOps` enum('Y','N') NOT NULL DEFAULT 'N',
  `IsConSuite` enum('Y','N') NOT NULL DEFAULT 'N',
  `IsIronGm` enum('Y','N') NOT NULL DEFAULT 'N',
  `IsSmallGameContestEntry` enum('Y','N') NOT NULL DEFAULT 'N',
  `ConMailDest` enum('GameMail','GMs') NOT NULL DEFAULT 'GMs',
  `Description` text NOT NULL,
  `ShortBlurb` text NOT NULL,
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EventId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Events`
--

LOCK TABLES `Events` WRITE;
/*!40000 ALTER TABLE `Events` DISABLE KEYS */;
/*!40000 ALTER TABLE `Events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `GMs`
--

DROP TABLE IF EXISTS `GMs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GMs` (
  `GMId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL DEFAULT '0',
  `EventId` int(11) NOT NULL DEFAULT '0',
  `Submitter` enum('Y','N') NOT NULL DEFAULT 'N',
  `DisplayAsGM` enum('Y','N') NOT NULL DEFAULT 'Y',
  `DisplayEMail` enum('Y','N') NOT NULL DEFAULT 'Y',
  `ReceiveConEMail` enum('Y','N') NOT NULL DEFAULT 'N',
  `ReceiveSignupEMail` enum('Y','N') NOT NULL DEFAULT 'N',
  `Role` enum( "panelist", "moderator" ) NULL DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`GMId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `GMs`
--

LOCK TABLES `GMs` WRITE;
/*!40000 ALTER TABLE `GMs` DISABLE KEYS */;
/*!40000 ALTER TABLE `GMs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IronGm`
--

DROP TABLE IF EXISTS `IronGm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IronGm` (
  `IronGmId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL,
  `TeamId` int(10) unsigned NOT NULL,
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`IronGmId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IronGm`
--

LOCK TABLES `IronGm` WRITE;
/*!40000 ALTER TABLE `IronGm` DISABLE KEYS */;
/*!40000 ALTER TABLE `IronGm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IronGmTeam`
--

DROP TABLE IF EXISTS `IronGmTeam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `IronGmTeam` (
  `TeamId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) NOT NULL DEFAULT '',
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TeamId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IronGmTeam`
--

LOCK TABLES `IronGmTeam` WRITE;
/*!40000 ALTER TABLE `IronGmTeam` DISABLE KEYS */;
/*!40000 ALTER TABLE `IronGmTeam` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Plugs`
--

DROP TABLE IF EXISTS `Plugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Plugs` (
  `PlugId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL,
  `Name` varchar(64) NOT NULL DEFAULT '',
  `Url` text NOT NULL,
  `Text` text NOT NULL,
  `EndDate` date NOT NULL,
  `Visible` enum('Y','N') DEFAULT 'N',
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PlugId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Plugs`
--

LOCK TABLES `Plugs` WRITE;
/*!40000 ALTER TABLE `Plugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `Plugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Poll`
--

DROP TABLE IF EXISTS `Poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Poll` (
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Vote` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserId`),
  UNIQUE KEY `UserId` (`UserId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Poll`
--

LOCK TABLES `Poll` WRITE;
/*!40000 ALTER TABLE `Poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `Poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PreConEvents`
--

DROP TABLE IF EXISTS `PreConEvents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PreConEvents` (
  `PreConEventId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Status` enum('Pending','Accepted','Rejected','Dropped') NOT NULL DEFAULT 'Pending',
  `Title` varchar(128) NOT NULL DEFAULT '',
  `SubmitterUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Hours` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `SpecialRequests` varchar(128) NOT NULL DEFAULT '',
  `InviteOthers` enum('Y','N') NOT NULL DEFAULT 'N',
  `Kind` varchar(64) NOT NULL DEFAULT '',
  `Thursday21` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Thursday22` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Thursday23` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday09` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday10` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday11` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday12` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday13` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday14` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday15` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday16` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `Friday17` enum('-','1','2','3','X') NOT NULL DEFAULT '-',
  `ShortDescription` text NOT NULL,
  `Description` text NOT NULL,
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PreConEventId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PreConEvents`
--

LOCK TABLES `PreConEvents` WRITE;
/*!40000 ALTER TABLE `PreConEvents` DISABLE KEYS */;
/*!40000 ALTER TABLE `PreConEvents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PreConRuns`
--

DROP TABLE IF EXISTS `PreConRuns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PreConRuns` (
  `PreConRunId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PreConEventId` int(10) unsigned NOT NULL DEFAULT '0',
  `Day` enum('Thu','Fri') NOT NULL DEFAULT 'Fri',
  `StartHour` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Rooms` SET( 'Master/Drop-In Space','Dance Space', 'Lecture Space A', 'Lecture Space B', 'Workshop Space', 'Food Space', 'Theater', 'Vendors', 'Pool' ) NOT NULL DEFAULT '',
  `UpdatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PreConRunId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PreConRuns`
--

LOCK TABLES `PreConRuns` WRITE;
/*!40000 ALTER TABLE `PreConRuns` DISABLE KEYS */;
/*!40000 ALTER TABLE `PreConRuns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Referrers`
--

DROP TABLE IF EXISTS `Referrers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Referrers` (
  `ReferrerId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Url` text NOT NULL,
  `UserId` int(10) unsigned NOT NULL DEFAULT '1',
  `NewUser` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `AtSite` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReferrerId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Referrers`
--

LOCK TABLES `Referrers` WRITE;
/*!40000 ALTER TABLE `Referrers` DISABLE KEYS */;
/*!40000 ALTER TABLE `Referrers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Runs`
--

DROP TABLE IF EXISTS `Runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Runs` (
  `RunId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `EventId` int(11) NOT NULL DEFAULT '0',
  `Track` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Span` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `Day` enum('Fri','Sat','Sun') NOT NULL DEFAULT 'Fri',
  `StartHour` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `TitleSuffix` char(32) DEFAULT '',
  `ScheduleNote` char(32) NOT NULL DEFAULT '',
  `Rooms` set('Master/Drop-In Space','Dance Space', 'Lecture Space A', 'Lecture Space B', 
		'Workshop Space', 'Food Space', 'Theater', 'Vendors', 'Pool') NOT NULL DEFAULT '',
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RunId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Runs`
--

LOCK TABLES `Runs` WRITE;
/*!40000 ALTER TABLE `Runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `Runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Signup`
--

DROP TABLE IF EXISTS `Signup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Signup` (
  `SignupId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL DEFAULT '0',
  `RunId` int(11) NOT NULL DEFAULT '0',
  `State` enum('Confirmed','Waitlisted','Withdrawn') NOT NULL DEFAULT 'Confirmed',
  `PrevState` enum('Confirmed','Waitlisted','Withdrawn','None') DEFAULT 'None',
  `Gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `Counted` enum('Y','N') NOT NULL DEFAULT 'Y',
  `UpdatedById` int(11) NOT NULL DEFAULT '0',
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SignupId`),
  KEY `State` (`State`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Signup`
--

LOCK TABLES `Signup` WRITE;
/*!40000 ALTER TABLE `Signup` DISABLE KEYS */;
/*!40000 ALTER TABLE `Signup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TShirts`
--

DROP TABLE IF EXISTS `TShirts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TShirts` (
  `TShirtID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL DEFAULT '0',
  `Status` enum('Unpaid','Paid','Cancelled') NOT NULL DEFAULT 'Unpaid',
  `PaymentAmount` int(11) NOT NULL DEFAULT '0',
  `Small` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Medium` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Large` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `XLarge` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `XXLarge` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `X3Large` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `X4Large` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `X5Large` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Small_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Medium_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Large_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `XLarge_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `XXLarge_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `X3Large_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `X4Large_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `X5Large_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `PaymentNote` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TShirtID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TShirts`
--

LOCK TABLES `TShirts` WRITE;
/*!40000 ALTER TABLE `TShirts` DISABLE KEYS */;
/*!40000 ALTER TABLE `TShirts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Thursday`
--

DROP TABLE IF EXISTS `Thursday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Thursday` (
  `UserId` int(10) unsigned NOT NULL,
  `Status` enum('Unpaid','Paid','Cancelled') NOT NULL DEFAULT 'Unpaid',
  `PaymentAmount` int(11) NOT NULL DEFAULT '0',
  `PaymentNote` text NOT NULL,
  `UpdatedById` int(11) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Thursday`
--

LOCK TABLES `Thursday` WRITE;
/*!40000 ALTER TABLE `Thursday` DISABLE KEYS */;
/*!40000 ALTER TABLE `Thursday` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `HashedPassword` char(32) DEFAULT NULL,
  `FirstName` char(30) NOT NULL DEFAULT '',
  `LastName` char(30) NOT NULL DEFAULT '',
  `StageName` char(60) NOT NULL DEFAULT '',
  `DisplayName` char(60) NOT NULL DEFAULT '',
  `Nickname` char(30) NOT NULL DEFAULT '',
  `EMail` char(64) NOT NULL DEFAULT '',
  `Age` int(11) NOT NULL DEFAULT '0',
  `BirthYear` int(10) unsigned NOT NULL DEFAULT '0',
  `Gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `Address1` char(64) NOT NULL DEFAULT '',
  `Address2` char(64) NOT NULL DEFAULT '',
  `City` char(64) NOT NULL DEFAULT '',
  `State` char(30) NOT NULL DEFAULT '',
  `Zipcode` char(10) NOT NULL DEFAULT '',
  `Country` char(30) NOT NULL DEFAULT '',
  `DayPhone` char(20) NOT NULL DEFAULT '',
  `EvePhone` char(20) NOT NULL DEFAULT '',
  `BestTime` char(128) NOT NULL DEFAULT '',
  `HowHeard` char(64) NOT NULL DEFAULT '',
  `PaymentNote` char(128) NOT NULL DEFAULT '',
  `PaymentAmount` int(11) NOT NULL DEFAULT '0',
  `TShirt` enum('No','S','M','L','XL','XXL') NOT NULL DEFAULT 'No',
  `PreferredContact` enum('EMail','DayPhone','EvePhone') DEFAULT NULL,
  `Priv` set('BidCom','Staff','Admin','BidChair','GMLiaison','MailToGMs','MailToAttendees','MailToAll','MailToVendors','Registrar','Outreach','ConCom','Scheduling','MailToUnpaid','MailToAlumni','PreConBidChair','PreConScheduling','ShowCom','ShowChair') NOT NULL DEFAULT '',
  `CanSignup` enum('Alumni','Unpaid','Paid','Comp','Marketing','Vendor','Rollover') NOT NULL DEFAULT 'Unpaid',
  `CanSignupModifiedId` int(10) unsigned NOT NULL DEFAULT '0',
  `CompEventId` int(10) unsigned NOT NULL DEFAULT '0',
  `ModifiedBy` int(11) NOT NULL DEFAULT '0',
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastLogin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `CanSignupModified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `openid` VARCHAR( 255 ) NOT NULL,
  KEY `CanSignup` (`CanSignup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-02-23  9:13:55
