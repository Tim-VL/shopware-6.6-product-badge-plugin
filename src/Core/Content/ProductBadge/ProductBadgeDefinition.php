<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeProduct\ProductBadgeProductDefinition;
use Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeTranslation\ProductBadgeTranslationDefinition;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductBadgeDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_product_badge';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductBadgeEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductBadgeCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new Required(), new ApiAware()),
            (new StringField('name', 'name'))->addFlags(new Required(), new ApiAware()),
            (new StringField('position1', 'position1'))->addFlags(new Required(), new ApiAware()),
            (new StringField('position2', 'position2'))->addFlags(new Required(), new ApiAware()),
            
            (new FkField('image_id', 'imageId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('image', 'image_id', MediaDefinition::class, 'id', false))->addFlags(new ApiAware()),
            
            (new TranslationsAssociationField(
                ProductBadgeTranslationDefinition::class,
                'swag_product_badge_id'
            ))->addFlags(new Required(), new ApiAware()),
            
            (new TranslatedField('label'))->addFlags(new ApiAware()),
            (new TranslatedField('altText'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            
            (new OneToManyAssociationField(
                'productBadgeProducts',
                ProductBadgeProductDefinition::class,
                'swag_product_badge_id'
            ))->addFlags(new ApiAware()),
        ]);
    }
}
