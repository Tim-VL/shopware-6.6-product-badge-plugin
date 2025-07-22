<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\ProductBadges\Core\Content\ProductBadge\ProductBadgeDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductBadgeTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'swag_product_badge_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductBadgeTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductBadgeTranslationCollection::class;
    }

    public function getParentDefinitionClass(): string
    {
        return ProductBadgeDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new Required(), new ApiAware()),
            (new StringField('alt_text', 'altText'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
