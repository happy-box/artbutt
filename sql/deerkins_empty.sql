-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2012 at 03:04 PM
-- Server version: 5.0.91
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `deerkins`
--

-- --------------------------------------------------------

--
-- Table structure for table `deer`
--

CREATE TABLE IF NOT EXISTS `deer` (
  `id` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `creator` varchar(255) NOT NULL,
  `deer` varchar(255) NOT NULL,
  `kinskode` text NOT NULL,
  `irccode` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `deer`
--

INSERT INTO `deer` (`date`, `creator`, `deer`, `kinskode`, `irccode`) VALUES
('2008-11-21 02:06:43', 'n/a', 'deer', '        A  A \n        A  A \n         AA  \n        AAA  \n         AA  \n  AAAAAAAAA  \n AAAAAAAAAA  \n AAAAAAAAAA  \n A A    A A  \n A A    A A  \n A A    A A  ', '01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@00,00@01,01@01,01@00,00@01,01@\n01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@00,00@01,01@01,01@00,00@01,01@\n01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@00,00@00,00@01,01@01,01@\n01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@00,00@00,00@00,00@01,01@01,01@\n01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@01,01@00,00@00,00@01,01@01,01@\n01,01@01,01@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@01,01@01,01@\n01,01@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@01,01@01,01@\n01,01@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@00,00@01,01@01,01@\n01,01@00,00@01,01@00,00@01,01@01,01@01,01@01,01@00,00@01,01@00,00@01,01@01,01@\n01,01@00,00@01,01@00,00@01,01@01,01@01,01@01,01@00,00@01,01@00,00@01,01@01,01@\n01,01@00,00@01,01@00,00@01,01@01,01@01,01@01,01@00,00@01,01@00,00@01,01@01,01@');
