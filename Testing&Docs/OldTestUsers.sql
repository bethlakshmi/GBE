-- phpMyAdmin SQL Dump
-- version 3.4.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 28, 2013 at 01:38 PM
-- Server version: 5.5.35
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `firstla5_testexpo`
--

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `HashedPassword` char(32) DEFAULT NULL,
  `FirstName` char(30) NOT NULL DEFAULT '',
  `LastName` char(30) NOT NULL DEFAULT '',
  `DisplayName` char(64) NOT NULL DEFAULT '',
  `StageName` char(64) NOT NULL DEFAULT '',
  `Nickname` char(30) NOT NULL DEFAULT '',
  `EMail` char(64) NOT NULL DEFAULT '',
  `PurchaseEmail` char(64) NOT NULL,
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
  `TShirt` enum('No','S','M','L','XL','XXL') NOT NULL DEFAULT 'No',
  `PreferredContact` enum('EMail','DayPhone','EvePhone') DEFAULT NULL,
  `Priv` set('BidCom','Staff','Admin','BidChair','GMLiaison','MailToGMs','MailToAttendees','MailToAll','MailToVendors','Registrar','Outreach','ConCom','Scheduling','MailToUnpaid','MailToAlumni','PreConBidChair','PreConScheduling','ShowCom','ShowChair') NOT NULL DEFAULT '',
  `CanSignup` enum('Alumni','Comp','Marketing','Vendor','Rollover','None','Signed-In') NOT NULL DEFAULT 'None',
  `CanSignupModifiedId` int(10) unsigned NOT NULL DEFAULT '0',
  `CompEventId` int(10) unsigned NOT NULL DEFAULT '0',
  `ModifiedBy` int(11) NOT NULL DEFAULT '0',
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastLogin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `CanSignupModified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `openid` varchar(255) NOT NULL,
  PRIMARY KEY (`UserId`),
  KEY `CanSignup` (`CanSignup`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UserId`, `HashedPassword`, `FirstName`, `LastName`, `DisplayName`, `StageName`, `Nickname`, `EMail`, `PurchaseEmail`, `Age`, `BirthYear`, `Gender`, `Address1`, `Address2`, `City`, `State`, `Zipcode`, `Country`, `DayPhone`, `EvePhone`, `BestTime`, `HowHeard`, `PaymentNote`, `TShirt`, `PreferredContact`, `Priv`, `CanSignup`, `CanSignupModifiedId`, `CompEventId`, `ModifiedBy`, `Modified`, `LastLogin`, `CanSignupModified`, `Created`, `openid`) VALUES
