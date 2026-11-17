<?php

declare(strict_types=1);

require_once __DIR__ . '/Article.php';

/**
 * Клас-менеджер: колекція статей блогу.
 */
class Blog
{
    /** @var Article[] */
    private array $articles = [];

    public function addArticle(Article $article): void
    {
        $this->articles[] = $article;
    }

    /** @return Article[] */
    public function getAll(): array
    {
        return $this->articles;
    }

    public function count(): int
    {
        return count($this->articles);
    }

    /** @return Article[] */
    public function findByAuthor(string $author): array
    {
        return array_values(array_filter(
            $this->articles,
            fn(Article $a): bool => $a->getAuthor() === $author
        ));
    }

    /** @return Article[] найновіші статті першими */
    public function listRecent(int $limit = 3): array
    {
        $sorted = $this->articles;
        usort(
            $sorted,
            fn(Article $a, Article $b): int => strcmp($b->getPublishedAt(), $a->getPublishedAt())
        );
        return array_slice($sorted, 0, $limit);
    }
}
