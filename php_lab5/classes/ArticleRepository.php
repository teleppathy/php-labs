<?php

declare(strict_types=1);

/**
 * Робота з таблицею articles через PDO (усі запити — підготовлені).
 */
class ArticleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Список статей; якщо задано $query — фільтр за заголовком (LIKE).
     * Найновіші статті першими.
     */
    public function searchByTitle(string $query = ''): array
    {
        if ($query === '') {
            return $this->pdo
                ->query('SELECT * FROM articles ORDER BY published_at DESC, id DESC')
                ->fetchAll();
        }

        // Екрануємо службові символи LIKE, щоб % і _ шукалися як звичайний текст
        $pattern = '%' . addcslashes($query, '%_\\') . '%';

        $stmt = $this->pdo->prepare(
            'SELECT * FROM articles WHERE title LIKE :pattern ORDER BY published_at DESC, id DESC'
        );
        $stmt->execute([':pattern' => $pattern]);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** INSERT — повертає id нового запису. */
    public function addArticle(string $title, string $content, string $author, string $publishedAt): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO articles (title, content, author, published_at)
             VALUES (:title, :content, :author, :published_at)'
        );
        $stmt->execute([
            ':title'        => $title,
            ':content'      => $content,
            ':author'       => $author,
            ':published_at' => $publishedAt,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
