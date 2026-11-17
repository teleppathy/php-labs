<?php

declare(strict_types=1);

/**
 * Базовий клас: стаття блогу.
 */
class Article
{
    protected string $title;
    protected string $content;
    protected string $author;
    protected string $publishedAt;

    public function __construct(string $title, string $content, string $author, string $publishedAt)
    {
        $this->title = $title;
        $this->content = $content;
        $this->author = $author;
        $this->publishedAt = $publishedAt;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getPublishedAt(): string
    {
        return $this->publishedAt;
    }

    public function getInfo(): string
    {
        return "«{$this->title}» — {$this->author}, "
            . date('d.m.Y', strtotime($this->publishedAt));
    }
}
