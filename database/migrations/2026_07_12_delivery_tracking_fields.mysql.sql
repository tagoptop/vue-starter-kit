ALTER TABLE `orders`
    ADD COLUMN `delivery_notes` TEXT NULL AFTER `delivery_longitude`,
    ADD COLUMN `driver_name` VARCHAR(255) NULL AFTER `delivery_notes`,
    ADD COLUMN `driver_phone` VARCHAR(30) NULL AFTER `driver_name`,
    ADD COLUMN `proof_of_delivery_path` VARCHAR(255) NULL AFTER `driver_phone`,
    ADD COLUMN `delivered_at` TIMESTAMP NULL AFTER `proof_of_delivery_path`;