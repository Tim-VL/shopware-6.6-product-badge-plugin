<?php declare(strict_types=1);

namespace Swag\ProductBadges\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1750776342SwagProductBadges extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1750776342;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `swag_product_badge` (
            `id` BINARY(16) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `name` VARCHAR(255) NOT NULL,
            `image_id` BINARY(16) NULL,
            `position1` VARCHAR(50) NOT NULL DEFAULT 'top-left',
            `position2` VARCHAR(50) NOT NULL DEFAULT 'top-left',
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk.swag_product_badge.image_id` FOREIGN KEY (`image_id`)
                REFERENCES `media` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `swag_product_badge_translation` (
            `swag_product_badge_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            `label` VARCHAR(255) NOT NULL,
            `alt_text` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`swag_product_badge_id`, `language_id`),
            CONSTRAINT `fk.swag_product_badge_translation.swag_product_badge_id` FOREIGN KEY (`swag_product_badge_id`)
                REFERENCES `swag_product_badge` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk.swag_product_badge_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `swag_product_badge_product` (
            `id` BINARY(16) NOT NULL,
            `product_id` BINARY(16) NOT NULL,
            `product_version_id` BINARY(16) NOT NULL,
            `swag_product_badge_id` BINARY(16) NOT NULL,
            `media_id` BINARY(16) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk.swag_product_badge_product.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE,
            CONSTRAINT `fk.swag_product_badge_product.swag_product_badge_id` FOREIGN KEY (`swag_product_badge_id`)
                REFERENCES `swag_product_badge` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk.swag_product_badge_product.media_id` FOREIGN KEY (`media_id`)
                REFERENCES `media` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // updateDestructive
    }
}
