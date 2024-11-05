<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\ORM\Entity;

class Category extends AbstractEntity
{
    protected string $name;

    protected ?Category $parent = null;

    protected Collection $children;

    protected int $level = 0;

    protected ?string $path = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->children = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): void
    {
        $this->parent = $parent;
        if ($parent !== null) {
            $this->level = $parent->getLevel() + 1;
            $this->path = $parent->getPath() . '|' . $this->name;
        } else {
            $this->level = 0;
            $this->path = $this->name;
        }
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Category $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(Category $child): void
    {
        if ($this->children->removeElement($child)) {
            $child->setParent(null);
        }
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}