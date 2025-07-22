<?php declare(strict_types=1);

namespace Swag\ProductBadges\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\Context;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Swag\ProductBadges\Service\ProductBadgeLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

class SwagProductBadgesEventSubscriber implements EventSubscriberInterface
{
    private ProductBadgeLoader $badgeLoader;

    public function __construct(ProductBadgeLoader $badgeLoader)
    {
        $this->badgeLoader = $badgeLoader;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'onProductsLoaded',
        ];
    }

    public function onProductsLoaded(EntityLoadedEvent $event): void
    {
        $products = $event->getEntities();
        $context = $event->getContext();

        foreach ($products as $product) {
            if (!$product instanceof SalesChannelProductEntity) {
                continue;
            }

            $this->processProductMedia($product, $context);
        }
    }

    private function processProductMedia(SalesChannelProductEntity $product, Context $context): void
    {
        $productId = $product->getId();

        //Listing Page
        $cover = $product->getCover();
        if ($cover && $cover->getMedia()) {
            $this->badgeLoader->addBadgesToMedia($cover->getMedia(), $productId, $context);
        }

        // Detail Page
        $mediaCollection = $product->getMedia();
        if ($mediaCollection) {
            foreach ($mediaCollection as $productMedia) {
                if ($productMedia->getMedia()) {
                    $this->badgeLoader->addBadgesToMedia($productMedia->getMedia(), $productId, $context);
                }
            }
        }
    }
}