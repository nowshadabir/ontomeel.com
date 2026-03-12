-- Add email_unsubscribed column to members table for spam prevention
-- Run this SQL to enable unsubscribe functionality

ALTER TABLE `members` 
ADD COLUMN `email_unsubscribed` TINYINT(1) NOT NULL DEFAULT 0 
AFTER `plan_expire_date`;

-- Create index for faster lookups
ALTER TABLE `members` 
ADD INDEX `idx_email_unsubscribed` (`email_unsubscribed`);
