<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ProductBadgeTranslationEntity>
 * @method void                               add(ProductBadgeTranslationEntity $entity)
 * @method void                               set(string $key, ProductBadgeTranslationEntity $entity)
 * @method ProductBadgeTranslationEntity[]    getIterator()
 * @method ProductBadgeTranslationEntity[]    getElements()
 * @method ProductBadgeTranslationEntity|null get(string $key)
 * @method ProductBadgeTranslationEntity|null first()
 * @method ProductBadgeTranslationEntity|null last()
 */
class ProductBadgeTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'swag_product_badge_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductBadgeTranslationEntity::class;
    }

    /**
     * Get translations by language ID
     */
    public function filterByLanguageId(string $languageId): self
    {
        return $this->filter(function (ProductBadgeTranslationEntity $translation) use ($languageId) {
            return $translation->getLanguageId() === $languageId;
        });
    }
}
