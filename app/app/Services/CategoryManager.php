<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Services;

use Nette\Database\Explorer;

class CategoryManager
{
    private Explorer $database;
    private array $categoryCache = [];

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function getCategoryFromPath(string $path): array
    {
        if (isset($this->categoryCache[$path])) {
            return $this->categoryCache[$path];
        }

        $category = $this->database->table('category')
            ->where('path', $path)
            ->fetch();

        if($category) {
            $this->categoryCache[$path] = $category->toArray();
            return $this->categoryCache[$path];
        }

        return $this->createCategoryPath($path);
    }

    private function createCategoryPath(string $path): array
    {
        $parts = explode('|', $path);
        $currentPath = '';
        $parentId = null;
        $level = 0;
        $lastCategory = null;

        foreach ($parts as $part) {
            $part = trim($part);
            $currentPath = $currentPath ? $currentPath . '|' . $part : $part;

            if (isset($this->categoryCache[$currentPath])){
                $lastCategory = $this->categoryCache[$currentPath];
                $parentId = $lastCategory['id'];
                $level++;
                continue;
            }

            $category = $this->database->table('category')
                ->where('path', $part)
                ->fetch();

            if ($category) {
                $lastCategory = $category->toArray();
                $this->categoryCache[$currentPath] = $lastCategory;
                $parentId = $lastCategory['id'];
                $level++;
                continue;
            }

            $data = [
                'name' => $part,
                'parent_id' => $parentId,
                'level' => $level,
                'path' => $currentPath
            ];

            $category = $this->database->table('category')
                ->insert($data);

            $lastCategory = array_merge(['id' => $category->id], $data);
            $this->categoryCache[$currentPath] = $lastCategory;

            $parentId = $category->id;
            $level++;
        }

        return $lastCategory;
    }
}