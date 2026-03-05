<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Post (module Post).
 */
class PostEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $post = new Post();
        $this->assertSame(PostStatus::PUBLISHED->value, $post->getStatus());
        $this->assertSame(0, $post->getViewCount());
        $this->assertCount(0, $post->getComments());
    }

    public function testGettersSetters(): void
    {
        $post = new Post();
        $post->setTitle('Mon premier post');
        $post->setContent('Contenu du post assez long pour validation.');
        $post->setStatus(PostStatus::DRAFT->value);
        $post->setViewCount(10);

        $this->assertSame('Mon premier post', $post->getTitle());
        $this->assertSame('Contenu du post assez long pour validation.', $post->getContent());
        $this->assertSame(PostStatus::DRAFT->value, $post->getStatus());
        $this->assertSame(10, $post->getViewCount());
    }

    public function testSetCreatedBy(): void
    {
        $user = new User();
        $user->setEmail('author@test.com');
        $post = new Post();
        $post->setCreatedBy($user);
        $this->assertSame($user, $post->getCreatedBy());
    }
}
