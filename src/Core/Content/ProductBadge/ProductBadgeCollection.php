<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(ProductBadgeEntity $entity)
 * @method void                     set(string $key, ProductBadgeEntity $entity)
 * @method ProductBadgeEntity[]     getIterator()
 * @method ProductBadgeEntity[]     getElements()
 * @method ProductBadgeEntity|null  get(string $key)
 * @method ProductBadgeEntity|null  first()
 * @method ProductBadgeEntity|null  last()
 */
class ProductBadgeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'swag_product_badge_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductBadgeEntity::class;
    }

    /**
     * Filter active badges
     */
    public function filterActive(): self
    {
        return $this->filter(function (ProductBadgeEntity $badge) {
            return $badge->isActive();
        });
    }

    /**
     * Filter by position
     */
    public function filterByPosition1(string $position): self
    {
        return $this->filter(function (ProductBadgeEntity $badge) use ($position) {
            return $badge->getPosition1() === $position;
        });
    }
    /**
     * Filter by position
     */
    public function filterByPosition2(string $position): self
    {
        return $this->filter(function (ProductBadgeEntity $badge) use ($position) {
            return $badge->getPosition2() === $position;
        });
    }
}