-- --------------------------------------------------------
-- Host:                         barkeep.beefyhost.com
-- Server version:               10.0.24-MariaDB-1~precise - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for barkeep
CREATE DATABASE IF NOT EXISTS `barkeep` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `barkeep`;


-- Dumping structure for table barkeep.lkpBarsIngredients
CREATE TABLE IF NOT EXISTS `lkpBarsIngredients` (
  `barID` bigint(20) unsigned NOT NULL,
  `ingredientID` bigint(20) unsigned NOT NULL,
  KEY `FK_lkpBarsIngredients_tblBars` (`barID`),
  KEY `FK_lkpBarsIngredients_tblIngredients` (`ingredientID`),
  CONSTRAINT `FK_lkpBarsIngredients_tblBars` FOREIGN KEY (`barID`) REFERENCES `tblBars` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_lkpBarsIngredients_tblIngredients` FOREIGN KEY (`ingredientID`) REFERENCES `tblIngredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.


-- Dumping structure for table barkeep.tblBars
CREATE TABLE IF NOT EXISTS `tblBars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `type` enum('Private','Public','Shared') NOT NULL DEFAULT 'Private',
  `title` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `description` text,
  `createStamp` double unsigned DEFAULT NULL,
  `modifyStamp` double unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tblBars_tblUsers` (`userID`),
  KEY `path` (`path`(8)),
  CONSTRAINT `FK_tblBars_tblUsers` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.


-- Dumping structure for table barkeep.tblBooks
CREATE TABLE IF NOT EXISTS `tblBooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `type` enum('Private','Public','Shared') NOT NULL DEFAULT 'Private',
  `title` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `description` text,
  `createStamp` double unsigned DEFAULT NULL,
  `modifyStamp` double unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_tblBoolks_tblUsers` (`userID`),
  CONSTRAINT `FK_tblBoolks_tblUsers` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.


-- Dumping structure for table barkeep.tblIngredients
CREATE TABLE IF NOT EXISTS `tblIngredients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `baseIngredientID` bigint(20) unsigned DEFAULT NULL,
  `createStamp` double unsigned DEFAULT NULL,
  `modifyStamp` double unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tblIngredients_tblIngredients` (`baseIngredientID`),
  KEY `path` (`path`(8)),
  CONSTRAINT `FK_tblIngredients_tblIngredients` FOREIGN KEY (`baseIngredientID`) REFERENCES `tblIngredients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.


-- Dumping structure for table barkeep.tblRecipeComponents
CREATE TABLE IF NOT EXISTS `tblRecipeComponents` (
  `recipeID` bigint(20) unsigned NOT NULL,
  `ingredientID` bigint(20) unsigned NOT NULL,
  `min` double unsigned DEFAULT NULL,
  `max` double unsigned DEFAULT NULL,
  `componentNum` smallint(5) unsigned DEFAULT NULL,
  `order` smallint(5) unsigned DEFAULT NULL,
  KEY `FK_tblRecipeComponents_tblRecipes` (`recipeID`),
  KEY `FK_tblRecipeComponents_tblIngredients` (`ingredientID`),
  CONSTRAINT `FK_tblRecipeComponents_tblIngredients` FOREIGN KEY (`ingredientID`) REFERENCES `tblIngredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tblRecipeComponents_tblRecipes` FOREIGN KEY (`recipeID`) REFERENCES `tblRecipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.


-- Dumping structure for table barkeep.tblRecipes
CREATE TABLE IF NOT EXISTS `tblRecipes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bookID` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `description` text,
  `imageURL` text,
  `instructions` text,
  `source` text,
  `createStamp` double unsigned DEFAULT NULL,
  `modifyStamp` double unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tblRecipes_tblBooks` (`bookID`),
  KEY `path` (`path`(8)),
  CONSTRAINT `FK_tblRecipes_tblBooks` FOREIGN KEY (`bookID`) REFERENCES `tblBooks` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.


-- Dumping structure for table barkeep.tblUsers
CREATE TABLE IF NOT EXISTS `tblUsers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `accountType` enum('Standard','Google') NOT NULL,
  `displayName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `createStamp` double unsigned DEFAULT NULL,
  `modifyStamp` double unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
