-- Images attached to posts: links 'postImage' attachments to their post, and removes them with it.
-- Run once on the existing database (schema.sql already includes this).

ALTER TABLE `Attachments`
  ADD COLUMN `postId` int DEFAULT NULL COMMENT 'Set for postImage attachments',
  ADD KEY `fk_attachments_post` (`postId`),
  ADD CONSTRAINT `fk_attachments_post` FOREIGN KEY (`postId`) REFERENCES `Posts` (`postId`) ON DELETE CASCADE ON UPDATE CASCADE;
