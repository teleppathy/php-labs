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

    /** Усі статті (найновіші першими); необов'язковий фільтр за автором. */
    public function findAll(?string $author = null): array
    {
        if ($author === null || $author === '') {
            return $this->pdo
                ->query('SELECT * FROM articles ORDER BY published_at DESC, id DESC')
                ->fetchAll();
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM articles WHERE author = :author ORDER BY published_at DESC, id DESC'
        );
        $stmt->execute([':author' => $author]);

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
    public function create(string $title, string $content, string $author, string $publishedAt): int
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

    /** Доменна дія «опублікувати»: published_at = NOW(). Повертає кількість змінених рядків. */
    public function publish(int $id): int
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET published_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}
