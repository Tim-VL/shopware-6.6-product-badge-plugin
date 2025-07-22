<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeProduct\ProductBadgeProductCollection;
use Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeTranslation\ProductBadgeTranslationCollection;

class ProductBadgeEntity extends Entity
{
    use EntityIdTrait;

    protected bool $active;
    protected string $name;
    protected string $position1;
    protected string $position2;
    protected ?string $imageId = null;
    protected ?MediaEntity $image = null;
    protected ?ProductBadgeTranslationCollection $translations = null;
    protected ?ProductBadgeProductCollection $productBadgeProducts = null;
    
    protected ?string $label = null;
    protected ?string $altText = null;
    protected ?array $customFields = null;

    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): void { $this->active = $active; }
    
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    
    public function getPosition1(): string { return $this->position1; }
    public function setPosition1(string $position): void { $this->position1 = $position; }
    public function getPosition2(): string { return $this->position2; }
    public function setPosition2(string $position): void { $this->position2 = $position; }
    
    public function getImageId(): ?string { return $this->imageId; }
    public function setImageId(?string $imageId): void { $this->imageId = $imageId; }
    
    public function getImage(): ?MediaEntity { return $this->image; }
    public function setImage(?MediaEntity $image): void { $this->image = $image; }
    
    public function getTranslations(): ?ProductBadgeTranslationCollection { return $this->translations; }
    public function setTranslations(?ProductBadgeTranslationCollection $translations): void { $this->translations = $translations; }
    
    public function getProductBadgeProducts(): ?ProductBadgeProductCollection { return $this->productBadgeProducts; }
    public function setProductBadgeProducts(?ProductBadgeProductCollection $productBadgeProducts): void { $this->productBadgeProducts = $productBadgeProducts; }
    
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(?string $label): void { $this->label = $label; }
    
    public function getAltText(): ?string { return $this->altText; }
    public function setAltText(?string $altText): void { $this->altText = $altText; }
    
    public function getCustomFields(): ?array { return $this->customFields; }
    public function setCustomFields(?array $customFields): void { $this->customFields = $customFields; }
}
