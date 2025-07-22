<?php declare(strict_types=1);

namespace Swag\ProductBadges\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductBadgeService
{
    public function __construct(
        private readonly EntityRepository $productBadgeRepository,
        private readonly EntityRepository $productBadgeProductRepository
    ) {
    }

    public function getActiveBadges(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAssociation('translations');

        $result = $this->productBadgeRepository->search($criteria, $context);

        return $result->getEntities()->getElements();
    }

    public function getProductBadges(string $productId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addAssociation('swagProductBadge');
        $criteria->addAssociation('swagProductBadge.translations');

        $result = $this->productBadgeProductRepository->search($criteria, $context);

        return $result->getEntities()->getElements();
    }

    public function assignBadgeToProduct(
        string $productId,
        string $productVersionId,
        string $badgeId,
        ?string $mediaId,
        Context $context
    ): string {
        $data = [
            'productId' => $productId,
            'productVersionId' => $productVersionId,
            'swagProductBadgeId' => $badgeId,
            'mediaId' => $mediaId,
        ];

        $result = $this->productBadgeProductRepository->create([$data], $context);
        
        return $result->getPrimaryKeys('swag_product_badge_product')[0];
    }

    public function unassignBadgeFromProduct(string $assignmentId, Context $context): void
    {
        $this->productBadgeProductRepository->delete([['id' => $assignmentId]], $context);
    }

    public function isProductBadgeAssigned(string $productId, string $badgeId, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter('swagProductBadgeId', $badgeId));

        $result = $this->productBadgeProductRepository->search($criteria, $context);

        return $result->getTotal() > 0;
    }

    public function bulkAssignBadges(array $assignments, Context $context): array
    {
        $data = [];
        foreach ($assignments as $assignment) {
            $data[] = [
                'productId' => $assignment['productId'],
                'productVersionId' => $assignment['productVersionId'],
                'swagProductBadgeId' => $assignment['swagProductBadgeId'],
                'mediaId' => $assignment['mediaId'] ?? null,
            ];
        }

        $result = $this->productBadgeProductRepository->create($data, $context);
        
        return $result->getPrimaryKeys('swag_product_badge_product');
    }
}
