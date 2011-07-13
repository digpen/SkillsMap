-- phpMyAdmin SQL Dump
-- version 3.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2011 at 01:47 PM
-- Server version: 5.1.56
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `digpen`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `companyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `companyName` varchar(32) NOT NULL,
  `companyDescription` text NOT NULL,
  PRIMARY KEY (`companyID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `companies`
--


-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE IF NOT EXISTS `profiles` (
  `profileID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profileName` varchar(32) NOT NULL,
  `profileSeoName` varchar(32) NOT NULL,
  `profileCompany` int(10) unsigned NOT NULL DEFAULT '0',
  `profileEmail` varchar(320) NOT NULL,
  `profileAddress` tinytext NOT NULL,
  `profilePostcode` int(9) NOT NULL,
  `profileLat` float(10,6) DEFAULT NULL,
  `profileLng` float(10,6) DEFAULT NULL,
  `profileTelephone` varchar(11) NOT NULL DEFAULT '',
  `profileDescription` text NOT NULL,
  `profileImage` varchar(32) NOT NULL DEFAULT '',
  `profileWebsite` varchar(255) NOT NULL DEFAULT '',
  `profileTwitter` varchar(64) NOT NULL DEFAULT '',
  `profileSkype` varchar(64) NOT NULL DEFAULT '',
  `profilePublish` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `profileApproved` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`profileID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `profiles`
--


-- --------------------------------------------------------

--
-- Table structure for table `profileskills`
--

DROP TABLE IF EXISTS `profileskills`;
CREATE TABLE IF NOT EXISTS `profileskills` (
  `psID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `psProfile` int(10) unsigned NOT NULL,
  `psSkill` int(10) unsigned NOT NULL,
  `psInterest` enum('Can do if I must','I quite like doing this','This is where the heart is') NOT NULL,
  `psLevel` enum('Beginner','Intermediate','Expert') NOT NULL,
  PRIMARY KEY (`psID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `profileskills`
--


-- --------------------------------------------------------

--
-- Table structure for table `skillgroups`
--

DROP TABLE IF EXISTS `skillgroups`;
CREATE TABLE IF NOT EXISTS `skillgroups` (
  `sgID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sgName` varchar(32) NOT NULL,
  `sgDescription` tinytext NOT NULL,
  PRIMARY KEY (`sgID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `skillgroups`
--

INSERT INTO `skillgroups` (`sgID`, `sgName`, `sgDescription`) VALUES
(1, 'Web Design', 'Web design skills'),
(2, 'Web Development', 'Web Development Skill');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE IF NOT EXISTS `skills` (
  `skillID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `skillGroup` int(10) unsigned NOT NULL,
  `skillName` varchar(32) NOT NULL,
  `skillDescription` tinytext NOT NULL,
  PRIMARY KEY (`skillID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skillID`, `skillGroup`, `skillName`, `skillDescription`) VALUES
(1, 1, 'XHTML', 'eXtensible Markup Language including HTML5'),
(2, 1, 'CSS', 'Cascading Style Sheets'),
(3, 1, 'Javascript', 'General javascript including DOM and JSON'),
(4, 1, 'jQuery', 'Skills using the jQuery framework'),
(5, 2, 'PHP', 'Hypertext Pre-Processor, serverside scripting language'),
(6, 2, 'MySQL', 'Open source database'),
(7, 3, 'Ruby on Rails', 'Server side programming language');
