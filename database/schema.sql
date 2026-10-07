-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: xxx
-- Generation Time: Oct 07, 2026 at 07:54 AM
-- Server version: 8.4.11-11
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xxx`
--

-- --------------------------------------------------------

--
-- Table structure for table `Attachments`
--

CREATE TABLE `Attachments` (
  `attachmentId` int UNSIGNED NOT NULL,
  `userId` char(18) NOT NULL,
  `attachmentType` varchar(25) NOT NULL,
  `attachmentFile` longblob NOT NULL,
  `attachmentUploadTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Channels`
--

CREATE TABLE `Channels` (
  `channelId` int NOT NULL,
  `isTopChannel` tinyint(1) NOT NULL,
  `ownerChannelId` int DEFAULT NULL,
  `channelName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `channelDescription` varchar(255) DEFAULT NULL,
  `channelCreator` char(18) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE `Comments` (
  `commentId` int NOT NULL,
  `userId` char(18) NOT NULL,
  `ownerPostId` int NOT NULL,
  `messageContent` text NOT NULL,
  `timeStamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- --------------------------------------------------------

--
-- Table structure for table `Interactions`
--

CREATE TABLE `Interactions` (
  `interactionId` int UNSIGNED NOT NULL,
  `interactionType` tinyint UNSIGNED NOT NULL COMMENT '1 upvote, 2 downvote',
  `postId` int NOT NULL,
  `userId` char(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- --------------------------------------------------------

--
-- Table structure for table `Posts`
--

CREATE TABLE `Posts` (
  `postId` int NOT NULL,
  `userId` char(18) NOT NULL,
  `channelId` int NOT NULL,
  `postTitle` varchar(60) NOT NULL DEFAULT 'Untitled',
  `postContent` text NOT NULL,
  `timeStamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `userId` char(18) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `lastSeen` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Attachments`
--
ALTER TABLE `Attachments`
  ADD PRIMARY KEY (`attachmentId`),
  ADD KEY `attachmentType` (`attachmentType`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `Channels`
--
ALTER TABLE `Channels`
  ADD PRIMARY KEY (`channelId`),
  ADD KEY `fk_owner_channel` (`ownerChannelId`),
  ADD KEY `fk_channel_creator` (`channelCreator`),
  ADD KEY `channelName` (`channelName`);

--
-- Indexes for table `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`commentId`),
  ADD KEY `fk_comment_user` (`userId`),
  ADD KEY `fk_comment_post` (`ownerPostId`);

--
-- Indexes for table `Interactions`
--
ALTER TABLE `Interactions`
  ADD PRIMARY KEY (`interactionId`),
  ADD UNIQUE KEY `uniqueVotePerUser` (`postId`,`userId`),
  ADD KEY `interactionBy` (`userId`);

--
-- Indexes for table `Posts`
--
ALTER TABLE `Posts`
  ADD PRIMARY KEY (`postId`),
  ADD KEY `fk_post_user` (`userId`),
  ADD KEY `fk_post_channel` (`channelId`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `userName` (`userName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Attachments`
--
ALTER TABLE `Attachments`
  MODIFY `attachmentId` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Channels`
--
ALTER TABLE `Channels`
  MODIFY `channelId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Comments`
--
ALTER TABLE `Comments`
  MODIFY `commentId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `Interactions`
--
ALTER TABLE `Interactions`
  MODIFY `interactionId` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Posts`
--
ALTER TABLE `Posts`
  MODIFY `postId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Attachments`
--
ALTER TABLE `Attachments`
  ADD CONSTRAINT `fk_attachments_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Channels`
--
ALTER TABLE `Channels`
  ADD CONSTRAINT `fk_channel_creator` FOREIGN KEY (`channelCreator`) REFERENCES `Users` (`userId`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_owner_channel` FOREIGN KEY (`ownerChannelId`) REFERENCES `Channels` (`channelId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `fk_comment_post` FOREIGN KEY (`ownerPostId`) REFERENCES `Posts` (`postId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Interactions`
--
ALTER TABLE `Interactions`
  ADD CONSTRAINT `fk_interactions_post` FOREIGN KEY (`postId`) REFERENCES `Posts` (`postId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_interactions_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `Posts`
--
ALTER TABLE `Posts`
  ADD CONSTRAINT `fk_post_channel` FOREIGN KEY (`channelId`) REFERENCES `Channels` (`channelId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