(13, 'b81cc5df1c80515c984ab97d8e8d4378', 'Amy', 'Priest', 'Scarlett (test1)', 'Scarlett (test1)', '', 'bythebooktotheletter@gmail.com', 'bythebooktotheletter@gmail.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', 'Scheduling,ShowCom', 'None', 0, 0, 1, '2013-12-21 22:38:53', '2013-08-27 05:42:21', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawknQ3txjU93S7yBfbUBZ2lpRUb__KievJ4'),
(12, 'e131a043f2a0cf79523af2ebaaf7fab5', 'Amy', 'Priest', 'Scarlett Letter', 'Scarlett Letter', '', 'letter.scarlett@gmail.com', 'letter.scarlett@gmail.com', 0, 0, '', '14622 ventura blvd #442', '', 'sherman oaks', 'California', '91403', 'United States', '8184391578', '8184391578', 'after 2pm before 5 am - PST', 'GBE 2013', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-23 08:08:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawnLi_7f1s5Hw5ZGyzKZk9ypxGqNg_JY5Cw'),
(9, '808166da22527eaf73460436058ee99c', 'Hunter', 'Heinlen', 'Hunter Heinlen', '', '', 'dracus@speakeasy.net', 'dracus@speakeasy.net', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', 'Scheduling,ShowCom', 'Comp', 1, 0, 1, '2013-12-21 22:38:53', '2013-08-28 22:38:45', '2013-08-12 22:12:40', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/PnRV2xYSs8keY3SdXEibJb7ii4j21g--'),
(7, 'd1502d8179ac67b8a9d5bc5c38392ae3', '', '', 'Ippy!', '', '', 'jeff.ippolito@gmail.com', 'jeff.ippolito@gmail.com', 0, 0, 'Male', '', '', '', '', '', '', '', '', '', '', '', 'No', NULL, 'Staff', 'None', 0, 0, 0, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(11, 'e49fd3079785441e352f7455f4d4f616', 'Jeffs', 'BasicUser', 'Jeffs_Basic_User', 'Jeffs_Basic_User', '', 'jki25@hotmail.com', 'jki25@hotmail.com', 0, 0, '', '1 Some Ave', '', 'Boston', 'Ma', '02111', 'USA', '555-867-5309', '', '', 'I work here', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-12 00:29:43', '0000-00-00 00:00:00', '2013-08-11 16:24:01', ''),
(3, '749a0f15ac0e0e09bfe33c39453df775', 'Mister', 'Scratch', 'Scratch', 'Scratch', '', 'scratch@bostonbabydolls.com', 'scratch@bostonbabydolls.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', 'BidCom,Staff,BidChair,GMLiaison,MailToGMs,MailToAttendees,MailToAll,MailToVendors,Registrar,Outreach,ConCom,Scheduling,MailToUnpaid,PreConBidChair,PreConScheduling,ShowCom,ShowChair', 'Comp', 3, 0, 3, '2013-12-28 04:46:25', '2013-12-28 04:46:25', '2013-08-23 13:48:44', '0000-00-00 00:00:00', ''),
(8, '45980f83dc69cc713b127957d4655a9c', 'Betty', 'Google', 'Googly Betty', 'Googly Betty', '', 'betty@google.test', 'bethlakshmi@gmail.com', 0, 0, '', 'please let this work', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 1, '2013-12-21 22:41:05', '2013-12-21 22:41:05', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawnOumGtely6pZUfqJTruwqRq3xdpVCyIjk'),
(6, 'c78e2da68ca63d55b53f8a58a79f6718', 'Beth', 'Test', 'Beth Test', '', '', 'betty@burlesque-expo.com', 'betty@burlesque-expo.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 1, 0, 1, '2013-12-21 22:38:53', '2013-11-27 13:35:30', '2013-06-13 09:42:45', '0000-00-00 00:00:00', ''),
(5, '2a0618570833cc7b1f2469674a8b88ae', 'Scandal from', 'Bohemia', 'Scandal from Bohemia', '', '', 'scandalfrombohemia@gmail.com', 'scandalfrombohemia@gmail.com', 0, 0, 'Male', '', '', '', '', '', 'Bohemia', '', '', '', '', '', 'No', 'EMail', 'BidCom,BidChair,MailToGMs,MailToAttendees,MailToAll', 'Comp', 1, 0, 1, '2013-12-21 22:38:53', '2013-08-20 22:09:38', '2013-06-12 10:07:11', '0000-00-00 00:00:00', ''),
(4, '05a671c66aefea124cc08b76ea6d30bb', 'Test', 'One', 'Test One', '', 'Test1', 'INFO@BURLESQUE-EXPO.COM', 'INFO@BURLESQUE-EXPO.COM', 0, 1990, 'Male', '575 Memorial Dr', '', 'Cambridge', 'MA', '02139', 'United States', '6178692000', '6178692000', '', 'I hear voices.', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-05-28 10:24:41', '0000-00-00 00:00:00', '2013-05-28 10:24:41', ''),
(1, '05a671c66aefea124cc08b76ea6d30bb', 'Betty', 'Blaize', 'Betty Blaize', 'Betty Blaize', '', 'bethlakshmi@gmail.com', 'bethlakshmi@gmail.com', 0, 0, '', '', '', '', '', '', '', '', '', '', 'bethlakshmi@gmail.com', '', 'No', 'EMail', 'Staff', 'None', 0, 0, 1, '2013-12-22 21:27:59', '2013-12-22 21:27:59', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(28, 'a470c7586f0612fa2a31bfdfbdb56453', 'Mark', 'Cockrum', 'Marcus DeBoyz', 'Marcus DeBoyz', '', 'marcus.deboyz@gmail.com', 'marcus.deboyz@gmail.com', 0, 0, 'Male', '', '', 'Seattle', 'WA', '98103', 'United States', '206-794-2638', '', '', 'A small bird told me about it.', '', 'No', 'EMail', 'Staff', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-12-06 18:38:22', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawkc3B9vXOd3Yz4kk-U3lgZwQHVrNH39AbI'),
(15, '7a561ab34a48726d2d41db241369cc95', 'Boston', 'Babydolls', 'Boston Babydolls Google', 'Boston Babydolls Google', '', 'bostonbabydolls@gmail.com', 'bostonbabydolls@gmail.com', 0, 0, '', '', '', '', '', '', '', '6178692000', '', '', 'Le Expo, c''est moi.', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-20 20:46:33', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawk5CpN-xyDcX4EUUxAS7BPtqZfW_Wa9QL8'),
(16, '6d7e2f7298c5928e6357aab9071ae100', 'LiveJournal', 'Scratch', 'Scratch LiveJournal', 'Scratch LiveJournal', '', 'thescoop@bostonbabydolls.com', 'thescoop@bostonbabydolls.com', 0, 0, '', '119 Braintree St., Suite 206', 'Suite 206', 'Allston', 'Massachusetts', '02134', 'United States', '6178692000', '6178692000', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-25 12:37:20', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'http://new-man.livejournal.com/'),
(17, '6c0c216a79fde0659772b9d72cbda9f0', 'Jennifer', 'Lyons', 'Bettysioux Tailor', 'Bettysioux Tailor', '', 'Jnikkifinn@aol.com', 'Jnikkifinn@aol.com', 0, 0, '', '77 Homer St.', '', 'Providence', 'RI', '02905', '', '401-632-9384', '', '', 'Lady Miss Iris', '', 'No', 'EMail', 'Outreach,Scheduling', 'Alumni', 3, 0, 3, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '2013-08-20 22:13:12', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawkMUo7zrESIv8C3gJwAti1JiU9Or9q9jY0'),
(19, 'f1fb001b45fb809085fb4ea43ca053c9', 'Scot', 'Garrett', 'Scot Garrett', '', '', 'theaveragehoipolloi@hotmail.com', 'theaveragehoipolloi@hotmail.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', 'MailToAttendees,Scheduling', 'Comp', 3, 0, 3, '2013-12-21 22:38:53', '2013-08-22 20:00:10', '2013-08-22 03:14:16', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawngTwPzmifGmPsHqE1BuZHfmoXWF6UVFqw'),
(22, '1e376fd47604addbf056f8fbc2cc4659', 'Lysha', 'Hamm', 'Dahlia Fatale', 'Dahlia Fatale', '', 'theladyfatal@gmail.com', 'theladyfatal@gmail.com', 0, 0, '', '5007 N Hermitage Ave', '', 'Chicago', 'Il', '60640', 'USA', '970-250-8462', '', 'morning', 'past years!', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-25 22:02:33', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawnxXFGNbm2N2n37yO8EZIH-dkPhukUqkjs'),
(21, '016d3f1eeb7a471be9e44585b4ea7754', 'jon', 'kiparsky', 'jon kiparsky', 'jon kiparsky', '', 'jon.kiparsky@gmail.com', 'jon.kiparsky@gmail.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', 'BidCom,Staff,BidChair,GMLiaison,Registrar,Outreach,ConCom,Scheduling,PreConBidChair,PreConScheduling,ShowCom,ShowChair', 'None', 0, 0, 3, '2013-12-28 04:47:38', '2013-12-27 22:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(23, 'a6479d5fab5b40188bc1bf5d69ff59cb', 'jpk', 'test', 'jpk test', '', '', 'jpk@kiparsky.net', 'jpk@kiparsky.net', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 23, '2013-12-21 22:38:53', '2013-09-06 19:02:14', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawmWyQhqVxryZqZQVSyB1kMbq9yZu6RcGk4'),
(24, '84fc546552da822a4937cb4a2b045f1f', 'yahoo', 'jpktest', 'yahoo jpktest', '', '', 'jpk_yahoo@kiparsky.net', 'jpk_yahoo@kiparsky.net', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-26 00:21:07', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/E4I11Kp_z8Ya3u9_PD8xl3D2CFA-'),
(25, '6df23dc03f9b54cc38a0fc1483df6e21', 'reg-w/o-signin', 'jpktest', 'reg-w/o-signin jpktest', '', '', 'jpk_noreg@kiparsky.net', 'jpk_noreg@kiparsky.net', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-25 23:23:06', '0000-00-00 00:00:00', '2013-08-25 23:23:06', ''),
(26, '2c1a72795e9edb4e6c9306d0ad5cc13c', 'Jeff', 'Test', 'JeffsBasicUser', 'JeffsBasicUser', '', 'JeffsBasicUser@yahoo.com', 'JeffsBasicUser@yahoo.com', 0, 0, '', 'Test Address 1', 'Test Address 2', 'boston', 'ma', '02111', 'USA', '5558675309', '5558675309', 'Day', 'I Work here', '', 'No', 'EMail', '', 'None', 0, 0, 26, '2013-12-21 22:38:53', '2013-08-26 01:42:02', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/u32S4lM4m_ntO2_TQeRXqSeDyGEQq3Va1bzc'),
(27, '69b012ce5aa1261bf1e84db9e9828eec', 'test 3', 'scarlett', 'test3', 'test3', '', 'apriest_76@yahoo.com', 'apriest_76@yahoo.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/X4ULdIs5j8CeHEiY2q2ZtManU_OX7pU-'),
(30, '65d20fb84b4339195ee1213a7507c294', 'Yvette', 'Zaepfel', 'Scandal from Bohemia', 'Scandal from Bohemia', '', 'yzaepfel@gmail.com', 'yzaepfel@gmail.com', 0, 0, '', '1736 N 130th St.', '', 'Seattle', 'WA', '98133', 'U.S.A.', '206-919-6837', '', 'any time', 'friend', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-08-30 03:04:47', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://www.google.com/accounts/o8/id?id=AItOawl9-rvtnGGW7FjxMl0oXe9cvVB-fLBuqb0'),
(36, NULL, 'Sarah', 'Blodgett', 'Sarah Blodgett', '', '*Auto-Generated User*', 'sarahkblodgett@yahoo.com', 'sarahkblodgett@yahoo.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '5087563308', '5087563308', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(37, NULL, 'Holly', 'Deck', 'Holly Deck', '', '*Auto-Generated User*', 'sarahlynnecronin@gmail.com', 'sarahlynnecronin@gmail.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '7737933000', '7737933000', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(38, NULL, 'Erica', 'Ruane', 'Erica Ruane', '', '*Auto-Generated User*', 'ruane.erica@gmail.com', 'ruane.erica@gmail.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '5083204263', '5083204263', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(39, NULL, 'Brian', 'Bergeron', 'Brian Bergeron', '', '*Auto-Generated User*', 'powerlinechief@aol.com', 'powerlinechief@aol.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '781-758-4949', '781-758-4949', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(40, NULL, 'Cherry', 'Killer Tomatoes', 'Cherry Killer Tomatoes', '', '*Auto-Generated User*', 'tracy_meeker@hotmail.com', 'tracy_meeker@hotmail.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '2064551933', '2064551933', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(41, NULL, 'Robert', 'Packie', 'Robert Packie', '', '*Auto-Generated User*', 'tpackie@gmail.com', 'tpackie@gmail.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '207-288-5442', '207-288-5442', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(42, NULL, 'Trixie', 'Santiago', 'Trixie Santiago', '', '*Auto-Generated User*', 'santiago.trixie@gmail.com', 'santiago.trixie@gmail.com', 0, 0, 'Male', '', '', '', '', '', 'United States', '206-434-0768', '206-434-0768', '', '', '', 'No', NULL, '', 'None', 0, 0, 28, '2013-12-21 22:38:53', '2013-10-04 23:52:18', '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(43, '3689cee1d4bee341d508b86d115abc5e', 'Mark', 'Cockrum', 'Mr. Mark Cockrum', 'Mr. Mark Cockrum', '', 'marcus.deboyz@yahoo.com', 'marcus.deboyz@yahoo.com', 0, 0, '', '', '', 'Seattle', 'WA', '98103', '', '425-555-12121', '', '', 'Test Site', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-10-08 23:53:28', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/XYGoIuNvzI6tcCKiEvKz62GpkeTD4xADOEI-'),
(44, '560c3b64f92be353896d52748abf6028', 'Jim', 'Cockrum', 'Jimbo Cockrum', 'Jimbo Cockrum', '', 'markallen1327@yahoo.com', 'markallen1327@yahoo.com', 0, 0, '', '', '', 'Seattle', 'WA', '98103', '', '', '', '', 'Test Account', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/UH61MbA6veMTE8gz4LhNtFDRo_Dn8CXxDwE-'),
(45, '2393523ab6965d24d7e84dc5816b981d', 'Yahoo', 'Betty', 'Yahoo Betty', '', '', 'bethlakshmi@yahoo.com', 'bethlakshmi@yahoo.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'https://me.yahoo.com/a/4fClQMIA3MV8SNdPAees_hVWupkXTYLz'),
(46, '6df23dc03f9b54cc38a0fc1483df6e21', 'jpk', 'jpk', 'jpk jpk', '', '', 'jpvk@kiparsky.net', 'jpvk@kiparsky.net', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 0, 0, 0, '2013-12-21 22:38:53', '2013-10-23 18:37:54', '0000-00-00 00:00:00', '2013-10-22 16:28:17', ''),
(47, NULL, 'Performer', 'Test1', 'Performer Test1', '', '', 'ptest1@test.com', 'ptest1@test.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 1, 0, 1, '2013-12-21 22:38:53', '2013-11-27 04:22:09', '2013-11-26 04:20:14', '0000-00-00 00:00:00', ''),
(48, NULL, 'Performer', 'Test2', 'Performer Test2', '', '', 'ptest2@test.com', 'ptest2@test.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 1, 0, 1, '2013-12-21 22:38:53', '2013-11-27 04:39:28', '2013-11-26 04:20:36', '0000-00-00 00:00:00', ''),
(49, NULL, 'Tech', 'Test1', 'Tech Test1', '', '', 'ttest1@test.com', 'ttest1@test.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 1, 0, 1, '2013-12-21 22:38:53', '2013-11-27 15:01:57', '2013-11-26 04:20:54', '0000-00-00 00:00:00', ''),
(50, NULL, 'Tech', 'Test2', 'Tech Test2', '', '', 'ttest2@test.com', 'ttest2@test.com', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 'No', 'EMail', '', 'None', 1, 0, 1, '2013-12-21 22:38:53', '2013-11-27 15:02:51', '2013-11-26 04:21:22', '0000-00-00 00:00:00', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
