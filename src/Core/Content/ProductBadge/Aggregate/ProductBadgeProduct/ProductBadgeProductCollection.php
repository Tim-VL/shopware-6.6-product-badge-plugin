<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeProduct;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(ProductBadgeProductEntity $entity)
 * @method void                         set(string $key, ProductBadgeProductEntity $entity)
 * @method ProductBadgeProductEntity[]   getIterator()
 * @method ProductBadgeProductEntity[]   getElements()
 * @method ProductBadgeProductEntity|null get(string $key)
 * @method ProductBadgeProductEntity|null first()
 * @method ProductBadgeProductEntity|null last()
 */
class ProductBadgeProductCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'swag_product_badge_product_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductBadgeProductEntity::class;
    }

    /**
     * Filter by product ID
     */
    public function filterByProductId(string $productId): self
    {
        return $this->filter(function (ProductBadgeProductEntity $association) use ($productId) {
            return $association->getProductId() === $productId;
        });
    }

    /**
     * Filter by media ID (specific product image)
     */
    public function filterByMediaId(string $mediaId): self
    {
        return $this->filter(function (ProductBadgeProductEntity $association) use ($mediaId) {
            return $association->getMediaId() === $mediaId;
        });
    }

    /**
     * Get all badge IDs from the collection
     * @return string[]
     */
    public function getBadgeIds(): array
    {
        return $this->fmap(function (ProductBadgeProductEntity $association) {
            return $association->getSwagProductBadgeId();
        });
    }
}