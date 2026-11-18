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

    /** SELECT * — усі статті. */
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM articles ORDER BY id')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findByAuthor(string $author): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE author = :author ORDER BY id');
        $stmt->execute([':author' => $author]);

        return $stmt->fetchAll();
    }

    public function listRecent(int $limit): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles ORDER BY published_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** Унікальні автори (для випадаючого списку фільтра). */
    public function authors(): array
    {
        return $this->pdo->query('SELECT DISTINCT author FROM articles ORDER BY author')
            ->fetchAll(PDO::FETCH_COLUMN);
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

    /** UPDATE — повертає кількість змінених рядків. */
    public function updateArticle(int $id, string $title, string $content, string $author, string $publishedAt): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE articles
             SET title = :title, content = :content, author = :author, published_at = :published_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':title'        => $title,
            ':content'      => $content,
            ':author'       => $author,
            ':published_at' => $publishedAt,
            ':id'           => $id,
        ]);

        return $stmt->rowCount();
    }

    /** DELETE — повертає кількість видалених рядків. */
    public function deleteArticle(int $id): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}
