<?php declare(strict_types=1);

namespace Swag\ProductBadges\Service;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeProduct\ProductBadgeProductEntity;

class ProductBadgeLoader
{
    private EntityRepository $badgeProductRepository;
    private EntityRepository $mediaRepository;

    public function __construct(EntityRepository $badgeProductRepository, EntityRepository $mediaRepository)
    {
        $this->badgeProductRepository = $badgeProductRepository;
        $this->mediaRepository = $mediaRepository;
    }

    public function addBadgesToMedia(MediaEntity $media, string $productId, Context $context): void
    {
        
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addAssociation('productBadge');
        $criteria->addAssociation('productBadge.image');
        $criteria->addAssociation('productBadge.translations');
        $criteria->addAssociation('media'); 

        $result = $this->badgeProductRepository->search($criteria, $context);

        $badges = [];

        /** @var ProductBadgeProductEntity $badgeProduct */
        foreach ($result->getEntities() as $badgeProduct) {
            $badge = $badgeProduct->getProductBadge();
            $assignedMedia = $badgeProduct->getMedia();


            if (!$badge || !$badge->isActive()) {
                continue;
            }

            if (!$assignedMedia || $assignedMedia->getId() !== $media->getId()) {
                continue;
            }

            $badgeImageEntity = $badge->getImage();
            
            if (!$badgeImageEntity && $badge->getImageId()) {
                $badgeImageEntity = $this->loadMedia($badge->getImageId(), $context);
            }

            if ($badgeImageEntity && $badgeImageEntity->getUrl()) {
                $badges[] = [
                    'type' => 'image',
                    'url' => $badgeImageEntity->getUrl(),
                    'alt' => $badge->getTranslation('altText') ?? $badge->getTranslation('label') ?? $badge->getName(),
                    'position1' => $badge->getPosition1() ?? 'top-left',
                    'position2' => $badge->getPosition2() ?? 'top-left',
                    'mediaId' => $assignedMedia->getId(), 
                    'badgeId' => $badge->getId(), 
                ];
            } elseif ($badge->getTranslation('label')) {
                $badges[] = [
                    'type' => 'label',
                    'label' => $badge->getTranslation('label'),
                    'position1' => $badge->getPosition1() ?? 'top-left',
                    'position2' => $badge->getPosition2() ?? 'top-left',
                    'mediaId' => $assignedMedia->getId(),
                    'badgeId' => $badge->getId(), 
                ];
            }
        }

        if (!empty($badges)) {
            $media->addExtension('product_image_badges', new ArrayStruct([
                'badges' => $badges,
            ]));
        }
    }

    private function loadMedia(string $mediaId, Context $context): ?MediaEntity
    {
        $criteria = new Criteria([$mediaId]);
        $result = $this->mediaRepository->search($criteria, $context);
        return $result->get($mediaId);
    }
}