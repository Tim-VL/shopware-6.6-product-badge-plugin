<?php declare(strict_types=1);

namespace Swag\ProductBadges;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class SwagProductBadges extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->removeTables($uninstallContext);
    }

    private function removeTables(UninstallContext $uninstallContext): void
    {
        $connection = $this->container->get('Doctrine\DBAL\Connection');
        
        
        $connection->executeStatement('DROP TABLE IF EXISTS `swag_product_badge_product`');
        $connection->executeStatement('DROP TABLE IF EXISTS `swag_product_badge_translation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `swag_product_badge`');
        
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        $connection->executeUpdate('DELETE FROM `system_config` where configuration_key LIKE "SwagProductBadges.config%"');
    }
}