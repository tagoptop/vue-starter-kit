START TRANSACTION;

SET @seed_password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

DROP TEMPORARY TABLE IF EXISTS `tmp_seed_users`;
CREATE TEMPORARY TABLE `tmp_seed_users` (
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `address` VARCHAR(255) NULL,
    `role` VARCHAR(20) NOT NULL
);

INSERT INTO `tmp_seed_users` (`name`, `email`, `phone`, `address`, `role`) VALUES
    ('System Admin', 'admin@construction.local', '0900000000', 'Sample Address', 'admin'),
    ('Warehouse Staff', 'staff@construction.local', '0900000000', 'Sample Address', 'staff'),
    ('Sample Customer', 'customer@construction.local', '0900000000', 'Sample Address', 'customer');

INSERT INTO `users` (
    `name`,
    `email`,
    `phone`,
    `address`,
    `role`,
    `password`,
    `email_verified_at`,
    `remember_token`,
    `created_at`,
    `updated_at`
)
SELECT
    `name`,
    `email`,
    `phone`,
    `address`,
    `role`,
    @seed_password,
    NULL,
    NULL,
    NOW(),
    NOW()
FROM `tmp_seed_users`
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `phone` = VALUES(`phone`),
    `address` = VALUES(`address`),
    `role` = VALUES(`role`),
    `password` = VALUES(`password`),
    `email_verified_at` = VALUES(`email_verified_at`),
    `remember_token` = VALUES(`remember_token`),
    `updated_at` = VALUES(`updated_at`);

DROP TEMPORARY TABLE IF EXISTS `tmp_seed_categories`;
CREATE TEMPORARY TABLE `tmp_seed_categories` (
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL
);

INSERT INTO `tmp_seed_categories` (`name`, `description`) VALUES
    ('Cement', 'Portland and blended cement products'),
    ('Hardware', 'Steel bars, fasteners, and general hardware'),
    ('Electrical', 'Electrical construction supplies'),
    ('Plumbing', 'Pipes, fittings, and plumbing accessories'),
    ('Aggregates', 'Sand, gravel and crushed stones'),
    ('Lumber', 'Wood sheets and plywood products'),
    ('Roofing', 'Roofing materials and accessories'),
    ('Fencing', 'Fencing materials and wire'),
    ('Sanitary', 'Sanitary ware and fixtures'),
    ('Tools', 'Hand tools and accessories');

INSERT INTO `categories` (`name`, `description`, `created_at`, `updated_at`)
SELECT `name`, `description`, NOW(), NOW()
FROM `tmp_seed_categories`
ON DUPLICATE KEY UPDATE
    `description` = VALUES(`description`),
    `updated_at` = VALUES(`updated_at`);

DROP TEMPORARY TABLE IF EXISTS `tmp_seed_suppliers`;
CREATE TEMPORARY TABLE `tmp_seed_suppliers` (
    `name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(30) NULL,
    `address` VARCHAR(255) NULL
);

INSERT INTO `tmp_seed_suppliers` (`name`, `contact_person`, `email`, `phone`, `address`) VALUES
    ('BuildRight Trading', 'Maria Santos', 'sales@buildright.test', '09171234567', 'Manila, Philippines'),
    ('SolidMix Industrial', 'John Cruz', 'orders@solidmix.test', '09987654321', 'Cebu, Philippines');

UPDATE `suppliers` AS `target`
JOIN `tmp_seed_suppliers` AS `seed` ON `seed`.`name` = `target`.`name`
SET
    `target`.`contact_person` = `seed`.`contact_person`,
    `target`.`email` = `seed`.`email`,
    `target`.`phone` = `seed`.`phone`,
    `target`.`address` = `seed`.`address`,
    `target`.`updated_at` = NOW();

INSERT INTO `suppliers` (`name`, `contact_person`, `email`, `phone`, `address`, `created_at`, `updated_at`)
SELECT
    `seed`.`name`,
    `seed`.`contact_person`,
    `seed`.`email`,
    `seed`.`phone`,
    `seed`.`address`,
    NOW(),
    NOW()
FROM `tmp_seed_suppliers` AS `seed`
LEFT JOIN `suppliers` AS `target` ON `target`.`name` = `seed`.`name`
WHERE `target`.`id` IS NULL;

DROP TEMPORARY TABLE IF EXISTS `tmp_seed_products`;
CREATE TEMPORARY TABLE `tmp_seed_products` (
    `name` VARCHAR(255) NOT NULL,
    `category_name` VARCHAR(255) NOT NULL,
    `supplier_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `stock_quantity` INT UNSIGNED NOT NULL,
    `low_stock_threshold` INT UNSIGNED NOT NULL
);

