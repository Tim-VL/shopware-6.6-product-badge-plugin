<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeProduct;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\ProductBadges\Core\Content\ProductBadge\ProductBadgeEntity;

class ProductBadgeProductEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;
    protected string $productVersionId;
    protected string $swagProductBadgeId; // Changed from $productBadgeId
    protected ?string $mediaId;
    protected ?ProductEntity $product;
    protected ?ProductBadgeEntity $productBadge;
    protected ?MediaEntity $media;

    // Getters and setters for all properties
    public function getProductId(): string { return $this->productId; }
    public function setProductId(string $productId): void { $this->productId = $productId; }
    
    public function getProductVersionId(): string { return $this->productVersionId; }
    public function setProductVersionId(string $productVersionId): void { $this->productVersionId = $productVersionId; }
    
    public function getSwagProductBadgeId(): string { return $this->swagProductBadgeId; } // Changed method name
    public function setSwagProductBadgeId(string $swagProductBadgeId): void { $this->swagProductBadgeId = $swagProductBadgeId; } // Changed method name
    
    public function getMediaId(): ?string { return $this->mediaId; }
    public function setMediaId(?string $mediaId): void { $this->mediaId = $mediaId; }
    
    public function getProduct(): ?ProductEntity { return $this->product; }
    public function setProduct(?ProductEntity $product): void { $this->product = $product; }
    
    public function getProductBadge(): ?ProductBadgeEntity { return $this->productBadge; }
    public function setProductBadge(?ProductBadgeEntity $productBadge): void { $this->productBadge = $productBadge; }
    
    public function getMedia(): ?MediaEntity { return $this->media; }
    public function setMedia(?MediaEntity $media): void { $this->media = $media; }
}
