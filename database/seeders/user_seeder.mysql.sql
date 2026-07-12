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
) VALUES
    (
        'System Admin',
        'admin@construction.local',
        '0900000000',
        'Sample Address',
        'admin',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        NULL,
        NULL,
        NOW(),
        NOW()
    ),
    (
        'Warehouse Staff',
        'staff@construction.local',
        '0900000000',
        'Sample Address',
        'staff',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        NULL,
        NULL,
        NOW(),
        NOW()
    ),
    (
        'Sample Customer',
        'customer@construction.local',
        '0900000000',
        'Sample Address',
        'customer',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        NULL,
        NULL,
        NOW(),
        NOW()
    )
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `phone` = VALUES(`phone`),
    `address` = VALUES(`address`),
    `role` = VALUES(`role`),
    `password` = VALUES(`password`),
    `email_verified_at` = VALUES(`email_verified_at`),
    `remember_token` = VALUES(`remember_token`),
    `updated_at` = VALUES(`updated_at`);