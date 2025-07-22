<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeProduct;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\ProductBadges\Core\Content\ProductBadge\ProductBadgeDefinition;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductBadgeProductDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_product_badge_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductBadgeProductEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductBadgeProductCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new FkField('product_version_id', 'productVersionId', ProductDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            
            (new FkField('swag_product_badge_id', 'swagProductBadgeId', ProductBadgeDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('productBadge', 'swag_product_badge_id', ProductBadgeDefinition::class, 'id', false),
            
            new FkField('media_id', 'mediaId', MediaDefinition::class),
            new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', false),
        ]);
    }

}