INSERT INTO `tmp_seed_products` (
    `name`,
    `category_name`,
    `supplier_name`,
    `description`,
    `price`,
    `stock_quantity`,
    `low_stock_threshold`
) VALUES
    ('Portland Cement 40kg', 'Cement', 'BuildRight Trading', 'Portland Cement 40kg', 265.00, 150, 10),
    ('Rebar 10mm x 6m', 'Hardware', 'SolidMix Industrial', 'Rebar 10mm x 6m', 310.00, 85, 10),
    ('Washed Sand (1 cu.m.)', 'Aggregates', 'BuildRight Trading', 'Washed Sand (1 cu.m.)', 1200.00, 40, 10),
    ('Gravel 3/4 (1 cu.m.)', 'Aggregates', 'SolidMix Industrial', 'Gravel 3/4 (1 cu.m.)', 1450.00, 28, 10),
    ('DEFORMED BAR 7mm', 'Hardware', 'BuildRight Trading', 'DEFORMED BAR 7mm', 70.00, 100, 10),
    ('DEFORMED BAR 8mm', 'Hardware', 'BuildRight Trading', 'DEFORMED BAR 8mm', 85.00, 100, 10),
    ('DEFORMED BAR 9mm', 'Hardware', 'BuildRight Trading', 'DEFORMED BAR 9mm', 95.00, 100, 10),
    ('DEFORMED BAR 10mm', 'Hardware', 'BuildRight Trading', 'DEFORMED BAR 10mm', 135.00, 100, 10),
    ('DEFORMED BAR 12mm', 'Hardware', 'BuildRight Trading', 'DEFORMED BAR 12mm', 190.00, 100, 10),
    ('DEFORMED BAR 16mm', 'Hardware', 'BuildRight Trading', 'DEFORMED BAR 16mm', 330.00, 100, 10),
    ('ROUND BAR 9mm', 'Hardware', 'SolidMix Industrial', 'ROUND BAR 9mm', 125.00, 80, 10),
    ('ROUND BAR 10mm', 'Hardware', 'SolidMix Industrial', 'ROUND BAR 10mm', 138.00, 80, 10),
    ('ROUND BAR 12mm', 'Hardware', 'SolidMix Industrial', 'ROUND BAR 12mm', 245.00, 80, 10),
    ('ROUND BAR 16mm', 'Hardware', 'SolidMix Industrial', 'ROUND BAR 16mm', 340.00, 80, 10),
    ('SQUARE BAR 9mm', 'Hardware', 'BuildRight Trading', 'SQUARE BAR 9mm', 140.00, 80, 10),
    ('SQUARE BAR 10mm', 'Hardware', 'BuildRight Trading', 'SQUARE BAR 10mm', 180.00, 80, 10),
    ('SQUARE BAR 12mm', 'Hardware', 'BuildRight Trading', 'SQUARE BAR 12mm', 220.00, 80, 10),
    ('ANGLE BAR 1/4 X 1 (Green) 3.5mm', 'Hardware', 'BuildRight Trading', 'ANGLE BAR 1/4 X 1 (Green) 3.5mm', 400.00, 60, 10),
    ('ANGLE BAR 3/16 X 1 (Red)', 'Hardware', 'BuildRight Trading', 'ANGLE BAR 3/16 X 1 (Red)', 280.00, 60, 10),
    ('ANGLE BAR 3/16 X 1 3mm', 'Hardware', 'BuildRight Trading', 'ANGLE BAR 3/16 X 1 3mm', 350.00, 60, 10),
    ('ANGLE BAR 3/16 X 1.5 (Red)', 'Hardware', 'SolidMix Industrial', 'ANGLE BAR 3/16 X 1.5 (Red)', 440.00, 60, 10),
    ('ANGLE BAR 1/4 X 1.5 (Yellow) 3mm', 'Hardware', 'SolidMix Industrial', 'ANGLE BAR 1/4 X 1.5 (Yellow) 3mm', 490.00, 60, 10),
    ('ANGLE BAR 1/4 X 1.5 (Orange) 4mm', 'Hardware', 'SolidMix Industrial', 'ANGLE BAR 1/4 X 1.5 (Orange) 4mm', 600.00, 60, 10),
    ('ANGLE BAR 1/4 X 1.5 (White) 5mm', 'Hardware', 'SolidMix Industrial', 'ANGLE BAR 1/4 X 1.5 (White) 5mm', 850.00, 50, 10),
    ('ANGLE BAR 3/16 X 2 (Red)', 'Hardware', 'BuildRight Trading', 'ANGLE BAR 3/16 X 2 (Red)', 545.00, 60, 10),
    ('ANGLE BAR 1/4 X 2 (Yellow) 3mm', 'Hardware', 'BuildRight Trading', 'ANGLE BAR 1/4 X 2 (Yellow) 3mm', 570.00, 60, 10),
    ('ANGLE BAR 1/4 X 2 (White) 5mm', 'Hardware', 'SolidMix Industrial', 'ANGLE BAR 1/4 X 2 (White) 5mm', 950.00, 50, 10),
    ('FLAT BAR 1/4 X 1 (White)', 'Hardware', 'BuildRight Trading', 'FLAT BAR 1/4 X 1 (White)', 335.00, 70, 10),
    ('FLAT BAR 3/16 X 1 (Yellow)', 'Hardware', 'BuildRight Trading', 'FLAT BAR 3/16 X 1 (Yellow)', 170.00, 70, 10),
    ('FLAT BAR 3/16 X 1 (Red)', 'Hardware', 'BuildRight Trading', 'FLAT BAR 3/16 X 1 (Red)', 230.00, 70, 10),
    ('FLAT BAR 1/4 X 1.5 (Orange)', 'Hardware', 'SolidMix Industrial', 'FLAT BAR 1/4 X 1.5 (Orange)', 585.00, 60, 10),
    ('FLAT BAR 3/16 X 1.5 (Red)', 'Hardware', 'BuildRight Trading', 'FLAT BAR 3/16 X 1.5 (Red)', 225.00, 70, 10),
    ('FLAT BAR 1/4 X 2 (White)', 'Hardware', 'SolidMix Industrial', 'FLAT BAR 1/4 X 2 (White)', 950.00, 50, 10),
    ('FLAT BAR 3/16 X 2 (Red)', 'Hardware', 'BuildRight Trading', 'FLAT BAR 3/16 X 2 (Red)', 545.00, 60, 10),
    ('TUBULAR 3/4 x 3/4', 'Hardware', 'BuildRight Trading', 'TUBULAR 3/4 x 3/4', 260.00, 50, 10),
    ('TUBULAR 1 x 1', 'Hardware', 'BuildRight Trading', 'TUBULAR 1 x 1', 330.00, 50, 10),
    ('TUBULAR 1.5 x 1.5', 'Hardware', 'BuildRight Trading', 'TUBULAR 1.5 x 1.5', 450.00, 40, 10),
    ('TUBULAR 1 x 1.5', 'Hardware', 'SolidMix Industrial', 'TUBULAR 1 x 1.5', 420.00, 40, 10),
    ('TUBULAR 1 x 2', 'Hardware', 'SolidMix Industrial', 'TUBULAR 1 x 2', 450.00, 40, 10),
    ('TUBULAR 1 x 3', 'Hardware', 'SolidMix Industrial', 'TUBULAR 1 x 3', 540.00, 35, 10),
    ('TUBULAR 2 x 2', 'Hardware', 'BuildRight Trading', 'TUBULAR 2 x 2', 570.00, 35, 10),
    ('TUBULAR 2 x 3', 'Hardware', 'BuildRight Trading', 'TUBULAR 2 x 3', 670.00, 35, 10),
    ('TUBULAR 2 x 4', 'Hardware', 'SolidMix Industrial', 'TUBULAR 2 x 4', 720.00, 30, 10),
    ('GI PIPE 0.5', 'Hardware', 'BuildRight Trading', 'GI PIPE 0.5', 420.00, 45, 10),
    ('GI PIPE 3/4', 'Hardware', 'BuildRight Trading', 'GI PIPE 3/4', 500.00, 45, 10),
    ('GI PIPE 1', 'Hardware', 'SolidMix Industrial', 'GI PIPE 1', 870.00, 40, 10),
    ('GI PIPE 1 1/4', 'Hardware', 'SolidMix Industrial', 'GI PIPE 1 1/4', 1000.00, 35, 10),
    ('GI PIPE 1.5', 'Hardware', 'SolidMix Industrial', 'GI PIPE 1.5', 1150.00, 35, 10),
    ('GI PIPE 2', 'Hardware', 'SolidMix Industrial', 'GI PIPE 2', 1580.00, 30, 10),
    ('GI PIPE 3', 'Hardware', 'SolidMix Industrial', 'GI PIPE 3', 2900.00, 25, 10),
    ('C-PURLINS 2x3', 'Hardware', 'BuildRight Trading', 'C-PURLINS 2x3', 560.00, 40, 10),
    ('C-PURLINS 2x4', 'Hardware', 'BuildRight Trading', 'C-PURLINS 2x4', 620.00, 40, 10),
    ('C-PURLINS 2x6', 'Hardware', 'BuildRight Trading', 'C-PURLINS 2x6', 700.00, 35, 10),
    ('CHANNEL BAR 2x3 Manipis', 'Hardware', 'SolidMix Industrial', 'CHANNEL BAR 2x3 Manipis', 900.00, 30, 10),
    ('CHANNEL BAR 2x3 Makapal', 'Hardware', 'SolidMix Industrial', 'CHANNEL BAR 2x3 Makapal', 1000.00, 30, 10),
    ('CHANNEL BAR 2x4', 'Hardware', 'SolidMix Industrial', 'CHANNEL BAR 2x4', 1300.00, 25, 10),
    ('METAL PARRING', 'Hardware', 'BuildRight Trading', 'METAL PARRING', 130.00, 80, 10),
    ('WALL ANGLE 10FT', 'Hardware', 'BuildRight Trading', 'WALL ANGLE 10FT', 70.00, 100, 10),
    ('CARRYING CHANNEL', 'Hardware', 'BuildRight Trading', 'CARRYING CHANNEL', 100.00, 80, 10),
    ('WALL CLIP', 'Hardware', 'BuildRight Trading', 'WALL CLIP', 5.00, 500, 10),
    ('Z BAR', 'Hardware', 'SolidMix Industrial', 'Z BAR', 350.00, 50, 10),
    ('STEEL MATTING GI Makapal', 'Fencing', 'BuildRight Trading', 'STEEL MATTING GI Makapal', 550.00, 30, 10),
    ('STEEL MATTING GI Manipis', 'Fencing', 'BuildRight Trading', 'STEEL MATTING GI Manipis', 350.00, 40, 10),
    ('CYCLONE 2X2X4', 'Fencing', 'SolidMix Industrial', 'CYCLONE 2X2X4', 1850.00, 20, 10),
    ('BARBED WIRE 130M', 'Fencing', 'SolidMix Industrial', 'BARBED WIRE 130M', 1950.00, 15, 10),
    ('HOG WIRE 9 HOLES', 'Fencing', 'BuildRight Trading', 'HOG WIRE 9 HOLES', 1100.00, 20, 10),
    ('WELDING ROD NIHONWELD 6013', 'Hardware', 'BuildRight Trading', 'WELDING ROD NIHONWELD 6013', 140.00, 50, 10),
    ('WELDING ROD GOLDEN BRIDGE 6013', 'Hardware', 'BuildRight Trading', 'WELDING ROD GOLDEN BRIDGE 6013', 100.00, 60, 10),
    ('KAWAD #18', 'Hardware', 'BuildRight Trading', 'KAWAD #18', 75.00, 40, 10),
    ('KAWAD #16', 'Hardware', 'BuildRight Trading', 'KAWAD #16', 75.00, 40, 10),
    ('HACKSAW', 'Tools', 'BuildRight Trading', 'HACKSAW', 80.00, 50, 10),
    ('PALA TULIS / LAPAD', 'Tools', 'BuildRight Trading', 'PALA TULIS / LAPAD', 250.00, 40, 10),
    ('CUTTING DISC (Novabull)', 'Tools', 'BuildRight Trading', 'CUTTING DISC (Novabull)', 20.00, 100, 10),
    ('PVC PIPE ORANGE 2"', 'Plumbing', 'BuildRight Trading', 'PVC PIPE ORANGE 2"', 140.00, 60, 10),
    ('PVC PIPE ORANGE 3"', 'Plumbing', 'BuildRight Trading', 'PVC PIPE ORANGE 3"', 210.00, 50, 10),
    ('PVC PIPE ORANGE 4"', 'Plumbing', 'BuildRight Trading', 'PVC PIPE ORANGE 4"', 270.00, 45, 10),
    ('PVC PIPE BLACK 6"', 'Plumbing', 'SolidMix Industrial', 'PVC PIPE BLACK 6"', 450.00, 35, 10),
    ('PVC ELBOW 90 2"', 'Plumbing', 'BuildRight Trading', 'PVC ELBOW 90 2"', 40.00, 100, 10),
    ('PVC ELBOW 90 3"', 'Plumbing', 'BuildRight Trading', 'PVC ELBOW 90 3"', 60.00, 80, 10),
    ('PVC ELBOW 90 4"', 'Plumbing', 'BuildRight Trading', 'PVC ELBOW 90 4"', 75.00, 70, 10),
    ('PVC TEE/Y 2"', 'Plumbing', 'BuildRight Trading', 'PVC TEE/Y 2"', 45.00, 100, 10),
    ('PVC TEE/Y 3"', 'Plumbing', 'BuildRight Trading', 'PVC TEE/Y 3"', 55.00, 80, 10),
    ('PVC TEE/Y 4"', 'Plumbing', 'BuildRight Trading', 'PVC TEE/Y 4"', 75.00, 70, 10),
    ('PIPE BLUE 1/2"', 'Plumbing', 'BuildRight Trading', 'PIPE BLUE 1/2"', 95.00, 80, 10),
    ('PIPE BLUE 3/4"', 'Plumbing', 'BuildRight Trading', 'PIPE BLUE 3/4"', 120.00, 70, 10),
    ('PIPE BLUE 1"', 'Plumbing', 'SolidMix Industrial', 'PIPE BLUE 1"', 175.00, 60, 10),
    ('BLUE ELBOW 1/2"', 'Plumbing', 'BuildRight Trading', 'BLUE ELBOW 1/2"', 15.00, 150, 10),
    ('BLUE BALL VALVE 1/2"', 'Plumbing', 'BuildRight Trading', 'BLUE BALL VALVE 1/2"', 45.00, 80, 10),
    ('PPR PIPE 1/2"', 'Plumbing', 'SolidMix Industrial', 'PPR PIPE 1/2"', 180.00, 50, 10),
    ('PPR PIPE 3/4"', 'Plumbing', 'SolidMix Industrial', 'PPR PIPE 3/4"', 270.00, 40, 10),
    ('PE PIPE 1/2"', 'Plumbing', 'BuildRight Trading', 'PE PIPE 1/2"', 27.00, 200, 10),
    ('GI ELBOW 1/2"', 'Plumbing', 'BuildRight Trading', 'GI ELBOW 1/2"', 30.00, 100, 10),
    ('GARDEN HOSE 1/2"', 'Plumbing', 'BuildRight Trading', 'GARDEN HOSE 1/2"', 30.00, 100, 10),
    ('LAVATORY SINK (Shark) Small', 'Sanitary', 'SolidMix Industrial', 'LAVATORY SINK (Shark) Small', 600.00, 20, 10),
    ('TOILET BOWL', 'Sanitary', 'SolidMix Industrial', 'TOILET BOWL', 900.00, 15, 10),
    ('EAGLE ADVANCE CEMENT (Pick-up)', 'Cement', 'BuildRight Trading', 'EAGLE ADVANCE CEMENT (Pick-up)', 185.00, 100, 10),
    ('ABC ADHESIVE', 'Cement', 'BuildRight Trading', 'ABC ADHESIVE', 290.00, 60, 10),
    ('SKIMCOAT', 'Cement', 'BuildRight Trading', 'SKIMCOAT', 300.00, 60, 10),
    ('COMMON NAIL (1 to 4 inch)', 'Hardware', 'BuildRight Trading', 'COMMON NAIL (1 to 4 inch)', 75.00, 100, 10),
    ('CONCRETE NAIL (1 to 4 inch)', 'Hardware', 'BuildRight Trading', 'CONCRETE NAIL (1 to 4 inch)', 110.00, 80, 10),
    ('BLIND RIVET (All sizes)', 'Hardware', 'BuildRight Trading', 'BLIND RIVET (All sizes)', 0.50, 500, 10),
    ('PLYWOOD ORDINARY 1/4"', 'Lumber', 'BuildRight Trading', 'PLYWOOD ORDINARY 1/4"', 300.00, 40, 10),
    ('PLYWOOD MARINE 1/4"', 'Lumber', 'SolidMix Industrial', 'PLYWOOD MARINE 1/4"', 400.00, 30, 10),
    ('HARDIFLEX 3/16"', 'Lumber', 'BuildRight Trading', 'HARDIFLEX 3/16"', 370.00, 35, 10),
    ('YERO ORDINARY 8FT', 'Roofing', 'BuildRight Trading', 'YERO ORDINARY 8FT', 360.00, 50, 10),
    ('YERO COLORED 8FT', 'Roofing', 'BuildRight Trading', 'YERO COLORED 8FT', 440.00, 45, 10),
    ('THHN WIRE #12 (Boston)', 'Electrical', 'BuildRight Trading', 'THHN WIRE #12 (Boston)', 25.00, 150, 10),
    ('THHN WIRE #14 (Boston)', 'Electrical', 'BuildRight Trading', 'THHN WIRE #14 (Boston)', 20.00, 150, 10),
    ('FLEXIBLE HOSE', 'Electrical', 'BuildRight Trading', 'FLEXIBLE HOSE', 10.00, 200, 10),
    ('KOTEN SAFETY BREAKER 20/30A', 'Electrical', 'SolidMix Industrial', 'KOTEN SAFETY BREAKER 20/30A', 540.00, 30, 10),
    ('ROYU OUTLET 1 GANG', 'Electrical', 'BuildRight Trading', 'ROYU OUTLET 1 GANG', 110.00, 60, 10);

