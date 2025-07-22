<?php declare(strict_types=1);

namespace Swag\ProductBadges\Core\Content\ProductBadge\Aggregate\ProductBadgeTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ProductBadgeTranslationEntity extends TranslationEntity
{
    protected ?string $label = null;
    protected ?string $altText = null;
    protected ?array $customFields = null;

    public function getLabel(): ?string 
    { 
        return $this->label; 
    }
    
    public function setLabel(?string $label): void 
    { 
        $this->label = $label; 
    }

    public function getAltText(): ?string 
    { 
        return $this->altText; 
    }
    
    public function setAltText(?string $altText): void 
    { 
        $this->altText = $altText; 
    }

    public function getCustomFields(): ?array 
    { 
        return $this->customFields; 
    }
    
    public function setCustomFields(?array $customFields): void 
    { 
        $this->customFields = $customFields; 
    }
}
