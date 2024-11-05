<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\ORM\Entity;

class Product extends AbstractEntity
{
    protected string $title;
    protected string $gid;
    protected string $productCondition;
    protected ?string $description = null;
    protected string $link;
    protected string $imageLink;
    protected string $brand;
    protected string $gtin;
    protected string $mpn;
    protected string $availability;
    protected float $price;
    protected Category $category;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private bool $active = true;

    public function __construct(
        string   $title,
        string   $gid,
        string   $productCondition,
        ?string  $description,
        string   $link,
        string   $imageLink,
        string   $brand,
        string   $gtin,
        string   $mpn,
        string   $availability,
        float    $price,
        Category $category,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        bool $active = true
    ) {
        $this->title = $title;
        $this->gid = $gid;
        $this->productCondition = $productCondition;
        $this->description = $description;
        $this->link = $link;
        $this->imageLink = $imageLink;
        $this->brand = $brand;
        $this->gtin = $gtin;
        $this->mpn = $mpn;
        $this->availability = $availability;
        $this->price = $price;
        $this->category = $category;
        $this->active = $active;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters and setters
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getGid(): string
    {
        return $this->gid;
    }

    public function setGid(string $gid): void
    {
        $this->gid = $gid;
    }

    public function getProductCondition(): string
    {
        return $this->productCondition;
    }

    public function setProductCondition(string $productCondition): void
    {
        $this->productCondition = $productCondition;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function getImageLink(): string
    {
        return $this->imageLink;
    }

    public function setImageLink(string $imageLink): void
    {
        $this->imageLink = $imageLink;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function getGtin(): string
    {
        return $this->gtin;
    }

    public function setGtin(string $gtin): void
    {
        $this->gtin = $gtin;
    }

    public function getMpn(): string
    {
        return $this->mpn;
    }

    public function setMpn(string $mpn): void
    {
        $this->mpn = $mpn;
    }

    public function getAvailability(): string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): void
    {
        $this->availability = $availability;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}