UPDATE `products` AS `target`
JOIN `tmp_seed_products` AS `seed` ON `seed`.`name` = `target`.`name`
JOIN `categories` AS `category` ON `category`.`name` = `seed`.`category_name`
JOIN `suppliers` AS `supplier` ON `supplier`.`name` = `seed`.`supplier_name`
SET
    `target`.`category_id` = `category`.`id`,
    `target`.`supplier_id` = `supplier`.`id`,
    `target`.`description` = `seed`.`description`,
    `target`.`price` = `seed`.`price`,
    `target`.`stock_quantity` = `seed`.`stock_quantity`,
    `target`.`low_stock_threshold` = `seed`.`low_stock_threshold`,
    `target`.`updated_at` = NOW();

INSERT INTO `products` (
    `category_id`,
    `supplier_id`,
    `name`,
    `description`,
    `price`,
    `stock_quantity`,
    `low_stock_threshold`,
    `image_path`,
    `created_at`,
    `updated_at`
)
SELECT
    `category`.`id`,
    `supplier`.`id`,
    `seed`.`name`,
    `seed`.`description`,
    `seed`.`price`,
    `seed`.`stock_quantity`,
    `seed`.`low_stock_threshold`,
    NULL,
    NOW(),
    NOW()
FROM `tmp_seed_products` AS `seed`
JOIN `categories` AS `category` ON `category`.`name` = `seed`.`category_name`
JOIN `suppliers` AS `supplier` ON `supplier`.`name` = `seed`.`supplier_name`
LEFT JOIN `products` AS `target` ON `target`.`name` = `seed`.`name`
WHERE `target`.`id` IS NULL;

DELETE FROM `inventory_transactions`
WHERE `reference` = 'SEED-STOCK-IN'
  AND `notes` = 'Initial stock from seeder';

INSERT INTO `inventory_transactions` (
    `product_id`,
    `user_id`,
    `type`,
    `quantity`,
    `reference`,
    `notes`,
    `created_at`,
    `updated_at`
)
SELECT
    `product`.`id`,
    (
        SELECT `id`
        FROM `users`
        WHERE `role` = 'admin'
        ORDER BY `id`
        LIMIT 1
    ),
    'in',
    `seed`.`stock_quantity`,
    'SEED-STOCK-IN',
    'Initial stock from seeder',
    NOW(),
    NOW()
FROM `tmp_seed_products` AS `seed`
JOIN `products` AS `product` ON `product`.`name` = `seed`.`name`;

SET @customer_id = (
    SELECT `id`
    FROM `users`
    WHERE `role` = 'customer'
    ORDER BY `id`
    LIMIT 1
);

SET @staff_id = (
    SELECT `id`
    FROM `users`
    WHERE `role` = 'staff'
    ORDER BY `id`
    LIMIT 1
);

SET @product_id = (
    SELECT `id`
    FROM `products`
    ORDER BY `id`
    LIMIT 1
);

SET @order_number = CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0001');

DELETE FROM `order_items`
WHERE `order_id` IN (
    SELECT `id`
    FROM `orders`
    WHERE `order_number` = @order_number
);

DELETE FROM `inventory_transactions`
WHERE `reference` = @order_number
  AND `notes` = 'Stock deduction from seeded order';

DELETE FROM `orders`
WHERE `order_number` = @order_number;

SET @quantity = 3;
SET @unit_price = (
    SELECT `price`
    FROM `products`
    WHERE `id` = @product_id
);
SET @subtotal = @quantity * COALESCE(@unit_price, 0);

INSERT INTO `orders` (
    `order_number`,
    `customer_id`,
    `status`,
    `total_amount`,
    `notes`,
    `created_at`,
    `updated_at`
)
SELECT
    @order_number,
    @customer_id,
    'approved',
    @subtotal,
    'Seeded sample order',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    NOW()
FROM DUAL
WHERE @customer_id IS NOT NULL
  AND @product_id IS NOT NULL;

INSERT INTO `order_items` (
    `order_id`,
    `product_id`,
    `quantity`,
    `unit_price`,
    `subtotal`,
    `created_at`,
    `updated_at`
)
SELECT
    `orders`.`id`,
    @product_id,
    @quantity,
    @unit_price,
    @subtotal,
    NOW(),
    NOW()
FROM `orders`
WHERE `orders`.`order_number` = @order_number
  AND @customer_id IS NOT NULL
  AND @product_id IS NOT NULL;

UPDATE `products`
SET `stock_quantity` = GREATEST(`stock_quantity` - @quantity, 0),
    `updated_at` = NOW()
WHERE `id` = @product_id
  AND @customer_id IS NOT NULL
  AND @product_id IS NOT NULL;

INSERT INTO `inventory_transactions` (
    `product_id`,
    `user_id`,
    `type`,
    `quantity`,
    `reference`,
    `notes`,
    `created_at`,
    `updated_at`
)
SELECT
    @product_id,
    @staff_id,
    'out',
    @quantity,
    @order_number,
    'Stock deduction from seeded order',
    NOW(),
    NOW()
FROM DUAL
WHERE @customer_id IS NOT NULL
  AND @product_id IS NOT NULL;

DROP TEMPORARY TABLE IF EXISTS `tmp_seed_products`;
DROP TEMPORARY TABLE IF EXISTS `tmp_seed_suppliers`;
DROP TEMPORARY TABLE IF EXISTS `tmp_seed_categories`;
DROP TEMPORARY TABLE IF EXISTS `tmp_seed_users`;

COMMIT;